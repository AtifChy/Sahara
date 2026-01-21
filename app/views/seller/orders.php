<?php
$seller_id = $_SESSION['user_id'];

$status_filter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';
$date_filter = $_GET['date'] ?? '';

$query = "SELECT DISTINCT 
          o.id,
          o.user_id,
          o.total,
          o.status,
          o.shipping_address,
          o.created_at,
          o.updated_at,
          u.email as customer_email,
          CONCAT(up.first_name, ' ', COALESCE(up.last_name, '')) as customer_name,
          up.phone as customer_phone,
          (SELECT SUM(oi.quantity * oi.price) 
           FROM order_items oi 
           WHERE oi.order_id = o.id AND oi.seller_id = {$seller_id}) as seller_total,
          (SELECT COUNT(*) 
           FROM order_items oi 
           WHERE oi.order_id = o.id AND oi.seller_id = {$seller_id}) as seller_items_count
          FROM orders o
          INNER JOIN order_items oi ON o.id = oi.order_id
          LEFT JOIN users u ON o.user_id = u.id
          LEFT JOIN user_profiles up ON u.id = up.user_id
          WHERE oi.seller_id = {$seller_id}";

$conditions = [];

if (!empty($status_filter)) {
  $conditions[] = "o.status = '{$status_filter}'";
}

if (!empty($search)) {
  $conditions[] = "(o.id LIKE '%{$search}%' OR u.email LIKE '%{$search}%' OR up.first_name LIKE '%{$search}%' OR up.last_name LIKE '%{$search}%')";
}

if (!empty($date_filter)) {
  switch ($date_filter) {
    case 'today':
      $conditions[] = "DATE(o.created_at) = CURDATE()";
      break;
    case 'week':
      $conditions[] = "o.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
      break;
    case 'month':
      $conditions[] = "o.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
      break;
  }
}

if (!empty($conditions)) {
  $query .= " AND " . implode(" AND ", $conditions);
}

$query .= " ORDER BY o.created_at DESC";

$orders = fetchAll($query);

$totalOrders = count($orders);
$pendingCount = count(array_filter($orders, fn($o) => $o['status'] === 'PENDING'));
$paidCount = count(array_filter($orders, fn($o) => $o['status'] === 'PAID'));
$deliveredCount = count(array_filter($orders, fn($o) => $o['status'] === 'DELIVERED'));

$totalRevenue = array_sum(array_map(fn($o) => ($o['status'] === 'DELIVERED' || $o['status'] === 'PAID') ? floatval($o['seller_total']) : 0, $orders));
?>

<main class="role-content">

  <div class="role-header">
    <div>
      <h1>My Orders</h1>
      <p>Manage orders containing your products</p>
    </div>
  </div>


  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-card-header">
        <span class="stat-card-title">Total Orders</span>
        <div class="stat-card-icon blue">
          <span class="material-symbols-outlined">receipt_long</span>
        </div>
      </div>
      <h2 class="stat-card-value"><?php echo number_format($totalOrders); ?></h2>
    </div>

    <div class="stat-card">
      <div class="stat-card-header">
        <span class="stat-card-title">Pending</span>
        <div class="stat-card-icon yellow">
          <span class="material-symbols-outlined">schedule</span>
        </div>
      </div>
      <h2 class="stat-card-value"><?php echo number_format($pendingCount); ?></h2>
    </div>

    <div class="stat-card">
      <div class="stat-card-header">
        <span class="stat-card-title">Delivered</span>
        <div class="stat-card-icon green">
          <span class="material-symbols-outlined">check_circle</span>
        </div>
      </div>
      <h2 class="stat-card-value"><?php echo number_format($deliveredCount); ?></h2>
    </div>

    <div class="stat-card">
      <div class="stat-card-header">
        <span class="stat-card-title">Revenue</span>
        <div class="stat-card-icon red">
          <span class="material-symbols-outlined">payments</span>
        </div>
      </div>
      <h2 class="stat-card-value">৳<?php echo number_format($totalRevenue, 2); ?></h2>
    </div>
  </div>


  <div class="section-card">
    <div class="section-card-header">
      <h2 class="section-card-title">
        <span class="material-symbols-outlined">local_shipping</span>
        All Orders
      </h2>
    </div>

    <div class="filters-bar" style="margin-bottom: 0; border-bottom: 1px solid var(--surface0); border-radius: 0;">
      <form method="GET" action="/seller.php" class="filters-form">
        <input type="hidden" name="page" value="orders" />

        <div class="filter-group search-group">
          <span class="material-symbols-outlined">search</span>
          <input class="filter-input search-input" type="text" name="search" placeholder="Search orders..." value="<?php echo htmlspecialchars($search); ?>" />
        </div>

        <select name="status" class="filter-select">
          <option value="">All Status</option>
          <option value="PENDING" <?php echo $status_filter === 'PENDING' ? 'selected' : ''; ?>>Pending</option>
          <option value="PAID" <?php echo $status_filter === 'PAID' ? 'selected' : ''; ?>>Paid</option>
          <option value="DELIVERED" <?php echo $status_filter === 'DELIVERED' ? 'selected' : ''; ?>>Delivered</option>
          <option value="CANCELLED" <?php echo $status_filter === 'CANCELLED' ? 'selected' : ''; ?>>Cancelled</option>
        </select>

        <select name="date" class="filter-select">
          <option value="">All Time</option>
          <option value="today" <?php echo $date_filter === 'today' ? 'selected' : ''; ?>>Today</option>
          <option value="week" <?php echo $date_filter === 'week' ? 'selected' : ''; ?>>Last 7 Days</option>
          <option value="month" <?php echo $date_filter === 'month' ? 'selected' : ''; ?>>Last 30 Days</option>
        </select>

        <button type="submit" class="btn btn-ghost">
          <span class="material-symbols-outlined">filter_list</span>
          Apply Filters
        </button>

        <?php if (!empty($search) || !empty($status_filter) || !empty($date_filter)): ?>
          <a href="/seller.php?page=orders" class="btn btn-ghost">
            <span class="material-symbols-outlined">clear</span>
            Clear
          </a>
        <?php endif; ?>
      </form>
    </div>

    <div class="section-card-body">
      <?php if (empty($orders)): ?>
        <div class="empty-state">
          <span class="material-symbols-outlined">local_shipping</span>
          <p>No orders found</p>
          <?php if (!empty($search) || !empty($status_filter) || !empty($date_filter)): ?>
            <small>Try adjusting your filters</small>
          <?php else: ?>
            <small>Orders containing your products will appear here</small>
          <?php endif; ?>
        </div>
      <?php else: ?>
        <table class="role-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Customer</th>
              <th>Your Items</th>
              <th>Your Total</th>
              <th>Status</th>
              <th>Date</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($orders as $order): ?>
              <tr>
                <td>
                  <strong>#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></strong>
                </td>
                <td>
                  <div class="user-info">
                    <div class="user-avatar">
                      <?php echo strtoupper(substr($order['customer_name'] ?: 'U', 0, 1)); ?>
                    </div>
                    <div class="user-details">
                      <div class="user-name"><?php echo $order['customer_name'] ?: 'N/A'; ?></div>
                      <div class="user-email"><?php echo $order['customer_email']; ?></div>
                    </div>
                  </div>
                </td>
                <td><?php echo $order['seller_items_count']; ?> item<?php echo $order['seller_items_count'] > 1 ? 's' : ''; ?></td>
                <td><strong>৳<?php echo number_format($order['seller_total'], 2); ?></strong></td>
                <td>
                  <span class="badge <?php echo strtolower($order['status']); ?>">
                    <?php echo ucfirst(strtolower($order['status'])); ?>
                  </span>
                </td>
                <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                <td>
                  <div class="table-actions">
                    <button class="table-btn view" title="View Details" onclick='viewOrder(<?php echo json_encode($order); ?>, <?php echo $order['id']; ?>)'>
                      <span class="material-symbols-outlined">visibility</span>
                    </button>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>


        <div class="table-footer">
          <p>Showing <?php echo count($orders); ?> order<?php echo count($orders) != 1 ? 's' : ''; ?></p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</main>


<div id="viewOrderModal" class="modal">
  <div class="modal-content modal-large">
    <div class="modal-header">
      <h2>Order Details</h2>
      <button class="modal-close" onclick="closeModal('viewOrderModal')">&times;</button>
    </div>
    <div class="modal-body" id="orderDetailsContent">

    </div>
  </div>
</div>

<style>
  .modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.6);
    animation: fadeIn 0.2s ease;
  }

  .modal.show {
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .modal-content {
    background: var(--mantle);
    border-radius: 12px;
    max-width: 700px;
    width: 90%;
    max-height: 85vh;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    border: 1px solid var(--surface0);
    animation: slideUp 0.3s ease;
  }

  .modal-content.modal-large {
    max-width: 900px;
  }

  .modal-header {
    padding: 24px;
    border-bottom: 1px solid var(--surface0);
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .modal-header h2 {
    margin: 0;
    font-size: 20px;
    color: var(--text);
  }

  .modal-close {
    background: none;
    border: none;
    font-size: 32px;
    color: var(--subtext0);
    cursor: pointer;
    line-height: 1;
    padding: 0;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    transition: all 0.2s;
  }

  .modal-close:hover {
    background: var(--surface0);
    color: var(--text);
  }

  .modal-body {
    padding: 24px;
    overflow-y: auto;
    flex: 1;
  }

  .order-detail-section {
    margin-bottom: 24px;
    padding-bottom: 24px;
    border-bottom: 1px solid var(--surface0);
  }

  .order-detail-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
  }

  .order-detail-section h3 {
    font-size: 16px;
    font-weight: 600;
    color: var(--text);
    margin-bottom: 16px;
  }

  .order-detail-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
  }

  .order-detail-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
  }

  .order-detail-label {
    font-size: 12px;
    color: var(--subtext0);
    text-transform: uppercase;
    font-weight: 600;
    letter-spacing: 0.5px;
  }

  .order-detail-value {
    font-size: 14px;
    color: var(--text);
  }

  .order-items-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
  }

  .order-item-card {
    display: flex;
    gap: 12px;
    padding: 12px;
    background: var(--base);
    border-radius: 8px;
    border: 1px solid var(--surface0);
  }

  .order-item-image {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 6px;
    background: var(--crust);
  }

  .order-item-details {
    flex: 1;
  }

  .order-item-title {
    font-weight: 600;
    color: var(--text);
    margin-bottom: 4px;
  }

  .order-item-meta {
    font-size: 12px;
    color: var(--subtext0);
  }

  .order-item-price {
    text-align: right;
  }

  .order-item-price strong {
    color: var(--blue);
    font-size: 16px;
  }

  .shipping-address {
    background: var(--base);
    padding: 12px;
    border-radius: 8px;
    white-space: pre-line;
    font-size: 14px;
    color: var(--text);
    line-height: 1.6;
  }

  @keyframes fadeIn {
    from {
      opacity: 0;
    }

    to {
      opacity: 1;
    }
  }

  @keyframes slideUp {
    from {
      transform: translateY(20px);
      opacity: 0;
    }

    to {
      transform: translateY(0);
      opacity: 1;
    }
  }

  @media (max-width: 768px) {
    .order-detail-grid {
      grid-template-columns: 1fr;
    }
  }
</style>

<script>
  function viewOrder(order, orderId) {
    // Fetch order items for this seller
    fetch(`/seller/get-order-items.php?order_id=${orderId}`)
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          displayOrderDetails(order, data.items);
        } else {
          alert('Failed to load order details');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Failed to load order details');
      });
  }

  function displayOrderDetails(order, items) {
    const itemsHtml = items.map(item => `
      <div class="order-item-card">
        <img src="${item.image || '/assets/product_placeholder.svg'}" 
             alt="${item.title}" 
             class="order-item-image">
        <div class="order-item-details">
          <div class="order-item-title">${item.title}</div>
          <div class="order-item-meta">Quantity: ${item.quantity} × ৳${parseFloat(item.price).toFixed(2)}</div>
        </div>
        <div class="order-item-price">
          <strong>৳${(parseFloat(item.price) * parseInt(item.quantity)).toFixed(2)}</strong>
        </div>
      </div>
    `).join('');

    const content = `
      <div class="order-detail-section">
        <h3>Order Information</h3>
        <div class="order-detail-grid">
          <div class="order-detail-item">
            <span class="order-detail-label">ID</span>
            <span class="order-detail-value"><strong>#${String(order.id).padStart(6, '0')}</strong></span>
          </div>
          <div class="order-detail-item">
            <span class="order-detail-label">Status</span>
            <span class="order-detail-value">
              <span class="badge ${order.status.toLowerCase()}">${order.status}</span>
            </span>
          </div>
          <div class="order-detail-item">
            <span class="order-detail-label">Order Date</span>
            <span class="order-detail-value">${new Date(order.created_at).toLocaleString()}</span>
          </div>
          <div class="order-detail-item">
            <span class="order-detail-label">Your Total</span>
            <span class="order-detail-value" style="font-size: 18px; color: var(--blue); font-weight: 700;">৳${parseFloat(order.seller_total).toFixed(2)}</span>
          </div>
        </div>
      </div>

      <div class="order-detail-section">
        <h3>Customer Information</h3>
        <div class="order-detail-grid">
          <div class="order-detail-item">
            <span class="order-detail-label">Name</span>
            <span class="order-detail-value">${order.customer_name || 'N/A'}</span>
          </div>
          <div class="order-detail-item">
            <span class="order-detail-label">Email</span>
            <span class="order-detail-value">${order.customer_email}</span>
          </div>
          <div class="order-detail-item">
            <span class="order-detail-label">Phone</span>
            <span class="order-detail-value">${order.customer_phone || 'N/A'}</span>
          </div>
        </div>
      </div>

      <div class="order-detail-section">
        <h3>Your Items (${items.length})</h3>
        <div class="order-items-list">
          ${itemsHtml}
        </div>
      </div>

      <div class="order-detail-section">
        <h3>Shipping Address</h3>
        <div class="shipping-address">${order.shipping_address}</div>
      </div>
    `;

    document.getElementById('orderDetailsContent').innerHTML = content;
    openModal('viewOrderModal');
  }

  function openModal(modalId) {
    document.getElementById(modalId).classList.add('show');
  }

  function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('show');
  }

  // Close modal when clicking outside
  window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
      event.target.classList.remove('show');
    }
  }
</script>
