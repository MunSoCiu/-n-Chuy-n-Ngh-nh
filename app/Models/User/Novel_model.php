<?php
class Novel_model {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    // Lấy thông tin chi tiết của novel
    public function getNovelById($novel_id) {
        $query = "SELECT n.*, GROUP_CONCAT(c.name) as categories 
                  FROM LightNovels n 
                  LEFT JOIN Novel_Categories nc ON n.novel_id = nc.novel_id
                  LEFT JOIN Categories c ON nc.category_id = c.category_id
                  WHERE n.novel_id = ?
                  GROUP BY n.novel_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $novel_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    // Kiểm tra xem người dùng đã mua truyện chưa
    public function checkPurchase($user_id, $novel_id) {
        $check_purchase = $this->conn->prepare("SELECT 1 FROM Purchases WHERE user_id = ? AND novel_id = ?");
        $check_purchase->bind_param("ii", $user_id, $novel_id);
        $check_purchase->execute();
        return $check_purchase->get_result()->num_rows > 0;
    }
    
    // Mua truyện
    public function purchaseNovel($user_id, $novel_id, $price, $discount = 0) {
        $purchase_stmt = $this->conn->prepare("INSERT INTO Purchases (user_id, novel_id, price, discount_applied) VALUES (?, ?, ?, ?)");
        $purchase_stmt->bind_param("iidd", $user_id, $novel_id, $price, $discount);
        return $purchase_stmt->execute();
    }
    
    // Lấy danh sách chapter
    public function getChapters($novel_id) {
        $query = "SELECT chapter_id, title, created_at 
                  FROM Chapters 
                  WHERE novel_id = ? 
                  ORDER BY chapter_id ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $novel_id);
        $stmt->execute();
        return $stmt->get_result();
    }
    
    // Kiểm tra trạng thái yêu thích
    public function checkFavorite($user_id, $novel_id) {
        $fav_check = $this->conn->prepare("SELECT 1 FROM Favorites WHERE user_id = ? AND novel_id = ?");
        $fav_check->bind_param("ii", $user_id, $novel_id);
        $fav_check->execute();
        return $fav_check->get_result()->num_rows > 0;
    }
    
    // Lấy số lượt yêu thích
    public function getFavoriteCount($novel_id) {
        $count_query = "SELECT COUNT(*) as count FROM Favorites WHERE novel_id = ?";
        $stmt = $this->conn->prepare($count_query);
        $stmt->bind_param("i", $novel_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc()['count'];
    }
    
    // Thêm/xóa yêu thích
    public function toggleFavorite($user_id, $novel_id, $is_favorited) {
        if ($is_favorited) {
            $stmt = $this->conn->prepare("DELETE FROM Favorites WHERE user_id = ? AND novel_id = ?");
            $stmt->bind_param("ii", $user_id, $novel_id);
            return $stmt->execute() ? 'removed' : false;
        } else {
            $stmt = $this->conn->prepare("INSERT INTO Favorites (user_id, novel_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $user_id, $novel_id);
            return $stmt->execute() ? 'added' : false;
        }
    }
    
    // Thêm chapter mới (admin only)
    public function addChapter($novel_id, $title, $content) {
        $stmt = $this->conn->prepare("INSERT INTO Chapters (novel_id, title, content) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $novel_id, $title, $content);
        return $stmt->execute();
    }
    
    // Lấy số lượt đọc
    public function getReadCount($novel_id) {
        $read_count_query = "SELECT COUNT(DISTINCT user_id) as count FROM Reading_History WHERE novel_id = ?";
        $stmt = $this->conn->prepare($read_count_query);
        $stmt->bind_param("i", $novel_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc()['count'];
    }
    
    // Thêm lịch sử đọc
    public function addReadingHistory($user_id, $novel_id, $chapter_id = null) {
        // Kiểm tra xem đã có lịch sử đọc chưa
        $check_query = "SELECT 1 FROM Reading_History WHERE user_id = ? AND novel_id = ?";
        $stmt = $this->conn->prepare($check_query);
        $stmt->bind_param("ii", $user_id, $novel_id);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows == 0) {
            // Thêm mới
            $insert_query = "INSERT INTO Reading_History (user_id, novel_id, last_chapter_id) VALUES (?, ?, ?)";
            $stmt = $this->conn->prepare($insert_query);
            $stmt->bind_param("iii", $user_id, $novel_id, $chapter_id);
            return $stmt->execute();
        } else {
            // Cập nhật
            $update_query = "UPDATE Reading_History SET last_chapter_id = ?, last_read = NOW() WHERE user_id = ? AND novel_id = ?";
            $stmt = $this->conn->prepare($update_query);
            $stmt->bind_param("iii", $chapter_id, $user_id, $novel_id);
            return $stmt->execute();
        }
    }
    
    // Lấy danh sách comment của novel
    public function getComments($novel_id) {
        $query = "SELECT c.*, u.username, u.avatar_url 
                  FROM Comments c 
                  JOIN Users u ON c.user_id = u.user_id 
                  WHERE c.novel_id = ? 
                  ORDER BY c.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $novel_id);
        $stmt->execute();
        return $stmt->get_result();
    }
    
    // Thêm comment mới
    public function addComment($user_id, $novel_id, $content) {
        $query = "INSERT INTO Comments (user_id, novel_id, content) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("iis", $user_id, $novel_id, $content);
        return $stmt->execute();
    }
    
    // Đếm số lượng comment
    public function getCommentCount($novel_id) {
        $query = "SELECT COUNT(*) as count FROM Comments WHERE novel_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $novel_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc()['count'];
    }
}
?>
