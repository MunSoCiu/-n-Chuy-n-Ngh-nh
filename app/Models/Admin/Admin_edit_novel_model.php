<?php
class Admin_edit_novel_model {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    // Lấy thông tin truyện
    public function getNovel($novel_id) {
        $query = "SELECT n.*, GROUP_CONCAT(nc.category_id) as category_ids
                  FROM LightNovels n
                  LEFT JOIN Novel_Categories nc ON n.novel_id = nc.novel_id
                  WHERE n.novel_id = ?
                  GROUP BY n.novel_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $novel_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if ($result && $result['category_ids']) {
            $result['category_ids'] = explode(',', $result['category_ids']);
        } else {
            $result['category_ids'] = [];
        }
        
        return $result;
    }
    
    // Lấy danh sách chapter
    public function getChapters($novel_id) {
        $query = "SELECT * FROM Chapters WHERE novel_id = ? ORDER BY chapter_id ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $novel_id);
        $stmt->execute();
        return $stmt->get_result();
    }
    
    // Lấy tất cả categories
    public function getAllCategories() {
        return $this->conn->query("SELECT * FROM Categories ORDER BY name");
    }
    
    // Cập nhật thông tin truyện
    public function updateNovel($novel_id, $title, $author, $description, $status, $price, $cover_image, $categories) {
        $this->conn->begin_transaction();
        try {
            $update_query = "UPDATE LightNovels SET 
                title = ?, author = ?, description = ?, status = ?, price = ?
                " . ($cover_image ? ", cover_image = ?" : "") . "
                WHERE novel_id = ?";
            
            $stmt = $this->conn->prepare($update_query);
            if ($cover_image) {
                $stmt->bind_param("ssssdsi", $title, $author, $description, $status, $price, $cover_image, $novel_id);
            } else {
                $stmt->bind_param("ssssdi", $title, $author, $description, $status, $price, $novel_id);
            }
            $stmt->execute();
            
            // Cập nhật categories
            $this->conn->query("DELETE FROM Novel_Categories WHERE novel_id = $novel_id");
            if (!empty($categories)) {
                $this->updateNovelCategories($novel_id, $categories);
            }
            
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }
    
    // Cập nhật categories của novel
    private function updateNovelCategories($novel_id, $categories) {
        foreach ($categories as $category_id) {
            $stmt = $this->conn->prepare("INSERT INTO Novel_Categories (novel_id, category_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $novel_id, $category_id);
            $stmt->execute();
        }
    }
    
    // Lấy ảnh bìa cũ
    public function getOldCoverImage($novel_id) {
        $query = "SELECT cover_image FROM LightNovels WHERE novel_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $novel_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result ? $result['cover_image'] : null;
    }
    
    // Chapter management methods
    public function addChapter($novel_id, $title, $content) {
        $stmt = $this->conn->prepare("INSERT INTO Chapters (novel_id, title, content) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $novel_id, $title, $content);
        return $stmt->execute();
    }
    
    public function updateChapter($chapter_id, $title, $content) {
        $stmt = $this->conn->prepare("UPDATE Chapters SET title = ?, content = ? WHERE chapter_id = ?");
        $stmt->bind_param("ssi", $title, $content, $chapter_id);
        return $stmt->execute();
    }
    
    public function deleteChapter($chapter_id) {
        $stmt = $this->conn->prepare("DELETE FROM Chapters WHERE chapter_id = ?");
        $stmt->bind_param("i", $chapter_id);
        return $stmt->execute();
    }
    
    public function getChapter($chapter_id) {
        $stmt = $this->conn->prepare("SELECT * FROM Chapters WHERE chapter_id = ?");
        $stmt->bind_param("i", $chapter_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}
?>
