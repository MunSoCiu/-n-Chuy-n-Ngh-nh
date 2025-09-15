<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý hóa đơn - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .admin-sidebar {
            min-height: calc(100vh - 56px);
        }
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
    </style>
</head>
<body>
    <?php include '../../../includes/navbar.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <?php 
            $current_page = 'purchases';
            include '../../../includes/admin_sidebar.php'; 
            ?>
            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <h1 class="h2 mb-4">Quản lý hóa đơn</h1>

                <?php if (!empty($data['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= $data['success'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (!empty($data['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= $data['error'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Thống kê -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card stats-card">
                            <div class="card-body text-center">
                                <h3 class="mb-1"><?= formatPrice($data['stats']['total_revenue']) ?></h3>
                                <p class="mb-0">Tổng doanh thu</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stats-card">
                            <div class="card-body text-center">
                                <h3 class="mb-1"><?= $data['stats']['total_orders'] ?></h3>
                                <p class="mb-0">Tổng đơn hàng</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stats-card">
                            <div class="card-body text-center">
                                <h3 class="mb-1"><?= $data['stats']['completed_orders'] ?></h3>
                                <p class="mb-0">Đã hoàn thành</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stats-card">
                            <div class="card-body text-center">
                                <h3 class="mb-1"><?= $data['stats']['pending_orders'] ?></h3>
                                <p class="mb-0">Đang xử lý</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top selling novels -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Top truyện bán chạy</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Tên truyện</th>
                                                <th>Số lượng bán</th>
                                                <th>Doanh thu</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($novel = $data['top_novels']->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($novel['title']) ?></td>
                                                    <td><?= $novel['sales_count'] ?></td>
                                                    <td><?= formatPrice($novel['revenue']) ?></td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form tìm kiếm -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="search" 
                                       value="<?= htmlspecialchars($data['search']) ?>" 
                                       placeholder="Nhập từ khóa tìm kiếm...">
                            </div>
                            <div class="col-md-4">
                                <select class="form-select" name="search_type">
                                    <option value="novel" <?= $data['search_type'] === 'novel' ? 'selected' : '' ?>>
                                        Tìm theo tên truyện
                                    </option>
                                    <option value="user" <?= $data['search_type'] === 'user' ? 'selected' : '' ?>>
                                        Tìm theo tên người dùng
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search me-1"></i> Tìm kiếm
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Người mua</th>
                                        <th>Tên truyện</th>
                                        <th>Giá gốc</th>
                                        <th>Giảm giá</th>
                                        <th>Thanh toán</th>
                                        <th>Trạng thái</th>
                                        <th>Ngày mua</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($data['purchases']->num_rows > 0): ?>
                                        <?php while ($purchase = $data['purchases']->fetch_assoc()): ?>
                                            <tr>
                                                <td><?= $purchase['purchase_id'] ?></td>
                                                <td><?= htmlspecialchars($purchase['username']) ?></td>
                                                <td><?= htmlspecialchars($purchase['novel_title']) ?></td>
                                                <td><?= formatPrice($purchase['original_price']) ?></td>
                                                <td><?= formatPrice($purchase['discount_applied']) ?></td>
                                                <td><?= formatPrice($purchase['price']) ?></td>
                                                <td>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="action" value="update_status">
                                                        <input type="hidden" name="purchase_id" value="<?= $purchase['purchase_id'] ?>">
                                                        <select class="form-select form-select-sm" name="status" 
                                                                onchange="this.form.submit()">
                                                            <option value="pending" <?= $purchase['status'] === 'pending' ? 'selected' : '' ?>>
                                                                Đang xử lý
                                                            </option>
                                                            <option value="completed" <?= $purchase['status'] === 'completed' ? 'selected' : '' ?>>
                                                                Hoàn thành
                                                            </option>
                                                            <option value="cancelled" <?= $purchase['status'] === 'cancelled' ? 'selected' : '' ?>>
                                                                Đã hủy
                                                            </option>
                                                        </select>
                                                    </form>
                                                </td>
                                                <td><?= date('d/m/Y H:i', strtotime($purchase['purchase_date'])) ?></td>
                                                <td>
                                                    <form method="POST" class="d-inline" 
                                                          onsubmit="return confirm('Bạn có chắc muốn xóa hóa đơn này?')">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="purchase_id" value="<?= $purchase['purchase_id'] ?>">
                                                        <button type="submit" class="btn btn-danger btn-sm">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="9" class="text-center">Không có hóa đơn nào.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <?php include '../../../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
