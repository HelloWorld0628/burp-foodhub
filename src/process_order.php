<?php
require_once __DIR__ . '/config.php';
$cfg = require __DIR__ . '/config.php';

$conn = new mysqli($cfg['host'], $cfg['user'], $cfg['pass'], $cfg['db']);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$fullName = $conn->real_escape_string($_POST['fullName']);
$location = $conn->real_escape_string($_POST['location']);
$mobile = $conn->real_escape_string($_POST['mobile']);
$email = $conn->real_escape_string($_POST['email']);
$notes = $conn->real_escape_string($_POST['notes']);
$paymentMethod = $conn->real_escape_string($_POST['paymentMethod']);
$cartData = $_POST['cartData'];
$totalPrice = $conn->real_escape_string($_POST['totalPrice']);

// Check if customer_id is provided (for logged-in users)
$customer_id = isset($_POST['customer_id']) ? (int)$_POST['customer_id'] : 0;

$randomString = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 5));
$trackingNumber = 'BURP-' . $randomString;

$targetDir = __DIR__ . '/../public/payments_img/';
if (!file_exists($targetDir)) mkdir($targetDir, 0777, true);

$originalFileName = basename($_FILES["receipt"]["name"]);
$fileType = strtolower(pathinfo($originalFileName, PATHINFO_EXTENSION));
$cleanName = preg_replace('/[^a-zA-Z0-9]/', '_', strtolower($fullName));
$newFileName = $cleanName . '_' . time() . '.' . $fileType;
$targetFilePath = $targetDir . $newFileName;

if(move_uploaded_file($_FILES["receipt"]["tmp_name"], $targetFilePath)){

    if ($customer_id > 0) {
        // Logged-in user: verify the customer exists and optionally update their info
        $verifyCustomer = $conn->query("SELECT customer_id FROM customers WHERE customer_id = $customer_id AND email = '$email' LIMIT 1");
        if ($verifyCustomer && $verifyCustomer->num_rows > 0) {
            $conn->query("UPDATE customers SET location = '$location', mobile = '$mobile', customer_name = '$fullName' WHERE customer_id = $customer_id");
        } else {
            // Fallback: customer_id doesn't match email, treat as guest
            $customer_id = 0;
        }
    }

    if ($customer_id === 0) {
        // Guest checkout: find or create customer by email
        $checkCustomer = $conn->query("SELECT customer_id FROM customers WHERE email = '$email' LIMIT 1");

        if ($checkCustomer && $checkCustomer->num_rows > 0) {
            $custRow = $checkCustomer->fetch_assoc();
            $customer_id = $custRow['customer_id'];
            $conn->query("UPDATE customers SET location = '$location', mobile = '$mobile', customer_name = '$fullName' WHERE customer_id = $customer_id");
        } else {
            $conn->query("INSERT INTO customers (customer_name, email, password, mobile, location) VALUES ('$fullName', '$email', '', '$mobile', '$location')");
            $customer_id = $conn->insert_id;
        }
    }

    $orderSql = "INSERT INTO orders (tracking_number, customer_id, notes, payment_method, total_price, receipt_image, status)
                 VALUES ('$trackingNumber', $customer_id, '$notes', '$paymentMethod', '$totalPrice', '$newFileName', 'Pending')";

    if($conn->query($orderSql) === TRUE){
        $order_id = $conn->insert_id;

        $cartArray = json_decode($cartData, true);
        foreach($cartArray as $item) {
            $menu_id = (int)$item['id'];
            $quantity = (int)$item['quantity'];
            $price_at_purchase = (float)$item['price'];

            $itemSql = "INSERT INTO order_items (order_id, menu_id, quantity, price_at_purchase)
                        VALUES ($order_id, $menu_id, $quantity, $price_at_purchase)";
            $conn->query($itemSql);
        }

        echo "Thank you, $fullName! Your order has been received.\n\nYour Tracking Number is: $trackingNumber\n\nPlease take a screenshot of this number to track your order.";
    } else {
        echo "Error saving order to database: " . $conn->error;
    }
} else {
    echo "Error uploading receipt image. Please try again.";
}

$conn->close();
?>