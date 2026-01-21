<?php
require_once __DIR__ . '/../../models/Auth.php';
require_once __DIR__ . '/../../config/database.php';

requireAuth('../auth/login.php');
requireRole('ADMIN');

// Summary metrics
$totalRevenueRow = fetchOne("SELECT COALESCE(SUM(total),0) as rev FROM orders WHERE status IN ('PAID','DELIVERED')");
$totalRevenue = $totalRevenueRow['rev'] ?? 0;
$ordersCountRow = fetchOne("SELECT COUNT(*) as c FROM orders");
$ordersCount = $ordersCountRow['c'] ?? 0;
$pendingCountRow = fetchOne("SELECT COUNT(*) as c FROM orders WHERE status = 'PENDING'");
$pendingCount = $pendingCountRow['c'] ?? 0;
$customersRow = fetchOne("SELECT COUNT(*) as c FROM users");
$customersCount = $customersRow['c'] ?? 0;

// Top products by quantity sold
$topProducts = fetchAll(
    "SELECT pr.id, pr.title, COALESCE(SUM(oi.quantity),0) as qty, COALESCE(SUM(oi.price*oi.quantity),0) as revenue
     FROM order_items oi
     JOIN products pr ON oi.product_id = pr.id
     GROUP BY pr.id
     ORDER BY qty DESC
     LIMIT 6"
);

// Recent orders
$recentOrders = fetchAll(
    "SELECT o.id, o.total, o.status, o.created_at, u.email, CONCAT(up.first_name, ' ', COALESCE(up.last_name,'')) AS name
     FROM orders o
     JOIN users u ON o.user_id = u.id
     LEFT JOIN user_profiles up ON u.id = up.user_id
     ORDER BY o.created_at DESC
     LIMIT 8"
);

?>

<main class="role-content">
  <div class="role-header">
    <h1>Reports</h1>
    <p>Sales overview and recent activity</p>
  </div>

  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-card-header">
        <span class="stat-card-title">Total Revenue</span>
        <div class="stat-card-icon green"><span class="material-symbols-outlined">payments</span></div>
      </div>
      <h2 class="stat-card-value">৳<?php echo number_format($totalRevenue,2); ?></h2>
      <div class="stat-card-note">Revenue (paid & delivered)</div>
    </div>

    <div class="stat-card">
      <div class="stat-card-header">
        <span class="stat-card-title">Orders</span>
        <div class="stat-card-icon yellow"><span class="material-symbols-outlined">receipt_long</span></div>
      </div>
      <h2 class="stat-card-value"><?php echo number_format($ordersCount); ?></h2>
      <div class="stat-card-note"><?php echo intval($pendingCount); ?> pending</div>
    </div>

    <div class="stat-card">
      <div class="stat-card-header">
        <span class="stat-card-title">Customers</span>
        <div class="stat-card-icon blue"><span class="material-symbols-outlined">group</span></div>
      </div>
      <h2 class="stat-card-value"><?php echo number_format($customersCount); ?></h2>
      <div class="stat-card-note">Registered users</div>
    </div>
  </div>

  <div class="dashboard-grid">
    <div class="section-card">
      <div class="section-card-header">
        <h2 class="section-card-title"><span class="material-symbols-outlined">star</span> Top Products</h2>
        <a class="section-card-action" href="/admin.php?page=products">Manage</a>
      </div>
      <div class="section-card-body">
        <?php if (empty($topProducts)): ?>
          <div class="empty-state"><p>No sales data yet.</p></div>
        <?php else: ?>
          <table class="role-table">
            <thead>
              <tr><th>Product</th><th>Qty Sold</th><th>Revenue</th></tr>
            </thead>
            <tbody>
              <?php foreach ($topProducts as $tp): ?>
                <tr>
                  <td><?php echo htmlspecialchars($tp['title']); ?></td>
                  <td><?php echo intval($tp['qty']); ?></td>
                  <td>৳<?php echo number_format($tp['revenue'],2); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </div>

    <div class="section-card">
      <div class="section-card-header">
        <h2 class="section-card-title"><span class="material-symbols-outlined">receipt_long</span> Recent Orders</h2>
        <a class="section-card-action" href="/admin.php?page=orders">All Orders</a>
      </div>
      <div class="section-card-body">
        <?php if (empty($recentOrders)): ?>
          <div class="empty-state"><p>No recent orders.</p></div>
        <?php else: ?>
          <table class="role-table">
            <thead>
              <tr><th>Order</th><th>Customer</th><th>Total</th><th>Status</th><th>Date</th></tr>
            </thead>
            <tbody>
              <?php foreach ($recentOrders as $o): ?>
                <tr>
                  <td>#<?php echo intval($o['id']); ?></td>
                  <td><?php echo htmlspecialchars($o['name'] ?: $o['email']); ?></td>
                  <td>৳<?php echo number_format($o['total'],2); ?></td>
                  <td><span class="badge <?php echo strtolower($o['status']); ?>"><?php echo htmlspecialchars($o['status']); ?></span></td>
                  <td><?php echo htmlspecialchars(date('M d, Y', strtotime($o['created_at']))); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </div>
  </div>
</main>
