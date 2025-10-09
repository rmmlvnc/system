<?php
session_start();

$product_id = $_POST['product_id'];
$product_name = $_POST['product_name'];
$price = $_POST['price'];

if (!isset($_SESSION['cart'])) {
  $_SESSION['cart'] = [];
}

if (isset($_SESSION['cart'][$product_id])) {
  $_SESSION['cart'][$product_id]['quantity'] += 1;
} else {
  $_SESSION['cart'][$product_id] = [
    'product_name' => $product_name,
    'price' => $price,
    'quantity' => 1
  ];
}

// Redirect back to menu.php with category preserved
$category = $_GET['category'] ?? $_POST['category'] ?? '';
$redirect_url = 'menu.php' . ($category ? '?category=' . urlencode($category) : '');
header("Location: $redirect_url");
exit();
