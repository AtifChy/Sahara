<?php
require_once __DIR__ . '/models/Auth.php';
require_once __DIR__ . '/models/User.php';

if (!isLoggedIn()) {
  header('Location: /auth.php?page=login');
  exit;
}

$user_id = $_SESSION['user_id'];

$success_message = $_SESSION['success_message'] ?? '';
$error_message = $_SESSION['error_message'] ?? '';

unset($_SESSION['success_message']);
unset($_SESSION['error_message']);

$user_data = getUserData($user_id);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>My Profile - Sahara</title>
  <link rel="icon" href="views/assets/favicon.ico">
  <link rel="stylesheet" href="views/css/colors.css" />
  <link rel="stylesheet" href="views/css/main.css" />
  <link rel="stylesheet" href="views/css/role.css" />
  <script type="module" src="./views/js/profile.js"></script>
</head>

<body>
  <?php include __DIR__ . '/views/partials/header.php'; ?>

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
          <form method="POST" action="/controllers/shop/ProfileController.php" class="profile-form">
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
          <form method="POST" action="/controllers/shop/ProfileController.php" enctype="multipart/form-data" class="profile-form" id="pictureForm">
            <input type="hidden" name="form_type" value="profile_picture">

            <div class="profile-picture-section">
              <div class="avatar-preview">
                <?php if (!empty($user_data['picture'])): ?>
                  <img src="/uploads/avatars/<?php echo $user_data['picture']; ?>" alt="Profile Picture" id="avatarImage">
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
          <form method="POST" action="/controllers/shop/ProfileController.php" class="profile-form">
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
          <form method="POST" action="/controllers/shop/ProfileController.php" class="profile-form">
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
