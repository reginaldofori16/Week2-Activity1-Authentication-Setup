<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ob_start();  

// Set base URL to this project folder so redirects point to the right place
const BASE_URL = '/register_sample';        

// Correct login page path relative to BASE_URL
const LOGIN_PAGE = 'login/login.php';      
const HOMEPAGE = 'index.php';             

// Session keys used across the app
// Canonical session key for logged-in user id
const USER_ID = 'user_id';                // session key that stores the logged-in user's id
const USER_ROLE = 'user_role';            
const ADMIN_ROLE = 1;                     
const CUSTOMER_ROLE = 2;                  

// Helper function to create full URLs based on base path
function generate_url(string $path): string {
    return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
}

// Function to redirect to a specific path
function redirect_to(string $path): void {
    header('Location: ' . generate_url($path));
    exit;
}


function is_logged_in(): bool {
    return isset($_SESSION[USER_ID]);
}

function is_admin(): bool {
    return isset($_SESSION[USER_ROLE]) && $_SESSION[USER_ROLE] === ADMIN_ROLE;
}

function require_role(int $role): void {
    if (!is_logged_in()) {
        redirect_to(LOGIN_PAGE);  
    }
    
    if ($_SESSION[USER_ROLE] !== $role) {
        redirect_to(HOMEPAGE);  
    }
}
?>
