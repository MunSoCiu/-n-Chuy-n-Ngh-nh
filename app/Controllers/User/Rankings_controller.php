<?php
session_start();
require_once '../../../app/config/config.php';
require_once '../../../includes/functions.php';
require_once '../../../app/Models/User/Rankings_model.php';

class Rankings_controller {
    private $model;
    
    public function __construct($connection) {
        $this->model = new Rankings_model($connection);
    }
    
    public function index() {
        $type = isset($_GET['type']) ? $_GET['type'] : 'favorite';
        $limit = 10;
        
        $data = [
            'type' => $type,
            'novels' => null,
            'title' => '',
            'count_label' => '',
            'count_field' => '',
            'icon' => '',
            'color' => ''
        ];
        
        // Lấy truyện theo lượt yêu thích
        if ($type === 'favorite') {
            $data['novels'] = $this->model->getFavoriteRankings($limit);
            $data['title'] = "Truyện được yêu thích nhiều nhất";
            $data['count_label'] = "lượt yêu thích";
            $data['count_field'] = "favorite_count";
            $data['icon'] = "heart";
            $data['color'] = "danger";
        }
        // Lấy truyện theo lượt đọc
        elseif ($type === 'reading') {
            $data['novels'] = $this->model->getReadingRankings($limit);
            $data['title'] = "Truyện được đọc nhiều nhất";
            $data['count_label'] = "lượt đọc";
            $data['count_field'] = "read_count";
            $data['icon'] = "book-reader";
            $data['color'] = "primary";
        }
        // Lấy truyện bán chạy nhất
        elseif ($type === 'bestseller') {
            $data['novels'] = $this->model->getBestsellerRankings($limit);
            $data['title'] = "Truyện bán chạy nhất";
            $data['count_label'] = "lượt mua";
            $data['count_field'] = "sold_count";
            $data['icon'] = "shopping-cart";
            $data['color'] = "warning";
        }
        // Lấy truyện mới cập nhật
        else {
            $data['novels'] = $this->model->getNewUpdateRankings($limit);
            $data['title'] = "Truyện mới cập nhật";
            $data['count_label'] = "cập nhật";
            $data['count_field'] = "last_update";
            $data['icon'] = "clock";
            $data['color'] = "success";
        }
        
        // Load view
        include '../../../app/views/User/Rankings_view.php';
    }
}

// Khởi tạo controller và chạy
$controller = new Rankings_controller($conn);
$controller->index();
?>
