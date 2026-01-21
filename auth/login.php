<?php
require_once __DIR__ . '/../app/models/Auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
  header('Location: /index.php');
  exit;
}

// Handle POST request (controller logic)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  require_once __DIR__ . '/../app/controllers/auth/LoginController.php';
  exit;
}

// Prepare variables for view
$error = $_SESSION['login_error'] ?? '';
$success = false;
$showResendLink = false;
$userEmail = '';

// Clear error from session
unset($_SESSION['login_error']);

// Include view
include __DIR__ . '/../app/views/auth/login.php';
