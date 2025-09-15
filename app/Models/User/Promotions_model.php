<?php
class Promotions_model {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    // Lấy danh sách mã giảm giá
    public function getPromotions($is_admin = false) {
        if ($is_admin) {
            $query = "SELECT * FROM Promotions ORDER BY created_at DESC";
        } else {
            $query = "SELECT * FROM Promotions 
                      WHERE NOW() BETWEEN start_date AND end_date 
                      ORDER BY end_date ASC";
        }
        return $this->conn->query($query);
    }
    
    // Gán mã giảm giá cho user
    public function assignPromotion($user_id, $promo_id) {
        $stmt = $this->conn->prepare("INSERT INTO User_Promotions (user_id, promo_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $user_id, $promo_id);
        return $stmt->execute();
    }
    
    // Lấy mã giảm giá của user
    public function getUserPromotions($user_id) {
        $query = "SELECT p.*, up.assigned_at 
                  FROM User_Promotions up
                  JOIN Promotions p ON up.promo_id = p.promo_id
                  WHERE up.user_id = ? AND p.end_date >= NOW()
                  ORDER BY p.end_date ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result();
    }
}
?>
