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

if ($action === 'getOrders') {
    $driver_id = (int)$_GET['driver_id'];

    $availableSql = "SELECT 
                        o.order_id,
                        o.tracking_number,
                        o.status,
                        o.total_price,
                        o.order_date,
                        c.customer_name,
                        c.location,
                        c.mobile,
                        COUNT(oi.order_item_id) as item_count
                    FROM orders o
                    JOIN customers c ON o.customer_id = c.customer_id
                    LEFT JOIN order_items oi ON o.order_id = oi.order_id
                    WHERE o.driver_id IS NULL AND o.status IN ('Pending', 'Preparing')
                    GROUP BY o.order_id
                    ORDER BY o.order_date DESC";

    $availableResult = $conn->query($availableSql);
    $available = [];
    if ($availableResult && $availableResult->num_rows > 0) {
        while ($row = $availableResult->fetch_assoc()) {
            $row['total_price'] = (float)$row['total_price'];
            $row['item_count'] = (int)$row['item_count'];
            $available[] = $row;
        }
    }

    $myOrdersSql = "SELECT 
                        o.order_id,
                        o.tracking_number,
                        o.status,
                        o.total_price,
                        o.order_date,
                        c.customer_name,
                        c.location,
                        c.mobile,
                        COUNT(oi.order_item_id) as item_count
                    FROM orders o
                    JOIN customers c ON o.customer_id = c.customer_id
                    LEFT JOIN order_items oi ON o.order_id = oi.order_id
                    WHERE o.driver_id = $driver_id AND o.status != 'Delivered'
                    GROUP BY o.order_id
                    ORDER BY o.order_date DESC";

    $myOrdersResult = $conn->query($myOrdersSql);
    $myOrders = [];
    if ($myOrdersResult && $myOrdersResult->num_rows > 0) {
        while ($row = $myOrdersResult->fetch_assoc()) {
            $row['total_price'] = (float)$row['total_price'];
            $row['item_count'] = (int)$row['item_count'];
            $myOrders[] = $row;
        }
    }

    echo json_encode([
        'success' => true,
        'available' => $available,
        'myOrders' => $myOrders
    ]);
}
else if ($action === 'getOrderDetails') {
    $order_id = (int)$_GET['order_id'];

    $orderSql = "SELECT 
                    o.order_id,
                    o.tracking_number,
                    o.status,
                    o.total_price,
                    o.order_date,
                    o.notes,
                    c.customer_name,
                    c.location,
                    c.mobile
                FROM orders o
                JOIN customers c ON o.customer_id = c.customer_id
                WHERE o.order_id = $order_id";

    $orderResult = $conn->query($orderSql);
    if ($orderResult && $orderResult->num_rows > 0) {
        $order = $orderResult->fetch_assoc();
        $order['total_price'] = (float)$order['total_price'];

        $itemsSql = "SELECT 
                        oi.quantity,
                        oi.price_at_purchase as price,
                        m.name
                    FROM order_items oi
                    JOIN menu_items m ON oi.menu_id = m.menu_id
                    WHERE oi.order_id = $order_id";

        $itemsResult = $conn->query($itemsSql);
        $items = [];
        if ($itemsResult && $itemsResult->num_rows > 0) {
            while ($row = $itemsResult->fetch_assoc()) {
                $row['price'] = (float)$row['price'];
                $items[] = $row;
            }
        }

        $order['items'] = $items;

        echo json_encode([
            'success' => true,
            'order' => $order
        ]);
    } else {
        echo json_encode(['error' => 'Order not found']);
    }
}
else if ($action === 'acceptOrder') {
    $order_id = (int)$_POST['order_id'];
    $driver_id = (int)$_POST['driver_id'];

    $checkSql = "SELECT driver_id, status FROM orders WHERE order_id = $order_id";
    $checkResult = $conn->query($checkSql);
    
    if ($checkResult->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'Order not found']);
        exit;
    }

    $checkRow = $checkResult->fetch_assoc();

    if ($checkRow['driver_id'] !== null) {
        echo json_encode(['success' => false, 'error' => 'Order already taken by another driver']);
        exit;
    }

    $sql = "UPDATE orders 
            SET driver_id = $driver_id
            WHERE order_id = $order_id";

    if ($conn->query($sql) === TRUE) {
        $log_sql = "INSERT INTO order_status_log (order_id, previous_status, new_status, updated_by, updated_by_role, notes)
                    VALUES ($order_id, '" . $checkRow['status'] . "', '" . $checkRow['status'] . "', (SELECT driver_name FROM drivers WHERE driver_id = $driver_id), 'driver', 'Order accepted by driver')";
        $conn->query($log_sql);

        echo json_encode(['success' => true, 'message' => 'Order accepted']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to accept order']);
    }
}
else if ($action === 'completeOrder') {
    echo json_encode([
        'success' => false, 
        'error' => 'Use delivery_complete.php to upload proof and mark as delivered'
    ]);
}
else {
    echo json_encode(['error' => 'Invalid action']);
}

$conn->close();
?>