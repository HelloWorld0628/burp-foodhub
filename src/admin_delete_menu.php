<?php
require_once __DIR__ . '/config.php';
$cfg = require __DIR__ . '/config.php';

header('Content-Type: application/json');
$conn = new mysqli($cfg['host'], $cfg['user'], $cfg['pass'], $cfg['db']);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $menu_id = isset($_POST['menu_id']) ? (int)$_POST['menu_id'] : 0;

    if ($menu_id > 0) {
        $sql = "DELETE FROM menu_items WHERE menu_id = $menu_id";
        if ($conn->query($sql) === TRUE) {
            echo json_encode(['success' => true, 'message' => 'Item deleted successfully!']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to delete item: ' . $conn->error]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid menu ID']);
    }
}
$conn->close();
?>