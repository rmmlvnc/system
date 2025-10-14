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

// Check if customer has a temporary/pending order (acts as cart)
// We'll use order_id stored in session or find the latest incomplete order
if (!isset($_SESSION['cart_order_id'])) {
  // Try to find an existing pending order
  $order_check = $conn->prepare("
    SELECT order_id 
    FROM orders 
    WHERE customer_id = ? 
    ORDER BY order_id DESC 
    LIMIT 1
  ");
  
  if ($order_check === false) {
    die("Error preparing order check: " . $conn->error);
  }
  
  $order_check->bind_param("i", $customer_id);
  $order_check->execute();
  $order_result = $order_check->get_result();
  $order_row = $order_result->fetch_assoc();
  
  if ($order_row) {
    $_SESSION['cart_order_id'] = $order_row['order_id'];
  }
  $order_check->close();
}

$cart = [];
$subtotal = 0;

if (isset($_SESSION['cart_order_id'])) {
  $order_id = $_SESSION['cart_order_id'];
  
  // Fetch cart items from order_item
  $cart_stmt = $conn->prepare("
    SELECT oi.product_id, oi.quantity, p.product_name, p.price, p.image, oi.total_price
    FROM order_item oi
    JOIN product p ON oi.product_id = p.product_id
    WHERE oi.order_id = ?
  ");
  
  if ($cart_stmt === false) {
    die("Error preparing cart query: " . $conn->error);
  }
  
  $cart_stmt->bind_param("i", $order_id);
  $cart_stmt->execute();
  $cart_result = $cart_stmt->get_result();
  
  while ($row = $cart_result->fetch_assoc()) {
    $price = $row['total_price'] / $row['quantity']; // Calculate unit price
    $cart[$row['product_id']] = [
      'product_name' => $row['product_name'],
      'price' => (float)$price,
      'image' => $row['image'],
      'quantity' => (int)$row['quantity']
    ];
    $subtotal += $row['total_price'];
  }
  
  $cart_stmt->close();
}

$total = $subtotal;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Your Cart | Kyla's Bistro</title>
  <link rel="stylesheet" href="style.css" />
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: linear-gradient(135deg, #fff7f0 0%, #ffe0e0 100%);
      margin: 0;
      padding: 0;
      min-height: 100vh;
    }

    .cart-wrapper {
      max-width: 1200px;
      margin: 0 auto;
      padding: 40px 20px;
    }

    .cart-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 40px;
      flex-wrap: wrap;
      gap: 20px;
    }

    .cart-title {
      display: flex;
      align-items: center;
      gap: 15px;
      font-size: 2.5rem;
      color: #253745;
      margin: 0;
    }

    .cart-icon {
      font-size: 3rem;
    }

    .back-link {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 12px 24px;
      background-color: #c00;
      color: white;
      text-decoration: none;
      border-radius: 25px;
      font-weight: 600;
      transition: all 0.3s ease;
      box-shadow: 0 4px 10px rgba(204, 0, 0, 0.2);
    }

    .back-link:hover {
      background-color: #a00;
      transform: translateY(-2px);
      box-shadow: 0 6px 14px rgba(204, 0, 0, 0.3);
    }

    .cart-content {
      display: grid;
      grid-template-columns: 1fr 400px;
      gap: 30px;
      align-items: start;
    }

    .cart-items {
      background: white;
      border-radius: 16px;
      padding: 30px;
      box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    }

    .cart-item {
      display: grid;
      grid-template-columns: 100px 1fr auto;
      gap: 20px;
      padding: 20px;
      border-bottom: 2px solid #f0f0f0;
      transition: all 0.3s ease;
      align-items: center;
    }

    .cart-item:last-child {
      border-bottom: none;
    }

    .cart-item:hover {
      background-color: #f8f9fa;
      border-radius: 8px;
    }

    .item-image {
      width: 100px;
      height: 100px;
      object-fit: cover;
      border-radius: 12px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    .item-details {
      flex: 1;
    }

    .item-name {
      font-size: 1.3rem;
      font-weight: 700;
      color: #253745;
      margin-bottom: 8px;
    }

    .item-price {
      font-size: 1.1rem;
      color: #c00;
      font-weight: 600;
      margin-bottom: 12px;
    }

    .item-quantity-section {
      display: flex;
      align-items: center;
      gap: 15px;
    }

    .quantity-display {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 8px 16px;
      background: #f8f9fa;
      border-radius: 8px;
      font-weight: 600;
      color: #253745;
    }

    .quantity-edit-form {
      display: none;
      align-items: center;
      gap: 10px;
    }

    .quantity-edit-form.active {
      display: flex;
    }

    .quantity-btn {
      width: 32px;
      height: 32px;
      border: none;
      background: #c00;
      color: white;
      border-radius: 6px;
      cursor: pointer;
      font-size: 1.1rem;
      font-weight: bold;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .quantity-btn:hover {
      background: #a00;
      transform: scale(1.1);
    }

    .quantity-input {
      width: 60px;
      padding: 8px;
      border: 2px solid #e0e0e0;
      border-radius: 6px;
      text-align: center;
      font-size: 1rem;
      font-weight: 600;
    }

    .item-actions {
      display: flex;
      flex-direction: column;
      gap: 10px;
      align-items: flex-end;
    }

    .item-total {
      font-size: 1.4rem;
      font-weight: 700;
      color: #253745;
      margin-bottom: 10px;
    }

    .btn-group {
      display: flex;
      gap: 8px;
    }

    .btn {
      padding: 8px 16px;
      border: none;
      border-radius: 8px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      font-size: 0.9rem;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 5px;
    }

    .btn-edit {
      background: #4db8ff;
      color: white;
    }

    .btn-edit:hover {
      background: #3399ff;
      transform: scale(1.05);
    }

    .btn-save {
      background: #2e7d32;
      color: white;
    }

    .btn-save:hover {
      background: #1b5e20;
      transform: scale(1.05);
    }

    .btn-cancel {
      background: #757575;
      color: white;
    }

    .btn-cancel:hover {
      background: #616161;
    }

    .btn-remove {
      background: #ff4757;
      color: white;
    }

    .btn-remove:hover {
      background: #ee2f3d;
      transform: scale(1.05);
    }

    .cart-summary {
      background: white;
      border-radius: 16px;
      padding: 30px;
      box-shadow: 0 8px 24px rgba(0,0,0,0.12);
      position: sticky;
      top: 20px;
    }

    .summary-title {
      font-size: 1.5rem;
      color: #253745;
      margin-bottom: 25px;
      padding-bottom: 15px;
      border-bottom: 3px solid #c00;
    }

    .summary-row {
      display: flex;
      justify-content: space-between;
      padding: 12px 0;
      font-size: 1rem;
      color: #555;
    }

    .summary-row.total {
      margin-top: 15px;
      padding-top: 15px;
      border-top: 2px solid #e0e0e0;
      font-size: 1.3rem;
      font-weight: 700;
      color: #253745;
    }

    .checkout-btn {
      width: 100%;
      padding: 16px;
      background: linear-gradient(135deg, #c00 0%, #a00 100%);
      color: white;
      border: none;
      border-radius: 25px;
      font-size: 1.1rem;
      font-weight: 700;
      cursor: pointer;
      margin-top: 20px;
      transition: all 0.3s ease;
      box-shadow: 0 6px 16px rgba(204, 0, 0, 0.3);
      text-transform: uppercase;
      letter-spacing: 1px;
      text-decoration: none;
      display: block;
      text-align: center;
    }

    .checkout-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(204, 0, 0, 0.4);
    }

    .empty-cart {
      background: white;
      border-radius: 16px;
      padding: 60px 40px;
      box-shadow: 0 8px 24px rgba(0,0,0,0.12);
      text-align: center;
    }

    .empty-cart-icon {
      font-size: 6rem;
      margin-bottom: 20px;
      opacity: 0.3;
    }

    .empty-cart h3 {
      font-size: 1.8rem;
      color: #253745;
      margin-bottom: 15px;
    }

    .empty-cart p {
      color: #666;
      font-size: 1.1rem;
      margin-bottom: 30px;
    }

    @media (max-width: 968px) {
      .cart-content {
        grid-template-columns: 1fr;
      }

      .cart-summary {
        position: static;
      }

      .cart-item {
        grid-template-columns: 80px 1fr;
        gap: 15px;
      }

      .item-actions {
        grid-column: 1 / -1;
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
      }

      .cart-title {
        font-size: 2rem;
      }
    }

    @media (max-width: 576px) {
      .cart-wrapper {
        padding: 20px 15px;
      }

      .cart-header {
        flex-direction: column;
        align-items: flex-start;
      }

      .item-name {
        font-size: 1.1rem;
      }

      .btn-group {
        flex-wrap: wrap;
      }
    }
  </style>
</head>
<body>
  <div class="cart-wrapper">
    <div class="cart-header">
      <h1 class="cart-title">
        <span class="cart-icon">🛒</span>
        Your Cart
      </h1>
      <a href="menu.php" class="back-link">← Continue Shopping</a>
    </div>

    <?php if (count($cart) > 0): ?>
      <div class="cart-content">
        <div class="cart-items">
          <?php foreach ($cart as $id => $item): ?>
            <div class="cart-item">
              <img src="pictures/<?= htmlspecialchars($item['image'] ?? 'placeholder.jpg') ?>" alt="<?= htmlspecialchars($item['product_name']) ?>" class="item-image" onerror="this.src='pictures/placeholder.jpg'">
              
              <div class="item-details">
                <div class="item-name"><?= htmlspecialchars($item['product_name']) ?></div>
                <div class="item-price">₱<?= number_format($item['price'], 2) ?></div>
                
                <div class="item-quantity-section">
                  <!-- Display Mode -->
                  <div class="quantity-display" id="qty-display-<?= $id ?>">
                    <span>Quantity:</span>
                    <strong><?= $item['quantity'] ?></strong>
                  </div>
                  
                  <!-- Edit Mode Form -->
                  <form method="POST" action="update_cart.php" class="quantity-edit-form" id="qty-edit-<?= $id ?>">
                    <input type="hidden" name="item_id" value="<?= $id ?>">
                    <button type="button" class="quantity-btn" onclick="decreaseQuantity(<?= $id ?>)">−</button>
                    <input type="number" class="quantity-input" name="quantity" id="qty-input-<?= $id ?>" value="<?= $item['quantity'] ?>" min="1" max="99">
                    <button type="button" class="quantity-btn" onclick="increaseQuantity(<?= $id ?>)">+</button>
                    <button type="submit" class="btn btn-save">✓ Save</button>
                    <button type="button" class="btn btn-cancel" onclick="cancelEdit(<?= $id ?>, <?= $item['quantity'] ?>)">✕</button>
                  </form>
                </div>
              </div>

              <div class="item-actions">
                <div class="item-total">
                  ₱<?= number_format($item['price'] * $item['quantity'], 2) ?>
                </div>
                
                <div class="btn-group">
                  <button class="btn btn-edit" id="btn-edit-<?= $id ?>" onclick="enableEdit(<?= $id ?>)">
                    ✏️ Edit
                  </button>
                  <a href="remove_from_cart.php?id=<?= $id ?>" class="btn btn-remove" onclick="return confirm('Remove this item from cart?')">
                    🗑️ Remove
                  </a>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <div class="cart-summary">
          <h2 class="summary-title">Order Summary</h2>
          
          <div class="summary-row">
            <span>Subtotal:</span>
            <strong>₱<?= number_format($subtotal, 2) ?></strong>
          </div>
          
          <div class="summary-row total">
            <span>Total:</span>
            <strong>₱<?= number_format($total, 2) ?></strong>
          </div>

          <a href="checkout.php" class="checkout-btn">
            Proceed to Checkout
          </a>
        </div>
      </div>
    <?php else: ?>
      <div class="empty-cart">
        <div class="empty-cart-icon">🛒</div>
        <h3>Your cart is empty</h3>
        <p>Looks like you haven't added anything to your cart yet.</p>
        <a href="menu.php" class="back-link">Browse Menu</a>
      </div>
    <?php endif; ?>
  </div>

  <script>
    function enableEdit(itemId) {
      document.getElementById('qty-display-' + itemId).style.display = 'none';
      document.getElementById('qty-edit-' + itemId).classList.add('active');
      document.getElementById('btn-edit-' + itemId).style.display = 'none';
    }

    function cancelEdit(itemId, originalQty) {
      document.getElementById('qty-display-' + itemId).style.display = 'flex';
      document.getElementById('qty-edit-' + itemId).classList.remove('active');
      document.getElementById('qty-input-' + itemId).value = originalQty;
      document.getElementById('btn-edit-' + itemId).style.display = 'inline-flex';
    }

    function increaseQuantity(itemId) {
      const input = document.getElementById('qty-input-' + itemId);
      input.value = parseInt(input.value) + 1;
    }

    function decreaseQuantity(itemId) {
      const input = document.getElementById('qty-input-' + itemId);
      if (parseInt(input.value) > 1) {
        input.value = parseInt(input.value) - 1;
      }
    }
  </script>
</body>
</html>
