<?php
require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/models/Auth.php';

if (!isLoggedIn() || !in_array($_SESSION['user_role'], ['SELLER', 'ADMIN'])) {
  header('Location: /index.php');
  exit;
}

$seller_id = $_SESSION['user_id'];
$categories = ['ELECTRONICS', 'FASHION', 'ACCESSORIES', 'HOME'];

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = sanitizeInput($_POST['title'] ?? '');
  $description = sanitizeInput($_POST['description'] ?? '');
  $price = sanitizeInput($_POST['price'] ?? '');
  $category = $_POST['category'] ?? '';
  $stock = sanitizeInput($_POST['stock'] ?? '');
  $is_new = isset($_POST['is_new']) ? 1 : 0;

  // Validate inputs
  if (empty($title) || empty($price) || empty($category) || empty($stock)) {
    $error = 'Please fill in all required fields.';
  } else if (!in_array($category, $categories)) {
    $error = 'Invalid category selected.';
  } else if (!is_numeric($price) || $price <= 0) {
    $error = 'Please enter a valid price.';
  } else if (!is_numeric($stock) || $stock <= 0) {
    $error = 'Please enter a valid stock quantity.';
  } else {
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
      $allowed_types = ['image/jpeg', 'image/png'];
      $max_size = 5 * 1024 * 1024;

      $file_type = $_FILES['image']['type'];
      $file_size = $_FILES['image']['size'];

      if (!in_array($file_type, $allowed_types)) {
        $error = 'Invalid image format. Only JPG and PNG are allowed.';
      }

      if ($file_size > $max_size) {
        $error = 'Image size exceeds the maximum limit of 5MB.';
      }

      if (empty($error)) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = uniqid("product_", true) . '.' . $ext;
        $upload_dir = __DIR__ . '/../uploads/products/';
        $upload_path = $upload_dir . $filename;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
          $image_path = '/uploads/products/' . $filename;
        } else {
          $error = 'Failed to upload image.';
        }
      }
    }
  }

  if (empty($error)) {
    $price = floatval($price);
    $stock = intval($stock);

    $sql =
      "INSERT INTO products
      (seller_id, title, description, price, category, stock, is_new, image)
      VALUES ('$seller_id', '$title', '$description', '$price', '$category', '$stock', '$is_new', '$image_path')";

    if (query($sql)) {
      $success = true;
      header('Location: /seller.php?page=products&status=added');
      exit;
    } else {
      $error = 'Failed to add product. Please try again.';
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sahara | Product</title>
  <link rel="icon" href="/assets/favicon.ico">
  <link rel="stylesheet" href="/css/main.css" />
  <link rel="stylesheet" href="/css/form.css" />
  <script type="module" src="/js/product__add.js"></script>
</head>

<body>
  <?php include __DIR__ . '/../app/views/partials/header.php'; ?>

  <div class="form-container">
    <div class="form-content">
      <a href="/seller.php?page=products" class="back-link">
        <span class="material-symbols-outlined">arrow_back</span>
        Back to Products
      </a>

      <div class="form-card">
        <div class="form-header">
          <h1>Add New Products</h1>
          <p>Fill in the details below to add a new product to your inventory.</p>
        </div>

        <?php if (!empty($error)): ?>
          <div class="alert alert-error">
            <span class="material-symbols-outlined">error</span>
            <span><?php echo $error; ?></span>
          </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data" class="product-form" id="product-form">
          <div class="form-section">
            <h3 class="form-section-title">Product Image</h3>

            <div class="image-upload-area" id="image-upload-area">
              <input
                type="file"
                name="image"
                class="image-input"
                id="image-input"
                accept="image/png, image/jpeg">
              <div class="image-preview" id="image-preview">
                <span class="material-symbols-outlined">image</span>
                <p>Click to upload an image here</p>
                <small>JPG, PNG (Max 5MB)</small>
              </div>
            </div>
          </div>

          <div class="form-section">
            <h3 class="form-section-title">Product Information</h3>

            <div class="form-group">
              <label for="title" class="form-label">Title</label>
              <input
                type="text"
                name="title"
                class="form-input"
                id="title"
                placeholder="Enter product title"
                value="<?php echo $title ?? ''; ?>">
              <small class="form-hint">A clear and descriptive title for your product.</small>
              <span class="error-message" id="title-error"></span>
            </div>

            <div class="form-group">
              <label for="description" class="form-label">Description</label>
              <textarea
                name="description"
                rows="5"
                placeholder="Enter product description"
                class="form-input"><?php echo $description ?? ''; ?></textarea>
              <small class="form-hint">Provide detailed information about the product, including features and benefits.</small>
            </div>
          </div>

          <div class="form-section">
            <h3 class="form-section-title">Pricing & Category</h3>

            <div class="form-row">
              <div class="form-group">
                <label for="price" class="form-label">Price (BDT)</label>
                <div class="input-with-symbol">
                  <span class="input-symbol">৳</span>
                  <input
                    type="number"
                    name="price"
                    class="form-input"
                    id="price"
                    placeholder="0.00"
                    step="0.01"
                    min="0"
                    value="<?php echo $price ?? ''; ?>"></input>
                </div>
                <span class="error-message" id="price-error"></span>
              </div>

              <div class="form-group">
                <label for="categories" class="form-label">Category</label>
                <select name="category" class="form-input" id="category">
                  <option value="" disabled>Select a category</option>
                  <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat; ?>" <?php echo (isset($category) && $category === $cat) ? 'selected' : '' ?>>
                      <?php echo ucfirst(strtolower($cat)); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
                <span class="error-message" id="category-error"></span>
              </div>
            </div>
          </div>

          <div class="form-section">
            <h3 class="form-section-title">Inventory</h3>

            <div class="form-group">
              <label for="stock" class="form-label">Stock Quantity</label>
              <input
                type="number"
                name="stock"
                class="form-input"
                id="stock"
                placeholder="0"
                min="0"
                value="<?php echo $stock ?? ''; ?>">
              <small class="form-hint">Number of items available for sale</small>
              <span class="error-message" id="stock-error"></span>
            </div>

            <div class="form-group">
              <div class="checkbox-group">
                <input
                  type="checkbox"
                  name="is_new"
                  value="1"
                  <?php echo (isset($is_new) && $is_new) ? 'checked' : ''; ?>>
                <label for="is_new" class="checkbox-label">
                  <strong>Marks as New Arrival</strong>
                  <small>Display a "New" badge on this product</small>
                </label>
              </div>
            </div>
          </div>

          <div class="form-actions">
            <a href="/seller.php?page=products" class="btn btn-secondary">
              <span class="material-symbols-outlined">clear</span>
              Cancel
            </a>
            <button type="submit" class="btn btn-primary">
              <span class="material-symbols-outlined">add</span>
              Add Product
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</body>

</html>
