<?php
session_start();
require_once '../../../app/config/config.php';
require_once '../../../app/Models/User/Login_model.php';

// Nếu đã đăng nhập thì chuyển về trang chủ
if (isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/index.php");
    exit();
}

class Login_controller {
    private $model;
    
    public function __construct($connection) {
        $this->model = new Login_model($connection);
    }
    
    public function index() {
        $login_error = null;
        
        // Xử lý đăng nhập khi form được submit
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $login_error = $this->processLogin();
        }
        
        // Load view
        include '../../../app/views/User/Login_view.php';
    }
    
    private function processLogin() {
        $username = trim($_POST["username"]);
        $password = $_POST["password"];
        
        $user = $this->model->authenticateUser($username, $password);
        
        if ($user) {
            // Đăng nhập thành công
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['avatar_url'] = $user['avatar_url'];
            
            // Chuyển hướng dựa vào role
            if ($user['role'] === 'admin') {
                header("Location: " . BASE_URL . "/app/Controllers/Admin/Admin_dashboard_controller.php");
            } else {
                header("Location: " . BASE_URL . "/index.php");
            }
            exit();
        } else {
            return "Tên đăng nhập hoặc mật khẩu không chính xác";
        }
    }
}

// Khởi tạo controller và chạy
$controller = new Login_controller($conn);
$controller->index();
?>
