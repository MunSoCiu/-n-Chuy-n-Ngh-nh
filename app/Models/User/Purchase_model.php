<?php
class Purchase_model {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    // Lấy thông tin novel theo ID
    public function getNovelById($novel_id) {
        $query = "SELECT * FROM LightNovels WHERE novel_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $novel_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    // Kiểm tra xem người dùng đã mua truyện chưa
    public function checkPurchase($user_id, $novel_id) {
        $check_purchase = $this->conn->prepare("SELECT 1 FROM Purchases WHERE user_id = ? AND novel_id = ?");
        $check_purchase->bind_param("ii", $user_id, $novel_id);
        $check_purchase->execute();
        return $check_purchase->get_result()->num_rows > 0;
    }
    
    // Thực hiện mua truyện
    public function purchaseNovel($user_id, $novel_id, $price, $payment_method) {
        $purchase_stmt = $this->conn->prepare(
            "INSERT INTO Purchases (user_id, novel_id, price, payment_method) 
             VALUES (?, ?, ?, ?)"
        );
        $purchase_stmt->bind_param("iids", $user_id, $novel_id, $price, $payment_method);
        return $purchase_stmt->execute();
    }

    // Tổng doanh thu
    public function getTotalRevenue() {
        $result = $this->conn->query("SELECT SUM(price) AS total FROM Purchases");
        $row = $result->fetch_assoc();
        return $row['total'] ?? 0;
    }
}
?>
