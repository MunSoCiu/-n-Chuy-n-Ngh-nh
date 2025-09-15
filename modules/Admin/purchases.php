<?php
session_start();

require_once '../../app/config/config.php';
require_once '../../includes/functions.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "/index.php");
    exit();
}

// Xử lý xóa hóa đơn
if (isset($_POST['action']) && $_POST['action'] === 'delete') {
    $purchase_id = (int)$_POST['purchase_id'];
    $conn->query("DELETE FROM Purchases WHERE purchase_id = $purchase_id");
    header("Location: purchases.php");
    exit();
}

// Xử lý cập nhật trạng thái
if (isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $purchase_id = (int)$_POST['purchase_id'];
    $status = $conn->real_escape_string($_POST['status']);
    $conn->query("UPDATE Purchases SET status = '$status' WHERE purchase_id = $purchase_id");
    header("Location: purchases.php");
    exit();
}

// Xử lý tìm kiếm
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_type = isset($_GET['search_type']) ? $_GET['search_type'] : 'novel';

// Query cơ bản
$query = "SELECT p.*, n.title as novel_title, u.username, n.price as original_price
          FROM Purchases p
          JOIN LightNovels n ON p.novel_id = n.novel_id
          JOIN Users u ON p.user_id = u.user_id
          WHERE 1=1";

// Thêm điều kiện tìm kiếm
if ($search) {
    if ($search_type === 'novel') {
        $query .= " AND n.title LIKE '%$search%'";
    } else {
        $query .= " AND u.username LIKE '%$search%'";
    }
}

$query .= " ORDER BY p.purchase_date DESC";
$purchases = $conn->query($query);
?>

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
    </style>
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <?php 
            $current_page = 'purchases';
            include '../../includes/admin_sidebar.php'; 
            ?>
            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <h1 class="h2 mb-4">Quản lý hóa đơn</h1>

                <!-- Form tìm kiếm -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="search" 
                                       value="<?= htmlspecialchars($search) ?>" 
                                       placeholder="Nhập từ khóa tìm kiếm...">
                            </div>
                            <div class="col-md-4">
                                <select class="form-select" name="search_type">
                                    <option value="novel" <?= $search_type === 'novel' ? 'selected' : '' ?>>
                                        Tìm theo tên truyện
                                    </option>
                                    <option value="user" <?= $search_type === 'user' ? 'selected' : '' ?>>
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
                                    <?php while ($purchase = $purchases->fetch_assoc()): ?>
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
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <?php include '../../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 