<?php
require_once __DIR__ . '/config.php';
$cfg = require __DIR__ . '/config.php';

header('Content-Type: application/json');

$conn = new mysqli($cfg['host'], $cfg['user'], $cfg['pass'], $cfg['db']);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed.']);
    exit;
}

$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');

if ($action === 'uploadProof') {
    if (!isset($_POST['order_id']) || !isset($_POST['driver_id'])) {
        echo json_encode(['success' => false, 'error' => 'Missing order_id or driver_id']);
        exit;
    }

    $order_id = (int)$_POST['order_id'];
    $driver_id = (int)$_POST['driver_id'];
    $delivery_notes = isset($_POST['delivery_notes']) ? $conn->real_escape_string($_POST['delivery_notes']) : '';
    $latitude = isset($_POST['latitude']) ? floatval($_POST['latitude']) : NULL;
    $longitude = isset($_POST['longitude']) ? floatval($_POST['longitude']) : NULL;

    if (!isset($_FILES['proof_image']) || $_FILES['proof_image']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'error' => 'Image upload failed']);
        exit;
    }

    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $file_type = mime_content_type($_FILES['proof_image']['tmp_name']);
    
    if (!in_array($file_type, $allowed_types)) {
        echo json_encode(['success' => false, 'error' => 'Invalid image type. Only JPEG, PNG, GIF, and WebP allowed']);
        exit;
    }

    $upload_dir = __DIR__ . '/../public/delivery_img/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $file_ext = pathinfo($_FILES['proof_image']['name'], PATHINFO_EXTENSION);
    $filename = 'delivery_' . $order_id . '_' . time() . '.' . $file_ext;
    $filepath = $upload_dir . $filename;

    if (!move_uploaded_file($_FILES['proof_image']['tmp_name'], $filepath)) {
        echo json_encode(['success' => false, 'error' => 'Failed to save image']);
        exit;
    }

    $image_path = 'delivery_img/' . $filename;
    $proof_sql = "INSERT INTO delivery_proofs (order_id, driver_id, image_path, delivery_notes, latitude, longitude)
                  VALUES ($order_id, $driver_id, '$image_path', '$delivery_notes', " . 
                  ($latitude ? "$latitude" : "NULL") . ", " . ($longitude ? "$longitude" : "NULL") . ")";
    
    if (!$conn->query($proof_sql)) {
        echo json_encode(['success' => false, 'error' => 'Failed to save delivery proof: ' . $conn->error]);
        exit;
    }

    $status_sql = "UPDATE orders SET status = 'Delivered', delivery_proof_image = '$image_path' WHERE order_id = $order_id";
    
    if (!$conn->query($status_sql)) {
        echo json_encode(['success' => false, 'error' => 'Failed to update order status: ' . $conn->error]);
        exit;
    }

    $log_sql = "INSERT INTO order_status_log (order_id, previous_status, new_status, updated_by, updated_by_role, notes)
                SELECT $order_id, status, 'Delivered', (SELECT driver_name FROM drivers WHERE driver_id = $driver_id), 'driver', 'Delivery completed with proof'
                FROM orders WHERE order_id = $order_id LIMIT 1";
    $conn->query($log_sql);

    echo json_encode([
        'success' => true,
        'message' => 'Delivery proof uploaded successfully',
        'image_path' => $image_path
    ]);
}
else if ($action === 'markOnTheWay') {
    if (!isset($_POST['order_id']) || !isset($_POST['driver_id'])) {
        echo json_encode(['success' => false, 'error' => 'Missing order_id or driver_id']);
        exit;
    }

    $order_id = (int)$_POST['order_id'];
    $driver_id = (int)$_POST['driver_id'];

    $verify_sql = "SELECT driver_id FROM orders WHERE order_id = $order_id";
    $verify_result = $conn->query($verify_sql);
    
    if ($verify_result->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'Order not found']);
        exit;
    }

    $order_row = $verify_result->fetch_assoc();
    if ($order_row['driver_id'] != $driver_id) {
        echo json_encode(['success' => false, 'error' => 'Driver not assigned to this order']);
        exit;
    }

    $update_sql = "UPDATE orders SET status = 'On the Way' WHERE order_id = $order_id";
    
    if (!$conn->query($update_sql)) {
        echo json_encode(['success' => false, 'error' => 'Failed to update status: ' . $conn->error]);
        exit;
    }

    $log_sql = "INSERT INTO order_status_log (order_id, previous_status, new_status, updated_by, updated_by_role)
                VALUES ($order_id, 'Preparing', 'On the Way', (SELECT driver_name FROM drivers WHERE driver_id = $driver_id), 'driver')";
    $conn->query($log_sql);

    echo json_encode([
        'success' => true,
        'message' => 'Order marked as "On the Way"'
    ]);
}
else if ($action === 'getProof') {
    if (!isset($_GET['order_id'])) {
        echo json_encode(['success' => false, 'error' => 'Missing order_id']);
        exit;
    }

    $order_id = (int)$_GET['order_id'];

    $proof_sql = "SELECT dp.*, d.driver_name FROM delivery_proofs dp
                  JOIN drivers d ON dp.driver_id = d.driver_id
                  WHERE dp.order_id = $order_id
                  ORDER BY dp.uploaded_at DESC
                  LIMIT 1";
    
    $proof_result = $conn->query($proof_sql);
    
    if ($proof_result->num_rows > 0) {
        $proof = $proof_result->fetch_assoc();
        echo json_encode([
            'success' => true,
            'proof' => $proof
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'No delivery proof found'
        ]);
    }
}
else if ($action === 'acceptOrder') {
    if (!isset($_POST['order_id']) || !isset($_POST['driver_id'])) {
        echo json_encode(['success' => false, 'error' => 'Missing order_id or driver_id']);
        exit;
    }

    $order_id = (int)$_POST['order_id'];
    $driver_id = (int)$_POST['driver_id'];

    $check_sql = "SELECT driver_id FROM orders WHERE order_id = $order_id AND (driver_id IS NULL OR driver_id = 0)";
    $check_result = $conn->query($check_sql);
    
    if ($check_result->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'Order already assigned to another driver']);
        exit;
    }

    $assign_sql = "UPDATE orders SET driver_id = $driver_id WHERE order_id = $order_id";
    
    if (!$conn->query($assign_sql)) {
        echo json_encode(['success' => false, 'error' => 'Failed to accept order: ' . $conn->error]);
        exit;
    }

    echo json_encode([
        'success' => true,
        'message' => 'Order accepted successfully'
    ]);
}

$conn->close();
?>