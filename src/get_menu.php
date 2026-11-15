<?php
require_once __DIR__ . '/config.php';
$cfg = require __DIR__ . '/config.php';

$conn = new mysqli($cfg['host'], $cfg['user'], $cfg['pass'], $cfg['db']);
if ($conn->connect_error) {
    die(json_encode(["error" => "Database connection failed: " . $conn->connect_error]));
}

$sql = "SELECT * FROM menu_items";
$result = $conn->query($sql);

$menu = array();

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $row['menu_id'] = (int)$row['menu_id'];
        $row['price'] = (float)$row['price'];
        $menu[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($menu);

$conn->close();
?>