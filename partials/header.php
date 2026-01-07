<header>
  <h1 class="logo">
    <a href="/">Sahara</a>
  </h1>

  <?php
  function aria_current($page)
  {
    $current = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    return $current === $page ? 'aria-current="page"' : '';
  }
  ?>

  <nav>
    <a href="/" <?php echo aria_current('/'); ?>>Home</a>
    <a href="/pages/shop.php" <?php echo aria_current('/pages/shop.php'); ?>>Shop</a>
    <a href="/pages/about.php" <?php echo aria_current('/pages/about.php'); ?>>About</a>
    <a href="/pages/contact.php" <?php echo aria_current('/pages/contact.php'); ?>>Contact</a>
    <a href="/pages/seller.php" <?php echo aria_current('/pages/seller.php'); ?> aria-label="Seller">Seller</a>
    <a href="/pages/admin.php" <?php echo aria_current('/pages/admin.php'); ?> aria-label="Admin">Admin</a>
  </nav>

  <div class="header-right">
    <button
      class="icon-btn"
      type="button"
      id="search-toggle"
      aria-hidden="false"
      aria-controls="search-form">
      <span class="material-symbols-outlined">search</span>
    </button>

    <form action="#" class="search-form" id="search-form" aria-hidden="true">
      <span class="material-symbols-outlined">search</span>
      <input id="search-input" type="search" placeholder="Search products..." />
    </form>

    <button class="icon-btn" type="button" id="shopping-cart">
      <span class="material-symbols-outlined">shopping_cart</span>
    </button>
  </div>
</header>

<script defer src="/js/search.js"></script>
