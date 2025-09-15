<?php
session_start();
require_once 'app/config/config.php';
require_once 'includes/functions.php';
require_once 'includes/novel_card.php';

// Khởi tạo các biến mặc định
$type = isset($_GET['type']) ? $_GET['type'] : 'all';
$category = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

// Xây dựng query
$query = "SELECT n.*, GROUP_CONCAT(DISTINCT c.name) as categories,
          COUNT(DISTINCT f.user_id) as favorite_count,
          COUNT(DISTINCT rh.user_id) as read_count
          FROM LightNovels n
          LEFT JOIN Novel_Categories nc ON n.novel_id = nc.novel_id
          LEFT JOIN Categories c ON nc.category_id = c.category_id
          LEFT JOIN Favorites f ON n.novel_id = f.novel_id
          LEFT JOIN Reading_History rh ON n.novel_id = rh.novel_id
          WHERE 1=1";

// Thêm điều kiện lọc theo loại
if ($type === 'free') {
    $query .= " AND n.price = 0";
} elseif ($type === 'paid') {
    $query .= " AND n.price > 0";
}

// Thêm điều kiện lọc theo thể loại
if ($category > 0) {
    $query .= " AND nc.category_id = " . $category;
}

$query .= " GROUP BY n.novel_id";

// Thêm sắp xếp
$query .= " ORDER BY n.created_at DESC";

// Thêm phân trang
$query .= " LIMIT ? OFFSET ?";

// Lấy tổng số truyện để tính số trang
$count_query = str_replace("SELECT n.*, GROUP_CONCAT(DISTINCT c.name) as categories", 
                          "SELECT COUNT(DISTINCT n.novel_id) as total", 
                          substr($query, 0, strpos($query, " GROUP BY")));
$total_result = $conn->query($count_query);
$total_novels = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_novels / $limit);

// Thực thi query chính
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$novels = $stmt->get_result();

// Lấy danh sách thể loại cho bộ lọc
$categories = $conn->query("SELECT * FROM Categories ORDER BY name");

// Lấy thể loại phổ biến
$popular_categories = $conn->query("
    SELECT c.category_id, c.name, COUNT(nc.novel_id) as novel_count
    FROM Categories c
    LEFT JOIN Novel_Categories nc ON c.category_id = nc.category_id
    GROUP BY c.category_id
    ORDER BY novel_count DESC
    LIMIT 10
");

// Lấy truyện nổi bật (nhiều lượt yêu thích nhất)
$featured_novels = $conn->query("
    SELECT n.*, GROUP_CONCAT(DISTINCT c.name) as categories,
    COUNT(DISTINCT f.novel_id) as favorite_count,
    COUNT(DISTINCT rh.user_id) as read_count
    FROM LightNovels n
    LEFT JOIN Novel_Categories nc ON n.novel_id = nc.novel_id
    LEFT JOIN Categories c ON nc.category_id = c.category_id
    LEFT JOIN Favorites f ON n.novel_id = f.novel_id
    LEFT JOIN Reading_History rh ON n.novel_id = rh.novel_id
    GROUP BY n.novel_id
    ORDER BY favorite_count DESC
    LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nhà Sách Số - Đọc Sách Online</title>
    <meta name="description" content="Đọc sách online miễn phí với kho sách đa dạng, cập nhật nhanh nhất">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .novel-card {
            transition: transform 0.2s;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        .novel-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,.2);
        }
        .novel-cover {
            height: 250px;
            object-fit: cover;
        }
        .status-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
        }
        .price-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
        }
        .novel-card .card-body {
            display: flex;
            flex-direction: column;
            flex: 1;
        }
        .novel-card .btn-primary {
            margin-top: auto;
        }
        .card-title {
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
            font-weight: 600;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .categories {
            margin: 0.5rem 0;
        }
        .categories .badge {
            font-size: 0.8rem;
            padding: 5px 10px;
            margin-right: 5px;
            margin-bottom: 5px;
            border-radius: 15px;
        }
        .hover-bg-light:hover {
            background-color: rgba(0,0,0,.03);
        }
        .hero-section {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }
        .carousel-item img {
            filter: brightness(0.8);
        }
        .carousel-caption {
            padding: 15px;
            left: 10%;
            right: 10%;
            bottom: 20px;
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <!-- Hero Section -->
    <div class="hero-section py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-md-6 mb-4 mb-md-0">
                    <h1 class="display-4 fw-bold mb-3">Khám phá thế giới sách</h1>
                    <p class="lead mb-4">Thư viện sách online với hàng nghìn tác phẩm đa dạng. Đọc mọi lúc, mọi nơi!</p>
                    <div class="d-flex gap-3">
                        <a href="<?= BASE_URL ?>/app/Controllers/User/Categories_controller.php" class="btn btn-primary btn-lg">Khám phá ngay</a>
                        <?php if (!isset($_SESSION['user_id'])): ?>
                            <a href="<?= BASE_URL ?>/app/Controllers/User/Register_controller.php" class="btn btn-outline-primary btn-lg">Đăng ký</a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <?php
                    // Lấy 5 sách được yêu thích nhất
                    $top_novels_query = "SELECT n.*, COUNT(f.novel_id) as favorite_count 
                                       FROM LightNovels n 
                                       LEFT JOIN Favorites f ON n.novel_id = f.novel_id 
                                       GROUP BY n.novel_id 
                                       ORDER BY favorite_count DESC 
                                       LIMIT 5";
                    $top_novels = $conn->query($top_novels_query);
                    ?>
                    
                    <div id="heroSlider" class="carousel slide" data-bs-ride="carousel" data-bs-interval="2000">
                        <div class="carousel-indicators">
                            <?php for($i = 0; $i < $top_novels->num_rows; $i++): ?>
                                <button type="button" data-bs-target="#heroSlider" 
                                        data-bs-slide-to="<?= $i ?>" 
                                        class="<?= $i === 0 ? 'active' : '' ?>"></button>
                            <?php endfor; ?>
                        </div>
                        
                        <div class="carousel-inner rounded shadow">
                            <?php $first = true; while($novel = $top_novels->fetch_assoc()): ?>
                                <div class="carousel-item <?= $first ? 'active' : '' ?>">
                                    <img src="<?= BASE_URL . '/' . ($novel['cover_image'] ?: 'images/covers/default-cover.jpg') ?>" 
                                         class="d-block w-100" style="height: 500px; object-fit: cover;" 
                                         alt="<?= htmlspecialchars($novel['title']) ?>">
                                    <div class="carousel-caption" style="background: rgba(0,0,0,0.7); border-radius: 10px;">
                                        <h5><?= htmlspecialchars($novel['title']) ?></h5>
                                        <p class="mb-0">
                                            <i class="fas fa-heart text-danger"></i> 
                                            <?= number_format($novel['favorite_count']) ?> lượt yêu thích
                                        </p>
                                        <a href="<?= BASE_URL ?>/app/Controllers/User/Novel_controller.php?id=<?= $novel['novel_id'] ?>" 
                                           class="btn btn-primary btn-sm mt-2">Xem chi tiết</a>
                                    </div>
                                </div>
                            <?php $first = false; endwhile; ?>
                        </div>
                        
                        <button class="carousel-control-prev" type="button" data-bs-target="#heroSlider" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon"></span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#heroSlider" data-bs-slide="next">
                            <span class="carousel-control-next-icon"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container py-5">
        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-9">
                <!-- Thể loại phổ biến -->
                <section class="mb-5">
                    <h2 class="mb-4">Danh mục phổ biến</h2>
                    <div class="row g-3">
                        <?php while ($category = $popular_categories->fetch_assoc()): ?>
                            <div class="col-md-3 col-6">
                                <a href="<?= BASE_URL ?>/app/Controllers/User/Categories_controller.php?id=<?= $category['category_id'] ?>" 
                                   class="card text-decoration-none text-dark">
                                    <div class="card-body">
                                        <h5 class="card-title mb-1"><?= htmlspecialchars($category['name']) ?></h5>
                                        <small class="text-muted"><?= $category['novel_count'] ?> sách</small>
                                    </div>
                                </a>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </section>

                <!-- Sách nổi bật -->
                <section class="mb-5">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="mb-0">Sách nổi bật</h2>
                    </div>
                    <div class="row g-4">
                        <?php while ($novel = $featured_novels->fetch_assoc()): ?>
                            <?= renderNovelCard($novel) ?>
                        <?php endwhile; ?>
                    </div>
                </section>

                <!-- Sách mới cập nhật -->
                <section>
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="mb-0">Sách mới cập nhật</h2>
                        <a href="<?= BASE_URL ?>/app/Controllers/User/Categories_controller.php" class="btn btn-outline-primary">Xem tất cả</a>
                    </div>
                    <div class="row g-4">
                        <?php while ($novel = $novels->fetch_assoc()): ?>
                            <?= renderNovelCard($novel) ?>
                        <?php endwhile; ?>
                    </div>

                    <!-- Phân trang -->
                    <?php if ($total_pages > 1): ?>
                        <nav class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                        <a class="page-link" 
                                           href="?type=<?= $type ?>&category=<?= $category ?>&page=<?= $i ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </section>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-3">
                <!-- Recent Comments -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Bình luận gần đây</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php
                        $recent_comments_query = "SELECT c.*, u.username, u.avatar_url, n.title as novel_title, n.novel_id 
                                               FROM Comments c 
                                               JOIN Users u ON c.user_id = u.user_id 
                                               JOIN LightNovels n ON c.novel_id = n.novel_id 
                                               ORDER BY c.created_at DESC LIMIT 5";
                        $recent_comments = $conn->query($recent_comments_query);
                        while ($comment = $recent_comments->fetch_assoc()):
                        ?>
                            <a href="<?= BASE_URL ?>/app/Controllers/User/Novel_controller.php?id=<?= $comment['novel_id'] ?>" class="text-decoration-none">
                                <div class="d-flex p-3 border-bottom hover-bg-light">
                                    <img src="<?= BASE_URL . ($comment['avatar_url'] ?: '/images/Avatar.jpg') ?>" 
                                         class="rounded-circle me-2" width="32" height="32" alt="Avatar">
                                    <div>
                                        <div class="text-dark"><strong><?= htmlspecialchars($comment['username']) ?></strong></div>
                                        <div class="text-muted small"><?= htmlspecialchars(truncateText($comment['content'], 50)) ?></div>
                                        <div class="text-primary small"><?= htmlspecialchars($comment['novel_title']) ?></div>
                                        <small class="text-muted"><?= timeAgo($comment['created_at']) ?></small>
                                    </div>
                                </div>
                            </a>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/main.js"></script>
</body>
</html>
