<?php
session_start();
require_once '../../app/config/config.php';
require_once '../../includes/functions.php';
require_once '../../includes/novel_card.php';

// Lấy các tham số lọc
$type = isset($_GET['type']) ? $_GET['type'] : 'all';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'latest';
$category = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 8;
$offset = ($page - 1) * $limit;

// Xây dựng query
$query = "SELECT n.*,
          GROUP_CONCAT(DISTINCT c.name) as categories,
          COUNT(DISTINCT f.user_id) as favorite_count,
          COUNT(DISTINCT rh.user_id) as read_count,
          COUNT(DISTINCT pu.purchase_id) as sold_count
          FROM LightNovels n
          LEFT JOIN Novel_Categories nc ON n.novel_id = nc.novel_id
          LEFT JOIN Categories c ON nc.category_id = c.category_id
          LEFT JOIN Favorites f ON n.novel_id = f.novel_id
          LEFT JOIN Reading_History rh ON n.novel_id = rh.novel_id
          LEFT JOIN Purchases pu ON n.novel_id = pu.novel_id
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
switch ($sort) {
    case 'favorite':
        $query .= " ORDER BY favorite_count DESC";
        break;
    case 'reading':
        $query .= " ORDER BY read_count DESC";
        break;
    case 'price_asc':
        $query .= " ORDER BY n.price ASC";
        break;
    case 'price_desc':
        $query .= " ORDER BY n.price DESC";
        break;
    default:
        $query .= " ORDER BY n.created_at DESC";
}

// Thêm phân trang
$query .= " LIMIT ? OFFSET ?";

// Thay đổi phần đếm tổng số truyện
$count_query = "SELECT COUNT(DISTINCT n.novel_id) as total 
                FROM LightNovels n
                LEFT JOIN Novel_Categories nc ON n.novel_id = nc.novel_id
                WHERE 1=1";

// Thêm điều kiện lọc theo loại
if ($type === 'free') {
    $count_query .= " AND n.price = 0";
} elseif ($type === 'paid') {
    $count_query .= " AND n.price > 0";
}

// Thêm điều kiện lọc theo thể loại
if ($category > 0) {
    $count_query .= " AND nc.category_id = " . $category;
}

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
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách truyện - Light Novel Hub</title>
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
        .filter-section {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>

    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>
                <?php if ($type === 'free'): ?>
                    <i class="fas fa-gift text-success"></i> Truyện miễn phí
                <?php elseif ($type === 'paid'): ?>
                    <i class="fas fa-tags text-primary"></i> Truyện trả phí
                <?php else: ?>
                    <i class="fas fa-book"></i> Tất cả truyện
                <?php endif; ?>
            </h2>
        </div>

        <!-- Bộ lọc -->
        <div class="filter-section">
            <form method="GET" class="row g-3">
                <input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>">
                
                <div class="col-md-4">
                    <label class="form-label">Thể loại</label>
                    <select class="form-select" name="category">
                        <option value="0">Tất cả thể loại</option>
                        <?php while ($cat = $categories->fetch_assoc()): ?>
                            <option value="<?= $cat['category_id'] ?>" 
                                    <?= $category == $cat['category_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Sắp xếp theo</label>
                    <select class="form-select" name="sort">
                        <option value="latest" <?= $sort === 'latest' ? 'selected' : '' ?>>Mới nhất</option>
                        <option value="favorite" <?= $sort === 'favorite' ? 'selected' : '' ?>>Yêu thích nhiều nhất</option>
                        <option value="reading" <?= $sort === 'reading' ? 'selected' : '' ?>>Đọc nhiều nhất</option>
                        <?php if ($type !== 'free'): ?>
                            <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Giá tăng dần</option>
                            <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Giá giảm dần</option>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-1"></i> Lọc kết quả
                    </button>
                </div>
            </form>
        </div>

        <?php if ($novels->num_rows > 0): ?>
            <div class="row g-4">
                <?php while ($novel = $novels->fetch_assoc()): ?>
                    <?= renderNovelCard($novel) ?>
                <?php endwhile; ?>
            </div>

            <!-- Phân trang -->
            <?php if ($total_pages > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?type=<?= $type ?>&sort=<?= $sort ?>&category=<?= $category ?>&page=<?= $page-1 ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                <a class="page-link" href="?type=<?= $type ?>&sort=<?= $sort ?>&category=<?= $category ?>&page=<?= $i ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?type=<?= $type ?>&sort=<?= $sort ?>&category=<?= $category ?>&page=<?= $page+1 ?>">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-book fa-3x mb-3 text-muted"></i>
                <h3>Không tìm thấy truyện</h3>
                <p class="text-muted">Không có truyện nào phù hợp với tiêu chí tìm kiếm.</p>
                <a href="list.php" class="btn btn-primary">
                    <i class="fas fa-sync-alt me-1"></i> Xem tất cả truyện
                </a>
            </div>
        <?php endif; ?>
    </div>

    <?php include '../../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 