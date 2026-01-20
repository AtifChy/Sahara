<?php

header('Content-Type: application/json');

require_once __DIR__ . '/../includes/db.php';

$category = $_GET['category'] ?? null;
$sort = $_GET['sort'] ?? 'newest';
$priceRange = $_GET['priceRange'] ?? 'all';
$search = $_GET['search'] ?? null;

$sql = "SELECT * FROM products WHERE stock > 0";

// Apply category filter
if ($category) {
  $category_upper = strtoupper($category);
  $sql .= " AND category = '$category_upper'";
}

// Apply search filter
if ($search) {
  $sql .= " AND (title LIKE '%{$search}%' OR description LIKE '%{$search}%')";
}

// Apply price range filter
if ($priceRange && $priceRange !== 'all') {
  switch ($priceRange) {
    case '0-100':
      $sql .= " AND price < 100";
      break;
    case '100-500':
      $sql .= " AND price >= 100 AND price < 500";
      break;
    case '500-1000':
      $sql .= " AND price >= 500 AND price < 1000";
      break;
    case '1000':
      $sql .= " AND price >= 1000";
      break;
  }
}

// Apply sorting
switch ($sort) {
  case 'price-low':
    $sql .= " ORDER BY price ASC";
    break;
  case 'price-high':
    $sql .= " ORDER BY price DESC";
    break;
  case 'popular':
  case 'rating':
    $sql .= " ORDER BY rating DESC";
    break;
  case 'newest':
  default:
    $sql .= " ORDER BY is_new DESC, created_at DESC";
    break;
}

$result = fetchAll($sql);

if (!$result) {
  echo json_encode([
    'success' => false,
    'message' => 'Failed to fetch products',
    'products' => [],
    'count' => 0
  ]);
  exit;
}

$products = array_map(function ($product) {
  return [
    'id' => (int) $product['id'],
    'title' => $product['title'],
    'description' => $product['description'] ?? '',
    'price' => (float) $product['price'],
    'category' => strtolower($product['category']),
    'image' => $product['image'],
    'rating' => (float) $product['rating'],
    'isNew' => (bool) $product['is_new'],
    'stock' => (int) $product['stock']
  ];
}, $result);

echo json_encode([
  'success' => true,
  'products' => $products,
  'count' => count($products)
]);
exit;
