<?php
session_start();
require_once '../../../app/config/config.php';
require_once '../../../includes/functions.php';
require_once '../../../app/Models/User/Search_model.php';

class Search_controller {
    private $model;
    
    public function __construct($connection) {
        $this->model = new Search_model($connection);
    }
    
    public function index() {
        $search = isset($_GET['q']) ? trim($_GET['q']) : '';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 12;
        $offset = ($page - 1) * $limit;
        
        $data = [
            'search' => $search,
            'page' => $page,
            'results' => null,
            'total' => 0,
            'total_pages' => 0
        ];
        
        if ($search) {
            $data['results'] = $this->model->searchNovels($search, $limit, $offset);
            $data['total'] = $this->model->getTotalSearchResults($search);
            $data['total_pages'] = ceil($data['total'] / $limit);
        }
        
        // Load view
        include '../../../app/views/User/Search_view.php';
    }
}

// Khởi tạo controller và chạy
$controller = new Search_controller($conn);
$controller->index();
?>
