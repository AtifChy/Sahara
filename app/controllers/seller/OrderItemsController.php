<?php
require_once __DIR__ . '/../../models/Auth.php';
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

// Check authentication for API endpoint
if (!isLoggedIn()) {
  echo json_encode([
    'success' => false,
    'message' => 'Not authenticated'
  ]);
  exit;
}

$user = getCurrentUser();
if (!$user || !in_array($user['role'], ['SELLER', 'ADMIN'])) {
  echo json_encode([
    'success' => false,
    'message' => 'Unauthorized'
  ]);
  exit;
}

$seller_id = $_SESSION['user_id'];
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
  // Get order items for this seller only
  $query = "SELECT 
              oi.id,
              oi.product_id,
              oi.quantity,
              oi.price,
              p.title,
              p.image
            FROM order_items oi
            INNER JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = {$order_id} AND oi.seller_id = {$seller_id}
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
