<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

if (!isLoggedIn()) {
  header('Location: /auth/login.php');
  exit;
}

$user_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

$user_data = getUserData($user_id);

function getUserData($user_id)
{
  return fetchOne("
    SELECT
      u.id,
      u.email,
      u.role,
      p.first_name,
      p.last_name,
      p.phone,
      p.gender,
      p.address,
      p.picture
    FROM users u
    JOIN user_profiles p ON u.id = p.user_id
    WHERE u.id = {$user_id}
  ");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $form_type = $_POST['form_type'] ?? '';

  if ($form_type === 'personal_info') {
    $first_name = sanitizeInput($_POST['first_name'] ?? '');
    $last_name = sanitizeInput($_POST['last_name'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');

    if (empty($first_name)) {
      $error_message = 'First name is required';
    } elseif (!empty($phone) && !validatePhone($phone)) {
      $error_message = 'Invalid phone number format';
    } else {
      $result = query("
          UPDATE user_profiles
          SET first_name = '$first_name',
              last_name = '$last_name',
              phone = '$phone'
          WHERE user_id = '$user_id'
        ");

      if ($result) {
        $_SESSION['user_fname'] = $first_name;
        $_SESSION['user_lname'] = $last_name;
        $success_message = 'Personal information updated successfully!';
        $user_data = getUserData($user_id);
      } else {
        $error_message = 'Failed to update personal information. Please try again.';
      }
    }
  } else if ($form_type === 'profile_picture') {
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
      $file = $_FILES['profile_picture'];
      $allowed_types = ['image/jpeg', 'image/png'];
      $max_size = 5 * 1024 * 1024;

      if (!in_array($file['type'], $allowed_types)) {
        $error_message = 'Invalid file type. Only JPG and PNG are allowed.';
      } elseif ($file['size'] > $max_size) {
        $error_message = 'File size exceeds the maximum limit of 5MB.';
      } else {
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $new_filename = 'user_' . uniqid() . '.' . $ext;
        $upload_path = '/uploads/avatars/' . $new_filename;

        if (move_uploaded_file($file['tmp_name'], $upload_path)) {
          if (!empty($user_data['picture']) && file_exists($user_data['picture'])) {
            unlink($user_data['picture']);
          }
          query("
            UPDATE user_profiles
            SET picture = '$new_filename'
            WHERE user_id = '$user_id'
          ");
          $success_message = 'Profile picture updated successfully!';
          $user_data = getUserData($user_id);
        } else {
          $error_message = 'Failed to upload profile picture. Please try again.';
        }
      }
    }
  } else if ($form_type === 'password_change') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
      $error_message = 'All password fields are required.';
    } elseif ($new_password !== $confirm_password) {
      $error_message = 'New password and confirmation do not match.';
    } elseif (strlen($new_password) < 8) {
      $error_message = 'New password must be at least 8 characters long.';
    } else {
      $user = fetchOne("SELECT password FROM users WHERE id = '$user_id'");
      if ($user && password_verify($current_password, $user['password'])) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        query("UPDATE users SET password = '$hashed_password' WHERE id = '$user_id'");
        $success_message = 'Password changed successfully!';
      } else {
        $error_message = 'Current password is incorrect.';
      }
    }
  } else if ($form_type === 'address') {
    $address = sanitizeInput($_POST['address'] ?? '');

    query("
      UPDATE user_profiles
      SET address = '$address'
      WHERE user_id = '$user_id'
    ");

    $success_message = 'Shipping address updated successfully!';
    $user_data = getUserData($user_id);
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>My Profile - Sahara</title>
  <link rel="icon" href="assets/favicon.ico">
  <link rel="stylesheet" href="css/colors.css" />
  <link rel="stylesheet" href="css/main.css" />
  <link rel="stylesheet" href="css/role.css" />
  <script type="module" src="./js/profile.js"></script>
</head>

<body>
  <?php include 'partials/header.php'; ?>

  <div class="role-layout">
    <aside class="role-sidebar">
      <div class="sidebar-header">
        <h2>Account Settings</h2>
      </div>

      <nav class="role-nav">
        <a href="/profile.php" class="role-nav-item active">
          <span class="material-symbols-outlined">person</span>
          Profile
        </a>
        <a href="/orders.php" class="role-nav-item">
          <span class="material-symbols-outlined">receipt_long</span>
          My Orders
        </a>
        <a href="/wishlist.php" class="role-nav-item">
          <span class="material-symbols-outlined">favorite</span>
          Wishlist
        </a>
      </nav>
    </aside>

    <main class="role-content">
      <div class="role-header">
        <h1>My Profile</h1>
        <p>Manage your personal information and account settings</p>
      </div>

      <?php if ($success_message): ?>
        <div class="alert alert-success">
          <span class="material-symbols-outlined">check_circle</span>
          <?php echo $success_message; ?>
        </div>
      <?php endif; ?>

      <?php if ($error_message): ?>
        <div class="alert alert-error">
          <span class="material-symbols-outlined">error</span>
          <?php echo $error_message; ?>
        </div>
      <?php endif; ?>

      <div class="section-card">
        <div class="section-card-header">
          <h2 class="section-card-title">Personal Information</h2>
        </div>
        <div class="section-card-body">
          <form method="POST" action="/profile.php" class="profile-form">
            <input type="hidden" name="form_type" value="personal_info">

            <div class="form-row">
              <div class="form-group">
                <label for="first_name" class="form-label">First Name</label>
                <input
                  type="text"
                  id="first_name"
                  name="first_name"
                  class="form-control"
                  value="<?php echo $user_data['first_name'] ?? ''; ?>"
                  required>
              </div>

              <div class="form-group">
                <label for="last_name" class="form-label">Last Name</label>
                <input
                  type="text"
                  id="last_name"
                  name="last_name"
                  class="form-control"
                  value="<?php echo $user_data['last_name'] ?? ''; ?>">
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <input
                  type="email"
                  id="email"
                  name="email"
                  class="form-control"
                  value="<?php echo $user_data['email'] ?? ''; ?>"
                  readonly
                  disabled
                  style="opacity: 0.6; cursor: not-allowed;">
                <small style="color: var(--subtext0); font-size: 12px; margin-top: 4px; display: block;">
                  Email cannot be changed
                </small>
              </div>

              <div class="form-group">
                <label for="phone" class="form-label">Phone Number</label>
                <input
                  type="tel"
                  id="phone"
                  name="phone"
                  class="form-control"
                  value="<?php echo $user_data['phone'] ?? ''; ?>"
                  placeholder="+880 123-4567">
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">Account Role</label>
              <div>
                <span class="badge badge-active" style="text-transform: uppercase; letter-spacing: 0.5px;">
                  <?php echo strtolower($user_data['role'] ?? 'Customer'); ?>
                </span>
              </div>
            </div>

            <div class="form-actions">
              <button type="submit" class="btn-primary">
                <span class="material-symbols-outlined">save</span>
                Save Changes
              </button>
            </div>
          </form>
        </div>
      </div>

      <div class="section-card">
        <div class="section-card-header">
          <h2 class="section-card-title">Profile Picture</h2>
        </div>
        <div class="section-card-body">
          <form method="POST" action="/profile.php" enctype="multipart/form-data" class="profile-form" id="pictureForm">
            <input type="hidden" name="form_type" value="profile_picture">

            <div class="profile-picture-section">
              <div class="avatar-preview">
                <?php if (!empty($user_data['picture']) && file_exists($user_data['picture'])): ?>
                  <img src="/<?php echo $user_data['picture']; ?>" alt="Profile Picture" id="avatarImage">
                <?php else: ?>
                  <span class="material-symbols-outlined" id="avatarIcon">account_circle</span>
                <?php endif; ?>
              </div>
              <div class="avatar-upload">
                <p style="margin-bottom: 12px; color: var(--text);">
                  Upload a new profile picture
                </p>
                <p style="margin-bottom: 16px; color: var(--subtext0); font-size: 13px;">
                  JPG, PNG. Max size 5MB.
                </p>
                <input
                  type="file"
                  id="profile_picture"
                  name="profile_picture"
                  accept="image/jpeg,image/png"
                  class="file-input">
                <label for="profile_picture" class="btn-secondary">
                  <span class="material-symbols-outlined">upload</span>
                  Choose File
                </label>
                <span id="fileName" style="color: var(--green); font-size: 14px;"></span>
              </div>
            </div>

            <div class="form-actions">
              <button type="submit" class="btn-primary" id="uploadBtn" disabled>
                <span class="material-symbols-outlined">save</span>
                Upload Picture
              </button>
            </div>
          </form>
        </div>
      </div>

      <div class="section-card">
        <div class="section-card-header">
          <h2 class="section-card-title">Change Password</h2>
        </div>
        <div class="section-card-body">
          <form method="POST" action="/profile.php" class="profile-form">
            <input type="hidden" name="form_type" value="password_change">

            <div class="form-group">
              <label for="current_password" class="form-label">Current Password</label>
              <div class="password-input-wrapper">
                <input
                  type="password"
                  id="current_password"
                  name="current_password"
                  class="form-control"
                  required>
                <button type="button" class="password-toggle">
                  <span class="material-symbols-outlined">visibility</span>
                </button>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label for="new_password" class="form-label">New Password</label>
                <div class="password-input-wrapper">
                  <input
                    type="password"
                    id="new_password"
                    name="new_password"
                    class="form-control"
                    minlength="8"
                    required>
                  <button type="button" class="password-toggle">
                    <span class="material-symbols-outlined">visibility</span>
                  </button>
                </div>
                <span class="error-message" id="new-password-error"></span>
              </div>

              <div class="form-group">
                <label for="confirm_password" class="form-label">Confirm New Password</label>
                <div class="password-input-wrapper">
                  <input
                    type="password"
                    id="confirm_password"
                    name="confirm_password"
                    class="form-control"
                    minlength="8"
                    required>
                  <button type="button" class="password-toggle">
                    <span class="material-symbols-outlined">visibility</span>
                  </button>
                </div>
                <span class="error-message" id="confirm-password-error"></span>
              </div>
            </div>

            <div class="form-actions">
              <button type="submit" class="btn-primary">
                <span class="material-symbols-outlined">lock_reset</span>
                Change Password
              </button>
            </div>
          </form>
        </div>
      </div>

      <div class="section-card">
        <div class="section-card-header">
          <h2 class="section-card-title">Shipping Address</h2>
        </div>
        <div class="section-card-body">
          <form method="POST" action="/profile.php" class="profile-form">
            <input type="hidden" name="form_type" value="address">

            <div class="form-group">
              <label for="address" class="form-label">Full Address</label>
              <textarea
                id="address"
                name="address"
                class="form-control"
                rows="3"
                placeholder="Street address, city, state, postal code, country"><?php echo $user_data['address'] ?? ''; ?></textarea>
              <small style="color: var(--subtext0); font-size: 12px; margin-top: 4px; display: block;">
                Enter your complete shipping address
              </small>
            </div>

            <div class="form-actions">
              <button type="submit" class="btn-primary">
                <span class="material-symbols-outlined">save</span>
                Save Address
              </button>
            </div>
          </form>
        </div>
      </div>

    </main>
  </div>
</body>

</html>
