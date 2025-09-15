<?php
session_start();
require_once '../../app/config/config.php';
require_once '../../includes/functions.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/modules/Login/login.php");
    exit();
}

// Lấy danh sách mã giảm giá có hiệu lực
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    $query = "SELECT * FROM Promotions ORDER BY created_at DESC";
} else {
    $query = "SELECT * FROM Promotions 
              WHERE NOW() BETWEEN start_date AND end_date 
              ORDER BY end_date ASC";
}
$promotions = $conn->query($query);

// Thêm form để admin cung cấp mã giảm giá cho người dùng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'assign') {
        $user_id = (int)$_POST['user_id'];
        $promo_id = (int)$_POST['promo_id'];
        
        // Thêm vào bảng User_Promotions (cần tạo mới)
        $stmt = $conn->prepare("INSERT INTO User_Promotions (user_id, promo_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $user_id, $promo_id);
        $stmt->execute();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mã giảm giá - Light Novel Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>

    <div class="container py-5">
        <h2 class="mb-4">
            <i class="fas fa-tags"></i> Mã giảm giá của bạn
        </h2>

        <?php if ($promotions->num_rows > 0): ?>
            <div class="row g-4">
                <?php while ($promo = $promotions->fetch_assoc()): ?>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-percent text-danger"></i> 
                                        Giảm <?= $promo['discount_percentage'] ?>% cho tất cả sản phẩm
                                    </h5>
                                    <span class="badge bg-success">Còn hiệu lực</span>
                                </div>
                                <div class="d-flex align-items-center mb-3">
                                    <div class="border rounded p-2 flex-grow-1 me-2 bg-light">
                                        <code class="fs-5"><?= $promo['code'] ?></code>
                                    </div>
                                    <button class="btn btn-primary" onclick="copyCode('<?= $promo['code'] ?>')">
                                        <i class="fas fa-copy"></i> Sao chép
                                    </button>
                                </div>
                                <p class="card-text text-muted mb-0">
                                    <i class="fas fa-clock me-1"></i>
                                    Hết hạn: <?= date('d/m/Y H:i', strtotime($promo['end_date'])) ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                Hiện tại không có mã giảm giá nào khả dụng.
            </div>
        <?php endif; ?>
    </div>

    <?php include '../../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function copyCode(code) {
        navigator.clipboard.writeText(code).then(() => {
            alert('Đã sao chép mã giảm giá: ' + code);
        });
    }
    </script>
</body>
</html>
