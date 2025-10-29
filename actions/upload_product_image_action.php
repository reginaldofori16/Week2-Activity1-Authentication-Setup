<?php
// Include core helpers for session management
require_once __DIR__ . '/../settings/core.php';

/**
 * Handle product image upload
 * @param string $file_field_name - Name of the file field in the form
 * @param int|null $product_id - Product ID (null for new products)
 * @param bool $is_new_product - Whether this is for a new product
 * @return array - Result array with status and message/file_path
 */
function handle_product_image_upload($file_field_name, $product_id = null, $is_new_product = false) {
    // Ensure session is started and user is admin
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!is_logged_in() || !is_admin()) {
        return ['status' => 'error', 'message' => 'Unauthorized'];
    }

    // Check if file was uploaded
    if (!isset($_FILES[$file_field_name]) || $_FILES[$file_field_name]['error'] !== UPLOAD_ERR_OK) {
        return ['status' => 'error', 'message' => 'No file uploaded or upload error'];
    }

    $file = $_FILES[$file_field_name];

    // Validate file type
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime_type, $allowed_types)) {
        return ['status' => 'error', 'message' => 'Invalid file type. Only JPG, PNG, and GIF are allowed'];
    }

    // Validate file size (max 5MB)
    $max_size = 5 * 1024 * 1024; // 5MB in bytes
    if ($file['size'] > $max_size) {
        return ['status' => 'error', 'message' => 'File too large. Maximum size is 5MB'];
    }

    // Get file extension
    $extension = '';
    switch ($mime_type) {
        case 'image/jpeg':
        case 'image/jpg':
            $extension = '.jpg';
            break;
        case 'image/png':
            $extension = '.png';
            break;
        case 'image/gif':
            $extension = '.gif';
            break;
    }

    // Create upload directory structure
    $upload_dir = __DIR__ . '/../uploads';

    // For new products without an ID, create a temporary directory
    if ($is_new_product && !$product_id) {
        $user_dir = $upload_dir . '/temp';
        if (!file_exists($user_dir)) {
            mkdir($user_dir, 0755, true);
        }
        $product_dir = $user_dir;
    } else {
        // For existing products or when product_id is available
        $user_id = $_SESSION['user_id'];
        $user_dir = $upload_dir . '/u' . $user_id;
        $product_dir = $user_dir . '/p' . ($product_id ?? 'temp');
    }

    // Create directory if it doesn't exist
    if (!file_exists($product_dir)) {
        mkdir($product_dir, 0755, true);
    }

    // Generate unique filename
    $filename = 'product_' . time() . '_' . uniqid() . $extension;
    $filepath = $product_dir . '/' . $filename;

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['status' => 'error', 'message' => 'Failed to save uploaded file'];
    }

    // Return relative path for database storage
    $relative_path = str_replace(__DIR__ . '/../', '', $filepath);

    return [
        'status' => 'success',
        'message' => 'File uploaded successfully',
        'file_path' => $relative_path,
        'full_path' => $filepath
    ];
}

// If this file is called directly via AJAX
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {

    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['product_id'])) {
        $product_id = $_POST['product_id'];
        $result = handle_product_image_upload('product_image', $product_id);
        echo json_encode($result);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    }
    exit();
}
?>