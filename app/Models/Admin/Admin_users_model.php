<?php
class Admin_users_model {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    // Lấy danh sách users với tìm kiếm và phân trang
    public function getUsers($search = '', $role = '', $limit = 20, $offset = 0) {
        $query = "SELECT u.*, 
                  COUNT(DISTINCT f.novel_id) as favorite_count,
                  COUNT(DISTINCT p.purchase_id) as purchase_count,
                  COUNT(DISTINCT rh.novel_id) as reading_count
                  FROM Users u
                  LEFT JOIN Favorites f ON u.user_id = f.user_id
                  LEFT JOIN Purchases p ON u.user_id = p.user_id
                  LEFT JOIN Reading_History rh ON u.user_id = rh.user_id
                  WHERE 1=1";
        
        $params = [];
        $types = "";
        
        if (!empty($search)) {
            $query .= " AND (u.username LIKE ? OR u.email LIKE ?)";
            $search_param = "%$search%";
            $params[] = $search_param;
            $params[] = $search_param;
            $types .= "ss";
        }
        
        if (!empty($role)) {
            $query .= " AND u.role = ?";
            $params[] = $role;
            $types .= "s";
        }
        
        $query .= " GROUP BY u.user_id ORDER BY u.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $types .= "ii";
        
        $stmt = $this->conn->prepare($query);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt->get_result();
    }
    
    // Đếm tổng số users
    public function getTotalUsers($search = '', $role = '') {
        $query = "SELECT COUNT(*) as total FROM Users WHERE 1=1";
        
        $params = [];
        $types = "";
        
        if (!empty($search)) {
            $query .= " AND (username LIKE ? OR email LIKE ?)";
            $search_param = "%$search%";
            $params[] = $search_param;
            $params[] = $search_param;
            $types .= "ss";
        }
        
        if (!empty($role)) {
            $query .= " AND role = ?";
            $params[] = $role;
            $types .= "s";
        }
        
        $stmt = $this->conn->prepare($query);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc()['total'];
    }
    
    // Xóa user
    public function deleteUser($user_id) {
        // Lấy avatar cũ để xóa file
        $avatar_query = "SELECT avatar_url FROM Users WHERE user_id = ?";
        $stmt = $this->conn->prepare($avatar_query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $old_avatar = $stmt->get_result()->fetch_assoc()['avatar_url'];
        
        // Xóa user
        $delete_query = "DELETE FROM Users WHERE user_id = ?";
        $stmt = $this->conn->prepare($delete_query);
        $stmt->bind_param("i", $user_id);
        $result = $stmt->execute();
        
        // Xóa avatar file nếu có
        if ($result && $old_avatar && file_exists("../../../" . $old_avatar)) {
            unlink("../../../" . $old_avatar);
        }
        
        return $result;
    }
    
    // Thêm user mới
    public function addUser($username, $email, $password, $role) {
        $password_hash = $password; // Assuming password is already hashed
        $stmt = $this->conn->prepare("INSERT INTO Users (username, email, password_hash, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $email, $password_hash, $role);
        return $stmt->execute();
    }
    
    // Cập nhật user
    public function updateUser($user_id, $username, $email, $role, $password = null) {
        if ($password) {
            $query = "UPDATE Users SET username = ?, email = ?, role = ?, password_hash = ? WHERE user_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("ssssi", $username, $email, $role, $password, $user_id);
        } else {
            $query = "UPDATE Users SET username = ?, email = ?, role = ? WHERE user_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("sssi", $username, $email, $role, $user_id);
        }
        return $stmt->execute();
    }
    
    // Kiểm tra username đã tồn tại
    public function checkUsernameExists($username, $user_id = null) {
        $query = "SELECT user_id FROM Users WHERE username = ?";
        if ($user_id) {
            $query .= " AND user_id != ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("si", $username, $user_id);
        } else {
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("s", $username);
        }
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }
    
    // Kiểm tra email đã tồn tại
    public function checkEmailExists($email, $user_id = null) {
        $query = "SELECT user_id FROM Users WHERE email = ?";
        if ($user_id) {
            $query .= " AND user_id != ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("si", $email, $user_id);
        } else {
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("s", $email);
        }
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }
    
    // Lấy thông tin user
    public function getUser($user_id) {
        $stmt = $this->conn->prepare("SELECT * FROM Users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}
?>
