<?php
require_once __DIR__ . '/models/Cart.php';
session_start();

$cartItems = getCartItemsWithDetails();
$totals = getCartTotals();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Cart | Sahara</title>
  <link rel="icon" href="views/assets/favicon.ico" />
  <link rel="stylesheet" href="views/css/main.css" />
</head>

<body>
  <?php include __DIR__ . '/views/partials/header.php'; ?>

  <main class="cart-page">
    <div class="cart-container">
      <h1>Shopping Cart</h1>

      <?php if (empty($cartItems)): ?>
        <div class="empty-cart">
          <span class="material-symbols-outlined">shopping_cart</span>
          <h2>Your cart is empty</h2>
          <p>Add some products to get started!</p>
          <a href="/shop.php" class="btn-primary">Continue Shopping</a>
        </div>
      <?php else: ?>
        <div class="cart-content">
          <div class="cart-items">
            <?php foreach ($cartItems as $item): ?>
              <div class="cart-item" data-product-id="<?php echo $item['id']; ?>">
                <div class="item-image">
                  <img src="<?php echo $item['image'] ?: '/views/assets/product_placeholder.svg'; ?>"
                    alt="<?php echo $item['title']; ?>" />
                </div>

                <div class="item-details">
                  <h3><?php echo $item['title']; ?></h3>
                  <p class="item-description"><?php echo $item['description']; ?></p>
                  <p class="item-stock">
                    <?php if ($item['stock'] > 0): ?>
                      <span class="in-stock">In Stock: <?php echo $item['stock']; ?> available</span>
                    <?php else: ?>
                      <span class="out-of-stock">Out of Stock</span>
                    <?php endif; ?>
                  </p>
                </div>

                <div class="item-price">
                  <span class="price">৳<?php echo number_format($item['price'], 2); ?></span>
                </div>

                <div class="item-quantity">
                  <button class="qty-btn qty-decrease" data-product-id="<?php echo $item['id']; ?>"
                    aria-label="Decrease quantity">
                    <span class="material-symbols-outlined">remove</span>
                  </button>
                  <input type="number" class="qty-input" value="<?php echo $item['quantity']; ?>" min="1"
                    max="<?php echo $item['stock']; ?>" data-product-id="<?php echo $item['id']; ?>" />
                  <button class="qty-btn qty-increase" data-product-id="<?php echo $item['id']; ?>"
                    aria-label="Increase quantity">
                    <span class="material-symbols-outlined">add</span>
                  </button>
                </div>

                <div class="item-subtotal">
                  <span class="subtotal">৳<?php echo number_format($item['subtotal'], 2); ?></span>
                </div>

                <button class="item-remove" data-product-id="<?php echo $item['id']; ?>" aria-label="Remove item">
                  <span class="material-symbols-outlined">delete</span>
                </button>
              </div>
            <?php endforeach; ?>
          </div>

          <div class="cart-summary">
            <h2>Order Summary</h2>

            <div class="summary-row">
              <span>Subtotal</span>
              <span class="summary-subtotal">৳<?php echo number_format($totals['subtotal'], 2); ?></span>
            </div>

            <div class="summary-row">
              <span>Shipping</span>
              <span class="summary-shipping">৳<?php echo number_format($totals['shipping'], 2); ?></span>
            </div>

            <div class="summary-row">
              <span>Tax (5%)</span>
              <span class="summary-tax">৳<?php echo number_format($totals['tax'], 2); ?></span>
            </div>

            <div class="summary-divider"></div>

            <div class="summary-row summary-total">
              <span>Total</span>
              <span class="summary-total-amount">৳<?php echo number_format($totals['total'], 2); ?></span>
            </div>

            <button class="btn-primary btn-checkout">
              Proceed to Checkout
            </button>

            <button class="btn-secondary btn-continue">
              <a href="/shop.php">Continue Shopping</a>
            </button>

            <button class="btn-clear-cart">Clear Cart</button>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </main>

  <?php include __DIR__ . '/views/partials/footer.html'; ?>

  <script type="module" src="/views/js/cart-page.js"></script>
</body>

</html>
