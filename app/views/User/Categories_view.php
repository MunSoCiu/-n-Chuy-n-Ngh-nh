<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php
        if (isset($data['type']) && $data['type'] === 'free') {
            echo 'Sách miễn phí';
        } elseif (isset($data['type']) && $data['type'] === 'paid') {
            echo 'Sách trả phí';
        } elseif (isset($data['category_info']) && $data['category_info']) {
            echo htmlspecialchars($data['category_info']['name']);
        } else {
            echo 'Tất cả thể loại';
        }
        ?> - Light Novel Hub
    </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .category-card {
            transition: transform 0.2s;
        }
        .category-card:hover {
            transform: translateY(-5px);
        }
        .novel-card {
            transition: transform 0.2s;
        }
        .novel-card:hover {
            transform: translateY(-5px);
        }
        .novel-cover {
            height: 250px;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <?php include '../../../includes/navbar.php'; ?>

    <div class="container py-5">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="<?= BASE_URL ?>/index.php">Trang chủ</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="<?= $_SERVER['PHP_SELF'] ?>">Thể loại</a>
                </li>
                <?php if (isset($data['type']) && $data['type'] === 'free'): ?>
                    <li class="breadcrumb-item active">Sách miễn phí</li>
                <?php elseif (isset($data['type']) && $data['type'] === 'paid'): ?>
                    <li class="breadcrumb-item active">Sách trả phí</li>
                <?php elseif (isset($data['category_info']) && $data['category_info']): ?>
                    <li class="breadcrumb-item active">
                        <?= htmlspecialchars($data['category_info']['name']) ?>
                    </li>
                <?php endif; ?>
            </ol>
        </nav>

        <div class="row">
            <!-- Sidebar thể loại -->
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-tags me-2"></i>Thể loại
                        </h5>
                    </div>
                    <div class="list-group list-group-flush">
                        <?php while ($category = $data['categories']->fetch_assoc()): ?>
                            <a href="?id=<?= $category['category_id'] ?>" 
                               class="list-group-item list-group-item-action d-flex justify-content-between align-items-center
                                      <?= $data['selected_category'] === $category['category_id'] ? 'active' : '' ?>">
                                <?= htmlspecialchars($category['name']) ?>
                                <span class="badge bg-secondary rounded-pill">
                                    <?= $category['novel_count'] ?>
                                </span>
                            </a>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>

            <!-- Danh sách truyện -->
            <div class="col-md-9">
                <!-- Page Header -->
                <div class="mb-4">
                    <h2 class="mb-3">
                        <?php
                        if (isset($data['type']) && $data['type'] === 'free') {
                            echo '<i class="fas fa-gift me-2 text-success"></i>Sách miễn phí';
                        } elseif (isset($data['type']) && $data['type'] === 'paid') {
                            echo '<i class="fas fa-crown me-2 text-warning"></i>Sách trả phí';
                        } elseif (isset($data['category_info']) && $data['category_info']) {
                            echo '<i class="fas fa-tag me-2"></i>' . htmlspecialchars($data['category_info']['name']);
                        } else {
                            echo '<i class="fas fa-books me-2"></i>Tất cả thể loại';
                        }
                        ?>
                    </h2>
                    <?php if (isset($data['type']) && $data['type'] === 'free'): ?>
                        <p class="text-muted">Khám phá những cuốn tiểu thuyết miễn phí hấp dẫn</p>
                    <?php elseif (isset($data['type']) && $data['type'] === 'paid'): ?>
                        <p class="text-muted">Những tác phẩm cao cấp đáng đầu tư</p>
                    <?php endif; ?>
                </div>

                <!-- Form tìm kiếm -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <?php if ($data['selected_category']): ?>
                                <input type="hidden" name="id" value="<?= $data['selected_category'] ?>">
                            <?php endif; ?>
                            <?php if (isset($data['type']) && $data['type'] !== 'all'): ?>
                                <input type="hidden" name="type" value="<?= $data['type'] ?>">
                            <?php endif; ?>
                            
                            <div class="col-md-8">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="search" 
                                           placeholder="Tìm kiếm theo tên truyện hoặc tác giả..." 
                                           value="<?= htmlspecialchars($data['search']) ?>">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="fas fa-search me-1"></i> Tìm kiếm
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <?php if (isset($data['novels']) && $data['novels'] && $data['novels']->num_rows > 0): ?>
                    <div class="row g-4">
                        <?php while ($novel = $data['novels']->fetch_assoc()): ?>
                            <?= renderNovelCard($novel) ?>
                        <?php endwhile; ?>
                    </div>

                    <!-- Phân trang -->
                    <?php if ($data['total_pages'] > 1): ?>
                        <nav class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php for ($i = 1; $i <= $data['total_pages']; $i++): ?>
                                    <li class="page-item <?= $i === $data['page'] ? 'active' : '' ?>">
                                        <a class="page-link" 
                                           href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-book fa-3x mb-3 text-muted"></i>
                        <h3>Không tìm thấy truyện nào</h3>
                        <p class="text-muted">Thử tìm kiếm với từ khóa khác hoặc chọn thể loại khác.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include '../../../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
