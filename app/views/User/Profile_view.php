<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hồ sơ cá nhân - Light Novel Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .profile-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
        }
        .avatar {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border: 4px solid white;
        }
        .novel-cover {
            width: 60px;
            height: 80px;
            object-fit: cover;
        }
        .card {
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border: none;
        }
    </style>
</head>
<body>
    <?php include '../../../includes/navbar.php'; ?>

    <div class="profile-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-auto">
                    <img src="<?= $data['user']['avatar_url'] ? '../../../' . $data['user']['avatar_url'] : '../../../images/avatars/Avatar.jpg' ?>" 
                         class="rounded-circle avatar" alt="Avatar">
                </div>
                <div class="col">
                    <h2 class="mb-1"><?= htmlspecialchars($data['user']['username']) ?></h2>
                    <p class="mb-0 opacity-75"><?= htmlspecialchars($data['user']['email']) ?></p>
                    <small class="opacity-75">Thành viên từ <?= date('d/m/Y', strtotime($data['user']['created_at'])) ?></small>
                </div>
            </div>
        </div>
    </div>

    <div class="container py-5">
        <?php if (!empty($data['error'])): ?>
            <div class="alert alert-danger"><?= $data['error'] ?></div>
        <?php endif; ?>
        
        <?php if (!empty($data['success'])): ?>
            <div class="alert alert-success"><?= $data['success'] ?></div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- Cập nhật thông tin -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-user-edit me-2"></i>Cập nhật thông tin
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label">Tên đăng nhập</label>
                                <input type="text" class="form-control" name="username" 
                                       value="<?= htmlspecialchars($data['user']['username']) ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" 
                                       value="<?= htmlspecialchars($data['user']['email']) ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Mật khẩu hiện tại</label>
                                <input type="password" class="form-control" name="current_password" 
                                       placeholder="Nhập mật khẩu hiện tại để thay đổi mật khẩu">
                                <small class="text-muted">Chỉ cần nhập khi muốn thay đổi mật khẩu</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Mật khẩu mới (để trống nếu không đổi)</label>
                                <input type="password" class="form-control" name="password" 
                                       placeholder="Nhập mật khẩu mới">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Avatar</label>
                                <input type="file" class="form-control" name="avatar" accept="image/*">
                                <small class="text-muted">Chỉ chấp nhận file ảnh (JPG, PNG, GIF), tối đa 2MB</small>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Cập nhật
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Thống kê -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-bar me-2"></i>Thống kê
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="border-end">
                                    <h4 class="text-primary"><?= $data['reading_history']->num_rows ?></h4>
                                    <small class="text-muted">Truyện đã đọc</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="border-end">
                                    <h4 class="text-danger"><?= $data['favorites']->num_rows ?></h4>
                                    <small class="text-muted">Yêu thích</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <h4 class="text-success">
                                    <?php
                                    $purchases_query = "SELECT COUNT(*) as count FROM Purchases WHERE user_id = ?";
                                    $stmt = $GLOBALS['conn']->prepare($purchases_query);
                                    $stmt->bind_param("i", $data['user']['user_id']);
                                    $stmt->execute();
                                    echo $stmt->get_result()->fetch_assoc()['count'];
                                    ?>
                                </h4>
                                <small class="text-muted">Đã mua</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lịch sử đọc gần đây -->
                <div class="card mt-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <i class="fas fa-history me-2"></i>Lịch sử đọc gần đây
                        </h6>
                        <a href="../../../app/Controllers/User/History_controller.php" class="btn btn-sm btn-outline-primary">
                            Xem tất cả
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if ($data['reading_history']->num_rows > 0): ?>
                            <?php $count = 0; ?>
                            <?php while (($item = $data['reading_history']->fetch_assoc()) && $count < 5): ?>
                                <div class="d-flex align-items-center mb-3">
                                    <img src="<?= BASE_URL . '/' . ($item['cover_image'] ?: 'images/covers/default-cover.jpg') ?>" 
                                         class="novel-cover me-3" alt="<?= htmlspecialchars($item['title']) ?>">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">
                                            <a href="../../../app/Controllers/User/Novel_controller.php?id=<?= $item['novel_id'] ?>" 
                                               class="text-decoration-none">
                                                <?= htmlspecialchars($item['title']) ?>
                                            </a>
                                        </h6>
                                        <small class="text-muted">
                                            <?= htmlspecialchars($item['chapter_title']) ?>
                                        </small>
                                        <br>
                                        <small class="text-muted">
                                            <?= timeAgo($item['last_read']) ?>
                                        </small>
                                    </div>
                                </div>
                                <?php $count++; ?>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p class="text-muted text-center">Chưa có lịch sử đọc</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Truyện yêu thích -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-heart text-danger me-2"></i>Truyện yêu thích
                        </h5>
                        <a href="../../../app/Controllers/User/Favorites_controller.php" class="btn btn-sm btn-outline-primary">
                            Xem tất cả
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if ($data['favorites']->num_rows > 0): ?>
                            <div class="row g-3">
                                <?php $count = 0; ?>
                                <?php while (($novel = $data['favorites']->fetch_assoc()) && $count < 6): ?>
                                    <div class="col-md-2">
                                        <div class="text-center">
                                            <img src="<?= BASE_URL . '/' . ($novel['cover_image'] ?: 'images/covers/default-cover.jpg') ?>" 
                                                 class="img-fluid rounded mb-2" style="height: 150px; object-fit: cover;" 
                                                 alt="<?= htmlspecialchars($novel['title']) ?>">
                                            <h6 class="small">
                                                <a href="../../../app/Controllers/User/Novel_controller.php?id=<?= $novel['novel_id'] ?>" 
                                                   class="text-decoration-none">
                                                    <?= htmlspecialchars($novel['title']) ?>
                                                </a>
                                            </h6>
                                        </div>
                                    </div>
                                    <?php $count++; ?>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted text-center">Chưa có truyện yêu thích</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../../../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
