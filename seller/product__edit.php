<?php
require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/models/Auth.php';

if (!isLoggedIn() || !in_array($_SESSION['user_role'], ['SELLER', 'ADMIN'])) {
  header('Location: /index.php');
  exit;
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];
$categories = ['ELECTRONICS', 'FASHION', 'ACCESSORIES', 'HOME'];

$error = '';
$product = null;

// Get product ID from URL
$product_id = $_GET['id'] ?? '';

if (empty($product_id) || !is_numeric($product_id)) {
  header('Location: /seller.php?page=products');
  exit;
}

$product_id = intval($product_id);

// Fetch product
if ($user_role === 'ADMIN') {
  $sql = "SELECT * FROM products WHERE id = $product_id";
} else {
  $sql = "SELECT * FROM products WHERE id = $product_id AND seller_id = $user_id";
}

$product = fetchOne($sql);

if (!$product) {
  header('Location: /seller.php?page=products');
  exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = sanitizeInput($_POST['title'] ?? '');
  $description = sanitizeInput($_POST['description'] ?? '');
  $price = sanitizeInput($_POST['price'] ?? '');
  $category = $_POST['category'] ?? '';
  $stock = sanitizeInput($_POST['stock'] ?? '');
  $is_new = isset($_POST['is_new']) ? 1 : 0;
  $keep_image = isset($_POST['keep_image']) ? 1 : 0;

  // Validate inputs
  if (empty($title) || empty($price) || empty($category) || !isset($stock) || $stock === '') {
    $error = 'Please fill in all required fields.';
  } else if (!in_array($category, $categories)) {
    $error = 'Invalid category selected.';
  } else if (!is_numeric($price) || $price <= 0) {
    $error = 'Please enter a valid price.';
  } else if (!is_numeric($stock) || $stock < 0) {
    $error = 'Please enter a valid stock quantity.';
  } else {
    $image_path = $product['image'];

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
      $allowed_types = ['image/jpeg', 'image/png'];
      $max_size = 5 * 1024 * 1024;

      $file_type = $_FILES['image']['type'];
      $file_size = $_FILES['image']['size'];

      if (!in_array($file_type, $allowed_types)) {
        $error = 'Invalid image format. Only JPG and PNG are allowed.';
      } else if ($file_size > $max_size) {
        $error = 'Image size exceeds the maximum limit of 5MB.';
      } else {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = uniqid("product_", true) . '.' . $ext;
        $upload_dir = __DIR__ . '/../uploads/products/';
        $upload_path = $upload_dir . $filename;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
          // Delete old image if it exists
          if (!empty($product['image']) && file_exists(__DIR__ . '/..' . $product['image'])) {
            @unlink(__DIR__ . '/..' . $product['image']);
          }
          $image_path = '/uploads/products/' . $filename;
        } else {
          $error = 'Failed to upload image.';
        }
      }
    } else if (!$keep_image) {
      if (!empty($product['image']) && file_exists(__DIR__ . '/..' . $product['image'])) {
        @unlink(__DIR__ . '/..' . $product['image']);
      }
      $image_path = null;
    }
  }

  if (empty($error)) {
    $price = floatval($price);
    $stock = intval($stock);

    $sql = "UPDATE products SET 
            title = '$title', 
            description = '$description', 
            price = '$price', 
            category = '$category', 
            stock = '$stock', 
            is_new = '$is_new', 
            image = " . ($image_path ? "'$image_path'" : "NULL") . "
            WHERE id = $product_id";

    if (query($sql)) {
      header('Location: /seller.php?page=products&status=updated');
      exit;
    } else {
      $error = 'Failed to update product. Please try again.';
    }
  }

  // If there's an error, update the product array with submitted values
  if (!empty($error)) {
    $product['title'] = $title;
    $product['description'] = $description;
    $product['price'] = $price;
    $product['category'] = $category;
    $product['stock'] = $stock;
    $product['is_new'] = $is_new;
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sahara | Edit Product</title>
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
          <h1>Edit Product</h1>
          <p>Update the details below to modify this product.</p>
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
              <div class="image-preview uploaded" id="image-preview">
                <?php if (!empty($product['image'])): ?>
                  <img src="<?php echo $product['image']; ?>" alt="Current Image">
                  <p>
                    <span class="material-symbols-outlined">edit</span>
                    Click to change image
                  </p>
                  <small>JPG, PNG (Max 5MB)</small>
                <?php else: ?>
                  <span class="material-symbols-outlined">image</span>
                  <p>Click to upload an image here</p>
                  <small>JPG, PNG (Max 5MB)</small>
                <?php endif; ?>
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
                value="<?php echo $product['title']; ?>">
              <small class="form-hint">A clear and descriptive title for your product.</small>
              <span class="error-message" id="title-error"></span>
            </div>

            <div class="form-group">
              <label for="description" class="form-label">Description</label>
              <textarea
                name="description"
                rows="5"
                placeholder="Enter product description"
                class="form-input"><?php echo $product['description'] ?? ''; ?></textarea>
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
                    value="<?php echo $product['price']; ?>">
                </div>
                <span class="error-message" id="price-error"></span>
              </div>

              <div class="form-group">
                <label for="category" class="form-label">Category</label>
                <select name="category" class="form-input" id="category">
                  <option value="" disabled>Select a category</option>
                  <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat; ?>" <?php echo ($product['category'] === $cat) ? 'selected' : '' ?>>
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
                value="<?php echo $product['stock']; ?>">
              <small class="form-hint">Number of items available for sale</small>
              <span class="error-message" id="stock-error"></span>
            </div>

            <div class="form-group">
              <div class="checkbox-group">
                <input
                  type="checkbox"
                  name="is_new"
                  id="is_new"
                  value="1"
                  <?php echo $product['is_new'] ? 'checked' : ''; ?>>
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
              <span class="material-symbols-outlined">save</span>
              Update Product
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</body>

</html>
