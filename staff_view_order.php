<?php
session_start();
include 'database.php';

if (!isset($_SESSION['staff_id'])) {
  header("Location: staff_login.php");
  exit();
}

$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch order details with customer info
$order_stmt = $conn->prepare("
  SELECT o.*, c.first_name, c.last_name, c.phone_number, c.email
  FROM `orders` o
  LEFT JOIN customer c ON o.customer_id = c.customer_id
  WHERE o.order_id = ?
");
$order_stmt->bind_param("i", $order_id);
$order_stmt->execute();
$order_result = $order_stmt->get_result();
$order = $order_result->fetch_assoc();
$order_stmt->close();

if (!$order) {
  header("Location: staff.php");
  exit();
}

// Fetch order items with product details
$items_stmt = $conn->prepare("
  SELECT 
    oi.order_item_id,
    oi.quantity,
    oi.total_price,
    p.product_name,
    p.price,
    p.image,
    p.description
  FROM order_item oi
  LEFT JOIN product p ON oi.product_id = p.product_id
  WHERE oi.order_id = ?
");
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Order #<?= $order_id ?> - Details</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    
    body { 
      font-family: 'Inter', 'Segoe UI', sans-serif; 
      background: darkslategray;
      min-height: 100vh;
      padding: 20px;
    }
    
    .container {
      max-width: 1000px;
      margin: 0 auto;
    }
    
    .header-bar {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      padding: 20px 30px;
      border-radius: 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 25px;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    }
    
    .header-bar h1 {
      font-size: 24px;
      background: black;
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }
    
    .btn {
      padding: 10px 20px;
      background: gray;
      color: white;
      border: none;
      border-radius: 10px;
      text-decoration: none;
      font-weight: 500;
      transition: all 0.3s ease;
      display: inline-block;
    }

    .btn-edit {
      background: green;
      margin-left: 10px;
    }

    .order-card {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      border-radius: 20px;
      padding: 30px;
      margin-bottom: 25px;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    }
    
    .order-header {
      display: flex;
      justify-content: space-between;
      align-items: start;
      margin-bottom: 30px;
      padding-bottom: 20px;
      border-bottom: 2px solid #f0f0f0;
    }
    
    .order-number {
      font-size: 32px;
      font-weight: bold;
      background: black;
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }
    
    .order-date {
      text-align: right;
      color: #666;
    }
    
    .order-date .date {
      font-size: 18px;
      font-weight: 600;
      color: #333;
    }
    
    .order-date .time {
      font-size: 14px;
      margin-top: 5px;
    }
    
    .info-section {
      margin-bottom: 30px;
    }
    
    .info-section h3 {
      font-size: 16px;
      color: #667eea;
      text-transform: uppercase;
      letter-spacing: 1px;
      margin-bottom: 15px;
    }
    
    .customer-info {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 15px;
    }
    
    .info-box {
      background: #f8f9ff;
      padding: 15px 20px;
      border-radius: 12px;
      border-left: 3px solid #667eea;
    }
    
    .info-box label {
      display: block;
      font-size: 11px;
      color: #667eea;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 5px;
    }
    
    .info-box .value {
      font-size: 16px;
      color: #333;
      font-weight: 500;
    }
    
    .items-section h3 {
      font-size: 18px;
      color: #333;
      margin-bottom: 20px;
    }
    
    .order-item {
      background: #f8f9ff;
      border-radius: 15px;
      padding: 20px;
      margin-bottom: 15px;
      display: flex;
      gap: 20px;
      align-items: center;
      transition: all 0.3s ease;
    }
    
    .item-image {
      width: 80px;
      height: 80px;
      border-radius: 12px;
      object-fit: cover;
      border: 2px solid #e8ebf7;
    }
    
    .item-details {
      flex: 1;
    }
    
    .item-name {
      font-size: 18px;
      font-weight: 600;
      color: #333;
      margin-bottom: 5px;
    }
    
    .item-description {
      font-size: 13px;
      color: #666;
      margin-bottom: 8px;
    }
    
    .item-price {
      font-size: 14px;
      color: #667eea;
    }
    
    .item-quantity {
      text-align: center;
      padding: 0 20px;
    }
    
    .item-quantity .label {
      font-size: 11px;
      color: #999;
      text-transform: uppercase;
    }
    
    .item-quantity .qty {
      font-size: 24px;
      font-weight: bold;
      color: #667eea;
    }
    
    .item-total {
      text-align: right;
      padding: 0 20px;
    }
    
    .item-total .label {
      font-size: 11px;
      color: #999;
      text-transform: uppercase;
    }
    
    .item-total .amount {
      font-size: 20px;
      font-weight: bold;
      color: #333;
    }
    
    .summary-card {
      background: red;
      border-radius: 15px;
      padding: 25px;
      color: white;
      margin-top: 30px;
    }
    
    .summary-row {
      display: flex;
      justify-content: space-between;
      padding: 10px 0;
      border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    .summary-row:last-child {
      border-bottom: none;
      margin-top: 10px;
      padding-top: 20px;
      border-top: 2px solid rgba(255, 255, 255, 0.3);
    }
    
    .summary-row.total {
      font-size: 24px;
      font-weight: bold;
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
    
    @media print {
      body { background: white; padding: 0; }
      .header-bar, .btn { display: none; }
      .order-card { box-shadow: none; }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="header-bar">
      <h1>📋 Order Details</h1>
      <div>
        <a href="staff.php" class="btn">← Back</a>
        <a href="staff_edit_order.php?id=<?= $order_id ?>" class="btn btn-edit">✏️ Edit Order</a>
      </div>
    </div>

    <div class="order-card">
      <div class="order-header">
        <div>
          <div class="order-number">Order #<?= htmlspecialchars($order_id) ?></div>
        </div>
        <div class="order-date">
          <div class="date"><?= date('F d, Y', strtotime($order['order_date'])) ?></div>
          <div class="time">🕐 <?= date('h:i A', strtotime($order['order_time'])) ?></div>
        </div>
      </div>

      <div class="info-section">
        <h3>👤 Customer Information</h3>
        <div class="customer-info">
          <div class="info-box">
            <label>Customer Name</label>
            <div class="value"><?= htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) ?></div>
          </div>
          <div class="info-box">
            <label>Phone Number</label>
            <div class="value"><?= htmlspecialchars($order['phone_number']) ?></div>
          </div>
          <div class="info-box">
            <label>Email Address</label>
            <div class="value"><?= htmlspecialchars($order['email']) ?></div>
          </div>
        </div>
      </div>

      <div class="items-section">
        <h3>🛒 Order Items</h3>
        
        <?php if ($items_result && $items_result->num_rows > 0): ?>
          <?php while ($item = $items_result->fetch_assoc()): ?>
            <div class="order-item">
              <img src="uploads/<?= htmlspecialchars($item['image']) ?>" 
                   alt="<?= htmlspecialchars($item['product_name']) ?>" 
                   class="item-image">
              
              <div class="item-details">
                <div class="item-name"><?= htmlspecialchars($item['product_name']) ?></div>
                <div class="item-description"><?= htmlspecialchars($item['description']) ?></div>
                <div class="item-price">₱<?= number_format($item['price'], 2) ?> per item</div>
              </div>
              
              <div class="item-quantity">
                <div class="label">Quantity</div>
                <div class="qty">×<?= htmlspecialchars($item['quantity']) ?></div>
              </div>
              
              <div class="item-total">
                <div class="label">Total</div>
                <div class="amount">₱<?= number_format($item['total_price'], 2) ?></div>
              </div>
            </div>
          <?php endwhile; ?>
        <?php else: ?>
          <div class="empty-state">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
            <p>No items in this order</p>
          </div>
        <?php endif; ?>
      </div>

      <div class="summary-card">
        <div class="summary-row total">
          <span>Total Amount</span>
          <span>₱<?= number_format($order['total_amount'], 2) ?></span>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
