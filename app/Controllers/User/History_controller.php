<?php
session_start();
require_once '../../../app/config/config.php';
require_once '../../../includes/functions.php';
require_once '../../../app/Models/User/History_model.php';

class History_controller {
    private $model;
    
    public function __construct($connection) {
        $this->model = new History_model($connection);
    }
    
    public function index() {
        // Kiểm tra đăng nhập
        if (!isset($_SESSION['user_id'])) {
            header("Location: ../../../app/Controllers/User/Login_controller.php");
            exit();
        }
        
        // Xử lý xóa lịch sử
        if (isset($_POST['delete_history'])) {
            $novel_id = (int)$_POST['novel_id'];
            if ($this->model->deleteHistory($_SESSION['user_id'], $novel_id)) {
                $_SESSION['success_message'] = "Đã xóa lịch sử đọc!";
            }
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
        
        // Lấy thông tin phân trang
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 12;
        $offset = ($page - 1) * $limit;
        
        // Lấy dữ liệu
        $total = $this->model->getTotalHistory($_SESSION['user_id']);
        $history = $this->model->getHistory($_SESSION['user_id'], $limit, $offset);
        
        $data = [
            'history' => $history,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($total / $limit)
        ];
        
        // Load view
        include '../../../app/views/User/History_view.php';
    }
}

// Khởi tạo controller và chạy
$controller = new History_controller($conn);
$controller->index();
?>
