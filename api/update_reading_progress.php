<?php
session_start();
require_once '../config.php';

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Lấy dữ liệu từ request
$data = json_decode(file_get_contents('php://input'), true);
$novel_id = isset($data['novel_id']) ? (int)$data['novel_id'] : 0;
$chapter_id = isset($data['chapter_id']) ? (int)$data['chapter_id'] : 0;

if (!$novel_id || !$chapter_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid parameters']);
    exit;
}

// Cập nhật tiến độ đọc
$query = "INSERT INTO Reading_History (user_id, novel_id, chapter_id) 
          VALUES (?, ?, ?) 
          ON DUPLICATE KEY UPDATE 
          chapter_id = ?, last_read = CURRENT_TIMESTAMP";

$stmt = $conn->prepare($query);
$stmt->bind_param("iiii", 
    $_SESSION['user_id'], 
    $novel_id, 
    $chapter_id,
    $chapter_id
);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
} 