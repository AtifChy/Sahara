<?php
require_once __DIR__ . '/../../models/Auth.php';
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
  echo json_encode([
    'success' => false,
    'message' => 'Not authenticated'
  ]);
  exit;
}

$user = getCurrentUser();
if (!$user || $user['role'] !== 'ADMIN') {
  echo json_encode([
    'success' => false,
    'message' => 'Unauthorized'
  ]);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode([
    'success' => false,
    'message' => 'Invalid request method'
  ]);
  exit;
}

$order_id = $_POST['order_id'] ?? null;
$new_status = $_POST['status'] ?? null;

if (!$order_id || !is_numeric($order_id)) {
  echo json_encode([
    'success' => false,
    'message' => 'Invalid order ID'
  ]);
  exit;
}

$valid_statuses = ['PENDING', 'PAID', 'DELIVERED', 'CANCELLED'];
if (!$new_status || !in_array($new_status, $valid_statuses)) {
  echo json_encode([
    'success' => false,
    'message' => 'Invalid status'
  ]);
  exit;
}

try {
  $order = fetchOne("SELECT status FROM orders WHERE id = {$order_id}");

  if (!$order) {
    throw new Exception('Order not found');
  }

  $old_status = $order['status'];

  if ($new_status === 'CANCELLED' && $old_status !== 'CANCELLED') {
    $items = fetchAll("SELECT product_id, quantity FROM order_items WHERE order_id = {$order_id}");

    foreach ($items as $item) {
      query("UPDATE products SET stock = stock + {$item['quantity']} WHERE id = {$item['product_id']}");
    }
  }

  if ($old_status === 'CANCELLED' && $new_status !== 'CANCELLED') {
    $items = fetchAll("SELECT product_id, quantity FROM order_items WHERE order_id = {$order_id}");

    foreach ($items as $item) {
      $product = fetchOne("SELECT stock FROM products WHERE id = {$item['product_id']}");

      if ($product['stock'] < $item['quantity']) {
        throw new Exception('Insufficient stock to reactivate this order');
      }

      query("UPDATE products SET stock = stock - {$item['quantity']} WHERE id = {$item['product_id']}");
    }
  }

  $result = query("UPDATE orders SET status = '{$new_status}', updated_at = NOW() WHERE id = {$order_id}");

  if (!$result) {
    throw new Exception('Failed to update order status');
  }

  echo json_encode([
    'success' => true,
    'message' => 'Order status updated successfully'
  ]);
} catch (Exception $e) {
  error_log('Error updating order status: ' . $e->getMessage());

  echo json_encode([
    'success' => false,
    'message' => $e->getMessage()
  ]);
}
