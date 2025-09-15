<?php
class Login_model {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    // Kiểm tra thông tin đăng nhập
    public function authenticateUser($username, $password) {
        $sql = "SELECT user_id, username, password_hash as password, role, avatar_url 
                FROM Users WHERE username = ? OR email = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if ($password === $user['password']) {
                return $user;
            }
        }
        return false;
    }
}
?>
