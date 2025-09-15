<?php
session_start();
require_once '../../app/config/config.php';
require_once '../../includes/functions.php';

// Lấy novel_id từ URL
$novel_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Lấy thông tin chi tiết của novel
$query = "SELECT n.*, GROUP_CONCAT(c.name) as categories 
          FROM LightNovels n 
          LEFT JOIN Novel_Categories nc ON n.novel_id = nc.novel_id
          LEFT JOIN Categories c ON nc.category_id = c.category_id
          WHERE n.novel_id = ?
          GROUP BY n.novel_id";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $novel_id);
$stmt->execute();
$novel = $stmt->get_result()->fetch_assoc();

if (!$novel) {
    header("Location: index.php");
    exit();
}

// Kiểm tra quyền đọc truyện
$can_read = true;
$has_purchased = false;
if ($novel['price'] > 0) {
    $can_read = false;
    if (isset($_SESSION['user_id'])) {
        // Kiểm tra xem người dùng đã mua truyện chưa
        $check_purchase = $conn->prepare("SELECT 1 FROM Purchases WHERE user_id = ? AND novel_id = ?");
        $check_purchase->bind_param("ii", $_SESSION['user_id'], $novel_id);
        $check_purchase->execute();
        $has_purchased = $check_purchase->get_result()->num_rows > 0;
        $can_read = $has_purchased || (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');
    }
}

// Xử lý mua truyện
if (isset($_POST['purchase']) && isset($_SESSION['user_id']) && !$has_purchased) {
    $user_id = $_SESSION['user_id'];
    $price = $novel['price'];
    $discount = 0; // Có thể thêm logic khuyến mãi ở đây

    $purchase_stmt = $conn->prepare("INSERT INTO Purchases (user_id, novel_id, price, discount_applied) VALUES (?, ?, ?, ?)");
    $purchase_stmt->bind_param("iidd", $user_id, $novel_id, $price, $discount);
    
    if ($purchase_stmt->execute()) {
        $has_purchased = true;
        $can_read = true;
        $_SESSION['success_message'] = "Mua truyện thành công!";
        header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $novel_id);
        exit();
    }
}

// Lấy danh sách chapter
$query = "SELECT chapter_id, title, created_at 
          FROM Chapters 
          WHERE novel_id = ? 
          ORDER BY chapter_id ASC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $novel_id);
$stmt->execute();
$chapters = $stmt->get_result();

// Kiểm tra trạng thái yêu thích
$is_favorited = false;
$favorite_count = 0;
if (isset($_SESSION['user_id'])) {
    $fav_check = $conn->prepare("SELECT 1 FROM Favorites WHERE user_id = ? AND novel_id = ?");
    $fav_check->bind_param("ii", $_SESSION['user_id'], $novel_id);
    $fav_check->execute();
    $is_favorited = $fav_check->get_result()->num_rows > 0;
}

// Lấy số lượt yêu thích
$count_query = "SELECT COUNT(*) as count FROM Favorites WHERE novel_id = ?";
$stmt = $conn->prepare($count_query);
$stmt->bind_param("i", $novel_id);
$stmt->execute();
$favorite_count = $stmt->get_result()->fetch_assoc()['count'];

// Xử lý yêu thích
if (isset($_POST['toggle_favorite']) && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    
    if ($is_favorited) {
        $stmt = $conn->prepare("DELETE FROM Favorites WHERE user_id = ? AND novel_id = ?");
        $stmt->bind_param("ii", $user_id, $novel_id);
        $stmt->execute();
        echo json_encode(['status' => 'removed']);
    } else {
        $stmt = $conn->prepare("INSERT INTO Favorites (user_id, novel_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $user_id, $novel_id);
        $stmt->execute();
        echo json_encode(['status' => 'added']);
    }
    exit();
}

// Xử lý thêm chapter mới (chỉ dành cho admin)
if (isset($_POST['add_chapter']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    $title = trim($_POST['chapter_title']);
    $content = trim($_POST['chapter_content']);
    
    if (!empty($title) && !empty($content)) {
        $stmt = $conn->prepare("INSERT INTO Chapters (novel_id, title, content) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $novel_id, $title, $content);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Thêm chapter mới thành công!";
            header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $novel_id);
            exit();
        }
    }
}

// Add read count query after novel query
$read_count_query = "SELECT COUNT(DISTINCT user_id) as count FROM Reading_History WHERE novel_id = ?";
$stmt = $conn->prepare($read_count_query);
$stmt->bind_param("i", $novel_id);
$stmt->execute();
$read_count = $stmt->get_result()->fetch_assoc()['count'];

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết sách - Nhà Sách Minh An</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .novel-cover {
            max-height: 500px;
            object-fit: cover;
        }
        .chapter-list {
            max-height: 500px;
            overflow-y: auto;
        }
        .favorite-btn.active {
            background-color: #dc3545;
            border-color: #dc3545;
            color: white;
        }
        .favorite-btn:not(.active) {
            background-color: white;
            border-color: #dc3545;
            color: #dc3545;
        }
        .favorite-btn:hover {
            opacity: 0.9;
        }
        .status-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 8px 12px;
        }
        .price-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            padding: 8px 12px;
        }
    </style>
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>

    <div class="container py-5">
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= $_SESSION['success_message'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <div class="row">
            <!-- Thông tin novel -->
            <div class="col-md-4">
                <div class="position-relative mb-4">
                    <img src="<?= BASE_URL . '/' . ($novel['cover_image'] ?: 'images/covers/default-cover.jpg') ?>" 
                         class="img-fluid rounded shadow novel-cover w-100" 
                         alt="<?= htmlspecialchars($novel['title']) ?>">
                    
                    <?php if ($novel['status']): ?>
                        <span class="badge bg-<?= getStatusBadgeClass($novel['status']) ?> status-badge">
                            <i class="fas fa-circle-notch <?= $novel['status'] === 'Đang tiến hành' ? 'fa-spin' : '' ?>"></i>
                            <?= htmlspecialchars($novel['status']) ?>
                        </span>
                    <?php endif; ?>
                    
                    <?php if ($novel['price'] > 0): ?>
                        <div class="price-info">
                            <?php if (isset($novel['discount_percentage']) && $novel['discount_percentage'] > 0): ?>
                                <del class="text-muted"><?= formatPrice($novel['price']) ?></del>
                                <span class="text-danger h4">
                                    <?= formatPrice($novel['price'] * (1 - $novel['discount_percentage']/100)) ?>
                                </span>
                                <span class="badge bg-danger">-<?= $novel['discount_percentage'] ?>%</span>
                            <?php else: ?>
                                <span class="h4"><?= formatPrice($novel['price']) ?></span>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <span class="badge bg-success">Miễn phí</span>
                    <?php endif; ?>
                </div>

                <div class="d-grid gap-2">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <button class="btn <?= $is_favorited ? 'btn-danger' : 'btn-outline-danger' ?>" 
                                onclick="toggleFavorite(<?= $novel['novel_id'] ?>)" 
                                id="favoriteBtn">
                            <i class="fas fa-heart"></i>
                            <span id="favoriteText">
                                <?= $is_favorited ? 'Đã yêu thích' : 'Yêu thích' ?>
                            </span>
                        </button>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>/modules/Login/login.php" class="btn btn-outline-danger">
                            <i class="fas fa-heart"></i> Đăng nhập để yêu thích
                        </a>
                    <?php endif; ?>

                    <?php if ($novel['price'] > 0): ?>
                        <?php if (!$has_purchased && isset($_SESSION['user_id'])): ?>
                            <a href="purchase.php?id=<?= $novel_id ?>" class="btn btn-primary">
                                <i class="fas fa-shopping-cart me-1"></i>
                                Mua với giá <?= formatPrice($novel['price']) ?>
                            </a>
                        <?php elseif ($has_purchased): ?>
                            <span class="badge bg-success p-2 text-center">
                                <i class="fas fa-check me-1"></i> Đã mua
                            </span>
                        <?php elseif (!isset($_SESSION['user_id'])): ?>
                            <a href="<?= BASE_URL ?>/modules/Login/login.php" class="btn btn-primary">
                                <i class="fas fa-shopping-cart me-1"></i>
                                Đăng nhập để mua
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <div class="card mt-3">
                    <div class="card-body">
                        <h5 class="card-title">Thông tin truyện</h5>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <i class="fas fa-user text-primary me-2"></i>
                                Tác giả: <?= htmlspecialchars($novel['author']) ?>
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-heart text-danger me-2"></i>
                                Lượt yêu thích: <span id="favoriteCount"><?= $favorite_count ?></span>
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-eye text-info me-2"></i>
                                Lượt đọc: <?= $read_count ?>
                            </li>
                            <li>
                                <i class="fas fa-clock text-success me-2"></i>
                                Cập nhật: <?= timeAgo($novel['created_at']) ?>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <h1 class="mb-3">Thông tin sách</h1>
                <p class="text-muted">
                    <i class="fas fa-user me-1"></i> 
                    Tác giả: <?= htmlspecialchars($novel['author']) ?>
                </p>
                
                <div class="mb-3">
                    <h5>Danh mục:</h5>
                    <?php
                    $categories = !is_null($novel['categories']) ? explode(',', $novel['categories']) : [];
                    foreach ($categories as $cat) {
                        if ($cat) {
                            echo "<span class='badge bg-secondary me-1'>".htmlspecialchars($cat)."</span>";
                        }
                    }
                    ?>
                </div>

                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-info-circle me-1"></i> Giới thiệu
                        </h5>
                        <p class="card-text"><?= nl2br(htmlspecialchars($novel['description'])) ?></p>
                    </div>
                </div>

                <?php if (!$can_read): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-lock me-2"></i>
                        Bạn cần mua truyện để có thể đọc nội dung.
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-list me-1"></i> Danh sách chương
                        </h5>
                        <div class="btn-group">
                            <button class="btn btn-outline-secondary btn-sm" onclick="sortChapters('asc')">
                                <i class="fas fa-sort-numeric-down"></i>
                            </button>
                            <button class="btn btn-outline-secondary btn-sm" onclick="sortChapters('desc')">
                                <i class="fas fa-sort-numeric-up"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body chapter-list" id="chapter-container">
                        <?php if ($chapters->num_rows > 0): ?>
                            <?php while ($chapter = $chapters->fetch_assoc()): ?>
                                <a href="<?= $can_read ? 'chapter.php?id=' . $chapter['chapter_id'] : '#' ?>" 
                                   class="d-flex justify-content-between align-items-center text-decoration-none text-dark p-2 border-bottom <?= !$can_read ? 'disabled' : '' ?>"
                                   <?= !$can_read ? 'onclick="return false;"' : '' ?>>
                                    <span>
                                        <?php if (!$can_read): ?>
                                            <i class="fas fa-lock text-muted me-2"></i>
                                        <?php endif; ?>
                                        <?= htmlspecialchars($chapter['title']) ?>
                                    </span>
                                    <small class="text-muted">
                                        <?= date('d/m/Y', strtotime($chapter['created_at'])) ?>
                                    </small>
                                </a>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-book fa-3x mb-3 text-muted"></i>
                                <h3>Chưa có chương nào</h3>
                                <p class="text-muted">Sách này chưa có chương nào được thêm vào.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-plus me-1"></i> Thêm chapter mới
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Tiêu đề chapter</label>
                                    <input type="text" class="form-control" name="chapter_title" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Nội dung</label>
                                    <textarea class="form-control" name="chapter_content" rows="10" required></textarea>
                                </div>
                                <button type="submit" name="add_chapter" class="btn btn-primary">
                                    <i class="fas fa-plus me-1"></i> Thêm chapter
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Comments Section -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h4>Bình luận</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <form id="comment-form" method="POST">
                                <div class="mb-3">
                                    <textarea class="form-control" name="comment" rows="3" required 
                                              placeholder="Viết bình luận của bạn..."></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-1"></i> Gửi bình luận
                                </button>
                            </form>
                        <?php else: ?>
                            <p>Vui lòng <a href="<?= BASE_URL ?>/modules/Login/login.php">đăng nhập</a> để bình luận.</p>
                        <?php endif; ?>

                        <div class="comments-list mt-4">
                            <?php
                            $comments_query = "SELECT c.*, u.username, u.avatar_url as avatar 
                                             FROM Comments c 
                                             JOIN Users u ON c.user_id = u.user_id 
                                             WHERE c.novel_id = ? 
                                             ORDER BY c.created_at DESC";
                            $stmt = $conn->prepare($comments_query);
                            $stmt->bind_param("i", $novel_id);
                            $stmt->execute();
                            $comments = $stmt->get_result();
                            
                            while ($comment = $comments->fetch_assoc()):
                            ?>
                                <div class="comment mb-3 p-3 border rounded" data-comment-id="<?= $comment['comment_id'] ?>">
                                    <div class="d-flex align-items-center mb-2">
                                        <img src="<?= BASE_URL . ($comment['avatar'] ?: '/images/Avatar.jpg') ?>" 
                                             class="rounded-circle me-2" width="40" height="40" alt="Avatar">
                                        <div>
                                            <strong><?= htmlspecialchars($comment['username']) ?></strong>
                                            <small class="text-muted d-block">
                                                <?= timeAgo($comment['created_at']) ?>
                                            </small>
                                        </div>
                                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                            <button class="btn btn-danger btn-sm ms-auto" 
                                                    onclick="deleteComment(<?= $comment['comment_id'] ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                    <p class="mb-0"><?= nl2br(htmlspecialchars($comment['content'])) ?></p>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../../includes/footer.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Xử lý yêu thích
        $('.favorite-btn').click(function() {
            var btn = $(this);
            var novelId = btn.data('novel-id');
            
            $.ajax({
                url: '<?= $_SERVER['PHP_SELF'] ?>',
                method: 'POST',
                data: {
                    toggle_favorite: true,
                    novel_id: novelId
                },
                success: function(response) {
                    var data = JSON.parse(response);
                    if (data.status === 'added') {
                        btn.addClass('active');
                        btn.find('.favorite-text').text('Đã yêu thích');
                    } else {
                        btn.removeClass('active');
                        btn.find('.favorite-text').text('Yêu thích');
                    }
                }
            });
        });

        // Xử lý sắp xếp chapter
        let chaptersAsc = true;
        function sortChapters(direction) {
            const container = document.getElementById('chapter-container');
            const chapters = Array.from(container.children);
            
            chapters.sort((a, b) => {
                const aText = a.querySelector('span').textContent;
                const bText = b.querySelector('span').textContent;
                return direction === 'asc' ? 
                    aText.localeCompare(bText, undefined, {numeric: true}) :
                    bText.localeCompare(aText, undefined, {numeric: true});
            });

            container.innerHTML = '';
            chapters.forEach(chapter => container.appendChild(chapter));
        }

        // Xử lý bình luận
        $('#comment-form').submit(function(e) {
            e.preventDefault();
            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: {
                    comment: $('textarea[name="comment"]').val(),
                    novel_id: <?= $novel_id ?>
                },
                success: function(response) {
                    location.reload();
                }
            });
        });

        // Xử lý xóa bình luận
        function deleteComment(commentId) {
            if (confirm('Bạn có chắc muốn xóa bình luận này?')) {
                $.ajax({
                    url: '',
                    method: 'POST',
                    data: {
                        delete_comment: commentId
                    },
                    success: function(response) {
                        $(`[data-comment-id="${commentId}"]`).fadeOut();
                    }
                });
            }
        }

        function toggleFavorite(novelId) {
            fetch('<?= BASE_URL ?>/api/toggle_favorite.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ novel_id: novelId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    const btn = document.getElementById('favoriteBtn');
                    const text = document.getElementById('favoriteText');
                    const count = document.getElementById('favoriteCount');
                    
                    if (data.action === 'added') {
                        btn.classList.remove('btn-outline-danger');
                        btn.classList.add('btn-danger');
                        text.textContent = 'Đã yêu thích';
                    } else {
                        btn.classList.remove('btn-danger');
                        btn.classList.add('btn-outline-danger');
                        text.textContent = 'Yêu thích';
                    }
                    
                    count.innerHTML = `<i class="fas fa-heart text-danger"></i> ${data.count.toLocaleString()} lượt yêu thích`;
                } else {
                    alert(data.error || 'Có lỗi xảy ra');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra');
            });
        }
    </script>
</body>
</html>

<?php
// Xử lý bình luận
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Please login to comment']);
        exit;
    }

    $comment = trim($_POST['comment']);
    if (empty($comment)) {
        echo json_encode(['error' => 'Comment cannot be empty']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO Comments (novel_id, user_id, content, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iis", $novel_id, $_SESSION['user_id'], $comment);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
        exit;
    }
}

// Xử lý xóa bình luận
if (isset($_POST['delete_comment']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    $comment_id = (int)$_POST['delete_comment'];
    $stmt = $conn->prepare("DELETE FROM Comments WHERE comment_id = ?");
    $stmt->bind_param("i", $comment_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
        exit;
    }
}

function getStatusBadgeClass($status) {
    switch($status) {
        case 'Đang tiến hành':
            return 'primary';
        case 'Đã hoàn thành':
            return 'success';
        case 'Đã hủy bỏ':
            return 'danger';
        default:
            return 'secondary';
    }
}
?> 