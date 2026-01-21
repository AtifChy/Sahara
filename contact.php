<?php session_start(); ?>

<!DOCTYPE HTML>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sahara | Contact</title>
  <link rel="icon" href="assets/favicon.ico">
  <link rel="stylesheet" href="css/main.css" />
  <style>
    .contact-hero {
      background: linear-gradient(135deg, var(--mauve) 0%, var(--red) 100%);
      padding: 80px 20px;
      text-align: center;
      border-radius: 20px;
      color: var(--base);
      margin-bottom: 60px;
    }

    .contact-hero h1 {
      font-size: 48px;
      font-weight: 700;
      margin-bottom: 20px;
      line-height: 1.2;
      color: inherit;
    }

    .contact-hero p {
      font-size: 18px;
      max-width: 700px;
      margin: 0 auto;
      opacity: 0.95;
      color: inherit;
      margin-bottom: 40px;
    }

    .contact-container {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 60px;
      margin-bottom: 60px;
    }

    .contact-info-section h2 {
      font-size: 28px;
      font-weight: 700;
      margin-bottom: 30px;
      color: var(--text);
    }

    .contact-info-item {
      margin-bottom: 40px;
      display: flex;
      gap: 20px;
    }

    .contact-info-icon {
      width: 50px;
      height: 50px;
      background: var(--surface0);
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      font-size: 24px;
    }

    .contact-info-item h3 {
      font-size: 18px;
      font-weight: 600;
      margin-bottom: 8px;
      color: var(--text);
    }

    .contact-info-item p {
      font-size: 14px;
      color: var(--subtext0);
      margin: 0;
      line-height: 1.6;
    }

    .contact-info-item a {
      color: var(--blue);
      text-decoration: none;
      transition: color 0.2s ease;
    }

    .contact-info-item a:hover {
      color: var(--sapphire);
      text-decoration: underline;
    }

    .contact-form-section {
      background: var(--mantle);
      border: 1px solid var(--surface0);
      padding: 40px;
      border-radius: 16px;
    }

    .contact-form-section h2 {
      font-size: 28px;
      font-weight: 700;
      margin-bottom: 30px;
      color: var(--text);
    }

    .contact-form {
      display: flex;
      flex-direction: column;
      gap: 20px;
    }

    .form-group {
      display: flex;
      flex-direction: column;
      gap: 8px;
    }

    .form-group label {
      font-size: 14px;
      font-weight: 600;
      color: var(--text);
    }

    .form-group input,
    .form-group textarea,
    .form-group select {
      padding: 12px 16px;
      border: 1px solid var(--surface0);
      border-radius: 8px;
      background: var(--crust);
      color: var(--text);
      font-family: inherit;
      font-size: 14px;
      transition: border-color 0.2s ease;
    }

    .form-group input:focus,
    .form-group textarea:focus,
    .form-group select:focus {
      outline: none;
      border-color: var(--blue);
    }

    .form-group input::placeholder,
    .form-group textarea::placeholder {
      color: var(--overlay0);
    }

    .form-group textarea {
      resize: vertical;
      min-height: 120px;
    }

    .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
    }

    .form-actions {
      display: flex;
      gap: 12px;
      margin-top: 10px;
    }

    .btn-submit {
      padding: 12px 24px;
      background: var(--blue);
      color: var(--base);
      border: none;
      border-radius: 8px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      transition: background 0.2s ease, transform 0.15s ease;
    }

    .btn-submit:hover {
      background: var(--sapphire);
    }

    .btn-submit:active {
      transform: scale(0.98);
    }

    .btn-reset {
      padding: 12px 24px;
      background: var(--surface0);
      color: var(--text);
      border: 1px solid var(--surface1);
      border-radius: 8px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      transition: background 0.2s ease;
    }

    .btn-reset:hover {
      background: var(--surface1);
    }

    .social-links {
      display: flex;
      gap: 16px;
      margin-top: 30px;
      padding-top: 30px;
      border-top: 1px solid var(--surface0);
    }

    .social-link {
      width: 40px;
      height: 40px;
      background: var(--surface0);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      text-decoration: none;
      color: var(--text);
      transition: background 0.2s ease, color 0.2s ease;
    }

    .social-link:hover {
      background: var(--blue);
      color: var(--base);
    }

    .faq-section {
      margin-top: 80px;
    }

    .faq-section h2 {
      font-size: 32px;
      font-weight: 700;
      margin-bottom: 30px;
      text-align: center;
      color: var(--text);
    }

    .faq-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 30px;
    }

    .faq-item {
      background: var(--mantle);
      border: 1px solid var(--surface0);
      padding: 24px;
      border-radius: 12px;
      cursor: pointer;
      transition: border-color 0.2s ease;
    }

    .faq-item:hover {
      border-color: var(--blue);
    }

    .faq-item.active {
      border-color: var(--blue);
      background: color-mix(in srgb, var(--blue) 5%, var(--mantle));
    }

    .faq-question {
      font-size: 16px;
      font-weight: 600;
      color: var(--text);
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 12px;
    }

    .faq-icon {
      font-size: 20px;
      transition: transform 0.3s ease;
    }

    .faq-item.active .faq-icon {
      transform: rotate(180deg);
    }

    .faq-answer {
      font-size: 14px;
      color: var(--subtext0);
      line-height: 1.6;
      display: none;
    }

    .faq-item.active .faq-answer {
      display: block;
    }

    .success-message {
      padding: 16px;
      background: color-mix(in srgb, var(--green) 10%, transparent);
      border: 1px solid var(--green);
      border-radius: 8px;
      color: var(--green);
      margin-bottom: 20px;
      display: none;
      font-size: 14px;
      align-items: center;
      gap: 12px;
    }

    .success-message.show {
      display: flex;
    }

    @media (max-width: 768px) {
      .contact-hero h1 {
        font-size: 32px;
      }

      .contact-container {
        grid-template-columns: 1fr;
        gap: 40px;
      }

      .form-row {
        grid-template-columns: 1fr;
      }

      .faq-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>

<body>
  <?php include 'partials/header.php'; ?>

  <main>
    <!-- Hero Section -->
    <section class="contact-hero">
      <h1>Get In Touch</h1>
      <p>We'd love to hear from you. Whether you have a question, feedback, or just want to say hello, feel free to reach out!</p>
    </section>

    <!-- Contact Container -->
    <section class="contact-container">
      <!-- Contact Information -->
      <div class="contact-info-section">
        <h2>Contact Information</h2>

        <div class="contact-info-item">
          <div class="contact-info-icon">📍</div>
          <div>
            <h3>Address</h3>
            <p>
              Sahara E-Commerce<br>
              Commerce Street<br>
              Dhaka,Bangladesh 1205<br>
              Bangladesh
            </p>
          </div>
        </div>

        <div class="contact-info-item">
          <div class="contact-info-icon">📞</div>
          <div>
            <h3>Phone</h3>
            <p>
              <a href="tel:+8801766649703">+880 1766649703</a><br>
              <a href="tel:+88098764567">+880 9876 4567</a><br>
              <span style="color: var(--subtext0);">Sunday - Thursday, 9:00 AM - 6:00 PM</span>
            </p>
          </div>
        </div>

        <div class="contact-info-item">
          <div class="contact-info-icon">✉️</div>
          <div>
            <h3>Email</h3>
            <p>
              <a href="mailto:support@sahara.com">support@sahara.com</a><br>
              <a href="mailto:info@sahara.com">info@sahara.com</a><br>
              <a href="mailto:ekramulhasib@gmail.com">ekramulhasib@gmail.com</a>
            </p>
          </div>
        </div>

        <div class="contact-info-item">
          <div class="contact-info-icon">⏰</div>
          <div>
            <h3>Business Hours</h3>
            <p>
              Sunday - Thursday: 9:00 AM - 6:00 PM<br>
              Saturday: Closed<br>
              Friday: Closed
            </p>
          </div>
        </div>

        <div class="social-links">
          <a href="#" class="social-link" title="Facebook">f</a>
          <a href="#" class="social-link" title="Twitter">𝕏</a>
          <a href="#" class="social-link" title="Instagram">📷</a>
          <a href="#" class="social-link" title="LinkedIn">in</a>
        </div>
      </div>

      <!-- Contact Form -->
      <div class="contact-form-section">
        <h2>Send us a Message</h2>

        <div class="success-message" id="successMessage">
          <span>✓</span>
          <span>Thank you! Your message has been sent successfully. We'll get back to you soon!</span>
        </div>

        <form class="contact-form" id="contactForm" novalidate>
          <div class="form-row">
            <div class="form-group">
              <label for="name">Full Name</label>
              <input
                type="text"
                id="name"
                name="name"
                placeholder="Ekramul Hasib"
                required>
            </div>
            <div class="form-group">
              <label for="email">Email Address</label>
              <input
                type="email"
                id="email"
                name="email"
                placeholder="ekramulhasib@gmail.com"
                required>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="phone">Phone Number</label>
              <input
                type="tel"
                id="phone"
                name="phone"
                placeholder="+880 1766649703">
            </div>
            <div class="form-group">
              <label for="subject">Subject</label>
              <select id="subject" name="subject" required>
                <option value="">Select a subject</option>
                <option value="general">General Inquiry</option>
                <option value="support">Customer Support</option>
                <option value="seller">Seller Inquiry</option>
                <option value="feedback">Feedback</option>
                <option value="partnership">Partnership</option>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label for="message">Message</label>
            <textarea
              id="message"
              name="message"
              placeholder="Type your message here..."
              required></textarea>
          </div>

          <div class="form-actions">
            <button type="submit" class="btn-submit">Send Message</button>
            <button type="reset" class="btn-reset">Clear</button>
          </div>
        </form>
      </div>
    </section>

    <!-- FAQ Section -->
    <section class="faq-section">
      <h2>Frequently Asked Questions</h2>
      <div class="faq-grid">
        <div class="faq-item">
          <div class="faq-question">
            <span>How can I track my order?</span>
            <span class="faq-icon">⌄</span>
          </div>
          <div class="faq-answer">
            You can track your order using the tracking number sent to your email after shipment. Visit our Orders page and enter your order ID to see real-time updates.
          </div>
        </div>

        <div class="faq-item">
          <div class="faq-question">
            <span>What is your return policy?</span>
            <span class="faq-icon">⌄</span>
          </div>
          <div class="faq-answer">
            We offer a 30-day return policy on most items. Products must be in original condition with original packaging. Contact our support team to initiate a return.
          </div>
        </div>

        <div class="faq-item">
          <div class="faq-question">
            <span>How long does shipping take?</span>
            <span class="faq-icon">⌄</span>
          </div>
          <div class="faq-answer">
            Standard shipping takes 3-7 business days. Express shipping is available for 1-2 business days. Shipping time may vary depending on your location.
          </div>
        </div>

        <div class="faq-item">
          <div class="faq-question">
            <span>Do you offer international shipping?</span>
            <span class="faq-icon">⌄</span>
          </div>
          <div class="faq-answer">
            Yes, we ship to over 100 countries. International shipping rates and times vary by location. You can check shipping costs at checkout.
          </div>
        </div>

        <div class="faq-item">
          <div class="faq-question">
            <span>How can I become a seller on Sahara?</span>
            <span class="faq-icon">⌄</span>
          </div>
          <div class="faq-answer">
            To become a seller, visit our Seller page and fill out the application form. We review all applications within 5-7 business days.
          </div>
        </div>

        <div class="faq-item">
          <div class="faq-question">
            <span>What payment methods do you accept?</span>
            <span class="faq-icon">⌄</span>
          </div>
          <div class="faq-answer">
            We accept all major credit cards, debit cards, digital wallets, and bank transfers. All payments are secured with SSL encryption.
          </div>
        </div>
      </div>
    </section>

  </main>

  <?php include 'partials/footer.html'; ?>

  <script>
    // FAQ Toggle
    document.querySelectorAll('.faq-item').forEach(item => {
      item.addEventListener('click', () => {
        // Close other items
        document.querySelectorAll('.faq-item').forEach(otherItem => {
          if (otherItem !== item) {
            otherItem.classList.remove('active');
          }
        });
        // Toggle current item
        item.classList.toggle('active');
      });
    });

    // Form Submission
    document.getElementById('contactForm').addEventListener('submit', (e) => {
      e.preventDefault();

      const name = document.getElementById('name').value.trim();
      const email = document.getElementById('email').value.trim();
      const subject = document.getElementById('subject').value;
      const message = document.getElementById('message').value.trim();

      if (!name || !email || !subject || !message) {
        alert('Please fill in all required fields');
        return;
      }

      if (!email.includes('@')) {
        alert('Please enter a valid email address');
        return;
      }

      // Show success message
      const successMsg = document.getElementById('successMessage');
      successMsg.classList.add('show');

      // Reset form
      document.getElementById('contactForm').reset();

      // Hide success message after 5 seconds
      setTimeout(() => {
        successMsg.classList.remove('show');
      }, 5000);
    });
  </script>
</body>

</html>
