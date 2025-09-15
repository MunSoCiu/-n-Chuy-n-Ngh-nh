<?php
session_start();
require_once '../../app/config/config.php';

// Nếu đã đăng nhập thì chuyển về trang chủ
if (isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/index.php");
    exit();
}

// Xử lý đăng nhập khi form được submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];
    
    // Truy vấn kiểm tra thông tin đăng nhập
    $sql = "SELECT user_id, username, password_hash as password, role, avatar_url 
            FROM Users WHERE username = ? OR email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if ($password === $user['password']) {
            // Đăng nhập thành công
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['avatar_url'] = $user['avatar_url'];
            
            // Chuyển hướng dựa vào role
            if ($user['role'] === 'admin') {
                header("Location: " . BASE_URL . "/modules/admin/dashboard.php");
            } else {
                header("Location: " . BASE_URL . "/index.php");
            }
            exit();
        } else {
            $login_error = "Tên đăng nhập hoặc mật khẩu không chính xác";
        }
    } else {
        $login_error = "Tên đăng nhập hoặc mật khẩu không chính xác";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập</title>
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
        .login-container {
            max-width: 450px;
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
        .alert {
            border-radius: 10px;
        }
    </style>
</head>
<body>
<div class="login-container">
    <h2 class="text-center">Đăng Nhập</h2>
    <form method="POST" action="">
        <div class="mb-4">
            <label for="username" class="form-label">Tên đăng nhập</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-user"></i></span>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
        </div>
        <div class="mb-4">
            <label for="password" class="form-label">Mật khẩu</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                <input type="password" class="form-control" id="password" name="password" required>
                <span class="password-toggle" onclick="togglePassword()">
                    <i class="fas fa-eye" id="toggleIcon"></i>
                </span>
            </div>
        </div>
        <button type="submit" class="btn btn-primary w-100 mb-3">Đăng Nhập</button>
    </form>
    <p class="text-center mb-3">
        <a href="../Register/Register.php" class="text-decoration-none">Chưa có tài khoản? Đăng Ký</a>
    </p>
    <p class="text-center">
        <a href="../../index.php" class="btn btn-secondary w-100">Quay về Trang Chủ</a>
    </p>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
}
</script>

<?php if(isset($login_error)): ?>
<script>
    Swal.fire({
        title: 'Lỗi!',
        text: '<?php echo $login_error; ?>',
        icon: 'error',
        confirmButtonText: 'Đóng'
    });
</script>
<?php endif; ?>
</body>
</html>