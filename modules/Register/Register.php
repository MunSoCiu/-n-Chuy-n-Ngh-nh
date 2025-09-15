<?php
session_start();
require_once '../../app/config/config.php';

// Nếu đã đăng nhập thì chuyển về trang chủ
if (isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/index.php");
    exit();
}

// Xử lý đăng ký khi form được submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];
    $error = null;

    // Kiểm tra các trường không được để trống
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "Vui lòng điền đầy đủ thông tin";
    }
    // Kiểm tra định dạng email
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email không hợp lệ";
    }
    // Kiểm tra mật khẩu khớp nhau
    elseif ($password !== $confirm_password) {
        $error = "Mật khẩu xác nhận không khớp";
    }
    // Kiểm tra độ dài mật khẩu
    elseif (strlen($password) < 6) {
        $error = "Mật khẩu phải có ít nhất 6 ký tự";
    } else {
        // Kiểm tra username đã tồn tại chưa
        $check_username = $conn->prepare("SELECT 1 FROM Users WHERE username = ?");
        $check_username->bind_param("s", $username);
        $check_username->execute();
        if ($check_username->get_result()->num_rows > 0) {
            $error = "Tên đăng nhập đã được sử dụng";
        } else {
            // Kiểm tra email đã tồn tại chưa
            $check_email = $conn->prepare("SELECT 1 FROM Users WHERE email = ?");
            $check_email->bind_param("s", $email);
            $check_email->execute();
            if ($check_email->get_result()->num_rows > 0) {
                $error = "Email đã được sử dụng";
            }
        }
    }

    // Nếu không có lỗi, tiến hành đăng ký
    if (!$error) {
        try {
            $stmt = $conn->prepare("INSERT INTO Users (username, email, password_hash, role, created_at) VALUES (?, ?, ?, 'user', NOW())");
            $stmt->bind_param("sss", $username, $email, $password);
            
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Đăng ký thành công! Vui lòng đăng nhập.";
                header("Location: " . BASE_URL . "/modules/Login/login.php");
                exit();
            } else {
                $error = "Có lỗi xảy ra, vui lòng thử lại sau";
            }
        } catch (Exception $e) {
            $error = "Có lỗi xảy ra, vui lòng thử lại sau";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Ký - Nhà Sách Minh An</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background: linear-gradient(120deg, #a1c4fd 0%, #c2e9fb 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .register-container {
            max-width: 500px;
            width: 90%;
            padding: 40px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.15);
            backdrop-filter: blur(4px);
        }
        .form-control {
            border-radius: 10px;
            padding: 12px;
            border: 2px solid #e1e1e1;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #a1c4fd;
            box-shadow: 0 0 10px rgba(161, 196, 253, 0.3);
        }
        .btn-primary {
            background: linear-gradient(45deg, #a1c4fd, #c2e9fb);
            border: none;
            padding: 12px;
            border-radius: 10px;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(161, 196, 253, 0.4);
        }
        .btn-secondary {
            background: transparent;
            border: 2px solid #a1c4fd;
            color: #666;
        }
        .btn-secondary:hover {
            background: #a1c4fd;
            color: white;
        }
        .input-group-text {
            background: transparent;
            border: 2px solid #e1e1e1;
            border-right: none;
        }
        .password-toggle {
            cursor: pointer;
            padding: 12px;
            background: transparent;
            border: 2px solid #e1e1e1;
            border-left: none;
            border-radius: 0 10px 10px 0;
        }
        h2 {
            color: #333;
            font-weight: bold;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h2 class="text-center">Đăng Ký Tài Khoản</h2>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form method="POST" action="" id="registerForm">
            <div class="mb-4">
                <label for="username" class="form-label">Tên đăng nhập</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                    <input type="text" class="form-control" id="username" name="username" 
                           value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>" required>
                </div>
            </div>

            <div class="mb-4">
                <label for="email" class="form-label">Email</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    <input type="email" class="form-control" id="email" name="email" 
                           value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" required>
                </div>
            </div>

            <div class="mb-4">
                <label for="password" class="form-label">Mật khẩu</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" class="form-control" id="password" name="password" required>
                    <span class="password-toggle" onclick="togglePassword('password', 'toggleIcon1')">
                        <i class="fas fa-eye" id="toggleIcon1"></i>
                    </span>
                </div>
            </div>

            <div class="mb-4">
                <label for="confirm_password" class="form-label">Xác nhận mật khẩu</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    <span class="password-toggle" onclick="togglePassword('confirm_password', 'toggleIcon2')">
                        <i class="fas fa-eye" id="toggleIcon2"></i>
                    </span>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 mb-3">Đăng Ký</button>
        </form>

        <p class="text-center mb-3">
            <a href="../Login/login.php" class="text-decoration-none">Đã có tài khoản? Đăng Nhập</a>
        </p>
        
        <p class="text-center">
            <a href="../../index.php" class="btn btn-secondary w-100">Quay về Trang Chủ</a>
        </p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function togglePassword(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    document.getElementById('registerForm').addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        
        if (password !== confirmPassword) {
            e.preventDefault();
            alert('Mật khẩu xác nhận không khớp!');
        }
    });
    </script>
</body>
</html>
