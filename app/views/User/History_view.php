<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch sử đọc - Light Novel Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .novel-cover {
            width: 100px;
            height: 150px;
            object-fit: cover;
        }
        .history-item {
            transition: transform 0.2s;
        }
        .history-item:hover {
            transform: translateY(-2px);
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

        <h2 class="mb-4">Lịch sử đọc</h2>

        <?php if ($data['history']->num_rows > 0): ?>
            <div class="row g-4">
                <?php while ($item = $data['history']->fetch_assoc()): ?>
                    <div class="col-md-6">
                        <div class="card history-item">
                            <div class="card-body">
                                <div class="d-flex gap-3">
                                    <img src="<?= BASE_URL . '/' . ($item['cover_image'] ?: 'images/covers/default-cover.jpg') ?>"
                                         class="novel-cover" alt="<?= htmlspecialchars($item['novel_title']) ?>">
                                    <div class="flex-grow-1">
                                        <h5 class="card-title mb-1">
                                            <a href="../../../app/Controllers/User/Novel_controller.php?id=<?= $item['novel_id'] ?>" 
                                               class="text-decoration-none">
                                                <?= htmlspecialchars($item['novel_title']) ?>
                                            </a>
                                        </h5>
                                        <p class="text-muted mb-2">
                                            <small><?= htmlspecialchars($item['author']) ?></small>
                                        </p>
                                        <p class="mb-2">
                                            Đang đọc: 
                                            <a href="../../../app/Controllers/User/Chapter_controller.php?id=<?= $item['chapter_id'] ?>"
                                               class="text-decoration-none">
                                                <?= htmlspecialchars($item['chapter_title']) ?>
                                            </a>
                                        </p>
                                        <div class="progress mb-2">
                                            <?php 
                                            $progress = ($item['chapter_id'] / $item['total_chapters']) * 100;
                                            ?>
                                            <div class="progress-bar" role="progressbar" 
                                                 style="width: <?= $progress ?>%"
                                                 aria-valuenow="<?= $progress ?>" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">
                                            </div>
                                        </div>
                                        <small class="text-muted">
                                            Đọc lần cuối: <?= timeAgo($item['last_read']) ?>
                                        </small>
                                        <div class="mt-2">
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="novel_id" value="<?= $item['novel_id'] ?>">
                                                <button type="submit" name="delete_history" class="btn btn-outline-danger btn-sm"
                                                        onclick="return confirm('Bạn có chắc muốn xóa lịch sử đọc này?')">
                                                    <i class="fas fa-trash"></i> Xóa
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
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
                                <a class="page-link" href="?page=<?= $i ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>

        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-book-open fa-3x mb-3 text-muted"></i>
                <h3>Chưa có lịch sử đọc</h3>
                <p class="text-muted">Hãy bắt đầu đọc truyện để ghi lại lịch sử.</p>
                <a href="../../../index.php" class="btn btn-primary">
                    <i class="fas fa-home me-2"></i>Khám phá truyện
                </a>
            </div>
        <?php endif; ?>
    </div>

    <?php include '../../../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
