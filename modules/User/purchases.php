<?php
session_start();
require_once '../../app/config/config.php';
require_once '../../includes/functions.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/modules/Login/login.php");
    exit();
}

// Lấy danh sách truyện đã mua
$query = "SELECT n.*, p.purchase_date, p.price as paid_price, p.discount_applied,
          GROUP_CONCAT(c.name) as categories
          FROM Purchases p
          JOIN LightNovels n ON p.novel_id = n.novel_id
          LEFT JOIN Novel_Categories nc ON n.novel_id = nc.novel_id
          LEFT JOIN Categories c ON nc.category_id = c.category_id
          WHERE p.user_id = ?
          GROUP BY n.novel_id, n.title, n.author, n.description, n.cover_image, 
                   n.status, n.price, n.created_at,
                   p.purchase_date, p.price, p.discount_applied
          ORDER BY p.purchase_date DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$purchases = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Truyện đã mua - Light Novel Hub</title>
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
    </style>
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>

    <div class="container py-5">
        <h2 class="mb-4">
            <i class="fas fa-shopping-cart text-primary"></i> Truyện đã mua
        </h2>

        <?php if ($purchases->num_rows > 0): ?>
            <div class="row g-4">
                <?php while ($novel = $purchases->fetch_assoc()): ?>
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

                                <p class="card-text">
                                    <small class="text-muted">
                                        Đã mua: <?= timeAgo($novel['purchase_date']) ?>
                                    </small>
                                </p>
                                <p class="card-text">
                                    <span class="badge bg-success">
                                        Đã thanh toán: <?= formatPrice($novel['paid_price']) ?>
                                    </span>
                                    <?php if ($novel['discount_applied'] > 0): ?>
                                        <span class="badge bg-warning text-dark ms-1">
                                            Giảm: <?= formatPrice($novel['discount_applied']) ?>
                                        </span>
                                    <?php endif; ?>
                                </p>

                                <a href="<?= BASE_URL ?>/modules/Novel/novel.php?id=<?= $novel['novel_id'] ?>" 
                                   class="btn btn-primary w-100">
                                    <i class="fas fa-book-reader me-1"></i> Đọc ngay
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-shopping-cart fa-3x mb-3 text-muted"></i>
                <h3>Chưa có truyện đã mua</h3>
                <p class="text-muted">Hãy khám phá và mua truyện để thêm vào bộ sưu tập của bạn.</p>
                <a href="<?= BASE_URL ?>/modules/Novel/list.php?type=paid" class="btn btn-primary">
                    <i class="fas fa-book-open me-1"></i> Khám phá truyện trả phí
                </a>
            </div>
        <?php endif; ?>
    </div>

    <?php include '../../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 