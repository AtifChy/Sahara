<!DOCTYPE HTML>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sahara | About</title>
  <link rel="icon" href="assets/favicon.ico" />
  <link rel="stylesheet" href="css/main.css" />
  <style>
    .about-hero {
      background: linear-gradient(135deg, var(--blue) 0%, var(--sapphire) 100%);
      padding: 80px 20px;
      text-align: center;
      border-radius: 20px;
      color: var(--base);
      margin-bottom: 60px;
    }

    .about-hero h1 {
      font-size: 48px;
      font-weight: 700;
      margin-bottom: 20px;
      line-height: 1.2;
    }

    .about-hero p {
      font-size: 18px;
      max-width: 700px;
      margin: 0 auto;
      opacity: 0.95;
    }

    .about-section {
      margin-bottom: 80px;
    }

    .about-section h2 {
      font-size: 32px;
      font-weight: 700;
      margin-bottom: 30px;
      color: var(--text);
    }

    .about-section p {
      font-size: 16px;
      line-height: 1.8;
      color: var(--subtext0);
      max-width: 900px;
      margin-bottom: 20px;
    }

    .values-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 30px;
      margin: 40px 0;
    }

    .value-card {
      background: var(--mantle);
      border: 1px solid var(--surface0);
      padding: 30px;
      border-radius: 12px;
      text-align: center;
      transition: transform 0.2s ease, border-color 0.2s ease;
    }

    .value-card:hover {
      transform: translateY(-8px);
      border-color: var(--blue);
    }

    .value-icon {
      font-size: 48px;
      color: var(--blue);
      margin-bottom: 20px;
    }

    .value-card h3 {
      font-size: 20px;
      font-weight: 600;
      margin-bottom: 12px;
      color: var(--text);
    }

    .value-card p {
      font-size: 14px;
      color: var(--subtext0);
      margin: 0;
    }

    .team-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 40px;
      margin: 40px 0;
    }

    .team-member {
      text-align: center;
    }

    .member-avatar {
      width: 150px;
      height: 150px;
      background: linear-gradient(135deg, var(--blue) 0%, var(--sapphire) 100%);
      border-radius: 50%;
      margin: 0 auto 20px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 64px;
      color: var(--base);
    }

    .member-name {
      font-size: 18px;
      font-weight: 600;
      color: var(--text);
      margin-bottom: 8px;
    }

    .member-role {
      font-size: 14px;
      color: var(--subtext0);
      margin-bottom: 12px;
    }

    .member-bio {
      font-size: 13px;
      line-height: 1.6;
      color: var(--subtext0);
    }

    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 30px;
      margin: 40px 0;
      background: var(--mantle);
      padding: 40px;
      border-radius: 12px;
      border: 1px solid var(--surface0);
    }

    .stat-item {
      text-align: center;
    }

    .stat-number {
      font-size: 36px;
      font-weight: 700;
      color: var(--blue);
      margin-bottom: 8px;
    }

    .stat-label {
      font-size: 14px;
      color: var(--subtext0);
    }

    .timeline {
      position: relative;
      padding: 40px 0;
      margin: 40px 0;
    }

    .timeline::before {
      content: '';
      position: absolute;
      left: 50%;
      transform: translateX(-50%);
      width: 2px;
      height: 100%;
      background: var(--surface0);
    }

    .timeline-item {
      margin-bottom: 40px;
      width: 48%;
    }

    .timeline-item:nth-child(odd) {
      margin-left: 0;
      text-align: right;
      padding-right: 5%;
    }

    .timeline-item:nth-child(even) {
      margin-left: 52%;
      text-align: left;
      padding-left: 5%;
    }

    .timeline-dot {
      position: absolute;
      left: 50%;
      transform: translateX(-50%);
      width: 16px;
      height: 16px;
      background: var(--blue);
      border: 3px solid var(--crust);
      border-radius: 50%;
      top: 20px;
      margin-top: -8px;
    }

    .timeline-content {
      background: var(--mantle);
      border: 1px solid var(--surface0);
      padding: 20px;
      border-radius: 8px;
    }

    .timeline-year {
      font-weight: 700;
      color: var(--blue);
      font-size: 16px;
      margin-bottom: 8px;
    }

    .timeline-text {
      font-size: 14px;
      color: var(--subtext0);
    }

    @media (max-width: 768px) {
      .about-hero h1 {
        font-size: 32px;
      }

      .about-section h2 {
        font-size: 24px;
      }

      .timeline::before {
        display: none;
      }

      .timeline-item,
      .timeline-item:nth-child(odd),
      .timeline-item:nth-child(even) {
        width: 100%;
        margin-left: 0;
        text-align: left;
        padding-left: 0;
        padding-right: 0;
      }

      .timeline-dot {
        display: none;
      }
    }
  </style>
</head>

<body>
  <?php include 'partials/header.php'; ?>

  <main>
    <!-- Hero Section -->
    <section class="about-hero">
      <h1>About Sahara</h1>
      <p>Discover the story behind your favorite e-commerce platform. We're here to bring quality, innovation, and exceptional service to your shopping experience.</p>
    </section>

    <!-- Our Story Section -->
    <section class="about-section">
      <h2>Our Story</h2>
      <p>
        Sahara was founded with a simple mission: to revolutionize online shopping by bringing together quality products, competitive prices, and outstanding customer service. What started as a small venture has grown into a trusted platform serving thousands of customers worldwide.
      </p>
      <p>
        We believe that shopping should be seamless, enjoyable, and rewarding. Every decision we make, from the products we offer to the features we build, is driven by our commitment to your satisfaction.
      </p>
    </section>

    <!-- Mission & Vision Section -->
    <section class="about-section">
      <h2>Our Mission & Vision</h2>
      <p>
        <strong>Mission:</strong> To empower people with access to a diverse range of quality products at fair prices, backed by exceptional service and support.
      </p>
      <p>
        <strong>Vision:</strong> To become the world's most trusted and customer-centric e-commerce platform, known for innovation, reliability, and excellence.
      </p>
    </section>

    <!-- Core Values -->
    <section class="about-section">
      <h2>Our Core Values</h2>
      <div class="values-grid">
        <div class="value-card">
          <div class="value-icon">💎</div>
          <h3>Quality</h3>
          <p>We only offer products that meet our high standards of quality and durability.</p>
        </div>
        <div class="value-card">
          <div class="value-icon">🤝</div>
          <h3>Customer First</h3>
          <p>Your satisfaction is our priority. We're here to support you every step of the way.</p>
        </div>
        <div class="value-card">
          <div class="value-icon">🚀</div>
          <h3>Innovation</h3>
          <p>We constantly improve our platform to provide the best shopping experience.</p>
        </div>
        <div class="value-card">
          <div class="value-icon">🌍</div>
          <h3>Sustainability</h3>
          <p>We're committed to responsible business practices for a better future.</p>
        </div>
        <div class="value-card">
          <div class="value-icon">✨</div>
          <h3>Integrity</h3>
          <p>Transparency and honesty guide all our interactions with customers and partners.</p>
        </div>
        <div class="value-card">
          <div class="value-icon">⚡</div>
          <h3>Speed</h3>
          <p>Fast shipping and quick customer support because your time matters.</p>
        </div>
      </div>
    </section>

    <!-- By The Numbers -->
    <section class="about-section">
      <h2>By The Numbers</h2>
      <div class="stats-grid">
        <div class="stat-item">
          <div class="stat-number">50K+</div>
          <div class="stat-label">Happy Customers</div>
        </div>
        <div class="stat-item">
          <div class="stat-number">10K+</div>
          <div class="stat-label">Products</div>
        </div>
        <div class="stat-item">
          <div class="stat-number">150+</div>
          <div class="stat-label">Sellers</div>
        </div>
        <div class="stat-item">
          <div class="stat-number">99.5%</div>
          <div class="stat-label">Satisfaction Rate</div>
        </div>
      </div>
    </section>

    <!-- Our Journey Timeline -->
    <section class="about-section">
      <h2>Our Journey</h2>
      <div class="timeline">
        <div class="timeline-item">
          <div class="timeline-dot"></div>
          <div class="timeline-content">
            <div class="timeline-year">2020</div>
            <div class="timeline-text">Sahara was founded by a team of passionate entrepreneurs</div>
          </div>
        </div>
        <div class="timeline-item">
          <div class="timeline-dot"></div>
          <div class="timeline-content">
            <div class="timeline-year">2021</div>
            <div class="timeline-text">Launched our platform with 500+ products and 10 sellers</div>
          </div>
        </div>
        <div class="timeline-item">
          <div class="timeline-dot"></div>
          <div class="timeline-content">
            <div class="timeline-year">2022</div>
            <div class="timeline-text">Reached 10K+ customers and expanded to 100+ sellers</div>
          </div>
        </div>
        <div class="timeline-item">
          <div class="timeline-dot"></div>
          <div class="timeline-content">
            <div class="timeline-year">2023</div>
            <div class="timeline-text">Implemented advanced features and improved logistics</div>
          </div>
        </div>
        <div class="timeline-item">
          <div class="timeline-dot"></div>
          <div class="timeline-content">
            <div class="timeline-year">2024</div>
            <div class="timeline-text">Celebrated 50K+ customers and launched mobile app</div>
          </div>
        </div>
        <div class="timeline-item">
          <div class="timeline-dot"></div>
          <div class="timeline-content">
            <div class="timeline-year">2025</div>
            <div class="timeline-text">Continuing to innovate with new features and better service</div>
          </div>
        </div>
      </div>
    </section>

    <!-- Team Section -->
    <section class="about-section">
      <h2>Meet Our Team</h2>
      <div class="team-grid">
        <div class="team-member">
          <div class="member-avatar">👔</div>
          <div class="member-name">Atif Chowdhury</div>
          <div class="member-role">Founder & CEO</div>
          <div class="member-bio">Visionary leader with 10+ years of e-commerce experience. Passionate about creating exceptional customer experiences.</div>
        </div>
        <div class="team-member">
          <div class="member-avatar">💻</div>
          <div class="member-name">Team Lead - Tech</div>
          <div class="member-role">Head of Engineering</div>
          <div class="member-bio">Expert in building scalable platforms. Leads the technical innovation at Sahara.</div>
        </div>
        <div class="team-member">
          <div class="member-avatar">📊</div>
          <div class="member-name">Team Lead - Operations</div>
          <div class="member-role">Head of Operations</div>
          <div class="member-bio">Ensures smooth operations and logistics. Dedicated to timely delivery and customer satisfaction.</div>
        </div>
        <div class="team-member">
          <div class="member-avatar">💬</div>
          <div class="member-name">Team Lead - Support</div>
          <div class="member-role">Head of Customer Support</div>
          <div class="member-bio">Your voice matters. Leads the team committed to solving your concerns 24/7.</div>
        </div>
      </div>
    </section>

    <!-- Call to Action -->
    <section class="about-section" style="text-align: center; padding: 60px 20px; background: var(--mantle); border-radius: 12px; border: 1px solid var(--surface0); margin-bottom: 40px;">
      <h2>Ready to Join Us?</h2>
      <p style="max-width: 600px; margin: 20px auto 40px;">
        Explore our diverse range of products or become a seller and grow your business with Sahara.
      </p>
      <div style="display: flex; gap: 20px; justify-content: center; flex-wrap: wrap;">
        <a href="shop.php" style="padding: 12px 30px; background: var(--blue); color: var(--base); text-decoration: none; border-radius: 8px; font-weight: 600; display: inline-block;">
          Start Shopping
        </a>
        <a href="seller.php" style="padding: 12px 30px; background: var(--surface0); color: var(--text); text-decoration: none; border-radius: 8px; font-weight: 600; border: 1px solid var(--surface1); display: inline-block;">
          Become a Seller
        </a>
      </div>
    </section>

  </main>

  <?php include 'partials/footer.html'; ?>
</body>

</html>
