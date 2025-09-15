<?php
session_start();
require_once '../../../app/config/config.php';
require_once '../../../includes/functions.php';
require_once '../../../app/Models/User/Novel_model.php';

class Novel_controller {
    private $model;
    
    public function __construct($connection) {
        $this->model = new Novel_model($connection);
    }
    
    public function index() {
        // Lấy novel_id từ URL
        $novel_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        // Lấy thông tin chi tiết của novel
        $novel = $this->model->getNovelById($novel_id);
        
        if (!$novel) {
            header("Location: ../../../index.php");
            exit();
        }
        
        // Kiểm tra quyền đọc truyện
        $can_read = true;
        $has_purchased = false;
        if ($novel['price'] > 0) {
            $can_read = false;
            if (isset($_SESSION['user_id'])) {
                $has_purchased = $this->model->checkPurchase($_SESSION['user_id'], $novel_id);
                $can_read = $has_purchased || (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');
            }
        }
        
        
        // Xử lý yêu thích
        if (isset($_POST['toggle_favorite']) && isset($_SESSION['user_id'])) {
            $this->toggleFavorite($novel_id);
            exit();
        }
        
        // Xử lý thêm chapter mới (admin only)
        if (isset($_POST['add_chapter']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
            $this->addChapter($novel_id);
        }
        
        // Xử lý thêm comment
        if (isset($_POST['add_comment']) && isset($_SESSION['user_id'])) {
            $this->addComment($novel_id);
        }
        
        // Lấy dữ liệu cho view
        $data = $this->getNovelData($novel_id, $novel, $can_read, $has_purchased);
        
        // Load view
        include '../../../app/views/User/Novel_view.php';
    }
    
    
    private function toggleFavorite($novel_id) {
        $user_id = $_SESSION['user_id'];
        $is_favorited = $this->model->checkFavorite($user_id, $novel_id);
        $result = $this->model->toggleFavorite($user_id, $novel_id, $is_favorited);
        
        if ($result) {
            echo json_encode(['status' => $result]);
        }
    }
    
    private function addChapter($novel_id) {
        $title = trim($_POST['chapter_title']);
        $content = trim($_POST['chapter_content']);
        
        if (!empty($title) && !empty($content)) {
            if ($this->model->addChapter($novel_id, $title, $content)) {
                $_SESSION['success_message'] = "Thêm chapter mới thành công!";
                header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $novel_id);
                exit();
            }
        }
    }
    
    private function addComment($novel_id) {
        $content = trim($_POST['comment_content']);
        $user_id = $_SESSION['user_id'];
        
        if (!empty($content)) {
            if ($this->model->addComment($user_id, $novel_id, $content)) {
                $_SESSION['success_message'] = "Thêm bình luận thành công!";
                header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $novel_id);
                exit();
            } else {
                $_SESSION['error_message'] = "Có lỗi xảy ra khi thêm bình luận!";
            }
        } else {
            $_SESSION['error_message'] = "Nội dung bình luận không được để trống!";
        }
    }
    
    private function getNovelData($novel_id, $novel, $can_read, $has_purchased) {
        $data = [
            'novel' => $novel,
            'can_read' => $can_read,
            'has_purchased' => $has_purchased,
            'chapters' => $this->model->getChapters($novel_id),
            'is_favorited' => false,
            'favorite_count' => $this->model->getFavoriteCount($novel_id),
            'read_count' => $this->model->getReadCount($novel_id),
            'comments' => $this->model->getComments($novel_id),
            'comment_count' => $this->model->getCommentCount($novel_id)
        ];
        
        if (isset($_SESSION['user_id'])) {
            $data['is_favorited'] = $this->model->checkFavorite($_SESSION['user_id'], $novel_id);
        }
        
        return $data;
    }
}

// Khởi tạo controller và chạy
$controller = new Novel_controller($conn);
$controller->index();
?>
