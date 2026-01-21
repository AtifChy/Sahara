<?php
require_once __DIR__ . '/../../models/Auth.php';
require_once __DIR__ . '/../../models/Cart.php';
require_once __DIR__ . '/../../models/Order.php';

requireAuth('/auth/login.php');


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /checkout.php');
    exit;
}

$cartItems = getCartItemsWithDetails();
$totals = getCartTotals();

if (empty($cartItems)) {
    $_SESSION['checkout_error'] = 'Your cart is empty';
    header('Location: /cart.php');
    exit;
}

$phone = sanitizeInput($_POST['phone'] ?? '');
$fullName = sanitizeInput($_POST['full_name'] ?? '');
$address = sanitizeInput($_POST['address'] ?? '');
$paymentMethod = sanitizeInput($_POST['payment_method'] ?? 'COD');

$errors = [];

if (empty($phone)) {
    $errors[] = 'Phone number is required';
}

if (empty($fullName)) {
    $errors[] = 'Full name is required';
}

if (empty($address)) {
    $errors[] = 'Shipping address is required';
}

if (!empty($errors)) {
    $_SESSION['checkout_error'] = implode(', ', $errors);
    header('Location: /checkout.php');
    exit;
}

$shippingAddress = "$fullName\n$address\nPhone: $phone";

$userId = $_SESSION['user_id'];

$result = createOrder($userId, $cartItems, $shippingAddress, $totals['total']);

if (!$result['success']) {
    $_SESSION['checkout_error'] = $result['message'];
    header('Location: /checkout.php');
    exit;
}

clearCart();

$_SESSION['last_order_id'] = $result['order_id'];

header('Location: /order-confirmation.php?order_id=' . $result['order_id']);
exit;
