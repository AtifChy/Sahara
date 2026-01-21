<?php
require_once __DIR__ . '/../../models/Auth.php';
require_once __DIR__ . '/../../models/Cart.php';
checkRememberToken();

// Get cart count from session using helper function
$cartCount = getCartCount();
?>

<header>
  <h1 class="logo">
    <a href="/">Sahara</a>
  </h1>

  <?php
  function aria_current($page)
  {
    $current = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if ($current === '/') $current = '/index.php';
    return $current === $page ? 'aria-current="page"' : '';
  }
  ?>

  <nav>
    <a href="/index.php" <?php echo aria_current('/index.php'); ?>>Home</a>
    <a href="/shop.php" <?php echo aria_current('/shop.php'); ?>>Shop</a>
    <a href="/about.php" <?php echo aria_current('/about.php'); ?>>About</a>
    <a href="/contact.php" <?php echo aria_current('/contact.php'); ?>>Contact</a>
    <?php if (isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['SELLER', 'ADMIN'])): ?>
      <a href="/seller.php" <?php echo aria_current('/seller.php'); ?> aria-label="Seller">Seller</a>
    <?php endif; ?>
    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'ADMIN'): ?>
      <a href="/admin.php" <?php echo aria_current('/admin.php'); ?> aria-label="Admin">Admin</a>
    <?php endif; ?>
  </nav>

  <div class="header-right">
    <button
      class="btn-icon"
      type="button"
      id="search-toggle"
      aria-hidden="false"
      aria-controls="search-form">
      <span class="material-symbols-outlined">search</span>
    </button>

    <div class="header-actions">
      <form class="search-form" id="search-form" aria-hidden="true">
        <span class="material-symbols-outlined">search</span>
        <input id="search-input" type="search" placeholder="Search products..." />
      </form>
      <script defer src="/views/js/search.js"></script>

      <a href="/cart.php" class="btn-icon cart-btn" id="shopping-cart">
        <span class="material-symbols-outlined">shopping_cart</span>
        <span
          class="cart-badge"
          id="cart-badge"
          style="<?php echo $cartCount > 0 ? 'display:block;' : 'display:none;'; ?>">
          <?php echo $cartCount; ?>
        </span>
      </a>

      <?php if (isLoggedIn()): ?>
        <div class="account-dropdown">
          <button class="btn-account" type="button" id="account-btn" aria-expanded="false" aria-controls="account-menu">
            <span class="material-symbols-outlined">account_circle</span>
            <span class="btn-account-text"><?php echo $_SESSION['user_fname']; ?></span>
          </button>

          <div class="account-menu" id="account-menu" role="menu" aria-hidden="true">
            <div class="account-menu-header">
              <span class="material-symbols-outlined">person</span>
              <div>
                <p class="user-name"><?php echo $_SESSION['user_fname'] . ' ' . $_SESSION['user_lname']; ?></p>
                <p class="user-email"><?php echo $_SESSION['user_email']; ?></p>
                <p class="user-role <?php echo strtolower($_SESSION['user_role']); ?>">
                  <?php echo $_SESSION['user_role']; ?>
                </p>
              </div>
            </div>

            <div class="account-menu-divider"></div>

            <a href="/profile.php" class="menu-item">
              <span class="material-symbols-outlined">person</span>
              My Profile
            </a>
            <a href="/orders.php" class="menu-item">
              <span class="material-symbols-outlined">receipt_long</span>
              My Orders
            </a>
            <a href="/wishlist.php" class="menu-item">
              <span class="material-symbols-outlined">favorite</span>
              My Wishlist
            </a>

            <div class="account-menu-divider"></div>

            <a href="/controllers/auth/LogoutController.php" class="menu-item logout">
              <span class="material-symbols-outlined">logout</span>
              Logout
            </a>
          </div>
        </div>
        <script defer src="/views/js/account.js"></script>
      <?php else: ?>
        <button type="button" class="btn-account">
          <a href="/auth.php?page=login">
            <span class="material-symbols-outlined">account_circle</span>
            <span class="btn-account-text">Sign In</span>
          </a>
        </button>
      <?php endif; ?>
    </div>
  </div>
</header>

<script type="module" src="/views/js/modules/cart.js"></script>
