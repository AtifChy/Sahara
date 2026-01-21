<?php
require_once __DIR__ . '/models/Auth.php';
require_once __DIR__ . '/models/Cart.php';

requireAuth('/auth.php?page=login');

$cartItems = getCartItemsWithDetails();
$totals = getCartTotals();

if (empty($cartItems)) {
  header('Location: /cart.php');
  exit;
}

$userId = $_SESSION['user_id'];
$userProfile = fetchOne("SELECT * FROM user_profiles WHERE user_id = $userId");
$user = getCurrentUser();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Checkout | Sahara</title>
  <link rel="icon" href="views/assets/favicon.ico" />
  <link rel="stylesheet" href="views/css/main.css" />
  <link rel="stylesheet" href="views/css/checkout.css" />
</head>

<body>
  <?php include __DIR__ . '/views/partials/header.php'; ?>

  <main class="checkout-page">
    <a href="/cart.php" class="back-to-cart">
      <span class="material-symbols-outlined">arrow_back</span>
      Back to Cart
    </a>

    <div class="checkout-container">
      <div class="checkout-form">
        <h2>Checkout</h2>

        <?php if (isset($_SESSION['checkout_error'])): ?>
          <div class="error-message">
            <?php
            echo $_SESSION['checkout_error'];
            unset($_SESSION['checkout_error']);
            ?>
          </div>
        <?php endif; ?>

        <form action="/controllers/shop/CheckoutController.php" method="POST" id="checkout-form">
          <div class="form-section">
            <h3>Contact Information</h3>

            <div class="form-group">
              <label for="email">Email</label>
              <input type="email" id="email" name="email" value="<?php echo $user['email']; ?>" readonly />
            </div>

            <div class="form-group">
              <label for="phone">Phone Number *</label>
              <input type="tel" id="phone" name="phone" value="<?php echo $userProfile['phone'] ?? ''; ?>" required />
            </div>
          </div>

          <div class="form-section">
            <h3>Shipping Address</h3>

            <div class="form-group">
              <label for="full_name">Full Name *</label>
              <input type="text" id="full_name" name="full_name"
                value="<?php echo ($userProfile['first_name'] ?? '') . ' ' . ($userProfile['last_name'] ?? ''); ?>"
                required />
            </div>

            <div class="form-group">
              <label for="address">Shipping Address *</label>
              <textarea id="address" name="address" required
                placeholder="Full address with house/flat no, street, area, city, postal code"><?php echo $userProfile['address'] ?? ''; ?></textarea>
              <small>Please include your complete address</small>
            </div>
          </div>

          <div class="form-section">
            <h3>Payment Method</h3>
            <div class="form-group">
              <label>
                <input type="radio" name="payment_method" value="COD" checked>
                Cash on Delivery (COD)
              </label>
              <label>
                <input type="radio" name="payment_method" value="Online">
                BKash / Rocket / Nagad
              </label>
              <label>
                <input type="radio" name="payment_method" value="Card">
                Credit / Debit Card
              </label>
            </div>
          </div>

          <button type="submit" class="btn-place-order">Place Order</button>
        </form>
      </div>

      <div class="order-summary-box">
        <h2>Order Summary</h2>

        <div class="summary-items">
          <?php foreach ($cartItems as $item): ?>
            <div class="summary-item">
              <img src="<?php echo $item['image'] ?: '/views/assets/product_placeholder.svg'; ?>"
                alt="<?php echo $item['title']; ?>" />
              <div class="summary-item-details">
                <div class="summary-item-title"><?php echo $item['title']; ?></div>
                <div class="summary-item-qty">Qty: <?php echo $item['quantity']; ?></div>
              </div>
              <div class="summary-item-price">৳<?php echo number_format($item['subtotal'], 2); ?></div>
            </div>
          <?php endforeach; ?>
        </div>

        <div class="summary-totals">
          <div class="summary-row">
            <span>Subtotal</span>
            <span>৳<?php echo number_format($totals['subtotal'], 2); ?></span>
          </div>

          <div class="summary-row">
            <span>Shipping</span>
            <span>৳<?php echo number_format($totals['shipping'], 2); ?></span>
          </div>

          <div class="summary-row">
            <span>Tax (5%)</span>
            <span>৳<?php echo number_format($totals['tax'], 2); ?></span>
          </div>

          <div class="summary-row total">
            <span>Total</span>
            <span>৳<?php echo number_format($totals['total'], 2); ?></span>
          </div>
        </div>
      </div>
    </div>
  </main>

  <?php include __DIR__ . '/views/partials/footer.html'; ?>

  <script>
    document.getElementById('checkout-form').addEventListener('submit', function(e) {
      const phone = document.getElementById('phone').value;
      const fullName = document.getElementById('full_name').value;
      const address = document.getElementById('address').value;

      if (!phone || !fullName || !address) {
        alert('Please fill in all required fields');
        e.preventDefault();
        return false;
      }
    });
  </script>
</body>

</html>
