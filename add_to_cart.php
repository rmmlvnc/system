<?php
session_start();
include("database.php");

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

// Get POST data
$product_id = (int)$_POST['product_id'];
$quantity = isset($_POST['quantity']) ? max(1, (int)$_POST['quantity']) : 1;
$category = $_POST['category'] ?? '';

// Check product availability and get details
$prod_stmt = $conn->prepare("SELECT product_name, price, stock_quantity FROM product WHERE product_id = ?");
if ($prod_stmt === false) {
  die("Error preparing product query: " . $conn->error);
}

$prod_stmt->bind_param("i", $product_id);
$prod_stmt->execute();
$prod_result = $prod_stmt->get_result();
$product = $prod_result->fetch_assoc();
$prod_stmt->close();

if (!$product) {
  die("Product not found");
}

if ($product['stock_quantity'] < $quantity) {
  die("Insufficient stock available");
}

// Check if customer has a pending order (cart)
if (!isset($_SESSION['cart_order_id'])) {
  // Create a new order
  $order_stmt = $conn->prepare("INSERT INTO orders (customer_id, order_date, total_amount) VALUES (?, NOW(), 0)");
  if ($order_stmt === false) {
    die("Error preparing order insert: " . $conn->error);
  }
  
  $order_stmt->bind_param("i", $customer_id);
  $order_stmt->execute();
  $_SESSION['cart_order_id'] = $conn->insert_id;
  $order_stmt->close();
}

$order_id = $_SESSION['cart_order_id'];

// Check if product already exists in order_item
$check_stmt = $conn->prepare("SELECT quantity FROM order_item WHERE order_id = ? AND product_id = ?");
if ($check_stmt === false) {
  die("Error preparing check query: " . $conn->error);
}

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
  if ($update_stmt === false) {
    die("Error preparing update query: " . $conn->error);
  }
  
  $update_stmt->bind_param("idii", $new_quantity, $new_total, $order_id, $product_id);
  $update_stmt->execute();
  $update_stmt->close();
} else {
  // Insert new item
  $insert_stmt = $conn->prepare("INSERT INTO order_item (order_id, product_id, quantity, total_price) VALUES (?, ?, ?, ?)");
  if ($insert_stmt === false) {
    die("Error preparing insert query: " . $conn->error);
  }
  
  $insert_stmt->bind_param("iiid", $order_id, $product_id, $quantity, $total_price);
  $insert_stmt->execute();
  $insert_stmt->close();
}

// Update total amount in orders table
$total_stmt = $conn->prepare("
  UPDATE orders 
  SET total_amount = (
    SELECT SUM(total_price) 
    FROM order_item 
    WHERE order_id = ?
  ) 
  WHERE order_id = ?
");
if ($total_stmt === false) {
  die("Error preparing total update: " . $conn->error);
}

$total_stmt->bind_param("ii", $order_id, $order_id);
$total_stmt->execute();
$total_stmt->close();

// Redirect back to menu with category preserved
$redirect_url = 'menu.php' . ($category ? '?category=' . urlencode($category) : '');
header("Location: $redirect_url");
exit();
?>
