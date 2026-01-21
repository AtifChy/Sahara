<!DOCTYPE HTML>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sahara</title>
  <link rel="icon" href="views/assets/favicon.ico" />
  <link rel="stylesheet" href="views/css/main.css" />
  <script type="module" src="views/js/home.js"></script>
  <script type="module" src="views/js/hero.js"></script>
  <script type="module" src="views/js/newsletter.js"></script>
</head>

<body>
  <?php include __DIR__ . '/views/partials/header.php'; ?>

  <main>
    <section class="hero">
      <div class="hero-slider"></div>

      <div class="dots">
        <div class="dot active"></div>
        <div class="dot"></div>
        <div class="dot"></div>
      </div>
    </section>

    <section class="products">
      <h2>Popular Products</h2>

      <div class="product-grid" id="home-products-grid">
      </div>

      <div class="see-more">
        <button>
          <a href="shop.php">See more</a>
        </button>
      </div>
    </section>

    <section class="spec">
      <h2>Our Specifications</h2>
      <p>
        We offer top-tier service and convenience to ensure your shopping
        experience smooth, secure and completely hassle-free.
      </p>

      <div class="spec-grid">
        <div class="spec-card free-shipping">
          <span class="material-symbols-outlined">local_shipping</span>
          <h3>Free Shipping</h3>
          <p>On all orders over $50</p>
        </div>

        <div class="spec-card support">
          <span class="material-symbols-outlined">support_agent</span>
          <h3>24/7 Support</h3>
          <p>We're here to help, anytime</p>
        </div>

        <div class="spec-card easy-returns">
          <span class="material-symbols-outlined">autorenew</span>
          <h3>Easy Returns</h3>
          <p>30-day money-back guarantee</p>
        </div>
      </div>
    </section>

    <section class="newsletter">
      <h2>Subscribe to our Newsletter</h2>
      <p>
        Get the latest updates on exclusive offers, new arrivals and insider
        updates delivered straight to your inbox.
      </p>

      <form action="#" class="newsletter-form" novalidate>
        <div class="input-group">
          <input
            type="email"
            name="email"
            id="email"
            placeholder="Enter your email address" />
          <input type="submit" value="Subscribe" />
        </div>
        <span class="error-message" id="email-error"></span>
      </form>
    </section>
  </main>

  <?php include __DIR__ . '/views/partials/footer.html'; ?>
</body>

</html>
