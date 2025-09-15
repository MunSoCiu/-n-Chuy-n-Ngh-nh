<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý sách - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
    <style>
        .admin-sidebar {
            min-height: calc(100vh - 56px);
        }
        .novel-cover-preview {
            max-width: 150px;
            max-height: 200px;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <?php include '../../../includes/navbar.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <?php 
            $current_page = 'novels';
            include '../../../includes/admin_sidebar.php'; 
            ?>
            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <h1 class="h2 mb-4">Quản lý sách</h1>

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
                            <div class="col-md-4">
                                <input type="text" class="form-control" name="search" 
                                       value="<?= htmlspecialchars($data['search']) ?>" 
                                       placeholder="Tìm theo tên sách hoặc tác giả...">
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="category">
                                    <option value="">Tất cả thể loại</option>
                                    <?php while ($cat = $data['categories']->fetch_assoc()): ?>
                                        <option value="<?= $cat['category_id'] ?>" 
                                                <?= $data['category'] == $cat['category_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cat['name']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="status">
                                    <option value="">Tất cả trạng thái</option>
                                    <option value="Đang tiến hành" <?= $data['status'] === 'Đang tiến hành' ? 'selected' : '' ?>>
                                        Đang tiến hành
                                    </option>
                                    <option value="Đã hoàn thành" <?= $data['status'] === 'Đã hoàn thành' ? 'selected' : '' ?>>
                                        Đã hoàn thành
                                    </option>
                                    <option value="Đã hủy bỏ" <?= $data['status'] === 'Đã hủy bỏ' ? 'selected' : '' ?>>
                                        Đã hủy bỏ
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

                <!-- Button thêm sách mới -->
                <div class="mb-4">
                    <button type="button" class="btn btn-success" data-bs-toggle="collapse" data-bs-target="#addNovelForm" aria-expanded="false">
                        <i class="fas fa-plus me-1"></i> Thêm sách mới
                    </button>
                </div>

                <!-- Form thêm sách mới (ẩn/hiện) -->
                <div class="collapse mb-4" id="addNovelForm">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-book me-2"></i>Thêm sách mới
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Tên sách <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="title" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Tác giả <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="author" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Mô tả</label>
                                            <textarea class="form-control" name="description" rows="3"></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Trạng thái <span class="text-danger">*</span></label>
                                            <select class="form-select" name="status" required>
                                                <option value="">Chọn trạng thái...</option>
                                                <option value="Đang tiến hành">Đang tiến hành</option>
                                                <option value="Đã hoàn thành">Đã hoàn thành</option>
                                                <option value="Đã hủy bỏ">Đã hủy bỏ</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Giá (VNĐ) <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" name="price" min="0" step="1000" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Ảnh bìa</label>
                                            <input type="file" class="form-control" name="cover_image" accept="image/*">
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Thể loại</label>
                                    <select class="form-select" name="categories[]" multiple id="categories-select-add">
                                        <?php foreach ($data['categories'] as $category): ?>
                                            <option value="<?= $category['category_id'] ?>">
                                                <?= htmlspecialchars($category['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="form-text">Có thể chọn nhiều thể loại</div>
                                </div>
                                <div class="d-flex justify-content-end gap-2">
                                    <button type="button" class="btn btn-secondary" data-bs-toggle="collapse" data-bs-target="#addNovelForm">
                                        <i class="fas fa-times me-1"></i> Hủy
                                    </button>
                                    <button type="submit" name="save_novel" value="1" class="btn btn-success">
                                        <i class="fas fa-save me-1"></i> Lưu sách
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Danh sách sách -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Ảnh bìa</th>
                                        <th>Tên sách</th>
                                        <th>Tác giả</th>
                                        <th>Thể loại</th>
                                        <th>Trạng thái</th>
                                        <th>Giá</th>
                                        <th>Ngày tạo</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($data['novels']->num_rows > 0): ?>
                                        <?php while ($novel = $data['novels']->fetch_assoc()): ?>
                                            <tr>
                                                <td>
                                                    <img src="<?= BASE_URL . '/' . ($novel['cover_image'] ?: 'images/covers/default-cover.jpg') ?>" 
                                                         class="img-thumbnail" style="width: 60px; height: 80px; object-fit: cover;">
                                                </td>
                                                <td><?= htmlspecialchars($novel['title']) ?></td>
                                                <td><?= htmlspecialchars($novel['author']) ?></td>
                                                <td>
                                                    <?php if ($novel['categories']): ?>
                                                        <?php foreach (explode(',', $novel['categories']) as $category): ?>
                                                            <span class="badge bg-secondary me-1"><?= htmlspecialchars($category) ?></span>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge <?= $novel['status'] === 'Đã hoàn thành' ? 'bg-success' : ($novel['status'] === 'Đang tiến hành' ? 'bg-primary' : 'bg-danger') ?>">
                                                        <?= htmlspecialchars($novel['status']) ?>
                                                    </span>
                                                </td>
                                                <td><?= formatPrice($novel['price']) ?></td>
                                                <td><?= date('d/m/Y', strtotime($novel['created_at'])) ?></td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="../../../app/Controllers/Admin/Admin_edit_novel_controller.php?id=<?= $novel['novel_id'] ?>" 
                                                           class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <form method="POST" style="display: inline;" 
                                                              onsubmit="return confirm('Bạn có chắc chắn muốn xóa sách này?')">
                                                            <button type="submit" name="delete_novel" value="<?= $novel['novel_id'] ?>" 
                                                                    class="btn btn-sm btn-outline-danger">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center">Không có sách nào.</td>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize Select2 for add form when collapse is shown
            $('#addNovelForm').on('shown.bs.collapse', function () {
                $('#categories-select-add').select2({
                    placeholder: "Chọn thể loại...",
                    allowClear: true,
                    width: '100%'
                });
            });
            
            // Reset form when collapse is hidden
            $('#addNovelForm').on('hidden.bs.collapse', function () {
                $(this).find('form')[0].reset();
                if ($('#categories-select-add').hasClass('select2-hidden-accessible')) {
                    $('#categories-select-add').select2('destroy');
                }
            });
        });
    </script>
</body>
</html>
