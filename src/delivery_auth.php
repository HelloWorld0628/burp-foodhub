<?php
require_once __DIR__ . '/config.php';
$cfg = require __DIR__ . '/config.php';

header('Content-Type: application/json');

$conn = new mysqli($cfg['host'], $cfg['user'], $cfg['pass'], $cfg['db']);
if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed.']);
    exit;
}

$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');

if ($action === 'login') {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    $sql = "SELECT driver_id, username, driver_name, email, phone, vehicle_type, availability_status FROM drivers WHERE username = '$username'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $driver = $result->fetch_assoc();
        
        $storedPassword = $conn->real_escape_string($password);
        $checkSql = "SELECT password FROM drivers WHERE username = '$username'";
        $checkResult = $conn->query($checkSql);
        $passwordRow = $checkResult->fetch_assoc();

        if ($passwordRow && $passwordRow['password'] === $storedPassword) {
            echo json_encode([
                'success' => true,
                'driver' => $driver
            ]);
        } else {
            echo json_encode(['error' => 'Invalid password']);
        }
    } else {
        echo json_encode(['error' => 'Driver not found']);
    }
}
else if ($action === 'register') {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $username = $conn->real_escape_string($_POST['username']);
    $vehicle = $conn->real_escape_string($_POST['vehicle']);
    $password = $conn->real_escape_string($_POST['password']);

    $checkUser = $conn->query("SELECT driver_id FROM drivers WHERE username = '$username'");
    if ($checkUser && $checkUser->num_rows > 0) {
        echo json_encode(['error' => 'Username already taken']);
        exit;
    }

    $checkEmail = $conn->query("SELECT driver_id FROM drivers WHERE email = '$email'");
    if ($checkEmail && $checkEmail->num_rows > 0) {
        echo json_encode(['error' => 'Email already registered']);
        exit;
    }

    $sql = "INSERT INTO drivers (username, password, driver_name, email, phone, vehicle_type, availability_status) 
            VALUES ('$username', '$password', '$name', '$email', '$phone', '$vehicle', 'Offline')";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(['success' => true, 'message' => 'Registration successful']);
    } else {
        echo json_encode(['error' => 'Registration failed: ' . $conn->error]);
    }
}
else if ($action === 'updateStatus') {
    $driver_id = (int)$_POST['driver_id'];
    $status = $conn->real_escape_string($_POST['status']);

    $sql = "UPDATE drivers SET availability_status = '$status' WHERE driver_id = $driver_id";
    if ($conn->query($sql) === TRUE) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Update failed']);
    }
}
else {
    echo json_encode(['error' => 'Invalid action']);
}

$conn->close();
?>