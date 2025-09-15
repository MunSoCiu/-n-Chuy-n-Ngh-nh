<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mua truyện - <?= htmlspecialchars($data['novel']['title']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .purchase-container {
            max-width: 600px;
            margin: 0 auto;
        }
        .novel-cover {
            max-height: 200px;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <?php include '../../../includes/navbar.php'; ?>

    <div class="container py-5">
        
        <div class="purchase-container">
            <?php if (!empty($data['error_message'])): ?>
                <div class="alert alert-danger"><?= $data['error_message'] ?></div>
            <?php endif; ?>

            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="mb-0">Xác nhận mua truyện</h4>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <img src="<?= BASE_URL . '/' . ($data['novel']['cover_image'] ?: 'images/covers/default-cover.jpg') ?>" 
                                 class="img-fluid rounded novel-cover" 
                                 alt="<?= htmlspecialchars($data['novel']['title']) ?>">
                        </div>
                        <div class="col-md-8">
                            <h5><?= htmlspecialchars($data['novel']['title']) ?></h5>
                            <p class="text-muted mb-2">Tác giả: <?= htmlspecialchars($data['novel']['author']) ?></p>
                            <div class="mb-3">
                                <span class="badge bg-warning text-dark">
                                    <i class="fas fa-tag me-1"></i>
                                    Giá: <?= formatPrice($data['novel']['price']) ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <form method="POST" class="mt-4">

                        <div class="mb-3">
                            <label class="form-label">Phương thức thanh toán</label>
                            <select class="form-select" required>
                                <option value="wallet">Ví điện tử</option>
                                <option value="bank">Chuyển khoản ngân hàng</option>
                                <option value="card">Thẻ tín dụng/ghi nợ</option>
                            </select>
                        </div>

                        <div class="price-summary">
                            <div class="d-flex justify-content-between mb-3">
                                <strong>Tổng thanh toán:</strong>
                                <strong><?= formatPrice($data['novel']['price']) ?></strong>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-shopping-cart me-1"></i>
                                Xác nhận mua với giá <?= formatPrice($data['novel']['price']) ?>
                            </button>
                            <a href="../../../app/Controllers/User/Novel_controller.php?id=<?= $data['novel_id'] ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i>
                                Quay lại
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include '../../../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
