<?php
class Admin_dashboard_model {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    // Lấy thống kê tổng quan về truyện
    public function getNovelStats() {
        $novel_query = "SELECT 
            COUNT(*) as total_novels,
            COUNT(CASE WHEN status = 'Đang tiến hành' THEN 1 END) as ongoing,
            COUNT(CASE WHEN status = 'Đã hoàn thành' THEN 1 END) as completed,
            COUNT(CASE WHEN status = 'Đã hủy bỏ' THEN 1 END) as dropped
            FROM LightNovels";
        return $this->conn->query($novel_query)->fetch_assoc();
    }
    
    
    
    // Lấy thống kê người dùng
    public function getUserStats() {
        $user_query = "SELECT 
            COUNT(*) as total_users,
            COUNT(CASE WHEN role = 'admin' THEN 1 END) as admin_count,
            COUNT(CASE WHEN role = 'user' THEN 1 END) as user_count
            FROM Users";
        return $this->conn->query($user_query)->fetch_assoc();
    }
    
    // Lấy thống kê bình luận
    public function getCommentStats() {
        return $this->conn->query("SELECT COUNT(*) as total FROM Comments")->fetch_assoc();
    }
    
    // Lấy truyện mới thêm gần đây
    public function getRecentNovels() {
        return $this->conn->query("
            SELECT n.*, COUNT(c.chapter_id) as chapter_count 
            FROM LightNovels n
            LEFT JOIN Chapters c ON n.novel_id = c.novel_id
            GROUP BY n.novel_id
            ORDER BY n.created_at DESC 
            LIMIT 5
        ");
    }
    
    // Lấy người dùng mới đăng ký
    public function getNewUsers() {
        return $this->conn->query("
            SELECT * FROM Users 
            ORDER BY created_at DESC 
            LIMIT 5
        ");
    }
    
    // Lấy bình luận mới nhất
    public function getRecentComments() {
        return $this->conn->query("
            SELECT c.*, u.username, n.title as novel_title
            FROM Comments c
            JOIN Users u ON c.user_id = u.user_id
            JOIN LightNovels n ON c.novel_id = n.novel_id
            ORDER BY c.created_at DESC
            LIMIT 5
        ");
    }
    
    // Lấy thống kê doanh thu
    public function getRevenueStats() {
        $revenue_query = "SELECT 
            SUM(price - discount_applied) as total_revenue,
            COUNT(DISTINCT novel_id) as total_novels_sold,
            COUNT(*) as total_purchases,
            SUM(CASE WHEN MONTH(purchase_date) = MONTH(CURRENT_DATE) 
                     AND YEAR(purchase_date) = YEAR(CURRENT_DATE) 
                THEN price - discount_applied ELSE 0 END) as current_month_revenue,
            SUM(CASE WHEN MONTH(purchase_date) = MONTH(CURRENT_DATE) 
                     AND YEAR(purchase_date) = YEAR(CURRENT_DATE) 
                THEN 1 ELSE 0 END) as current_month_sales
            FROM Purchases
            WHERE status = 'completed'";
        return $this->conn->query($revenue_query)->fetch_assoc();
    }
    
    // Lấy top truyện bán chạy nhất
    public function getTopNovels() {
        $top_novels_query = "SELECT 
            n.novel_id,
            n.title,
            COUNT(*) as purchase_count,
            SUM(p.price - p.discount_applied) as total_revenue
            FROM Purchases p
            JOIN LightNovels n ON p.novel_id = n.novel_id
            WHERE p.status = 'completed'
            GROUP BY n.novel_id, n.title
            ORDER BY purchase_count DESC
            LIMIT 5";
        return $this->conn->query($top_novels_query);
    }
    
    // Lấy thống kê doanh thu theo tháng
    public function getMonthlyRevenue() {
        $monthly_revenue_query = "SELECT 
            DATE_FORMAT(purchase_date, '%Y-%m') as month,
            COUNT(*) as total_sales,
            SUM(price - discount_applied) as revenue
            FROM Purchases
            WHERE status = 'completed'
            AND purchase_date >= DATE_SUB(CURRENT_DATE, INTERVAL 6 MONTH)
            GROUP BY DATE_FORMAT(purchase_date, '%Y-%m')
            ORDER BY month DESC";
        return $this->conn->query($monthly_revenue_query);
    }
    
    // Xóa bình luận
    public function deleteComment($comment_id) {
        $delete_stmt = $this->conn->prepare("DELETE FROM Comments WHERE comment_id = ?");
        $delete_stmt->bind_param("i", $comment_id);
        return $delete_stmt->execute();
    }

    // Lấy số lượt đọc trong ngày
    public function getTodayReads() {
    $sql = "SELECT COUNT(*) AS total 
            FROM reading_history 
            WHERE DATE(last_read) = CURDATE()";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return (int)($row['total'] ?? 0);
}


    // Lấy doanh thu trong ngày
    public function getTodayRevenue() {
    $sql = "SELECT IFNULL(SUM(price - discount_applied), 0) AS revenue 
            FROM purchases 
            WHERE DATE(purchase_date) = CURDATE()
              AND status = 'completed'";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return (float)($row['revenue']?? 0);
}

}
?>