<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

if (!isLoggedIn() || !in_array($_SESSION['user_role'], ['SELLER', 'ADMIN'])) {
  http_response_code(401); // Unauthorized
  echo json_encode([
    'success' => false,
    'message' => 'Unauthorized access.'
  ]);
  exit;
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405); // Method not allowed
  echo json_encode([
    'success' => false,
    'message' => 'Method not allowed.'
  ]);
  exit;
}

$product_id = $_POST['id'] ?? '';

if (empty($product_id) || !is_numeric($product_id)) {
  http_response_code(400); // Bad request
  echo json_encode([
    'success' => false,
    'message' => 'Invalid product ID.'
  ]);
  exit;
}

$product_id = intval($product_id);

// check owner
if ($user_role === 'ADMIN') {
  $sql = "SELECT * FROM products WHERE id = $product_id";
} else {
  $sql = "SELECT * FROM products WHERE id = $product_id AND seller_id = $user_id";
}

$product = fetchOne($sql);

if (!$product) {
  http_response_code(404); // Not found
  echo json_encode([
    'success' => false,
    'message' => 'Product not found or you do not have permission to delete it.'
  ]);
  exit;
}

$delete_sql = "DELETE FROM products WHERE id = $product_id";

if (query($delete_sql)) {
  if (!empty($product['image'])) {
    $image_path = __DIR__ . '/..' . $product['image'];
    if (file_exists($image_path)) {
      @unlink($image_path);
    }
  }

  http_response_code(200); // OK
  echo json_encode([
    'success' => true,
    'message' => 'Product deleted successfully.'
  ]);
} else {
  http_response_code(500); // Internal server error
  echo json_encode([
    'success' => false,
    'message' => 'Failed to delete product. Please try again.'
  ]);
}
exit;
