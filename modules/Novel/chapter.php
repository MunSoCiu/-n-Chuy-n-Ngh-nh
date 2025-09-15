<?php
session_start();
require_once '../../app/config/config.php';
require_once '../../includes/functions.php';

// Lấy chapter_id từ URL
$chapter_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Lấy thông tin chapter và novel
$query = "SELECT c.*, n.title as novel_title, n.novel_id,
          (SELECT chapter_id FROM Chapters 
           WHERE novel_id = c.novel_id AND chapter_id < c.chapter_id 
           ORDER BY chapter_id DESC LIMIT 1) as prev_chapter,
          (SELECT chapter_id FROM Chapters 
           WHERE novel_id = c.novel_id AND chapter_id > c.chapter_id 
           ORDER BY chapter_id ASC LIMIT 1) as next_chapter
          FROM Chapters c
          JOIN LightNovels n ON c.novel_id = n.novel_id
          WHERE c.chapter_id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $chapter_id);
$stmt->execute();
$chapter = $stmt->get_result()->fetch_assoc();

if (!$chapter) {
    header("Location: index.php");
    exit();
}

// Cập nhật lịch sử đọc nếu đã đăng nhập
if (isset($_SESSION['user_id'])) {
    $query = "INSERT INTO Reading_History (user_id, novel_id, chapter_id) 
              VALUES (?, ?, ?) 
              ON DUPLICATE KEY UPDATE chapter_id = ?, last_read = CURRENT_TIMESTAMP";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiii", $_SESSION['user_id'], $chapter['novel_id'], $chapter_id, $chapter_id);
    $stmt->execute();
}

// Thêm hàm xử lý nội dung chương truyện
function formatChapterContent($content) {
    // Loại bỏ các thẻ HTML
    $content = strip_tags($content);
    
    // Chuyển đổi các ký tự đặc biệt
    $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    
    // Xử lý các dấu xuống dòng
    $content = str_replace(["\r\n", "\r"], "\n", $content);
    
    // Xóa các khoảng trắng thừa
    $content = preg_replace('/\n\s*\n/', "\n\n", $content);
    
    // Xóa khoảng trắng ở đầu và cuối
    $content = trim($content);
    
    return $content;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($chapter['title']) ?> - <?= htmlspecialchars($chapter['novel_title']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .chapter-content {
            font-size: 1.1rem;
            line-height: 1.8;
            max-width: 800px;
            margin: 0 auto;
        }
        .chapter-nav {
            position: sticky;
            top: 0;
            background: white;
            z-index: 100;
            padding: 1rem 0;
            border-bottom: 1px solid #eee;
        }
        @media (prefers-color-scheme: dark) {
            body.dark-mode {
                background-color: #1a1a1a;
                color: #fff;
            }
            body.dark-mode .chapter-nav {
                background-color: #1a1a1a;
                border-bottom-color: #333;
            }
        }
    </style>
</head>
<body>
    <div class="chapter-nav">
        <div class="container">
            <div class="row align-items-center">
                <div class="col">
                    <a href="novel.php?id=<?= $chapter['novel_id'] ?>" class="text-decoration-none">
                        <i class="fas fa-book"></i>
                        <?= htmlspecialchars($chapter['novel_title']) ?>
                    </a>
                </div>
                <div class="col-auto">
                    <div class="btn-group">
                        <button class="btn btn-outline-secondary" onclick="toggleDarkMode()">
                            <i class="fas fa-adjust"></i>
                        </button>
                        <button class="btn btn-outline-secondary" onclick="adjustFontSize(-1)">A-</button>
                        <button class="btn btn-outline-secondary" onclick="adjustFontSize(1)">A+</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container py-4">
        <div class="text-center mb-4">
            <h1 class="h3"><?= htmlspecialchars($chapter['title']) ?></h1>
        </div>

        <div class="d-flex justify-content-center gap-2 mb-4">
            <?php if ($chapter['prev_chapter']): ?>
                <a href="?id=<?= $chapter['prev_chapter'] ?>" class="btn btn-primary">
                    <i class="fas fa-chevron-left"></i> Chương trước
                </a>
            <?php endif; ?>

            <?php if ($chapter['next_chapter']): ?>
                <a href="?id=<?= $chapter['next_chapter'] ?>" class="btn btn-primary">
                    Chương sau <i class="fas fa-chevron-right"></i>
                </a>
            <?php endif; ?>
        </div>

        <div class="chapter-content" id="chapter-content">
            <?= nl2br(htmlspecialchars(formatChapterContent($chapter['content']))) ?>
        </div>

        <div class="d-flex justify-content-center gap-2 my-4">
            <?php if ($chapter['prev_chapter']): ?>
                <a href="?id=<?= $chapter['prev_chapter'] ?>" class="btn btn-primary">
                    <i class="fas fa-chevron-left"></i> Chương trước
                </a>
            <?php endif; ?>

            <?php if ($chapter['next_chapter']): ?>
                <a href="?id=<?= $chapter['next_chapter'] ?>" class="btn btn-primary">
                    Chương sau <i class="fas fa-chevron-right"></i>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Lưu cài đặt người dùng
        const settings = JSON.parse(localStorage.getItem('reader-settings') || '{"fontSize": 18, "darkMode": false}');
        
        // Áp dụng cài đặt
        function applySettings() {
            document.getElementById('chapter-content').style.fontSize = settings.fontSize + 'px';
            document.body.classList.toggle('dark-mode', settings.darkMode);
            localStorage.setItem('reader-settings', JSON.stringify(settings));
        }

        // Điều chỉnh cỡ chữ
        function adjustFontSize(delta) {
            settings.fontSize = Math.max(12, Math.min(24, settings.fontSize + delta));
            applySettings();
        }

        // Chuyển đổi chế độ tối
        function toggleDarkMode() {
            settings.darkMode = !settings.darkMode;
            applySettings();
        }

        // Áp dụng cài đặt khi tải trang
        applySettings();

        // Lưu vị trí đọc
        window.addEventListener('scroll', () => {
            localStorage.setItem('scroll-position-' + <?= $chapter_id ?>, window.scrollY);
        });

        // Khôi phục vị trí đọc
        const savedPosition = localStorage.getItem('scroll-position-' + <?= $chapter_id ?>);
        if (savedPosition) {
            window.scrollTo(0, parseInt(savedPosition));
        }
    </script>
</body>
</html> 