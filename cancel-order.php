<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/order-functions.php';

header('Content-Type: application/json');

requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode([
    'success' => false,
    'message' => 'Invalid request method'
  ]);
  exit;
}

$orderId = $_POST['order_id'] ?? null;
$userId = $_SESSION['user_id'];

if (!$orderId || !is_numeric($orderId)) {
  echo json_encode([
    'success' => false,
    'message' => 'Invalid order ID'
  ]);
  exit;
}

$result = cancelOrder((int)$orderId, (int)$userId);

echo json_encode($result);
?>
