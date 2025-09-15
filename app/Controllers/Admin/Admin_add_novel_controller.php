<?php
session_start();
ob_start();
require_once '../../../app/config/config.php';
require_once '../../../includes/functions.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "/index.php");
    exit();
}

$success = '';
$error = '';

// Xử lý thêm truyện
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_novel'])) {
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $description = trim($_POST['description']);
    $status = $_POST['status'];
    $price = (float)$_POST['price'];
    $categories = isset($_POST['categories']) ? $_POST['categories'] : [];

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
        }
    }

    // Bắt đầu transaction
    $conn->begin_transaction();
    try {
        // Thêm truyện mới
        $insert_query = "INSERT INTO LightNovels (title, author, description, status, price, cover_image) 
                       VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("ssssds", $title, $author, $description, $status, $price, $cover_image);
        $stmt->execute();
        $novel_id = $conn->insert_id;

        // Thêm categories
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
        $_SESSION['success'] = "Đã thêm sách thành công!";
        header("Location: Admin_novels_controller.php");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Có lỗi xảy ra: " . $e->getMessage();
    }
}

// Lấy danh sách thể loại
$categories = $conn->query("SELECT * FROM Categories ORDER BY name");

// Load view
include '../../../app/views/Admin/Admin_add_novel_view.php';
?>
