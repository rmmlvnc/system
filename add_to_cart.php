<?php
session_start();
include("database.php");

// Check if user is logged in
if (!isset($_SESSION['username'])) {
  header("Location: login.php");
  exit();
}

// Get customer_id from username
$cust_stmt = $conn->prepare("SELECT customer_id FROM customer WHERE username = ?");
if ($cust_stmt === false) {
  die("Error preparing customer query: " . $conn->error);
}

$cust_stmt->bind_param("s", $_SESSION['username']);
$cust_stmt->execute();
$cust_result = $cust_stmt->get_result();
$cust_row = $cust_result->fetch_assoc();

if (!$cust_row) {
  die("Customer not found");
}

$customer_id = $cust_row['customer_id'];
$cust_stmt->close();

// Get form data
$product_id = $_POST['product_id'] ?? null;
$quantity = (int)($_POST['quantity'] ?? 1);

if (!$product_id || $quantity < 1) {
  die("Invalid product or quantity");
}

// Get product details
$prod_stmt = $conn->prepare("SELECT product_name, price, stock_quantity FROM product WHERE product_id = ?");
$prod_stmt->bind_param("i", $product_id);
$prod_stmt->execute();
$prod_result = $prod_stmt->get_result();
$product = $prod_result->fetch_assoc();
$prod_stmt->close();

if (!$product) {
  die("Product not found");
}

// Check stock
if ($quantity > $product['stock_quantity']) {
  die("Insufficient stock");
}

// Check if customer has a pending order (cart)
if (!isset($_SESSION['cart_order_id'])) {
  // Create a new order with current date and time
  $order_stmt = $conn->prepare("INSERT INTO orders (customer_id, order_date, order_time, total_amount) VALUES (?, CURDATE(), CURTIME(), 0)");
  $order_stmt->bind_param("i", $customer_id);
  
  if (!$order_stmt->execute()) {
    die("Error creating order: " . $conn->error);
  }
  
  $_SESSION['cart_order_id'] = $conn->insert_id;
  $order_stmt->close();
}

$order_id = $_SESSION['cart_order_id'];

// Check if item already exists in cart
$check_stmt = $conn->prepare("SELECT quantity FROM order_item WHERE order_id = ? AND product_id = ?");
$check_stmt->bind_param("ii", $order_id, $product_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();
$existing_item = $check_result->fetch_assoc();
$check_stmt->close();

$total_price = $product['price'] * $quantity;

if ($existing_item) {
  // Update existing item
  $new_quantity = $existing_item['quantity'] + $quantity;
  $new_total = $product['price'] * $new_quantity;
  
  $update_stmt = $conn->prepare("UPDATE order_item SET quantity = ?, total_price = ? WHERE order_id = ? AND product_id = ?");
  $update_stmt->bind_param("idii", $new_quantity, $new_total, $order_id, $product_id);
  
  if (!$update_stmt->execute()) {
    die("Error updating cart: " . $conn->error);
  }
  $update_stmt->close();
} else {
  // Insert new item
  $insert_stmt = $conn->prepare("INSERT INTO order_item (order_id, product_id, quantity, total_price) VALUES (?, ?, ?, ?)");
  $insert_stmt->bind_param("iiid", $order_id, $product_id, $quantity, $total_price);
  
  if (!$insert_stmt->execute()) {
    die("Error adding to cart: " . $conn->error);
  }
  $insert_stmt->close();
}

// Update order total
$total_stmt = $conn->prepare("UPDATE orders SET total_amount = (SELECT SUM(total_price) FROM order_item WHERE order_id = ?) WHERE order_id = ?");
$total_stmt->bind_param("ii", $order_id, $order_id);
$total_stmt->execute();
$total_stmt->close();

// Redirect back to menu with category preserved
$category = $_POST['category'] ?? '';
$redirect_url = 'menu.php' . ($category ? '?category=' . urlencode($category) : '');
header("Location: $redirect_url");
exit();
?>
