<?php

function getProductStock($productId)
{
    require_once __DIR__ . '/../controllers/db.php';

    $result = fetchOne("SELECT stock FROM products WHERE id = $productId");
    if ($result) {
        return (int)$result['stock'];
    }

    return 0;
}

function initCart()
{
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
}

function getCartItems()
{
    initCart();
    return $_SESSION['cart'];
}

function getCartCount()
{
    $items = getCartItems();
    $count = 0;

    foreach ($items as $item) {
        $count += (int)$item['quantity'];
    }

    return $count;
}

function addToCart($productId, $quantity = 1)
{
    initCart();

    $currentQuantity = getCartItemQuantity($productId);
    $newQuantity = $currentQuantity + $quantity;

    $availableStock = getProductStock($productId);

    if ($newQuantity > $availableStock) {
        return false;
    }

    $found = false;

    // check if item already exists
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['id'] == $productId) {
            $item['quantity'] = $newQuantity;
            $found = true;
            break;
        }
    }

    // add new item if not found
    if (!$found) {
        $_SESSION['cart'][] = [
            'id' => (int)$productId,
            'quantity' => (int)$quantity
        ];
    }

    return true;
}

function updateCartQuantity($productId, $quantity)
{
    initCart();

    if ($quantity <= 0) {
        removeFromCart($productId);
        return true;
    }

    // Check stock availability
    $availableStock = getProductStock($productId);

    if ($quantity > $availableStock) {
        // Return false if exceeds stock
        return false;
    }

    // Update quantity
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['id'] == $productId) {
            $item['quantity'] = (int)$quantity;
            break;
        }
    }

    return true;
}

function removeFromCart($productId)
{
    initCart();

    $_SESSION['cart'] = array_filter($_SESSION['cart'], function ($item) use ($productId) {
        return $item['id'] != $productId;
    });

    // Re-index array
    $_SESSION['cart'] = array_values($_SESSION['cart']);
}

function clearCart()
{
    $_SESSION['cart'] = [];
}

function isInCart($productId)
{
    $items = getCartItems();

    foreach ($items as $item) {
        if ($item['id'] == $productId) {
            return true;
        }
    }

    return false;
}

function getCartItemQuantity($productId)
{
    $items = getCartItems();

    foreach ($items as $item) {
        if ($item['id'] == $productId) {
            return (int)$item['quantity'];
        }
    }

    return 0;
}

function getCartItemsWithDetails()
{
    require_once __DIR__ . '/../controllers/db.php';
    $items = getCartItems();

    if (empty($items)) {
        return [];
    }

    $detailedItems = [];

    foreach ($items as $item) {
        $result = fetchOne("SELECT id, title, description, price, image, stock FROM products WHERE id = {$item['id']}");

        $detailedItems[] = [
            'id' => (int)$result['id'],
            'title' => $result['title'],
            'description' => $result['description'],
            'price' => (float)$result['price'],
            'image' => $result['image'],
            'stock' => (int)$result['stock'],
            'quantity' => (int)$item['quantity'],
            'subtotal' => (float)$result['price'] * (int)$item['quantity']
        ];
    }

    return $detailedItems;
}

function getCartTotals()
{
    $items = getCartItemsWithDetails();

    $subtotal = 0;
    foreach ($items as $item) {
        $subtotal += $item['subtotal'];
    }

    $shipping = $subtotal > 0 ? 50 : 0;
    $tax = $subtotal * 0.05; // 5% tax
    $total = $subtotal + $shipping + $tax;

    return [
        'subtotal' => $subtotal,
        'shipping' => $shipping,
        'tax' => $tax,
        'total' => $total
    ];
}
