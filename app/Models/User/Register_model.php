<?php
class Register_model {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    // Kiểm tra username đã tồn tại chưa
    public function checkUsernameExists($username) {
        $check_username = $this->conn->prepare("SELECT 1 FROM Users WHERE username = ?");
        $check_username->bind_param("s", $username);
        $check_username->execute();
        return $check_username->get_result()->num_rows > 0;
    }
    
    // Kiểm tra email đã tồn tại chưa
    public function checkEmailExists($email) {
        $check_email = $this->conn->prepare("SELECT 1 FROM Users WHERE email = ?");
        $check_email->bind_param("s", $email);
        $check_email->execute();
        return $check_email->get_result()->num_rows > 0;
    }
    
    // Tạo tài khoản mới
    public function createUser($username, $email, $password) {
        $stmt = $this->conn->prepare("INSERT INTO Users (username, email, password_hash, role, created_at) VALUES (?, ?, ?, 'user', NOW())");
        $stmt->bind_param("sss", $username, $email, $password);
        return $stmt->execute();
    }
}
?>
