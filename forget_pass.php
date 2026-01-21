<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sahara | Forgot Password</title>
  <link rel="icon" href="assets/favicon.ico" />
  <link rel="stylesheet" href="css/auth.css" />
</head>

<body>
  <main class="auth-page">
    <div class="logo">
      <span>Sahara</span>
    </div>

    <div class="auth-container">
      <div class="auth-card">
        <div class="auth-header">
          <h1>Forgot Password?</h1>
          <p>No worries! Enter your email and we'll send you a link to your reset password</p>
        </div>

        <form class="auth-form" novalidate>
          <div class="form-group">
            <label for="email">Email Address</label>
            <input 
              type="email" 
              id="email" 
              name="email" 
              placeholder="Enter your registered email"
              required>
            <span class="error-message" id="email-error"></span>
          </div>

          <button type="submit" class="btn-primary">Send Reset Link</button>

          <div class="auth-divider">
            <span>OR</span>
          </div>

          <div class="auth-footer">
            <p>
              Remember your password? <a class="link" href="auth/login.php">Sign in</a>
            </p>
            <p>
              Don't have an account? <a class="link" href="auth/signup.php">Sign up</a>
            </p>
          </div>
        </form>
      </div>
    </div>
  </main>

  <script>
    document.querySelector('.auth-form').addEventListener('submit', (e) => {
      e.preventDefault();
      const email = document.getElementById('email').value;
      const errorEl = document.getElementById('email-error');

      // Simple validation
      if (!email) {
        errorEl.textContent = 'Email must be required';
        errorEl.classList.add('show');
        return;
      }

      if (!email.includes('@')) {
        errorEl.textContent = 'Please enter your valid email';
        errorEl.classList.add('show');
        return;
      }

      errorEl.classList.remove('show');
      alert('Reset link sent to: ' + email);
    });
  </script>
</body>

</html>
