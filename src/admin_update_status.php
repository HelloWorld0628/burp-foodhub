<?php
require_once __DIR__ . '/config.php';
$cfg = require __DIR__ . '/config.php';

$conn = new mysqli($cfg['host'], $cfg['user'], $cfg['pass'], $cfg['db']);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $new_status = isset($_POST['status']) ? $conn->real_escape_string($_POST['status']) : '';

    if ($order_id <= 0 || empty($new_status)) {
        echo json_encode(['success' => false, 'error' => 'Invalid data provided']);
        exit;
    }

    $admin_allowed_statuses = ['Pending', 'Preparing'];
    
    if (!in_array($new_status, $admin_allowed_statuses)) {
        echo json_encode([
            'success' => false, 
            'error' => 'Admin cannot set order to "' . $new_status . '". Only drivers can mark orders as "On the Way" or "Delivered".'
        ]);
        exit;
    }

    $get_current_sql = "SELECT status FROM orders WHERE order_id = $order_id";
    $current_result = $conn->query($get_current_sql);
    $current_status = '';
    
    if ($current_result && $current_result->num_rows > 0) {
        $row = $current_result->fetch_assoc();
        $current_status = $row['status'];
    }

    $sql = "UPDATE orders SET status = '$new_status' WHERE order_id = $order_id";
    
    if ($conn->query($sql) === TRUE) {
        $log_sql = "INSERT INTO order_status_log (order_id, previous_status, new_status, updated_by, updated_by_role, notes)
                    VALUES ($order_id, '$current_status', '$new_status', 'admin', 'admin', 'Status updated by admin')";
        $conn->query($log_sql);

        echo json_encode([
            'success' => true, 
            'message' => 'Status updated to ' . $new_status
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'error' => 'Error updating record: ' . $conn->error
        ]);
    }
}

$conn->close();
?>