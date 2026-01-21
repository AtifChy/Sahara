<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../../models/Auth.php';

if (isLoggedIn()) {
  header('Location: /index.php');
  exit;
}

$error = '';
$success = false;
$step = 'request'; // request, reset, confirm, success
$email_submitted = '';
$reset_token = '';

if (isset($_GET['token'])) {
  $reset_token = sanitizeInput($_GET['token']);
  $step = 'reset';
}

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
          $reset_url = "http://" . $_SERVER['HTTP_HOST'] . "/auth.php?page=forgot-password&token=" . $reset_token;
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
      $token_data = fetchOne(
        "SELECT user_id, expires_at FROM remember_tokens WHERE token = '$token'"
      );

      if (!$token_data) {
        $error = 'Invalid or expired reset link.';
      } elseif (strtotime($token_data['expires_at']) < time()) {
        $error = 'Reset link has expired. Please request a new one.';
        query("DELETE FROM remember_tokens WHERE token = '$token'");
      } else {
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

if ($step === 'reset' && !empty($reset_token)) {
  $token_data = fetchOne(
    "SELECT user_id, expires_at FROM remember_tokens WHERE token = '$reset_token'"
  );

  if (!$token_data || strtotime($token_data['expires_at']) < time()) {
    $error = 'Reset link has expired. Please request a new one.';
    $step = 'request';
  }
}

include __DIR__ . '/../../views/auth/forgot-password.php';
