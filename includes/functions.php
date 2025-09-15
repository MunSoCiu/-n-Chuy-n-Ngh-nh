<?php
// Hàm format thời gian
function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $difference = time() - $timestamp;
    
    if ($difference < 60) {
        return "Vừa xong";
    } elseif ($difference < 3600) {
        $minutes = floor($difference / 60);
        return $minutes . " phút trước";
    } elseif ($difference < 86400) {
        $hours = floor($difference / 3600);
        return $hours . " giờ trước";
    } elseif ($difference < 2592000) {
        $days = floor($difference / 86400);
        return $days . " ngày trước";
    } elseif ($difference < 31536000) {
        $months = floor($difference / 2592000);
        return $months . " tháng trước";
    } else {
        $years = floor($difference / 31536000);
        return $years . " năm trước";
    }
}

// Hàm cắt chuỗi với dấu ba chấm
function truncateText($text, $length = 100) {
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    return mb_substr($text, 0, $length) . '...';
}

// Hàm tạo breadcrumb
function generateBreadcrumb($items) {
    $html = '<nav aria-label="breadcrumb">';
    $html .= '<ol class="breadcrumb">';
    
    foreach ($items as $label => $url) {
        if ($url === null) {
            $html .= '<li class="breadcrumb-item active" aria-current="page">' . htmlspecialchars($label) . '</li>';
        } else {
            $html .= '<li class="breadcrumb-item"><a href="' . htmlspecialchars($url) . '">' . htmlspecialchars($label) . '</a></li>';
        }
    }
    
    $html .= '</ol>';
    $html .= '</nav>';
    return $html;
}

// Hàm phân trang
function pagination($total, $current_page, $limit, $url) {
    $total_pages = ceil($total / $limit);
    
    if ($total_pages <= 1) {
        return '';
    }
    
    $html = '<nav aria-label="Page navigation">';
    $html .= '<ul class="pagination justify-content-center">';
    
    // Nút Previous
    if ($current_page > 1) {
        $html .= '<li class="page-item">';
        $html .= '<a class="page-link" href="' . sprintf($url, $current_page - 1) . '">Trước</a>';
        $html .= '</li>';
    }
    
    // Các trang
    for ($i = max(1, $current_page - 2); $i <= min($total_pages, $current_page + 2); $i++) {
        $html .= '<li class="page-item ' . ($i == $current_page ? 'active' : '') . '">';
        $html .= '<a class="page-link" href="' . sprintf($url, $i) . '">' . $i . '</a>';
        $html .= '</li>';
    }
    
    // Nút Next
    if ($current_page < $total_pages) {
        $html .= '<li class="page-item">';
        $html .= '<a class="page-link" href="' . sprintf($url, $current_page + 1) . '">Sau</a>';
        $html .= '</li>';
    }
    
    $html .= '</ul>';
    $html .= '</nav>';
    return $html;
}

// Hàm upload file
function uploadFile($file, $allowed_types = ['image/jpeg', 'image/png'], $max_size = 5242880) {
    if (!isset($file['error']) || is_array($file['error'])) {
        throw new RuntimeException('Invalid parameters.');
    }

    switch ($file['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            throw new RuntimeException('No file sent.');
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            throw new RuntimeException('Exceeded filesize limit.');
        default:
            throw new RuntimeException('Unknown errors.');
    }

    if ($file['size'] > $max_size) {
        throw new RuntimeException('Exceeded filesize limit.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    if (false === $ext = array_search(
        $finfo->file($file['tmp_name']),
        $allowed_types,
        true
    )) {
        throw new RuntimeException('Invalid file format.');
    }

    $filename = sprintf('%s.%s',
        sha1_file($file['tmp_name']),
        $ext
    );

    if (!move_uploaded_file(
        $file['tmp_name'],
        sprintf($_SERVER['DOCUMENT_ROOT'] . BASE_URL . '/uploads/%s',
            $filename
        )
    )) {
        throw new RuntimeException('Failed to move uploaded file.');
    }

    return $filename;
} 