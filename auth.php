<?php
require_once __DIR__ . '/models/Auth.php';

if (isLoggedIn()) {
  header('Location: /index.php');
  exit;
}

$allowed_pages = ['login', 'signup', 'forgot-password'];
$page = $_GET['page'] ?? 'login';

if (!in_array($page, $allowed_pages)) {
  $page = 'login';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $controller_map = [
    'login' => __DIR__ . '/controllers/auth/LoginController.php',
    'signup' => __DIR__ . '/controllers/auth/SignupController.php',
    'forgot-password' => __DIR__ . '/controllers/auth/ForgotPasswordController.php'
  ];

  if (isset($controller_map[$page])) {
    require_once $controller_map[$page];
    exit;
  }
}

if ($page === 'login') {
  $error = $_SESSION['login_error'] ?? '';
  $success = false;
  $userEmail = '';
  unset($_SESSION['login_error']);

  include __DIR__ . '/views/auth/login.php';
} elseif ($page === 'signup') {
  $error = $_SESSION['signup_error'] ?? '';
  $success = $_SESSION['signup_success'] ?? false;
  $formData = $_SESSION['signup_form_data'] ?? [];

  unset($_SESSION['signup_error']);
  unset($_SESSION['signup_success']);
  unset($_SESSION['signup_form_data']);

  include __DIR__ . '/views/auth/signup.php';
} elseif ($page === 'forgot-password') {
  require_once __DIR__ . '/controllers/auth/ForgotPasswordController.php';
}
