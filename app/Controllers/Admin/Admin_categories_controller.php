<?php
session_start();
ob_start();
require_once '../../../app/config/config.php';
require_once '../../../includes/functions.php';
require_once '../../../app/Models/Admin/Admin_categories_model.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "/index.php");
    exit();
}

class Admin_categories_controller {
    private $model;
    
    public function __construct($connection) {
        $this->model = new Admin_categories_model($connection);
    }
    
    public function index() {
        // Xử lý thêm/sửa thể loại
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_category'])) {
            $this->saveCategory();
        }
        
        // Xử lý xóa thể loại
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_category'])) {
            $this->deleteCategory();
        }
        
        // Lấy dữ liệu để hiển thị
        $data = $this->getCategoriesData();
        
        // Load view
        include '../../../app/views/Admin/Admin_categories_view.php';
    }
    
    private function saveCategory() {
        $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : null;
        $name = trim($_POST['name']);
        $error = false;
        
        // Validation
        if (empty($name)) {
            $_SESSION['error'] = "Tên thể loại không được để trống!";
            $error = true;
        } elseif (strlen($name) > 100) {
            $_SESSION['error'] = "Tên thể loại không được vượt quá 100 ký tự!";
            $error = true;
        }
        
        if (!$error) {
            try {
                // Kiểm tra tên thể loại đã tồn tại chưa
                if ($this->model->checkCategoryExists($name, $category_id)) {
                    $_SESSION['error'] = "Tên thể loại đã tồn tại!";
                } else {
                    if ($category_id) {
                        // Cập nhật thể loại
                        if ($this->model->updateCategory($category_id, $name)) {
                            $_SESSION['success'] = "Đã cập nhật thể loại thành công!";
                            header("Location: " . $_SERVER['PHP_SELF']);
                            exit();
                        } else {
                            $_SESSION['error'] = "Không thể cập nhật thể loại!";
                        }
                    } else {
                        // Thêm thể loại mới
                        if ($this->model->addCategory($name)) {
                            $_SESSION['success'] = "Đã thêm thể loại mới thành công!";
                            header("Location: " . $_SERVER['PHP_SELF']);
                            exit();
                        } else {
                            $_SESSION['error'] = "Không thể thêm thể loại mới!";
                        }
                    }
                }
            } catch (Exception $e) {
                $_SESSION['error'] = "Có lỗi xảy ra: " . $e->getMessage();
            }
        }
    }
    
    private function deleteCategory() {
        $category_id = (int)$_POST['category_id'];
        
        try {
            // Kiểm tra xem thể loại có đang được sử dụng không
            if ($this->model->isCategoryInUse($category_id)) {
                $_SESSION['error'] = "Không thể xóa thể loại đang được sử dụng!";
            } else {
                if ($this->model->deleteCategory($category_id)) {
                    $_SESSION['success'] = "Đã xóa thể loại thành công!";
                } else {
                    $_SESSION['error'] = "Không thể xóa thể loại!";
                }
            }
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } catch (Exception $e) {
            $_SESSION['error'] = "Có lỗi xảy ra: " . $e->getMessage();
        }
    }
    
    private function getCategoriesData() {
        // Xử lý tìm kiếm và phân trang
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        // Lấy dữ liệu
        $categories = $this->model->getCategories($search, $limit, $offset);
        $total_records = $this->model->getTotalCategories($search);
        $total_pages = ceil($total_records / $limit);
        
        // Lấy thông tin thể loại cần sửa
        $edit_category = null;
        if (isset($_GET['edit'])) {
            $edit_id = (int)$_GET['edit'];
            $edit_category = $this->model->getCategoryById($edit_id);
        }
        
        return [
            'categories' => $categories,
            'search' => $search,
            'page' => $page,
            'total_pages' => $total_pages,
            'edit_category' => $edit_category
        ];
    }
}

// Khởi tạo controller và chạy
$controller = new Admin_categories_controller($conn);
$controller->index();
?>