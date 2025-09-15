<?php
class Chapter_model {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    // Lấy thông tin chapter và novel
    public function getChapterById($chapter_id) {
        $query = "SELECT c.*, n.title as novel_title, n.novel_id,
                  (SELECT chapter_id FROM Chapters 
                   WHERE novel_id = c.novel_id AND chapter_id < c.chapter_id 
                   ORDER BY chapter_id DESC LIMIT 1) as prev_chapter,
                  (SELECT chapter_id FROM Chapters 
                   WHERE novel_id = c.novel_id AND chapter_id > c.chapter_id 
                   ORDER BY chapter_id ASC LIMIT 1) as next_chapter
                  FROM Chapters c
                  JOIN LightNovels n ON c.novel_id = n.novel_id
                  WHERE c.chapter_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $chapter_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    // Cập nhật lịch sử đọc
    public function updateReadingHistory($user_id, $novel_id, $chapter_id) {
        $query = "INSERT INTO Reading_History (user_id, novel_id, chapter_id) 
                  VALUES (?, ?, ?) 
                  ON DUPLICATE KEY UPDATE chapter_id = ?, last_read = CURRENT_TIMESTAMP";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("iiii", $user_id, $novel_id, $chapter_id, $chapter_id);
        return $stmt->execute();
    }
    
    // Format nội dung chương
    public function formatChapterContent($content) {
        // Loại bỏ các thẻ HTML
        $content = strip_tags($content);
        
        // Chuyển đổi các ký tự đặc biệt
        $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        // Xử lý các dấu xuống dòng
        $content = str_replace(["\r\n", "\r"], "\n", $content);
        
        // Xóa các khoảng trắng thừa
        $content = preg_replace('/\n\s*\n/', "\n\n", $content);
        
        // Xóa khoảng trắng ở đầu và cuối
        $content = trim($content);
        
        return $content;
    }
}
?>
