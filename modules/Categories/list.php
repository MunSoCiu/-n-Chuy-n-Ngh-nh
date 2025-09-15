<?php
session_start();

require_once '../../app/config/config.php';
require_once '../../includes/functions.php';
require_once '../../includes/novel_card.php';

// Lấy thể loại được chọn (nếu có)
$selected_category = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

try {
    // Lấy thông tin thể loại nếu có id
    $category_info = null;
    if ($selected_category) {
        $cat_query = "SELECT * FROM Categories WHERE category_id = ?";
        $stmt = $conn->prepare($cat_query);
        $stmt->bind_param("i", $selected_category);
        $stmt->execute();
        $category_info = $stmt->get_result()->fetch_assoc();
        
        if (!$category_info) {
            header("Location: list.php");
            exit();
        }
    }

    // Query lấy danh sách truyện
    $query = "SELECT DISTINCT n.*, 
              GROUP_CONCAT(DISTINCT c2.name) as categories,
              COUNT(DISTINCT f.user_id) as favorite_count,
              COUNT(DISTINCT rh.user_id) as read_count,
              COUNT(DISTINCT p.purchase_id) as sold_count
              FROM LightNovels n
              LEFT JOIN Novel_Categories nc ON n.novel_id = nc.novel_id
              LEFT JOIN Categories c2 ON nc.category_id = c2.category_id
              LEFT JOIN Favorites f ON n.novel_id = f.novel_id
              LEFT JOIN Reading_History rh ON n.novel_id = rh.novel_id
              LEFT JOIN Purchases p ON n.novel_id = p.novel_id AND p.status = 'completed'";

    if ($selected_category) {
        $query .= " JOIN Novel_Categories nc2 ON n.novel_id = nc2.novel_id 
                   WHERE nc2.category_id = ?";
    }
    
    if ($search) {
        $query .= $selected_category ? " AND" : " WHERE";
        $query .= " (n.title LIKE ? OR n.author LIKE ?)";
    }
    
    $query .= " GROUP BY n.novel_id ORDER BY n.title";
    $query .= " LIMIT ? OFFSET ?";

    // Chuẩn bị và thực thi query
    $types = "";
    $params = array();
    
    if ($selected_category) {
        $types .= "i";
        $params[] = $selected_category;
    }
    
    if ($search) {
        $search_param = "%$search%";
        $types .= "ss";
        $params[] = $search_param;
        $params[] = $search_param;
    }
    
    $types .= "ii";
    $params[] = $limit;
    $params[] = $offset;

    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $novels = $stmt->get_result();

    // Lấy tổng số truyện
    $count_query = "SELECT COUNT(DISTINCT n.novel_id) as total 
                    FROM LightNovels n";
    
    if ($selected_category) {
        $count_query .= " JOIN Novel_Categories nc ON n.novel_id = nc.novel_id 
                         WHERE nc.category_id = ?";
    }
    
    if ($search) {
        $count_query .= $selected_category ? " AND" : " WHERE";
        $count_query .= " (n.title LIKE ? OR n.author LIKE ?)";
    }

    $stmt = $conn->prepare($count_query);
    if ($selected_category) {
        if ($search) {
            $stmt->bind_param("iss", $selected_category, $search_param, $search_param);
        } else {
            $stmt->bind_param("i", $selected_category);
        }
    } elseif ($search) {
        $stmt->bind_param("ss", $search_param, $search_param);
    }
    $stmt->execute();
    $total_novels = $stmt->get_result()->fetch_assoc()['total'];
    $total_pages = ceil($total_novels / $limit);

    // Lấy danh sách tất cả thể loại
    $categories = $conn->query("SELECT c.*, COUNT(nc.novel_id) as novel_count 
                               FROM Categories c
                               LEFT JOIN Novel_Categories nc ON c.category_id = nc.category_id
                               GROUP BY c.category_id
                               ORDER BY c.name");
} catch (Exception $e) {
    $_SESSION['error'] = "Có lỗi xảy ra: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $category_info ? htmlspecialchars($category_info['name']) : 'Tất cả thể loại' ?> - Light Novel Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .category-card {
            transition: transform 0.2s;
        }
        .category-card:hover {
            transform: translateY(-5px);
        }
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
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="<?= BASE_URL ?>/index.php">Trang chủ</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="list.php">Thể loại</a>
                </li>
                <?php if ($category_info): ?>
                    <li class="breadcrumb-item active">
                        <?= htmlspecialchars($category_info['name']) ?>
                    </li>
                <?php endif; ?>
            </ol>
        </nav>

        <div class="row">
            <!-- Sidebar thể loại -->
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-tags me-2"></i>Thể loại
                        </h5>
                    </div>
                    <div class="list-group list-group-flush">
                        <?php while ($category = $categories->fetch_assoc()): ?>
                            <a href="?id=<?= $category['category_id'] ?>" 
                               class="list-group-item list-group-item-action d-flex justify-content-between align-items-center
                                      <?= $selected_category === $category['category_id'] ? 'active' : '' ?>">
                                <?= htmlspecialchars($category['name']) ?>
                                <span class="badge bg-secondary rounded-pill">
                                    <?= $category['novel_count'] ?>
                                </span>
                            </a>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>

            <!-- Danh sách truyện -->
            <div class="col-md-9">
                <!-- Form tìm kiếm -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <?php if ($selected_category): ?>
                                <input type="hidden" name="id" value="<?= $selected_category ?>">
                            <?php endif; ?>
                            
                            <div class="col-md-8">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="search" 
                                           placeholder="Tìm kiếm theo tên truyện hoặc tác giả..." 
                                           value="<?= htmlspecialchars($search) ?>">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="fas fa-search me-1"></i> Tìm kiếm
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <?php if ($novels && $novels->num_rows > 0): ?>
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
                                           href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-book fa-3x mb-3 text-muted"></i>
                        <h3>Không tìm thấy truyện nào</h3>
                        <p class="text-muted">Thử tìm kiếm với từ khóa khác hoặc chọn thể loại khác.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include '../../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>