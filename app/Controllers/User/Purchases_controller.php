<?php
session_start();
require_once '../../../app/config/config.php';
require_once '../../../includes/functions.php';
require_once '../../../app/Models/User/Purchases_model.php';

class Purchases_controller {
    private $model;
    
    public function __construct($connection) {
        $this->model = new Purchases_model($connection);
    }
    
    public function index() {
        // Kiểm tra đăng nhập
        if (!isset($_SESSION['user_id'])) {
            header("Location: ../../../app/Controllers/User/Login_controller.php");
            exit();
        }
        
        $user_id = $_SESSION['user_id'];
        
        // Lấy dữ liệu
        $data = [
            'purchases' => $this->model->getPurchases($user_id),
            'total_spent' => $this->model->getTotalSpent($user_id),
            'total_purchases' => $this->model->getTotalPurchases($user_id)
        ];
        
        // Load view
        include '../../../app/views/User/Purchases_view.php';
    }
}

// Khởi tạo controller và chạy
$controller = new Purchases_controller($conn);
$controller->index();
?>
