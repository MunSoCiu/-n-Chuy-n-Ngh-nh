<?php
class Admin_novels_model {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    // Lấy danh sách novels với tìm kiếm và lọc
    public function getNovels($search = '', $category = '', $status = '') {
        $query = "SELECT n.*, GROUP_CONCAT(c.name) as categories 
                 FROM LightNovels n 
                 LEFT JOIN Novel_Categories nc ON n.novel_id = nc.novel_id
                 LEFT JOIN Categories c ON nc.category_id = c.category_id
                 WHERE 1=1";
        
        $params = [];
        $types = "";
        
        if (!empty($search)) {
            $query .= " AND (n.title LIKE ? OR n.author LIKE ?)";
            $search_param = "%$search%";
            $params[] = $search_param;
            $params[] = $search_param;
            $types .= "ss";
        }
        
        if (!empty($category)) {
            $query .= " AND nc.category_id = ?";
            $params[] = $category;
            $types .= "i";
        }
        
        if (!empty($status)) {
            $query .= " AND n.status = ?";
            $params[] = $status;
            $types .= "s";
        }
        
        $query .= " GROUP BY n.novel_id ORDER BY n.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt->get_result();
    }
    
    // Xóa novel và các bản ghi liên quan
    public function deleteNovel($novel_id) {
        $this->conn->begin_transaction();
        try {
            // Xóa các bản ghi liên quan trước
            $this->conn->query("DELETE FROM Favorites WHERE novel_id = $novel_id");
            $this->conn->query("DELETE FROM Comments WHERE novel_id = $novel_id");
            $this->conn->query("DELETE FROM Reading_History WHERE novel_id = $novel_id");
            $this->conn->query("DELETE FROM Purchases WHERE novel_id = $novel_id");
            $this->conn->query("DELETE FROM Novel_Categories WHERE novel_id = $novel_id");
            $this->conn->query("DELETE FROM Chapters WHERE novel_id = $novel_id");
            
            // Cuối cùng xóa truyện
            $this->conn->query("DELETE FROM LightNovels WHERE novel_id = $novel_id");
            
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }
    
    // Thêm novel mới
    public function addNovel($title, $author, $description, $status, $price, $cover_image, $categories) {
        $this->conn->begin_transaction();
        try {
            error_log("Starting addNovel transaction");
            error_log("Novel data: title=$title, author=$author, status=$status, price=$price, cover_image=$cover_image");
            
            $insert_query = "INSERT INTO LightNovels (title, author, description, status, price, cover_image) 
                           VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($insert_query);
            
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $this->conn->error);
            }
            
            $stmt->bind_param("ssssds", $title, $author, $description, $status, $price, $cover_image);
            
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            
            $novel_id = $this->conn->insert_id;
            error_log("Novel inserted with ID: $novel_id");
            
            // Thêm categories
            if (!empty($categories)) {
                error_log("Adding categories: " . print_r($categories, true));
                $this->updateNovelCategories($novel_id, $categories);
            }
            
            $this->conn->commit();
            error_log("Transaction committed successfully");
            return $novel_id;
        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("Transaction rolled back: " . $e->getMessage());
            throw $e;
        }
    }
    
    // Cập nhật novel
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
    
    // Lấy tất cả categories
    public function getAllCategories() {
        return $this->conn->query("SELECT * FROM Categories ORDER BY name");
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
}
?>
