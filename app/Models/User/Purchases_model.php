<?php
class Purchases_model {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    // Lấy danh sách truyện đã mua
    public function getPurchases($user_id) {
        $query = "SELECT n.*, p.purchase_date, p.price as paid_price, p.discount_applied,
                  GROUP_CONCAT(c.name) as categories
                  FROM Purchases p
                  JOIN LightNovels n ON p.novel_id = n.novel_id
                  LEFT JOIN Novel_Categories nc ON n.novel_id = nc.novel_id
                  LEFT JOIN Categories c ON nc.category_id = c.category_id
                  WHERE p.user_id = ?
                  GROUP BY n.novel_id, n.title, n.author, n.description, n.cover_image, 
                           n.status, n.price, n.created_at,
                           p.purchase_date, p.price, p.discount_applied
                  ORDER BY p.purchase_date DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result();
    }
    
    // Lấy tổng số tiền đã chi
    public function getTotalSpent($user_id) {
        $query = "SELECT SUM(price - discount_applied) as total FROM Purchases WHERE user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc()['total'] ?? 0;
    }
    
    // Lấy số lượng truyện đã mua
    public function getTotalPurchases($user_id) {
        $query = "SELECT COUNT(*) as count FROM Purchases WHERE user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc()['count'];
    }
}
?>
