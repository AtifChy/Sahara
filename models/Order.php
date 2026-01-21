<?php

require_once __DIR__ . '/../controllers/db.php';

function createOrder($userId, $cartItems, $shippingAddress, $total)
{
    $db = getDB();

    if (empty($userId) || empty($cartItems) || empty($shippingAddress)) {
        return ['success' => false, 'message' => 'Missing required information'];
    }

    // Begin transaction
    mysqli_begin_transaction($db);

    try {
        foreach ($cartItems as $item) {
            $productId = (int)$item['id'];
            $requestedQty = (int)$item['quantity'];

            $product = fetchOne("SELECT stock, seller_id FROM products WHERE id = $productId");

            if (!$product) {
                throw new Exception("Product ID $productId not found");
            }

            if ($product['stock'] < $requestedQty) {
                throw new Exception("Insufficient stock for {$item['title']}. Only {$product['stock']} available.");
            }
        }

        // Insert into orders table
        $sql = "INSERT INTO orders (user_id, total, status, shipping_address) 
                          VALUES ($userId, $total, 'PENDING', '$shippingAddress')";

        if (!query($sql)) {
            throw new Exception("Failed to create order");
        }

        $orderId = lastInsertId();

        // Insert order items and update product stock
        foreach ($cartItems as $item) {
            $productId = (int)$item['id'];
            $quantity = (int)$item['quantity'];
            $price = (float)$item['price'];

            // Get seller_id from product
            $product = fetchOne("SELECT seller_id FROM products WHERE id = $productId");
            $sellerId = (int)$product['seller_id'];

            // Insert order item
            $insertItemSQL = "INSERT INTO order_items (order_id, product_id, seller_id, quantity, price) 
                             VALUES ($orderId, $productId, $sellerId, $quantity, $price)";

            if (!query($insertItemSQL)) {
                throw new Exception("Failed to add order item");
            }

            // Reduce product stock
            $updateStockSQL = "UPDATE products SET stock = stock - $quantity WHERE id = $productId";

            if (!query($updateStockSQL)) {
                throw new Exception("Failed to update product stock");
            }
        }

        // Commit transaction
        mysqli_commit($db);

        return [
            'success' => true,
            'order_id' => $orderId,
            'message' => 'Order created successfully'
        ];
    } catch (Exception $e) {
        // Rollback on error
        mysqli_rollback($db);

        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

function getOrdersByUser($userId)
{
    $sql = "SELECT id, total, status, shipping_address, created_at, updated_at 
            FROM orders 
            WHERE user_id = $userId 
            ORDER BY created_at DESC";

    return fetchAll($sql);
}

function getOrderDetails($orderId, $userId = null)
{
    // Get order
    $orderSQL = "SELECT o.id, o.user_id, o.total, o.status, o.shipping_address, o.created_at, o.updated_at,
                        u.email, p.first_name, p.last_name, p.phone
                 FROM orders o
                 LEFT JOIN users u ON o.user_id = u.id
                 LEFT JOIN user_profiles p ON u.id = p.user_id
                 WHERE o.id = $orderId";

    if ($userId !== null) {
        $orderSQL .= " AND o.user_id = $userId";
    }

    $order = fetchOne($orderSQL);

    if (!$order) {
        return null;
    }

    // Get order items
    $itemsSQL = "SELECT oi.id, oi.product_id, oi.quantity, oi.price,
                        p.title, p.image, p.description
                 FROM order_items oi
                 LEFT JOIN products p ON oi.product_id = p.id
                 WHERE oi.order_id = $orderId";

    $items = fetchAll($itemsSQL);

    $order['items'] = $items;

    return $order;
}

function updateOrderStatus($orderId, $status)
{
    $validStatuses = ['PENDING', 'PAID', 'DELIVERED', 'CANCELLED'];

    if (!in_array($status, $validStatuses)) {
        return false;
    }

    $sql = "UPDATE orders SET status = '$status', updated_at = NOW() WHERE id = $orderId";

    return query($sql) !== false;
}

function getOrderCountByStatus($userId, $status)
{
    $sql = "SELECT COUNT(*) as count FROM orders WHERE user_id = $userId AND status = '$status'";

    $result = fetchOne($sql);

    return $result ? (int)$result['count'] : 0;
}

function cancelOrder($orderId, $userId)
{
    $db = getDB();

    // Get order details
    $order = fetchOne("SELECT status FROM orders WHERE id = $orderId AND user_id = $userId");

    if (!$order) {
        return ['success' => false, 'message' => 'Order not found'];
    }

    if ($order['status'] !== 'PENDING') {
        return ['success' => false, 'message' => 'Only pending orders can be cancelled'];
    }

    // Begin transaction
    mysqli_begin_transaction($db);

    try {
        // Get order items to restore stock
        $items = fetchAll("SELECT product_id, quantity FROM order_items WHERE order_id = $orderId");

        // Restore product stock
        foreach ($items as $item) {
            $productId = (int)$item['product_id'];
            $quantity = (int)$item['quantity'];

            $updateStockSQL = "UPDATE products SET stock = stock + $quantity WHERE id = $productId";

            if (!query($updateStockSQL)) {
                throw new Exception("Failed to restore product stock");
            }
        }

        // Update order status to CANCELLED
        if (!updateOrderStatus($orderId, 'CANCELLED')) {
            throw new Exception("Failed to cancel order");
        }

        // Commit transaction
        mysqli_commit($db);

        return ['success' => true, 'message' => 'Order cancelled successfully'];
    } catch (Exception $e) {
        // Rollback on error
        mysqli_rollback($db);

        return ['success' => false, 'message' => $e->getMessage()];
    }
}
