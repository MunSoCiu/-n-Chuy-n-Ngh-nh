<?php
session_start();
ob_start();
require_once '../../../app/config/config.php';
require_once '../../../includes/functions.php';
require_once '../../../app/Models/Admin/Admin_dashboard_model.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "/index.php");
    exit();
}

class Admin_dashboard_controller {
    private $model;
    
    public function __construct($connection) {
        $this->model = new Admin_dashboard_model($connection);
    }
    
    public function index() {
        // Handle comment deletion
        if (isset($_POST['delete_comment']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
            $this->deleteComment();
        }
        
        // Lấy tất cả dữ liệu thống kê
        $data = $this->getDashboardData();
        
        // Load view
        include '../../../app/views/Admin/Admin_dashboard_view.php';
    }
    
    private function deleteComment() {
        $comment_id = (int)$_POST['delete_comment'];
        
        if ($this->model->deleteComment($comment_id)) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success']);
            exit;
        } else {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Failed to delete comment']);
            exit;
        }
    }
    
    private function getDashboardData() {
        $stats = [];
        
        // Lấy các thống kê
        $stats['novels'] = $this->model->getNovelStats();
        $stats['users'] = $this->model->getUserStats();
        $stats['comments'] = $this->model->getCommentStats();
        $stats['revenue'] = $this->model->getRevenueStats();
        
        // Lấy dữ liệu chi tiết
        $recent_novels = $this->model->getRecentNovels();
        $new_users = $this->model->getNewUsers();
        $recent_comments = $this->model->getRecentComments();
        $top_novels = $this->model->getTopNovels();
        $monthly_revenue = $this->model->getMonthlyRevenue();
        
        return [
            'stats' => $stats,
            'recent_novels' => $recent_novels,
            'new_users' => $new_users,
            'recent_comments' => $recent_comments,
            'top_novels' => $top_novels,
            'monthly_revenue' => $monthly_revenue
        ];
    }
}

// Khởi tạo controller và chạy
$controller = new Admin_dashboard_controller($conn);
$controller->index();
?>