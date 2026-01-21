<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sahara | Forgot Password</title>
  <link rel="icon" href="/views/assets/favicon.ico" />
  <link rel="stylesheet" href="/views/css/auth.css" />
</head>

<body>
  <main class="auth-page">
    <div class="logo">
      <span>Sahara</span>
    </div>

    <div class="auth-container">
      <div class="auth-card">


        <?php if ($step === 'request'): ?>
          <div class="auth-header">
            <h1>Reset Your Password</h1>
            <p>Enter your email address and we'll send you a link to reset your password</p>
          </div>

          <?php if ($error): ?>
            <div class="alert alert-error">
              <span class="material-symbols-outlined">error</span>
              <span><?php echo $error; ?></span>
            </div>
          <?php endif; ?>

          <form method="post" class="auth-form" novalidate>
            <input type="hidden" name="form_type" value="request_reset">

            <div class="form-group">
              <label for="email">Email Address</label>
              <input type="email" name="email" id="email" placeholder="Enter your registered email"
                value="<?php echo $_POST['email'] ?? ''; ?>" required>
              <span class="error-message" id="email-error"></span>
            </div>

            <button type="submit" class="btn-primary">Send Reset Link</button>

            <div class="auth-footer">
              <p>
                Remember your password? <a class="link" href="/auth.php?page=login">Sign in</a>
              </p>
            </div>
          </form>


        <?php elseif ($step === 'confirm'): ?>
          <div class="success-message">
            <div class="success-icon">
              <span class="material-symbols-outlined">mail</span>
            </div>
            <h1>Check Your Email</h1>
            <p>We've sent a password reset link to <strong><?php echo $email_submitted; ?></strong></p>
            <p style="color: var(--subtext0); margin-top: 20px;">
              The link will expire in 1 hour. If you don't see the email, check your spam folder.
            </p>
            <div class="btn-group">
              <a href="/auth.php?page=login" class="btn-primary">
                Back to Sign In
              </a>
              <a href="/auth.php?page=forgot-password" class="btn-social">
                Try Another Email
              </a>
            </div>
          </div>


        <?php elseif ($step === 'reset'): ?>
          <div class="auth-header">
            <h1>Create New Password</h1>
            <p>Enter your new password below</p>
          </div>

          <?php if ($error): ?>
            <div class="alert alert-error">
              <span class="material-symbols-outlined">error</span>
              <span><?php echo $error; ?></span>
            </div>
          <?php endif; ?>

          <form method="post" class="auth-form" novalidate>
            <input type="hidden" name="form_type" value="reset_password">
            <input type="hidden" name="token" value="<?php echo $reset_token; ?>">

            <div class="form-group">
              <label for="new_password">New Password</label>
              <div class="password-container">
                <input type="password" name="new_password" id="new_password"
                  placeholder="Create a new password" minlength="8" required>
                <button type="button" class="toggle-password" data-target="new_password">
                  <span class="material-symbols-outlined">visibility</span>
                </button>
              </div>
              <span class="error-message" id="password-error"></span>
              <small style="color: var(--subtext0); font-size: 12px; margin-top: 4px; display: block;">
                Minimum 8 characters
              </small>
            </div>

            <div class="form-group">
              <label for="confirm_password">Confirm Password</label>
              <div class="password-container">
                <input type="password" name="confirm_password" id="confirm_password"
                  placeholder="Re-enter your password" minlength="8" required>
                <button type="button" class="toggle-password" data-target="confirm_password">
                  <span class="material-symbols-outlined">visibility</span>
                </button>
              </div>
              <span class="error-message" id="confirm-password-error"></span>
            </div>

            <button type="submit" class="btn-primary">Reset Password</button>

            <div class="auth-footer">
              <p><a class="link" href="/auth.php?page=login">Back to Sign In</a></p>
            </div>
          </form>


        <?php elseif ($step === 'success'): ?>
          <div class="success-message">
            <div class="success-icon">
              <span class="material-symbols-outlined">check_circle</span>
            </div>
            <h1>Password Reset Successfully!</h1>
            <p>Your password has been changed. You can now sign in with your new password.</p>
            <a href="/auth.php?page=login" class="btn-primary" style="display: inline-block; text-decoration: none; margin-top: 30px;">
              Go to Sign In
            </a>
          </div>
        <?php endif; ?>

      </div>
    </div>
  </main>

  <script>
    // Toggle password visibility
    document.querySelectorAll('.toggle-password').forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        const target = btn.dataset.target;
        const input = document.getElementById(target);
        const icon = btn.querySelector('span');

        if (input.type === 'password') {
          input.type = 'text';
          icon.textContent = 'visibility_off';
        } else {
          input.type = 'password';
          icon.textContent = 'visibility';
        }
      });
    });
  </script>

</body>

</html>
