<?php
session_start();
require_once '../../app/config/config.php';
require_once '../../includes/functions.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit();
}

// Xử lý xóa người dùng
if (isset($_POST['delete_user'])) {
    $user_id = (int)$_POST['delete_user'];
    
    // Không cho phép admin xóa chính mình
    if ($user_id == $_SESSION['user_id']) {
        $_SESSION['error'] = "Không thể xóa tài khoản của chính mình!";
    } else {
        // Xóa avatar cũ nếu có
        $avatar_query = "SELECT avatar_url FROM Users WHERE user_id = ?";
        $stmt = $conn->prepare($avatar_query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $old_avatar = $stmt->get_result()->fetch_assoc()['avatar_url'];
        if ($old_avatar && file_exists("../../" . $old_avatar)) {
            unlink("../../" . $old_avatar);
        }
        
        // Xóa user (các bảng liên quan sẽ tự động xóa do CASCADE)
        $delete_query = "DELETE FROM Users WHERE user_id = ?";
        $stmt = $conn->prepare($delete_query);
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            $_SESSION['success'] = "Đã xóa người dùng thành công!";
        } else {
            $_SESSION['error'] = "Có lỗi xảy ra khi xóa người dùng!";
        }
    }
    header("Location: users.php");
    exit();
}

// Xử lý thêm/sửa người dùng
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_user'])) {
    $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : null;
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $new_password = trim($_POST['password']);

    // Kiểm tra username và email đã tồn tại chưa
    $check_query = "SELECT user_id FROM Users WHERE (username = ? OR email = ?) AND user_id != ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("ssi", $username, $email, $user_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $_SESSION['error'] = "Username hoặc email đã tồn tại!";
    } else {
        // Xử lý upload avatar
        $avatar_url = null;
        if (isset($_FILES['avatar']) && $_FILES['avatar']['size'] > 0) {
            $upload_dir = "../../uploads/avatars/";
            $filename = uniqid() . "_" . basename($_FILES['avatar']['name']);
            $target_file = $upload_dir . $filename;
            
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $target_file)) {
                $avatar_url = "uploads/avatars/" . $filename;
                
                // Xóa avatar cũ nếu đang sửa user
                if ($user_id) {
                    $old_avatar_query = "SELECT avatar_url FROM Users WHERE user_id = ?";
                    $stmt = $conn->prepare($old_avatar_query);
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $old_avatar = $stmt->get_result()->fetch_assoc()['avatar_url'];
                    if ($old_avatar && file_exists("../../" . $old_avatar)) {
                        unlink("../../" . $old_avatar);
                    }
                }
            }
        }

        if ($user_id) {
            // Cập nhật user
            $update_query = "UPDATE Users SET username = ?, email = ?, role = ?";
            $params = [$username, $email, $role];
            $types = "sss";

            if ($new_password) {
                $update_query .= ", password_hash = ?";
                $params[] = password_hash($new_password, PASSWORD_DEFAULT);
                $types .= "s";
            }
            if ($avatar_url) {
                $update_query .= ", avatar_url = ?";
                $params[] = $avatar_url;
                $types .= "s";
            }

            $update_query .= " WHERE user_id = ?";
            $params[] = $user_id;
            $types .= "i";

            $stmt = $conn->prepare($update_query);
            $stmt->bind_param($types, ...$params);
            
        } else {
            // Thêm user mới
            if (!$new_password) {
                $_SESSION['error'] = "Phải nhập mật khẩu cho người dùng mới!";
                header("Location: users.php");
                exit();
            }
            
            $insert_query = "INSERT INTO Users (username, email, password_hash, role, avatar_url) 
                           VALUES (?, ?, ?, ?, ?)";
            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("sssss", $username, $email, $password_hash, $role, $avatar_url);
        }

        if ($stmt->execute()) {
            $_SESSION['success'] = "Đã lưu thông tin người dùng thành công!";
            header("Location: users.php");
            exit();
        } else {
            $_SESSION['error'] = "Có lỗi xảy ra khi lưu thông tin!";
        }
    }
}

// Lấy thông tin user cần sửa
$edit_user = null;
if (isset($_GET['edit'])) {
    $user_id = (int)$_GET['edit'];
    $edit_query = "SELECT * FROM Users WHERE user_id = ?";
    $stmt = $conn->prepare($edit_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $edit_user = $stmt->get_result()->fetch_assoc();
}

// Lấy danh sách người dùng với phân trang và tìm kiếm
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$limit = 10;
$offset = ($page - 1) * $limit;

$where = "";
$params = [];
$types = "";

if ($search) {
    $where = "WHERE username LIKE ? OR email LIKE ?";
    $search_param = "%$search%";
    $params = [$search_param, $search_param];
    $types = "ss";
}

// Lấy tổng số user
$count_query = "SELECT COUNT(*) as total FROM Users $where";
if ($params) {
    $stmt = $conn->prepare($count_query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $total_users = $stmt->get_result()->fetch_assoc()['total'];
} else {
    $total_users = $conn->query($count_query)->fetch_assoc()['total'];
}

// Lấy danh sách user
$query = "SELECT u.*, 
          (SELECT COUNT(*) FROM Comments WHERE user_id = u.user_id) as comment_count,
          (SELECT COUNT(*) FROM Favorites WHERE user_id = u.user_id) as favorite_count,
          (SELECT COUNT(*) FROM Reading_History WHERE user_id = u.user_id) as history_count
          FROM Users u
          $where
          ORDER BY u.created_at DESC 
          LIMIT ? OFFSET ?";

$stmt = $conn->prepare($query);
if ($params) {
    $params[] = $limit;
    $params[] = $offset;
    $stmt->bind_param($types . "ii", ...$params);
} else {
    $stmt->bind_param("ii", $limit, $offset);
}
$stmt->execute();
$users = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý người dùng - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .admin-sidebar {
            min-height: calc(100vh - 56px);
        }
        .user-avatar {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 50%;
        }
        .avatar-preview {
            max-width: 200px;
            height: auto;
        }
    </style>
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <?php 
            $current_page = 'users';
            include '../../includes/admin_sidebar.php'; 
            ?>
            
            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <h1 class="h2 mb-4">Quản lý người dùng</h1>

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

                <!-- Form thêm/sửa user -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><?= $edit_user ? 'Sửa thông tin người dùng' : 'Thêm người dùng mới' ?></h5>
                    </div>
                    <div class="card-body">
                        <form action="" method="POST" enctype="multipart/form-data">
                            <?php if ($edit_user): ?>
                                <input type="hidden" name="user_id" value="<?= $edit_user['user_id'] ?>">
                            <?php endif; ?>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Username</label>
                                        <input type="text" class="form-control" name="username" required
                                               value="<?= $edit_user ? htmlspecialchars($edit_user['username']) : '' ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control" name="email" required
                                               value="<?= $edit_user ? htmlspecialchars($edit_user['email']) : '' ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Mật khẩu <?= $edit_user ? '(để trống nếu không đổi)' : '' ?></label>
                                        <input type="password" class="form-control" name="password"
                                               <?= $edit_user ? '' : 'required' ?>>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Quyền</label>
                                        <select class="form-select" name="role" required>
                                            <option value="user" <?= ($edit_user && $edit_user['role'] == 'user') ? 'selected' : '' ?>>
                                                Người dùng
                                            </option>
                                            <option value="admin" <?= ($edit_user && $edit_user['role'] == 'admin') ? 'selected' : '' ?>>
                                                Admin
                                            </option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Avatar</label>
                                        <input type="file" class="form-control" name="avatar" accept="image/*">
                                        <?php if ($edit_user && $edit_user['avatar_url']): ?>
                                            <img src="../../<?= htmlspecialchars($edit_user['avatar_url']) ?>" 
                                                 class="mt-2 avatar-preview">
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" name="save_user" class="btn btn-primary">
                                <?= $edit_user ? 'Cập nhật' : 'Thêm mới' ?>
                            </button>
                            <?php if ($edit_user): ?>
                                <a href="users.php" class="btn btn-secondary">Hủy</a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

                <!-- Tìm kiếm -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form action="" method="GET" class="row g-3">
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="search" 
                                       placeholder="Tìm kiếm theo username hoặc email..."
                                       value="<?= htmlspecialchars($search) ?>">
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-2"></i>Tìm kiếm
                                </button>
                                <?php if ($search): ?>
                                    <a href="users.php" class="btn btn-secondary">
                                        <i class="fas fa-times me-2"></i>Xóa bộ lọc
                                    </a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Danh sách người dùng -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Danh sách người dùng</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Avatar</th>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Quyền</th>
                                        <th>Hoạt động</th>
                                        <th>Ngày tạo</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($user = $users->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $user['user_id'] ?></td>
                                        <td>
                                            <?php if ($user['avatar_url']): ?>
                                                <img src="../../<?= htmlspecialchars($user['avatar_url']) ?>" 
                                                     class="user-avatar">
                                            <?php else: ?>
                                                <i class="fas fa-user-circle fa-2x text-secondary"></i>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($user['username']) ?></td>
                                        <td><?= htmlspecialchars($user['email']) ?></td>
                                        <td>
                                            <span class="badge bg-<?= $user['role'] === 'admin' ? 'danger' : 'primary' ?>">
                                                <?= $user['role'] === 'admin' ? 'Admin' : 'Người dùng' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small>
                                                <i class="fas fa-comment"></i> <?= $user['comment_count'] ?> bình luận<br>
                                                <i class="fas fa-heart"></i> <?= $user['favorite_count'] ?> yêu thích<br>
                                                <i class="fas fa-book-reader"></i> <?= $user['history_count'] ?> lịch sử đọc
                                            </small>
                                        </td>
                                        <td><?= date('d/m/Y', strtotime($user['created_at'])) ?></td>
                                        <td>
                                            <a href="?edit=<?= $user['user_id'] ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($user['user_id'] != $_SESSION['user_id']): ?>
                                                <form action="" method="POST" class="d-inline" 
                                                      onsubmit="return confirm('Bạn có chắc muốn xóa người dùng này?')">
                                                    <button type="submit" name="delete_user" value="<?= $user['user_id'] ?>" 
                                                            class="btn btn-sm btn-danger">
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

                        <!-- Phân trang -->
                        <?php if ($total_users > $limit): ?>
                            <div class="mt-4">
                                <?= pagination($total_users, $page, $limit, 
                                    "users.php?page=%d" . ($search ? "&search=".urlencode($search) : "")) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 