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

                    <!-- Chọn phương thức thanh toán -->
                    <?php if ($data['paymentMethod'] === null): ?>
                        <h5>Chọn phương thức thanh toán</h5>
                        <form method="post" action="">
                            <button type="submit" name="payment_method" value="wallet" class="btn btn-outline-primary w-100 mb-2">
                                <i class="fas fa-wallet me-1"></i> Ví điện tử
                            </button>
                            <button type="submit" name="payment_method" value="bank" class="btn btn-outline-success w-100 mb-2">
                                <i class="fas fa-university me-1"></i> Chuyển khoản ngân hàng
                            </button>
                            <button type="submit" name="payment_method" value="card" class="btn btn-outline-danger w-100">
                                <i class="fas fa-credit-card me-1"></i> Thẻ tín dụng / ghi nợ
                            </button>
                        </form>

                    <?php elseif ($data['paymentMethod'] === 'wallet'): ?>
                        <h5>Thanh toán qua Ví điện tử</h5>
                        <p>Vui lòng quét mã QR bên dưới để thanh toán:</p>
                        <img src="<?= BASE_URL ?>/uploads/covers/ViDienTu.jpg" 
                             alt="QR Ví điện tử" class="img-fluid mb-3" style="max-width:300px;">
                        <form method="post" action="">
                            <input type="hidden" name="payment_method" value="wallet">
                            <button type="submit" name="confirm_payment" value="1" class="btn btn-success">Xác nhận đã thanh toán</button>
                        </form>
                        <form method="post" action="" class="mt-2">
                            <button type="submit" class="btn btn-secondary">Quay lại chọn phương thức khác</button>
                        </form>

                    <?php elseif ($data['paymentMethod'] === 'bank'): ?>
                        <h5>Thanh toán qua Chuyển khoản ngân hàng</h5>
                        <p>Vui lòng quét mã QR bên dưới để thanh toán:</p>
                        <img src="<?= BASE_URL ?>/uploads/covers/NganHang.jpg" 
                             alt="QR Ngân hàng" class="img-fluid mb-3" style="max-width:300px;">
                        <form method="post" action="">
                            <input type="hidden" name="payment_method" value="bank">
                            <button type="submit" name="confirm_payment" value="1" class="btn btn-success">Xác nhận đã thanh toán</button>
                        </form>
                        <form method="post" action="" class="mt-2">
                            <button type="submit" class="btn btn-secondary">Quay lại chọn phương thức khác</button>
                        </form>

                    <?php elseif ($data['paymentMethod'] === 'card'): ?>
                        <h5>Thanh toán qua Thẻ tín dụng / ghi nợ</h5>
                        <form method="post" action="">
                            <div class="mb-3">
                                <label class="form-label">Số thẻ:</label>
                                <input type="text" name="card_number" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Ngày hết hạn:</label>
                                <input type="text" name="expiry_date" placeholder="MM/YY" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">CVV:</label>
                                <input type="password" name="cvv" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Thanh toán</button>
                        </form>
                        <form method="post" action="" class="mt-2">
                            <button type="submit" class="btn btn-secondary">Quay lại chọn phương thức khác</button>
                        </form>
                    <?php endif; ?>

                    <!-- Tổng thanh toán -->
                    <div class="price-summary mt-4">
                        <div class="d-flex justify-content-between mb-3">
                            <strong>Tổng thanh toán:</strong>
                            <strong><?= formatPrice($data['novel']['price']) ?></strong>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <?php include '../../../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
