<?php
session_start();
require_once '../../../app/config/config.php';
require_once '../../../includes/functions.php';
require_once '../../../app/Models/User/Purchase_model.php';

class Purchase_controller {
    private $model;
    
    public function __construct($connection) {
        $this->model = new Purchase_model($connection);
    }
    
    public function index() {
        // Kiểm tra đăng nhập
        if (!isset($_SESSION['user_id'])) {
            header("Location: ../../../app/Controllers/User/Login_controller.php");
            exit();
        }
        
        // Lấy novel_id từ URL
        $novel_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        // Kiểm tra novel có tồn tại không
        $novel = $this->model->getNovelById($novel_id);
        
        if (!$novel) {
            header("Location: ../../../index.php");
            exit();
        }
        
        // Kiểm tra xem người dùng đã mua truyện chưa
        if ($this->model->checkPurchase($_SESSION['user_id'], $novel_id)) {
            header("Location: ../../../app/Controllers/User/Novel_controller.php?id=" . $novel_id);
            exit();
        }
        
        $error_message = '';
        
        // Xử lý mua truyện
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user_id = $_SESSION['user_id'];
            $price = $novel['price'];
            
            if ($this->model->purchaseNovel($user_id, $novel_id, $price)) {
                $_SESSION['success_message'] = "Mua truyện thành công!";
                header("Location: ../../../app/Controllers/User/Novel_controller.php?id=" . $novel_id);
                exit();
            } else {
                $error_message = "Có lỗi xảy ra khi xử lý giao dịch. Vui lòng thử lại.";
            }
        }
        
        // Chuẩn bị dữ liệu cho view
        $data = [
            'novel' => $novel,
            'novel_id' => $novel_id,
            'error_message' => $error_message
        ];
        
        // Load view
        include '../../../app/views/User/Purchase_view.php';
    }
}

// Khởi tạo controller và chạy
$controller = new Purchase_controller($conn);
$controller->index();
?>
