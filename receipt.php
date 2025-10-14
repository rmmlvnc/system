<?php
session_start();
include("database.php");

// Check if order_id is provided
if (!isset($_GET['order_id']) || !isset($_SESSION['last_order'])) {
  header("Location: index.php");
  exit();
}

$order_id = $_GET['order_id'];
$order = $_SESSION['last_order'];

// Verify order_id matches
if ($order['order_id'] != $order_id) {
  header("Location: index.php");
  exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Order Receipt | Kyla's Bistro</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(135deg, #fff7f0 0%, #ffe0e0 100%);
      padding: 20px;
      min-height: 100vh;
    }

    .container {
      max-width: 800px;
      margin: 0 auto;
    }

    .success-header {
      text-align: center;
      margin-bottom: 30px;
      animation: fadeIn 0.6s ease;
    }

    .success-icon {
      width: 80px;
      height: 80px;
      background: linear-gradient(135deg, #28a745, #20c997);
      border-radius: 50%;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 3rem;
      color: white;
      margin-bottom: 15px;
      animation: scaleIn 0.5s ease;
    }

    .success-title {
      font-size: 2rem;
      color: #253745;
      margin-bottom: 10px;
    }

    .success-subtitle {
      color: #666;
      font-size: 1.1rem;
    }

    .receipt-card {
      background: white;
      border-radius: 16px;
      box-shadow: 0 8px 24px rgba(0,0,0,0.12);
      overflow: hidden;
      animation: slideUp 0.6s ease;
    }

    .receipt-header {
      background: linear-gradient(135deg, #c00 0%, #a00 100%);
      color: white;
      padding: 30px;
      text-align: center;
    }

    .receipt-header h1 {
      font-size: 2rem;
      margin-bottom: 10px;
    }

    .receipt-number {
      font-size: 1.3rem;
      font-weight: 700;
      background: rgba(255,255,255,0.2);
      display: inline-block;
      padding: 10px 25px;
      border-radius: 25px;
      margin-top: 10px;
      letter-spacing: 1px;
    }

    .order-id-badge {
      display: inline-block;
      background: rgba(255,255,255,0.3);
      padding: 8px 20px;
      border-radius: 20px;
      margin-top: 8px;
      font-size: 1rem;
    }

    .receipt-body {
      padding: 30px;
    }

    .info-section {
      margin-bottom: 30px;
    }

    .section-title {
      font-size: 1.2rem;
      font-weight: 700;
      color: #253745;
      margin-bottom: 15px;
      padding-bottom: 10px;
      border-bottom: 2px solid #f0f0f0;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .info-grid {
      display: grid;
      gap: 12px;
    }

    .info-row {
      display: flex;
      justify-content: space-between;
      padding: 10px;
      background: #f8f9fa;
      border-radius: 6px;
      font-size: 0.95rem;
    }

    .info-label {
      font-weight: 600;
      color: #555;
    }

    .info-value {
      color: #253745;
      text-align: right;
      font-weight: 500;
    }

    .status-badge {
      display: inline-block;
      padding: 5px 15px;
      border-radius: 20px;
      font-size: 0.85rem;
      font-weight: 600;
      background: #ffc107;
      color: #856404;
    }

    .address-box {
      background: #f8f9fa;
      padding: 15px;
      border-radius: 8px;
      color: #555;
      line-height: 1.6;
      border-left: 4px solid #c00;
    }

    .notes-box {
      background: #e7f3ff;
      padding: 12px;
      border-radius: 6px;
      color: #004085;
      font-style: italic;
      margin-top: 10px;
    }

    .order-items-list {
      margin-bottom: 20px;
    }

    .item-card {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 15px;
      background: #f8f9fa;
      border-radius: 8px;
      margin-bottom: 10px;
      border-left: 4px solid #c00;
    }

    .item-details {
      flex: 1;
    }

    .item-name {
      font-weight: 700;
      color: #253745;
      margin-bottom: 5px;
      font-size: 1rem;
    }

    .item-qty {
      font-size: 0.9rem;
      color: #666;
    }

    .item-price {
      font-weight: 700;
      color: #c00;
      font-size: 1.2rem;
    }

    .total-section {
      background: #f8f9fa;
      padding: 20px;
      border-radius: 12px;
      margin-top: 20px;
    }

    .total-row {
      display: flex;
      justify-content: space-between;
      padding: 10px 0;
      font-size: 1rem;
    }

    .total-row.grand {
      font-size: 1.5rem;
      font-weight: 700;
      color: #c00;
      padding-top: 15px;
      margin-top: 10px;
      border-top: 2px solid #dee2e6;
    }

    .alert-box {
      background: #fff3cd;
      border: 2px solid #ffc107;
      border-radius: 8px;
      padding: 15px;
      margin-top: 20px;
      display: flex;
      gap: 12px;
    }

    .alert-icon {
      font-size: 1.5rem;
    }

    .alert-content {
      flex: 1;
      color: #856404;
    }

    .alert-content strong {
      display: block;
      margin-bottom: 5px;
      font-size: 1rem;
    }

    .alert-content p {
      font-size: 0.9rem;
      line-height: 1.5;
    }

    .action-buttons {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 15px;
      margin-top: 30px;
    }

    .btn {
      padding: 14px 24px;
      border: none;
      border-radius: 25px;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      text-align: center;
      text-decoration: none;
      display: inline-block;
    }

    .btn-print {
      background: #6c757d;
      color: white;
    }

    .btn-print:hover {
      background: #5a6268;
      transform: translateY(-2px);
    }

    .btn-home {
      background: linear-gradient(135deg, #c00 0%, #a00 100%);
      color: white;
      box-shadow: 0 4px 12px rgba(204, 0, 0, 0.3);
    }

    .btn-home:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 16px rgba(204, 0, 0, 0.4);
    }

    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }

    @keyframes scaleIn {
      from { transform: scale(0); }
      to { transform: scale(1); }
    }

    @keyframes slideUp {
      from {
        opacity: 0;
        transform: translateY(20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @media print {
      body {
        background: white;
        padding: 0;
      }

      .success-header,
      .action-buttons,
      .alert-box {
        display: none;
      }

      .receipt-card {
        box-shadow: none;
      }
    }

    @media (max-width: 576px) {
      .action-buttons {
        grid-template-columns: 1fr;
      }

      .success-title {
        font-size: 1.5rem;
      }

      .receipt-header h1 {
        font-size: 1.5rem;
      }

      .receipt-number {
        font-size: 1rem;
      }

      .receipt-body {
        padding: 20px;
      }

      .item-card {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="success-header">
      <div class="success-icon">✓</div>
      <h1 class="success-title">Order Placed Successfully!</h1>
      <p class="success-subtitle">Thank you for your order</p>
    </div>

    <div class="receipt-card">
      <div class="receipt-header">
        <h1>🍽️ Kyla's Bistro</h1>
        <p>Official Order Receipt</p>
        <div class="order-id-badge">Order ID: #<?= htmlspecialchars($order['order_id']) ?></div>
        <div class="receipt-number"><?= htmlspecialchars($order['receipt_number']) ?></div>
      </div>

      <div class="receipt-body">
        <!-- Customer Information -->
        <div class="info-section">
          <h2 class="section-title">👤 Customer Information</h2>
          <div class="info-grid">
            <div class="info-row">
              <span class="info-label">Name:</span>
              <span class="info-value"><?= htmlspecialchars($order['customer_name']) ?></span>
            </div>
            <div class="info-row">
              <span class="info-label">Email:</span>
              <span class="info-value"><?= htmlspecialchars($order['customer_email']) ?></span>
            </div>
            <div class="info-row">
              <span class="info-label">Phone:</span>
              <span class="info-value"><?= htmlspecialchars($order['customer_phone']) ?></span>
            </div>
          </div>
        </div>

        <!-- Order Details -->
        <div class="info-section">
          <h2 class="section-title">📋 Order Details</h2>
          <div class="info-grid">
            <div class="info-row">
              <span class="info-label">Order Date:</span>
              <span class="info-value"><?= date('F j, Y') ?></span>
            </div>
            <div class="info-row">
              <span class="info-label">Order Time:</span>
              <span class="info-value"><?= date('g:i A') ?></span>
            </div>
            <div class="info-row">
              <span class="info-label">Payment Method:</span>
              <span class="info-value"><?= htmlspecialchars($order['payment_method']) ?></span>
            </div>
            <div class="info-row">
              <span class="info-label">Status:</span>
              <span class="info-value"><span class="status-badge">Pending</span></span>
            </div>
          </div>
        </div>

        <!-- Delivery Address -->
        <div class="info-section">
          <h2 class="section-title">📍 Delivery Address</h2>
          <div class="address-box">
            <?= nl2br(htmlspecialchars($order['customer_address'])) ?>
          </div>
        </div>

        <!-- Order Items -->
        <div class="info-section">
          <h2 class="section-title">🛒 Order Items</h2>
          <div class="order-items-list">
            <?php foreach ($order['items'] as $item): ?>
              <div class="item-card">
                <div class="item-details">
                  <div class="item-name"><?= htmlspecialchars($item['product_name']) ?></div>
                  <div class="item-qty">
                    Quantity: <?= $item['quantity'] ?> × ₱<?= number_format($item['price'], 2) ?>
                  </div>
                </div>
                <div class="item-price">
                  ₱<?= number_format($item['price'] * $item['quantity'], 2) ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Total Section -->
        <div class="total-section">
          <div class="total-row">
            <span>Subtotal:</span>
            <strong>₱<?= number_format($order['total'], 2) ?></strong>
          </div>
          <div class="total-row">
            <span>Delivery Fee:</span>
            <strong>₱0.00</strong>
          </div>
          <div class="total-row grand">
            <span>Grand Total:</span>
            <strong>₱<?= number_format($order['total'], 2) ?></strong>
          </div>
        </div>

        <div class="alert-box">
          <div class="alert-icon">⚠️</div>
          <div class="alert-content">
            <strong>Important Notice</strong>
            <p>
              Your order is currently pending. We will contact you shortly to confirm your order.
              <?php if ($order['payment_method'] === 'Cash on Delivery'): ?>
                Please prepare the exact amount (₱<?= number_format($order['total'], 2) ?>) for faster processing.
              <?php endif; ?>
              Keep this receipt number for reference: <strong><?= htmlspecialchars($order['receipt_number']) ?></strong>
            </p>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
          <button onclick="window.print()" class="btn btn-print">
            🖨️ Print Receipt
          </button>
          <a href="index.php" class="btn btn-home">
            🏠 Back to Home
          </a>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
