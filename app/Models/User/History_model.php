<?php
class History_model {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    // Lấy tổng số bản ghi lịch sử đọc
    public function getTotalHistory($user_id) {
        $count_query = "SELECT COUNT(*) as total FROM Reading_History WHERE user_id = ?";
        $stmt = $this->conn->prepare($count_query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc()['total'];
    }
    
    // Lấy lịch sử đọc chi tiết với phân trang
    public function getHistory($user_id, $limit = 12, $offset = 0) {
        $query = "SELECT rh.*, n.title as novel_title, n.cover_image, n.author,
                  c.title as chapter_title, c.chapter_id,
                  (SELECT COUNT(*) FROM Chapters WHERE novel_id = n.novel_id) as total_chapters
                  FROM Reading_History rh
                  JOIN LightNovels n ON rh.novel_id = n.novel_id
                  JOIN Chapters c ON rh.chapter_id = c.chapter_id
                  WHERE rh.user_id = ?
                  ORDER BY rh.last_read DESC
                  LIMIT ? OFFSET ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("iii", $user_id, $limit, $offset);
        $stmt->execute();
        return $stmt->get_result();
    }
    
    // Xóa lịch sử đọc
    public function deleteHistory($user_id, $novel_id) {
        $stmt = $this->conn->prepare("DELETE FROM Reading_History WHERE user_id = ? AND novel_id = ?");
        $stmt->bind_param("ii", $user_id, $novel_id);
        return $stmt->execute();
    }
}
?>
