<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
    <div class="container">
        <!-- Logo -->
        <a class="navbar-brand" href="<?= BASE_URL ?>/index.php">
            <i class="fas fa-book me-2"></i>
            Nhà Sách Số
        </a>
        
        <!-- Toggle button -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <!-- Navbar content -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <!-- Left menu -->
            <ul class="navbar-nav me-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="fas fa-list me-1"></i> Danh mục
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/app/Controllers/User/Categories_controller.php">Tất cả danh mục</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/app/Controllers/User/Categories_controller.php?type=free">Sách miễn phí</a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/app/Controllers/User/Categories_controller.php?type=paid">Sách trả phí</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="fas fa-chart-line me-1"></i> Bảng xếp hạng
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/app/Controllers/User/Rankings_controller.php?type=favorite">Yêu thích nhất</a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/app/Controllers/User/Rankings_controller.php?type=reading">Đọc nhiều nhất</a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/app/Controllers/User/Rankings_controller.php?type=bestseller">Bán chạy nhất</a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/app/Controllers/User/Rankings_controller.php?type=new">Mới cập nhật</a></li>
                    </ul>
                </li>
            </ul>
            
          
            
            <!-- User menu -->
            <?php if(isset($_SESSION['user_id'])): ?>
                <div class="nav-item dropdown user-dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" data-bs-toggle="dropdown">
                        <img src="<?= BASE_URL . '/' . ($_SESSION['avatar_url'] ?: 'images/Avatar.jpg') ?>" 
                             class="rounded-circle me-2" width="32" height="32" alt="Avatar">
                        <span class="d-none d-sm-inline text-light"><?= htmlspecialchars($_SESSION['username']) ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/app/Controllers/User/Profile_controller.php">
                            <i class="fas fa-user me-2"></i> Trang cá nhân</a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/app/Controllers/User/Favorites_controller.php">
                            <i class="fas fa-heart me-2"></i> Truyện yêu thích</a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/app/Controllers/User/Purchases_controller.php">
                            <i class="fas fa-shopping-cart me-2"></i> Truyện đã mua</a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/app/Controllers/User/History_controller.php">
                            <i class="fas fa-history me-2"></i> Lịch sử đọc</a></li>
                        <?php if($_SESSION['role'] === 'admin'): ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/app/Controllers/Admin/Admin_dashboard_controller.php">
                                <i class="fas fa-cog me-2"></i> Quản trị</a></li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>/app/Controllers/User/Logout_controller.php">
                            <i class="fas fa-sign-out-alt me-2"></i> Đăng xuất</a></li>
                    </ul>
                </div>
            <?php else: ?>
                <div class="nav-buttons">
                    <a href="<?= BASE_URL ?>/app/Controllers/User/Login_controller.php" class="btn btn-outline-light me-2">
                        <i class="fas fa-sign-in-alt me-1"></i> Đăng nhập
                    </a>
                    <a href="<?= BASE_URL ?>/app/Controllers/User/Register_controller.php" class="btn btn-light">
                        <i class="fas fa-user-plus me-1"></i> Đăng ký
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</nav>

<style>
/* Reset and base styles */
.navbar {
    box-shadow: 0 2px 4px rgba(0,0,0,.1);
    padding: 0.5rem 1rem;
}

.navbar-brand {
    font-weight: bold;
    font-size: 1.5rem;
}

.navbar-collapse {
    display: flex;
    align-items: center;
}

/* Search form styles */
.search-container {
    width: 300px !important;
    min-width: 300px;
}

.search-input {
    width: 250px !important;
    transition: none !important;
}

/* User dropdown styles */
.user-dropdown .nav-link {
    display: flex;
    align-items: center;
    padding: 0.5rem;
    color: white !important;
}

.user-dropdown img {
    width: 32px;
    height: 32px;
    object-fit: cover;
}

/* Dropdown menu styles */
.dropdown-menu {
    border: none;
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,.15);
}

.dropdown-item:active {
    background-color: #343a40;
}

/* Responsive styles */
@media (max-width: 991.98px) {
    .navbar-collapse {
        flex-direction: column;
        align-items: flex-start;
        padding-top: 1rem;
    }
    
    .search-container {
        width: 100% !important;
    }
    
    .search-input {
        width: calc(100% - 50px) !important;
    }
    
    .user-dropdown, .nav-buttons {
        width: 100%;
        margin-top: 0.5rem;
    }
    
    .nav-buttons {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .nav-buttons .btn {
        width: 100%;
    }
}
</style>

<script>
// Add page-specific class to body
document.addEventListener('DOMContentLoaded', function() {
    const currentPage = window.location.pathname;
    if (currentPage.endsWith('index.php') || currentPage === '/' || currentPage.endsWith('/Btl/')) {
        document.body.classList.add('home-page');
    } else if (currentPage.includes('/list.php')) {
        document.body.classList.add('list-page');
    }
});
</script>