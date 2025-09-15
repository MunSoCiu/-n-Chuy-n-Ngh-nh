<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa truyện - <?= htmlspecialchars($data['novel']['title']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../../../includes/navbar.php'; ?>

    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>
                <i class="fas fa-edit"></i> 
                Sửa truyện: <?= htmlspecialchars($data['novel']['title']) ?>
            </h2>
            <a href="../../../app/Controllers/Admin/Admin_novels_controller.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Quay lại
            </a>
        </div>

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

        <!-- Tab Navigation -->
        <ul class="nav nav-tabs mb-4">
            <li class="nav-item">
                <a class="nav-link <?= $data['current_tab'] === 'info' ? 'active' : '' ?>" 
                   href="?id=<?= $data['novel']['novel_id'] ?>&tab=info">
                    <i class="fas fa-info-circle me-1"></i> Thông tin truyện
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $data['current_tab'] === 'chapters' ? 'active' : '' ?>" 
                   href="?id=<?= $data['novel']['novel_id'] ?>&tab=chapters">
                    <i class="fas fa-list me-1"></i> Quản lý chapter
                </a>
            </li>
        </ul>

        <?php if ($data['current_tab'] === 'chapters'): ?>
            <!-- Tab Quản lý Chapter -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">Thêm chapter mới</h5>
                    <form method="POST">
                        <input type="hidden" name="chapter_action" value="add_chapter">
                        <input type="hidden" name="novel_id" value="<?= $data['novel']['novel_id'] ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Tiêu đề chapter</label>
                            <input type="text" class="form-control" name="chapter_title" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Nội dung</label>
                            <textarea class="form-control summernote" name="chapter_content" rows="10" required></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i> Thêm chapter
                        </button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-3">Danh sách chapter</h5>
                    
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tiêu đề</th>
                                    <th>Ngày tạo</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($chapter = $data['chapters']->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $chapter['chapter_id'] ?></td>
                                        <td><?= htmlspecialchars($chapter['title']) ?></td>
                                        <td><?= date('d/m/Y H:i', strtotime($chapter['created_at'])) ?></td>
                                        <td>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-primary btn-sm"
                                                        onclick="editChapter(<?= $chapter['chapter_id'] ?>, 
                                                                           '<?= addslashes($chapter['title']) ?>', 
                                                                           '<?= addslashes($chapter['content']) ?>')">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form method="POST" class="d-inline"
                                                      onsubmit="return confirm('Bạn có chắc muốn xóa chapter này?')">
                                                    <input type="hidden" name="chapter_action" value="delete_chapter">
                                                    <input type="hidden" name="novel_id" value="<?= $data['novel']['novel_id'] ?>">
                                                    <input type="hidden" name="chapter_id" value="<?= $chapter['chapter_id'] ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Modal Sửa Chapter -->
            <div class="modal fade" id="editChapterModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Sửa chapter</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form method="POST">
                            <div class="modal-body">
                                <input type="hidden" name="chapter_action" value="edit_chapter">
                                <input type="hidden" name="novel_id" value="<?= $data['novel']['novel_id'] ?>">
                                <input type="hidden" name="chapter_id" id="edit_chapter_id">
                                
                                <div class="mb-3">
                                    <label class="form-label">Tiêu đề chapter</label>
                                    <input type="text" class="form-control" name="chapter_title" 
                                           id="edit_chapter_title" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Nội dung</label>
                                    <textarea class="form-control summernote" name="chapter_content" 
                                              id="edit_chapter_content" rows="10" required></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <!-- Tab Thông tin truyện -->
            <div class="card">
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="update_novel" value="1">
                        <input type="hidden" name="novel_id" value="<?= $data['novel']['novel_id'] ?>">
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label">Tên truyện</label>
                                    <input type="text" class="form-control" name="title" 
                                           value="<?= htmlspecialchars($data['novel']['title']) ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Tác giả</label>
                                    <input type="text" class="form-control" name="author" 
                                           value="<?= htmlspecialchars($data['novel']['author']) ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Mô tả</label>
                                    <textarea class="form-control" name="description" rows="5"><?= htmlspecialchars($data['novel']['description']) ?></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Thể loại</label>
                                    <select class="form-select" name="categories[]" multiple id="edit-categories-select">
                                        <?php 
                                        $selected_categories = $data['novel']['category_ids'];
                                        while ($category = $data['categories']->fetch_assoc()):
                                        ?>
                                            <option value="<?= $category['category_id'] ?>"
                                                    <?= in_array($category['category_id'], $selected_categories) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($category['name']) ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Ảnh bìa hiện tại</label>
                                    <img src="<?= BASE_URL . '/' . ($data['novel']['cover_image'] ?: 'images/covers/default-cover.jpg') ?>" 
                                         class="img-fluid rounded mb-2" alt="Cover">
                                    <input type="file" class="form-control" name="cover_image" accept="image/*">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Giá</label>
                                    <input type="number" class="form-control" name="price" 
                                           value="<?= $data['novel']['price'] ?>" min="0" step="1000">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Trạng thái</label>
                                    <select class="form-select" name="status">
                                        <option value="Đang tiến hành" <?= $data['novel']['status'] === 'Đang tiến hành' ? 'selected' : '' ?>>
                                            Đang tiến hành
                                        </option>
                                        <option value="Đã hoàn thành" <?= $data['novel']['status'] === 'Đã hoàn thành' ? 'selected' : '' ?>>
                                            Đã hoàn thành
                                        </option>
                                        <option value="Đã hủy bỏ" <?= $data['novel']['status'] === 'Đã hủy bỏ' ? 'selected' : '' ?>>
                                            Đã hủy bỏ
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Lưu thay đổi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php include '../../../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
    <script>
    $(document).ready(function() {
        // Khởi tạo Select2 cho thể loại
        $('#edit-categories-select').select2({
            placeholder: "Chọn thể loại...",
            allowClear: true
        });
        
        // Khởi tạo Summernote
        $('.summernote').summernote({
            height: 300,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['insert', ['link']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ]
        });
    });

    // Hàm mở modal sửa chapter
    function editChapter(chapterId, title, content) {
        $('#edit_chapter_id').val(chapterId);
        $('#edit_chapter_title').val(title);
        $('#edit_chapter_content').summernote('code', content);
        $('#editChapterModal').modal('show');
    }
    </script>
</body>
</html>
