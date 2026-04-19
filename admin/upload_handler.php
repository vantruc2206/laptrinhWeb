<?php
require_once '../includes/functions.php';

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

    // Tạo tên tệp duy nhất
    $newName = time() . '_' . uniqid() . '.' . $ext;
    $destination = $uploadDir . $newName;

    if (move_uploaded_file($tmpName, $destination)) {
        // Trả về JSON cho TinyMCE
        // Đường dẫn nên liên quan đến tên miền hoặc URL tuyệt đối
        // Trong cấu trúc dự án này, assets nằm ở thư mục gốc.
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'];
        
        // Lấy đường dẫn cơ sở của dự án
        // Nếu URI là /laptrinhWeb/admin/upload_handler.php
        // Chúng ta muốn /laptrinhWeb/assets/images/articles/name
        $scriptPath = $_SERVER['SCRIPT_NAME']; // /laptrinhWeb/admin/upload_handler.php
        $adminPath = dirname($scriptPath);     // /laptrinhWeb/admin
        $rootPath = dirname($adminPath);       // /laptrinhWeb
        
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
