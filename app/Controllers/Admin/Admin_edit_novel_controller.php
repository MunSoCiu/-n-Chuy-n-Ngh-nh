<?php
session_start();
ob_start();
require_once '../../../app/config/config.php';
require_once '../../../includes/functions.php';
require_once '../../../app/Models/Admin/Admin_edit_novel_model.php';

class Admin_edit_novel_controller {
    private $model;
    
    public function __construct($connection) {
        $this->model = new Admin_edit_novel_model($connection);
    }
    
    public function index() {
        // Kiểm tra quyền admin
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header("Location: ../../../../index.php");
            exit();
        }
        
        $novel_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $current_tab = isset($_GET['tab']) ? $_GET['tab'] : 'info';
        
        // Lấy thông tin truyện
        $novel = $this->model->getNovel($novel_id);
        
        if (!$novel) {
            header("Location: ../../../app/Controllers/Admin/Admin_novels_controller.php");
            exit();
        }
        
        $success = '';
        $error = '';
        
        // Xử lý cập nhật thông tin truyện
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_novel'])) {
            try {
                $this->updateNovel($novel_id);
                $success = "Đã cập nhật thông tin truyện thành công!";
                // Reload novel data
                $novel = $this->model->getNovel($novel_id);
            } catch (Exception $e) {
                $error = "Có lỗi xảy ra: " . $e->getMessage();
            }
        }
        
        // Xử lý chapter actions
        if (isset($_POST['chapter_action'])) {
            try {
                $this->handleChapterAction($novel_id);
                $success = "Đã thực hiện thao tác chapter thành công!";
            } catch (Exception $e) {
                $error = "Có lỗi xảy ra: " . $e->getMessage();
            }
        }
        
        // Lấy dữ liệu
        $data = [
            'novel' => $novel,
            'chapters' => $this->model->getChapters($novel_id),
            'categories' => $this->model->getAllCategories(),
            'current_tab' => $current_tab,
            'success' => $success,
            'error' => $error
        ];
        
        // Load view
        include '../../../app/views/Admin/Admin_edit_novel_view.php';
    }
    
    private function updateNovel($novel_id) {
        $title = trim($_POST['title']);
        $author = trim($_POST['author']);
        $description = trim($_POST['description']);
        $status = $_POST['status'];
        $price = (float)$_POST['price'];
        $categories = isset($_POST['categories']) ? $_POST['categories'] : [];
        
        // Xử lý upload ảnh bìa
        $cover_image = null;
        if (isset($_FILES['cover_image']) && $_FILES['cover_image']['size'] > 0) {
            $cover_image = $this->handleImageUpload($novel_id);
        }
        
        $this->model->updateNovel($novel_id, $title, $author, $description, $status, $price, $cover_image, $categories);
    }
    
    private function handleImageUpload($novel_id) {
        $upload_dir = "../../../uploads/covers/";
        $filename = uniqid() . "_" . basename($_FILES['cover_image']['name']);
        $target_file = $upload_dir . $filename;
        
        if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $target_file)) {
            $cover_image = "uploads/covers/" . $filename;
            
            // Xóa ảnh cũ
            $old_cover = $this->model->getOldCoverImage($novel_id);
            if ($old_cover && file_exists("../../../" . $old_cover)) {
                unlink("../../../" . $old_cover);
            }
            
            return $cover_image;
        }
        
        return null;
    }
    
    private function handleChapterAction($novel_id) {
        switch ($_POST['chapter_action']) {
            case 'add_chapter':
                $title = $_POST['chapter_title'];
                $content = $_POST['chapter_content'];
                $this->model->addChapter($novel_id, $title, $content);
                break;
                
            case 'edit_chapter':
                $chapter_id = (int)$_POST['chapter_id'];
                $title = $_POST['chapter_title'];
                $content = $_POST['chapter_content'];
                $this->model->updateChapter($chapter_id, $title, $content);
                break;
                
            case 'delete_chapter':
                $chapter_id = (int)$_POST['chapter_id'];
                $this->model->deleteChapter($chapter_id);
                break;
        }
    }
}

// Khởi tạo controller và chạy
$controller = new Admin_edit_novel_controller($conn);
$controller->index();
?>
