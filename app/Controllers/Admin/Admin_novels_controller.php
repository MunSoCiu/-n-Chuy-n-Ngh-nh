<?php
session_start();
ob_start();
require_once '../../../app/config/config.php';
require_once '../../../includes/functions.php';
require_once '../../../app/Models/Admin/Admin_novels_model.php';

class Admin_novels_controller {
    private $model;
    
    public function __construct($connection) {
        $this->model = new Admin_novels_model($connection);
    }
    
    public function index() {
        // Kiểm tra quyền admin
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header("Location: ../../../../index.php");
            exit();
        }
        
        $success = '';
        $error = '';
        
        // Debug: Log all requests
        error_log("Request method: " . $_SERVER['REQUEST_METHOD']);
        error_log("POST data keys: " . print_r(array_keys($_POST), true));
        
        // Xử lý xóa truyện
        if (isset($_POST['delete_novel'])) {
            $novel_id = (int)$_POST['delete_novel'];
            
            try {
                $this->model->deleteNovel($novel_id);
                $success = "Đã xóa sách thành công!";
            } catch (Exception $e) {
                $error = "Có lỗi xảy ra khi xóa sách: " . $e->getMessage();
            }
        }
        
        // Xử lý thêm/sửa/xóa chapter
        if (isset($_POST['chapter_action'])) {
            $this->handleChapterAction();
            return;
        }
        
        // Xử lý thêm/sửa truyện
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_novel'])) {
            error_log("=== SAVE NOVEL REQUEST DETECTED ===");
            try {
                // Debug: Log POST data
                error_log("POST data: " . print_r($_POST, true));
                error_log("FILES data: " . print_r($_FILES, true));
                
                $this->saveNovel();
                $success = "Đã lưu truyện thành công!";
                error_log("=== NOVEL SAVED SUCCESSFULLY ===");
            } catch (Exception $e) {
                $error = "Có lỗi xảy ra: " . $e->getMessage();
                error_log("Error in saveNovel: " . $e->getMessage());
                error_log("Stack trace: " . $e->getTraceAsString());
            }
        } else {
            error_log("No save_novel POST detected. POST keys: " . print_r(array_keys($_POST), true));
        }
        
        // Lấy dữ liệu
        $search = $_GET['search'] ?? '';
        $category = $_GET['category'] ?? '';
        $status = $_GET['status'] ?? '';
        
        $data = [
            'novels' => $this->model->getNovels($search, $category, $status),
            'categories' => $this->model->getAllCategories(),
            'search' => $search,
            'category' => $category,
            'status' => $status,
            'success' => $success,
            'error' => $error
        ];
        
        // Load view
        include '../../../app/views/Admin/Admin_novels_view.php';
    }
    
    private function handleChapterAction() {
        $novel_id = (int)$_POST['novel_id'];
        
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
        
        header("Location: ../../../app/Controllers/Admin/Admin_edit_novel_controller.php?id=$novel_id&tab=chapters");
        exit();
    }
    
    private function saveNovel() {
        // Validate required fields
        if (empty($_POST['title']) || empty($_POST['author']) || empty($_POST['status']) || !isset($_POST['price'])) {
            throw new Exception("Vui lòng điền đầy đủ thông tin bắt buộc.");
        }
        
        $novel_id = isset($_POST['novel_id']) ? (int)$_POST['novel_id'] : null;
        $title = trim($_POST['title']);
        $author = trim($_POST['author']);
        $description = trim($_POST['description']);
        $status = $_POST['status'];
        $price = (float)$_POST['price'];
        $categories = isset($_POST['categories']) ? $_POST['categories'] : [];
        
        // Debug validation
        error_log("Validating novel data: title=$title, author=$author, status=$status, price=$price");
        error_log("Categories: " . print_r($categories, true));
        
        // Xử lý upload ảnh bìa
        $cover_image = null;
        if (isset($_FILES['cover_image']) && $_FILES['cover_image']['size'] > 0) {
            $upload_dir = "../../../uploads/covers/";
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $filename = uniqid() . "_" . basename($_FILES['cover_image']['name']);
            $target_file = $upload_dir . $filename;
            
            if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $target_file)) {
                $cover_image = "uploads/covers/" . $filename;
                
                // Xóa ảnh cũ nếu đang sửa truyện
                if ($novel_id) {
                    $old_cover = $this->model->getOldCoverImage($novel_id);
                    if ($old_cover && file_exists("../../../" . $old_cover)) {
                        unlink("../../../" . $old_cover);
                    }
                }
            }
        }
        
        // Sử dụng direct database connection như modules/Admin/novels.php
        global $conn;
        
        // Bắt đầu transaction
        $conn->begin_transaction();
        try {
            if ($novel_id) {
                // Cập nhật truyện
                $update_query = "UPDATE LightNovels SET 
                    title = ?, author = ?, description = ?, status = ?, price = ?
                    " . ($cover_image ? ", cover_image = ?" : "") . "
                    WHERE novel_id = ?";
                
                $stmt = $conn->prepare($update_query);
                if ($cover_image) {
                    $stmt->bind_param("ssssdsi", $title, $author, $description, $status, $price, $cover_image, $novel_id);
                } else {
                    $stmt->bind_param("ssssdi", $title, $author, $description, $status, $price, $novel_id);
                }
                $stmt->execute();
            } else {
                // Thêm truyện mới
                $insert_query = "INSERT INTO LightNovels (title, author, description, status, price, cover_image) 
                               VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($insert_query);
                $stmt->bind_param("ssssds", $title, $author, $description, $status, $price, $cover_image);
                $stmt->execute();
                $novel_id = $conn->insert_id;
            }

            // Xóa categories cũ nếu đang sửa truyện
            if ($novel_id) {
                $delete_categories = "DELETE FROM Novel_Categories WHERE novel_id = ?";
                $stmt = $conn->prepare($delete_categories);
                $stmt->bind_param("i", $novel_id);
                $stmt->execute();
            }

            // Thêm categories mới
            if (!empty($categories)) {
                $category_values = [];
                $category_params = [];
                
                foreach ($categories as $category_id) {
                    $category_values[] = "(?, ?)";
                    $category_params[] = $novel_id;
                    $category_params[] = $category_id;
                }
                
                $category_query = "INSERT INTO Novel_Categories (novel_id, category_id) VALUES " . 
                                implode(", ", $category_values);
                $stmt = $conn->prepare($category_query);
                
                $types = str_repeat('ii', count($categories));
                $stmt->bind_param($types, ...$category_params);
                $stmt->execute();
            }

            $conn->commit();
            error_log("Novel saved successfully with ID: " . $novel_id);
        } catch (Exception $e) {
            $conn->rollback();
            error_log("Database error: " . $e->getMessage());
            throw $e;
        }
    }
    
    private function handleImageUpload($novel_id = null) {
        $upload_dir = "../../../uploads/covers/";
        
        // Validate file upload
        if ($_FILES['cover_image']['error'] !== UPLOAD_ERR_OK) {
            $upload_errors = [
                UPLOAD_ERR_INI_SIZE => 'File quá lớn (vượt quá upload_max_filesize)',
                UPLOAD_ERR_FORM_SIZE => 'File quá lớn (vượt quá MAX_FILE_SIZE)',
                UPLOAD_ERR_PARTIAL => 'File chỉ được upload một phần',
                UPLOAD_ERR_NO_FILE => 'Không có file nào được upload',
                UPLOAD_ERR_NO_TMP_DIR => 'Thiếu thư mục tạm',
                UPLOAD_ERR_CANT_WRITE => 'Không thể ghi file lên disk',
                UPLOAD_ERR_EXTENSION => 'Upload bị dừng bởi extension'
            ];
            $error_msg = $upload_errors[$_FILES['cover_image']['error']] ?? 'Lỗi upload không xác định';
            throw new Exception("Lỗi upload ảnh: " . $error_msg);
        }
        
        // Validate file type
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $file_type = $_FILES['cover_image']['type'];
        if (!in_array($file_type, $allowed_types)) {
            throw new Exception("Định dạng file không được hỗ trợ. Chỉ chấp nhận: JPG, PNG, GIF");
        }
        
        $filename = uniqid() . "_" . basename($_FILES['cover_image']['name']);
        $target_file = $upload_dir . $filename;
        
        error_log("Attempting to upload file to: " . $target_file);
        
        if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $target_file)) {
            $cover_image = "uploads/covers/" . $filename;
            
            // Xóa ảnh cũ nếu đang sửa truyện
            if ($novel_id) {
                $old_cover = $this->model->getOldCoverImage($novel_id);
                if ($old_cover && file_exists("../../../" . $old_cover)) {
                    unlink("../../../" . $old_cover);
                }
            }
            
            error_log("File uploaded successfully: " . $cover_image);
            return $cover_image;
        } else {
            error_log("Failed to move uploaded file from " . $_FILES['cover_image']['tmp_name'] . " to " . $target_file);
            throw new Exception("Không thể di chuyển file upload đến thư mục đích.");
        }
    }
}

// Khởi tạo controller và chạy
$controller = new Admin_novels_controller($conn);
$controller->index();
?>
