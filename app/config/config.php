<?php
// Thông tin kết nối database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'webbansach');

// Định nghĩa BASE_URL
define('BASE_URL', '/Btl');
define('BASE_PATH', __DIR__ . '/../..');

/*
Database structure changes:
- LightNovels -> Books
- Novel_Categories -> Book_Categories
- Chapters -> Book_Chapters
- Reading_History -> Reading_History (unchanged)
- Favorites -> Favorites (unchanged but references books)
- Purchases -> Purchases (unchanged but references books)
*/

// Tạo kết nối
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Đặt charset là utf8mb4
$conn->set_charset("utf8mb4");

// Cấu hình múi giờ
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Hàm xử lý lỗi chung
function handleError($message) {
    // Log lỗi
    error_log($message);
    
    // Trả về JSON nếu là AJAX request
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['error' => $message]);
        exit;
    }
    
    // Hiển thị trang lỗi
    include 'error.php';
    exit;
}

// Hàm kiểm tra đăng nhập
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Hàm kiểm tra quyền admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Hàm format tiền tệ
function formatPrice($price) {
    return number_format($price, 0, ',', '.') . 'đ';
}

// Hàm tạo URL thân thiện
function slugify($text) {
    // Chuyển đổi sang chữ thường
    $text = mb_strtolower($text);
    
    // Thay thế ký tự đặc biệt
    $text = preg_replace('/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/', 'a', $text);
    $text = preg_replace('/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/', 'e', $text);
    $text = preg_replace('/(ì|í|ị|ỉ|ĩ)/', 'i', $text);
    $text = preg_replace('/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/', 'o', $text);
    $text = preg_replace('/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/', 'u', $text);
    $text = preg_replace('/(ỳ|ý|ỵ|ỷ|ỹ)/', 'y', $text);
    $text = preg_replace('/(đ)/', 'd', $text);
    
    // Xóa ký tự đặc biệt
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    
    // Thay thế khoảng trắng bằng dấu gạch ngang
    $text = preg_replace('/[\s-]+/', '-', $text);
    
    // Xóa gạch ngang ở đầu và cuối
    $text = trim($text, '-');
    
    return $text;
} 