<?php
session_start();
require_once '../../config.php';
require_once '../../includes/functions.php';

$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

if ($search) {
    $query = "SELECT n.*, GROUP_CONCAT(c.name) as categories
              FROM LightNovels n
              LEFT JOIN Novel_Categories nc ON n.novel_id = nc.novel_id
              LEFT JOIN Categories c ON nc.category_id = c.category_id
              WHERE n.title LIKE ? OR n.author LIKE ?
              GROUP BY n.novel_id
              LIMIT ? OFFSET ?";
    
    $search_param = "%$search%";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssii", $search_param, $search_param, $limit, $offset);
    $stmt->execute();
    $results = $stmt->get_result();
    
    // Lấy tổng số kết quả
    $count_query = "SELECT COUNT(DISTINCT n.novel_id) as total 
                   FROM LightNovels n
                   WHERE n.title LIKE ? OR n.author LIKE ?";
    $stmt = $conn->prepare($count_query);
    $stmt->bind_param("ss", $search_param, $search_param);
    $stmt->execute();
    $total = $stmt->get_result()->fetch_assoc()['total'];
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tìm kiếm - Nhà Sách Minh An</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>

    <div class="container py-5">
        <h2 class="mb-4">
            <?php if ($search): ?>
                Kết quả tìm kiếm cho "<?= htmlspecialchars($search) ?>"
                <small class="text-muted">(<?= $total ?> kết quả)</small>
            <?php else: ?>
                Tìm kiếm truyện
            <?php endif; ?>
        </h2>

        <?php if ($search && $results->num_rows > 0): ?>
            <div class="row g-4">
                <?php while ($novel = $results->fetch_assoc()): ?>
                    <div class="col-md-3">
                        <div class="card h-100">
                            <img src="<?= htmlspecialchars($novel['cover_image'] ?: 'images/default-cover.jpg') ?>" 
                                 class="card-img-top" style="height: 250px; object-fit: cover;">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($novel['title']) ?></h5>
                                <p class="card-text">
                                    <small class="text-muted">
                                        Tác giả: <?= htmlspecialchars($novel['author']) ?>
                                    </small>
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
                                <a href="novel.php?id=<?= $novel['novel_id'] ?>" 
                                   class="btn btn-primary w-100">
                                    Xem chi tiết
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <?php 
            if ($total > $limit) {
                echo pagination($total, $page, $limit, "search.php?q=" . urlencode($search) . "&page=%d");
            }
            ?>

        <?php elseif ($search): ?>
            <div class="text-center py-5">
                <i class="fas fa-book fa-3x mb-3 text-muted"></i>
                <h3>Không tìm thấy sách</h3>
                <p class="text-muted">Không có sách nào phù hợp với từ khóa tìm kiếm.</p>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 