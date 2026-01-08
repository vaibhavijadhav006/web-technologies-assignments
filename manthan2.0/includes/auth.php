<?php
// Include config if not already included
if (!isset($conn)) {
    require_once 'config.php';
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

// Check user role
function checkRole($required_role) {
    if (!isLoggedIn()) {
        header('Location: ../../login.php');
        exit();
    }
    
    if ($_SESSION['role'] !== $required_role) {
        // Redirect to appropriate dashboard
        header('Location: index.php');
        exit();
    }
}

// Note: getUserNotifications() function is now in includes/functions.php
// Include functions.php if you need notification functions
// require_once 'functions.php';

?>