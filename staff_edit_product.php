<?php
session_start();
include 'database.php';

if (!isset($_SESSION['staff_id'])) {
  header("Location: staff_login.php");
  exit();
}

$product_id = $_GET['id'] ?? null;
if (!$product_id) {
  header("Location: staff.php");
  exit();
}

// Fetch product details
$stmt = $conn->prepare("SELECT * FROM product WHERE product_id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

// Fetch categories
$category_result = $conn->query("SELECT * FROM category");

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = $_POST['product_name'];
  $desc = $_POST['description'];
  $image = $_POST['image'];
  $price = $_POST['price'];
  $category_id = $_POST['category_id'];

  $stock_quantity = $_POST['stock_quantity'];
  $update_stmt = $conn->prepare("UPDATE product SET product_name = ?, description = ?, image = ?, price = ?, category_id = ?, stock_quantity = ? WHERE product_id = ?");
  $update_stmt->bind_param("sssiiii", $name, $desc, $image, $price, $category_id, $stock_quantity, $product_id);
  $update_stmt->execute();
  $update_stmt->close();

  header("Location: staff.php");
  exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Edit Product</title>
  <style>
    body { font-family: 'Segoe UI', sans-serif; background: #f4f6f8; padding: 40px; }
    .form-box { background: white; padding: 30px; border-radius: 10px; max-width: 500px; margin: auto; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    input, select, button { width: 100%; padding: 10px; margin-bottom: 15px; border-radius: 6px; border: 1px solid #ccc; }
    button { background: #4db8ff; color: white; border: none; cursor: pointer; }
    button:hover { background: #3399ff; }
    .cancel-btn {
    display: inline-block;
    padding: 10px;
    background: #f10000ff;
    color: #ffffffff;
    text-align: center;
    border-radius: 6px;
    text-decoration: none;
    flex-grow: 1;
  }
  .cancel-btn:hover {
    background: #bbb;
  }

  </style>
</head>
<body>
  <div class="form-box">
    <h2>Edit Product</h2>
    <form method="POST">
      <input type="text" name="product_name" value="<?= htmlspecialchars($product['product_name']) ?>" required />
      <input type="text" name="description" value="<?= htmlspecialchars($product['description']) ?>" required />
      <input type="text" name="image" value="<?= htmlspecialchars($product['image']) ?>" />
      <input type="number" name="price" value="<?= htmlspecialchars($product['price']) ?>" required />
      <label for="stock_quantity">Stock Quantity:</label>
      <input type="number" name="stock_quantity" value="<?= htmlspecialchars($product['stock_quantity']) ?>" required />
      <select name="category_id" required>
        <?php while ($cat = $category_result->fetch_assoc()): ?>
          <option value="<?= $cat['category_id'] ?>" <?= $cat['category_id'] == $product['category_id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($cat['category_name']) ?>
          </option>
        <?php endwhile; ?>
      </select>
      <div style="display: flex; gap: 10px;">
        <button type="submit">Update Product</button>
        <a href="staff.php" class="cancel-btn">Cancel</a>
      </div>
    </form>
  </div>
</body>
</html>
