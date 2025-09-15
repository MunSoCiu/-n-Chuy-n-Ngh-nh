<?php
class Admin_promotions_model {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    // Lấy tất cả promotions
    public function getAllPromotions() {
        $query = "SELECT p.*, 
                  COUNT(up.user_id) as assigned_count
                  FROM Promotions p
                  LEFT JOIN User_Promotions up ON p.promo_id = up.promo_id
                  GROUP BY p.promo_id
                  ORDER BY p.created_at DESC";
        return $this->conn->query($query);
    }
    
    // Thêm promotion mới
    public function addPromotion($code, $discount_percentage, $start_date, $end_date, $description = '') {
        $stmt = $this->conn->prepare("INSERT INTO Promotions (code, discount_percentage, start_date, end_date, description) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sisss", $code, $discount_percentage, $start_date, $end_date, $description);
        return $stmt->execute();
    }
    
    // Cập nhật promotion
    public function updatePromotion($promo_id, $code, $discount_percentage, $start_date, $end_date, $description = '') {
        $stmt = $this->conn->prepare("UPDATE Promotions SET code = ?, discount_percentage = ?, start_date = ?, end_date = ?, description = ? WHERE promo_id = ?");
        $stmt->bind_param("sisssi", $code, $discount_percentage, $start_date, $end_date, $description, $promo_id);
        return $stmt->execute();
    }
    
    // Xóa promotion
    public function deletePromotion($promo_id) {
        // Xóa các assignment trước
        $this->conn->query("DELETE FROM User_Promotions WHERE promo_id = $promo_id");
        
        // Xóa promotion
        $stmt = $this->conn->prepare("DELETE FROM Promotions WHERE promo_id = ?");
        $stmt->bind_param("i", $promo_id);
        return $stmt->execute();
    }
    
    // Lấy promotion theo ID
    public function getPromotion($promo_id) {
        $stmt = $this->conn->prepare("SELECT * FROM Promotions WHERE promo_id = ?");
        $stmt->bind_param("i", $promo_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    // Kiểm tra code đã tồn tại
    public function checkCodeExists($code, $promo_id = null) {
        $query = "SELECT promo_id FROM Promotions WHERE code = ?";
        if ($promo_id) {
            $query .= " AND promo_id != ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("si", $code, $promo_id);
        } else {
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("s", $code);
        }
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }
    
    // Gán promotion cho user
    public function assignPromotionToUser($user_id, $promo_id) {
        $stmt = $this->conn->prepare("INSERT INTO User_Promotions (user_id, promo_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $user_id, $promo_id);
        return $stmt->execute();
    }
    
    // Lấy users đã được gán promotion
    public function getAssignedUsers($promo_id) {
        $query = "SELECT u.user_id, u.username, u.email, up.assigned_at
                  FROM User_Promotions up
                  JOIN Users u ON up.user_id = u.user_id
                  WHERE up.promo_id = ?
                  ORDER BY up.assigned_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $promo_id);
        $stmt->execute();
        return $stmt->get_result();
    }
    
    // Lấy tất cả users
    public function getAllUsers() {
        return $this->conn->query("SELECT user_id, username, email FROM Users WHERE role = 'user' ORDER BY username");
    }
}
?>
