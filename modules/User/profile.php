<?php
session_start();
require_once '../../app/config/config.php';
require_once '../../includes/functions.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/modules/Login/login.php");
    exit();
}

// Lấy thông tin user hiện tại
$query = "SELECT * FROM Users WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Lấy lịch sử đọc
$history_query = "SELECT rh.*, n.title, n.cover_image, c.title as chapter_title
                 FROM Reading_History rh
                 JOIN LightNovels n ON rh.novel_id = n.novel_id
                 JOIN Chapters c ON rh.chapter_id = c.chapter_id
                 WHERE rh.user_id = ?
                 ORDER BY rh.last_read DESC";
$stmt = $conn->prepare($history_query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$reading_history = $stmt->get_result();

// Lấy truyện yêu thích
$favorites_query = "SELECT n.* 
                   FROM Favorites f
                   JOIN LightNovels n ON f.novel_id = n.novel_id
                   WHERE f.user_id = ?
                   ORDER BY n.title";
$stmt = $conn->prepare($favorites_query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$favorites = $stmt->get_result();

// Xử lý cập nhật thông tin
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : $user['username'];
    $email = isset($_POST['email']) ? trim($_POST['email']) : $user['email'];
    $password = isset($_POST['password']) && !empty($_POST['password']) ? trim($_POST['password']) : null;

    // Kiểm tra username và email không được trống
    if (empty($username) || empty($email)) {
        $error = "Tên đăng nhập và email không được để trống";
    } else {
        // Kiểm tra username đã tồn tại chưa (trừ username hiện tại)
        $check_query = "SELECT 1 FROM Users WHERE username = ? AND user_id != ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("si", $username, $_SESSION['user_id']);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $error = "Tên đăng nhập đã tồn tại";
        } else {
            // Kiểm tra email đã tồn tại chưa (trừ email hiện tại)
            $check_query = "SELECT 1 FROM Users WHERE email = ? AND user_id != ?";
            $stmt = $conn->prepare($check_query);
            $stmt->bind_param("si", $email, $_SESSION['user_id']);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $error = "Email đã tồn tại";
            } else {
                // Xử lý upload avatar nếu có
                $avatar_url = $user['avatar_url'];
                if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                    try {
                        $upload_dir = $_SERVER['DOCUMENT_ROOT'] . BASE_URL . '/uploads/avatars/';
                        if (!file_exists($upload_dir)) {
                            mkdir($upload_dir, 0777, true);
                        }

                        $file_info = pathinfo($_FILES['avatar']['name']);
                        $file_extension = strtolower($file_info['extension']);
                        
                        // Kiểm tra định dạng file
                        $allowed_types = ['jpg', 'jpeg', 'png'];
                        if (!in_array($file_extension, $allowed_types)) {
                            throw new Exception('Chỉ chấp nhận file ảnh định dạng JPG, JPEG hoặc PNG');
                        }

                        // Tạo tên file mới
                        $new_filename = uniqid() . '.' . $file_extension;
                        $upload_path = $upload_dir . $new_filename;

                        // Upload file
                        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $upload_path)) {
                            // Xóa avatar cũ nếu có
                            if ($avatar_url && file_exists($_SERVER['DOCUMENT_ROOT'] . BASE_URL . $avatar_url)) {
                                unlink($_SERVER['DOCUMENT_ROOT'] . BASE_URL . $avatar_url);
                            }
                            $avatar_url = '/uploads/avatars/' . $new_filename;
                        }
                    } catch (Exception $e) {
                        $error = $e->getMessage();
                    }
                }

                if (!isset($error)) {
                    // Xây dựng câu query cập nhật
                    $update_fields = [];
                    $types = "";
                    $params = [];

                    if ($username !== $user['username']) {
                        $update_fields[] = "username = ?";
                        $types .= "s";
                        $params[] = $username;
                    }

                    if ($email !== $user['email']) {
                        $update_fields[] = "email = ?";
                        $types .= "s";
                        $params[] = $email;
                    }

                    if ($password !== null) {
                        $update_fields[] = "password_hash = ?";
                        $types .= "s";
                        $params[] = $password;
                    }

                    if ($avatar_url !== $user['avatar_url']) {
                        $update_fields[] = "avatar_url = ?";
                        $types .= "s";
                        $params[] = $avatar_url;
                    }

                    if (!empty($update_fields)) {
                        $params[] = $_SESSION['user_id'];
                        $types .= "i";

                        $query = "UPDATE Users SET " . implode(", ", $update_fields) . " WHERE user_id = ?";
                        $stmt = $conn->prepare($query);
                        $stmt->bind_param($types, ...$params);
                        
                        if ($stmt->execute()) {
                            // Cập nhật session
                            $_SESSION['username'] = $username;
                            $_SESSION['avatar_url'] = $avatar_url;
                            $success = "Cập nhật thông tin thành công";
                            
                            // Refresh user data
                            $stmt = $conn->prepare("SELECT * FROM Users WHERE user_id = ?");
                            $stmt->bind_param("i", $_SESSION['user_id']);
                            $stmt->execute();
                            $user = $stmt->get_result()->fetch_assoc();
                        } else {
                            $error = "Có lỗi xảy ra khi cập nhật thông tin";
                        }
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông tin cá nhân - Light Novel Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .avatar-preview {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 1rem;
        }
        .form-container {
            max-width: 600px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>

    <div class="container py-5">
        <div class="form-container">
            <h2 class="mb-4">Thông tin cá nhân</h2>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="text-center mb-4">
                    <img src="<?= $user['avatar_url'] ? BASE_URL . $user['avatar_url'] : BASE_URL . '/images/Avatar.jpg' ?>" 
                         alt="Avatar" class="avatar-preview" id="avatar-preview">
                    <div class="mb-3">
                        <input type="file" class="form-control" id="avatar" name="avatar" accept="image/*" onchange="previewAvatar(this)">
                        <div class="form-text">Chọn ảnh JPG, JPEG hoặc PNG</div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="username" class="form-label">Tên đăng nhập</label>
                    <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>">
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>">
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Mật khẩu mới</label>
                    <input type="password" class="form-control" id="password" name="password">
                    <div class="form-text">Để trống nếu không muốn thay đổi mật khẩu</div>
                </div>

                <button type="submit" class="btn btn-primary w-100">Cập nhật thông tin</button>
            </form>
        </div>
    </div>

    <?php include '../../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function previewAvatar(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('avatar-preview').src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
    </script>
</body>
</html>