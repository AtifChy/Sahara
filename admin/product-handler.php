<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

// Check if user is admin
if (!isLoggedIn() || $_SESSION['user_role'] !== 'ADMIN') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Get POST data
$action = $_POST['action'] ?? '';
$productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;

if (!$productId || !$action) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

switch ($action) {
    case 'update_stock':
        $newStock = isset($_POST['stock']) ? (int)$_POST['stock'] : 0;

        if ($newStock < 0) {
            echo json_encode(['success' => false, 'message' => 'Stock cannot be negative']);
            exit;
        }

        $result = query("UPDATE products SET stock = $newStock, updated_at = NOW() WHERE id = $productId");

        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Stock updated successfully',
                'stock' => $newStock
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update stock']);
        }
        break;

    case 'update_price':
        $newPrice = isset($_POST['price']) ? floatval($_POST['price']) : 0;

        if ($newPrice <= 0) {
            echo json_encode(['success' => false, 'message' => 'Price must be greater than 0']);
            exit;
        }

        $result = query("UPDATE products SET price = $newPrice, updated_at = NOW() WHERE id = $productId");

        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Price updated successfully',
                'price' => $newPrice
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update price']);
        }
        break;

    case 'update_product':
        $title = mysqli_real_escape_string(getDB(), $_POST['title'] ?? '');
        $description = mysqli_real_escape_string(getDB(), $_POST['description'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $category = mysqli_real_escape_string(getDB(), $_POST['category'] ?? '');
        $stock = intval($_POST['stock'] ?? 0);
        $isNew = isset($_POST['is_new']) ? 1 : 0;

        // Validate
        if (empty($title) || $price <= 0 || $stock < 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid product data']);
            exit;
        }

        $validCategories = ['ELECTRONICS', 'FASHION', 'ACCESSORIES', 'HOME'];
        if (!in_array($category, $validCategories)) {
            echo json_encode(['success' => false, 'message' => 'Invalid category']);
            exit;
        }

        $sql = "UPDATE products SET 
                title = '$title',
                description = '$description',
                price = $price,
                category = '$category',
                stock = $stock,
                is_new = $isNew,
                updated_at = NOW()
                WHERE id = $productId";

        $result = query($sql);

        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Product updated successfully'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update product']);
        }
        break;

    case 'delete_product':
        // Check if product has orders
        $orderCheck = fetchOne("SELECT COUNT(*) as count FROM order_items WHERE product_id = $productId");

        if ($orderCheck && $orderCheck['count'] > 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Cannot delete product with existing orders. Set stock to 0 instead.'
            ]);
            exit;
        }

        $result = query("DELETE FROM products WHERE id = $productId");

        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Product deleted successfully'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete product']);
        }
        break;

    case 'toggle_featured':
        $isNew = isset($_POST['is_new']) ? (int)$_POST['is_new'] : 0;

        $result = query("UPDATE products SET is_new = $isNew, updated_at = NOW() WHERE id = $productId");

        if ($result) {
            $statusText = $isNew ? 'marked as featured' : 'unmarked as featured';
            echo json_encode([
                'success' => true,
                'message' => "Product $statusText successfully",
                'is_new' => $isNew
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update product']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
