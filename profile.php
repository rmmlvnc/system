<?php
session_start();
include 'database.php';

// Check if customer is logged in
if (!isset($_SESSION['customer_id'])) {
  header("Location: login.php");
  exit();
}

$customer_id = $_SESSION['customer_id'];
$success_message = '';
$error_message = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
  $first_name = trim($_POST['first_name']);
  $middle_name = trim($_POST['middle_name']);
  $last_name = trim($_POST['last_name']);
  $email = trim($_POST['email']);
  $phone_number = trim($_POST['phone_number']);
  $address = trim($_POST['address']);
  
  // Validate inputs
  if (empty($first_name) || empty($last_name) || empty($email) || empty($phone_number)) {
    $error_message = "Please fill in all required fields.";
  } else {
    // Check if email is already used by another customer
    $email_check = $conn->prepare("SELECT customer_id FROM customer WHERE email = ? AND customer_id != ?");
    $email_check->bind_param("si", $email, $customer_id);
    $email_check->execute();
    $email_result = $email_check->get_result();
    
    if ($email_result->num_rows > 0) {
      $error_message = "Email is already in use by another account.";
    } else {
      // Update customer profile
      $update_stmt = $conn->prepare("
        UPDATE customer 
        SET first_name = ?, middle_name = ?, last_name = ?, email = ?, phone_number = ?, address = ?
        WHERE customer_id = ?
      ");
      $update_stmt->bind_param("ssssssi", $first_name, $middle_name, $last_name, $email, $phone_number, $address, $customer_id);
      
      if ($update_stmt->execute()) {
        $success_message = "Profile updated successfully!";
        $_SESSION['username'] = $first_name; // Update session username
      } else {
        $error_message = "Error updating profile. Please try again.";
      }
      $update_stmt->close();
    }
    $email_check->close();
  }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
  $current_password = $_POST['current_password'];
  $new_password = $_POST['new_password'];
  $confirm_password = $_POST['confirm_password'];
  
  if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
    $error_message = "Please fill in all password fields.";
  } elseif ($new_password !== $confirm_password) {
    $error_message = "New passwords do not match.";
  } elseif (strlen($new_password) < 6) {
    $error_message = "Password must be at least 6 characters long.";
  } else {
    // Verify current password
    $pass_check = $conn->prepare("SELECT password FROM customer WHERE customer_id = ?");
    $pass_check->bind_param("i", $customer_id);
    $pass_check->execute();
    $pass_result = $pass_check->get_result();
    $customer_data = $pass_result->fetch_assoc();
    
    if ($customer_data['password'] === $current_password) {
      // Update password
      $pass_update = $conn->prepare("UPDATE customer SET password = ? WHERE customer_id = ?");
      $pass_update->bind_param("si", $new_password, $customer_id);
      
      if ($pass_update->execute()) {
        $success_message = "Password changed successfully!";
      } else {
        $error_message = "Error changing password. Please try again.";
      }
      $pass_update->close();
    } else {
      $error_message = "Current password is incorrect.";
    }
    $pass_check->close();
  }
}

// Fetch customer information
$customer_stmt = $conn->prepare("SELECT * FROM customer WHERE customer_id = ?");
$customer_stmt->bind_param("i", $customer_id);
$customer_stmt->execute();
$customer_result = $customer_stmt->get_result();
$customer = $customer_result->fetch_assoc();
$customer_stmt->close();

if (!$customer) {
  header("Location: login.php");
  exit();
}

// Fetch customer orders
$orders_stmt = $conn->prepare("
  SELECT 
    o.order_id,
    o.order_date,
    o.order_time,
    o.total_amount
  FROM `orders` o
  WHERE o.customer_id = ?
  ORDER BY o.order_date DESC, o.order_time DESC
");
$orders_stmt->bind_param("i", $customer_id);
$orders_stmt->execute();
$orders_result = $orders_stmt->get_result();
$orders_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Profile - Kyla's Bistro</title>
  <link rel="stylesheet" href="style.css" />
  <style>
    .profile-container {
      max-width: 900px;
      margin: 40px auto;
      padding: 20px;
    }
    
    .page-title {
      font-size: 28px;
      margin-bottom: 30px;
      color: #333;
    }
    
    .alert {
      padding: 12px 16px;
      border-radius: 4px;
      margin-bottom: 20px;
    }
    
    .alert-success {
      background: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }
    
    .alert-error {
      background: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }
    
    .profile-section {
      background: white;
      border: 1px solid #ddd;
      border-radius: 4px;
      padding: 25px;
      margin-bottom: 25px;
    }
    
    .section-title {
      font-size: 18px;
      font-weight: 600;
      color: #333;
      margin-bottom: 20px;
      padding-bottom: 10px;
      border-bottom: 1px solid #eee;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .edit-btn {
      background: #007bff;
      color: white;
      padding: 6px 12px;
      border-radius: 3px;
      text-decoration: none;
      font-size: 14px;
      border: none;
      cursor: pointer;
    }
    
    .edit-btn:hover {
      background: #0056b3;
    }
    
    .info-row {
      display: grid;
      grid-template-columns: 150px 1fr;
      padding: 10px 0;
      border-bottom: 1px solid #f5f5f5;
    }
    
    .info-row:last-child {
      border-bottom: none;
    }
    
    .info-label {
      font-weight: 600;
      color: #666;
    }
    
    .info-value {
      color: #333;
    }
    
    .orders-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 15px;
    }
    
    .orders-table th {
      text-align: left;
      padding: 10px;
      background: #f8f9fa;
      border-bottom: 2px solid #dee2e6;
      font-weight: 600;
      color: #333;
    }
    
    .orders-table td {
      padding: 12px 10px;
      border-bottom: 1px solid #dee2e6;
    }
    
    .orders-table tr:hover {
      background: #f8f9fa;
    }
    
    .view-btn {
      background: #28a745;
      color: white;
      padding: 5px 12px;
      border-radius: 3px;
      text-decoration: none;
      font-size: 13px;
    }
    
    .view-btn:hover {
      background: #218838;
    }
    
    .back-btn {
      background: #6c757d;
      color: white;
      padding: 8px 16px;
      border-radius: 3px;
      text-decoration: none;
      display: inline-block;
      margin-bottom: 20px;
    }
    
    .back-btn:hover {
      background: #5a6268;
    }
    
    .empty-state {
      text-align: center;
      padding: 40px 20px;
      color: #999;
    }
  </style>
</head>
<body class="index">
  <header>
    <div class="nav-bar">
      <img src="pictures/logo.jpg" alt="Kyla Logo" class="logo" />
      <div class="nav-actions">
        <?php $cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?>
        <span class="welcome-text">👋 Welcome, <?= htmlspecialchars($_SESSION['username']) ?></span>
        <a href="profile.php" class="btn profile-btn" title="View Profile">👤 Profile</a>
        <a href="cart.php" class="cart-icon" title="View Cart">🛒<?= $cart_count > 0 ? " ($cart_count)" : "" ?></a>
        <a href="customer_logout.php" class="btn logout-btn">LOG OUT</a>
      </div>
    </div>
  </header>

  <nav>
    <ul class="links">
      <li><a href="index.php">HOME</a></li>
      <li><a href="menu.php">MENU</a></li>
      <li><a href="aboutus.php">ABOUT US</a></li>
    </ul>
  </nav>

  <div class="profile-container">
    <a href="menu.php" class="back-btn">← Back to Menu</a>
    
    <?php if ($success_message): ?>
      <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
    <?php endif; ?>
    
    <?php if ($error_message): ?>
      <div class="alert alert-error"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>
    
    <h1 class="page-title">My Profile</h1>

    <div class="profile-section">
      <div class="section-title">
        <span>Personal Information</span>
        <a href="profile_edit.php" class="edit-btn">Edit Profile</a>
      </div>
      
      <div class="info-row">
        <div class="info-label">First Name:</div>
        <div class="info-value"><?= htmlspecialchars($customer['first_name']) ?></div>
      </div>
      <div class="info-row">
        <div class="info-label">Middle Name:</div>
        <div class="info-value"><?= htmlspecialchars($customer['middle_name']) ?: '-' ?></div>
      </div>
      <div class="info-row">
        <div class="info-label">Last Name:</div>
        <div class="info-value"><?= htmlspecialchars($customer['last_name']) ?></div>
      </div>
      <div class="info-row">
        <div class="info-label">Username:</div>
        <div class="info-value"><?= htmlspecialchars($customer['username']) ?></div>
      </div>
      <div class="info-row">
        <div class="info-label">Email:</div>
        <div class="info-value"><?= htmlspecialchars($customer['email']) ?></div>
      </div>
      <div class="info-row">
        <div class="info-label">Phone Number:</div>
        <div class="info-value"><?= htmlspecialchars($customer['phone_number']) ?></div>
      </div>
      <div class="info-row">
        <div class="info-label">Address:</div>
        <div class="info-value"><?= htmlspecialchars($customer['address']) ?></div>
      </div>
    </div>

    <div class="profile-section">
      <h2 class="section-title">Order History</h2>
      
      <?php if ($orders_result && $orders_result->num_rows > 0): ?>
        <table class="orders-table">
          <thead>
            <tr>
              <th>Order ID</th>
              <th>Date</th>
              <th>Time</th>
              <th>Total Amount</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($order = $orders_result->fetch_assoc()): ?>
              <tr>
                <td>#<?= htmlspecialchars($order['order_id']) ?></td>
                <td><?= date('F d, Y', strtotime($order['order_date'])) ?></td>
                <td><?= date('h:i A', strtotime($order['order_time'])) ?></td>
                <td>₱<?= number_format($order['total_amount'], 2) ?></td>
                <td>
                  <a href="view_order.php?id=<?= $order['order_id'] ?>" class="view-btn">View Details</a>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      <?php else: ?>
        <div class="empty-state">
          <p>You haven't placed any orders yet</p>
          <a href="menu.php" class="view-btn">Start Shopping</a>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <section class="banner">
    <img src="pictures/bg.jpg" alt="bg Kyla's Bistro" />
  </section>
</body>
</html>
