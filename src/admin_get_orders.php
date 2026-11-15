<?php
require_once __DIR__ . '/config.php';
$cfg = require __DIR__ . '/config.php';

header('Content-Type: application/json');

$conn = new mysqli($cfg['host'], $cfg['user'], $cfg['pass'], $cfg['db']);
if ($conn->connect_error) {
    die(json_encode(["error" => "Database connection failed."]));
}

$sql = "SELECT 
            o.order_id AS id, 
            o.tracking_number, 
            o.total_price, 
            o.status, 
            o.order_date, 
            o.receipt_image,
            o.delivery_proof_image,
            o.driver_id,
            c.customer_name, 
            c.location,
            c.mobile,
            d.driver_name,
            d.vehicle_type,
            d.availability_status
        FROM orders o
        JOIN customers c ON o.customer_id = c.customer_id
        LEFT JOIN drivers d ON o.driver_id = d.driver_id
        ORDER BY o.order_date DESC";

$result = $conn->query($sql);

$orders = array();
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
}

echo json_encode($orders);
$conn->close();
?>