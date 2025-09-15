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
    <?php include '../../../includes/navbar.php'; ?>

    <div class="container py-5">
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= $_SESSION['success_message'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= $_SESSION['error_message'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <div class="row">
            <!-- Thông tin novel -->
            <div class="col-md-4">
                <div class="position-relative mb-4">
                    <img src="<?= BASE_URL . '/' . ($data['novel']['cover_image'] ?: 'images/covers/default-cover.jpg') ?>" 
                         class="img-fluid rounded shadow novel-cover w-100" 
                         alt="<?= htmlspecialchars($data['novel']['title']) ?>">
                    
                    <?php if ($data['novel']['status']): ?>
                        <span class="badge bg-<?= getStatusBadgeClass($data['novel']['status']) ?> status-badge">
                            <i class="fas fa-circle-notch <?= $data['novel']['status'] === 'Đang tiến hành' ? 'fa-spin' : '' ?>"></i>
                            <?= htmlspecialchars($data['novel']['status']) ?>
                        </span>
                    <?php endif; ?>
                    
                    <?php if ($data['novel']['price'] > 0): ?>
                        <div class="price-info">
                            <?php if (isset($data['novel']['discount_percentage']) && $data['novel']['discount_percentage'] > 0): ?>
                                <del class="text-muted"><?= formatPrice($data['novel']['price']) ?></del>
                                <span class="text-danger h4">
                                    <?= formatPrice($data['novel']['price'] * (1 - $data['novel']['discount_percentage']/100)) ?>
                                </span>
                                <span class="badge bg-danger">-<?= $data['novel']['discount_percentage'] ?>%</span>
                            <?php else: ?>
                                <span class="h4"><?= formatPrice($data['novel']['price']) ?></span>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <span class="badge bg-success">Miễn phí</span>
                    <?php endif; ?>
                </div>

                <div class="d-grid gap-2">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <button class="btn <?= $data['is_favorited'] ? 'btn-danger' : 'btn-outline-danger' ?>" 
                                onclick="toggleFavorite(<?= $data['novel']['novel_id'] ?>)" 
                                id="favoriteBtn">
                            <i class="fas fa-heart"></i>
                            <span id="favoriteText">
                                <?= $data['is_favorited'] ? 'Đã yêu thích' : 'Yêu thích' ?>
                            </span>
                        </button>
                    <?php else: ?>
                        <a href="../../../app/Controllers/User/Login_controller.php" class="btn btn-outline-danger">
                            <i class="fas fa-heart"></i> Đăng nhập để yêu thích
                        </a>
                    <?php endif; ?>

                    <?php if ($data['novel']['price'] > 0): ?>
                        <?php if (!$data['has_purchased'] && isset($_SESSION['user_id'])): ?>
                            <a href="../../../app/Controllers/User/Purchase_controller.php?id=<?= $data['novel']['novel_id'] ?>" 
                               class="btn btn-primary w-100">
                                <i class="fas fa-shopping-cart me-1"></i>
                                Mua với giá <?= formatPrice($data['novel']['price']) ?>
                            </a>
                        <?php elseif ($data['has_purchased']): ?>
                            <div class="btn btn-success w-100" disabled>
                                <i class="fas fa-check me-1"></i> Đã mua
                            </div>
                        <?php elseif (!isset($_SESSION['user_id'])): ?>
                            <a href="../../../app/Controllers/User/Login_controller.php" class="btn btn-primary w-100">
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
                                Tác giả: <?= htmlspecialchars($data['novel']['author']) ?>
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-heart text-danger me-2"></i>
                                Lượt yêu thích: <span id="favoriteCount"><?= $data['favorite_count'] ?></span>
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-eye text-info me-2"></i>
                                Lượt đọc: <?= $data['read_count'] ?>
                            </li>
                            <li>
                                <i class="fas fa-clock text-success me-2"></i>
                                Cập nhật: <?= timeAgo($data['novel']['created_at']) ?>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <h1 class="mb-3"><?= htmlspecialchars($data['novel']['title']) ?></h1>
                <p class="text-muted">
                    <i class="fas fa-user me-1"></i> 
                    Tác giả: <?= htmlspecialchars($data['novel']['author']) ?>
                </p>
                
                <div class="mb-3">
                    <h5>Danh mục:</h5>
                    <?php
                    $categories = !is_null($data['novel']['categories']) ? explode(',', $data['novel']['categories']) : [];
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
                        <p class="card-text"><?= nl2br(htmlspecialchars($data['novel']['description'])) ?></p>
                    </div>
                </div>

                <?php if (!$data['can_read']): ?>
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
                        <?php if ($data['chapters']->num_rows > 0): ?>
                            <?php while ($chapter = $data['chapters']->fetch_assoc()): ?>
                                <a href="<?= $data['can_read'] ? '../../../app/Controllers/User/Chapter_controller.php?id=' . $chapter['chapter_id'] : '#' ?>" 
                                   class="d-flex justify-content-between align-items-center text-decoration-none text-dark p-2 border-bottom <?= !$data['can_read'] ? 'disabled' : '' ?>"
                                   <?= !$data['can_read'] ? 'onclick="return false;"' : '' ?>>
                                    <span>
                                        <?php if (!$data['can_read']): ?>
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

                <!-- Bình luận -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-comments me-1"></i> Bình luận (<?= $data['comment_count'] ?>)
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <!-- Form thêm bình luận -->
                            <form method="POST" class="mb-4">
                                <div class="mb-3">
                                    <label class="form-label">Viết bình luận</label>
                                    <textarea class="form-control" name="comment_content" rows="3" 
                                              placeholder="Chia sẻ suy nghĩ của bạn về truyện này..." required></textarea>
                                </div>
                                <button type="submit" name="add_comment" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-1"></i> Gửi bình luận
                                </button>
                            </form>
                            <hr>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <a href="../../../app/Controllers/User/Login_controller.php">Đăng nhập</a> để viết bình luận
                            </div>
                        <?php endif; ?>

                        <!-- Danh sách bình luận -->
                        <?php if ($data['comments']->num_rows > 0): ?>
                            <div class="comments-list">
                                <?php while ($comment = $data['comments']->fetch_assoc()): ?>
                                    <div class="comment-item border-bottom pb-3 mb-3">
                                        <div class="d-flex align-items-start">
                                            <img src="<?= $comment['avatar_url'] ? '../../../' . $comment['avatar_url'] : '../../../images/avatars/Avatar.jpg' ?>" 
                                                 class="rounded-circle me-3" style="width: 40px; height: 40px; object-fit: cover;" 
                                                 alt="<?= htmlspecialchars($comment['username']) ?>">
                                            <div class="flex-grow-1">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <h6 class="mb-0"><?= htmlspecialchars($comment['username']) ?></h6>
                                                    <small class="text-muted"><?= timeAgo($comment['created_at']) ?></small>
                                                </div>
                                                <p class="mb-0"><?= nl2br(htmlspecialchars($comment['content'])) ?></p>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-comments fa-3x mb-3 text-muted"></i>
                                <h5>Chưa có bình luận nào</h5>
                                <p class="text-muted">Hãy là người đầu tiên bình luận về truyện này!</p>
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
            </div>
        </div>
    </div>

    <?php include '../../../includes/footer.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Xử lý sắp xếp chapter
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

        function toggleFavorite(novelId) {
            fetch('../../../api/toggle_favorite.php', {
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
                    
                    count.textContent = data.count;
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
