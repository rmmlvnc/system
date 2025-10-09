<?php
session_start();
$cart = $_SESSION['cart'] ?? [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Your Cart</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      padding: 40px;
      background: #f4f6f8;
    }
    h2 {
      text-align: center;
      margin-bottom: 30px;
      color: #2e7d32;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      background: white;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    th, td {
      padding: 12px;
      border-bottom: 1px solid #ddd;
      text-align: left;
    }
    th {
      background-color: #f0f2f5;
    }
    input[type="number"] {
      width: 60px;
      padding: 6px;
      border-radius: 4px;
      border: 1px solid #ccc;
    }
    .btn {
      padding: 8px 16px;
      background: #4db8ff;
      color: white;
      border: none;
      border-radius: 6px;
      text-decoration: none;
      cursor: pointer;
      font-weight: bold;
    }
    .btn:hover {
      background: #3399ff;
    }
    .back-btn {
      display: inline-block;
      margin-top: 20px;
      text-align: center;
      background: #ccc;
      color: #333;
    }
    .back-btn:hover {
      background: #bbb;
    }
  </style>
</head>
<body>
  <h2>Your Cart 🛒</h2>

  <?php if (count($cart) > 0): ?>
    <form method="POST" action="update_cart.php">
      <table>
        <tr>
          <th>Product</th>
          <th>Price</th>
          <th>Quantity</th>
          <th>Total</th>
          <th>Action</th>
        </tr>
        <?php foreach ($cart as $id => $item): ?>
          <tr>
            <td><?= htmlspecialchars($item['product_name']) ?></td>
            <td>₱<?= number_format($item['price'], 2) ?></td>
            <td>
              <input type="number" name="quantities[<?= $id ?>]" value="<?= $item['quantity'] ?>" min="1">
            </td>
            <td>₱<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
            <td>
              <a href="remove_from_cart.php?id=<?= $id ?>" class="btn">❌ Remove</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </table>
      <button type="submit" class="btn" style="margin-top: 20px;">Update Cart</button>
    </form>
    <a href="menu.php" class="btn back-btn">← Back to Menu</a>
  <?php else: ?>
    <p style="text-align:center; font-style:italic;">Your cart is empty.</p>
    <div style="text-align:center;">
      <a href="menu.php" class="btn back-btn">← Back to Menu</a>
    </div>
  <?php endif; ?>
</body>
</html>
