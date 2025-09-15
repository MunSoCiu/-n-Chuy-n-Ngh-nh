<footer class="bg-dark text-light py-5 mt-5">
    <div class="container">
        <div class="row">
            <!-- Giới thiệu -->
            <div class="col-md-4 mb-4">
                <h5 class="mb-3">Light Novel Hub</h5>
                <p class="text-light">
                    Trang web đọc Light Novel online lớn nhất Việt Nam với hàng nghìn tác phẩm được cập nhật liên tục.
                </p>
                <div class="social-links">
                    <a href="#" class="text-light me-3"><i class="fab fa-facebook"></i></a>
                    <a href="#" class="text-light me-3"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-light"><i class="fab fa-discord"></i></a>
                </div>
            </div>

            <!-- Liên kết nhanh -->
            <div class="col-md-4 mb-4">
                <h5 class="mb-3">Liên kết nhanh</h5>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <a href="<?= BASE_URL ?>/app/Controllers/User/Categories_controller.php" class="text-light text-decoration-none">Thể loại</a>
                    </li>
                    <li class="mb-2">
                        <a href="<?= BASE_URL ?>/app/Controllers/User/Rankings_controller.php" class="text-light text-decoration-none">Bảng xếp hạng</a>
                    </li>
                    <li class="mb-2">
                        <a href="<?= BASE_URL ?>/app/Controllers/User/Categories_controller.php?sort=newest" class="text-light text-decoration-none">Mới cập nhật</a>
                    </li>
                    <li class="mb-2">
                        <a href="<?= BASE_URL ?>/app/Controllers/User/Categories_controller.php?status=completed" class="text-light text-decoration-none">Đã hoàn thành</a>
                    </li>
                </ul>
            </div>

            <!-- Thông tin liên hệ -->
            <div class="col-md-4 mb-4">
                <h5 class="mb-3">Liên hệ</h5>
                <ul class="list-unstyled text-light">
                    <li class="mb-2">
                        <i class="fas fa-envelope me-2"></i> contact@lightnovelhub.com
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-phone me-2"></i> (84) 123 456 789
                    </li>
                    <li>
                        <i class="fas fa-map-marker-alt me-2"></i> Hà Nội, Việt Nam
                    </li>
                </ul>
            </div>
        </div>

        <!-- Copyright -->
        <div class="border-top pt-4 mt-4">
            <div class="row">
                <div class="col-md-6 text-center text-md-start">
                    <p class="mb-0 text-light">
                        &copy; <?= date('Y') ?> Light Novel Hub. All rights reserved.
                    </p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <ul class="list-inline mb-0">
                        <li class="list-inline-item">
                            <a href="terms.php" class="text-light text-decoration-none">Điều khoản sử dụng</a>
                        </li>
                        <li class="list-inline-item">
                            <span class="text-light">|</span>
                        </li>
                        <li class="list-inline-item">
                            <a href="privacy.php" class="text-light text-decoration-none">Chính sách bảo mật</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</footer> 