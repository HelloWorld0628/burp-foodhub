<?php
require_once __DIR__ . '/config.php';
$cfg = require __DIR__ . '/config.php';

session_start();
header('Content-Type: application/json');

$conn = new mysqli($cfg['host'], $cfg['user'], $cfg['pass'], $cfg['db']);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $conn->real_escape_string($_POST['username']);
    $password_typed = $_POST['password'];

    $sql = "SELECT admin_id, username, password, role FROM admin_users WHERE username = '$username' LIMIT 1";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        if ($password_typed === $row['password']) {
            
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $row['admin_id'];
            $_SESSION['admin_username'] = $row['username'];
            $_SESSION['admin_role'] = $row['role'];

            echo json_encode(['success' => true, 'message' => 'Login successful']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid username or password.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid username or password.']);
    }
}

$conn->close();
?>