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
        // Xử lý xoá bình luận
        if (isset($_POST['delete_comment']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
            $this->deleteComment();
        }
        
        // Lấy tất cả dữ liệu thống kê
        $data = $this->getDashboardData();

        // 👉 Tạo biến riêng để tránh undefined variable
        $todayReads = $data['stats']['today']['reads'] ?? 0;
        $todayRevenue = $data['stats']['today']['revenue'] ?? 0;
        
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
        
        // 👉 Thêm lượt đọc và doanh thu trong ngày
        $stats['today'] = [
            'reads'   => $this->model->getTodayReads(),
            'revenue' => $this->model->getTodayRevenue()
        ];
    
        // Lấy dữ liệu chi tiết
        $recent_novels   = $this->model->getRecentNovels();
        $new_users       = $this->model->getNewUsers();
        $recent_comments = $this->model->getRecentComments();
        $top_novels      = $this->model->getTopNovels();
        $year = date("Y"); // hoặc lấy từ request nếu muốn linh động
        $monthly_revenue = $this->model->getMonthlyRevenue($year);

        
        return [
            'stats' => $stats,
            'recent_novels' => $recent_novels,
            'new_users' => $new_users,
            'recent_comments' => $recent_comments,
            'top_novels' => $top_novels,
            'monthly_revenue' => $monthly_revenue
        ];
    }

    // ========================
    // API: trả dữ liệu JSON cho Chart.js
    // ========================
    public function statsByMonth() {
        $year = isset($_GET['year']) ? intval($_GET['year']) : intval(date("Y"));
        $month = isset($_GET['month']) ? intval($_GET['month']) : intval(date("m"));

        $daily = $this->model->getDailyStatsByMonth($year, $month);

        // Tổng tháng hiện tại
        $current = $this->model->getMonthlySummary($year, $month);

        // Tháng trước
        $prevMonth = $month - 1;
        $prevYear = $year;
        if ($prevMonth === 0) {
            $prevMonth = 12;
            $prevYear -= 1;
        }
        $prev = $this->model->getMonthlySummary($prevYear, $prevMonth);

        // % thay đổi
        $readsChange = ($prev['reads'] > 0) 
            ? (($current['reads'] - $prev['reads']) / $prev['reads']) * 100 
            : (($current['reads'] > 0) ? 100 : 0);

        $revenueChange = ($prev['revenue'] > 0) 
            ? (($current['revenue'] - $prev['revenue']) / $prev['revenue']) * 100 
            : (($current['revenue'] > 0) ? 100 : 0);

        // 👉 Format dữ liệu để Chart.js đọc được
        $readsFormatted = [];
        foreach ($daily['reads'] as $day => $count) {
            $readsFormatted[] = ["day" => (int)$day, "count" => (int)$count];
        }

        $revenueFormatted = [];
        foreach ($daily['revenue'] as $day => $amount) {
            $revenueFormatted[] = ["day" => (int)$day, "amount" => (float)$amount];
        }

        $result = [
            'reads' => $readsFormatted,
            'revenue' => $revenueFormatted,
            'totalReads' => (int)$current['reads'],
            'totalRevenue' => (float)$current['revenue'],
            'readsChange' => round($readsChange, 2),
            'revenueChange' => round($revenueChange, 2),
            'year' => $year,
            'month' => $month
        ];

        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }

    // API: doanh thu theo năm (dùng cho biểu đồ cột theo tháng)
    // ========================
public function statsByYear() {
    $year = isset($_GET['year']) ? intval($_GET['year']) : intval(date("Y"));

    $months = $this->model->getMonthlyRevenue($year);

    $formatted = [];
    foreach ($months as $m => $row) {
        $formatted[] = [
            "month" => (int)$m,
            "amount" => (float)$row['revenue']  // lấy đúng cột revenue
        ];
    }

    $result = ["months" => $formatted, "year" => $year];

    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
}

}



// ===============================
// Router đơn giản
$controller = new Admin_dashboard_controller($conn);
$action = $_GET['action'] ?? 'index';

if (method_exists($controller, $action)) {
    $controller->$action();
} else {
    $controller->index();
}
