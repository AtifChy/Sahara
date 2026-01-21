<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json');

// Require seller authentication
requireAuth();
requireRole('SELLER');

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
            WHERE oi.order_id = ? AND oi.seller_id = ?
            ORDER BY oi.id ASC";
  
  $stmt = mysqli_prepare(getDB(), $query);
  mysqli_stmt_bind_param($stmt, 'ii', $order_id, $seller_id);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);
  
  $items = [];
  while ($row = mysqli_fetch_assoc($result)) {
    $items[] = [
      'id' => $row['id'],
      'product_id' => $row['product_id'],
      'title' => $row['title'],
      'quantity' => $row['quantity'],
      'price' => $row['price'],
      'image' => $row['image'] ? '/uploads/' . $row['image'] : '/assets/product_placeholder.svg'
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
?>
