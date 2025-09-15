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
    <?php include '../../../includes/navbar.php'; ?>

    <div class="container py-5">
        <h2 class="mb-4">
            <?php if ($data['search']): ?>
                Kết quả tìm kiếm cho "<?= htmlspecialchars($data['search']) ?>"
                <small class="text-muted">(<?= $data['total'] ?> kết quả)</small>
            <?php else: ?>
                Tìm kiếm truyện
            <?php endif; ?>
        </h2>

        <?php if ($data['search'] && $data['results'] && $data['results']->num_rows > 0): ?>
            <div class="row g-4">
                <?php while ($novel = $data['results']->fetch_assoc()): ?>
                    <div class="col-md-3">
                        <div class="card h-100">
                            <img src="<?= BASE_URL . '/' . htmlspecialchars($novel['cover_image'] ?: 'images/covers/default-cover.jpg') ?>" 
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
                                    if ($novel['categories']) {
                                        $categories = explode(',', $novel['categories']);
                                        foreach ($categories as $category) {
                                            if ($category) {
                                                echo "<span class='badge bg-secondary me-1'>".htmlspecialchars($category)."</span>";
                                            }
                                        }
                                    }
                                    ?>
                                </div>
                                <a href="../../../app/Controllers/User/Novel_controller.php?id=<?= $novel['novel_id'] ?>" 
                                   class="btn btn-primary w-100">
                                    Xem chi tiết
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <!-- Phân trang -->
            <?php if ($data['total_pages'] > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $data['total_pages']; $i++): ?>
                            <li class="page-item <?= $i === $data['page'] ? 'active' : '' ?>">
                                <a class="page-link" 
                                   href="?q=<?= urlencode($data['search']) ?>&page=<?= $i ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>

        <?php elseif ($data['search']): ?>
            <div class="text-center py-5">
                <i class="fas fa-book fa-3x mb-3 text-muted"></i>
                <h3>Không tìm thấy sách</h3>
                <p class="text-muted">Không có sách nào phù hợp với từ khóa tìm kiếm.</p>
            </div>
        <?php endif; ?>
    </div>

    <?php include '../../../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
