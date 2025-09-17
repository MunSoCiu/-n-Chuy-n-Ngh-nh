<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Light Novel Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .stat-card { transition: transform 0.2s; }
        .stat-card:hover { transform: translateY(-5px); }
        .admin-sidebar { min-height: calc(100vh - 56px); }
    </style>
</head>
<body>
    <?php include '../../../includes/navbar.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <?php 
            $current_page = 'dashboard';
            include '../../../includes/admin_sidebar.php'; 
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
                                <h2><?= $data['stats']['novels']['total_novels'] ?></h2>
                                <div class="mt-2">
                                    <small>
                                        Đang tiến hành: <?= $data['stats']['novels']['ongoing'] ?><br>
                                        Đã hoàn thành: <?= $data['stats']['novels']['completed'] ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card stat-card bg-info text-white">
                            <div class="card-body">
                                <h5 class="card-title">Người dùng</h5>
                                <h2><?= $data['stats']['users']['total_users'] ?></h2>
                                <small>
                                    Admin: <?= $data['stats']['users']['admin_count'] ?> |
                                    Users: <?= $data['stats']['users']['user_count'] ?>
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card bg-warning text-dark">
                            <div class="card-body">
                                <h5 class="card-title">Bình luận</h5>
                                <h2><?= $data['stats']['comments']['total'] ?></h2>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Thống kê doanh thu -->
                <div class="row g-4 mb-4">
                    <!-- Tổng doanh thu -->
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title">Tổng doanh thu</h5>
                                <h3><?= formatPrice($data['stats']['revenue']['total_revenue']) ?></h3>
                                <p class="mb-0">
                                    <i class="fas fa-shopping-cart"></i>
                                    <?= number_format($data['stats']['revenue']['total_purchases']) ?> đơn hàng
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Doanh thu tháng hiện tại -->
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title">Doanh thu tháng này</h5>
                                <h3><?= formatPrice($data['stats']['revenue']['current_month_revenue']) ?></h3>
                                <p class="mb-0">
                                    <i class="fas fa-chart-line"></i>
                                    <?= number_format($data['stats']['revenue']['current_month_sales']) ?> đơn hàng
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Lượt đọc trong ngày -->
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title">Lượt đọc trong ngày</h5>
                                <h3><?= $todayReads ?></h3>
                                <p class="mb-0">
                                    <i class="fas fa-chart-line"></i>
                                    <?= $data['stats']['today']['reads'] ?? 0 ?> lượt đọc
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Doanh thu trong ngày -->
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title">Doanh thu trong ngày</h5>
                                <h3><?= number_format($todayRevenue, 0, ',', '.') ?> VND</h3>
                                <p class="mb-0">
                                    <i class="fas fa-chart-line"></i>
                                    <?= number_format($data['stats']['today']['revenue'] ?? 0, 0, ',', '.') ?> VND
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Số truyện đã bán -->
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h5 class="card-title">Truyện đã bán</h5>
                                <h3><?= number_format($data['stats']['revenue']['total_novels_sold']) ?></h3>
                                <p class="mb-0">
                                    <i class="fas fa-book"></i>
                                    Tổng số truyện bán được
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Biểu đồ doanh thu theo tháng -->
                <div class="row mb-4">
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

    <!-- Biểu đồ lượt đọc & doanh thu theo ngày -->
    <div class="row mb-4 px-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Lượt đọc theo ngày</h5>
                    <select id="monthSelector" class="form-select form-select-sm w-auto">
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                            <option value="<?= $m ?>" <?= ($m == date('n')) ? 'selected' : '' ?>>
                                Tháng <?= $m ?>/<?= date('Y') ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="card-body">
                    <canvas id="readsChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Doanh thu theo ngày</h5>
                </div>
                <div class="card-body">
                    <canvas id="dailyRevenueChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    let readsChart, dailyRevenueChart, revenueChart;

    function loadStats(month) {
        $.getJSON(
            "../../../app/Controllers/Admin/Admin_dashboard_controller.php",
            { action: "statsByMonth", year: new Date().getFullYear(), month: month },
            function(data) {
                const labels = data.reads.map(r => "Ngày " + r.day);
                const reads = data.reads.map(r => r.count);
                const revenues = data.revenue.map(r => r.amount);

                // Biểu đồ lượt đọc
                if (readsChart) readsChart.destroy();
                readsChart = new Chart(document.getElementById("readsChart"), {
                    type: "bar",
                    data: {
                        labels: labels,
                        datasets: [{
                            label: "Lượt đọc",
                            data: reads,
                            backgroundColor: "rgba(54, 162, 235, 0.6)"
                        }]
                    },
                    options: { responsive: true }
                });

                // Biểu đồ doanh thu theo ngày
                if (dailyRevenueChart) dailyRevenueChart.destroy();
                dailyRevenueChart = new Chart(document.getElementById("dailyRevenueChart"), {
                    type: "line",
                    data: {
                        labels: labels,
                        datasets: [{
                            label: "Doanh thu (VNĐ)",
                            data: revenues,
                            borderColor: "rgb(75, 192, 192)",
                            tension: 0.1
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                ticks: {
                                    callback: function(value) {
                                        return new Intl.NumberFormat('vi-VN').format(value) + " ₫";
                                    }
                                }
                            }
                        }
                    }
                });
            }
        );
    }

    function loadMonthlyRevenue() {
        $.getJSON(
            "../../../app/Controllers/Admin/Admin_dashboard_controller.php",
            { action: "statsByYear", year: new Date().getFullYear() },
            function(data) {
                const labels = data.months.map(m => "Tháng " + m.month);
                const revenues = data.months.map(m => m.amount);

                if (revenueChart) revenueChart.destroy();
                revenueChart = new Chart(document.getElementById("revenueChart"), {
                    type: "bar",
                    data: {
                        labels: labels,
                        datasets: [{
                            label: "Doanh thu theo tháng",
                            data: revenues,
                            backgroundColor: "rgba(255, 99, 132, 0.6)"
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                ticks: {
                                    callback: function(value) {
                                        return new Intl.NumberFormat('vi-VN').format(value) + " ₫";
                                    }
                                }
                            }
                        }
                    }
                });
            }
        );
    }

    // Load mặc định tháng hiện tại
    loadStats($("#monthSelector").val());
    loadMonthlyRevenue();

    // Khi chọn tháng khác
    $("#monthSelector").change(function() {
        loadStats($(this).val());
    });
    </script>
</body>
</html>
