<?php
session_start();
require_once '../../../app/config/config.php';
require_once '../../../app/Models/User/Register_model.php';

// Nếu đã đăng nhập thì chuyển về trang chủ
if (isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/index.php");
    exit();
}

class Register_controller {
    private $model;
    
    public function __construct($connection) {
        $this->model = new Register_model($connection);
    }
    
    public function index() {
        $error = null;
        
        // Xử lý đăng ký khi form được submit
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $error = $this->processRegistration();
        }
        
        // Load view
        include '../../../app/views/User/Register_view.php';
    }
    
    private function processRegistration() {
        $username = trim($_POST["username"]);
        $email = trim($_POST["email"]);
        $password = $_POST["password"];
        $confirm_password = $_POST["confirm_password"];
        
        // Validation
        if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
            return "Vui lòng điền đầy đủ thông tin";
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return "Email không hợp lệ";
        }
        
        if ($password !== $confirm_password) {
            return "Mật khẩu xác nhận không khớp";
        }
        
        if (strlen($password) < 6) {
            return "Mật khẩu phải có ít nhất 6 ký tự";
        }
        
        if ($this->model->checkUsernameExists($username)) {
            return "Tên đăng nhập đã được sử dụng";
        }
        
        if ($this->model->checkEmailExists($email)) {
            return "Email đã được sử dụng";
        }
        
        // Tạo tài khoản
        try {
            if ($this->model->createUser($username, $email, $password)) {
                $_SESSION['success_message'] = "Đăng ký thành công! Vui lòng đăng nhập.";
                header("Location: " . BASE_URL . "/app/Controllers/User/Login_controller.php");
                exit();
            } else {
                return "Có lỗi xảy ra, vui lòng thử lại sau";
            }
        } catch (Exception $e) {
            return "Có lỗi xảy ra, vui lòng thử lại sau";
        }
    }
}

// Khởi tạo controller và chạy
$controller = new Register_controller($conn);
$controller->index();
?>
