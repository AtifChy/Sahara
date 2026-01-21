<?php
require_once __DIR__ . '/../../models/Auth.php';
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

// Require admin authentication
requireAuth();
requireRole('ADMIN');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode([
    'success' => false,
    'message' => 'Invalid request method'
  ]);
  exit;
}

$order_id = $_POST['order_id'] ?? null;
$new_status = $_POST['status'] ?? null;

// Validate inputs
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
  $db = getDB();
  
  // Start transaction
  mysqli_begin_transaction($db);
  
  // Get current order status
  $query = "SELECT status FROM orders WHERE id = ?";
  $stmt = mysqli_prepare($db, $query);
  mysqli_stmt_bind_param($stmt, 'i', $order_id);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);
  $order = mysqli_fetch_assoc($result);
  
  if (!$order) {
    throw new Exception('Order not found');
  }
  
  $old_status = $order['status'];
  
  // If changing TO cancelled, restore stock
  if ($new_status === 'CANCELLED' && $old_status !== 'CANCELLED') {
    $query = "SELECT product_id, quantity FROM order_items WHERE order_id = ?";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, 'i', $order_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    while ($item = mysqli_fetch_assoc($result)) {
      $update_stock = "UPDATE products SET stock = stock + ? WHERE id = ?";
      $stmt2 = mysqli_prepare($db, $update_stock);
      mysqli_stmt_bind_param($stmt2, 'ii', $item['quantity'], $item['product_id']);
      mysqli_stmt_execute($stmt2);
    }
  }
  
  // If changing FROM cancelled TO something else, reduce stock again
  if ($old_status === 'CANCELLED' && $new_status !== 'CANCELLED') {
    $query = "SELECT product_id, quantity FROM order_items WHERE order_id = ?";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, 'i', $order_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    while ($item = mysqli_fetch_assoc($result)) {
      // Check if there's enough stock
      $check_stock = "SELECT stock FROM products WHERE id = ?";
      $stmt2 = mysqli_prepare($db, $check_stock);
      mysqli_stmt_bind_param($stmt2, 'i', $item['product_id']);
      mysqli_stmt_execute($stmt2);
      $stock_result = mysqli_stmt_get_result($stmt2);
      $product = mysqli_fetch_assoc($stock_result);
      
      if ($product['stock'] < $item['quantity']) {
        throw new Exception('Insufficient stock to reactivate this order');
      }
      
      // Reduce stock
      $update_stock = "UPDATE products SET stock = stock - ? WHERE id = ?";
      $stmt3 = mysqli_prepare($db, $update_stock);
      mysqli_stmt_bind_param($stmt3, 'ii', $item['quantity'], $item['product_id']);
      mysqli_stmt_execute($stmt3);
    }
  }
  
  // Update order status
  $query = "UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?";
  $stmt = mysqli_prepare($db, $query);
  mysqli_stmt_bind_param($stmt, 'si', $new_status, $order_id);
  mysqli_stmt_execute($stmt);
  
  // Commit transaction
  mysqli_commit($db);
  
  echo json_encode([
    'success' => true,
    'message' => 'Order status updated successfully'
  ]);
  
} catch (Exception $e) {
  // Rollback on error
  mysqli_rollback($db);
  error_log('Error updating order status: ' . $e->getMessage());
  
  echo json_encode([
    'success' => false,
    'message' => $e->getMessage()
  ]);
}
?>
