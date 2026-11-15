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
    $name = $conn->real_escape_string($_POST['name']);
    $price = (float)$_POST['price'];
    $category = $conn->real_escape_string($_POST['category']);
    $description = $conn->real_escape_string($_POST['description']);
    
    $imagePath = "";
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === 0) {
        $targetDir = __DIR__ . '/../public/menu_img/';
        if (!file_exists($targetDir)) mkdir($targetDir, 0777, true);
        
        $originalFileName = basename($_FILES["image_file"]["name"]);
        $fileExtension = strtolower(pathinfo($originalFileName, PATHINFO_EXTENSION));
        $cleanName = preg_replace('/[^a-zA-Z0-9]/', '_', strtolower($name)) . '_' . time() . '.' . $fileExtension;
        $targetFilePath = $targetDir . $cleanName;
        
        if (move_uploaded_file($_FILES["image_file"]["tmp_name"], $targetFilePath)) {
            $imagePath = 'menu_img/' . $cleanName;
        }
    }

    if ($menu_id > 0) {
        if ($imagePath !== "") {
            $sql = "UPDATE menu_items SET name='$name', price=$price, category='$category', description='$description', image='$imagePath' WHERE menu_id=$menu_id";
        } else {
            $sql = "UPDATE menu_items SET name='$name', price=$price, category='$category', description='$description' WHERE menu_id=$menu_id";
        }
        $message = "Menu item updated successfully!";
    } else {
        if ($imagePath === "") { $imagePath = "menu_img/default.png"; }
        $sql = "INSERT INTO menu_items (name, price, category, description, image) VALUES ('$name', $price, '$category', '$description', '$imagePath')";
        $message = "New menu item added successfully!";
    }

    if ($conn->query($sql) === TRUE) {
        echo json_encode(['success' => true, 'message' => $message]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
}
$conn->close();
?>