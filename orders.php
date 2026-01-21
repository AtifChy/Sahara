<?php
require_once __DIR__ . '/models/Auth.php';
require_once __DIR__ . '/models/Order.php';

requireAuth('/auth.php?page=login');

$userId = $_SESSION['user_id'];
$orders = getOrdersByUser($userId);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>My Orders | Sahara</title>
  <link rel="icon" href="views/assets/favicon.ico" />
  <link rel="stylesheet" href="views/css/main.css" />
  <link rel="stylesheet" href="views/css/checkout.css" />
</head>

<body>
  <?php include __DIR__ . '/views/partials/header.php'; ?>

  <main class="orders-page">
    <h1>My Orders</h1>

    <?php if (empty($orders)): ?>
      <div class="empty-orders">
        <span class="material-symbols-outlined">receipt_long</span>
        <h2>No orders yet</h2>
        <p>You haven't placed any orders yet. Start shopping to see your orders here!</p>
        <a href="/shop.php" class="btn btn-primary btn-continue">Start Shopping</a>
      </div>
    <?php else: ?>
      <div class="orders-list">
        <?php foreach ($orders as $order): ?>
          <?php
          $orderDetails = getOrderDetails($order['id'], $userId);
          $itemCount = count($orderDetails['items']);
          ?>

          <div class="order-card">
            <div class="order-header">
              <div class="order-info">
                <div class="order-info-item">
                  <span class="order-label">Order Number</span>
                  <span class="order-value order-number">
                    #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?>
                  </span>
                </div>

                <div class="order-info-item">
                  <span class="order-label">Date</span>
                  <span class="order-value"><?php echo date('M j, Y', strtotime($order['created_at'])); ?></span>
                </div>

                <div class="order-info-item">
                  <span class="order-label">Items</span>
                  <span class="order-value"><?php echo $itemCount; ?> item<?php echo $itemCount > 1 ? 's' : ''; ?></span>
                </div>
              </div>

              <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                <?php echo $order['status']; ?>
              </span>
            </div>

            <div class="order-body">
              <div class="order-items">
                <?php foreach ($orderDetails['items'] as $item): ?>
                  <div class="order-item">
                    <img src="<?php echo $item['image'] ?: '/views/assets/product_placeholder.svg'; ?>"
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
            </div>

            <div class="order-footer">
              <div class="order-total">
                Total: ৳<?php echo number_format($order['total'], 2); ?>
              </div>

              <div class="order-actions">
                <a href="/order-details.php?id=<?php echo $order['id']; ?>" class="btn btn-primary">
                  View Details
                </a>
                <?php if ($order['status'] === 'PENDING'): ?>
                  <button class="btn btn-secondary" onclick="cancelOrder(<?php echo $order['id']; ?>)">
                    Cancel Order
                  </button>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </main>

  <?php include __DIR__ . '/views/partials/footer.html'; ?>

  <script>
    function cancelOrder(orderId) {
      if (!confirm('Are you sure you want to cancel this order? Your product stock will be restored.')) {
        return;
      }

      // Send cancellation request
      fetch('/controllers/shop/CancelOrderController.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `order_id=${orderId}`
      })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            alert(data.message);
            // Reload page to show updated status
            window.location.reload();
          } else {
            alert('Error: ' + data.message);
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('Failed to cancel order. Please try again.');
        });
    }
  </script>
</body>

</html>
