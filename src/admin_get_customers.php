<?php
require_once __DIR__ . '/config.php';
$cfg = require __DIR__ . '/config.php';

header('Content-Type: application/json');

$conn = new mysqli($cfg['host'], $cfg['user'], $cfg['pass'], $cfg['db']);
if ($conn->connect_error) {
    die(json_encode(["error" => "Database connection failed."]));
}

$sql = "SELECT 
            c.customer_name, 
            c.email, 
            c.mobile, 
            c.location, 
            COUNT(o.order_id) as total_orders 
        FROM customers c
        LEFT JOIN orders o ON c.customer_id = o.customer_id
        GROUP BY c.customer_id 
        ORDER BY c.customer_name ASC";

$result = $conn->query($sql);

$customers = array();
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $customers[] = $row;
    }
}

echo json_encode($customers);
$conn->close();
?>