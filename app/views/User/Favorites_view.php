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
    <?php include '../../../includes/navbar.php'; ?>

    <div class="container py-5">
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= $_SESSION['success_message'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <h2 class="mb-4">
            <i class="fas fa-heart text-danger"></i> Truyện yêu thích
        </h2>

        <?php if ($data['favorites']->num_rows > 0): ?>
            <div class="row g-4">
                <?php while ($novel = $data['favorites']->fetch_assoc()): ?>
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

                                <?php if ($novel['last_read_chapter'] && $novel['latest_chapter']): ?>
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
                                        <a href="../../../app/Controllers/User/Chapter_controller.php?id=<?= $novel['last_read_chapter'] ?>" 
                                           class="btn btn-primary">
                                            Đọc tiếp
                                        </a>
                                    <?php else: ?>
                                        <a href="../../../app/Controllers/User/Novel_controller.php?id=<?= $novel['novel_id'] ?>" 
                                           class="btn btn-primary">
                                            Đọc ngay
                                        </a>
                                    <?php endif; ?>
                                    
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="novel_id" value="<?= $novel['novel_id'] ?>">
                                        <button type="submit" name="remove_favorite" class="btn btn-outline-danger w-100"
                                                onclick="return confirm('Bạn có chắc muốn bỏ truyện này khỏi danh sách yêu thích?')">
                                            <i class="fas fa-heart-broken"></i> Bỏ yêu thích
                                        </button>
                                    </form>
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
                <a href="../../../index.php" class="btn btn-primary">
                    <i class="fas fa-book"></i> Khám phá truyện
                </a>
            </div>
        <?php endif; ?>
    </div>

    <?php include '../../../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
