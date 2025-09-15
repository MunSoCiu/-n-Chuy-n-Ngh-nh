<?php
session_start();
require_once '../../../app/config/config.php';
require_once '../../../includes/functions.php';
require_once '../../../app/Models/User/Profile_model.php';

class Profile_controller {
    private $model;
    
    public function __construct($connection) {
        $this->model = new Profile_model($connection);
    }
    
    public function index() {
        // Kiểm tra đăng nhập
        if (!isset($_SESSION['user_id'])) {
            header("Location: ../../../app/Controllers/User/Login_controller.php");
            exit();
        }
        
        $user_id = $_SESSION['user_id'];
        $error = '';
        $success = '';
        
        // Xử lý cập nhật thông tin
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $this->updateProfile($user_id);
            $error = $result['error'];
            $success = $result['success'];
        }
        
        // Lấy dữ liệu cho view
        $data = [
            'user' => $this->model->getUserById($user_id),
            'reading_history' => $this->model->getReadingHistory($user_id),
            'favorites' => $this->model->getFavorites($user_id),
            'error' => $error,
            'success' => $success
        ];
        
        // Load view
        include '../../../app/views/User/Profile_view.php';
    }
    
    private function updateProfile($user_id) {
        $user = $this->model->getUserById($user_id);
        $username = isset($_POST['username']) ? trim($_POST['username']) : $user['username'];
        $email = isset($_POST['email']) ? trim($_POST['email']) : $user['email'];
        $password = isset($_POST['password']) && !empty($_POST['password']) ? trim($_POST['password']) : null;
        $current_password = isset($_POST['current_password']) ? trim($_POST['current_password']) : '';
        
        // Kiểm tra username và email không được trống
        if (empty($username) || empty($email)) {
            return ['error' => "Tên đăng nhập và email không được để trống", 'success' => ''];
        }
        
        // Nếu muốn đổi mật khẩu, phải nhập mật khẩu hiện tại
        if ($password && empty($current_password)) {
            return ['error' => "Vui lòng nhập mật khẩu hiện tại để thay đổi mật khẩu", 'success' => ''];
        }
        
        // Xác thực mật khẩu hiện tại nếu muốn đổi mật khẩu
        if ($password && !$this->model->verifyCurrentPassword($user_id, $current_password)) {
            return ['error' => "Mật khẩu hiện tại không đúng", 'success' => ''];
        }
        
        // Kiểm tra username đã tồn tại
        if ($this->model->checkUsernameExists($username, $user_id)) {
            return ['error' => "Tên đăng nhập đã tồn tại", 'success' => ''];
        }
        
        // Kiểm tra email đã tồn tại
        if ($this->model->checkEmailExists($email, $user_id)) {
            return ['error' => "Email đã tồn tại", 'success' => ''];
        }
        
        // Xử lý upload avatar
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $avatar_result = $this->handleAvatarUpload($user_id);
            if ($avatar_result['error']) {
                return ['error' => $avatar_result['error'], 'success' => ''];
            }
        }
        
        // Cập nhật thông tin
        if ($this->model->updateUser($user_id, $username, $email, $password)) {
            $_SESSION['username'] = $username;
            return ['error' => '', 'success' => "Cập nhật thông tin thành công!"];
        } else {
            return ['error' => "Có lỗi xảy ra khi cập nhật thông tin", 'success' => ''];
        }
    }
    
    private function handleAvatarUpload($user_id) {
        $upload_dir = '../../../uploads/avatars/';
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 2 * 1024 * 1024; // 2MB
        
        $file = $_FILES['avatar'];
        
        // Kiểm tra loại file
        if (!in_array($file['type'], $allowed_types)) {
            return ['error' => "Chỉ chấp nhận file ảnh (JPG, PNG, GIF)"];
        }
        
        // Kiểm tra kích thước
        if ($file['size'] > $max_size) {
            return ['error' => "File ảnh quá lớn (tối đa 2MB)"];
        }
        
        // Tạo tên file unique
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $extension;
        $filepath = $upload_dir . $filename;
        
        // Upload file
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            // Cập nhật avatar trong database
            $avatar_url = 'uploads/avatars/' . $filename;
            if ($this->model->updateAvatar($user_id, $avatar_url)) {
                $_SESSION['avatar_url'] = $avatar_url;
                return ['error' => ''];
            }
        }
        
        return ['error' => "Có lỗi xảy ra khi upload avatar"];
    }
}

// Khởi tạo controller và chạy
$controller = new Profile_controller($conn);
$controller->index();
?>
