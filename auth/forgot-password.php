<?php
require_once __DIR__ . '/../app/models/Auth.php';
require_once __DIR__ . '/../app/config/database.php';

if (isLoggedIn()) {
  header('Location: /index.php');
  exit;
}

$error = '';
$success = false;
$step = 'request'; // request, reset, confirm
$email_submitted = '';
$reset_token = '';

// Handle GET parameters for reset link
if (isset($_GET['token'])) {
  $reset_token = sanitizeInput($_GET['token']);
  $step = 'reset';
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $form_type = $_POST['form_type'] ?? '';

  if ($form_type === 'request_reset') {
    $email = sanitizeInput($_POST['email'] ?? '');

    if (empty($email)) {
      $error = 'Email address is required.';
    } elseif (!validateEmail($email)) {
      $error = 'Please enter a valid email address.';
    } else {
      $user = fetchOne("SELECT id, email FROM users WHERE email = '$email'");

      if (!$user) {
        $success = true;
        $step = 'confirm';
        $email_submitted = $email;
      } else {
        $reset_token = bin2hex(random_bytes(32));
        $reset_expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $result = query(
          "INSERT INTO remember_tokens (user_id, token, expires_at)
           VALUES ('{$user['id']}', '$reset_token', '$reset_expires')"
        );

        if (!$result) {
          $error = 'Failed to generate reset link. Please try again.';
        } else {
          $reset_url = "http://" . $_SERVER['HTTP_HOST'] . "/auth/forgot-password.php?token=" . $reset_token;
          error_log("Password Reset URL: " . $reset_url);

          $success = true;
          $step = 'confirm';
          $email_submitted = $email;
        }
      }
    }
  } elseif ($form_type === 'reset_password') {
    $token = sanitizeInput($_POST['token'] ?? '');
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($token)) {
      $error = 'Invalid reset token.';
    } elseif (empty($new_password) || empty($confirm_password)) {
      $error = 'Both password fields are required.';
    } elseif (strlen($new_password) < 8) {
      $error = 'Password must be at least 8 characters long.';
    } elseif ($new_password !== $confirm_password) {
      $error = 'Passwords do not match.';
    } else {
      // Validate token
      $token_data = fetchOne(
        "SELECT user_id, expires_at FROM remember_tokens WHERE token = '$token'"
      );

      if (!$token_data) {
        $error = 'Invalid or expired reset link.';
      } elseif (strtotime($token_data['expires_at']) < time()) {
        $error = 'Reset link has expired. Please request a new one.';
        query("DELETE FROM remember_tokens WHERE token = '$token'");
      } else {
        // Update password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_result = query(
          "UPDATE users SET password = '$hashed_password' WHERE id = '{$token_data['user_id']}'"
        );

        if (!$update_result) {
          $error = 'Failed to update password. Please try again.';
        } else {
          query("DELETE FROM remember_tokens WHERE token = '$token'");
          $success = true;
          $step = 'success';
        }
      }
    }
  }
}

// Check if reset token from URL is still valid
if ($step === 'reset' && !empty($reset_token)) {
  $token_data = fetchOne(
    "SELECT user_id, expires_at FROM remember_tokens WHERE token = '$reset_token'"
  );

  if (!$token_data || strtotime($token_data['expires_at']) < time()) {
    $error = 'Reset link has expired. Please request a new one.';
    $step = 'request';
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sahara | Forgot Password</title>
  <link rel="icon" href="/assets/favicon.ico" />
  <link rel="stylesheet" href="/css/auth.css" />
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
                Remember your password? <a class="link" href="login.php">Sign in</a>
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
              <a href="login.php" class="btn-primary">
                Back to Sign In
              </a>
              <a href="/auth/forgot-password.php" class="btn-social">
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
              <p><a class="link" href="login.php">Back to Sign In</a></p>
            </div>
          </form>


        <?php elseif ($step === 'success'): ?>
          <div class="success-message">
            <div class="success-icon">
              <span class="material-symbols-outlined">check_circle</span>
            </div>
            <h1>Password Reset Successfully!</h1>
            <p>Your password has been changed. You can now sign in with your new password.</p>
            <a href="login.php" class="btn-primary" style="display: inline-block; text-decoration: none; margin-top: 30px;">
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
