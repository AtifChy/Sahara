<!DOCTYPE HTML>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sahara | Shop</title>
  <link rel="icon" href="assets/favicon.ico">
  <link rel="stylesheet" href="css/main.css" />
  <script type="module" src="js/shop.js"></script>
</head>

<body>
  <?php include 'partials/header.php'; ?>

  <main class="shop-page">
    <aside class="shop-sidebar">
      <div class="filters">
        <h3>Categories</h3>
        <div class="category-list">
          <button class="category-btn active" data-category="all">All Products</button>
          <button class="category-btn" data-category="electronics">Electronics</button>
          <button class="category-btn" data-category="fashion">Fashion</button>
          <button class="category-btn" data-category="accessories">Accessories</button>
          <button class="category-btn" data-category="home">Home & Garden</button>
        </div>
      </div>

      <div class="filters">
        <h3>Sort By</h3>
        <div class="select-box">
          <select id="sort-select">
            <option value="newest">Newest</option>
            <option value="price-low">Price: Low to High</option>
            <option value="price-high">Price: High to Low</option>
            <option value="popular">Most Popular</option>
            <option value="rating">Top Rated</option>
          </select>
        </div>
      </div>

      <div class="filters">
        <h3>Price Range</h3>
        <div class="select-box">
          <select id="price-select">
            <option value="all">All Prices</option>
            <option value="0-100">Under ৳100</option>
            <option value="100-500">৳100 - ৳500</option>
            <option value="500-1000">৳500 - ৳1,000</option>
            <option value="1000">৳1,000+</option>
          </select>
        </div>
      </div>
    </aside>

    <section class="shop-content">
      <div class="shop-header">
        <h1>Shop Our Collection</h1>
        <p class="shop-subtitle">Discover trending products at unbeatable prices</p>
      </div>

      <div class="product-grid" id="products-grid">
      </div>

      <div class="no-products" id="no-products" style="display: none;">
        <p>No products found in this category. Try adjusting your filters.</p>
      </div>
    </section>
  </main>

  <?php include 'partials/footer.html'; ?>
</body>

</html>
