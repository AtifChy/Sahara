<?php
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$stock_filter = $_GET['stock'] ?? '';
$seller_filter = $_GET['seller'] ?? '';

$query = "SELECT p.*, 
          CONCAT(up.first_name, ' ', COALESCE(up.last_name, '')) as seller_name,
          u.email as seller_email
          FROM products p
          LEFT JOIN users u ON p.seller_id = u.id
          LEFT JOIN user_profiles up ON u.id = up.user_id
          WHERE 1=1";
$conditions = [];

if (!empty($search)) {
  $conditions[] = "(p.title LIKE '%{$search}%' OR p.description LIKE '%{$search}%')";
}

if (!empty($category)) {
  $conditions[] = "p.category = '{$category}'";
}

if ($stock_filter === 'low') {
  $conditions[] = "p.stock < 10";
} elseif ($stock_filter === 'out') {
  $conditions[] = "p.stock = 0";
}

if (!empty($seller_filter)) {
  $conditions[] = "p.seller_id = {$seller_filter}";
}

if (!empty($conditions)) {
  $query .= " AND " . implode(" AND ", $conditions);
}

$query .= " ORDER BY p.created_at DESC";

$products = fetchAll($query);

$totalProducts = fetchOne("SELECT COUNT(*) as count FROM products")['count'] ?? 0;
$lowStockCount = fetchOne("SELECT COUNT(*) as count FROM products WHERE stock < 10")['count'] ?? 0;
$outOfStockCount = fetchOne("SELECT COUNT(*) as count FROM products WHERE stock = 0")['count'] ?? 0;
$totalValue = fetchOne("SELECT SUM(price * stock) as value FROM products")['value'] ?? 0;

$categories = ['ELECTRONICS', 'FASHION', 'ACCESSORIES', 'HOME'];

$sellers = fetchAll("SELECT u.id, CONCAT(up.first_name, ' ', COALESCE(up.last_name, '')) as name 
                     FROM users u 
                     LEFT JOIN user_profiles up ON u.id = up.user_id 
                     WHERE u.role IN ('SELLER', 'ADMIN') 
                     ORDER BY up.first_name");
?>

<main class="role-content">

  <div class="role-header">
    <div>
      <h1>Product Management</h1>
      <p>View and manage all products in the marketplace</p>
    </div>
  </div>


  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-card-header">
        <span class="stat-card-title">Total Products</span>
        <div class="stat-card-icon blue">
          <span class="material-symbols-outlined">inventory_2</span>
        </div>
      </div>
      <h2 class="stat-card-value"><?php echo number_format($totalProducts); ?></h2>
    </div>

    <div class="stat-card">
      <div class="stat-card-header">
        <span class="stat-card-title">Low Stock</span>
        <div class="stat-card-icon yellow">
          <span class="material-symbols-outlined">warning</span>
        </div>
      </div>
      <h2 class="stat-card-value"><?php echo number_format($lowStockCount); ?></h2>
    </div>

    <div class="stat-card">
      <div class="stat-card-header">
        <span class="stat-card-title">Out of Stock</span>
        <div class="stat-card-icon red">
          <span class="material-symbols-outlined">block</span>
        </div>
      </div>
      <h2 class="stat-card-value"><?php echo number_format($outOfStockCount); ?></h2>
    </div>

    <div class="stat-card">
      <div class="stat-card-header">
        <span class="stat-card-title">Inventory Value</span>
        <div class="stat-card-icon green">
          <span class="material-symbols-outlined">payments</span>
        </div>
      </div>
      <h2 class="stat-card-value">৳<?php echo number_format($totalValue, 2); ?></h2>
    </div>
  </div>


  <div class="section-card">
    <div class="section-card-header">
      <h2 class="section-card-title">
        <span class="material-symbols-outlined">inventory_2</span>
        All Products
      </h2>
    </div>

    <div class="filters-bar">
      <form method="GET" action="/admin.php" class="filters-form">
        <input type="hidden" name="page" value="products" />

        <div class="filter-group search-group">
          <span class="material-symbols-outlined">search</span>
          <input class="filter-input search-input" type="text" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>" />
        </div>

        <select name="category" class="filter-select">
          <option value="">All Categories</option>
          <?php foreach ($categories as $cat): ?>
            <option value="<?php echo $cat; ?>" <?php echo $category === $cat ? 'selected' : ''; ?>>
              <?php echo ucfirst(strtolower($cat)); ?>
            </option>
          <?php endforeach; ?>
        </select>

        <select name="stock" class="filter-select">
          <option value="">All Stock Levels</option>
          <option value="low" <?php echo $stock_filter === 'low' ? 'selected' : ''; ?>>Low Stock</option>
          <option value="out" <?php echo $stock_filter === 'out' ? 'selected' : ''; ?>>Out of Stock</option>
        </select>

        <select name="seller" class="filter-select">
          <option value="">All Sellers</option>
          <?php foreach ($sellers as $seller): ?>
            <option value="<?php echo $seller['id']; ?>" <?php echo $seller_filter == $seller['id'] ? 'selected' : ''; ?>>
              <?php echo $seller['name']; ?>
            </option>
          <?php endforeach; ?>
        </select>

        <button type="submit" class="btn btn-ghost">
          <span class="material-symbols-outlined">filter_list</span>
          Apply
        </button>

        <?php if (!empty($search) || !empty($category) || !empty($stock_filter) || !empty($seller_filter)): ?>
          <a href="/admin.php?page=products" class="btn btn-ghost">
            <span class="material-symbols-outlined">clear</span>
            Clear
          </a>
        <?php endif; ?>
      </form>
    </div>

    <div class="section-card-body">
      <?php if (empty($products)): ?>
        <div class="empty-state">
          <span class="material-symbols-outlined">inventory_2</span>
          <p>No products found</p>
        </div>
      <?php else: ?>
        <table class="role-table">
          <thead>
            <tr>
              <th>Product</th>
              <th>Seller</th>
              <th>Category</th>
              <th>Price</th>
              <th>Stock</th>
              <th>Rating</th>
              <th>Status</th>
              <th>Created</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($products as $product): ?>
              <tr>
                <td>
                  <div class="product">
                    <img src="<?php echo !empty($product['image']) ? $product['image'] : '/assets/product_placeholder.svg'; ?>"
                      alt="<?php echo $product['title']; ?>"
                      class="product-image">
                    <div>
                      <div class="product-title"><?php echo $product['title']; ?></div>
                      <div class="product-id">ID: #<?php echo $product['id']; ?></div>
                      <?php if ($product['is_new']): ?>
                        <span class="badge badge-new">New</span>
                      <?php endif; ?>
                    </div>
                  </div>
                </td>
                <td>
                  <div class="user-info-mini">
                    <div class="user-name"><?php echo $product['seller_name'] ?: 'N/A'; ?></div>
                    <div class="user-email"><?php echo $product['seller_email']; ?></div>
                  </div>
                </td>
                <td>
                  <span class="badge badge-category">
                    <?php echo ucfirst(strtolower($product['category'])); ?>
                  </span>
                </td>
                <td><strong>৳<?php echo number_format($product['price'], 2); ?></strong></td>
                <td>
                  <?php
                  $stock = $product['stock'];
                  $stockClass = $stock === 0 ? 'out-stock' : ($stock < 10 ? 'low-stock' : 'in-stock');
                  ?>
                  <span class="stock-indicator <?php echo $stockClass; ?>">
                    <?php echo $stock; ?> units
                  </span>
                </td>
                <td>
                  <div class="rating">
                    <span class="material-symbols-outlined" style="font-size: 16px; color: var(--yellow);">star</span>
                    <span><?php echo number_format($product['rating'], 1); ?></span>
                  </div>
                </td>
                <td>
                  <?php if ($product['stock'] > 0): ?>
                    <span class="badge active">Active</span>
                  <?php else: ?>
                    <span class="badge inactive">Out of Stock</span>
                  <?php endif; ?>
                </td>
                <td><?php echo date('M d, Y', strtotime($product['created_at'])); ?></td>
                <td>
                  <div class="table-actions">
                    <button class="table-btn view" title="View Details" onclick='viewProduct(<?php echo json_encode($product); ?>)'>
                      <span class="material-symbols-outlined">visibility</span>
                    </button>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>


        <div class="table-footer">
          <p>Showing <?php echo count($products); ?> of <?php echo $totalProducts; ?> products</p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</main>


<div id="viewProductModal" class="modal">
  <div class="modal-content modal-large">
    <div class="modal-header">
      <h2>Product Details</h2>
      <button class="modal-close" onclick="closeModal('viewProductModal')">&times;</button>
    </div>
    <div class="modal-body" id="productDetailsContent">
    </div>
  </div>
</div>

<style>
  .filters-bar {
    border-bottom: 1px solid var(--surface0);
    border-radius: 0;
    margin-bottom: 0;
  }

  .modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.6);
    animation: fadeIn 0.2s ease;
  }

  .modal.show {
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .modal-content {
    background: var(--mantle);
    border-radius: 12px;
    max-width: 600px;
    width: 90%;
    max-height: 80vh;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    border: 1px solid var(--surface0);
    animation: slideUp 0.3s ease;
  }

  .modal-content.modal-large {
    max-width: 800px;
  }

  .modal-header {
    padding: 24px;
    border-bottom: 1px solid var(--surface0);
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .modal-header h2 {
    margin: 0;
    font-size: 20px;
    color: var(--text);
  }

  .modal-close {
    background: none;
    border: none;
    font-size: 32px;
    color: var(--subtext0);
    cursor: pointer;
    line-height: 1;
    padding: 0;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    transition: all 0.2s;
  }

  .modal-close:hover {
    background: var(--surface0);
    color: var(--text);
  }

  .modal-body {
    padding: 24px;
    overflow-y: auto;
    flex: 1;
  }

  .product-detail-grid {
    display: grid;
    grid-template-columns: 200px 1fr;
    gap: 24px;
  }

  .product-detail-image {
    width: 200px;
    height: 200px;
    object-fit: cover;
    border-radius: 8px;
    background: var(--base);
  }

  .product-detail-info {
    display: grid;
    gap: 16px;
  }

  .product-detail-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
  }

  .product-detail-label {
    font-size: 12px;
    color: var(--subtext0);
    text-transform: uppercase;
    font-weight: 600;
    letter-spacing: 0.5px;
  }

  .product-detail-value {
    font-size: 14px;
    color: var(--text);
  }

  .product-detail-description {
    margin-top: 16px;
    padding-top: 16px;
    border-top: 1px solid var(--surface0);
  }

  .user-info-mini {
    display: flex;
    flex-direction: column;
    gap: 2px;
  }

  .user-info-mini .user-name {
    font-size: 14px;
    font-weight: 600;
    color: var(--text);
  }

  .user-info-mini .user-email {
    font-size: 12px;
    color: var(--subtext0);
  }

  .stock-indicator {
    font-weight: 600;
  }

  .stock-indicator.in-stock {
    color: var(--green);
  }

  .stock-indicator.low-stock {
    color: var(--yellow);
  }

  .stock-indicator.out-stock {
    color: var(--red);
  }

  .rating {
    display: flex;
    align-items: center;
    gap: 4px;
  }
</style>

<script>
  function viewProduct(product) {
    const content = `
      <div class="product-detail-grid">
        <img src="${product.image || '/assets/product_placeholder.svg'}" 
             alt="${product.title}" 
             class="product-detail-image">
        
        <div class="product-detail-info">
          <div class="product-detail-item">
            <span class="product-detail-label">Product ID</span>
            <span class="product-detail-value">#${product.id}</span>
          </div>
          
          <div class="product-detail-item">
            <span class="product-detail-label">Title</span>
            <span class="product-detail-value"><strong>${product.title}</strong></span>
          </div>
          
          <div class="product-detail-item">
            <span class="product-detail-label">Seller</span>
            <span class="product-detail-value">${product.seller_name || 'N/A'} (${product.seller_email})</span>
          </div>
          
          <div class="product-detail-item">
            <span class="product-detail-label">Category</span>
            <span class="product-detail-value">
              <span class="badge badge-category">${product.category}</span>
            </span>
          </div>
          
          <div class="product-detail-item">
            <span class="product-detail-label">Price</span>
            <span class="product-detail-value" style="font-size: 18px; color: var(--blue); font-weight: 700;">৳${parseFloat(product.price).toFixed(2)}</span>
          </div>
          
          <div class="product-detail-item">
            <span class="product-detail-label">Stock</span>
            <span class="product-detail-value">
              <span class="${product.stock === 0 ? 'out-stock' : (product.stock < 10 ? 'low-stock' : 'in-stock')}" style="font-weight: 600;">
                ${product.stock} units
              </span>
            </span>
          </div>
          
          <div class="product-detail-item">
            <span class="product-detail-label">Rating</span>
            <span class="product-detail-value">
              <div class="rating">
                <span class="material-symbols-outlined" style="color: var(--yellow);">star</span>
                <strong>${parseFloat(product.rating).toFixed(1)}</strong>
              </div>
            </span>
          </div>
          
          <div class="product-detail-item">
            <span class="product-detail-label">Status</span>
            <span class="product-detail-value">
              ${product.is_new == 1 ? '<span class="badge badge-new">New</span> ' : ''}
              ${product.stock > 0 ? '<span class="badge active">Active</span>' : '<span class="badge inactive">Out of Stock</span>'}
            </span>
          </div>
          
          <div class="product-detail-item">
            <span class="product-detail-label">Created</span>
            <span class="product-detail-value">${new Date(product.created_at).toLocaleDateString()}</span>
          </div>
          
          <div class="product-detail-item">
            <span class="product-detail-label">Last Updated</span>
            <span class="product-detail-value">${new Date(product.updated_at).toLocaleDateString()}</span>
          </div>
        </div>
      </div>
      
      ${product.description ? `
        <div class="product-detail-description">
          <span class="product-detail-label">Description</span>
          <p class="product-detail-value" style="margin-top: 8px; line-height: 1.6;">${product.description}</p>
        </div>
      ` : ''}
    `;

    document.getElementById('productDetailsContent').innerHTML = content;
    openModal('viewProductModal');
  }

  function openModal(modalId) {
    document.getElementById(modalId).classList.add('show');
  }

  function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('show');
  }

  // Close modal when clicking outside
  window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
      event.target.classList.remove('show');
    }
  }
</script>
