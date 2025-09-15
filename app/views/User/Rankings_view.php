<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $data['title'] ?> - Light Novel Hub</title>
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
    <?php include '../../../includes/navbar.php'; ?>

    <div class="container py-5">
        <!-- Tabs for different rankings -->
        <ul class="nav nav-pills mb-4">
            <li class="nav-item">
                <a class="nav-link <?= $data['type'] === 'favorite' ? 'active' : '' ?>" 
                   href="?type=favorite">
                    <i class="fas fa-heart me-1"></i> Yêu thích nhiều nhất
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $data['type'] === 'reading' ? 'active' : '' ?>" 
                   href="?type=reading">
                    <i class="fas fa-book-reader me-1"></i> Đọc nhiều nhất
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $data['type'] === 'bestseller' ? 'active' : '' ?>" 
                   href="?type=bestseller">
                    <i class="fas fa-shopping-cart me-1"></i> Bán chạy nhất
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $data['type'] === 'new' ? 'active' : '' ?>" 
                   href="?type=new">
                    <i class="fas fa-clock me-1"></i> Mới cập nhật
                </a>
            </li>
        </ul>

        <h2 class="mb-4"><?= $data['title'] ?></h2>
        
        <div class="row g-4">
            <?php $rank = 1; ?>
            <?php while ($novel = $data['novels']->fetch_assoc()): ?>
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
                                        if ($novel['categories']) {
                                            $categories = explode(',', $novel['categories']);
                                            foreach ($categories as $category) {
                                                if ($category) {
                                                    echo "<span class='badge bg-secondary me-1'>".htmlspecialchars($category)."</span>";
                                                }
                                            }
                                        }
                                        ?>
                                    </p>
                                    <p class="card-text">
                                        <i class="fas fa-<?= $data['icon'] ?> text-<?= $data['color'] ?>"></i> 
                                        <?php if ($data['type'] === 'new'): ?>
                                            Cập nhật: <?= timeAgo($novel[$data['count_field']]) ?>
                                        <?php else: ?>
                                            <?= number_format($novel[$data['count_field']]) ?> <?= $data['count_label'] ?>
                                        <?php endif; ?>
                                    </p>
                                    <?php if ($data['type'] === 'bestseller'): ?>
                                        <p class="card-text">
                                            <span class="badge bg-success me-2">
                                                <i class="fas fa-shopping-cart"></i> 
                                                <?= number_format($novel[$data['count_field']]) ?> <?= $data['count_label'] ?>
                                            </span>
                                        </p>
                                    <?php endif; ?>
                                    <a href="<?= BASE_URL ?>/app/Controllers/User/Novel_controller.php?id=<?= $novel['novel_id'] ?>" 
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

    <?php include '../../../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
