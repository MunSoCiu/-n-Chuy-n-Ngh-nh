<?php
class Profile_model {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    // Lấy thông tin user
    public function getUserById($user_id) {
        $query = "SELECT * FROM Users WHERE user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    // Lấy lịch sử đọc
    public function getReadingHistory($user_id) {
        $history_query = "SELECT rh.*, n.title, n.cover_image, c.title as chapter_title
                         FROM Reading_History rh
                         JOIN LightNovels n ON rh.novel_id = n.novel_id
                         JOIN Chapters c ON rh.chapter_id = c.chapter_id
                         WHERE rh.user_id = ?
                         ORDER BY rh.last_read DESC";
        $stmt = $this->conn->prepare($history_query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result();
    }
    
    // Lấy truyện yêu thích
    public function getFavorites($user_id) {
        $favorites_query = "SELECT n.* 
                           FROM Favorites f
                           JOIN LightNovels n ON f.novel_id = n.novel_id
                           WHERE f.user_id = ?
                           ORDER BY n.title";
        $stmt = $this->conn->prepare($favorites_query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result();
    }
    
    // Kiểm tra username đã tồn tại
    public function checkUsernameExists($username, $exclude_user_id = null) {
        $query = "SELECT user_id FROM Users WHERE username = ?";
        $params = [$username];
        
        if ($exclude_user_id) {
            $query .= " AND user_id != ?";
            $params[] = $exclude_user_id;
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(str_repeat('s', count($params)), ...$params);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }
    
    // Kiểm tra email đã tồn tại
    public function checkEmailExists($email, $exclude_user_id = null) {
        $query = "SELECT user_id FROM Users WHERE email = ?";
        $params = [$email];
        
        if ($exclude_user_id) {
            $query .= " AND user_id != ?";
            $params[] = $exclude_user_id;
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(str_repeat('s', count($params)), ...$params);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }
    
    // Cập nhật thông tin user
    public function updateUser($user_id, $username, $email, $password = null) {
        if ($password) {
            $query = "UPDATE Users SET username = ?, email = ?, password_hash = ? WHERE user_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("sssi", $username, $email, $password, $user_id);
        } else {
            $query = "UPDATE Users SET username = ?, email = ? WHERE user_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("ssi", $username, $email, $user_id);
        }
        return $stmt->execute();
    }
    
    // Cập nhật avatar
    public function updateAvatar($user_id, $avatar_url) {
        $query = "UPDATE Users SET avatar_url = ? WHERE user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("si", $avatar_url, $user_id);
        return $stmt->execute();
    }
    
    // Xác thực mật khẩu hiện tại
    public function verifyCurrentPassword($user_id, $current_password) {
        $query = "SELECT password_hash FROM Users WHERE user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if ($result) {
            return password_verify($current_password, $result['password_hash']);
        }
        return false;
    }
}
?>
