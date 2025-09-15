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
        }
    </style>
</head>
<body>
    <?php include '../../../includes/navbar.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <?php 
            $current_page = 'users';
            include '../../../includes/admin_sidebar.php'; 
            ?>
            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <h1 class="h2 mb-4">Quản lý người dùng</h1>

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

                <!-- Form tìm kiếm -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="search" 
                                       value="<?= htmlspecialchars($data['search']) ?>" 
                                       placeholder="Tìm theo tên đăng nhập hoặc email...">
                            </div>
                            <div class="col-md-4">
                                <select class="form-select" name="role">
                                    <option value="">Tất cả vai trò</option>
                                    <option value="admin" <?= $data['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                    <option value="user" <?= $data['role'] === 'user' ? 'selected' : '' ?>>User</option>
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

                <!-- Button thêm user mới -->
                <div class="mb-4">
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#userModal">
                        <i class="fas fa-plus me-1"></i> Thêm người dùng mới
                    </button>
                </div>

                <!-- Danh sách người dùng -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Avatar</th>
                                        <th>Tên đăng nhập</th>
                                        <th>Email</th>
                                        <th>Vai trò</th>
                                        <th>Yêu thích</th>
                                        <th>Đã mua</th>
                                        <th>Đang đọc</th>
                                        <th>Ngày tạo</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($data['users']->num_rows > 0): ?>
                                        <?php while ($user = $data['users']->fetch_assoc()): ?>
                                            <tr>
                                                <td>
                                                    <img src="<?= $user['avatar_url'] ? '../../../' . $user['avatar_url'] : '../../../images/avatars/default-avatar.jpg' ?>" 
                                                         class="rounded-circle user-avatar" alt="Avatar">
                                                </td>
                                                <td><?= htmlspecialchars($user['username']) ?></td>
                                                <td><?= htmlspecialchars($user['email']) ?></td>
                                                <td>
                                                    <span class="badge <?= $user['role'] === 'admin' ? 'bg-danger' : 'bg-primary' ?>">
                                                        <?= ucfirst($user['role']) ?>
                                                    </span>
                                                </td>
                                                <td><?= $user['favorite_count'] ?></td>
                                                <td><?= $user['purchase_count'] ?></td>
                                                <td><?= $user['reading_count'] ?></td>
                                                <td><?= date('d/m/Y', strtotime($user['created_at'])) ?></td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                                onclick="editUser(<?= htmlspecialchars(json_encode($user)) ?>)">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <?php if ($user['user_id'] != $_SESSION['user_id']): ?>
                                                            <form method="POST" style="display: inline;" 
                                                                  onsubmit="return confirm('Bạn có chắc chắn muốn xóa người dùng này?')">
                                                                <button type="submit" name="delete_user" value="<?= $user['user_id'] ?>" 
                                                                        class="btn btn-sm btn-outline-danger">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="9" class="text-center">Không có người dùng nào.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($data['total_pages'] > 1): ?>
                            <nav aria-label="Page navigation">
                                <ul class="pagination justify-content-center">
                                    <?php for ($i = 1; $i <= $data['total_pages']; $i++): ?>
                                        <li class="page-item <?= $i == $data['page'] ? 'active' : '' ?>">
                                            <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($data['search']) ?>&role=<?= urlencode($data['role']) ?>">
                                                <?= $i ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal thêm/sửa người dùng -->
    <div class="modal fade" id="userModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="userModalTitle">Thêm người dùng mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="user_id" id="user_id">
                        
                        <div class="mb-3">
                            <label class="form-label">Tên đăng nhập *</label>
                            <input type="text" class="form-control" name="username" id="username" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Email *</label>
                            <input type="email" class="form-control" name="email" id="email" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Mật khẩu</label>
                            <input type="password" class="form-control" name="password" id="password">
                            <div class="form-text">Để trống nếu không muốn thay đổi mật khẩu (chỉ khi sửa)</div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Vai trò *</label>
                            <select class="form-select" name="role" id="role" required>
                                <option value="user">User</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Avatar</label>
                            <input type="file" class="form-control" name="avatar" accept="image/*">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" name="save_user" class="btn btn-primary">Lưu</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editUser(user) {
            document.getElementById('userModalTitle').textContent = 'Sửa thông tin người dùng';
            document.getElementById('user_id').value = user.user_id;
            document.getElementById('username').value = user.username;
            document.getElementById('email').value = user.email;
            document.getElementById('role').value = user.role;
            document.getElementById('password').value = '';
            
            var modal = new bootstrap.Modal(document.getElementById('userModal'));
            modal.show();
        }
        
        // Reset form when modal is hidden
        document.getElementById('userModal').addEventListener('hidden.bs.modal', function() {
            document.getElementById('userModalTitle').textContent = 'Thêm người dùng mới';
            document.querySelector('#userModal form').reset();
        });
    </script>
</body>
</html>
