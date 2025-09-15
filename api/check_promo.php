<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Vui lòng đăng nhập']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$code = isset($data['code']) ? trim($data['code']) : '';
$novel_id = isset($data['novel_id']) ? (int)$data['novel_id'] : 0;

if (!$code || !$novel_id) {
    echo json_encode(['status' => 'error', 'message' => 'Thiếu thông tin']);
    exit;
}

// Kiểm tra mã giảm giá
$query = "SELECT * FROM Promotions 
          WHERE code = ? 
          AND NOW() BETWEEN start_date AND end_date";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $code);
$stmt->execute();
$promo = $stmt->get_result()->fetch_assoc();

if ($promo) {
    echo json_encode([
        'status' => 'success',
        'promo_id' => $promo['promo_id'],
        'discount_percentage' => $promo['discount_percentage']
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Mã giảm giá không hợp lệ hoặc đã hết hạn'
    ]);
}
