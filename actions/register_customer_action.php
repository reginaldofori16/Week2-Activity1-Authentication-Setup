<?php

// turn on errors for debugging (remove or disable display_errors in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

session_start();

try {
    // If already logged in
    if (isset($_SESSION['user_id'])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'You are already logged in']);
        exit();
    }

    // require controller (use absolute path)
    require_once __DIR__ . '/../controllers/customer_controller.php';

    // Quick DB connection debug check (will be returned in JSON)
    // require db class explicitly so we can test connection without triggering fatal errors later
    require_once __DIR__ . '/../settings/db_class.php';
    $db_connected = null;
    $db_error = null;
    try {
        $dbc_test = new db_connection();
        $db_connected = $dbc_test->db_connect();
        if ($db_connected === false) {
            // capture the last mysqli connection error
            $db_error = mysqli_connect_error();
        }
    } catch (Throwable $t) {
        $db_connected = false;
        $db_error = $t->getMessage();
    }

    // Collect and validate input
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $phone_number = isset($_POST['phone_number']) ? trim($_POST['phone_number']) : '';
    $role = isset($_POST['role']) ? intval($_POST['role']) : 2;
    $country = isset($_POST['country']) ? trim($_POST['country']) : '';
    $city = isset($_POST['city']) ? trim($_POST['city']) : '';

    if ($name === '' || $email === '' || $password === '' || $phone_number === '') {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Missing required fields', 'debug_db_connected' => $db_connected, 'debug_db_error' => $db_error]);
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid email address', 'debug_db_connected' => $db_connected, 'debug_db_error' => $db_error]);
        exit();
    }

    // Call controller
    $result = register_customer_ctr($name, $email, $password, $phone_number, $role, $country, $city);

    if (is_array($result) && isset($result['success']) && $result['success'] === true) {
        echo json_encode(['status' => 'success', 'message' => 'Registered successfully', 'customer_id' => $result['id'], 'debug_db_connected' => $db_connected, 'debug_db_error' => $db_error]);
        exit();
    }

    // Duplicate email or other controller-level error
    $message = is_array($result) && isset($result['message']) ? $result['message'] : 'Failed to register';
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $message, 'debug_db_connected' => $db_connected, 'debug_db_error' => $db_error]);
} catch (Throwable $e) {
    // If $db_connected not set here, attempt a minimal check so we can return something useful
    if (!isset($db_connected)) {
        try {
            require_once __DIR__ . '/../settings/db_class.php';
            $tmp = new db_connection();
            $db_connected = $tmp->db_connect();
            if ($db_connected === false) {
                $db_error = mysqli_connect_error();
            }
        } catch (Throwable $_t) {
            $db_connected = false;
            $db_error = $_t->getMessage();
        }
    }

    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage(), 'debug_db_connected' => $db_connected, 'debug_db_error' => $db_error]);
}
?>

