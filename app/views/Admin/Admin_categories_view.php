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
    <?php include '../../../includes/navbar.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <?php 
            $current_page = 'categories';
            include '../../../includes/admin_sidebar.php'; 
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
                        <form method="POST" class="row g-3">
                            <?php if (isset($data['edit_category'])): ?>
                                <input type="hidden" name="category_id" value="<?= $data['edit_category']['category_id'] ?>">
                            <?php endif; ?>
                            
                            <div class="col-md-8">
                                <label class="form-label">Tên thể loại</label>
                                <input type="text" class="form-control" name="name" 
                                       value="<?= isset($data['edit_category']) ? htmlspecialchars($data['edit_category']['name']) : '' ?>" 
                                       required maxlength="100">
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" name="save_category" class="btn btn-primary w-100">
                                    <i class="fas fa-save me-1"></i> 
                                    <?= isset($data['edit_category']) ? 'Cập nhật' : 'Thêm mới' ?>
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
                                    <?php while ($category = $data['categories']->fetch_assoc()): ?>
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
                                                        <input type="hidden" name="delete_category" value="1">
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

    <?php include '../../../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>