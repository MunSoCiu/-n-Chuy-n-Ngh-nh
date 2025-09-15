<?php
class Admin_categories_model {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    // Kiểm tra tên thể loại đã tồn tại
    public function checkCategoryExists($name, $category_id = null) {
        $check_query = "SELECT category_id FROM Categories WHERE name = ? AND category_id != COALESCE(?, 0)";
        $stmt = $this->conn->prepare($check_query);
        $stmt->bind_param("si", $name, $category_id);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }
    
    // Thêm thể loại mới
    public function addCategory($name) {
        $insert_query = "INSERT INTO Categories (name) VALUES (?)";
        $stmt = $this->conn->prepare($insert_query);
        $stmt->bind_param("s", $name);
        return $stmt->execute();
    }
    
    // Cập nhật thể loại
    public function updateCategory($category_id, $name) {
        $update_query = "UPDATE Categories SET name = ? WHERE category_id = ?";
        $stmt = $this->conn->prepare($update_query);
        $stmt->bind_param("si", $name, $category_id);
        return $stmt->execute();
    }
    
    // Kiểm tra thể loại có đang được sử dụng không
    public function isCategoryInUse($category_id) {
        $check_query = "SELECT COUNT(*) as count FROM Novel_Categories WHERE category_id = ?";
        $stmt = $this->conn->prepare($check_query);
        $stmt->bind_param("i", $category_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['count'] > 0;
    }
    
    // Xóa thể loại
    public function deleteCategory($category_id) {
        $delete_query = "DELETE FROM Categories WHERE category_id = ?";
        $stmt = $this->conn->prepare($delete_query);
        $stmt->bind_param("i", $category_id);
        return $stmt->execute();
    }
    
    // Lấy danh sách thể loại với phân trang và tìm kiếm
    public function getCategories($search = '', $limit = 10, $offset = 0) {
        $query = "SELECT c.*, COUNT(nc.novel_id) as novel_count 
                  FROM Categories c 
                  LEFT JOIN Novel_Categories nc ON c.category_id = nc.category_id";
        
        if ($search) {
            $query .= " WHERE c.name LIKE ?";
            $search_param = "%$search%";
        }
        
        $query .= " GROUP BY c.category_id ORDER BY c.name LIMIT ? OFFSET ?";
        
        $stmt = $this->conn->prepare($query);
        if ($search) {
            $stmt->bind_param("sii", $search_param, $limit, $offset);
        } else {
            $stmt->bind_param("ii", $limit, $offset);
        }
        $stmt->execute();
        return $stmt->get_result();
    }
    
    // Đếm tổng số thể loại
    public function getTotalCategories($search = '') {
        $count_query = "SELECT COUNT(*) as total FROM Categories";
        if ($search) {
            $count_query .= " WHERE name LIKE ?";
            $search_param = "%$search%";
        }
        
        $stmt = $this->conn->prepare($count_query);
        if ($search) {
            $stmt->bind_param("s", $search_param);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc()['total'];
    }
    
    // Lấy thông tin thể loại theo ID
    public function getCategoryById($category_id) {
        $query = "SELECT * FROM Categories WHERE category_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $category_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}
?>