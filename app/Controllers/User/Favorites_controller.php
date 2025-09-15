<?php
session_start();
require_once '../../../app/config/config.php';
require_once '../../../includes/functions.php';
require_once '../../../app/Models/User/Favorites_model.php';

class Favorites_controller {
    private $model;
    
    public function __construct($connection) {
        $this->model = new Favorites_model($connection);
    }
    
    public function index() {
        // Kiểm tra đăng nhập
        if (!isset($_SESSION['user_id'])) {
            header("Location: ../../../app/Controllers/User/Login_controller.php");
            exit();
        }
        
        // Xử lý xóa yêu thích
        if (isset($_POST['remove_favorite'])) {
            $novel_id = (int)$_POST['novel_id'];
            if ($this->model->removeFavorite($_SESSION['user_id'], $novel_id)) {
                $_SESSION['success_message'] = "Đã xóa khỏi danh sách yêu thích!";
            }
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
        
        // Lấy danh sách truyện yêu thích
        $data = [
            'favorites' => $this->model->getFavorites($_SESSION['user_id'])
        ];
        
        // Load view
        include '../../../app/views/User/Favorites_view.php';
    }
}

// Khởi tạo controller và chạy
$controller = new Favorites_controller($conn);
$controller->index();
?>
