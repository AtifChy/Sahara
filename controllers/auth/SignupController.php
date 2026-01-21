<?php
require_once __DIR__ . '/../../models/Auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = sanitizeInput($_POST['name'] ?? '');
  $email = sanitizeInput($_POST['email'] ?? '');
  $phone = sanitizeInput($_POST['phone'] ?? '');
  $gender = $_POST['gender'] ?? '';
  $password = $_POST['password'] ?? '';
  $confirmPassword = $_POST['confirm_password'] ?? '';
  $terms = isset($_POST['terms']);
  $newsletter = isset($_POST['newsletter']);

  if ($password !== $confirmPassword) {
    $_SESSION['signup_error'] = 'Passwords do not match.';
  } else if (!$terms) {
    $_SESSION['signup_error'] = 'You must agree to the Terms of Service and Privacy Policy.';
  } else {
    $result = registerUser($name, $email, $phone, $gender, $password, $newsletter);

    if ($result['success']) {
      $_SESSION['signup_success'] = true;
    } else {
      $_SESSION['signup_error'] = $result['message'];
    }
  }

  $_SESSION['signup_form_data'] = $_POST;

  header('Location: /auth.php?page=signup');
  exit;
}
