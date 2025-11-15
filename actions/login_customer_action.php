<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
session_start();
// Ensure core helpers/constants are loaded
require_once __DIR__ . '/../settings/core.php';

try {
    // Check if user is already logged in (use canonical CUSTOMER_ID constant)
    if (isset($_SESSION[USER_ID])) {
        echo json_encode(['status' => 'error', 'message' => 'You are already logged in']);
        exit();
    }

    require_once __DIR__ . '/../controllers/customer_controller.php';
    require_once __DIR__ . '/../settings/db_class.php';

    // Database connection check
    $db_connected = null;
    $db_error = null;
    try {
        $dbc_test = new db_connection();
        $db_connected = $dbc_test->db_connect();
        if ($db_connected === false) $db_error = mysqli_connect_error();
    } catch (Throwable $t) {
        $db_connected = false;
        $db_error = $t->getMessage();
    }

    // Get form data
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Input validation
    if ($email === '' || $password === '') {
        echo json_encode(['status' => 'error', 'message' => 'Missing email or password', 'debug_db_connected' => $db_connected, 'debug_db_error' => $db_error]);
        exit();
    }

    // Attempt to login the customer
    $user = login_customer_ctr($email, $password);

    // If login is successful
    if ($user['success']) {
        // Set session variables (legacy `user_id` used across the app)
        $_SESSION['user_id'] = $user['id'];

        $_SESSION['user_name'] = $user['name'];

        // Migrate guest cart to user cart if items exist
        require_once __DIR__ . '/../controllers/cart_controller.php';
        CartController::migrate_guest_cart_ctr($user['id']);
        $_SESSION['user_email'] = $user['email'];
    // Ensure role is stored as integer so strict checks work
    $_SESSION['user_role'] = (int) $user['role'];

        // Return JSON so AJAX performs the redirect
    $redirect = ($_SESSION['user_role'] == 1) ? '/register_sample/admin/category.php' : '/register_sample/index.php';
        echo json_encode(['status' => 'success', 'message' => 'Login successful', 'redirect' => $redirect, 'debug_db_connected' => $db_connected, 'debug_db_error' => $db_error]);
        exit();
    }

    // If login failed, return an error message
    echo json_encode(['status' => 'error', 'message' => $user['message'], 'debug_db_connected' => $db_connected, 'debug_db_error' => $db_error]);
} catch (Throwable $e) {
    echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
}
