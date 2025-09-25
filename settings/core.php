<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ob_start();  


const BASE_URL = '/your_app_base';        
const LOGIN_PAGE = 'view/login.php';      
const HOMEPAGE = 'index.php';             

const USER_ID = 'customer_id';            
const USER_ROLE = 'user_role';            
const ADMIN_ROLE = 1;                     
const CUSTOMER_ROLE = 2;                  

// Helper function to create full URLs based on base path
function generate_url(string $path): string {
    return BASE_URL . '/' . ltrim($path, '/');
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


if (is_logged_in() && is_admin()) {
    echo "Welcome, Admin!";
} elseif (is_logged_in()) {
    echo "Welcome, Customer!";
} else {
    redirect_to(LOGIN_PAGE);
}
?>
