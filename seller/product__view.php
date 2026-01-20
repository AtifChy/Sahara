<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

if (!isLoggedIn() || !in_array($_SESSION['user_role'], ['SELLER', 'ADMIN'])) {
  header('Location: /index.php');
  exit;
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];
$error = '';
$product = null;

// Get product ID from URL
$product_id = $_GET['id'] ?? '';

if (empty($product_id) || !is_numeric($product_id)) {
  $error = 'Invalid product ID.';
} else {
  // Fetch product from database
  $product_id = intval($product_id);

  // Build query based on role
  if ($user_role === 'ADMIN') {
    // Admins can view any product
    $sql = "SELECT p.*, u.email as seller_email 
            FROM products p 
            LEFT JOIN users u ON p.seller_id = u.id 
            WHERE p.id = $product_id";
  } else {
    // Sellers can only view their own products
    $sql = "SELECT * FROM products WHERE id = $product_id AND seller_id = $user_id";
  }

  $product = fetchOne($sql);

  if (!$product) {
    $error = 'Product not found or you do not have permission to view it.';
  }
}

$categories = ['ELECTRONICS', 'FASHION', 'ACCESSORIES', 'HOME'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sahara | View Product</title>
  <link rel="icon" href="/assets/favicon.ico">
  <link rel="stylesheet" href="/css/main.css" />
  <link rel="stylesheet" href="/css/form.css" />
  <script defer src="/seller/product__actions.js"></script>
</head>

<body>
  <?php include '../partials/header.php'; ?>

  <div class="form-container">
    <div class="form-content">
      <a href="/seller.php?page=products" class="back-link">
        <span class="material-symbols-outlined">arrow_back</span>
        Back to Products
      </a>

      <?php if ($error): ?>
        <div class="form-card">
          <div class="alert alert-error">
            <span class="material-symbols-outlined">error</span>
            <span><?php echo $error; ?></span>
          </div>
          <div style="text-align: center; margin-top: 20px; padding: 0 32px 32px;">
            <a href="/seller.php?page=products" class="btn btn-primary">
              <span class="material-symbols-outlined">arrow_back</span>
              Go to Products
            </a>
          </div>
        </div>
      <?php else: ?>
        <div class="form-card">
          <div class="form-header">
            <h1>View Product</h1>
            <p>Complete information about this product.</p>
          </div>

          <div class="product-form">
            <div class="form-section">
              <h3 class="form-section-title">Product Image</h3>

              <div class="image-display-area">
                <?php if (!empty($product['image'])): ?>
                  <img
                    src="<?php echo $product['image']; ?>"
                    alt="<?php echo $product['title']; ?>">
                  <?php if ($product['is_new']): ?>
                    <span class="badge-new-overlay">New</span>
                  <?php endif; ?>
                <?php else: ?>
                  <div class="no-image">
                    <span class="material-symbols-outlined">image</span>
                    <p>No image uploaded</p>
                  </div>
                <?php endif; ?>
              </div>
            </div>

            <div class="form-section">
              <h3 class="form-section-title">Product Information</h3>

              <div class="form-group">
                <label class="form-label">ID
                  <span class="product-id-display">#<?php echo $product['id']; ?></span>
                </label>
              </div>

              <div class="form-group">
                <label for="title" class="form-label">Title</label>
                <input
                  type="text"
                  name="title"
                  class="form-input"
                  id="title"
                  value="<?php echo $product['title']; ?>"
                  disabled>
                <small class="form-hint">A clear and descriptive title for your product.</small>
              </div>

              <div class="form-group">
                <label for="description" class="form-label">Description</label>
                <textarea
                  name="description"
                  rows="5"
                  class="form-input"
                  disabled><?php echo $product['description'] ?? ''; ?></textarea>
                <small class="form-hint">Provide detailed information about the product, including features and benefits.</small>
              </div>

              <?php if ($user_role === 'ADMIN' && isset($product['seller_email'])): ?>
                <div class="form-group">
                  <label for="seller" class="form-label">Seller</label>
                  <input
                    type="text"
                    class="form-input"
                    id="seller"
                    value="<?php echo $product['seller_email']; ?>"
                    disabled>
                </div>
              <?php endif; ?>
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
                      step="0.01"
                      value="<?php echo $product['price']; ?>"
                      disabled>
                  </div>
                </div>

                <div class="form-group">
                  <label for="category" class="form-label">Category</label>
                  <select name="category" class="form-input" id="category" disabled>
                    <?php foreach ($categories as $cat): ?>
                      <option value="<?php echo $cat; ?>" <?php echo ($product['category'] === $cat) ? 'selected' : '' ?>>
                        <?php echo ucfirst(strtolower($cat)); ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
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
                  value="<?php echo $product['stock']; ?>"
                  disabled>
                <small class="form-hint">Number of items available for sale</small>
              </div>

              <div class="form-group">
                <div class="checkbox-group">
                  <input
                    type="checkbox"
                    name="is_new"
                    id="is_new"
                    <?php echo $product['is_new'] ? 'checked' : ''; ?>
                    disabled>
                  <label for="is_new" class="checkbox-label">
                    <strong>Marks as New Arrival</strong>
                    <small>Display a "New" badge on this product</small>
                  </label>
                </div>
              </div>

              <div class="form-group">
                <label for="rating" class="form-label">Rating</label>
                <div class="rating-display">
                  <span class="material-symbols-outlined">star</span>
                  <strong><?php echo $product['rating']; ?></strong>
                  <span class="rating-max">/ 5.0</span>
                </div>
              </div>
            </div>

            <div class="form-section">
              <h3 class="form-section-title">Metadata</h3>

              <?php
              function format_time($str)
              {
                $format = 'F j, Y \a\t g:i A';
                return date($format, strtotime($str));
              }
              ?>

              <div class="metadata-display">
                <div class="metadata-item">
                  <span class="metadata-label">Created At</span>
                  <span class="metadata-value"><?php echo format_time($product['created_at']); ?></span>
                </div>

                <div class="metadata-item">
                  <span class="metadata-label">Last Updated</span>
                  <span class="metadata-value"><?php echo format_time($product['updated_at']); ?></span>
                </div>
              </div>
            </div>

            <div class="form-actions">
              <a href="/seller.php?page=products" class="btn btn-secondary">
                <span class="material-symbols-outlined">arrow_back</span>
                Back
              </a>
              <div style="display: flex; gap: 8px;">
                <button type="button" onclick="editProduct(<?php echo $product['id']; ?>)" class="btn btn-primary">
                  <span class="material-symbols-outlined">edit</span>
                  Edit Product
                </button>
                <button type="button" onclick="deleteProduct(<?php echo $product['id']; ?>, '<?php echo addslashes($product['title']); ?>')" class="btn btn-danger">
                  <span class="material-symbols-outlined">delete</span>
                  Delete
                </button>
              </div>
            </div>
            </form>
          </div>
        <?php endif; ?>
        </div>
    </div>
</body>

</html>
