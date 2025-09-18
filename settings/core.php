// Settings/core.php
<?php
session_start();


//for header redirection
ob_start();

//funtion to check for login
if (!isset($_SESSION['id'])) {
    header("Location: ../Login/login_register.php");
    exit;
}


//function to get user ID


//function to check for role (admin, customer, etc)



?>