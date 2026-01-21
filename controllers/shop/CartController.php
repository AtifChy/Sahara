<?php
session_start();
require_once __DIR__ . '/../../models/Cart.php';

$action = isset($_POST['action']) ? $_POST['action'] : '';
$productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

switch ($action) {
    case 'add':
        if ($productId > 0 && $quantity > 0) {
            $success = addToCart($productId, $quantity);
            if ($success) {
                echo getCartCount();
            } else {
                http_response_code(400);
                echo "STOCK_EXCEEDED";
            }
        } else {
            http_response_code(400);
            echo "INVALID_INPUT";
        }
        break;

    case 'update':
        if ($productId > 0 && $quantity >= 0) {
            $success = updateCartQuantity($productId, $quantity);
            if ($success) {
                echo getCartCount();
            } else {
                http_response_code(400);
                echo "STOCK_EXCEEDED";
            }
        } else {
            http_response_code(400);
            echo "INVALID_INPUT";
        }
        break;

    case 'remove':
        if ($productId > 0) {
            removeFromCart($productId);
            echo getCartCount();
        } else {
            http_response_code(400);
            echo "0";
        }
        break;

    case 'clear':
        clearCart();
        echo "0";
        break;

    case 'count':
        echo getCartCount();
        break;

    case 'get':
        $items = getCartItems();
        $output = array();
        foreach ($items as $item) {
            $output[] = $item['id'] . ':' . $item['quantity'];
        }
        echo implode(',', $output);
        break;

    default:
        http_response_code(400);
        echo "0";
        break;
}
