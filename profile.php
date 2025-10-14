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
      max-width: 1000px;
      margin: 40px auto;
      padding: 0 20px;
    }
    
    .profile-header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 40px;
      border-radius: 15px;
      text-align: center;
      margin-bottom: 30px;
      box-shadow: 0 5px 20px rgba(102, 126, 234, 0.3);
    }
    
    .profile-header h1 {
      font-size: 32px;
      margin-bottom: 10px;
    }
    
    .profile-header p {
      font-size: 16px;
      opacity: 0.9;
    }
    
    .alert {
      padding: 15px 20px;
      border-radius: 10px;
      margin-bottom: 20px;
      font-weight: 500;
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
      border-radius: 15px;
      padding: 30px;
      margin-bottom: 30px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .section-title {
      font-size: 22px;
      color: #333;
      margin-bottom: 30px;
      padding-bottom: 10px;
      border-bottom: 2px solid #667eea;
      display: flex;
      justify-content: space-between;
      align-items: center;
      
    }
    
    .edit-btn {
      background: #667eea;
      color: white;
      padding: 8px 16px;
      border-radius: 8px;
      text-decoration: none;
      font-size: 14px;
      font-weight: 500;
      cursor: pointer;
      border: none;
      transition: all 0.3s ease;
    }
    
    .edit-btn:hover {
      background: #5568d3;
      transform: translateY(-2px);
    }
    
    .form-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 20px;
      margin-top: 20px;
    }
    
    .form-group {
      display: flex;
      flex-direction: column;
    }
    
    .form-group label {
      font-size: 12px;
      color: #667eea;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 8px;
      font-weight: 600;
    }
    
    .form-group input,
    .form-group textarea {
      padding: 12px 15px;
      border: 2px solid #e8ebf7;
      border-radius: 8px;
      font-size: 15px;
      transition: all 0.3s ease;
      font-family: 'Segoe UI', sans-serif;
    }
    
    .form-group input:focus,
    .form-group textarea:focus {
      outline: none;
      border-color: #667eea;
      background: #f8f9ff;
    }
    
    .form-group textarea {
      resize: vertical;
      min-height: 80px;
    }
    
    .form-actions {
      display: flex;
      gap: 10px;
      justify-content: flex-end;
      margin-top: 20px;
    }
    
    .btn-save {
      background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
      color: white;
      padding: 12px 24px;
      border-radius: 8px;
      border: none;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.3s ease;
    }
    
    .btn-save:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(17, 153, 142, 0.4);
    }
    
    .btn-cancel {
      background: #e0e0e0;
      color: #333;
      padding: 12px 24px;
      border-radius: 8px;
      border: none;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.3s ease;
    }
    
    .btn-cancel:hover {
      background: #d0d0d0;
    }
    
    .info-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 20px;
      margin-top: 20px;
    }
    
    .info-item {
      background: #f8f9ff;
      padding: 15px 20px;
      border-radius: 10px;
      border-left: 4px solid #667eea;
    }
    
    .info-label {
      font-size: 12px;
      color: #667eea;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 5px;
      font-weight: 600;
    }
    
    .info-value {
      font-size: 16px;
      color: #333;
      font-weight: 500;
    }
    
    .orders-table {
      width: 100%;
      border-collapse: separate;
      border-spacing: 0 10px;
      margin-top: 20px;
    }
    
    .orders-table th {
      text-align: left;
      padding: 12px 15px;
      color: #667eea;
      text-transform: uppercase;
      font-size: 13px;
      letter-spacing: 0.5px;
      font-weight: 600;
    }
    
    .orders-table td {
      padding: 15px;
      background: #f8f9ff;
      border-top: 1px solid #e8ebf7;
      border-bottom: 1px solid #e8ebf7;
    }
    
    .orders-table td:first-child {
      border-left: 1px solid #e8ebf7;
      border-radius: 10px 0 0 10px;
    }
    
    .orders-table td:last-child {
      border-right: 1px solid #e8ebf7;
      border-radius: 0 10px 10px 0;
    }
    
    .orders-table tr:hover td {
      background: #eef1ff;
      transform: scale(1.01);
      transition: all 0.2s ease;
    }
    
    .view-btn {
      background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
      color: white;
      padding: 8px 16px;
      border-radius: 8px;
      text-decoration: none;
      font-size: 14px;
      font-weight: 500;
      transition: all 0.3s ease;
      display: inline-block;
    }
    
    .view-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(79, 172, 254, 0.4);
    }
    
    .back-btn {
      background: #667eea;
      color: white;
      padding: 12px 24px;
      border-radius: 8px;
      text-decoration: none;
      font-weight: 500;
      display: inline-block;
      margin-bottom: 20px;
      transition: all 0.3s ease;
    }
    
    .back-btn:hover {
      background: #5568d3;
      transform: translateY(-2px);
    }
    
    .empty-state {
      text-align: center;
      padding: 60px 20px;
      color: #999;
    }
    
    .empty-state svg {
      width: 80px;
      height: 80px;
      margin-bottom: 20px;
      opacity: 0.3;
    }
    
    .edit-mode {
      display: none;
    }
    
    .view-mode {
      display: block;
    }
    
    .required {
      color: red;
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
        <a href="cart.php" class="cart-icon" title="View Cart">🛒<?= $cart_count > 0 ? "($cart_count)" : "" ?></a>
        <a href="customer_logout.php" class="btn logout-btn">LOG OUT</a>
      </div>
    </div>
  </header>

  <nav>
    <ul class="links">
      <li><a href="index.php">HOME</a></li>
      <li><a href="menu.php">MENU</a></li>
      <li><a href="#">FEEDBACK</a></li>
      <li><a href="aboutus.php">ABOUT US</a></li>
    </ul>
  </nav>

  <div class="profile-container">
    <a href="menu.php" class="back-btn">← Back to Menu</a>
    
    <?php if ($success_message): ?>
      <div class="alert alert-success">✓ <?= htmlspecialchars($success_message) ?></div>
    <?php endif; ?>
    
    <?php if ($error_message): ?>
      <div class="alert alert-error">⚠ <?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>
    
    <div class="profile-header">
      <h1>👤 My Profile</h1>
      <p>View and manage your personal information</p>
    </div>

    <div class="profile-section">
      <div class="section-title">
        <span>Personal Information</span>
        <a href="profile_edit.php" class="edit-btn">✏️ Edit Profile</a>
      </div>
      
      <!-- View Mode -->
      <div id="viewMode" class="view-mode">
        <div class="info-grid">
          <div class="info-item">
            <div class="info-label">First Name</div>
            <div class="info-value"><?= htmlspecialchars($customer['first_name']) ?></div>
          </div>
          <div class="info-item">
            <div class="info-label">Middle Name</div>
            <div class="info-value"><?= htmlspecialchars($customer['middle_name']) ?: '-' ?></div>
          </div>
          <div class="info-item">
            <div class="info-label">Last Name</div>
            <div class="info-value"><?= htmlspecialchars($customer['last_name']) ?></div>
          </div>
          <div class="info-item">
            <div class="info-label">Username</div>
            <div class="info-value"><?= htmlspecialchars($customer['username']) ?></div>
          </div>
          <div class="info-item">
            <div class="info-label">Email Address</div>
            <div class="info-value"><?= htmlspecialchars($customer['email']) ?></div>
          </div>
          <div class="info-item">
            <div class="info-label">Phone Number</div>
            <div class="info-value"><?= htmlspecialchars($customer['phone_number']) ?></div>
          </div>
          <div class="info-item" style="grid-column: 1 / -1;">
            <div class="info-label">Address</div>
            <div class="info-value"><?= htmlspecialchars($customer['address']) ?></div>
          </div>
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
                <td><strong>#<?= htmlspecialchars($order['order_id']) ?></strong></td>
                <td><?= date('F d, Y', strtotime($order['order_date'])) ?></td>
                <td><?= date('h:i A', strtotime($order['order_time'])) ?></td>
                <td><strong>₱<?= number_format($order['total_amount'], 2) ?></strong></td>
                <td>
                  <a href="view_order.php?id=<?= $order['order_id'] ?>" class="view-btn">View Details</a>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      <?php else: ?>
        <div class="empty-state">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
          </svg>
          <p>You haven't placed any orders yet</p>
          <a href="menu.php" class="view-btn" style="margin-top: 20px;">Start Shopping</a>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <section class="banner">
    <img src="pictures/bg.jpg" alt="bg Kyla's Bistro" />
  </section>
</body>
</html>
