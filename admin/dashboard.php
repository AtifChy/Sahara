<main class="role-content">
  <div class="role-header">
    <h1>Dashboard Overview</h1>
    <p>Welcome back, <?php echo $_SESSION['user_fname']; ?>! Here's what's happening with your store today.</p>
  </div>

  <div class="stats-grid">
    <!-- Total Users -->
    <div class="stat-card">
      <div class="stat-card-header">
        <span class="stat-card-title">Total Users</span>
        <div class="stat-card-icon blue">
          <span class="material-symbols-outlined">group</span>
        </div>
      </div>
      <h2 class="stat-card-value"><?php echo number_format($userCount); ?></h2>
      <div class="stat-card-change positive">
        <span class="material-symbols-outlined">trending_up</span>
        <span>+12% from last month</span>
      </div>
    </div>

    <!-- Total Products -->
    <div class="stat-card">
      <div class="stat-card-header">
        <span class="stat-card-title">Total Products</span>
        <div class="stat-card-icon green">
          <span class="material-symbols-outlined">inventory_2</span>
        </div>
      </div>
      <h2 class="stat-card-value"><?php echo number_format($productCount); ?></h2>
      <div class="stat-card-change positive">
        <span class="material-symbols-outlined">trending_up</span>
        <span>+8% from last month</span>
      </div>
    </div>

    <!-- Total Orders -->
    <div class="stat-card">
      <div class="stat-card-header">
        <span class="stat-card-title">Total Orders</span>
        <div class="stat-card-icon yellow">
          <span class="material-symbols-outlined">receipt_long</span>
        </div>
      </div>
      <h2 class="stat-card-value"><?php echo number_format($orderCount); ?></h2>
      <div class="stat-card-change positive">
        <span class="material-symbols-outlined">trending_up</span>
        <span>+23% from last month</span>
      </div>
    </div>

    <!-- Total Revenue -->
    <div class="stat-card">
      <div class="stat-card-header">
        <span class="stat-card-title">Total Revenue</span>
        <div class="stat-card-icon red">
          <span class="material-symbols-outlined">payments</span>
        </div>
      </div>
      <h2 class="stat-card-value">$<?php echo number_format($totalRevenue, 2); ?></h2>
      <div class="stat-card-change positive">
        <span class="material-symbols-outlined">trending_up</span>
        <span>+18% from last month</span>
      </div>
    </div>
  </div>

  <!-- Dashboard Grid -->
  <div class="dashboard-grid">
    <!-- Recent Orders -->
    <div class="section-card">
      <div class="section-card-header">
        <h2 class="section-card-title">
          <span class="material-symbols-outlined">receipt_long</span>
          Recent Orders
        </h2>
        <a href="/admin/orders.php" class="section-card-action">View All</a>
      </div>
      <div class="section-card-body">
        <?php if (empty($recentOrders)): ?>
          <div class="empty-state">
            <span class="material-symbols-outlined">receipt_long</span>
            <p>No orders yet</p>
          </div>
        <?php else: ?>
          <table class="role-table">
            <thead>
              <tr>
                <th>Order ID</th>
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
                  <td>
                    <div class="user-info">
                      <div class="user-avatar">
                        <?php echo strtoupper(substr($order['customer_name'], 0, 1)); ?>
                      </div>
                      <div class="user-details">
                        <div class="user-name"><?php echo $order['customer_name']; ?></div>
                        <div class="user-email"><?php echo $order['customer_email']; ?></div>
                      </div>
                    </div>
                  </td>
                  <td>$<?php echo number_format($order['total'], 2); ?></td>
                  <td>
                    <span class="badge <?php echo $order['status']; ?>">
                      <?php echo ucfirst($order['status']); ?>
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

    <!-- Low Stock Alert -->
    <div class="section-card">
      <div class="section-card-header">
        <h2 class="section-card-title">
          <span class="material-symbols-outlined">warning</span>
          Low Stock Alert
        </h2>
        <a href="/admin/products.php" class="section-card-action">View All</a>
      </div>
      <div class="section-card-body">
        <?php if (empty($lowStockProducts)): ?>
          <div class="empty-state">
            <span class="material-symbols-outlined">inventory_2</span>
            <p>All products are well stocked</p>
          </div>
        <?php else: ?>
          <table class="role-table">
            <thead>
              <tr>
                <th>Product</th>
                <th>Category</th>
                <th>Stock</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($lowStockProducts as $product): ?>
                <tr>
                  <td>
                    <div class="product">
                      <img src="<?php echo !empty($product['image']) ? $product['image'] : '/assets/product_placeholder.svg'; ?>" alt="" class="product-image">
                      <div class="product-title"><?php echo $product['title']; ?></div>
                    </div>
                  </td>
                  <td>
                    <span class="badge badge-category"><?php echo ucfirst($product['category']); ?></span>
                  </td>
                  <td>
                    <span style="color: var(--red); font-weight: 600;">
                      <?php echo $product['stock']; ?> left
                    </span>
                  </td>
                  <td>
                    <div class="table-actions">
                      <button class="table-btn edit">
                        <span class="material-symbols-outlined">edit</span>
                      </button>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Recent Users -->
  <div class="section-card">
    <div class="section-card-header">
      <h2 class="section-card-title">
        <span class="material-symbols-outlined">person_add</span>
        Recent Users
      </h2>
      <a href="/admin/users.php" class="section-card-action">View All</a>
    </div>
    <div class="section-card-body">
      <?php if (empty($recentUsers)): ?>
        <div class="empty-state">
          <span class="material-symbols-outlined">group</span>
          <p>No users yet</p>
        </div>
      <?php else: ?>
        <table class="role-table">
          <thead>
            <tr>
              <th>User</th>
              <th>Phone</th>
              <th>Role</th>
              <th>Status</th>
              <th>Joined</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($recentUsers as $user): ?>
              <tr>
                <td>
                  <div class="user-info">
                    <div class="user-avatar">
                      <?php echo strtoupper(substr($user['first_name'] ?? 'U', 0, 1)); ?>
                    </div>
                    <div class="user-details">
                      <div class="user-name"><?php echo $user['first_name'] ?? 'N/A'; ?></div>
                      <div class="user-email"><?php echo $user['email']; ?></div>
                    </div>
                  </div>
                </td>
                <td><?php echo $user['phone'] ?? 'N/A'; ?></td>
                <td>
                  <span class="badge <?php echo strtolower($user['role']); ?>">
                    <?php echo ucfirst(strtolower($user['role'])); ?>
                  </span>
                </td>
                <td>
                  <span class="badge <?php echo $user['is_active'] ? 'active' : 'inactive'; ?>">
                    <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                  </span>
                </td>
                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                <td>
                  <div class="table-actions">
                    <button class="table-btn view">
                      <span class="material-symbols-outlined">visibility</span>
                    </button>
                    <button class="table-btn edit">
                      <span class="material-symbols-outlined">edit</span>
                    </button>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </div>
</main>
