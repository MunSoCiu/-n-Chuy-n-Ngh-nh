<?php
session_start();
require_once '../../../app/config/config.php';
require_once '../../../includes/functions.php';
require_once '../../../app/Models/User/Chapter_model.php';

class Chapter_controller {
    private $model;
    
    public function __construct($connection) {
        $this->model = new Chapter_model($connection);
    }
    
    public function index() {
        // Lấy chapter_id từ URL
        $chapter_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        // Lấy thông tin chapter và novel
        $chapter = $this->model->getChapterById($chapter_id);
        
        if (!$chapter) {
            header("Location: ../../../index.php");
            exit();
        }
        
        // Cập nhật lịch sử đọc nếu đã đăng nhập
        if (isset($_SESSION['user_id'])) {
            $this->model->updateReadingHistory($_SESSION['user_id'], $chapter['novel_id'], $chapter_id);
        }
        
        // Chuẩn bị dữ liệu cho view
        $data = [
            'chapter' => $chapter,
            'formatted_content' => $this->model->formatChapterContent($chapter['content'])
        ];
        
        // Load view
        include '../../../app/views/User/Chapter_view.php';
    }
}

// Khởi tạo controller và chạy
$controller = new Chapter_controller($conn);
$controller->index();
?>
