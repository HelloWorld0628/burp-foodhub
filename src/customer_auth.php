<?php
require_once __DIR__ . '/config.php';
$cfg = require __DIR__ . '/config.php';

session_start();
header('Content-Type: application/json');

$conn = new mysqli($cfg['host'], $cfg['user'], $cfg['pass'], $cfg['db']);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed.']);
    exit;
}

$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');

if ($action === 'register') {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $mobile = $conn->real_escape_string($_POST['mobile']);
    $location = $conn->real_escape_string($_POST['location']);

    if (empty($name) || empty($email) || empty($password) || empty($mobile) || empty($location)) {
        echo json_encode(['success' => false, 'error' => 'All fields are required']);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'error' => 'Invalid email format']);
        exit;
    }

    if (strlen($password) < 6) {
        echo json_encode(['success' => false, 'error' => 'Password must be at least 6 characters']);
        exit;
    }

    $checkEmail = $conn->query("SELECT customer_id FROM customers WHERE email = '$email'");
    if ($checkEmail && $checkEmail->num_rows > 0) {
        echo json_encode(['success' => false, 'error' => 'Email already registered']);
        exit;
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO customers (customer_name, email, password, mobile, location) 
            VALUES ('$name', '$email', '$passwordHash', '$mobile', '$location')";

    if ($conn->query($sql) === TRUE) {
        $customer_id = $conn->insert_id;
        $_SESSION['customer_logged_in'] = true;
        $_SESSION['customer_id'] = $customer_id;
        $_SESSION['customer_name'] = $name;
        $_SESSION['customer_email'] = $email;
        $_SESSION['customer_mobile'] = $mobile;
        $_SESSION['customer_location'] = $location;

        echo json_encode([
            'success' => true,
            'message' => 'Registration successful',
            'customer' => [
                'customer_id' => $customer_id,
                'customer_name' => $name,
                'email' => $email,
                'mobile' => $mobile,
                'location' => $location
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Registration failed: ' . $conn->error]);
    }
}
else if ($action === 'login') {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'error' => 'Email and password are required']);
        exit;
    }

    $sql = "SELECT customer_id, customer_name, email, password, mobile, location FROM customers WHERE email = '$email' LIMIT 1";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $customer = $result->fetch_assoc();

        if (password_verify($password, $customer['password'])) {
            $_SESSION['customer_logged_in'] = true;
            $_SESSION['customer_id'] = $customer['customer_id'];
            $_SESSION['customer_name'] = $customer['customer_name'];
            $_SESSION['customer_email'] = $customer['email'];
            $_SESSION['customer_mobile'] = $customer['mobile'];
            $_SESSION['customer_location'] = $customer['location'];

            echo json_encode([
                'success' => true,
                'message' => 'Login successful',
                'customer' => [
                    'customer_id' => $customer['customer_id'],
                    'customer_name' => $customer['customer_name'],
                    'email' => $customer['email'],
                    'mobile' => $customer['mobile'],
                    'location' => $customer['location']
                ]
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid email or password']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid email or password']);
    }
}
else if ($action === 'logout') {
    session_destroy();
    echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
}
else if ($action === 'check') {
    if (isset($_SESSION['customer_logged_in']) && $_SESSION['customer_logged_in'] === true) {
        echo json_encode([
            'success' => true,
            'logged_in' => true,
            'customer' => [
                'customer_id' => $_SESSION['customer_id'],
                'customer_name' => $_SESSION['customer_name'],
                'email' => $_SESSION['customer_email'],
                'mobile' => $_SESSION['customer_mobile'],
                'location' => $_SESSION['customer_location']
            ]
        ]);
    } else {
        echo json_encode(['success' => true, 'logged_in' => false]);
    }
}
else if ($action === 'updateProfile') {
    if (!isset($_SESSION['customer_logged_in']) || $_SESSION['customer_logged_in'] !== true) {
        echo json_encode(['success' => false, 'error' => 'Not logged in']);
        exit;
    }

    $customer_id = (int)$_SESSION['customer_id'];
    $name = $conn->real_escape_string($_POST['name']);
    $mobile = $conn->real_escape_string($_POST['mobile']);
    $location = $conn->real_escape_string($_POST['location']);

    $sql = "UPDATE customers SET customer_name = '$name', mobile = '$mobile', location = '$location' WHERE customer_id = $customer_id";

    if ($conn->query($sql) === TRUE) {
        $_SESSION['customer_name'] = $name;
        $_SESSION['customer_mobile'] = $mobile;
        $_SESSION['customer_location'] = $location;

        echo json_encode([
            'success' => true,
            'message' => 'Profile updated',
            'customer' => [
                'customer_id' => $customer_id,
                'customer_name' => $name,
                'email' => $_SESSION['customer_email'],
                'mobile' => $mobile,
                'location' => $location
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Update failed: ' . $conn->error]);
    }
}
else if ($action === 'changePassword') {
    if (!isset($_SESSION['customer_logged_in']) || $_SESSION['customer_logged_in'] !== true) {
        echo json_encode(['success' => false, 'error' => 'Not logged in']);
        exit;
    }

    $customer_id = (int)$_SESSION['customer_id'];
    $currentPassword = $_POST['currentPassword'];
    $newPassword = $_POST['newPassword'];

    if (strlen($newPassword) < 6) {
        echo json_encode(['success' => false, 'error' => 'New password must be at least 6 characters']);
        exit;
    }

    $sql = "SELECT password FROM customers WHERE customer_id = $customer_id";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($currentPassword, $row['password'])) {
            $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $updateSql = "UPDATE customers SET password = '$newPasswordHash' WHERE customer_id = $customer_id";
            if ($conn->query($updateSql) === TRUE) {
                echo json_encode(['success' => true, 'message' => 'Password changed successfully']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to change password']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Current password is incorrect']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Customer not found']);
    }
}
else {
    echo json_encode(['success' => false, 'error' => 'Invalid action']);
}

$conn->close();
?>