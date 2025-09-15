<?php
session_start();
require_once '../../app/config/config.php';
require_once '../../includes/functions.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/modules/Login/login.php");
    exit();
}

// Lấy novel_id từ URL
$novel_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Kiểm tra novel có tồn tại không
$query = "SELECT * FROM LightNovels WHERE novel_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $novel_id);
$stmt->execute();
$novel = $stmt->get_result()->fetch_assoc();

if (!$novel) {
    header("Location: " . BASE_URL . "/index.php");
    exit();
}

// Kiểm tra xem người dùng đã mua truyện chưa
$check_purchase = $conn->prepare("SELECT 1 FROM Purchases WHERE user_id = ? AND novel_id = ?");
$check_purchase->bind_param("ii", $_SESSION['user_id'], $novel_id);
$check_purchase->execute();
if ($check_purchase->get_result()->num_rows > 0) {
    header("Location: novel.php?id=" . $novel_id);
    exit();
}

// Xử lý mua truyện
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $price = $novel['price'];
    
    // Thêm vào bảng Purchases
    $purchase_stmt = $conn->prepare("INSERT INTO Purchases (user_id, novel_id, price) VALUES (?, ?, ?)");
    $purchase_stmt->bind_param("iid", $user_id, $novel_id, $price);
    
    if ($purchase_stmt->execute()) {
        $_SESSION['success_message'] = "Mua truyện thành công!";
        header("Location: novel.php?id=" . $novel_id);
        exit();
    } else {
        $error_message = "Có lỗi xảy ra khi xử lý giao dịch. Vui lòng thử lại.";
    }
}


?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mua truyện - <?= htmlspecialchars($novel['title']) ?></title>
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
    <?php include '../../includes/navbar.php'; ?>

    <div class="container py-5">
        
        <div class="purchase-container">
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger"><?= $error_message ?></div>
            <?php endif; ?>

            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="mb-0">Xác nhận mua truyện</h4>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <img src="<?= BASE_URL . '/' . ($novel['cover_image'] ?: 'images/covers/default-cover.jpg') ?>" 
                                 class="img-fluid rounded novel-cover" 
                                 alt="<?= htmlspecialchars($novel['title']) ?>">
                        </div>
                        <div class="col-md-8">
                            <h5><?= htmlspecialchars($novel['title']) ?></h5>
                            <p class="text-muted mb-2">Tác giả: <?= htmlspecialchars($novel['author']) ?></p>
                            <div class="mb-3">
                                <span class="badge bg-warning text-dark">
                                    <i class="fas fa-tag me-1"></i>
                                    Giá: <?= formatPrice($novel['price']) ?>
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
                                <strong><?= formatPrice($novel['price']) ?></strong>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-shopping-cart me-1"></i>
                                Xác nhận mua với giá <?= formatPrice($novel['price']) ?>
                            </button>
                            <a href="novel.php?id=<?= $novel_id ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i>
                                Quay lại
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include '../../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>