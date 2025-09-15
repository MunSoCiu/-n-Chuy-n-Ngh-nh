<?php
session_start();
require_once '../../../app/config/config.php';
require_once '../../../includes/functions.php';
require_once '../../../includes/novel_card.php';
require_once '../../../app/Models/User/Categories_model.php';

class Categories_controller {
    private $model;
    
    public function __construct($connection) {
        $this->model = new Categories_model($connection);
    }
    
    public function index() {
        try {
            // Lấy parameters
            $selected_category = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            $search = isset($_GET['search']) ? trim($_GET['search']) : '';
            $type = isset($_GET['type']) ? $_GET['type'] : 'all'; // free, paid, all
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = 12;
            $offset = ($page - 1) * $limit;
            
            // Lấy thông tin thể loại nếu có id
            $category_info = null;
            if ($selected_category) {
                $category_info = $this->model->getCategoryById($selected_category);
                
                if (!$category_info) {
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit();
                }
            }
            
            // Lấy dữ liệu
            $data = $this->getCategoriesData($selected_category, $search, $type, $limit, $offset);
            $data['category_info'] = $category_info;
            $data['type'] = $type;
            
            // Load view
            include '../../../app/views/User/Categories_view.php';
            
        } catch (Exception $e) {
            $_SESSION['error'] = "Có lỗi xảy ra: " . $e->getMessage();
            include '../../../app/views/User/Categories_view.php';
        }
    }
    
    private function getCategoriesData($selected_category, $search, $type, $limit, $offset) {
        // Lấy danh sách truyện
        $novels = $this->model->getNovels($selected_category, $search, $type, $limit, $offset);
        
        // Lấy tổng số truyện
        $total_novels = $this->model->getTotalNovels($selected_category, $search, $type);
        $total_pages = ceil($total_novels / $limit);
        
        // Lấy danh sách tất cả thể loại
        $categories = $this->model->getAllCategories();
        
        return [
            'novels' => $novels,
            'categories' => $categories,
            'selected_category' => $selected_category,
            'search' => $search,
            'page' => isset($_GET['page']) ? (int)$_GET['page'] : 1,
            'total_pages' => $total_pages,
            'total_novels' => $total_novels
        ];
    }
}

// Khởi tạo controller và chạy
$controller = new Categories_controller($conn);
$controller->index();
?>
