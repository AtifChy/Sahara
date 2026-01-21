<?php
$searchQuery = $_GET['search'] ?? '';
$statusFilter = $_GET['status'] ?? '';
$dateFilter = $_GET['date'] ?? '';

$sql = "
  SELECT o.*, 
    u.email as customer_email,
    CONCAT(up.first_name, ' ', COALESCE(up.last_name, '')) as customer_name,
    up.phone as customer_phone,
    (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as items_count,
    (SELECT COUNT(DISTINCT seller_id) FROM order_items WHERE order_id = o.id) as sellers_count
  FROM orders o
  LEFT JOIN users u ON o.user_id = u.id
  LEFT JOIN user_profiles up ON u.id = up.user_id
  WHERE 1=1
";

// Apply filters
if (!empty($searchQuery)) {
  $sql .= " AND (o.id LIKE '%$searchQuery%' OR u.email LIKE '%$searchQuery%' OR up.first_name LIKE '%$searchQuery%' OR up.last_name LIKE '%$searchQuery%')";
}

if (!empty($statusFilter)) {
  $sql .= " AND o.status = '$statusFilter'";
}

if (!empty($dateFilter)) {
  switch ($dateFilter) {
    case 'today':
      $sql .= " AND DATE(o.created_at) = CURDATE()";
      break;
    case 'week':
      $sql .= " AND o.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
      break;
    case 'month':
      $sql .= " AND o.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
      break;
  }
}

$sql .= " ORDER BY o.created_at DESC";

$orders = fetchAll($sql);

$totalOrders = count($orders);
$pendingCount = count(array_filter($orders, fn($o) => $o['status'] === 'PENDING'));
$paidCount = count(array_filter($orders, fn($o) => $o['status'] === 'PAID'));
$deliveredCount = count(array_filter($orders, fn($o) => $o['status'] === 'DELIVERED'));
$cancelledCount = count(array_filter($orders, fn($o) => $o['status'] === 'CANCELLED'));

$totalRevenue = array_sum(array_map(fn($o) => ($o['status'] === 'DELIVERED' || $o['status'] === 'PAID') ? floatval($o['total']) : 0, $orders));
?>

<main class="role-content">
  <!-- Page Header -->
  <div class="role-header">
    <div>
      <h1>Order Management</h1>
      <p>View and manage all orders in the system</p>
    </div>
  </div>

  <!-- Stats Overview -->
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
        <span class="stat-card-title">Total Revenue</span>
        <div class="stat-card-icon red">
          <span class="material-symbols-outlined">payments</span>
        </div>
      </div>
      <h2 class="stat-card-value">৳<?php echo number_format($totalRevenue, 2); ?></h2>
    </div>
  </div>

  <!-- Filters Bar -->
  <div class="section-card">
    <div class="section-card-header">
      <h2 class="section-card-title">
        <span class="material-symbols-outlined">local_shipping</span>
        All Orders
      </h2>
    </div>

    <div class="filters-bar" style="margin-bottom: 0; border-bottom: 1px solid var(--surface0); border-radius: 0;">
      <form method="GET" action="/admin.php" class="filters-form">
        <input type="hidden" name="page" value="orders" />

        <div class="filter-group search-group">
          <span class="material-symbols-outlined">search</span>
          <input class="filter-input search-input" type="text" name="search" placeholder="Search orders..." value="<?php echo htmlspecialchars($searchQuery); ?>" />
        </div>

        <select name="status" class="filter-select">
          <option value="">All Status</option>
          <option value="PENDING" <?php echo $statusFilter === 'PENDING' ? 'selected' : ''; ?>>Pending</option>
          <option value="PAID" <?php echo $statusFilter === 'PAID' ? 'selected' : ''; ?>>Paid</option>
          <option value="DELIVERED" <?php echo $statusFilter === 'DELIVERED' ? 'selected' : ''; ?>>Delivered</option>
          <option value="CANCELLED" <?php echo $statusFilter === 'CANCELLED' ? 'selected' : ''; ?>>Cancelled</option>
        </select>

        <select name="date" class="filter-select">
          <option value="">All Time</option>
          <option value="today" <?php echo $dateFilter === 'today' ? 'selected' : ''; ?>>Today</option>
          <option value="week" <?php echo $dateFilter === 'week' ? 'selected' : ''; ?>>Last 7 Days</option>
          <option value="month" <?php echo $dateFilter === 'month' ? 'selected' : ''; ?>>Last 30 Days</option>
        </select>

        <button type="submit" class="btn btn-ghost">
          <span class="material-symbols-outlined">filter_list</span>
          Apply Filters
        </button>

        <?php if (!empty($searchQuery) || !empty($statusFilter) || !empty($dateFilter)): ?>
          <a href="/admin.php?page=orders" class="btn btn-ghost">
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
          <?php if (!empty($searchQuery) || !empty($statusFilter) || !empty($dateFilter)): ?>
            <small>Try adjusting your filters</small>
          <?php else: ?>
            <small>Orders will appear here when customers place them</small>
          <?php endif; ?>
        </div>
      <?php else: ?>
        <table class="role-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Customer</th>
              <th>Items</th>
              <th>Sellers</th>
              <th>Total</th>
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
                <td><?php echo $order['items_count']; ?> item<?php echo $order['items_count'] > 1 ? 's' : ''; ?></td>
                <td><?php echo $order['sellers_count']; ?> seller<?php echo $order['sellers_count'] > 1 ? 's' : ''; ?></td>
                <td><strong>৳<?php echo number_format($order['total'], 2); ?></strong></td>
                <td>
                  <span class="badge <?php echo strtolower($order['status']); ?>">
                    <?php echo ucfirst(strtolower($order['status'])); ?>
                  </span>
                </td>
                <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                <td>
                  <div class="table-actions">
                    <button class="table-btn view" title="View Details" onclick='viewOrder(<?php echo json_encode($order); ?>)'>
                      <span class="material-symbols-outlined">visibility</span>
                    </button>
                    <button class="table-btn edit" title="Update Status" onclick='updateOrderStatus(<?php echo $order['id']; ?>, "<?php echo $order['status']; ?>")'>
                      <span class="material-symbols-outlined">edit</span>
                    </button>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

        <!-- Results Summary -->
        <div class="table-footer">
          <p>Showing <?php echo count($orders); ?> order<?php echo count($orders) != 1 ? 's' : ''; ?></p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</main>

<!-- View Order Modal -->
<div id="viewOrderModal" class="modal">
  <div class="modal-content modal-large">
    <div class="modal-header">
      <h2>Order Details</h2>
      <button class="modal-close" onclick="closeModal('viewOrderModal')">&times;</button>
    </div>
    <div class="modal-body" id="orderDetailsContent">
      <!-- Content will be populated by JavaScript -->
    </div>
  </div>
</div>

<!-- Update Status Modal -->
<div id="updateStatusModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h2>Update Order Status</h2>
      <button class="modal-close" onclick="closeModal('updateStatusModal')">&times;</button>
    </div>
    <div class="modal-body">
      <form id="updateStatusForm">
        <input type="hidden" id="updateOrderId" name="order_id" />

        <div class="form-group">
          <label for="newStatus">New Status</label>
          <select id="newStatus" name="status" class="form-control" required>
            <option value="PENDING">Pending</option>
            <option value="PAID">Paid</option>
            <option value="DELIVERED">Delivered</option>
            <option value="CANCELLED">Cancelled</option>
          </select>
          <small style="color: var(--subtext0); display: block; margin-top: 8px;">
            <strong>Note:</strong> Changing status to CANCELLED will restore product stock.
          </small>
        </div>

        <div class="modal-actions">
          <button type="button" class="btn btn-ghost" onclick="closeModal('updateStatusModal')">Cancel</button>
          <button type="submit" class="btn btn-primary">Update Status</button>
        </div>
      </form>
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

  .form-group {
    margin-bottom: 20px;
  }

  .form-group label {
    display: block;
    margin-bottom: 8px;
    color: var(--text);
    font-weight: 600;
    font-size: 14px;
  }

  .form-control {
    width: 100%;
    padding: 12px;
    border: 1px solid var(--surface0);
    border-radius: 8px;
    background: var(--base);
    color: var(--text);
    font-size: 14px;
    transition: all 0.2s;
  }

  .form-control:focus {
    outline: none;
    border-color: var(--blue);
  }

  .modal-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    margin-top: 24px;
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
  function viewOrder(order) {
    // Fetch all order items
    fetch(`/admin/get-order-items.php?order_id=${order.id}`)
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          displayOrderDetails(order, data.items);
        } else {
          showToast('Failed to load order details', 'error');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showToast('Failed to load order details', 'error');
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
          <div class="order-item-meta">
            Quantity: ${item.quantity} × ৳${parseFloat(item.price).toFixed(2)} 
            <span style="color: var(--subtext1);">• Seller: ${item.seller_name || 'N/A'}</span>
          </div>
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
            <span class="order-detail-label">Total Amount</span>
            <span class="order-detail-value" style="font-size: 18px; color: var(--blue); font-weight: 700;">৳${parseFloat(order.total).toFixed(2)}</span>
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
        <h3>Order Items (${items.length})</h3>
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

  function updateOrderStatus(orderId, currentStatus) {
    document.getElementById('updateOrderId').value = orderId;
    document.getElementById('newStatus').value = currentStatus;
    openModal('updateStatusModal');
  }

  // Handle status update form submission
  document.getElementById('updateStatusForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch('/admin/order-handler.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showToast(data.message, 'success');
          closeModal('updateStatusModal');
          // Reload page to show updated status
          setTimeout(() => window.location.reload(), 1000);
        } else {
          showToast(data.message, 'error');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showToast('Failed to update order status', 'error');
      });
  });

  function openModal(modalId) {
    document.getElementById(modalId).classList.add('show');
  }

  function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('show');
  }

  function showToast(message, type = 'info') {
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    toast.style.cssText = `
      position: fixed;
      bottom: 24px;
      right: 24px;
      padding: 16px 24px;
      background: ${type === 'success' ? 'var(--green)' : type === 'error' ? 'var(--red)' : 'var(--blue)'};
      color: var(--crust);
      border-radius: 8px;
      font-weight: 600;
      z-index: 10000;
      animation: slideInRight 0.3s ease;
    `;

    document.body.appendChild(toast);

    // Remove after 3 seconds
    setTimeout(() => {
      toast.style.animation = 'slideOutRight 0.3s ease';
      setTimeout(() => toast.remove(), 300);
    }, 3000);
  }

  // Close modal when clicking outside
  window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
      event.target.classList.remove('show');
    }
  }
</script>
