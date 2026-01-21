<?php
require_once __DIR__ . '/../controllers/db.php';

function getProductById($productId)
{
  $productId = intval($productId);

  $product = fetchOne("
    SELECT p.*, 
           CONCAT(up.first_name, ' ', COALESCE(up.last_name, '')) as seller_name
    FROM products p
    LEFT JOIN users u ON p.seller_id = u.id
    LEFT JOIN user_profiles up ON u.id = up.user_id
    WHERE p.id = $productId
  ");

  return $product ?: null;
}

function getRelatedProducts($category, $excludeId, $limit = 4)
{
  $excludeId = intval($excludeId);
  $limit = intval($limit);
  $category = mysqli_real_escape_string($GLOBALS['conn'], $category);

  $products = fetchAll("
    SELECT * FROM products
    WHERE category = '$category'
      AND id != $excludeId
      AND stock > 0
    ORDER BY rating DESC, created_at DESC
    LIMIT $limit
  ");

  return $products ?: [];
}

function getProducts($filters = [], $limit = 20, $offset = 0)
{
  $where = ["1=1"];
  $limit = intval($limit);
  $offset = intval($offset);

  if (!empty($filters['category'])) {
    $category = mysqli_real_escape_string($GLOBALS['conn'], $filters['category']);
    $where[] = "category = '$category'";
  }

  if (!empty($filters['search'])) {
    $search = mysqli_real_escape_string($GLOBALS['conn'], $filters['search']);
    $where[] = "(title LIKE '%$search%' OR description LIKE '%$search%')";
  }

  if (!empty($filters['min_price'])) {
    $minPrice = floatval($filters['min_price']);
    $where[] = "price >= $minPrice";
  }

  if (!empty($filters['max_price'])) {
    $maxPrice = floatval($filters['max_price']);
    $where[] = "price <= $maxPrice";
  }

  if (isset($filters['in_stock']) && $filters['in_stock']) {
    $where[] = "stock > 0";
  }

  $whereClause = implode(' AND ', $where);

  $orderBy = "created_at DESC";
  if (!empty($filters['sort'])) {
    switch ($filters['sort']) {
      case 'price_asc':
        $orderBy = "price ASC";
        break;
      case 'price_desc':
        $orderBy = "price DESC";
        break;
      case 'rating':
        $orderBy = "rating DESC";
        break;
      case 'newest':
        $orderBy = "created_at DESC";
        break;
    }
  }

  $products = fetchAll("
    SELECT * FROM products
    WHERE $whereClause
    ORDER BY $orderBy
    LIMIT $limit OFFSET $offset
  ");

  return $products ?: [];
}

function getProductCount($filters = [])
{
  $where = ["1=1"];

  if (!empty($filters['category'])) {
    $category = mysqli_real_escape_string($GLOBALS['conn'], $filters['category']);
    $where[] = "category = '$category'";
  }

  if (!empty($filters['search'])) {
    $search = mysqli_real_escape_string($GLOBALS['conn'], $filters['search']);
    $where[] = "(title LIKE '%$search%' OR description LIKE '%$search%')";
  }

  if (!empty($filters['min_price'])) {
    $minPrice = floatval($filters['min_price']);
    $where[] = "price >= $minPrice";
  }

  if (!empty($filters['max_price'])) {
    $maxPrice = floatval($filters['max_price']);
    $where[] = "price <= $maxPrice";
  }

  if (isset($filters['in_stock']) && $filters['in_stock']) {
    $where[] = "stock > 0";
  }

  $whereClause = implode(' AND ', $where);

  $result = fetchOne("
    SELECT COUNT(*) as count FROM products
    WHERE $whereClause
  ");

  return $result ? intval($result['count']) : 0;
}

function getProductCategories()
{
  $categories = fetchAll("
    SELECT DISTINCT category 
    FROM products 
    WHERE category IS NOT NULL AND category != ''
    ORDER BY category ASC
  ");

  return $categories ? array_column($categories, 'category') : [];
}

function isProductAvailable($productId)
{
  $productId = intval($productId);

  $product = fetchOne("
    SELECT stock FROM products
    WHERE id = $productId
  ");

  return $product && $product['stock'] > 0;
}

function updateProductStock($productId, $quantity)
{
  $productId = intval($productId);
  $quantity = intval($quantity);

  $result = mysqli_query($GLOBALS['conn'], "
    UPDATE products 
    SET stock = stock - $quantity
    WHERE id = $productId AND stock >= $quantity
  ");

  return $result && mysqli_affected_rows($GLOBALS['conn']) > 0;
}
