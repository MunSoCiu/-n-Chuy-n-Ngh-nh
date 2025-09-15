<?php
class Rankings_model {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    // Lấy truyện theo lượt yêu thích
    public function getFavoriteRankings($limit = 10) {
        $query = "SELECT n.*, COUNT(f.novel_id) as favorite_count,
                  GROUP_CONCAT(c.name) as categories
                  FROM LightNovels n
                  LEFT JOIN Favorites f ON n.novel_id = f.novel_id
                  LEFT JOIN Novel_Categories nc ON n.novel_id = nc.novel_id
                  LEFT JOIN Categories c ON nc.category_id = c.category_id
                  GROUP BY n.novel_id
                  ORDER BY favorite_count DESC
                  LIMIT ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        return $stmt->get_result();
    }
    
    // Lấy truyện theo lượt đọc
    public function getReadingRankings($limit = 10) {
        $query = "SELECT n.*, COUNT(DISTINCT rh.user_id) as read_count,
                  GROUP_CONCAT(c.name) as categories
                  FROM LightNovels n
                  LEFT JOIN Reading_History rh ON n.novel_id = rh.novel_id
                  LEFT JOIN Novel_Categories nc ON n.novel_id = nc.novel_id
                  LEFT JOIN Categories c ON nc.category_id = c.category_id
                  GROUP BY n.novel_id
                  ORDER BY read_count DESC
                  LIMIT ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        return $stmt->get_result();
    }
    
    // Lấy truyện bán chạy nhất
    public function getBestsellerRankings($limit = 10) {
        $query = "SELECT n.*, 
                  COUNT(DISTINCT p.purchase_id) as sold_count,
                  SUM(p.price - p.discount_applied) as total_revenue,
                  GROUP_CONCAT(DISTINCT c.name) as categories,
                  COUNT(DISTINCT f.novel_id) as favorite_count,
                  COUNT(DISTINCT rh.user_id) as read_count
                  FROM LightNovels n
                  LEFT JOIN Purchases p ON n.novel_id = p.novel_id AND p.status = 'completed'
                  LEFT JOIN Novel_Categories nc ON n.novel_id = nc.novel_id
                  LEFT JOIN Categories c ON nc.category_id = c.category_id
                  LEFT JOIN Favorites f ON n.novel_id = f.novel_id
                  LEFT JOIN Reading_History rh ON n.novel_id = rh.novel_id
                  GROUP BY n.novel_id, n.title, n.author, n.cover_image, n.price
                  HAVING sold_count > 0
                  ORDER BY sold_count DESC, total_revenue DESC
                  LIMIT ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        return $stmt->get_result();
    }
    
    // Lấy truyện mới cập nhật
    public function getNewUpdateRankings($limit = 10) {
        $query = "SELECT n.*, 
                  (SELECT MAX(created_at) FROM Chapters WHERE novel_id = n.novel_id) as last_update,
                  GROUP_CONCAT(c.name) as categories
                  FROM LightNovels n
                  LEFT JOIN Novel_Categories nc ON n.novel_id = nc.novel_id
                  LEFT JOIN Categories c ON nc.category_id = c.category_id
                  GROUP BY n.novel_id
                  HAVING last_update IS NOT NULL
                  ORDER BY last_update DESC
                  LIMIT ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        return $stmt->get_result();
    }
}
?>
