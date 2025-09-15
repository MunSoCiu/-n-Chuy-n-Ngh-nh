<?php
/**
 * Template cho card truyện
 * @param array $novel Mảng chứa thông tin truyện
 * @param string $card_class Class bổ sung cho card (optional)
 */
function renderNovelCard($novel, $card_class = '') {
    $base_url = BASE_URL;
    ob_start();
?>
    <div class="col-md-3">
        <div class="card h-100 novel-card <?= $card_class ?>">
            <!-- Phần ảnh cố định chiều cao -->
            <div class="novel-cover-wrapper">
                <img src="<?= $base_url . '/' . ($novel['cover_image'] ?: 'images/covers/default-cover.jpg') ?>" 
                     class="card-img-top novel-cover" 
                     alt="<?= htmlspecialchars($novel['title']) ?>">
            </div>
            
            <div class="card-body d-flex flex-column">
                <!-- Phần thông tin cơ bản -->
                <div class="flex-grow-1">
                    <h5 class="card-title"><?= htmlspecialchars($novel['title']) ?></h5>
                    <p class="card-text text-muted">
                        <small>Tác giả: <?= htmlspecialchars($novel['author']) ?></small>
                    </p>
                    
                    <div class="mb-2">
                        <?php 
                        $categories = !is_null($novel['categories']) ? explode(',', $novel['categories']) : [];
                        foreach ($categories as $category) {
                            if ($category) {
                                echo "<span class='badge bg-secondary me-1'>".htmlspecialchars($category)."</span>";
                            }
                        }
                        ?>
                    </div>

                    <!-- Phần giá và số lượng bán -->
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <?php if ($novel['price'] > 0): ?>
                            <?php if (isset($novel['discount_percentage']) && $novel['discount_percentage'] > 0): ?>
                                <div>
                                    <del class="text-muted"><?= formatPrice($novel['price']) ?></del>
                                    <br>
                                    <span class="text-danger fw-bold">
                                        <?= formatPrice($novel['price'] * (1 - $novel['discount_percentage']/100)) ?>
                                    </span>
                                </div>
                                <span class="badge bg-danger">-<?= $novel['discount_percentage'] ?>%</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark">
                                    <i class="fas fa-tag me-1"></i> <?= formatPrice($novel['price']) ?>
                                </span>
                            <?php endif; ?>

                            <?php if (isset($novel['sold_count']) && $novel['sold_count'] > 0): ?>
                                <span class="badge bg-success">
                                    <i class="fas fa-shopping-cart me-1"></i>
                                    Đã bán: <?= number_format($novel['sold_count']) ?>
                                </span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="badge bg-success">
                                <i class="fas fa-gift me-1"></i> Miễn phí
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Phần footer cố định -->
                <div class="card-footer-custom">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <small class="text-muted">
                            <i class="fas fa-heart text-danger me-1"></i> <?= $novel['favorite_count'] ?? 0 ?>
                            <i class="fas fa-eye ms-2 me-1"></i> <?= $novel['read_count'] ?? 0 ?>
                            <?php if (isset($novel['sold_count']) && $novel['sold_count'] > 0): ?>
                                <i class="fas fa-shopping-cart ms-2 me-1 text-success"></i> <?= $novel['sold_count'] ?>
                            <?php endif; ?>
                        </small>
                    </div>

                    <a href="<?= $base_url ?>/app/Controllers/User/Novel_controller.php?id=<?= $novel['novel_id'] ?>" 
                       class="btn btn-primary w-100">
                        <i class="fas fa-book-reader me-1"></i> Xem chi tiết
                    </a>
                </div>
            </div>
        </div>
    </div>
<?php
    return ob_get_clean();
}
?>

<!-- CSS cho novel card -->
<style>
.novel-card {
    transition: transform 0.2s;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,.1);
    display: flex;
    flex-direction: column;
}
.novel-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 8px rgba(0,0,0,.2);
}
.novel-cover-wrapper {
    height: 250px;
    overflow: hidden;
}
.novel-cover {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.card-title {
    font-size: 1.1rem;
    margin-bottom: 0.5rem;
    font-weight: 600;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.card-body {
    padding-bottom: 0;
}
.card-footer-custom {
    padding: 1rem;
    margin: 0 -1rem -1rem -1rem;
    background-color: rgba(0,0,0,.03);
    border-top: 1px solid rgba(0,0,0,.125);
}
</style>
