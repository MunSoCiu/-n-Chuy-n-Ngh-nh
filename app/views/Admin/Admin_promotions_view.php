<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý mã giảm giá - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .admin-sidebar {
            min-height: calc(100vh - 56px);
        }
        .promo-code {
            font-family: 'Courier New', monospace;
            background: #f8f9fa;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            border: 1px solid #dee2e6;
        }
    </style>
</head>
<body>
    <?php include '../../../includes/navbar.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <?php 
            $current_page = 'promotions';
            include '../../../includes/admin_sidebar.php'; 
            ?>
            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <h1 class="h2 mb-4">Quản lý mã giảm giá</h1>

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

                <!-- Button thêm mã giảm giá mới -->
                <div class="mb-4">
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#promotionModal">
                        <i class="fas fa-plus me-1"></i> Thêm mã giảm giá mới
                    </button>
                </div>

                <!-- Danh sách mã giảm giá -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Mã</th>
                                        <th>Giảm giá</th>
                                        <th>Ngày bắt đầu</th>
                                        <th>Ngày kết thúc</th>
                                        <th>Trạng thái</th>
                                        <th>Đã gán</th>
                                        <th>Mô tả</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($data['promotions']->num_rows > 0): ?>
                                        <?php while ($promo = $data['promotions']->fetch_assoc()): ?>
                                            <?php 
                                            $now = time();
                                            $start = strtotime($promo['start_date']);
                                            $end = strtotime($promo['end_date']);
                                            $status = '';
                                            $status_class = '';
                                            
                                            if ($now < $start) {
                                                $status = 'Chưa bắt đầu';
                                                $status_class = 'bg-warning';
                                            } elseif ($now > $end) {
                                                $status = 'Đã hết hạn';
                                                $status_class = 'bg-danger';
                                            } else {
                                                $status = 'Đang hoạt động';
                                                $status_class = 'bg-success';
                                            }
                                            ?>
                                            <tr>
                                                <td><code class="promo-code"><?= htmlspecialchars($promo['code']) ?></code></td>
                                                <td><?= $promo['discount_percentage'] ?>%</td>
                                                <td><?= date('d/m/Y', strtotime($promo['start_date'])) ?></td>
                                                <td><?= date('d/m/Y', strtotime($promo['end_date'])) ?></td>
                                                <td>
                                                    <span class="badge <?= $status_class ?>"><?= $status ?></span>
                                                </td>
                                                <td><?= $promo['assigned_count'] ?> người</td>
                                                <td><?= htmlspecialchars($promo['description']) ?></td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                                onclick="editPromotion(<?= htmlspecialchars(json_encode($promo)) ?>)">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-info" 
                                                                onclick="assignPromotion(<?= $promo['promo_id'] ?>, '<?= htmlspecialchars($promo['code']) ?>')">
                                                            <i class="fas fa-user-plus"></i>
                                                        </button>
                                                        <form method="POST" style="display: inline;" 
                                                              onsubmit="return confirm('Bạn có chắc chắn muốn xóa mã giảm giá này?')">
                                                            <button type="submit" name="delete_promotion" value="<?= $promo['promo_id'] ?>" 
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
                                            <td colspan="8" class="text-center">Không có mã giảm giá nào.</td>
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

    <!-- Modal thêm/sửa mã giảm giá -->
    <div class="modal fade" id="promotionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="promotionModalTitle">Thêm mã giảm giá mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="promo_id" id="promo_id">
                        
                        <div class="mb-3">
                            <label class="form-label">Mã giảm giá *</label>
                            <input type="text" class="form-control" name="code" id="code" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Phần trăm giảm giá (%) *</label>
                            <input type="number" class="form-control" name="discount_percentage" id="discount_percentage" 
                                   min="1" max="100" required>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Ngày bắt đầu *</label>
                                    <input type="datetime-local" class="form-control" name="start_date" id="start_date" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Ngày kết thúc *</label>
                                    <input type="datetime-local" class="form-control" name="end_date" id="end_date" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Mô tả</label>
                            <textarea class="form-control" name="description" id="description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" name="save_promotion" class="btn btn-primary">Lưu</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal gán mã giảm giá cho user -->
    <div class="modal fade" id="assignModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Gán mã giảm giá cho người dùng</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="promo_id" id="assign_promo_id">
                        
                        <div class="mb-3">
                            <label class="form-label">Mã giảm giá</label>
                            <input type="text" class="form-control" id="assign_promo_code" readonly>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Chọn người dùng *</label>
                            <select class="form-select" name="user_id" required>
                                <option value="">-- Chọn người dùng --</option>
                                <?php while ($user = $data['users']->fetch_assoc()): ?>
                                    <option value="<?= $user['user_id'] ?>">
                                        <?= htmlspecialchars($user['username']) ?> (<?= htmlspecialchars($user['email']) ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" name="assign_promotion" class="btn btn-primary">Gán mã giảm giá</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editPromotion(promo) {
            document.getElementById('promotionModalTitle').textContent = 'Sửa mã giảm giá';
            document.getElementById('promo_id').value = promo.promo_id;
            document.getElementById('code').value = promo.code;
            document.getElementById('discount_percentage').value = promo.discount_percentage;
            document.getElementById('start_date').value = promo.start_date.replace(' ', 'T');
            document.getElementById('end_date').value = promo.end_date.replace(' ', 'T');
            document.getElementById('description').value = promo.description;
            
            var modal = new bootstrap.Modal(document.getElementById('promotionModal'));
            modal.show();
        }
        
        function assignPromotion(promoId, promoCode) {
            document.getElementById('assign_promo_id').value = promoId;
            document.getElementById('assign_promo_code').value = promoCode;
            
            var modal = new bootstrap.Modal(document.getElementById('assignModal'));
            modal.show();
        }
        
        // Reset form when modal is hidden
        document.getElementById('promotionModal').addEventListener('hidden.bs.modal', function() {
            document.getElementById('promotionModalTitle').textContent = 'Thêm mã giảm giá mới';
            document.querySelector('#promotionModal form').reset();
        });
    </script>
</body>
</html>
