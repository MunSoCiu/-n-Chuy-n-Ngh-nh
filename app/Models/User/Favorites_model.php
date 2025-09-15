<?php
class Favorites_model {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    // Lấy danh sách truyện yêu thích
    public function getFavorites($user_id) {
        $query = "SELECT n.*, GROUP_CONCAT(c.name) as categories,
                  (SELECT MAX(chapter_id) FROM Chapters WHERE novel_id = n.novel_id) as latest_chapter,
                  (SELECT chapter_id FROM Reading_History 
                   WHERE user_id = ? AND novel_id = n.novel_id) as last_read_chapter
                  FROM Favorites f
                  JOIN LightNovels n ON f.novel_id = n.novel_id
                  LEFT JOIN Novel_Categories nc ON n.novel_id = nc.novel_id
                  LEFT JOIN Categories c ON nc.category_id = c.category_id
                  WHERE f.user_id = ?
                  GROUP BY n.novel_id
                  ORDER BY n.title";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $user_id, $user_id);
        $stmt->execute();
        return $stmt->get_result();
    }
    
    // Xóa khỏi danh sách yêu thích
    public function removeFavorite($user_id, $novel_id) {
        $stmt = $this->conn->prepare("DELETE FROM Favorites WHERE user_id = ? AND novel_id = ?");
        $stmt->bind_param("ii", $user_id, $novel_id);
        return $stmt->execute();
    }
}
?>
