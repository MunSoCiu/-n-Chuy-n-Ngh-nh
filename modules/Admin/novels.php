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

// Xử lý xóa truyện
if (isset($_POST['delete_novel'])) {
    $novel_id = (int)$_POST['delete_novel'];
    
    try {
        // Xóa các bản ghi liên quan trước
        $conn->query("DELETE FROM Favorites WHERE novel_id = $novel_id");
        $conn->query("DELETE FROM Comments WHERE novel_id = $novel_id");
        $conn->query("DELETE FROM Reading_History WHERE novel_id = $novel_id");
        $conn->query("DELETE FROM Purchases WHERE novel_id = $novel_id");
        $conn->query("DELETE FROM Novel_Categories WHERE novel_id = $novel_id");
        $conn->query("DELETE FROM Chapters WHERE novel_id = $novel_id");
        
        // Cuối cùng xóa truyện
        $conn->query("DELETE FROM LightNovels WHERE novel_id = $novel_id");
        
        $_SESSION['success'] = "Đã xóa sách thành công!";
    } catch (Exception $e) {
        $_SESSION['error'] = "Có lỗi xảy ra khi xóa sách: " . $e->getMessage();
    }
    
    header("Location: novels.php");
    exit();
}

// Xử lý thêm/sửa/xóa chapter
if (isset($_POST['chapter_action'])) {
    $novel_id = (int)$_POST['novel_id'];
    
    switch ($_POST['chapter_action']) {
        case 'add_chapter':
            $title = $conn->real_escape_string($_POST['chapter_title']);
            $content = $conn->real_escape_string($_POST['chapter_content']);
            $conn->query("INSERT INTO Chapters (novel_id, title, content) VALUES ($novel_id, '$title', '$content')");
            break;
            
        case 'edit_chapter':
            $chapter_id = (int)$_POST['chapter_id'];
            $title = $conn->real_escape_string($_POST['chapter_title']);
            $content = $conn->real_escape_string($_POST['chapter_content']);
            $conn->query("UPDATE Chapters SET title = '$title', content = '$content' WHERE chapter_id = $chapter_id");
            break;
            
        case 'delete_chapter':
            $chapter_id = (int)$_POST['chapter_id'];
            $conn->query("DELETE FROM Chapters WHERE chapter_id = $chapter_id");
            break;
    }
    
    header("Location: edit_novel.php?id=$novel_id&tab=chapters");
    exit();
}

// Xây dựng query với điều kiện tìm kiếm
$query = "SELECT n.*, GROUP_CONCAT(c.name) as categories 
         FROM LightNovels n 
         LEFT JOIN Novel_Categories nc ON n.novel_id = nc.novel_id
         LEFT JOIN Categories c ON nc.category_id = c.category_id
         WHERE 1=1";

// Thêm điều kiện tìm kiếm
if (!empty($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
    $query .= " AND (n.title LIKE '%$search%' OR n.author LIKE '%$search%')";
}

if (!empty($_GET['category'])) {
    $category_id = (int)$_GET['category'];
    $query .= " AND nc.category_id = $category_id";
}

if (!empty($_GET['status'])) {
    $status = $conn->real_escape_string($_GET['status']);
    $query .= " AND n.status = '$status'";
}

$query .= " GROUP BY n.novel_id ORDER BY n.created_at DESC";
$novels = $conn->query($query);

// Xử lý thêm/sửa truyện
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_novel'])) {
    $novel_id = isset($_POST['novel_id']) ? (int)$_POST['novel_id'] : null;
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $description = trim($_POST['description']);
    $status = $_POST['status'];
    $price = (float)$_POST['price'];
    $categories = isset($_POST['categories']) ? $_POST['categories'] : [];

    // Xử lý upload ảnh bìa
    $cover_image = null;
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['size'] > 0) {
        $upload_dir = "../../uploads/covers/";
        $filename = uniqid() . "_" . basename($_FILES['cover_image']['name']);
        $target_file = $upload_dir . $filename;
        
        if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $target_file)) {
            $cover_image = "uploads/covers/" . $filename;
            
            // Xóa ảnh cũ nếu đang sửa truyện
            if ($novel_id) {
                $old_cover_query = "SELECT cover_image FROM LightNovels WHERE novel_id = ?";
                $stmt = $conn->prepare($old_cover_query);
                $stmt->bind_param("i", $novel_id);
                $stmt->execute();
                $old_cover = $stmt->get_result()->fetch_assoc()['cover_image'];
                if ($old_cover && file_exists("../../" . $old_cover)) {
                    unlink("../../" . $old_cover);
                }
            }
        }
    }

    // Bắt đầu transaction
    $conn->begin_transaction();
    try {
        if ($novel_id) {
            // Cập nhật truyện
            $update_query = "UPDATE LightNovels SET 
                title = ?, author = ?, description = ?, status = ?, price = ?
                " . ($cover_image ? ", cover_image = ?" : "") . "
                WHERE novel_id = ?";
            
            $stmt = $conn->prepare($update_query);
            if ($cover_image) {
                $stmt->bind_param("ssssdsi", $title, $author, $description, $status, $price, $cover_image, $novel_id);
            } else {
                $stmt->bind_param("ssssdi", $title, $author, $description, $status, $price, $novel_id);
            }
            $stmt->execute();
        } else {
            // Thêm truyện mới
            $insert_query = "INSERT INTO LightNovels (title, author, description, status, price, cover_image) 
                           VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("ssssds", $title, $author, $description, $status, $price, $cover_image);
            $stmt->execute();
            $novel_id = $conn->insert_id;
        }

        // Xóa categories cũ nếu đang sửa truyện
        if ($novel_id) {
            $delete_categories = "DELETE FROM Novel_Categories WHERE novel_id = ?";
            $stmt = $conn->prepare($delete_categories);
            $stmt->bind_param("i", $novel_id);
            $stmt->execute();
        }

        // Thêm categories mới
        if (!empty($categories)) {
            $category_values = [];
            $category_params = [];
            
            foreach ($categories as $category_id) {
                $category_values[] = "(?, ?)";
                $category_params[] = $novel_id;
                $category_params[] = $category_id;
            }
            
            $category_query = "INSERT INTO Novel_Categories (novel_id, category_id) VALUES " . 
                            implode(", ", $category_values);
            $stmt = $conn->prepare($category_query);
            
            $types = str_repeat('ii', count($categories));
            $stmt->bind_param($types, ...$category_params);
            $stmt->execute();
        }

        $conn->commit();
        $_SESSION['success'] = "Đã lưu sách thành công!";
        header("Location: novels.php");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Có lỗi xảy ra: " . $e->getMessage();
    }
}

// Lấy danh sách thể loại
$categories = $conn->query("SELECT * FROM Categories ORDER BY name");

// Lấy thông tin truyện cần sửa
$edit_novel = null;
if (isset($_GET['edit'])) {
    $novel_id = (int)$_GET['edit'];
    $edit_query = "SELECT n.*, GROUP_CONCAT(nc.category_id) as category_ids 
                  FROM LightNovels n 
                  LEFT JOIN Novel_Categories nc ON n.novel_id = nc.novel_id 
                  WHERE n.novel_id = ?
                  GROUP BY n.novel_id";
    $stmt = $conn->prepare($edit_query);
    $stmt->bind_param("i", $novel_id);
    $stmt->execute();
    $edit_novel = $stmt->get_result()->fetch_assoc();
    if ($edit_novel) {
        $edit_novel['category_ids'] = $edit_novel['category_ids'] ? 
            explode(',', $edit_novel['category_ids']) : [];
    }
}

// Lấy danh sách truyện với phân trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$total_query = "SELECT COUNT(*) as total FROM LightNovels";
$total_novels = $conn->query($total_query)->fetch_assoc()['total'];

$novels_query = "SELECT n.*, 
                 GROUP_CONCAT(c.name) as categories,
                 (SELECT COUNT(*) FROM Chapters WHERE novel_id = n.novel_id) as chapter_count
                 FROM LightNovels n
                 LEFT JOIN Novel_Categories nc ON n.novel_id = nc.novel_id
                 LEFT JOIN Categories c ON nc.category_id = c.category_id
                 GROUP BY n.novel_id
                 ORDER BY n.created_at DESC
                 LIMIT ? OFFSET ?";
$stmt = $conn->prepare($novels_query);
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$novels = $stmt->get_result();

// Lấy lại danh sách thể loại cho form (reset con trỏ)
$categories_for_form = $conn->query("SELECT * FROM Categories ORDER BY name");

?>

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
    <?php include '../../includes/navbar.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <?php 
            $current_page = 'novels';
            include '../../includes/admin_sidebar.php'; 
            ?>
            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <h1 class="h2 mb-4">Quản lý sách</h1>

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

                <!-- Form tìm kiếm -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <input type="text" class="form-control" name="search" 
                                       value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>" 
                                       placeholder="Tìm theo tên sách hoặc tác giả...">
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="category">
                                    <option value="">Tất cả thể loại</option>
                                    <?php
                                    // Reset categories để sử dụng lại
                                    $categories = $conn->query("SELECT * FROM Categories ORDER BY name");
                                    while ($cat = $categories->fetch_assoc()):
                                    ?>
                                        <option value="<?= $cat['category_id'] ?>" 
                                                <?= isset($_GET['category']) && $_GET['category'] == $cat['category_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cat['name']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="status">
                                    <option value="">Tất cả trạng thái</option>
                                    <option value="Đang tiến hành" <?= isset($_GET['status']) && $_GET['status'] === 'Đang tiến hành' ? 'selected' : '' ?>>
                                        Đang tiến hành
                                    </option>
                                    <option value="Đã hoàn thành" <?= isset($_GET['status']) && $_GET['status'] === 'Đã hoàn thành' ? 'selected' : '' ?>>
                                        Đã hoàn thành
                                    </option>
                                    <option value="Đã hủy bỏ" <?= isset($_GET['status']) && $_GET['status'] === 'Đã hủy bỏ' ? 'selected' : '' ?>>
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

                <!-- Form thêm/sửa sách -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><?= $edit_novel ? 'Sửa sách' : 'Thêm sách mới' ?></h5>
                    </div>
                    <div class="card-body">
                        <form action="" method="POST" enctype="multipart/form-data">
                            <?php if ($edit_novel): ?>
                                <input type="hidden" name="novel_id" value="<?= $edit_novel['novel_id'] ?>">
                            <?php endif; ?>

                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label class="form-label">Tên sách</label>
                                        <input type="text" class="form-control" name="title" required
                                               value="<?= $edit_novel ? htmlspecialchars($edit_novel['title']) : '' ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Tác giả</label>
                                        <input type="text" class="form-control" name="author" required
                                               value="<?= $edit_novel ? htmlspecialchars($edit_novel['author']) : '' ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Mô tả sách</label>
                                        <textarea class="form-control" name="description" rows="4"><?= $edit_novel ? htmlspecialchars($edit_novel['description']) : '' ?></textarea>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Ảnh bìa</label>
                                        <input type="file" class="form-control" name="cover_image" accept="image/*">
                                        <?php if ($edit_novel && $edit_novel['cover_image']): ?>
                                            <img src="../../<?= htmlspecialchars($edit_novel['cover_image']) ?>" 
                                                 class="mt-2 novel-cover-preview">
                                        <?php endif; ?>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Trạng thái</label>
                                        <select class="form-select" name="status" required>
                                            <option value="Đang tiến hành" <?= ($edit_novel && $edit_novel['status'] == 'Đang tiến hành') ? 'selected' : '' ?>>
                                                Đang tiến hành
                                            </option>
                                            <option value="Đã hoàn thành" <?= ($edit_novel && $edit_novel['status'] == 'Đã hoàn thành') ? 'selected' : '' ?>>
                                                Đã hoàn thành
                                            </option>
                                            <option value="Đã hủy bỏ" <?= ($edit_novel && $edit_novel['status'] == 'Đã hủy bỏ') ? 'selected' : '' ?>>
                                                Đã hủy bỏ
                                            </option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Giá (đ)</label>
                                        <input type="number" class="form-control" name="price" min="0" step="1000" required
                                               value="<?= $edit_novel ? $edit_novel['price'] : '0' ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Thể loại</label>
                                        <select class="form-select" name="categories[]" multiple>
                                            <?php 
                                            // Sử dụng categories_for_form đã được reset
                                            while ($category = $categories_for_form->fetch_assoc()): 
                                            ?>
                                                <option value="<?= $category['category_id'] ?>"
                                                    <?= ($edit_novel && in_array($category['category_id'], $edit_novel['category_ids'])) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($category['name']) ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" name="save_novel" class="btn btn-primary">
                                <?= $edit_novel ? 'Cập nhật' : 'Thêm mới' ?>
                            </button>
                            <?php if ($edit_novel): ?>
                                <a href="novels.php" class="btn btn-secondary">Hủy</a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

                <!-- Danh sách sách -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Danh sách sách</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Ảnh bìa</th>
                                        <th>Tên sách</th>
                                        <th>Tác giả</th>
                                        <th>Thể loại</th>
                                        <th>Số chapter</th>
                                        <th>Trạng thái</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($novel = $novels->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $novel['novel_id'] ?></td>
                                        <td>
                                            <?php if ($novel['cover_image']): ?>
                                                <img src="../../<?= htmlspecialchars($novel['cover_image']) ?>" 
                                                     alt="Cover" style="height: 50px;">
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($novel['title']) ?></td>
                                        <td><?= htmlspecialchars($novel['author']) ?></td>
                                        <td>
                                            <?php 
                                            $categories = !is_null($novel['categories']) ? explode(',', $novel['categories']) : [];
                                            foreach ($categories as $cat) {
                                                if ($cat) {
                                                    echo "<span class='badge bg-secondary me-1'>".htmlspecialchars($cat)."</span>";
                                                }
                                            }
                                            ?>
                                        </td>
                                        <td><?= $novel['chapter_count'] ?></td>
                                        <td>
                                            <span class="badge bg-<?= $novel['status'] === 'Đã hoàn thành' ? 'success' : 
                                                ($novel['status'] === 'Đã hủy bỏ' ? 'danger' : 'primary') ?>">
                                                <?= htmlspecialchars($novel['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="?edit=<?= $novel['novel_id'] ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="edit_novel.php?id=<?= $novel['novel_id'] ?>&tab=chapters" 
                                               class="btn btn-info btn-sm" title="Quản lý chapter">
                                                <i class="fas fa-list"></i>
                                            </a>
                                            <form action="" method="POST" class="d-inline" 
                                                  onsubmit="return confirm('Bạn có chắc muốn xóa sách này?')">
                                                <button type="submit" name="delete_novel" value="<?= $novel['novel_id'] ?>" 
                                                        class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Phân trang -->
                        <?php if ($total_novels > $limit): ?>
                            <div class="mt-4">
                                <?= pagination($total_novels, $page, $limit, "novels.php?page=%d") ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
    <script>
        $(document).ready(function() {
            $('select[name="categories[]"]').select2({
                placeholder: "Chọn thể loại",
                allowClear: true
            });
        });
    </script>
</body>
</html> 