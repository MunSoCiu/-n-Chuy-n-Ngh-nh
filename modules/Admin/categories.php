<?php
session_start();
ob_start();
require_once '../../app/config/config.php';
require_once '../../includes/functions.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "/index.php");
    exit();
}

// Xử lý thêm/sửa thể loại
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_category'])) {
    $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $name = trim($_POST['name']);
    $error = false;

    // Kiểm tra tên không được trống và không vượt quá 100 ký tự
    if (empty($name)) {
        $_SESSION['error'] = "Tên thể loại không được để trống!";
        $error = true;
    } elseif (strlen($name) > 100) {
        $_SESSION['error'] = "Tên thể loại không được vượt quá 100 ký tự!";
        $error = true;
    }

    if (!$error) {
        try {
            // Kiểm tra tên thể loại đã tồn tại chưa
            $check_query = "SELECT category_id FROM Categories WHERE name = ? AND category_id != COALESCE(?, 0)";
            $stmt = $conn->prepare($check_query);
            $stmt->bind_param("si", $name, $category_id);
            $stmt->execute();
            
            if ($stmt->get_result()->num_rows > 0) {
                $_SESSION['error'] = "Tên thể loại đã tồn tại!";
            } else {
                if ($category_id) {
                    // Cập nhật thể loại
                    $update_query = "UPDATE Categories SET name = ? WHERE category_id = ?";
                    $stmt = $conn->prepare($update_query);
                    $stmt->bind_param("si", $name, $category_id);
                    
                    if ($stmt->execute()) {
                        $_SESSION['success'] = "Đã cập nhật thể loại thành công!";
                        header("Location: categories.php");
                        exit();
                    } else {
                        $_SESSION['error'] = "Không thể cập nhật thể loại: " . $stmt->error;
                    }
                } else {
                    // Thêm thể loại mới
                    $insert_query = "INSERT INTO Categories (name) VALUES (?)";
                    $stmt = $conn->prepare($insert_query);
                    $stmt->bind_param("s", $name);
                    
                    if ($stmt->execute()) {
                        $_SESSION['success'] = "Đã thêm thể loại mới thành công!";
                        header("Location: categories.php");
                        exit();
                    } else {
                        $_SESSION['error'] = "Không thể thêm thể loại mới: " . $stmt->error;
                    }
                }
            }
        } catch (Exception $e) {
            $_SESSION['error'] = "Có lỗi xảy ra: " . $e->getMessage();
        }
    }
}

// Xử lý xóa thể loại
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_category'])) {
    $category_id = (int)$_POST['category_id'];
    
    try {
        // Kiểm tra xem thể loại có đang được sử dụng không
        $check_query = "SELECT COUNT(*) as count FROM Novel_Categories WHERE category_id = ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("i", $category_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if ($result['count'] > 0) {
            $_SESSION['error'] = "Không thể xóa thể loại đang được sử dụng!";
        } else {
            $delete_query = "DELETE FROM Categories WHERE category_id = ?";
            $stmt = $conn->prepare($delete_query);
            $stmt->bind_param("i", $category_id);
            $stmt->execute();
            $_SESSION['success'] = "Đã xóa thể loại thành công!";
        }
        header("Location: categories.php");
        exit();
    } catch (Exception $e) {
        $_SESSION['error'] = "Có lỗi xảy ra: " . $e->getMessage();
    }
}

// Xử lý tìm kiếm và phân trang
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Query cơ bản
$query = "SELECT c.*, COUNT(nc.novel_id) as novel_count 
          FROM Categories c 
          LEFT JOIN Novel_Categories nc ON c.category_id = nc.category_id";

// Thêm điều kiện tìm kiếm
if ($search) {
    $query .= " WHERE c.name LIKE ?";
    $search_param = "%$search%";
}

$query .= " GROUP BY c.category_id ORDER BY c.name";

// Đếm tổng số bản ghi để phân trang
$count_query = "SELECT COUNT(*) as total FROM Categories";
if ($search) {
    $count_query .= " WHERE name LIKE ?";
}

$stmt = $conn->prepare($count_query);
if ($search) {
    $stmt->bind_param("s", $search_param);
}
$stmt->execute();
$total_records = $stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_records / $limit);

// Thêm LIMIT và OFFSET cho phân trang
$query .= " LIMIT ? OFFSET ?";

// Thực thi query chính
$stmt = $conn->prepare($query);
if ($search) {
    $stmt->bind_param("sii", $search_param, $limit, $offset);
} else {
    $stmt->bind_param("ii", $limit, $offset);
}
$stmt->execute();
$categories = $stmt->get_result();

// Lấy thông tin thể loại cần sửa
$edit_category = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $edit_query = "SELECT * FROM Categories WHERE category_id = ?";
    $stmt = $conn->prepare($edit_query);
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $edit_category = $stmt->get_result()->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý thể loại - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <?php 
            $current_page = 'categories';
            include '../../includes/admin_sidebar.php'; 
            ?>
            
            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-tags"></i> Quản lý thể loại</h2>
                </div>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= $_SESSION['success'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= $_SESSION['error'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <!-- Form thêm/sửa thể loại -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form action="categories.php" method="POST" class="row g-3">
                            <?php if (isset($edit_category)): ?>
                                <input type="hidden" name="category_id" value="<?= $edit_category['category_id'] ?>">
                            <?php endif; ?>
                            
                            <div class="col-md-8">
                                <label class="form-label">Tên thể loại</label>
                                <input type="text" class="form-control" name="name" 
                                       value="<?= isset($edit_category) ? htmlspecialchars($edit_category['name']) : '' ?>" 
                                       required maxlength="100">
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" name="save_category" class="btn btn-primary w-100">
                                    <i class="fas fa-save me-1"></i> 
                                    <?= isset($edit_category) ? 'Cập nhật' : 'Thêm mới' ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Danh sách thể loại -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Tên thể loại</th>
                                        <th>Số truyện</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($category = $categories->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= $category['category_id'] ?></td>
                                            <td><?= htmlspecialchars($category['name']) ?></td>
                                            <td><?= $category['novel_count'] ?></td>
                                            <td>
                                                <a href="?edit=<?= $category['category_id'] ?>" 
                                                   class="btn btn-sm btn-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <?php if ($category['novel_count'] == 0): ?>
                                                    <form method="POST" class="d-inline" 
                                                          onsubmit="return confirm('Bạn có chắc muốn xóa thể loại này?')">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="category_id" 
                                                               value="<?= $category['category_id'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-danger">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
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