<?php
session_start();
include("database.php");

// Check if user is logged in
if (!isset($_SESSION['username'])) {
  header("Location: login.php");
  exit();
}

$cart = $_SESSION['cart'] ?? [];

// Redirect if cart is empty
if (empty($cart)) {
  header("Location: cart.php");
  exit();
}

// Get customer info
$username = $_SESSION['username'];
$cust_stmt = $conn->prepare("SELECT customer_id, first_name, middle_name, last_name, email, phone_number, address FROM customer WHERE username = ?");
$cust_stmt->bind_param("s", $username);
$cust_stmt->execute();
$cust_result = $cust_stmt->get_result();
$customer = $cust_result->fetch_assoc();
$cust_stmt->close();

// Calculate totals
$subtotal = 0;
foreach ($cart as $item) {
  $subtotal += $item['price'] * $item['quantity'];
}
$total = $subtotal;

// Handle order submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $payment_method = $_POST['payment_method'] ?? '';
  $delivery_address = $_POST['delivery_address'] ?? $customer['address'];
  $notes = $_POST['notes'] ?? '';

  if (!empty($payment_method)) {
    $conn->begin_transaction();

    try {
      // Insert order
      $order_stmt = $conn->prepare("INSERT INTO orders (customer_id, order_date, order_time, total_amount) VALUES (?, CURDATE(), CURTIME(), ?)");
      if (!$order_stmt) throw new Exception("Order prepare failed: " . $conn->error);
      $order_stmt->bind_param("id", $customer['customer_id'], $total);
      if (!$order_stmt->execute()) throw new Exception("Order execution failed: " . $order_stmt->error);
      $order_id = $conn->insert_id;
      $order_stmt->close();

      // Insert order items
      $item_stmt = $conn->prepare("INSERT INTO order_item (order_id, product_id, quantity, total_price) VALUES (?, ?, ?, ?)");
      if (!$item_stmt) throw new Exception("Order item prepare failed: " . $conn->error);

      foreach ($cart as $product_id => $item) {
        $quantity = $item['quantity'];
        $total_price = $item['price'] * $quantity;
        $item_stmt->bind_param("iiid", $order_id, $product_id, $quantity, $total_price);
        if (!$item_stmt->execute()) throw new Exception("Order item execution failed: " . $item_stmt->error);
      }
      $item_stmt->close();

      // Insert payment record
      $payment_status = 'Pending';
      $payment_stmt = $conn->prepare("INSERT INTO payment (order_id, payment_date, payment_time, payment_method, payment_status, total_amount) VALUES (?, CURDATE(), CURTIME(), ?, ?, ?)");
      if (!$payment_stmt) throw new Exception("Payment prepare failed: " . $conn->error);
      $payment_stmt->bind_param("issd", $order_id, $payment_method, $payment_status, $total);
      if (!$payment_stmt->execute()) throw new Exception("Payment execution failed: " . $payment_stmt->error);
      $payment_stmt->close();

      // Generate receipt number
      $receipt_number = "RCP-" . date('Ymd') . "-" . str_pad($order_id, 6, '0', STR_PAD_LEFT);

      $conn->commit();

      // Store order details in session
      $_SESSION['last_order'] = [
        'order_id' => $order_id,
        'receipt_number' => $receipt_number,
        'items' => $cart,
        'total' => $total,
        'payment_method' => $payment_method,
        'delivery_address' => $delivery_address,
        'notes' => $notes,
        'customer_name' => trim($customer['first_name'] . ' ' . $customer['middle_name'] . ' ' . $customer['last_name']),
        'customer_email' => $customer['email'],
        'customer_phone' => $customer['phone_number']
      ];

      unset($_SESSION['cart']);
      header("Location: receipt.php?order_id=" . $order_id);
      exit();

    } catch (Exception $e) {
      $conn->rollback();
      $error_message = "Failed to place order: " . $e->getMessage();
    }
  } else {
    $error_message = "Please select a payment method.";
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Checkout | Kyla's Bistro</title>
  <link rel="stylesheet" href="style.css" />
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #f9f6f2;
      color: #2c1810;
      margin: 0;
      padding: 0;
      min-height: 100vh;
    }

    .checkout-wrapper {
      max-width: 1200px;
      margin: 0 auto;
      padding: 40px 20px;
    }

    .header-section {
      margin-bottom: 30px;
    }

    .back-link {
      display: inline-block;
      padding: 10px 20px;
      background: #2c1810;
      color: white;
      text-decoration: none;
      border-radius: 8px;
      margin-bottom: 20px;
      font-weight: 600;
      transition: background-color 0.3s ease;
    }

    .back-link:hover {
      background: #3d2417;
    }

    .page-title {
      font-size: 32px;
      color: #8b4513;
      margin: 0 0 8px 0;
      font-weight: 700;
    }

    .page-subtitle {
      color: #666;
      font-size: 16px;
      margin: 0;
    }

    .checkout-content {
      display: grid;
      grid-template-columns: 1.3fr 1fr;
      gap: 30px;
    }

    .checkout-form,
    .order-summary {
      background: white;
      border-radius: 12px;
      padding: 30px;
      box-shadow: 0 6px 16px rgba(44, 24, 16, 0.1);
    }

    .section-heading {
      font-size: 22px;
      color: #2c1810;
      margin: 0 0 20px 0;
      padding-bottom: 12px;
      border-bottom: 3px solid #d4a574;
      font-weight: 700;
    }

    .form-section {
      margin-bottom: 25px;
    }

    .section-label {
      font-size: 15px;
      font-weight: 600;
      color: #2c1810;
      margin-bottom: 12px;
      display: block;
    }

    .info-box {
      background: #f5e6d3;
      padding: 18px;
      border-radius: 8px;
      font-size: 15px;
      border-left: 4px solid #8b4513;
    }

    .info-row {
      display: flex;
      justify-content: space-between;
      padding: 6px 0;
      color: #2c1810;
    }

    .info-row strong {
      color: #2c1810;
      font-weight: 600;
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-group label {
      display: block;
      font-weight: 600;
      color: #2c1810;
      margin-bottom: 8px;
      font-size: 15px;
    }

    .form-group textarea {
      width: 100%;
      padding: 12px 14px;
      border: 1px solid #d4a574;
      border-radius: 8px;
      font-size: 15px;
      font-family: 'Segoe UI', sans-serif;
      box-sizing: border-box;
      background-color: #f9f9f9;
      transition: border-color 0.3s ease;
    }

    .form-group textarea:focus {
      outline: none;
      border-color: #8b4513;
      background-color: white;
    }

    .payment-options {
      display: grid;
      gap: 12px;
    }

    .payment-option input[type="radio"] {
      display: none;
    }

    .payment-label {
      display: flex;
      align-items: center;
      gap: 15px;
      padding: 16px 18px;
      background: #f9f6f2;
      border: 2px solid #d4a574;
      border-radius: 8px;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .payment-option input[type="radio"]:checked + .payment-label {
      background: #f5e6d3;
      border-color: #8b4513;
      box-shadow: 0 4px 12px rgba(139, 69, 19, 0.15);
    }

    .payment-label:hover {
      border-color: #8b4513;
    }

    .payment-text {
      flex: 1;
    }

    .payment-name {
      font-weight: 700;
      font-size: 16px;
      color: #2c1810;
      margin-bottom: 3px;
    }

    .payment-desc {
      font-size: 13px;
      color: #666;
    }

    .order-items {
      margin-bottom: 20px;
    }

    .order-item {
      display: flex;
      justify-content: space-between;
      padding: 12px 0;
      border-bottom: 1px solid #f0f0f0;
      font-size: 15px;
    }

    .order-item:last-child {
      border-bottom: none;
    }

    .item-details {
      flex: 1;
    }

    .item-name {
      font-weight: 600;
      color: #2c1810;
      margin-bottom: 4px;
    }

    .item-qty {
      font-size: 14px;
      color: #666;
    }

    .item-price {
      font-weight: 700;
      color: #8b4513;
    }

    .summary-totals {
      margin-top: 20px;
      padding-top: 20px;
      border-top: 2px solid #d4a574;
    }

    .summary-row {
      display: flex;
      justify-content: space-between;
      padding: 10px 0;
      font-size: 15px;
    }

    .summary-row.total {
      font-size: 22px;
      font-weight: 700;
      color: #2c1810;
      padding-top: 15px;
      margin-top: 10px;
      border-top: 3px solid #8b4513;
    }

    .place-order-btn {
      width: 100%;
      padding: 16px;
      background-color: #8b4513;
      color: white;
      border: none;
      border-radius: 8px;
      font-size: 18px;
      font-weight: 700;
      cursor: pointer;
      margin-top: 20px;
      box-shadow: 0 6px 16px rgba(139, 69, 19, 0.3);
      transition: background-color 0.3s ease;
    }

    .place-order-btn:hover:not(:disabled) {
      background-color: #6d3610;
    }

    .place-order-btn:disabled {
      opacity: 0.6;
      cursor: not-allowed;
    }

    .error-message {
      background: #fee;
      color: #c00;
      padding: 15px;
      margin-bottom: 20px;
      border-left: 4px solid #c00;
      border-radius: 8px;
      font-size: 15px;
    }

    @media (max-width: 968px) {
      .checkout-content {
        grid-template-columns: 1fr;
      }
    }

    @media (max-width: 576px) {
      .checkout-wrapper {
        padding: 20px 15px;
      }

      .checkout-form,
      .order-summary {
        padding: 20px;
      }

      .page-title {
        font-size: 26px;
      }

      .section-heading {
        font-size: 20px;
      }
    }
  </style>
</head>
<body>
  <div class="checkout-wrapper">
    <div class="header-section">
      <a href="cart.php" class="back-link">← Back to Cart</a>
      <h1 class="page-title">Checkout</h1>
      <p class="page-subtitle">Review your order and complete payment</p>
    </div>

    <?php if (isset($error_message)): ?>
      <div class="error-message"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="checkout-content">
        <div class="checkout-form">
          <h2 class="section-heading">Delivery Information</h2>

          <div class="form-section">
            <span class="section-label">Customer Details</span>
            <div class="info-box">
              <div class="info-row">
                <span>Name:</span>
                <strong><?= htmlspecialchars(trim($customer['first_name'] . ' ' . $customer['middle_name'] . ' ' . $customer['last_name'])) ?></strong>
              </div>
              <div class="info-row">
                <span>Email:</span>
                <strong><?= htmlspecialchars($customer['email']) ?></strong>
              </div>
              <div class="info-row">
                <span>Phone:</span>
                <strong><?= htmlspecialchars($customer['phone_number']) ?></strong>
              </div>
              <div class="info-row">
                <span>Address:</span>
                <strong><?= htmlspecialchars($customer['address']) ?></strong>
              </div>
            </div>
          </div>

          <h2 class="section-heading">Payment Method</h2>

          <div class="payment-options">
            <div class="payment-option">
              <input type="radio" id="cod" name="payment_method" value="Cash on Delivery" required>
              <label for="cod" class="payment-label">
                <div class="payment-text">
                  <div class="payment-name">Cash on Delivery</div>
                  <div class="payment-desc">Pay when your order arrives</div>
                </div>
              </label>
            </div>

            <div class="payment-option">
              <input type="radio" id="paypal" name="payment_method" value="PayPal" required>
              <label for="paypal" class="payment-label">
                <div class="payment-text">
                  <div class="payment-name">PayPal</div>
                  <div class="payment-desc">Pay securely with PayPal</div>
                </div>
              </label>
            </div>
          </div>
        </div>

        <div class="order-summary">
          <h2 class="section-heading">Order Summary</h2>

          <div class="order-items">
            <?php foreach ($cart as $id => $item): ?>
              <div class="order-item">
                <div class="item-details">
                  <div class="item-name"><?= htmlspecialchars($item['product_name']) ?></div>
                  <div class="item-qty"><?= $item['quantity'] ?> × ₱<?= number_format($item['price'], 2) ?></div>
                </div>
                <div class="item-price">
                  ₱<?= number_format($item['price'] * $item['quantity'], 2) ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <div class="summary-totals">
            <div class="summary-row">
              <span>Subtotal:</span>
              <strong>₱<?= number_format($subtotal, 2) ?></strong>
            </div>
            <div class="summary-row total">
              <span>Total:</span>
              <strong>₱<?= number_format($total, 2) ?></strong>
            </div>
          </div>

          <button type="submit" class="place-order-btn">
            Place Order
          </button>
        </div>
      </div>
    </form>
  </div>

  <script>
    document.querySelector('form').addEventListener('submit', function(e) {
      const paymentMethod = document.querySelector('input[name="payment_method"]:checked');
      
      if (!paymentMethod) {
        e.preventDefault();
        alert('Please select a payment method');
        return false;
      }
      
      const btn = document.querySelector('.place-order-btn');
      btn.disabled = true;
      btn.textContent = 'Processing...';
    });
  </script>
</body>
</html>
