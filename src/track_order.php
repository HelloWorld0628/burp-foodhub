<?php
require_once __DIR__ . '/config.php';
$cfg = require __DIR__ . '/config.php';

header('Content-Type: application/json');

$conn = new mysqli($cfg['host'], $cfg['user'], $cfg['pass'], $cfg['db']);
if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed.']);
    exit;
}

$tracking = isset($_GET['tracking']) ? trim($_GET['tracking']) : '';
$email = isset($_GET['email']) ? trim($_GET['email']) : '';
$mode = isset($_GET['mode']) ? $_GET['mode'] : 'search';

if ($mode === 'search' && !empty($tracking)) {
    $tracking = $conn->real_escape_string(strtoupper($tracking));

    $sql = "SELECT 
                o.order_id,
                o.tracking_number, 
                o.status, 
                o.total_price, 
                o.order_date, 
                c.customer_name 
            FROM orders o
            JOIN customers c ON o.customer_id = c.customer_id
            WHERE UPPER(o.tracking_number) = '$tracking'
            LIMIT 1";

    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $orderData = $result->fetch_assoc();
        $orderData['total_price'] = (float)$orderData['total_price'];
        $orderId = $orderData['order_id'];

        $itemsSql = "SELECT 
                        oi.quantity, 
                        oi.price_at_purchase as price, 
                        m.name 
                     FROM order_items oi
                     JOIN menu_items m ON oi.menu_id = m.menu_id
                     WHERE oi.order_id = $orderId";
                     
        $itemsResult = $conn->query($itemsSql);
        
        $cart_items = array();
        if($itemsResult && $itemsResult->num_rows > 0){
            while($itemRow = $itemsResult->fetch_assoc()){
                $cart_items[] = $itemRow;
            }
        }
        
        $orderData['cart_items'] = $cart_items;
        unset($orderData['order_id']); 

        echo json_encode(['success' => true, 'order' => $orderData]);
    } else {
        echo json_encode(['error' => 'No order found with that tracking number. Please double-check and try again.']);
    }
}
else if ($mode === 'all' && !empty($email)) {
    $email = $conn->real_escape_string($email);

    $sql = "SELECT 
                o.order_id,
                o.tracking_number, 
                o.status, 
                o.total_price, 
                o.order_date, 
                c.customer_name 
            FROM orders o
            JOIN customers c ON o.customer_id = c.customer_id
            WHERE c.email = '$email'
            ORDER BY o.order_date DESC";

    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $orders = array();
        while ($orderRow = $result->fetch_assoc()) {
            $orderRow['total_price'] = (float)$orderRow['total_price'];
            $orderId = $orderRow['order_id'];

            $itemsSql = "SELECT 
                            oi.quantity, 
                            oi.price_at_purchase as price, 
                            m.name 
                         FROM order_items oi
                         JOIN menu_items m ON oi.menu_id = m.menu_id
                         WHERE oi.order_id = $orderId";
            
            $itemsResult = $conn->query($itemsSql);
            $cart_items = array();
            
            if($itemsResult && $itemsResult->num_rows > 0){
                while($itemRow = $itemsResult->fetch_assoc()){
                    $cart_items[] = $itemRow;
                }
            }
            
            $orderRow['cart_items'] = $cart_items;
            unset($orderRow['order_id']);
            $orders[] = $orderRow;
        }
        
        echo json_encode(['success' => true, 'orders' => $orders, 'count' => count($orders)]);
    } else {
        echo json_encode(['success' => true, 'orders' => [], 'count' => 0]);
    }
}
else {
    echo json_encode(['error' => 'Invalid request. Please provide tracking number or email.']);
}

$conn->close();
?>