<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
session_start();

try {
    if (isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'You are already logged in']);
        exit();
    }

    require_once __DIR__ . '/../controllers/customer_controller.php';
    require_once __DIR__ . '/../settings/db_class.php';

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

    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        echo json_encode(['status' => 'error', 'message' => 'Missing email or password', 'debug_db_connected' => $db_connected, 'debug_db_error' => $db_error]);
        exit();
    }

    $user = login_customer_ctr($email, $password);

    if ($user['success']) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];

        echo json_encode(['status' => 'success', 'message' => 'Login successful', 'user_id' => $user['id'], 'debug_db_connected' => $db_connected, 'debug_db_error' => $db_error]);
        exit();
    }

    echo json_encode(['status' => 'error', 'message' => $user['message'], 'debug_db_connected' => $db_connected, 'debug_db_error' => $db_error]);
} catch (Throwable $e) {
    echo json_encode(['status' => 'error', 'message' => 'Server error: '.$e->getMessage()]);
}
