<?php
class Search_model {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    // Tìm kiếm truyện theo từ khóa
    public function searchNovels($search, $limit = 12, $offset = 0) {
        $query = "SELECT n.*, GROUP_CONCAT(c.name) as categories
                  FROM LightNovels n
                  LEFT JOIN Novel_Categories nc ON n.novel_id = nc.novel_id
                  LEFT JOIN Categories c ON nc.category_id = c.category_id
                  WHERE n.title LIKE ? OR n.author LIKE ?
                  GROUP BY n.novel_id
                  LIMIT ? OFFSET ?";
        
        $search_param = "%$search%";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ssii", $search_param, $search_param, $limit, $offset);
        $stmt->execute();
        return $stmt->get_result();
    }
    
    // Đếm tổng số kết quả tìm kiếm
    public function getTotalSearchResults($search) {
        $count_query = "SELECT COUNT(DISTINCT n.novel_id) as total 
                       FROM LightNovels n
                       WHERE n.title LIKE ? OR n.author LIKE ?";
        $search_param = "%$search%";
        $stmt = $this->conn->prepare($count_query);
        $stmt->bind_param("ss", $search_param, $search_param);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc()['total'];
    }
}
?>
