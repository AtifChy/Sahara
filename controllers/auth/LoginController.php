<?php
require_once __DIR__ . '/../../models/Auth.php';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = sanitizeInput($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';
  $remember = isset($_POST['remember']);

  $result = authenticateUser($email, $password);
  if (!$result['success']) {
    $_SESSION['login_error'] = $result['message'];
    header('Location: /auth.php?page=login');
    exit;
  } else {
    createUserSession($result['user'], $remember);
    header('Location: /index.php');
    exit;
  }
}
