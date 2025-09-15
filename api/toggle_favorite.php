<?php
session_start();
require_once '../app/config/config.php';

header('Content-Type: application/json');

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Vui lòng đăng nhập để thực hiện chức năng này']);
    exit;
}

// Lấy novel_id từ request
$data = json_decode(file_get_contents('php://input'), true);
$novel_id = isset($data['novel_id']) ? (int)$data['novel_id'] : 0;

if (!$novel_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Thiếu thông tin truyện']);
    exit;
}

// Kiểm tra xem đã yêu thích chưa
$check_query = "SELECT 1 FROM Favorites WHERE user_id = ? AND novel_id = ?";
$stmt = $conn->prepare($check_query);
$stmt->bind_param("ii", $_SESSION['user_id'], $novel_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Nếu đã yêu thích thì xóa
    $query = "DELETE FROM Favorites WHERE user_id = ? AND novel_id = ?";
    $action = 'removed';
} else {
    // Nếu chưa yêu thích thì thêm
    $query = "INSERT INTO Favorites (user_id, novel_id) VALUES (?, ?)";
    $action = 'added';
}

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $_SESSION['user_id'], $novel_id);

if ($stmt->execute()) {
    // Lấy số lượt yêu thích mới
    $count_query = "SELECT COUNT(*) as count FROM Favorites WHERE novel_id = ?";
    $stmt = $conn->prepare($count_query);
    $stmt->bind_param("i", $novel_id);
    $stmt->execute();
    $count = $stmt->get_result()->fetch_assoc()['count'];
    
    echo json_encode([
        'status' => 'success',
        'action' => $action,
        'count' => $count
    ]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Có lỗi xảy ra']);
} 