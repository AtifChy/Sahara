<?php
require_once __DIR__ . '/../app/models/Auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
  header('Location: /index.php');
  exit;
}

// Handle POST request (controller logic)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  require_once __DIR__ . '/../app/controllers/auth/SignupController.php';
  exit;
}

// Prepare variables for view
$error = $_SESSION['signup_error'] ?? '';
$success = $_SESSION['signup_success'] ?? false;
$formData = $_SESSION['signup_form_data'] ?? [];

// Clear session variables
unset($_SESSION['signup_error']);
unset($_SESSION['signup_success']);
unset($_SESSION['signup_form_data']);

// Include view
include __DIR__ . '/../app/views/auth/signup.php';
