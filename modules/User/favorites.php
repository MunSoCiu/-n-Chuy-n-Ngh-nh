<?php
session_start();
require_once '../../app/config/config.php';
require_once '../../includes/functions.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/modules/Login/login.php");
    exit();
}

// Lấy danh sách truyện yêu thích
$query = "SELECT n.*, GROUP_CONCAT(c.name) as categories,
          (SELECT MAX(chapter_id) FROM Chapters WHERE novel_id = n.novel_id) as latest_chapter,
          (SELECT chapter_id FROM Reading_History 
           WHERE user_id = ? AND novel_id = n.novel_id) as last_read_chapter
          FROM Favorites f
          JOIN LightNovels n ON f.novel_id = n.novel_id
          LEFT JOIN Novel_Categories nc ON n.novel_id = nc.novel_id
          LEFT JOIN Categories c ON nc.category_id = c.category_id
          WHERE f.user_id = ?
          GROUP BY n.novel_id
          ORDER BY n.title";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $_SESSION['user_id'], $_SESSION['user_id']);
$stmt->execute();
$favorites = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Truyện yêu thích - Light Novel Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .novel-card {
            transition: transform 0.2s;
        }
        .novel-card:hover {
            transform: translateY(-5px);
        }
        .novel-cover {
            height: 250px;
            object-fit: cover;
        }
        .progress {
            height: 5px;
        }
    </style>
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>

    <div class="container py-5">
        <h2 class="mb-4">
            <i class="fas fa-heart text-danger"></i> Truyện yêu thích
        </h2>

        <?php if ($favorites->num_rows > 0): ?>
            <div class="row g-4">
                <?php while ($novel = $favorites->fetch_assoc()): ?>
                    <div class="col-md-3">
                        <div class="card h-100 novel-card">
                            <img src="<?= BASE_URL . '/' . ($novel['cover_image'] ?: 'images/covers/default-cover.jpg') ?>" 
                                 class="card-img-top novel-cover" 
                                 alt="<?= htmlspecialchars($novel['title']) ?>">
                            
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($novel['title']) ?></h5>
                                <p class="card-text text-muted">
                                    <small>Tác giả: <?= htmlspecialchars($novel['author']) ?></small>
                                </p>
                                
                                <div class="mb-2">
                                    <?php 
                                    $categories = explode(',', $novel['categories']);
                                    foreach ($categories as $category) {
                                        if ($category) {
                                            echo "<span class='badge bg-secondary me-1'>".htmlspecialchars($category)."</span>";
                                        }
                                    }
                                    ?>
                                </div>

                                <?php if ($novel['last_read_chapter']): ?>
                                    <div class="mb-2">
                                        <small class="text-muted">Tiến độ đọc:</small>
                                        <div class="progress">
                                            <?php 
                                            $progress = ($novel['last_read_chapter'] / $novel['latest_chapter']) * 100;
                                            ?>
                                            <div class="progress-bar" role="progressbar" 
                                                 style="width: <?= $progress ?>%"
                                                 aria-valuenow="<?= $progress ?>" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <div class="d-grid gap-2">
                                    <?php if ($novel['last_read_chapter']): ?>
                                        <a href="<?= BASE_URL ?>/modules/Novel/chapter.php?id=<?= $novel['last_read_chapter'] ?>" 
                                           class="btn btn-primary">
                                            Đọc tiếp
                                        </a>
                                    <?php else: ?>
                                        <a href="<?= BASE_URL ?>/modules/Novel/novel.php?id=<?= $novel['novel_id'] ?>" 
                                           class="btn btn-primary">
                                            Đọc ngay
                                        </a>
                                    <?php endif; ?>
                                    
                                    <button class="btn btn-outline-danger" 
                                            onclick="removeFavorite(<?= $novel['novel_id'] ?>, this)">
                                        <i class="fas fa-heart-broken"></i> Bỏ yêu thích
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-heart-broken fa-3x mb-3 text-muted"></i>
                <h3>Chưa có truyện yêu thích</h3>
                <p class="text-muted">Hãy thêm truyện vào danh sách yêu thích của bạn.</p>
                <a href="<?= BASE_URL ?>/index.php" class="btn btn-primary">
                    <i class="fas fa-book"></i> Khám phá truyện
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function removeFavorite(novelId, button) {
        if (confirm('Bạn có chắc muốn bỏ truyện này khỏi danh sách yêu thích?')) {
            fetch('<?= BASE_URL ?>/api/toggle_favorite.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ novel_id: novelId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success' && data.action === 'removed') {
                    // Xóa card khỏi giao diện
                    button.closest('.col-md-3').remove();
                    
                    // Kiểm tra nếu không còn truyện nào
                    if (document.querySelectorAll('.novel-card').length === 0) {
                        location.reload();
                    }
                } else if (data.error) {
                    alert(data.error);
                } else {
                    alert('Có lỗi xảy ra khi bỏ yêu thích');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra. Vui lòng thử lại.');
            });
        }
    }
    </script>
</body>
</html> 