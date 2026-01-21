<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

// Check if user is admin
if (!isLoggedIn() || $_SESSION['user_role'] !== 'ADMIN') {
  header('Location: /index.php');
  exit;
}

// Total users
$userCountResult = fetchOne("SELECT COUNT(*) as count FROM users");
$userCount = $userCountResult['count'] ?? 0;

// Total products
$productCountResult = fetchOne("SELECT COUNT(*) as count FROM products");
$productCount = $productCountResult['count'] ?? 0;

// Total orders
$orderCountResult = fetchOne("SELECT COUNT(*) as count FROM orders");
$orderCount = $orderCountResult['count'] ?? 0;

// Total revenue
$revenueResult = fetchOne("SELECT SUM(total) as revenue FROM orders WHERE status IN ('PAID', 'DELIVERED')");
$totalRevenue = $revenueResult['revenue'] ?? 0;

// Recent orders (last 5)
$recentOrders = fetchAll("
  SELECT o.*,
         CONCAT(up.first_name, ' ', COALESCE(up.last_name, '')) as customer_name,
         u.email as customer_email
  FROM orders o
  JOIN users u ON o.user_id = u.id
  LEFT JOIN user_profiles up ON u.id = up.user_id
  ORDER BY o.created_at DESC
  LIMIT 5
");

// Low stock products (stock < 10)
$lowStockProducts = fetchAll("
  SELECT * FROM products 
  WHERE stock < 10 
  ORDER BY stock ASC 
  LIMIT 5
");

// Recent users (last 5)
$recentUsers = fetchAll("
  SELECT u.*, 
         up.first_name, 
         up.last_name, 
         up.phone,
         CONCAT(up.first_name, ' ', COALESCE(up.last_name, '')) as full_name
  FROM users u
  LEFT JOIN user_profiles up ON u.id = up.user_id
  ORDER BY u.created_at DESC 
  LIMIT 5
");
?>

<!DOCTYPE HTML>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sahara | Admin</title>
  <link rel="icon" href="assets/favicon.ico">
  <link rel="stylesheet" href="css/main.css" />
  <link rel="stylesheet" href="css/role.css" />
</head>

<body>
  <?php include 'partials/header.php'; ?>

  <div class="role-layout">
    <!-- Sidebar Navigation -->
    <aside class="role-sidebar">
      <h2>Admin Panel</h2>
      <nav class="role-nav">
        <?php
        $allowed_pages = ['dashboard', 'users', 'products', 'orders', 'reports', 'newsletter', 'settings'];
        $page = $_GET['page'] ?? 'dashboard';

        if (! in_array($page, $allowed_pages)) {
          $page = 'dashboard';
        }

        function isActive($page)
        {
          return ($_GET['page'] ?? 'dashboard') === $page ? 'active' : '';
        }
        ?>

        <a href="/admin.php" class="role-nav-item <?php echo isActive('dashboard'); ?>">
          <span class="material-symbols-outlined">dashboard</span>
          Dashboard
        </a>
        <a href="/admin.php?page=users" class="role-nav-item <?php echo isActive('users'); ?>">
          <span class="material-symbols-outlined">group</span>
          Users
        </a>
        <a href="/admin.php?page=products" class="role-nav-item <?php echo isActive('products'); ?>">
          <span class="material-symbols-outlined">inventory_2</span>
          Products
        </a>
        <a href="/admin.php?page=orders" class="role-nav-item <?php echo isActive('orders'); ?>">
          <span class="material-symbols-outlined">receipt_long</span>
          Orders
        </a>
      </nav>
    </aside>

    <?php include "./admin/{$page}.php" ?>
  </div>
</body>

</html>
