<?php
require_once __DIR__ . '/config.php';
$cfg = require __DIR__ . '/config.php';

header('Content-Type: application/json');
$conn = new mysqli($cfg['host'], $cfg['user'], $cfg['pass'], $cfg['db']);

$email = $conn->real_escape_string($_GET['email']);

$sql = "SELECT o.* FROM orders o 
        JOIN customers c ON o.customer_id = c.customer_id 
        WHERE c.email = '$email' 
        ORDER BY o.order_date DESC";

$result = $conn->query($sql);
$orders = [];
while($row = $result->fetch_assoc()) {
    $orders[] = $row;
}
echo json_encode($orders);
$conn->close();
?>