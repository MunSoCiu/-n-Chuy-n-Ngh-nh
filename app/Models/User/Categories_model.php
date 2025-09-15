<?php
class Categories_model {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    // Lấy thông tin thể loại theo ID
    public function getCategoryById($category_id) {
        $cat_query = "SELECT * FROM Categories WHERE category_id = ?";
        $stmt = $this->conn->prepare($cat_query);
        $stmt->bind_param("i", $category_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    // Lấy danh sách truyện theo thể loại với tìm kiếm và phân trang
    public function getNovels($selected_category = 0, $search = '', $type = 'all', $limit = 12, $offset = 0) {
        $query = "SELECT DISTINCT n.*, 
                  GROUP_CONCAT(DISTINCT c2.name) as categories,
                  COUNT(DISTINCT f.user_id) as favorite_count,
                  COUNT(DISTINCT rh.user_id) as read_count,
                  COUNT(DISTINCT p.purchase_id) as sold_count
                  FROM LightNovels n
                  LEFT JOIN Novel_Categories nc ON n.novel_id = nc.novel_id
                  LEFT JOIN Categories c2 ON nc.category_id = c2.category_id
                  LEFT JOIN Favorites f ON n.novel_id = f.novel_id
                  LEFT JOIN Reading_History rh ON n.novel_id = rh.novel_id
                  LEFT JOIN Purchases p ON n.novel_id = p.novel_id AND p.status = 'completed'";

        $where_conditions = [];
        
        if ($selected_category) {
            $query .= " JOIN Novel_Categories nc2 ON n.novel_id = nc2.novel_id";
            $where_conditions[] = "nc2.category_id = ?";
        }
        
        if ($search) {
            $where_conditions[] = "(n.title LIKE ? OR n.author LIKE ?)";
        }
        
        // Add price filtering
        if ($type === 'free') {
            $where_conditions[] = "n.price = 0";
        } elseif ($type === 'paid') {
            $where_conditions[] = "n.price > 0";
        }
        
        if (!empty($where_conditions)) {
            $query .= " WHERE " . implode(" AND ", $where_conditions);
        }
        
        $query .= " GROUP BY n.novel_id ORDER BY n.title";
        $query .= " LIMIT ? OFFSET ?";

        // Chuẩn bị parameters
        $types = "";
        $params = array();
        
        if ($selected_category) {
            $types .= "i";
            $params[] = $selected_category;
        }
        
        if ($search) {
            $search_param = "%$search%";
            $types .= "ss";
            $params[] = $search_param;
            $params[] = $search_param;
        }
        
        $types .= "ii";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }
        
        if (!empty($params)) {
            if (!$stmt->bind_param($types, ...$params)) {
                throw new Exception("Bind param failed: " . $stmt->error);
            }
        }
        
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        if ($result === false) {
            throw new Exception("Get result failed: " . $stmt->error);
        }
        
        return $result;
    }
    
    // Đếm tổng số truyện
    public function getTotalNovels($selected_category = 0, $search = '', $type = 'all') {
        $count_query = "SELECT COUNT(DISTINCT n.novel_id) as total 
                        FROM LightNovels n";
        
        $where_conditions = [];
        
        if ($selected_category) {
            $count_query .= " JOIN Novel_Categories nc ON n.novel_id = nc.novel_id";
            $where_conditions[] = "nc.category_id = ?";
        }
        
        if ($search) {
            $where_conditions[] = "(n.title LIKE ? OR n.author LIKE ?)";
        }
        
        // Add price filtering
        if ($type === 'free') {
            $where_conditions[] = "n.price = 0";
        } elseif ($type === 'paid') {
            $where_conditions[] = "n.price > 0";
        }
        
        if (!empty($where_conditions)) {
            $count_query .= " WHERE " . implode(" AND ", $where_conditions);
        }

        // Prepare parameters
        $types = "";
        $params = array();
        
        if ($selected_category) {
            $types .= "i";
            $params[] = $selected_category;
        }
        
        if ($search) {
            $search_param = "%$search%";
            $types .= "ss";
            $params[] = $search_param;
            $params[] = $search_param;
        }

        $stmt = $this->conn->prepare($count_query);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result ? (int)$result['total'] : 0;
    }
    
    // Lấy danh sách tất cả thể loại
    public function getAllCategories() {
        $query = "SELECT c.*, COUNT(nc.novel_id) as novel_count 
                  FROM Categories c
                  LEFT JOIN Novel_Categories nc ON c.category_id = nc.category_id
                  GROUP BY c.category_id
                  ORDER BY c.name";
        
        $result = $this->conn->query($query);
        if ($result === false) {
            throw new Exception("Query failed: " . $this->conn->error);
        }
        
        return $result;
    }
}
?>
