<?php
$seller_id = $_SESSION['user_id'];
$seller_name = $_SESSION['user_fname'] ?? 'Seller';

$productCountResult = fetchOne("SELECT COUNT(*) as count FROM products WHERE seller_id = $seller_id");
$myProductCount = $productCountResult['count'] ?? 0;

$orderCountResult = fetchOne("
  SELECT COUNT(DISTINCT oi.order_id) as count
  FROM order_items oi
  INNER JOIN orders o ON oi.order_id = o.id
  WHERE oi.seller_id = $seller_id AND status != 'CANCELLED'
");
$myOrderCount = $orderCountResult['count'] ?? 0;

$revenueResult = fetchOne("
  SELECT SUM(oi.price * oi.quantity) as revenue
  FROM order_items oi
  INNER JOIN orders o ON oi.order_id = o.id
  WHERE oi.seller_id = $seller_id AND o.status IN ('PAID', 'DELIVERED')
");
$myRevenue = $revenueResult['revenue'] ?? 0;

$pendingOrdersResult = fetchOne("
  SELECT COUNT(DISTINCT oi.order_id) as count
  FROM order_items oi
  INNER JOIN orders o ON oi.order_id = o.id
  WHERE oi.seller_id = $seller_id AND o.status = 'PENDING'
");
$pendingOrders = $pendingOrdersResult['count'] ?? 0;

$recentOrders = fetchAll("
  SELECT o.*,
         CONCAT(up.first_name, ' ', COALESCE(up.last_name, '')) as customer_name,
         u.email as customer_email
  FROM orders o
  JOIN order_items oi ON o.id = oi.order_id
  JOIN users u ON o.user_id = u.id
  LEFT JOIN user_profiles up ON u.id = up.user_id
  WHERE oi.seller_id = $seller_id
  GROUP BY o.id
  ORDER BY o.created_at DESC
  LIMIT 5
");

$lowStockProducts = fetchAll("
  SELECT * FROM products
  WHERE seller_id = $seller_id AND stock < 10 
  ORDER BY stock ASC
  LIMIT 5
");

$topProducts = fetchAll("
  SELECT * FROM products
  WHERE seller_id = $seller_id
  ORDER BY rating DESC
  LIMIT 5
");
?>

<main class="role-content">
  <div class="role-header">
    <h1>Seller Dashboard</h1>
    <p>Welcome back, <?php echo $seller_name; ?>! Here's your store performance overview.</p>
  </div>

  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-card-header">
        <span class="stat-card-title">My Products</span>
        <div class="stat-card-icon green">
          <span class="material-symbols-outlined">inventory_2</span>
        </div>
      </div>
      <h2 class="stat-card-value"><?php echo $myProductCount; ?></h2>
      <div class="stat-card-footer">
        <a href="/seller.php?page=products" class="stat-card-link">Manage Products</a>
      </div>
    </div>

    <div class="stat-card">
      <div class="stat-card-header">
        <span class="stat-card-title">Active Orders</span>
        <div class="stat-card-icon yellow">
          <span class="material-symbols-outlined">local_shipping</span>
        </div>
      </div>
      <h2 class="stat-card-value"><?php echo $myOrderCount; ?></h2>
      <div class="stat-card-footer">
        <a href="/seller.php?page=orders" class="stat-card-link">View Orders</a>
      </div>
    </div>

    <div class="stat-card">
      <div class="stat-card-header">
        <span class="stat-card-title">Pending Orders</span>
        <div class="stat-card-icon red">
          <span class="material-symbols-outlined">schedule</span>
        </div>
      </div>
      <h2 class="stat-card-value"><?php echo $pendingOrders; ?></h2>
      <div class="stat-card-footer">
        <span class="stat-card-note">Awaiting processing</span>
      </div>
    </div>

    <div class="stat-card">
      <div class="stat-card-header">
        <span class="stat-card-title">Total Revenue</span>
        <div class="stat-card-icon blue">
          <span class="material-symbols-outlined">payments</span>
        </div>
      </div>
      <h2 class="stat-card-value">৳<?php echo $myRevenue; ?></h2>
      <div class="stat-card-footer">
        <a href="/seller.php?page=analytics" class="stat-card-link">View Analytics</a>
      </div>
    </div>
  </div>

  <div class="dashboard-grid">
    <div class="section-card">
      <div class="section-card-header">
        <h2 class="section-card-title">
          <span class="material-symbols-outlined">receipt_long</span>
          Recent Orders
        </h2>
        <a href="/seller.php?page=orders" class="section-card-action">View All</a>
      </div>
      <div class="section-card-body">
        <?php if (empty($recentOrders)): ?>
          <div class="empty-state">
            <span class="material-symbols-outlined">receipt_long</span>
            <p>No orders yet</p>
            <small>Orders containing your products will appear here</small>
          </div>
        <?php else: ?>
          <table class="role-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Customer</th>
                <th>Total</th>
                <th>Status</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($recentOrders as $order): ?>
                <tr>
                  <td>#<?php echo $order['id']; ?></td>
                  <td><?php echo $order['customer_name']; ?></td>
                  <td>৳<?php echo number_format($order['total'], 2); ?></td>
                  <td>
                    <span class="badge badge-<?php echo strtolower($order['status']); ?>">
                      <?php echo $order['status']; ?>
                    </span>
                  </td>
                  <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </div>

    <div class="section-card">
      <div class="section-card-header">
        <h2 class="section-card-title">
          <span class="material-symbols-outlined">warning</span>
          Low Stock Alert
        </h2>
        <a href="/seller.php?page=products" class="section-card-action">Manage Stock</a>
      </div>
      <div class="section-card-body">
        <?php if (empty($lowStockProducts)): ?>
          <div class="empty-state">
            <span class="material-symbols-outlined">check_circle</span>
            <p>All products in stock</p>
            <small>Products with low stock will appear here</small>
          </div>
        <?php else: ?>
          <table class="role-table">
            <thead>
              <tr>
                <th>Product</th>
                <th>Category</th>
                <th>Price</th>
                <th>Stock</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($lowStockProducts as $product): ?>
                <tr>
                  <td>
                    <div class="product">
                      <img
                        src="<?php echo !empty($product['image']) ? $product['image'] : '/views/assets/product_placeholder.svg'; ?>"
                        alt="<?php echo $product['title']; ?>">
                      <strong><?php echo $product['title']; ?></strong>
                    </div>
                  </td>
                  <td><span class="badge badge-category"><?php echo $product['category']; ?></span></td>
                  <td><strong>৳<?php echo $product['price']; ?></strong></td>
                  <td>
                    <span>
                      <?php echo $product['stock']; ?> left
                    </span>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </div>

    <div class="section-card">
      <div class="section-card-header">
        <h2 class="section-card-title">
          <span class="material-symbols-outlined">star</span>
          Top Rated Products
        </h2>
        <a href="/seller.php?page=products" class="section-card-action">View All</a>
      </div>
      <div class="section-card-body">
        <?php if (empty($topProducts)): ?>
          <div class="empty-state">
            <span class="material-symbols-outlined">inventory_2</span>
            <p>No products yet</p>
            <small>Add your first product to get started</small>
          </div>
        <?php else: ?>
          <table class="role-table">
            <thead>
              <tr>
                <th>Product</th>
                <th>Category</th>
                <th>Price</th>
                <th>Rating</th>
                <th>Stock</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($topProducts as $product): ?>
                <tr>
                  <td>
                    <div class="product">
                      <img
                        src="<?php echo !empty($product['image']) ? $product['image'] : '/views/assets/product_placeholder.svg'; ?>"
                        alt="<?php echo $product['title']; ?>">
                      <strong><?php echo $product['title']; ?></strong>
                    </div>
                  </td>
                  <td><span class="badge badge-category"><?php echo $product['category']; ?></span></td>
                  <td><strong>৳<?php echo $product['price']; ?></strong></td>
                  <td>
                    <div class="rating">
                      <span class="material-symbols-outlined filled">star</span>
                      <strong><?php echo $product['rating']; ?></strong>
                    </div>
                  </td>
                  <td><span><?php echo $product['stock']; ?> units</span></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </div>
  </div>
</main>
