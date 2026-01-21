<?php
require_once __DIR__ . '/app/config/database.php';
require_once __DIR__ . '/app/models/Auth.php';

// Check if user is seller or admin
if (!isLoggedIn() || !in_array($_SESSION['user_role'], ['SELLER', 'ADMIN'])) {
  header('Location: /index.php');
  exit;
}

// Routing logic
$allowed_pages = ['dashboard', 'products', 'orders', 'analytics', 'settings'];
$page = $_GET['page'] ?? 'dashboard';

if (!in_array($page, $allowed_pages)) {
  $page = 'dashboard';
}
?>

<!DOCTYPE HTML>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sahara | Seller</title>
  <link rel="icon" href="assets/favicon.ico">
  <link rel="stylesheet" href="css/main.css" />
  <link rel="stylesheet" href="css/role.css" />
</head>

<body>
  <?php include __DIR__ . '/app/views/partials/header.php'; ?>

  <div class="role-layout seller-layout">
    <?php
    function isActive($page)
    {
      $current = $_GET['page'] ?? 'dashboard';
      return $current === $page ? 'active' : '';
    }

    $allowed_pages = ['dashboard', 'products', 'orders', 'analytics', 'settings'];
    $page = $_GET['page'] ?? 'dashboard';
    ?>

    <aside class="role-sidebar">
      <div class="sidebar-header">
        <h2>Seller Panel</h2>
      </div>

      <nav class="role-nav">
        <a href="seller.php" class="role-nav-item <?php echo isActive('dashboard'); ?>">
          <span class="material-symbols-outlined">dashboard</span>
          Dashboard
        </a>
        <a href="seller.php?page=products" class="role-nav-item <?php echo isActive('products'); ?>">
          <span class="material-symbols-outlined">inventory_2</span>
          My Products
        </a>
        <a href="seller.php?page=orders" class="role-nav-item <?php echo isActive('orders'); ?>">
          <span class="material-symbols-outlined">local_shipping</span>
          Orders
        </a>
      </nav>
    </aside>

    <?php include __DIR__ . "/app/views/seller/{$page}.php"; ?>
  </div>
</body>

</html>
