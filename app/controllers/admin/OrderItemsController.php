<?php
require_once __DIR__ . '/../../models/Auth.php';
require_once __DIR__ . '/../../config/database.php';

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

$order_id = $_GET['order_id'] ?? null;

// Validate order_id
if (!$order_id || !is_numeric($order_id)) {
  echo json_encode([
    'success' => false,
    'message' => 'Invalid order ID'
  ]);
  exit;
}

try {
  // Get all order items with product and seller information
  $query = "SELECT 
              oi.id,
              oi.product_id,
              oi.quantity,
              oi.price,
              oi.seller_id,
              p.title,
              p.image,
              CONCAT(up.first_name, ' ', COALESCE(up.last_name, '')) as seller_name
            FROM order_items oi
            INNER JOIN products p ON oi.product_id = p.id
            LEFT JOIN users u ON oi.seller_id = u.id
            LEFT JOIN user_profiles up ON u.id = up.user_id
            WHERE oi.order_id = {$order_id}
            ORDER BY oi.id ASC";

  $result = fetchAll($query);

  $items = [];
  foreach ($result as $row) {
    $items[] = [
      'id' => $row['id'],
      'product_id' => $row['product_id'],
      'title' => $row['title'],
      'quantity' => $row['quantity'],
      'price' => $row['price'],
      'seller_id' => $row['seller_id'],
      'seller_name' => $row['seller_name'] ?: 'Unknown',
      'image' => $row['image'] ? $row['image'] : '/assets/product_placeholder.svg'
    ];
  }

  echo json_encode([
    'success' => true,
    'items' => $items
  ]);
} catch (Exception $e) {
  error_log('Error fetching order items: ' . $e->getMessage());
  echo json_encode([
    'success' => false,
    'message' => 'Failed to fetch order items'
  ]);
}
