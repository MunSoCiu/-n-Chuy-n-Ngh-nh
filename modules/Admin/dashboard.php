<?php
session_start();
ob_start();
require_once '../../app/config/config.php';
require_once '../../includes/functions.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit();
}

// Lấy thống kê tổng quan
$stats = [];

// Tổng số truyện
$novel_query = "SELECT 
    COUNT(*) as total_novels,
    COUNT(CASE WHEN status = 'Đang tiến hành' THEN 1 END) as ongoing,
    COUNT(CASE WHEN status = 'Đã hoàn thành' THEN 1 END) as completed,
    COUNT(CASE WHEN status = 'Đã hủy bỏ' THEN 1 END) as dropped
    FROM LightNovels";
$stats['novels'] = $conn->query($novel_query)->fetch_assoc();

// Tổng số chapter
$stats['chapters'] = $conn->query("SELECT COUNT(*) as total FROM Chapters")->fetch_assoc();

// Tổng số người dùng
$user_query = "SELECT 
    COUNT(*) as total_users,
    COUNT(CASE WHEN role = 'admin' THEN 1 END) as admin_count,
    COUNT(CASE WHEN role = 'user' THEN 1 END) as user_count
    FROM Users";
$stats['users'] = $conn->query($user_query)->fetch_assoc();

// Tổng số bình luận
$stats['comments'] = $conn->query("SELECT COUNT(*) as total FROM Comments")->fetch_assoc();

// Truyện mới thêm gần đây
$recent_novels = $conn->query("
    SELECT n.*, COUNT(c.chapter_id) as chapter_count 
    FROM LightNovels n
    LEFT JOIN Chapters c ON n.novel_id = c.novel_id
    GROUP BY n.novel_id
    ORDER BY n.created_at DESC 
    LIMIT 5
");

// Người dùng mới đăng ký
$new_users = $conn->query("
    SELECT * FROM Users 
    ORDER BY created_at DESC 
    LIMIT 5
");

// Bình luận mới nhất
$recent_comments = $conn->query("
    SELECT c.*, u.username, n.title as novel_title
    FROM Comments c
    JOIN Users u ON c.user_id = u.user_id
    JOIN LightNovels n ON c.novel_id = n.novel_id
    ORDER BY c.created_at DESC
    LIMIT 5
");

// Thống kê doanh thu
$revenue_query = "SELECT 
    SUM(price - discount_applied) as total_revenue,
    COUNT(DISTINCT novel_id) as total_novels_sold,
    COUNT(*) as total_purchases,
    SUM(CASE WHEN MONTH(purchase_date) = MONTH(CURRENT_DATE) 
             AND YEAR(purchase_date) = YEAR(CURRENT_DATE) 
        THEN price - discount_applied ELSE 0 END) as current_month_revenue,
    SUM(CASE WHEN MONTH(purchase_date) = MONTH(CURRENT_DATE) 
             AND YEAR(purchase_date) = YEAR(CURRENT_DATE) 
        THEN 1 ELSE 0 END) as current_month_sales
    FROM Purchases
    WHERE status = 'completed'";
$stats['revenue'] = $conn->query($revenue_query)->fetch_assoc();

// Top 5 truyện bán chạy nhất
$top_novels_query = "SELECT 
    n.novel_id,
    n.title,
    COUNT(*) as purchase_count,
    SUM(p.price - p.discount_applied) as total_revenue
    FROM Purchases p
    JOIN LightNovels n ON p.novel_id = n.novel_id
    WHERE p.status = 'completed'
    GROUP BY n.novel_id, n.title
    ORDER BY purchase_count DESC
    LIMIT 5";
$top_novels = $conn->query($top_novels_query);

// Thống kê doanh thu theo tháng (6 tháng gần nhất)
$monthly_revenue_query = "SELECT 
    DATE_FORMAT(purchase_date, '%Y-%m') as month,
    COUNT(*) as total_sales,
    SUM(price - discount_applied) as revenue
    FROM Purchases
    WHERE status = 'completed'
    AND purchase_date >= DATE_SUB(CURRENT_DATE, INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(purchase_date, '%Y-%m')
    ORDER BY month DESC";
$monthly_revenue = $conn->query($monthly_revenue_query);

// Handle comment deletion
if (isset($_POST['delete_comment']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    $comment_id = (int)$_POST['delete_comment'];
    $delete_stmt = $conn->prepare("DELETE FROM Comments WHERE comment_id = ?");
    $delete_stmt->bind_param("i", $comment_id);
    
    if ($delete_stmt->execute()) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success']);
        exit;
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Failed to delete comment']);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Light Novel Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .stat-card {
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .admin-sidebar {
            min-height: calc(100vh - 56px);
        }
    </style>
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <?php 
            $current_page = 'dashboard';
            include '../../includes/admin_sidebar.php'; 
            ?>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <h1 class="h2 mb-4">Dashboard</h1>

                <!-- Thống kê tổng quan -->
                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <div class="card stat-card bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title">Tổng số truyện</h5>
                                <h2><?= $stats['novels']['total_novels'] ?></h2>
                                <div class="mt-2">
                                    <small>
                                        Đang tiến hành: <?= $stats['novels']['ongoing'] ?><br>
                                        Đã hoàn thành: <?= $stats['novels']['completed'] ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title">Tổng số chapter</h5>
                                <h2><?= $stats['chapters']['total'] ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card bg-info text-white">
                            <div class="card-body">
                                <h5 class="card-title">Người dùng</h5>
                                <h2><?= $stats['users']['total_users'] ?></h2>
                                <small>
                                    Admin: <?= $stats['users']['admin_count'] ?> |
                                    Users: <?= $stats['users']['user_count'] ?>
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card bg-warning text-dark">
                            <div class="card-body">
                                <h5 class="card-title">Bình luận</h5>
                                <h2><?= $stats['comments']['total'] ?></h2>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Thống kê tổng quan -->
                <div class="row g-4 mb-4">
                    <!-- Tổng doanh thu -->
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title">Tổng doanh thu</h5>
                                <h3><?= formatPrice($stats['revenue']['total_revenue']) ?></h3>
                                <p class="mb-0">
                                    <i class="fas fa-shopping-cart"></i>
                                    <?= number_format($stats['revenue']['total_purchases']) ?> đơn hàng
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Doanh thu tháng hiện tại -->
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title">Doanh thu tháng này</h5>
                                <h3><?= formatPrice($stats['revenue']['current_month_revenue']) ?></h3>
                                <p class="mb-0">
                                    <i class="fas fa-chart-line"></i>
                                    <?= number_format($stats['revenue']['current_month_sales']) ?> đơn hàng
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Số truyện đã bán -->
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h5 class="card-title">Truyện đã bán</h5>
                                <h3><?= number_format($stats['revenue']['total_novels_sold']) ?></h3>
                                <p class="mb-0">
                                    <i class="fas fa-book"></i>
                                    Tổng số truyện bán được
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Truyện mới -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Truyện mới thêm</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Tên truyện</th>
                                                <th>Tác giả</th>
                                                <th>Số chapter</th>
                                                <th>Ngày thêm</th>
                                                <th>Thao tác</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($novel = $recent_novels->fetch_assoc()): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($novel['title']) ?></td>
                                                <td><?= htmlspecialchars($novel['author']) ?></td>
                                                <td><?= $novel['chapter_count'] ?></td>
                                                <td><?= date('d/m/Y', strtotime($novel['created_at'])) ?></td>
                                                <td>
                                                    <a href="novels.php?edit=<?= $novel['novel_id'] ?>" 
                                                       class="btn btn-sm btn-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Người dùng mới và Bình luận mới -->
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Người dùng mới đăng ký</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Username</th>
                                                <th>Email</th>
                                                <th>Ngày đăng ký</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($user = $new_users->fetch_assoc()): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($user['username']) ?></td>
                                                <td><?= htmlspecialchars($user['email']) ?></td>
                                                <td><?= date('d/m/Y', strtotime($user['created_at'])) ?></td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Bình luận mới nhất</h5>
                            </div>
                            <div class="card-body">
                                <?php while ($comment = $recent_comments->fetch_assoc()): ?>
                                    <div class="mb-3" data-comment-id="<?= $comment['comment_id'] ?>">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <strong><?= htmlspecialchars($comment['username']) ?></strong>
                                            <div>
                                                <small class="text-muted me-2">
                                                    <?= timeAgo($comment['created_at']) ?>
                                                </small>
                                                <button class="btn btn-danger btn-sm" 
                                                        onclick="deleteComment(<?= $comment['comment_id'] ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div>trên truyện: <?= htmlspecialchars($comment['novel_title']) ?></div>
                                        <p class="mb-0"><?= htmlspecialchars($comment['content']) ?></p>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top truyện bán chạy -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Top truyện bán chạy</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Tên truyện</th>
                                                <th>Số lượng</th>
                                                <th>Doanh thu</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $rank = 1; while ($novel = $top_novels->fetch_assoc()): ?>
                                            <tr>
                                                <td><?= $rank++ ?></td>
                                                <td><?= htmlspecialchars($novel['title']) ?></td>
                                                <td><?= number_format($novel['purchase_count']) ?></td>
                                                <td><?= formatPrice($novel['total_revenue']) ?></td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Biểu đồ doanh thu theo tháng -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Doanh thu theo tháng</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="revenueChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    function deleteComment(commentId) {
        if (confirm('Bạn có chắc chắn muốn xóa bình luận này?')) {
            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    delete_comment: commentId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Remove the comment element from the DOM
                    document.querySelector(`[data-comment-id="${commentId}"]`).remove();
                } else {
                    alert('Có lỗi xảy ra khi xóa bình luận!');
                }
            });
        }
    }

    // Dữ liệu cho biểu đồ
    const monthlyData = <?php 
        $labels = [];
        $revenues = [];
        while ($row = $monthly_revenue->fetch_assoc()) {
            $labels[] = date('m/Y', strtotime($row['month']));
            $revenues[] = $row['revenue'];
        }
        echo json_encode(['labels' => $labels, 'revenues' => $revenues]);
    ?>;

    // Vẽ biểu đồ
    const ctx = document.getElementById('revenueChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: monthlyData.labels,
            datasets: [{
                label: 'Doanh thu (VNĐ)',
                data: monthlyData.revenues,
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return new Intl.NumberFormat('vi-VN', {
                                style: 'currency',
                                currency: 'VND'
                            }).format(value);
                        }
                    }
                }
            }
        }
    });
    </script>
</body>
</html> 