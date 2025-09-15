<?php
session_start();
require_once '../../../app/config/config.php';
require_once '../../../includes/functions.php';
require_once '../../../app/Models/Admin/Admin_promotions_model.php';

class Admin_promotions_controller {
    private $model;
    
    public function __construct($connection) {
        $this->model = new Admin_promotions_model($connection);
    }
    
    public function index() {
        // Kiểm tra quyền admin
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header("Location: ../../../../index.php");
            exit();
        }
        
        $success = '';
        $error = '';
        
        // Xử lý thêm/sửa promotion
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_promotion'])) {
            try {
                $this->savePromotion();
                $success = "Đã lưu mã giảm giá thành công!";
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
        
        // Xử lý xóa promotion
        if (isset($_POST['delete_promotion'])) {
            $promo_id = (int)$_POST['delete_promotion'];
            if ($this->model->deletePromotion($promo_id)) {
                $success = "Đã xóa mã giảm giá thành công!";
            } else {
                $error = "Có lỗi xảy ra khi xóa mã giảm giá!";
            }
        }
        
        // Xử lý gán promotion cho user
        if (isset($_POST['assign_promotion'])) {
            $user_id = (int)$_POST['user_id'];
            $promo_id = (int)$_POST['promo_id'];
            
            if ($this->model->assignPromotionToUser($user_id, $promo_id)) {
                $success = "Đã gán mã giảm giá cho người dùng thành công!";
            } else {
                $error = "Có lỗi xảy ra khi gán mã giảm giá!";
            }
        }
        
        // Lấy dữ liệu
        $data = [
            'promotions' => $this->model->getAllPromotions(),
            'users' => $this->model->getAllUsers(),
            'success' => $success,
            'error' => $error
        ];
        
        // Load view
        include '../../../app/views/Admin/Admin_promotions_view.php';
    }
    
    private function savePromotion() {
        $promo_id = isset($_POST['promo_id']) ? (int)$_POST['promo_id'] : null;
        $code = trim($_POST['code']);
        $discount_percentage = (int)$_POST['discount_percentage'];
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        $description = trim($_POST['description']);
        
        // Validation
        if (empty($code)) {
            throw new Exception("Mã giảm giá không được để trống!");
        }
        
        if ($discount_percentage <= 0 || $discount_percentage > 100) {
            throw new Exception("Phần trăm giảm giá phải từ 1-100!");
        }
        
        if (strtotime($start_date) >= strtotime($end_date)) {
            throw new Exception("Ngày bắt đầu phải trước ngày kết thúc!");
        }
        
        // Kiểm tra trùng code
        if ($this->model->checkCodeExists($code, $promo_id)) {
            throw new Exception("Mã giảm giá đã tồn tại!");
        }
        
        if ($promo_id) {
            $this->model->updatePromotion($promo_id, $code, $discount_percentage, $start_date, $end_date, $description);
        } else {
            $this->model->addPromotion($code, $discount_percentage, $start_date, $end_date, $description);
        }
    }
}

// Khởi tạo controller và chạy
$controller = new Admin_promotions_controller($conn);
$controller->index();
?>
