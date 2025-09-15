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
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
    </style>
</head>
<body>
    <?php include '../../../includes/navbar.php'; ?>

    <div class="container py-5">
        <h2 class="mb-4">
            <i class="fas fa-shopping-cart text-primary"></i> Truyện đã mua
        </h2>

        <!-- Thống kê -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <h3 class="mb-1"><?= $data['total_purchases'] ?></h3>
                        <p class="mb-0">Truyện đã mua</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <h3 class="mb-1"><?= formatPrice($data['total_spent']) ?></h3>
                        <p class="mb-0">Tổng chi tiêu</p>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($data['purchases']->num_rows > 0): ?>
            <div class="row g-4">
                <?php while ($novel = $data['purchases']->fetch_assoc()): ?>
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

                                <a href="../../../app/Controllers/User/Novel_controller.php?id=<?= $novel['novel_id'] ?>" 
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
                <a href="../../../app/Controllers/User/Novel_controller.php" class="btn btn-primary">
                    <i class="fas fa-book-open me-1"></i> Khám phá truyện
                </a>
            </div>
        <?php endif; ?>
    </div>

    <?php include '../../../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
