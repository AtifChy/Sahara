<?php
require_once 'includes/auth.php';
require_once 'includes/order-functions.php';

// Require authentication
requireAuth('/auth/login.php');

// Get order ID from URL
$orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
$userId = $_SESSION['user_id'];

// Validate order ID and ownership
if (!$orderId || !isset($_SESSION['last_order_id']) || $_SESSION['last_order_id'] != $orderId) {
    header('Location: /orders.php');
    exit;
}

// Get order details
$order = getOrderDetails($orderId, $userId);

if (!$order) {
    header('Location: /orders.php');
    exit;
}

// Clear last order ID from session
unset($_SESSION['last_order_id']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Order Confirmation | Sahara</title>
  <link rel="icon" href="assets/favicon.ico" />
  <link rel="stylesheet" href="css/main.css" />
  <link rel="stylesheet" href="css/checkout.css" />
</head>

<body>
  <?php include 'partials/header.php'; ?>

  <main class="confirmation-page">
    <div class="confirmation-card">
      <div class="success-icon">
        <span class="material-symbols-outlined">check_circle</span>
      </div>

      <h1>Order Placed Successfully!</h1>

      <p class="order-number">
        Order Number: <strong>#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></strong>
      </p>

      <p class="confirmation-message">
        Thank you for your order! We've received your order and will process it shortly.
        You will receive a confirmation email at <strong><?php echo $order['email']; ?></strong>.
      </p>

      <div class="order-details">
        <h3>Order Summary</h3>

        <div class="detail-row">
          <span class="detail-label">Order Date</span>
          <span class="detail-value"><?php echo date('F j, Y, g:i a', strtotime($order['created_at'])); ?></span>
        </div>

        <div class="detail-row">
          <span class="detail-label">Status</span>
          <span class="detail-value">
            <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
              <?php echo $order['status']; ?>
            </span>
          </span>
        </div>

        <div class="detail-row">
          <span class="detail-label">Payment Method</span>
          <span class="detail-value">Cash on Delivery</span>
        </div>

        <div class="detail-row">
          <span class="detail-label">Total Amount</span>
          <span class="detail-value" style="font-size: 1.25rem; font-weight: 700; color: #2563eb;">
            ৳<?php echo number_format($order['total'], 2); ?>
          </span>
        </div>
      </div>

      <div class="order-items">
        <h3>Order Items (<?php echo count($order['items']); ?>)</h3>

        <?php foreach ($order['items'] as $item): ?>
          <div class="order-item">
            <img src="<?php echo $item['image'] ?: '/assets/product_placeholder.svg'; ?>"
              alt="<?php echo $item['title']; ?>" />
            <div class="order-item-details">
              <div class="order-item-title"><?php echo $item['title']; ?></div>
              <div class="order-item-qty">Quantity: <?php echo $item['quantity']; ?></div>
            </div>
            <div class="order-item-price">
              ৳<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="shipping-info">
        <h3>Shipping Address</h3>
        <address><?php echo htmlspecialchars($order['shipping_address']); ?></address>
      </div>

      <div class="action-buttons">
        <a href="/orders.php" class="btn btn-primary">View All Orders</a>
        <a href="/shop.php" class="btn btn-secondary">Continue Shopping</a>
      </div>
    </div>
  </main>

  <?php include 'partials/footer.html'; ?>
</body>

</html>
