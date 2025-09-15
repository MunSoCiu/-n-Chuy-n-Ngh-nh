<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm sách mới - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <style>
        body {
            margin: 0;
            padding: 0;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
            min-height: 100vh;
        }
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2">
                <?php include '../../../includes/admin_sidebar.php'; ?>
            </div>
            <div class="col-md-10">
                <main class="p-4">
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h2><i class="fas fa-plus-circle me-2"></i>Thêm sách mới</h2>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="Admin_novels_controller.php">Quản lý sách</a></li>
                                    <li class="breadcrumb-item active">Thêm sách mới</li>
                                </ol>
                            </nav>
                        </div>
                        <a href="Admin_novels_controller.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Quay lại
                        </a>
                    </div>

                    <!-- Thông báo -->
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Form thêm sách -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-book me-2"></i>Thông tin sách
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" enctype="multipart/form-data" id="addNovelForm">
                                <div class="row">
                                    <!-- Cột trái -->
                                    <div class="col-md-8">
                                        <div class="mb-3">
                                            <label class="form-label">Tên sách <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="title" required 
                                                   placeholder="Nhập tên sách...">
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Tác giả <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="author" required 
                                                   placeholder="Nhập tên tác giả...">
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Mô tả</label>
                                            <textarea class="form-control" name="description" rows="5" 
                                                      placeholder="Nhập mô tả về sách..."></textarea>
                                        </div>

                                        <div class="row">
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
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Giá (VNĐ) <span class="text-danger">*</span></label>
                                                    <input type="number" class="form-control" name="price" min="0" step="1000" required 
                                                           placeholder="0">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Thể loại</label>
                                            <select class="form-select" name="categories[]" multiple id="categories-select">
                                                <?php if ($categories): ?>
                                                    <?php while ($category = $categories->fetch_assoc()): ?>
                                                        <option value="<?= $category['category_id'] ?>">
                                                            <?= htmlspecialchars($category['name']) ?>
                                                        </option>
                                                    <?php endwhile; ?>
                                                <?php endif; ?>
                                            </select>
                                            <div class="form-text">Có thể chọn nhiều thể loại</div>
                                        </div>
                                    </div>

                                    <!-- Cột phải -->
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">Ảnh bìa</label>
                                            <input type="file" class="form-control" name="cover_image" accept="image/*" id="coverImageInput">
                                            <div class="form-text">Chấp nhận: JPG, PNG, GIF</div>
                                        </div>

                                        <!-- Preview ảnh -->
                                        <div class="mb-3">
                                            <div id="imagePreview" class="text-center" style="display: none;">
                                                <img id="previewImg" src="" alt="Preview" class="img-thumbnail" style="max-width: 200px; max-height: 300px;">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Buttons -->
                                <div class="row">
                                    <div class="col-12">
                                        <hr>
                                        <div class="d-flex justify-content-between">
                                            <a href="Admin_novels_controller.php" class="btn btn-secondary">
                                                <i class="fas fa-times me-1"></i> Hủy
                                            </a>
                                            <button type="submit" name="save_novel" value="1" class="btn btn-success">
                                                <i class="fas fa-save me-1"></i> Lưu sách
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize Select2
            $('#categories-select').select2({
                theme: 'bootstrap-5',
                placeholder: "Chọn thể loại...",
                allowClear: true,
                width: '100%'
            });

            // Image preview
            $('#coverImageInput').on('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#previewImg').attr('src', e.target.result);
                        $('#imagePreview').show();
                    };
                    reader.readAsDataURL(file);
                } else {
                    $('#imagePreview').hide();
                }
            });

            // Form validation
            $('#addNovelForm').on('submit', function(e) {
                const title = $('input[name="title"]').val().trim();
                const author = $('input[name="author"]').val().trim();
                const status = $('select[name="status"]').val();
                const price = $('input[name="price"]').val();

                if (!title || !author || !status || price === '') {
                    e.preventDefault();
                    alert('Vui lòng điền đầy đủ thông tin bắt buộc!');
                    return false;
                }

                // Show loading
                const submitBtn = $(this).find('button[type="submit"]');
                const originalText = submitBtn.html();
                submitBtn.html('<i class="fas fa-spinner fa-spin me-1"></i> Đang lưu...').prop('disabled', true);

                // Let form submit
                return true;
            });
        });
    </script>
</body>
</html>
