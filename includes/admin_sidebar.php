<?php
// Admin sidebar component - dùng chung cho tất cả trang admin
// $current_page được truyền từ file gọi để highlight menu hiện tại
?>
<!-- Sidebar -->
<nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar admin-sidebar">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?= ($current_page === 'dashboard') ? 'active' : '' ?>" href="Admin_dashboard_controller.php">
                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($current_page === 'novels') ? 'active' : '' ?>" href="Admin_novels_controller.php">
                    <i class="fas fa-book me-2"></i>Quản lý sách
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($current_page === 'users') ? 'active' : '' ?>" href="Admin_users_controller.php">
                    <i class="fas fa-users me-2"></i>Quản lý người dùng
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($current_page === 'purchases') ? 'active' : '' ?>" href="Admin_purchases_controller.php">
                    <i class="fas fa-file-invoice-dollar me-2"></i>Quản lý hóa đơn
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($current_page === 'categories') ? 'active' : '' ?>" href="Admin_categories_controller.php">
                    <i class="fas fa-tags me-2"></i>Quản lý thể loại
                </a>
            </li>

        </ul>
        
        <hr>
        
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link text-danger" href="../../../index.php">
                    <i class="fas fa-arrow-left me-2"></i>Về trang chủ
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-danger" href="../User/Logout_controller.php">
                    <i class="fas fa-sign-out-alt me-2"></i>Đăng xuất
                </a>
            </li>
        </ul>
    </div>
</nav>
