<?php
session_start();
require_once '../../../app/config/config.php';
require_once '../../../includes/functions.php';
require_once '../../../app/Models/Admin/Admin_users_model.php';

class Admin_users_controller {
    private $model;
    
    public function __construct($connection) {
        $this->model = new Admin_users_model($connection);
    }
    
    public function index() {
        // Kiểm tra quyền admin
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header("Location: ../../../../index.php");
            exit();
        }
        
        $success = '';
        $error = '';
        
        // Xử lý xóa người dùng
        if (isset($_POST['delete_user'])) {
            $user_id = (int)$_POST['delete_user'];
            
            // Không cho phép admin xóa chính mình
            if ($user_id == $_SESSION['user_id']) {
                $error = "Không thể xóa tài khoản của chính mình!";
            } else {
                if ($this->model->deleteUser($user_id)) {
                    $success = "Đã xóa người dùng thành công!";
                } else {
                    $error = "Có lỗi xảy ra khi xóa người dùng!";
                }
            }
        }
        
        // Xử lý thêm/sửa người dùng
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_user'])) {
            try {
                $this->saveUser();
                $success = "Đã lưu thông tin người dùng thành công!";
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
        
        // Lấy dữ liệu
        $search = $_GET['search'] ?? '';
        $role = $_GET['role'] ?? '';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        $total_users = $this->model->getTotalUsers($search, $role);
        $total_pages = ceil($total_users / $limit);
        
        $data = [
            'users' => $this->model->getUsers($search, $role, $limit, $offset),
            'search' => $search,
            'role' => $role,
            'page' => $page,
            'total_pages' => $total_pages,
            'total_users' => $total_users,
            'success' => $success,
            'error' => $error
        ];
        
        // Load view
        include '../../../app/views/Admin/Admin_users_view.php';
    }
    
    private function saveUser() {
        $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : null;
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $role = $_POST['role'];
        $new_password = trim($_POST['password']);
        
        // Validation
        if (empty($username) || empty($email)) {
            throw new Exception("Tên đăng nhập và email không được để trống!");
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Email không hợp lệ!");
        }
        
        // Kiểm tra trùng lặp
        if ($this->model->checkUsernameExists($username, $user_id)) {
            throw new Exception("Tên đăng nhập đã tồn tại!");
        }
        
        if ($this->model->checkEmailExists($email, $user_id)) {
            throw new Exception("Email đã tồn tại!");
        }
        
        if ($user_id) {
            // Cập nhật user
            $password = !empty($new_password) ? $new_password : null;
            $this->model->updateUser($user_id, $username, $email, $role, $password);
        } else {
            // Thêm user mới
            if (empty($new_password)) {
                throw new Exception("Mật khẩu không được để trống khi tạo tài khoản mới!");
            }
            $this->model->addUser($username, $email, $new_password, $role);
        }
    }
}

// Khởi tạo controller và chạy
$controller = new Admin_users_controller($conn);
$controller->index();
?>
