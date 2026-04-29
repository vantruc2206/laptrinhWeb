<?php
require_once __DIR__ . '/../app/Helpers/functions.php';

// Đảm bảo người dùng là admin
if (!isAdmin()) {
    header('HTTP/1.1 403 Forbidden');
    exit;
}

$uploadDir = '../assets/images/articles/';

// Đảm bảo thư mục upload tồn tại
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Kiểm tra xem có tệp nào được tải lên không
if ($_FILES['file']) {
    $file = $_FILES['file'];
    
    // Kiểm tra lỗi upload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(['error' => 'Tải lên thất bại với mã lỗi ' . $file['error']]);
        exit;
    }

    $tmpName = $file['tmp_name'];
    $name = basename($file['name']);
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (!in_array($ext, $allowed)) {
        header('HTTP/1.1 400 Bad Request');
        echo json_encode(['error' => 'Định dạng tệp không hợp lệ.']);
        exit;
    }

    $newName = time() . '_' . uniqid() . '.' . $ext;
    $destination = $uploadDir . $newName;

    if (move_uploaded_file($tmpName, $destination)) {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'];
        
        $scriptPath = $_SERVER['SCRIPT_NAME']; 
        $adminPath = dirname($scriptPath);     
        $rootPath = dirname($adminPath);       
        
        $fileUrl = $rootPath . '/assets/images/articles/' . $newName;
        
        header('Content-Type: application/json');
        echo json_encode(['location' => $fileUrl]);
    } else {
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(['error' => 'Không thể di chuyển tệp đã tải lên.']);
    }
} else {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => 'Không có tệp nào được tải lên.']);
}
