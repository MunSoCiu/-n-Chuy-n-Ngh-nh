<?php
class Admin_purchases_model {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    // Lấy danh sách purchases với tìm kiếm + lọc ngày/khoảng ngày
// Lấy danh sách purchases với tìm kiếm + lọc ngày/khoảng ngày
public function getPurchases($search = '', $search_type = 'novel', $date = '', $from_date = '', $to_date = '') {
    $query = "SELECT p.*, n.title as novel_title, u.username, n.price as original_price
              FROM Purchases p
              JOIN LightNovels n ON p.novel_id = n.novel_id
              JOIN Users u ON p.user_id = u.user_id
              WHERE 1=1";
    
    $params = [];
    $types = "";

    // Tìm kiếm theo tên truyện hoặc user
    if ($search) {
        if ($search_type === 'novel') {
            $query .= " AND n.title LIKE ?";
            $params[] = "%$search%";
            $types .= "s";
        } elseif ($search_type === 'user') {
            $query .= " AND u.username LIKE ?";
            $params[] = "%$search%";
            $types .= "s";
        }
    }

    // Lọc theo 1 ngày cụ thể
    if ($date) {
        $query .= " AND p.purchase_date BETWEEN ? AND ?";
        $params[] = $date . " 00:00:00";
        $params[] = $date . " 23:59:59";
        $types .= "ss";
    }

    // Lọc theo khoảng ngày
    if ($from_date && $to_date) {
        $query .= " AND p.purchase_date BETWEEN ? AND ?";
        $params[] = $from_date . " 00:00:00";
        $params[] = $to_date . " 23:59:59";
        $types .= "ss";
    }

    $query .= " ORDER BY p.purchase_date DESC";

    $stmt = $this->conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    return $stmt->get_result();
}



    
    // Xóa purchase
    public function deletePurchase($purchase_id) {
        $stmt = $this->conn->prepare("DELETE FROM Purchases WHERE purchase_id = ?");
        $stmt->bind_param("i", $purchase_id);
        return $stmt->execute();
    }
    
    // Cập nhật trạng thái purchase
    public function updatePurchaseStatus($purchase_id, $status) {
        $stmt = $this->conn->prepare("UPDATE Purchases SET status = ? WHERE purchase_id = ?");
        $stmt->bind_param("si", $status, $purchase_id);
        return $stmt->execute();
    }
    
    // Lấy thống kê purchases
    public function getPurchaseStats() {
        $stats = [];
        
        // Tổng doanh thu
        $revenue_query = "SELECT SUM(price - discount_applied) as total_revenue FROM Purchases WHERE status = 'completed'";
        $result = $this->conn->query($revenue_query);
        $stats['total_revenue'] = $result->fetch_assoc()['total_revenue'] ?? 0;
        
        // Tổng số đơn hàng
        $orders_query = "SELECT COUNT(*) as total_orders FROM Purchases";
        $result = $this->conn->query($orders_query);
        $stats['total_orders'] = $result->fetch_assoc()['total_orders'];
        
        // Đơn hàng hoàn thành
        $completed_query = "SELECT COUNT(*) as completed_orders FROM Purchases WHERE status = 'completed'";
        $result = $this->conn->query($completed_query);
        $stats['completed_orders'] = $result->fetch_assoc()['completed_orders'];
        
        // Đơn hàng pending
        $pending_query = "SELECT COUNT(*) as pending_orders FROM Purchases WHERE status = 'pending'";
        $result = $this->conn->query($pending_query);
        $stats['pending_orders'] = $result->fetch_assoc()['pending_orders'];
        
        return $stats;
    }
    
    // Lấy top novels bán chạy
    public function getTopSellingNovels($limit = 10) {
        $query = "SELECT n.title, COUNT(p.purchase_id) as sales_count, SUM(p.price - p.discount_applied) as revenue
                  FROM Purchases p
                  JOIN LightNovels n ON p.novel_id = n.novel_id
                  WHERE p.status = 'completed'
                  GROUP BY n.novel_id, n.title
                  ORDER BY sales_count DESC
                  LIMIT ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        return $stmt->get_result();
    }
}
?>
