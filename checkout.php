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
      $error_message = "⚠️ Failed to place order: " . $e->getMessage();
    }
  } else {
    $error_message = "⚠️ Please select a payment method.";
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
      background: linear-gradient(135deg, #fff7f0 0%, #ffe0e0 100%);
      margin: 0;
      padding: 0;
      min-height: 100vh;
    }

    .checkout-wrapper {
      max-width: 1000px;
      margin: 0 auto;
      padding: 20px 15px;
    }

    .checkout-header {
      text-align: center;
      margin-bottom: 20px;
    }

    .checkout-title {
      font-size: 1.8rem;
      color: #253745;
      margin: 0 0 5px 0;
    }

    .checkout-subtitle {
      color: #666;
      font-size: 0.95rem;
      margin: 0;
    }

    .checkout-content {
      display: grid;
      grid-template-columns: 1.2fr 1fr;
      gap: 20px;
    }

    .checkout-form,
    .order-summary {
      background: white;
      border-radius: 12px;
      padding: 20px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .section-title {
      font-size: 1.2rem;
      color: #253745;
      margin: 0 0 15px 0;
      padding-bottom: 10px;
      border-bottom: 2px solid #c00;
    }

    .form-section {
      margin-bottom: 20px;
    }

    .section-label {
      font-size: 0.95rem;
      font-weight: 600;
      color: #253745;
      margin-bottom: 10px;
      display: block;
    }

    .info-display {
      background: #f8f9fa;
      padding: 12px;
      border-radius: 6px;
      font-size: 0.9rem;
    }

    .info-row {
      display: flex;
      justify-content: space-between;
      padding: 5px 0;
      color: #555;
    }

    .info-label {
      font-weight: 600;
    }

    .form-group {
      margin-bottom: 15px;
    }

    .form-group label {
      display: block;
      font-weight: 600;
      color: #253745;
      margin-bottom: 6px;
      font-size: 0.95rem;
    }

    .form-group input,
    .form-group textarea {
      width: 100%;
      padding: 10px 12px;
      border: 2px solid #e0e0e0;
      border-radius: 6px;
      font-size: 0.95rem;
      font-family: 'Segoe UI', sans-serif;
      box-sizing: border-box;
    }

    .form-group input:focus,
    .form-group textarea:focus {
      outline: none;
      border-color: #c00;
    }

    .form-group textarea {
      resize: vertical;
      min-height: 60px;
    }

    .payment-methods {
      display: grid;
      gap: 10px;
    }

    .payment-option {
      position: relative;
    }

    .payment-option input[type="radio"] {
      display: none;
    }

    .payment-label {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 12px 15px;
      background: #f8f9fa;
      border: 2px solid #e0e0e0;
      border-radius: 8px;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .payment-option input[type="radio"]:checked + .payment-label {
      background: #fff7f0;
      border-color: #c00;
      box-shadow: 0 2px 8px rgba(204, 0, 0, 0.15);
    }

    .payment-label:hover {
      border-color: #c00;
    }

    .payment-icon {
      font-size: 1.5rem;
      width: 40px;
      text-align: center;
    }

    .payment-details {
      flex: 1;
    }

    .payment-name {
      font-weight: 700;
      font-size: 0.95rem;
      color: #253745;
      margin-bottom: 2px;
    }

    .payment-desc {
      font-size: 0.8rem;
      color: #666;
    }

    .order-items {
      margin-bottom: 15px;
    }

    .order-item {
      display: flex;
      justify-content: space-between;
      padding: 10px 0;
      border-bottom: 1px solid #f0f0f0;
      font-size: 0.9rem;
    }

    .order-item:last-child {
      border-bottom: none;
    }

    .item-info {
      flex: 1;
    }

    .item-name {
      font-weight: 600;
      color: #253745;
      margin-bottom: 3px;
    }

    .item-qty {
      font-size: 0.85rem;
      color: #666;
    }

    .item-price {
      font-weight: 700;
      color: #c00;
    }

    .summary-totals {
      margin-top: 15px;
      padding-top: 15px;
      border-top: 2px solid #f0f0f0;
    }

    .summary-row {
      display: flex;
      justify-content: space-between;
      padding: 8px 0;
      font-size: 0.95rem;
    }

    .summary-row.total {
      font-size: 1.2rem;
      font-weight: 700;
      color: #253745;
      padding-top: 12px;
      border-top: 2px solid #e0e0e0;
      margin-top: 8px;
    }

    .place-order-btn {
      width: 100%;
      padding: 14px;
      background: linear-gradient(135deg, #c00 0%, #a00 100%);
      color: white;
      border: none;
      border-radius: 20px;
      font-size: 1rem;
      font-weight: 700;
      cursor: pointer;
      margin-top: 15px;
      transition: all 0.3s ease;
      box-shadow: 0 4px 12px rgba(204, 0, 0, 0.3);
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .place-order-btn:hover:not(:disabled) {
      transform: translateY(-2px);
      box-shadow: 0 6px 16px rgba(204, 0, 0, 0.4);
    }

    .place-order-btn:disabled {
      opacity: 0.6;
      cursor: not-allowed;
    }

    .back-to-cart {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 8px 16px;
      background: #e0e0e0;
      color: #555;
      text-decoration: none;
      border-radius: 20px;
      font-weight: 600;
      margin-bottom: 15px;
      transition: all 0.3s ease;
      font-size: 0.9rem;
    }

    .back-to-cart:hover {
      background: #d0d0d0;
      transform: translateY(-2px);
    }

    .error-message {
      background: #fee;
      color: #c00;
      padding: 12px;
      border-radius: 6px;
      margin-bottom: 15px;
      border-left: 4px solid #c00;
      font-size: 0.9rem;
    }

    @media (max-width: 968px) {
      .checkout-content {
        grid-template-columns: 1fr;
      }
    }

    @media (max-width: 576px) {
      .checkout-wrapper {
        padding: 15px 10px;
      }

      .checkout-form,
      .order-summary {
        padding: 15px;
      }

      .checkout-title {
        font-size: 1.5rem;
      }
    }
  </style>
</head>
<body>
  <div class="checkout-wrapper">
    <a href="cart.php" class="back-to-cart">← Back to Cart</a>

    <div class="checkout-header">
      <h1 class="checkout-title">🛒 Checkout</h1>
      <p class="checkout-subtitle">Review your order and complete payment</p>
    </div>

    <?php if (isset($error_message)): ?>
      <div class="error-message">⚠️ <?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="checkout-content">
        <div class="checkout-form">
          <h2 class="section-title">Delivery Information</h2>

          <div class="form-section">
            <span class="section-label">Customer Details</span>
            <div class="info-display">
              <div class="info-row">
                <span class="info-label">Name:</span>
                <span><?= htmlspecialchars(trim($customer['first_name'] . ' ' . $customer['middle_name'] . ' ' . $customer['last_name'])) ?></span>
              </div>
              <div class="info-row">
                <span class="info-label">Email:</span>
                <span><?= htmlspecialchars($customer['email']) ?></span>
              </div>
              <div class="info-row">
                <span class="info-label">Phone:</span>
                <span><?= htmlspecialchars($customer['phone_number']) ?></span>
              </div>
              <div class="info-row">
                <span class="info-label">Address:</span>
                <span><?= htmlspecialchars($customer['address']) ?></span>
              </div>
            </div>
          </div>

          <div class="form-group">
            <label for="notes">Order Notes (Optional)</label>
            <textarea name="notes" id="notes" placeholder="Any special instructions..."></textarea>
          </div>

          <h2 class="section-title">Payment Method</h2>

          <div class="payment-methods">
            <div class="payment-option">
              <input type="radio" id="cod" name="payment_method" value="Cash on Delivery" required>
              <label for="cod" class="payment-label">
                <span class="payment-icon">💵</span>
                <div class="payment-details">
                  <div class="payment-name">Cash on Delivery</div>
                  <div class="payment-desc">Pay when your order arrives</div>
                </div>
              </label>
            </div>

            <div class="payment-option">
              <input type="radio" id="paypal" name="payment_method" value="PayPal" required>
              <label for="paypal" class="payment-label">
                <span class="payment-icon">💳</span>
                <div class="payment-details">
                  <div class="payment-name">PayPal</div>
                  <div class="payment-desc">Pay securely with PayPal</div>
                </div>
              </label>
            </div>
          </div>
        </div>

        <div class="order-summary">
          <h2 class="section-title">Order Summary</h2>

          <div class="order-items">
            <?php foreach ($cart as $id => $item): ?>
              <div class="order-item">
                <div class="item-info">
                  <div class="item-name"><?= htmlspecialchars($item['product_name']) ?></div>
                  <div class="item-qty">Qty: <?= $item['quantity'] ?> × ₱<?= number_format($item['price'], 2) ?></div>
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
    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
      const paymentMethod = document.querySelector('input[name="payment_method"]:checked');
      
      if (!paymentMethod) {
        e.preventDefault();
        alert('Please select a payment method');
        return false;
      }
      
      // Show loading state
      const btn = document.querySelector('.place-order-btn');
      btn.disabled = true;
      btn.textContent = 'Processing...';
    });
  </script>
</body>
</html>
