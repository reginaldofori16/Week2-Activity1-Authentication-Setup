<?php
// Start session
require_once __DIR__ . '/../settings/core.php';

// Set response header
header('Content-Type: application/json');

// Get redirect URL from session
$redirect_url = $_SESSION['redirect_after_login'] ?? null;

// Clear the redirect URL from session
unset($_SESSION['redirect_after_login']);

// Return response
echo json_encode([
    'redirect_url' => $redirect_url
]);

exit;