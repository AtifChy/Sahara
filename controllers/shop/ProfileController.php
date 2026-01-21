<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../../models/Auth.php';
require_once __DIR__ . '/../../models/User.php';

if (!isLoggedIn()) {
  header('Location: /auth.php?page=login');
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: /profile.php');
  exit;
}

$user_id = $_SESSION['user_id'];
$form_type = $_POST['form_type'] ?? '';
$success_message = '';
$error_message = '';

switch ($form_type) {
  case 'personal_info':
    handlePersonalInfo($user_id);
    break;

  case 'profile_picture':
    handleProfilePicture($user_id);
    break;

  case 'password_change':
    handlePasswordChange($user_id);
    break;

  case 'address':
    handleAddress($user_id);
    break;

  default:
    $_SESSION['error_message'] = 'Invalid form submission';
    header('Location: /profile.php');
    exit;
}

function handlePersonalInfo($user_id)
{
  $first_name = $_POST['first_name'] ?? '';
  $last_name = $_POST['last_name'] ?? '';
  $phone = $_POST['phone'] ?? '';

  if (empty($first_name)) {
    $_SESSION['error_message'] = 'First name is required';
    header('Location: /profile.php');
    exit;
  }

  if (!empty($phone) && !validatePhone($phone)) {
    $_SESSION['error_message'] = 'Invalid phone number format';
    header('Location: /profile.php');
    exit;
  }

  $result = updateUserProfile($user_id, $first_name, $last_name, $phone);

  if ($result) {
    $_SESSION['user_fname'] = $first_name;
    $_SESSION['user_lname'] = $last_name;
    $_SESSION['success_message'] = 'Personal information updated successfully!';
  } else {
    $_SESSION['error_message'] = 'Failed to update personal information. Please try again.';
  }

  header('Location: /profile.php');
  exit;
}

function handleProfilePicture($user_id)
{
  if (!isset($_FILES['profile_picture']) || $_FILES['profile_picture']['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['error_message'] = 'No file uploaded or upload error occurred';
    header('Location: /profile.php');
    exit;
  }

  $file = $_FILES['profile_picture'];
  $allowed_types = ['image/jpeg', 'image/png'];
  $max_size = 5 * 1024 * 1024; // 5MB

  if (!in_array($file['type'], $allowed_types)) {
    $_SESSION['error_message'] = 'Invalid file type. Only JPG and PNG are allowed.';
    header('Location: /profile.php');
    exit;
  }

  if ($file['size'] > $max_size) {
    $_SESSION['error_message'] = 'File size exceeds the maximum limit of 5MB.';
    header('Location: /profile.php');
    exit;
  }

  $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
  $new_filename = 'user_' . uniqid() . '.' . $ext;
  $upload_dir = __DIR__ . '/../../uploads/avatars/';
  $upload_path = $upload_dir . $new_filename;

  if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
  }

  if (move_uploaded_file($file['tmp_name'], $upload_path)) {
    $user_data = getUserData($user_id);
    if (!empty($user_data['picture'])) {
      $old_picture_path = __DIR__ . '/../../uploads/avatars/' . $user_data['picture'];
      if (file_exists($old_picture_path)) {
        unlink($old_picture_path);
      }
    }

    updateUserPicture($user_id, $new_filename);
    $_SESSION['success_message'] = 'Profile picture updated successfully!';
  } else {
    $_SESSION['error_message'] = 'Failed to upload profile picture. Please try again.';
  }

  header('Location: /profile.php');
  exit;
}

function handlePasswordChange($user_id)
{
  $current_password = $_POST['current_password'] ?? '';
  $new_password = $_POST['new_password'] ?? '';
  $confirm_password = $_POST['confirm_password'] ?? '';

  if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
    $_SESSION['error_message'] = 'All password fields are required.';
    header('Location: /profile.php');
    exit;
  }

  if ($new_password !== $confirm_password) {
    $_SESSION['error_message'] = 'New password and confirmation do not match.';
    header('Location: /profile.php');
    exit;
  }

  if (strlen($new_password) < 8) {
    $_SESSION['error_message'] = 'New password must be at least 8 characters long.';
    header('Location: /profile.php');
    exit;
  }

  $user = getUserPassword($user_id);
  if (!$user || !password_verify($current_password, $user['password'])) {
    $_SESSION['error_message'] = 'Current password is incorrect.';
    header('Location: /profile.php');
    exit;
  }

  $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
  updateUserPassword($user_id, $hashed_password);
  $_SESSION['success_message'] = 'Password changed successfully!';

  header('Location: /profile.php');
  exit;
}

function handleAddress($user_id)
{
  $address = $_POST['address'] ?? '';

  updateUserAddress($user_id, $address);
  $_SESSION['success_message'] = 'Shipping address updated successfully!';

  header('Location: /profile.php');
  exit;
}
