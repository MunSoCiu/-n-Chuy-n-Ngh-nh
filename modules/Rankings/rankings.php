<?php
session_start();
require_once '../../app/config/config.php';
require_once '../../includes/functions.php';

$type = isset($_GET['type']) ? $_GET['type'] : 'favorite';

// Lấy truyện theo lượt yêu thích
if ($type === 'favorite') {
    $query = "SELECT n.*, COUNT(f.novel_id) as favorite_count,
              GROUP_CONCAT(c.name) as categories
              FROM LightNovels n
              LEFT JOIN Favorites f ON n.novel_id = f.novel_id
              LEFT JOIN Novel_Categories nc ON n.novel_id = nc.novel_id
              LEFT JOIN Categories c ON nc.category_id = c.category_id
              GROUP BY n.novel_id
              ORDER BY favorite_count DESC
              LIMIT 10";
    $novels = $conn->query($query);
    $title = "Truyện được yêu thích nhiều nhất";
    $count_label = "lượt yêu thích";
    $count_field = "favorite_count";
    $icon = "heart";
    $color = "danger";
}
// Lấy truyện theo lượt đọc
elseif ($type === 'reading') {
    $query = "SELECT n.*, COUNT(DISTINCT rh.user_id) as read_count,
              GROUP_CONCAT(c.name) as categories
              FROM LightNovels n
              LEFT JOIN Reading_History rh ON n.novel_id = rh.novel_id
              LEFT JOIN Novel_Categories nc ON n.novel_id = nc.novel_id
              LEFT JOIN Categories c ON nc.category_id = c.category_id
              GROUP BY n.novel_id
              ORDER BY read_count DESC
              LIMIT 10";
    $novels = $conn->query($query);
    $title = "Truyện được đọc nhiều nhất";
    $count_label = "lượt đọc";
    $count_field = "read_count";
    $icon = "book-reader";
    $color = "primary";
}
// Lấy truyện mới cập nhật
elseif ($type === 'bestseller') {
    $query = "SELECT n.*, 
              COUNT(DISTINCT p.purchase_id) as sold_count,
              SUM(p.price - p.discount_applied) as total_revenue,
              GROUP_CONCAT(DISTINCT c.name) as categories,
              COUNT(DISTINCT f.novel_id) as favorite_count,
              COUNT(DISTINCT rh.user_id) as read_count
              FROM LightNovels n
              LEFT JOIN Purchases p ON n.novel_id = p.novel_id AND p.status = 'completed'
              LEFT JOIN Novel_Categories nc ON n.novel_id = nc.novel_id
              LEFT JOIN Categories c ON nc.category_id = c.category_id
              LEFT JOIN Favorites f ON n.novel_id = f.novel_id
              LEFT JOIN Reading_History rh ON n.novel_id = rh.novel_id
              GROUP BY n.novel_id, n.title, n.author, n.cover_image, n.price
              HAVING sold_count > 0
              ORDER BY sold_count DESC, total_revenue DESC
              LIMIT 10";
    $novels = $conn->query($query);
    $title = "Truyện bán chạy nhất";
    $count_label = "lượt mua";
    $count_field = "sold_count";
    $icon = "shopping-cart";
    $color = "warning";
}
// Lấy truyện mới cập nhật
else {
    $query = "SELECT n.*, 
              (SELECT MAX(created_at) FROM Chapters WHERE novel_id = n.novel_id) as last_update,
              GROUP_CONCAT(c.name) as categories
              FROM LightNovels n
              LEFT JOIN Novel_Categories nc ON n.novel_id = nc.novel_id
              LEFT JOIN Categories c ON nc.category_id = c.category_id
              GROUP BY n.novel_id
              HAVING last_update IS NOT NULL
              ORDER BY last_update DESC
              LIMIT 10";
    $novels = $conn->query($query);
    $title = "Truyện mới cập nhật";
    $count_label = "cập nhật";
    $count_field = "last_update";
    $icon = "clock";
    $color = "success";
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - Light Novel Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .novel-card {
            transition: transform 0.2s;
        }
        .novel-card:hover {
            transform: translateY(-5px);
        }
        .rank-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: rgba(0,0,0,0.7);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.2rem;
        }
    </style>
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>

    <div class="container py-5">
        <!-- Tabs for different rankings -->
        <ul class="nav nav-pills mb-4">
            <li class="nav-item">
                <a class="nav-link <?= $type === 'favorite' ? 'active' : '' ?>" 
                   href="?type=favorite">
                    <i class="fas fa-heart me-1"></i> Yêu thích nhiều nhất
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $type === 'reading' ? 'active' : '' ?>" 
                   href="?type=reading">
                    <i class="fas fa-book-reader me-1"></i> Đọc nhiều nhất
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $type === 'bestseller' ? 'active' : '' ?>" 
                   href="?type=bestseller">
                    <i class="fas fa-shopping-cart me-1"></i> Bán chạy nhất
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $type === 'new' ? 'active' : '' ?>" 
                   href="?type=new">
                    <i class="fas fa-clock me-1"></i> Mới cập nhật
                </a>
            </li>
        </ul>

        <h2 class="mb-4"><?= $title ?></h2>
        
        <div class="row g-4">
            <?php $rank = 1; ?>
            <?php while ($novel = $novels->fetch_assoc()): ?>
                <div class="col-md-6">
                    <div class="card h-100 novel-card">
                        <div class="row g-0">
                            <div class="col-md-4 position-relative">
                                <span class="rank-badge">#<?= $rank ?></span>
                                <img src="<?= BASE_URL . '/' . ($novel['cover_image'] ?: 'images/covers/default-cover.jpg') ?>" 
                                     class="img-fluid rounded-start h-100" 
                                     style="object-fit: cover;"
                                     alt="<?= htmlspecialchars($novel['title']) ?>">
                            </div>
                            <div class="col-md-8">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <?= htmlspecialchars($novel['title']) ?>
                                    </h5>
                                    <p class="card-text">
                                        <small class="text-muted">
                                            Tác giả: <?= htmlspecialchars($novel['author']) ?>
                                        </small>
                                    </p>
                                    <p class="card-text">
                                        <?php 
                                        $categories = explode(',', $novel['categories']);
                                        foreach ($categories as $category) {
                                            if ($category) {
                                                echo "<span class='badge bg-secondary me-1'>".htmlspecialchars($category)."</span>";
                                            }
                                        }
                                        ?>
                                    </p>
                                    <p class="card-text">
                                        <i class="fas fa-<?= $icon ?> text-<?= $color ?>"></i> 
                                        <?php if ($type === 'new'): ?>
                                            Cập nhật: <?= timeAgo($novel[$count_field]) ?>
                                        <?php else: ?>
                                            <?= number_format($novel[$count_field]) ?> <?= $count_label ?>
                                        <?php endif; ?>
                                    </p>
                                    <?php if ($type === 'bestseller'): ?>
                                        <p class="card-text">
                                            <span class="badge bg-success me-2">
                                                <i class="fas fa-shopping-cart"></i> 
                                                <?= number_format($novel[$count_field]) ?> <?= $count_label ?>
                                            </span>
                                        </p>
                                    <?php endif; ?>
                                    <a href="<?= BASE_URL ?>/modules/Novel/novel.php?id=<?= $novel['novel_id'] ?>" 
                                       class="btn btn-primary">
                                        Đọc ngay
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php $rank++; ?>
            <?php endwhile; ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 