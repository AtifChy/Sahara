<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sahara | Shop</title>
  <link rel="icon" href="../assets/favicon.png">
  <link rel="stylesheet" href="../css/main.css" />
</head>

<body>
  <?php include '../partials/header.php'; ?>

  <main class="shop-page">
    <aside class="filters">
      <h3>Categories</h3>

      <ul class="category-list">
        <li><button class="active" data-category="all">All</button></li>
        <li><button data-category="earphones">Earphones</button></li>
        <li><button data-category="headphones">Headphones</button></li>
        <li><button data-category="speakers">Speakers</button></li>
        <li><button data-category="laptops">Laptops</button></li>
      </ul>
    </aside>

    <section class="shop-products">
      <div class="shop-header">
        <h2>All Products</h2>

        <div class="select-box">
          <select name="" id="sort">
            <option value="default">Sort by</option>
            <option value="price-low">Price: Low to High</option>
            <option value="price-high">Price: High to Low</option>
          </select>
        </div>
      </div>

      <div class="product-grid" id="product-grid"></div>
    </section>
  </main>

  <?php include '../partials/footer.html'; ?>
</body>

</html>
