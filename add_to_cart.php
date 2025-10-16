<?php
session_start();

if (!isset($_SESSION['username'])) {
  header("Location: login.php");
  exit();
}

$product_id   = $_POST['product_id'];
$product_name = $_POST['product_name'];
$price        = $_POST['price'];
$quantity     = isset($_POST['quantity']) ? max(1, (int)$_POST['quantity']) : 1; // Ensure quantity is at least 1

if (!isset($_SESSION['cart'])) {
  $_SESSION['cart'] = [];
}

// If product already in cart, increase quantity
if (isset($_SESSION['cart'][$product_id])) {
  $_SESSION['cart'][$product_id]['quantity'] += $quantity;
} else {
  $_SESSION['cart'][$product_id] = [
    'product_name' => $product_name,
    'price'        => $price,
    'quantity'     => $quantity
  ];
}

// Redirect back to menu.php with category preserved
$category = $_POST['category'] ?? $_GET['category'] ?? '';
$redirect_url = 'menu.php' . ($category ? '?category=' . urlencode($category) : '');
header("Location: $redirect_url");
exit();
