<?php
session_start();
require_once '../../../app/config/config.php';
require_once '../../../includes/functions.php';
require_once '../../../app/Models/Admin/Admin_purchases_model.php';

class Admin_purchases_controller {
    private $model;
    
    public function __construct($connection) {
        $this->model = new Admin_purchases_model($connection);
    }
    
    public function index() {
        // Kiểm tra quyền admin
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header("Location: ../../../../index.php");
            exit();
        }
        
        $success = '';
        $error = '';
        
        // Xử lý xóa hóa đơn
        if (isset($_POST['action']) && $_POST['action'] === 'delete') {
            $purchase_id = (int)$_POST['purchase_id'];
            if ($this->model->deletePurchase($purchase_id)) {
                $success = "Đã xóa hóa đơn thành công!";
            } else {
                $error = "Có lỗi xảy ra khi xóa hóa đơn!";
            }
        }
        
        // Xử lý cập nhật trạng thái
        if (isset($_POST['action']) && $_POST['action'] === 'update_status') {
            $purchase_id = (int)$_POST['purchase_id'];
            $status = $_POST['status'];
            if ($this->model->updatePurchaseStatus($purchase_id, $status)) {
                $success = "Đã cập nhật trạng thái thành công!";
            } else {
                $error = "Có lỗi xảy ra khi cập nhật trạng thái!";
            }
        }
        
        // Lấy dữ liệu tìm kiếm
        $search      = $_GET['search'] ?? '';
        $search_type = $_GET['search_type'] ?? 'novel';
       $date = $_GET['search_date'] ?? '';
        // tìm theo 1 ngày
        $from_date   = $_GET['from_date'] ?? '';   // tìm theo khoảng
        $to_date     = $_GET['to_date'] ?? '';
        
        // Lấy danh sách hóa đơn theo điều kiện
        $purchases = $this->model->getPurchases($search, $search_type, $date, $from_date, $to_date);
        
        $data = [
            'purchases'   => $purchases,
            'stats'       => $this->model->getPurchaseStats(),
            'top_novels'  => $this->model->getTopSellingNovels(),
            'search'      => $search,
            'search_type' => $search_type,
            'date'        => $date,
            'from_date'   => $from_date,
            'to_date'     => $to_date,
            'success'     => $success,
            'error'       => $error
        ];
        
        // Load view
        include '../../../app/views/Admin/Admin_purchases_view.php';
    }
}

// Khởi tạo controller và chạy
$controller = new Admin_purchases_controller($conn);
$controller->index();
