<?php
class Admin_dashboard_model {
    private $conn;
    
    public function __construct($connection) {
        if (!$connection instanceof mysqli) {
            throw new Exception("Kết nối CSDL không hợp lệ");
        }
        $this->conn = $connection;
    }

    /* ==================== THỐNG KÊ TỔNG QUAN ==================== */

    public function getNovelStats() {
        $sql = "SELECT 
                    COUNT(*) as total_novels,
                    COUNT(CASE WHEN status = 'Đang tiến hành' THEN 1 END) as ongoing,
                    COUNT(CASE WHEN status = 'Đã hoàn thành' THEN 1 END) as completed,
                    COUNT(CASE WHEN status = 'Đã hủy bỏ' THEN 1 END) as dropped
                FROM LightNovels";
        return $this->fetchOne($sql, ['total_novels'=>0,'ongoing'=>0,'completed'=>0,'dropped'=>0]);
    }

    public function getUserStats() {
        $sql = "SELECT 
                    COUNT(*) as total_users,
                    COUNT(CASE WHEN role = 'admin' THEN 1 END) as admin_count,
                    COUNT(CASE WHEN role = 'user' THEN 1 END) as user_count
                FROM Users";
        return $this->fetchOne($sql, ['total_users'=>0,'admin_count'=>0,'user_count'=>0]);
    }

    public function getCommentStats() {
        $sql = "SELECT COUNT(*) as total FROM Comments";
        return $this->fetchOne($sql, ['total'=>0]);
    }

    /* ==================== DỮ LIỆU GẦN ĐÂY ==================== */

    public function getRecentNovels() {
        $sql = "SELECT 
                    n.novel_id,
                    n.title,
                    n.author,
                    n.status,
                    n.created_at,
                    COUNT(c.chapter_id) as chapter_count 
                FROM LightNovels n
                LEFT JOIN Chapters c ON n.novel_id = c.novel_id
                GROUP BY n.novel_id, n.title, n.author, n.status, n.created_at
                ORDER BY n.created_at DESC 
                LIMIT 5";
        return $this->fetchAll($sql);
    }

    public function getNewUsers() {
        $sql = "SELECT user_id, username, email, role, created_at
                FROM Users 
                ORDER BY created_at DESC 
                LIMIT 5";
        return $this->fetchAll($sql);
    }

    public function getRecentComments() {
        $sql = "SELECT c.comment_id, c.content, c.created_at,
                       u.username, n.title as novel_title
                FROM Comments c
                JOIN Users u ON c.user_id = u.user_id
                JOIN LightNovels n ON c.novel_id = n.novel_id
                ORDER BY c.created_at DESC
                LIMIT 5";
        return $this->fetchAll($sql);
    }

    /* ==================== DOANH THU ==================== */

    public function getRevenueStats() {
        $sql = "SELECT 
                    IFNULL(SUM(price - discount_applied),0) as total_revenue,
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
        return $this->fetchOne($sql, [
            'total_revenue'=>0,'total_novels_sold'=>0,
            'total_purchases'=>0,'current_month_revenue'=>0,'current_month_sales'=>0
        ]);
    }

    public function getTopNovels() {
        $sql = "SELECT 
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
        return $this->fetchAll($sql);
    }

    public function getMonthlyRevenue($year) {
        $sql = "SELECT 
                    MONTH(purchase_date) as month,
                    COUNT(*) as total_sales,
                    SUM(price - discount_applied) as revenue
                FROM Purchases
                WHERE status = 'completed'
                  AND YEAR(purchase_date) = ?
                GROUP BY MONTH(purchase_date)
                ORDER BY month ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $year);
        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];
        for ($m = 1; $m <= 12; $m++) {
            $data[$m] = ['month'=>$m,'total_sales'=>0,'revenue'=>0.0];
        }

        while ($row = $result->fetch_assoc()) {
            $month = (int)$row['month'];
            $data[$month]['total_sales'] = (int)($row['total_sales'] ?? 0);
            $data[$month]['revenue'] = (float)($row['revenue'] ?? 0.0);
        }

        $stmt->close();
        return $data;
    }

    /* ==================== HÀNH ĐỘNG ==================== */

    public function deleteComment($comment_id) {
        $sql = "DELETE FROM Comments WHERE comment_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $comment_id);
        $stmt->execute();
        $affected = $stmt->affected_rows; // số dòng bị ảnh hưởng
        $stmt->close();
        return $affected > 0; // chỉ true nếu thực sự xóa được
    }

    /* ==================== THỐNG KÊ THEO NGÀY ==================== */

    public function getTodayReads() {
        $sql = "SELECT COUNT(*) AS total 
                FROM reading_history 
                WHERE DATE(last_read) = CURDATE()";
        $row = $this->fetchOne($sql, ['total'=>0]);
        return (int)$row['total'];
    }

    public function getTodayRevenue() {
        $sql = "SELECT IFNULL(SUM(price - discount_applied), 0) AS revenue 
                FROM Purchases 
                WHERE DATE(purchase_date) = CURDATE()
                  AND status = 'completed'";
        $row = $this->fetchOne($sql, ['revenue'=>0]);
        return (float)$row['revenue'];
    }

    public function getDailyStatsByMonth($year, $month) {
    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    $stats = ["reads" => [], "revenue" => []];

    // init 0 cho tất cả ngày
    for ($d = 1; $d <= $daysInMonth; $d++) {
        $stats['reads'][$d] = 0;
        $stats['revenue'][$d] = 0.0;
    }

    // Lượt đọc
    $sqlReads = "SELECT DAY(last_read) AS day, COUNT(*) AS total
                 FROM reading_history
                 WHERE YEAR(last_read) = ? AND MONTH(last_read) = ?
                 GROUP BY DAY(last_read)";
    $stmt = $this->conn->prepare($sqlReads);
    $stmt->bind_param("ii", $year, $month);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $stats['reads'][(int)$row['day']] = (int)$row['total'];
    }
    $stmt->close();

    // Doanh thu
    $sqlRev = "SELECT DAY(purchase_date) AS day, IFNULL(SUM(price - discount_applied),0) AS revenue
               FROM Purchases
               WHERE YEAR(purchase_date) = ? AND MONTH(purchase_date) = ?
                 AND status = 'completed'
               GROUP BY DAY(purchase_date)";
    $stmt = $this->conn->prepare($sqlRev);
    $stmt->bind_param("ii", $year, $month);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $stats['revenue'][(int)$row['day']] = (float)$row['revenue'];
    }
    $stmt->close();

    return $stats;
}


    public function getMonthlySummary($year, $month) {
        $summary = ["reads" => 0, "revenue" => 0.0];

        $sqlReads = "SELECT COUNT(*) AS total
                     FROM reading_history
                     WHERE YEAR(last_read) = ? AND MONTH(last_read) = ?";
        $stmt = $this->conn->prepare($sqlReads);
        $stmt->bind_param("ii", $year, $month);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $summary['reads'] = (int)($res['total'] ?? 0);
        $stmt->close();

        $sqlRev = "SELECT IFNULL(SUM(price - discount_applied),0) AS revenue
                   FROM Purchases
                   WHERE YEAR(purchase_date) = ? AND MONTH(purchase_date) = ? 
                     AND status = 'completed'";
        $stmt = $this->conn->prepare($sqlRev);
        $stmt->bind_param("ii", $year, $month);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $summary['revenue'] = (float)($res['revenue'] ?? 0.0);
        $stmt->close();

        return $summary;
    }

    /* ==================== HELPER ==================== */

    private function fetchOne($sql, $default = []) {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $row ?: $default;
        } catch (Exception $e) {
            return $default;
        }
    }

    private function fetchAll($sql) {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            return $rows ?: [];
        } catch (Exception $e) {
            return [];
        }
    }
}
?>
