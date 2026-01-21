<?php
// Get all users with their profiles
$searchQuery = $_GET['search'] ?? '';
$roleFilter = $_GET['role'] ?? '';
$statusFilter = $_GET['status'] ?? '';

$sql = "
  SELECT u.*, 
         up.first_name, 
         up.last_name, 
         up.phone,
         up.gender,
         up.address,
         CONCAT(up.first_name, ' ', COALESCE(up.last_name, '')) as full_name,
         (SELECT COUNT(*) FROM orders WHERE user_id = u.id) as order_count,
         (SELECT SUM(total) FROM orders WHERE user_id = u.id AND status IN ('PAID', 'DELIVERED')) as total_spent
  FROM users u
  LEFT JOIN user_profiles up ON u.id = up.user_id
  WHERE 1=1
";

// Apply filters
if (!empty($searchQuery)) {
  $searchQuery = mysqli_real_escape_string(getDB(), $searchQuery);
  $sql .= " AND (u.email LIKE '%$searchQuery%' OR up.first_name LIKE '%$searchQuery%' OR up.last_name LIKE '%$searchQuery%')";
}

if (!empty($roleFilter)) {
  $sql .= " AND u.role = '$roleFilter'";
}

if (!empty($statusFilter)) {
  $isActive = $statusFilter === 'active' ? 1 : 0;
  $sql .= " AND u.is_active = $isActive";
}

$sql .= " ORDER BY u.created_at DESC";

$users = fetchAll($sql);

// Get statistics
$totalUsers = count($users);
$activeUsers = count(array_filter($users, fn($u) => $u['is_active'] == 1));
$adminCount = count(array_filter($users, fn($u) => $u['role'] === 'ADMIN'));
$sellerCount = count(array_filter($users, fn($u) => $u['role'] === 'SELLER'));
$customerCount = count(array_filter($users, fn($u) => $u['role'] === 'CUSTOMER'));
?>

<main class="role-content">
  <div class="role-header">
    <div>
      <h1>User Management</h1>
      <p>Manage and monitor all users in the system</p>
    </div>
  </div>

  <!-- Stats Cards -->
  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-card-header">
        <span class="stat-card-title">Total Users</span>
        <div class="stat-card-icon blue">
          <span class="material-symbols-outlined">group</span>
        </div>
      </div>
      <h2 class="stat-card-value"><?php echo number_format($totalUsers); ?></h2>
    </div>

    <div class="stat-card">
      <div class="stat-card-header">
        <span class="stat-card-title">Active Users</span>
        <div class="stat-card-icon green">
          <span class="material-symbols-outlined">check_circle</span>
        </div>
      </div>
      <h2 class="stat-card-value"><?php echo number_format($activeUsers); ?></h2>
    </div>

    <div class="stat-card">
      <div class="stat-card-header">
        <span class="stat-card-title">Customers</span>
        <div class="stat-card-icon yellow">
          <span class="material-symbols-outlined">shopping_bag</span>
        </div>
      </div>
      <h2 class="stat-card-value"><?php echo number_format($customerCount); ?></h2>
    </div>

    <div class="stat-card">
      <div class="stat-card-header">
        <span class="stat-card-title">Sellers</span>
        <div class="stat-card-icon red">
          <span class="material-symbols-outlined">storefront</span>
        </div>
      </div>
      <h2 class="stat-card-value"><?php echo number_format($sellerCount); ?></h2>
    </div>
  </div>

  <!-- Filters and Search -->
  <div class="section-card">
    <div class="section-card-header">
      <h2 class="section-card-title">
        <span class="material-symbols-outlined">group</span>
        All Users
      </h2>
    </div>

    <div class="filters-bar">
      <form method="GET" action="/admin.php" class="filters-form">
        <input type="hidden" name="page" value="users" />

        <div class="filter-group search-group">
          <span class="material-symbols-outlined">search</span>
          <input class="filter-input search-input" type="text" name="search" placeholder="Search users..." value="<?php echo $searchQuery; ?>" />
        </div>

        <select name="role" class="filter-select">
          <option value="">All Roles</option>
          <option value="CUSTOMER" <?php echo $roleFilter === 'CUSTOMER' ? 'selected' : ''; ?>>Customer</option>
          <option value="SELLER" <?php echo $roleFilter === 'SELLER' ? 'selected' : ''; ?>>Seller</option>
          <option value="ADMIN" <?php echo $roleFilter === 'ADMIN' ? 'selected' : ''; ?>>Admin</option>
        </select>

        <select name="status" class="filter-select">
          <option value="">All Status</option>
          <option value="active" <?php echo $statusFilter === 'active' ? 'selected' : ''; ?>>Active</option>
          <option value="inactive" <?php echo $statusFilter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
        </select>

        <button type="submit" class="btn btn-ghost">
          <span class="material-symbols-outlined">filter_list</span>
          Apply
        </button>

        <?php if (!empty($searchQuery) || !empty($roleFilter) || !empty($statusFilter)): ?>
          <a href="/admin.php?page=users" class="btn btn-ghost">
            <span class="material-symbols-outlined">clear</span>
            Clear
          </a>
        <?php endif; ?>
      </form>
    </div>

    <div class="section-card-body">
      <?php if (empty($users)): ?>
        <div class="empty-state">
          <span class="material-symbols-outlined">group</span>
          <p>No users found</p>
        </div>
      <?php else: ?>
        <table class="role-table">
          <thead>
            <tr>
              <th>User</th>
              <th>Phone</th>
              <th>Role</th>
              <th>Status</th>
              <th>Orders</th>
              <th>Total Spent</th>
              <th>Joined</th>
              <th>Last Login</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($users as $user): ?>
              <tr>
                <td>
                  <div class="user-info">
                    <div class="user-avatar">
                      <?php echo strtoupper(substr($user['first_name'] ?? $user['email'], 0, 1)); ?>
                    </div>
                    <div class="user-details">
                      <div class="user-name"><?php echo $user['full_name'] ?: 'N/A'; ?></div>
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
                <td><?php echo $user['order_count'] ?? 0; ?></td>
                <td>৳<?php echo number_format($user['total_spent'] ?? 0, 2); ?></td>
                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                <td>
                  <?php if ($user['last_login']): ?>
                    <?php echo date('M d, Y', strtotime($user['last_login'])); ?>
                  <?php else: ?>
                    <span style="color: var(--subtext0);">Never</span>
                  <?php endif; ?>
                </td>
                <td>
                  <div class="table-actions">
                    <button class="table-btn view" title="View Details" onclick='viewUser(<?php echo json_encode($user); ?>)'>
                      <span class="material-symbols-outlined">visibility</span>
                    </button>
                    <button class="table-btn edit" title="Change Role" onclick="changeRole(<?php echo $user['id']; ?>, '<?php echo $user['role']; ?>', '<?php echo $user['full_name'] ?: $user['email']; ?>')">
                      <span class="material-symbols-outlined">badge</span>
                    </button>
                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                      <?php if ($user['is_active']): ?>
                        <button class="table-btn delete" title="Deactivate User" onclick="toggleUserStatus(<?php echo $user['id']; ?>, 0, '<?php echo $user['full_name'] ?: $user['email']; ?>')">
                          <span class="material-symbols-outlined">block</span>
                        </button>
                      <?php else: ?>
                        <button class="table-btn" title="Activate User" onclick="toggleUserStatus(<?php echo $user['id']; ?>, 1, '<?php echo $user['full_name'] ?: $user['email']; ?>')">
                          <span class="material-symbols-outlined">check_circle</span>
                        </button>
                      <?php endif; ?>
                    <?php endif; ?>
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

<!-- View User Modal -->
<div id="viewUserModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h2>User Details</h2>
      <button class="modal-close" onclick="closeModal('viewUserModal')">&times;</button>
    </div>
    <div class="modal-body" id="userDetailsContent">
      <!-- Content will be populated by JavaScript -->
    </div>
  </div>
</div>

<!-- Change Role Modal -->
<div id="changeRoleModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h2>Change User Role</h2>
      <button class="modal-close" onclick="closeModal('changeRoleModal')">&times;</button>
    </div>
    <div class="modal-body">
      <p>Change role for: <strong id="roleUserName"></strong></p>
      <form id="changeRoleForm" onsubmit="submitRoleChange(event)">
        <input type="hidden" id="roleUserId" name="user_id">
        <div class="form-group">
          <label for="newRole">Select New Role</label>
          <select id="newRole" name="role" required class="filter-select">
            <option value="CUSTOMER">Customer</option>
            <option value="SELLER">Seller</option>
            <option value="ADMIN">Admin</option>
          </select>
        </div>
        <div class="modal-actions">
          <button type="button" class="btn btn-ghost" onclick="closeModal('changeRoleModal')">Cancel</button>
          <button type="submit" class="btn btn-primary">Update Role</button>
        </div>
      </form>
    </div>
  </div>
</div>

<style>
  .filters-bar {
    border-bottom: 1px solid var(--surface0);
    border-radius: 0;
    margin-bottom: 0;
  }

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
    max-width: 600px;
    width: 90%;
    max-height: 80vh;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    border: 1px solid var(--surface0);
    animation: slideUp 0.3s ease;
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

  .modal-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    margin-top: 24px;
  }

  .user-detail-grid {
    display: grid;
    gap: 16px;
  }

  .user-detail-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
  }

  .user-detail-label {
    font-size: 12px;
    color: var(--subtext0);
    text-transform: uppercase;
    font-weight: 600;
    letter-spacing: 0.5px;
  }

  .user-detail-value {
    font-size: 14px;
    color: var(--text);
  }
</style>

<script>
  function showNotification(type, title, message) {
    let container = document.querySelector(".notification-container");

    if (!container) {
      container = document.createElement("div");
      container.className = "notification-container";
      document.body.appendChild(container);
    }

    const notification = document.createElement("div");
    notification.className = `notification notification-${type}`;

    const iconMap = {
      success: "check_circle",
      error: "error",
      info: "info",
      warning: "warning"
    };

    notification.innerHTML = `
      <span class="material-symbols-outlined">${iconMap[type] || "info"}</span>
      <div class="notification-content">
        <strong>${title}</strong>
        <p>${message}</p>
      </div>
    `;

    container.appendChild(notification);

    setTimeout(() => notification.classList.add("show"), 10);

    setTimeout(() => {
      notification.classList.remove("show");
      setTimeout(() => notification.remove(), 300);
    }, 3000);
  }

  function viewUser(user) {
    const content = `
      <div class="user-detail-grid">
        <div class="user-detail-item">
          <span class="user-detail-label">Full Name</span>
          <span class="user-detail-value">${user.full_name || 'N/A'}</span>
        </div>
        <div class="user-detail-item">
          <span class="user-detail-label">Email</span>
          <span class="user-detail-value">${user.email}</span>
        </div>
        <div class="user-detail-item">
          <span class="user-detail-label">Phone</span>
          <span class="user-detail-value">${user.phone || 'N/A'}</span>
        </div>
        <div class="user-detail-item">
          <span class="user-detail-label">Gender</span>
          <span class="user-detail-value">${user.gender || 'N/A'}</span>
        </div>
        <div class="user-detail-item">
          <span class="user-detail-label">Address</span>
          <span class="user-detail-value">${user.address || 'N/A'}</span>
        </div>
        <div class="user-detail-item">
          <span class="user-detail-label">Role</span>
          <span class="user-detail-value">
            <span class="badge ${user.role.toLowerCase()}">${user.role}</span>
          </span>
        </div>
        <div class="user-detail-item">
          <span class="user-detail-label">Status</span>
          <span class="user-detail-value">
            <span class="badge ${user.is_active == 1 ? 'active' : 'inactive'}">
              ${user.is_active == 1 ? 'Active' : 'Inactive'}
            </span>
          </span>
        </div>
        <div class="user-detail-item">
          <span class="user-detail-label">Total Orders</span>
          <span class="user-detail-value">${user.order_count || 0}</span>
        </div>
        <div class="user-detail-item">
          <span class="user-detail-label">Total Spent</span>
          <span class="user-detail-value">৳${parseFloat(user.total_spent || 0).toFixed(2)}</span>
        </div>
        <div class="user-detail-item">
          <span class="user-detail-label">Joined</span>
          <span class="user-detail-value">${new Date(user.created_at).toLocaleDateString()}</span>
        </div>
        <div class="user-detail-item">
          <span class="user-detail-label">Last Login</span>
          <span class="user-detail-value">${user.last_login ? new Date(user.last_login).toLocaleDateString() : 'Never'}</span>
        </div>
      </div>
    `;

    document.getElementById('userDetailsContent').innerHTML = content;
    openModal('viewUserModal');
  }

  function changeRole(userId, currentRole, userName) {
    document.getElementById('roleUserId').value = userId;
    document.getElementById('roleUserName').textContent = userName;
    document.getElementById('newRole').value = currentRole;
    openModal('changeRoleModal');
  }

  function submitRoleChange(event) {
    event.preventDefault();

    const formData = new FormData(event.target);
    formData.append('action', 'change_role');

    const submitBtn = event.target.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Updating...';

    fetch('/admin/user-handler.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showNotification('success', 'Success', data.message);
          closeModal('changeRoleModal');
          setTimeout(() => window.location.reload(), 1000);
        } else {
          showNotification('error', 'Error', data.message);
          submitBtn.disabled = false;
          submitBtn.textContent = 'Update Role';
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showNotification('error', 'Error', 'Failed to update role. Please try again.');
        submitBtn.disabled = false;
        submitBtn.textContent = 'Update Role';
      });
  }

  function toggleUserStatus(userId, status, userName) {
    const action = status ? 'activate' : 'deactivate';

    if (!confirm(`Are you sure you want to ${action} ${userName}?`)) {
      return;
    }

    const formData = new FormData();
    formData.append('action', 'toggle_status');
    formData.append('user_id', userId);
    formData.append('status', status);

    fetch('/admin/user-handler.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showNotification('success', 'Success', data.message);
          setTimeout(() => window.location.reload(), 1000);
        } else {
          showNotification('error', 'Error', data.message);
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showNotification('error', 'Error', 'Failed to update status. Please try again.');
      });
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
