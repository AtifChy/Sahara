<?php
// Get seller ID
$seller_id = $_SESSION['user_id'];

// Get success message if exists
$success_message = $_SESSION['success_message'] ?? '';
if ($success_message) {
  unset($_SESSION['success_message']);
}

// Handle search and filters
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$stock_filter = $_GET['stock'] ?? '';

// Build query
$query = "SELECT * FROM products WHERE seller_id = {$seller_id}";
$conditions = [];

if (!empty($search)) {
  $search_safe = mysqli_real_escape_string(getDB(), $search);
  $conditions[] = "(title LIKE '%{$search_safe}%' OR description LIKE '%{$search_safe}%')";
}

if (!empty($category)) {
  $conditions[] = "category = '{$category}'";
}

if ($stock_filter === 'low') {
  $conditions[] = "stock < 10";
} elseif ($stock_filter === 'out') {
  $conditions[] = "stock = 0";
}

if (!empty($conditions)) {
  $query .= " AND " . implode(" AND ", $conditions);
}

$query .= " ORDER BY created_at DESC";

// Fetch products
$products = fetchAll($query);

// Get stats
$totalProducts = fetchOne("SELECT COUNT(*) as count FROM products WHERE seller_id = {$seller_id}")['count'] ?? 0;
$lowStockCount = fetchOne("SELECT COUNT(*) as count FROM products WHERE seller_id = {$seller_id} AND stock < 10")['count'] ?? 0;
$outOfStockCount = fetchOne("SELECT COUNT(*) as count FROM products WHERE seller_id = {$seller_id} AND stock = 0")['count'] ?? 0;

// Categories
$categories = ['ELECTRONICS', 'FASHION', 'ACCESSORIES', 'HOME'];
?>

<script defer src="/seller/product__actions.js"></script>

<main class="role-content">
  <!-- Success Message -->
  <?php if ($success_message): ?>
    <div class="alert alert-success">
      <span class="material-symbols-outlined">check_circle</span>
      <span><?php echo $success_message; ?></span>
      <button class="alert-close" onclick="this.parentElement.remove()">
        <span class="material-symbols-outlined">close</span>
      </button>
    </div>
  <?php endif; ?>

  <!-- Page Header -->
  <div class="page-header">
    <div class="page-header-content">
      <h1>My Products</h1>
      <p>Manage your product catalog and inventory</p>
    </div>
    <div class="page-header-actions">
      <a href="/seller/product__add.php" class="btn btn-primary">
        <span class="material-symbols-outlined">add</span>
        Add Product
      </a>
    </div>
  </div>

  <!-- Stats Overview -->
  <div class="product-stats">
    <div class="product-stat-card">
      <div class="stat-card-icon blue">
        <span class="material-symbols-outlined">inventory_2</span>
      </div>
      <div class="product-stat-content">
        <span class="product-stat-label">Total Products</span>
        <strong class="product-stat-value"><?php echo $totalProducts; ?></strong>
      </div>
    </div>
    <div class="product-stat-card">
      <div class="stat-card-icon yellow">
        <span class="material-symbols-outlined">warning</span>
      </div>
      <div class="product-stat-content">
        <span class="product-stat-label">Low Stock</span>
        <strong class="product-stat-value"><?php echo $lowStockCount; ?></strong>
      </div>
    </div>
    <div class="product-stat-card">
      <div class="stat-card-icon red">
        <span class="material-symbols-outlined">block</span>
      </div>
      <div class="product-stat-content">
        <span class="product-stat-label">Out of Stock</span>
        <strong class="product-stat-value"><?php echo $outOfStockCount; ?></strong>
      </div>
    </div>
  </div>

  <!-- Filters Bar -->
  <div class="filters-bar">
    <form method="GET" action="/seller.php" class="filters-form">
      <input type="hidden" name="page" value="products">

      <!-- Search -->
      <div class="filter-group search-group">
        <span class="material-symbols-outlined">search</span>
        <input
          type="text"
          name="search"
          placeholder="Search products..."
          value="<?php echo $search; ?>"
          class="filter-input search-input">
      </div>

      <!-- Category Filter -->
      <div class="filter-group">
        <select name="category" class="filter-select">
          <option value="">All Categories</option>
          <?php foreach ($categories as $cat): ?>
            <option value="<?php echo $cat; ?>" <?php echo $category === $cat ? 'selected' : ''; ?>>
              <?php echo ucfirst(strtolower($cat)); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Stock Filter -->
      <div class="filter-group">
        <select name="stock" class="filter-select">
          <option value="">All Stock Levels</option>
          <option value="low" <?php echo $stock_filter === 'low' ? 'selected' : ''; ?>>Low Stock</option>
          <option value="out" <?php echo $stock_filter === 'out' ? 'selected' : ''; ?>>Out of Stock</option>
        </select>
      </div>

      <!-- Filter Actions -->
      <button type="submit" class="btn btn-secondary">
        <span class="material-symbols-outlined">filter_list</span>
        Apply
      </button>
      <?php if (!empty($search) || !empty($category) || !empty($stock_filter)): ?>
        <a href="/seller.php?page=products" class="btn btn-ghost">
          <span class="material-symbols-outlined">clear</span>
          Clear
        </a>
      <?php endif; ?>
    </form>
  </div>

  <!-- Products Table -->
  <div class="section-card">
    <?php if (empty($products)): ?>
      <div class="empty-state">
        <span class="material-symbols-outlined">inventory_2</span>
        <?php if (!empty($search) || !empty($category) || !empty($stock_filter)): ?>
          <p>No products found matching your filters</p>
          <small>Try adjusting your search criteria</small>
        <?php else: ?>
          <p>No products yet</p>
          <small>Add your first product to get started selling</small>
          <a href="/product-add.php" class="btn btn-primary" style="margin-top: 16px;">
            <span class="material-symbols-outlined">add</span>
            Add Your First Product
          </a>
        <?php endif; ?>
      </div>
    <?php else: ?>
      <div class="table-container">
        <table class="role-table products-table">
          <thead>
            <tr>
              <th width="50%">Product</th>
              <th>Category</th>
              <th>Price</th>
              <th>Stock</th>
              <th>Rating</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($products as $product): ?>
              <tr>
                <!-- Product Info -->
                <td>
                  <div class="product-detailed">
                    <img
                      src="<?php echo $product['image'] ?? 'assets/placeholder.png'; ?>"
                      alt="<?php echo $product['title']; ?>"
                      class="product-thumbnail">
                    <div>
                      <strong class="product-title"><?php echo $product['title']; ?></strong>
                      <small class="product-id">ID: #<?php echo $product['id']; ?></small>
                      <?php if ($product['is_new']): ?>
                        <span class="badge badge-new">New</span>
                      <?php endif; ?>
                    </div>
                  </div>
                </td>

                <!-- Category -->
                <td>
                  <span class="badge badge-category">
                    <?php echo $product['category']; ?>
                  </span>
                </td>

                <!-- Price -->
                <td>
                  <strong class="product-price">৳<?php echo $product['price']; ?></strong>
                </td>

                <!-- Stock -->
                <td>
                  <?php
                  $stock = $product['stock'];
                  $stockClass = $stock === 0 ? 'red' : ($stock < 10 ? 'yellow' : 'green');
                  $stockText = $stock === 0 ? 'Out of Stock' : $stock . ' units';
                  ?>
                  <span>
                    <?php echo $stockText; ?>
                  </span>
                </td>

                <!-- Rating -->
                <td>
                  <div class="rating">
                    <span class="material-symbols-outlined">star</span>
                    <strong><?php echo $product['rating']; ?></strong>
                  </div>
                </td>

                <!-- Status -->
                <td>
                  <?php if ($product['stock'] > 0): ?>
                    <span class="badge badge-active">Active</span>
                  <?php else: ?>
                    <span class="badge badge-inactive">Inactive</span>
                  <?php endif; ?>
                </td>

                <!-- Actions -->
                <td>
                  <div class="table-actions">
                    <button
                      class="table-btn view"
                      title="View Product"
                      onclick="viewProduct(<?php echo $product['id']; ?>)">
                      <span class="material-symbols-outlined">visibility</span>
                    </button>
                    <button
                      class="table-btn edit"
                      title="Edit Product"
                      onclick="editProduct(<?php echo $product['id']; ?>)">
                      <span class="material-symbols-outlined">edit</span>
                    </button>
                    <button
                      class="table-btn delete"
                      title="Delete Product"
                      onclick="deleteProduct(<?php echo $product['id']; ?>, '<?php echo $product['title']; ?>')">
                      <span class="material-symbols-outlined">delete</span>
                    </button>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- Results Summary -->
      <div class="table-footer">
        <p>
          Showing <?php echo count($products); ?> of <?php echo $totalProducts; ?> products
        </p>
      </div>
    <?php endif; ?>
  </div>
</main>
