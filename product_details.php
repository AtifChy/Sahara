<?php
require_once __DIR__ . '/app/config/database.php';

// Get product ID from URL
$product_id = $_GET['id'] ?? '';

if (empty($product_id) || !is_numeric($product_id)) {
  header('Location: /shop.php');
  exit;
}

$product_id = intval($product_id);

// Fetch product with seller info
$product = fetchOne("
  SELECT p.*, 
         CONCAT(up.first_name, ' ', COALESCE(up.last_name, '')) as seller_name
  FROM products p
  LEFT JOIN users u ON p.seller_id = u.id
  LEFT JOIN user_profiles up ON u.id = up.user_id
  WHERE p.id = $product_id
");

if (!$product) {
  header('Location: /shop.php?error=not_found');
  exit;
}

// Fetch related products (same category)
$relatedProducts = fetchAll("
  SELECT * FROM products
  WHERE category = '{$product['category']}'
    AND id != $product_id
    AND stock > 0
  ORDER BY rating DESC, created_at DESC
  LIMIT 4
");

// Helper function for stars
function renderStarsHTML($rating)
{
  $output = '';
  $fullStars = floor($rating);
  $halfStar = ($rating - $fullStars) >= 0.5;

  for ($i = 0; $i < 5; $i++) {
    if ($i < $fullStars) {
      $output .= '<span class="star full">★</span>';
    } elseif ($i == $fullStars && $halfStar) {
      $output .= '<span class="star half">★</span>';
    } else {
      $output .= '<span class="star empty">★</span>';
    }
  }

  return $output;
}
?>

<!DOCTYPE HTML>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo $product['title']; ?> | Sahara</title>
  <link rel="icon" href="assets/favicon.ico">
  <link rel="stylesheet" href="css/detail.css" />
  <script type="module" src="js/product-detail.js"></script>
</head>

<body>
  <?php include __DIR__ . '/app/views/partials/header.php'; ?>

  <main class="product-detail-page">
    <nav class="breadcrumb">
      <a href="/">Home</a>
      <span>/</span>
      <a href="/shop.php">Shop</a>
      <span>/</span>
      <span><?php echo $product['title']; ?></span>
    </nav>

    <section class="product-detail">
      <div class="product-image-section">
        <div class="product-main-image">
          <?php if ($product['is_new']): ?>
            <span class="new-badge">New</span>
          <?php endif; ?>
          <img
            src="<?php echo !empty($product['image']) ? $product['image'] : '/assets/product_placeholder.svg'; ?>"
            alt="<?php echo $product['title']; ?>" />
        </div>
      </div>

      <div class="product-info-section">
        <div class="product-meta">
          <span class="category-badge"><?php echo $product['category']; ?></span>
          <?php if ($product['stock'] > 0): ?>
            <span class="stock-badge in-stock">In Stock</span>
          <?php else: ?>
            <span class="stock-badge out-of-stock">Out of Stock</span>
          <?php endif; ?>
        </div>

        <!-- Product Title -->
        <h1 class="product-title"><?php echo $product['title']; ?></h1>

        <!-- Rating -->
        <div class="product-rating-section">
          <div class="rating-stars">
            <?php echo renderStarsHTML($product['rating']); ?>
          </div>
          <span class="rating-value"><?php echo $product['rating']; ?></span>
        </div>

        <div class="product-description">
          <p><?php echo $product['description']; ?></p>
        </div>

        <div class="product-price-section">
          <span class="product-price">৳<?php echo number_format($product['price'], 2); ?></span>
        </div>

        <?php if ($product['stock'] > 0 && $product['stock'] < 10): ?>
          <div class="stock-warning">
            <span class="material-symbols-outlined">warning</span>
            Only <?php echo $product['stock']; ?> units left!
          </div>
        <?php endif; ?>

        <div class="product-actions">
          <?php if ($product['stock'] > 0): ?>
            <div class="quantity-selector">
              <button class="qty-btn" id="qty-decrease" aria-label="Decrease quantity">
                <span class="material-symbols-outlined">remove</span>
              </button>
              <input
                type="number"
                id="quantity-input"
                value="1"
                min="1"
                max="<?php echo $product['stock']; ?>"
                aria-label="Quantity" />
              <button class="qty-btn" id="qty-increase" aria-label="Increase quantity">
                <span class="material-symbols-outlined">add</span>
              </button>
            </div>

            <button class="add-to-cart-btn" data-product-id="<?php echo $product['id']; ?>">
              <span class="material-symbols-outlined">shopping_cart</span>
              Add to Cart
            </button>

            <button class="wishlist-btn" aria-label="Add to wishlist">
              <span class="material-symbols-outlined">favorite_border</span>
            </button>
          <?php else: ?>
            <button class="disabled-btn" disabled>Out of Stock</button>
          <?php endif; ?>
        </div>

        <!-- Seller Info -->
        <div class="seller-info">
          <div class="seller-details">
            <span class="material-symbols-outlined">storefront</span>
            <div>
              <strong>Sold by:</strong>
              <span><?php echo $product['seller_name']; ?></span>
            </div>
          </div>
        </div>

        <!-- Product Details -->
        <div class="product-details-box">
          <h3>Product Details</h3>
          <div class="detail-row">
            <span class="detail-label">Product ID:</span>
            <span class="detail-value">#<?php echo str_pad($product['id'], 5, '0', STR_PAD_LEFT); ?></span>
          </div>
          <div class="detail-row">
            <span class="detail-label">Category:</span>
            <span class="detail-value"><?php echo $product['category']; ?></span>
          </div>
          <div class="detail-row">
            <span class="detail-label">Availability:</span>
            <span class="detail-value"><?php echo $product['stock']; ?> units</span>
          </div>
        </div>
      </div>
    </section>

    <!-- Related Products Section -->
    <?php if (!empty($relatedProducts)): ?>
      <section class="related-products">
        <h2>You May Also Like</h2>
        <div class="product-grid">
          <?php foreach ($relatedProducts as $relProduct): ?>
            <div class="product-card" onclick="window.location.href='/product.php?id=<?php echo $relProduct['id']; ?>'">
              <?php if ($relProduct['is_new']): ?>
                <span class="new-badge">New</span>
              <?php endif; ?>

              <button class="wishlist" aria-label="Add to wishlist" onclick="event.stopPropagation();">
                <span class="material-symbols-outlined">favorite_border</span>
              </button>

              <div class="product-image">
                <img src="<?php echo $relProduct['image'] ?: '/assets/placeholder.png'; ?>" alt="<?php echo $relProduct['title']; ?>" />
              </div>

              <h3><?php echo $relProduct['title']; ?></h3>
              <p class="desc"><?php echo $relProduct['description']; ?></p>

              <div class="product-rating">
                <div class="rating-stars" aria-hidden="true">
                  <?php echo renderStarsHTML($relProduct['rating']); ?>
                </div>
                <span class="rating-value"><?php echo $relProduct['rating']; ?></span>
              </div>

              <div class="product-info">
                <span class="price">৳<?php echo $relProduct['price']; ?></span>
                <button class="add-to-cart" aria-label="Add to cart" onclick="event.stopPropagation();">Add to cart</button>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>
  </main>

  <?php include __DIR__ . '/app/views/partials/footer.html'; ?>
</body>

</html>
