<?php
session_start();
require_once '../../../app/config/config.php';
require_once '../../../includes/functions.php';
require_once '../../../app/Models/User/Promotions_model.php';

class Promotions_controller {
    private $model;
    
    public function __construct($connection) {
        $this->model = new Promotions_model($connection);
    }
    
    public function index() {
        // Kiểm tra đăng nhập
        if (!isset($_SESSION['user_id'])) {
            header("Location: ../../../app/Controllers/User/Login_controller.php");
            exit();
        }
        
        $success = '';
        
        // Xử lý gán mã giảm giá (admin only)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            if ($_POST['action'] === 'assign' && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
                $user_id = (int)$_POST['user_id'];
                $promo_id = (int)$_POST['promo_id'];
                
                if ($this->model->assignPromotion($user_id, $promo_id)) {
                    $success = "Đã gán mã giảm giá thành công!";
                }
            }
        }
        
        // Lấy dữ liệu
        $is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
        
        $data = [
            'promotions' => $this->model->getPromotions($is_admin),
            'user_promotions' => $is_admin ? null : $this->model->getUserPromotions($_SESSION['user_id']),
            'is_admin' => $is_admin,
            'success' => $success
        ];
        
        // Load view
        include '../../../app/views/User/Promotions_view.php';
    }
}

// Khởi tạo controller và chạy
$controller = new Promotions_controller($conn);
$controller->index();
?>
