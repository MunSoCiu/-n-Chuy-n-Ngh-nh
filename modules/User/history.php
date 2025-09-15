<?php
session_start();
require_once '../../app/config/config.php';
require_once '../../includes/functions.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../modules/Login/login.php");
    exit();
}

// Lấy lịch sử đọc của user với phân trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

// Lấy tổng số bản ghi
$count_query = "SELECT COUNT(*) as total FROM Reading_History WHERE user_id = ?";
$stmt = $conn->prepare($count_query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$total = $stmt->get_result()->fetch_assoc()['total'];

// Lấy lịch sử đọc chi tiết
$query = "SELECT rh.*, n.title as novel_title, n.cover_image, n.author,
          c.title as chapter_title, c.chapter_id,
          (SELECT COUNT(*) FROM Chapters WHERE novel_id = n.novel_id) as total_chapters
          FROM Reading_History rh
          JOIN LightNovels n ON rh.novel_id = n.novel_id
          JOIN Chapters c ON rh.chapter_id = c.chapter_id
          WHERE rh.user_id = ?
          ORDER BY rh.last_read DESC
          LIMIT ? OFFSET ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("iii", $_SESSION['user_id'], $limit, $offset);
$stmt->execute();
$history = $stmt->get_result();
?>

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
    <?php include '../../includes/navbar.php'; ?>

    <div class="container py-5">
        <h2 class="mb-4">Lịch sử đọc</h2>

        <?php if ($history->num_rows > 0): ?>
            <div class="row g-4">
                <?php while ($item = $history->fetch_assoc()): ?>
                    <div class="col-md-6">
                        <div class="card history-item">
                            <div class="card-body">
                                <div class="d-flex gap-3">
                                    <img src="<?= BASE_URL . '/' . ($item['cover_image'] ?: 'images/covers/default-cover.jpg') ?>"
                                         class="novel-cover" alt="<?= htmlspecialchars($item['novel_title']) ?>">
                                    <div class="flex-grow-1">
                                        <h5 class="card-title mb-1">
                                            <a href="../Novel/novel.php?id=<?= $item['novel_id'] ?>" 
                                               class="text-decoration-none">
                                                <?= htmlspecialchars($item['novel_title']) ?>
                                            </a>
                                        </h5>
                                        <p class="text-muted mb-2">
                                            <small><?= htmlspecialchars($item['author']) ?></small>
                                        </p>
                                        <p class="mb-2">
                                            Đang đọc: 
                                            <a href="../Novel/chapter.php?id=<?= $item['chapter_id'] ?>"
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
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <!-- Phân trang -->
            <?php if ($total > $limit): ?>
                <div class="mt-4">
                    <?= pagination($total, $page, $limit, "history.php?page=%d") ?>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-book-open fa-3x mb-3 text-muted"></i>
                <h3>Chưa có lịch sử đọc</h3>
                <p class="text-muted">Hãy bắt đầu đọc truyện để ghi lại lịch sử.</p>
                <a href="../../index.php" class="btn btn-primary">
                    <i class="fas fa-home me-2"></i>Khám phá truyện
                </a>
            </div>
        <?php endif; ?>
    </div>

    <?php include '../../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 