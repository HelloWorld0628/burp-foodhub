<?php
session_start();
header('Content-Type: application/json');

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    // Admin is securely logged in
    echo json_encode([
        'logged_in' => true, 
        'username' => $_SESSION['admin_username']
    ]);
} else {
    // Admin is NOT logged in
    echo json_encode(['logged_in' => false]);
}
?>