<?php
session_start();
include ("database.php");
$category_result = $conn->query("SELECT * FROM category");

$category_id = $_GET['category'] ?? null;

if ($category_id) {
  $product_stmt = $conn->prepare("SELECT * FROM product WHERE category_id = ?");
  $product_stmt->bind_param("i", $category_id);
  $product_stmt->execute();
  $product_result = $product_stmt->get_result();
  $product_stmt->close();
} else {
  $product_result = $conn->query("SELECT * FROM product");
}

$selected_category_name = null;

if ($category_id) {
  $cat_stmt = $conn->prepare("SELECT category_name FROM category WHERE category_id = ?");
  $cat_stmt->bind_param("i", $category_id);
  $cat_stmt->execute();
  $cat_result = $cat_stmt->get_result();
  $cat_row = $cat_result->fetch_assoc();
  $selected_category_name = $cat_row['category_name'] ?? null;
  $cat_stmt->close();
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Kyla's Bistro | Customer Page</title>
  <link rel="stylesheet" href="style.css" />
</head>
<body class="index">
  <header>
    <div class="nav-bar">
      <img src="pictures/logo.jpg" alt="Kyla Logo" class="logo" />
      <div class="nav-actions">
        <?php
          $cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
        ?>
        <?php if (isset($_SESSION['username'])): ?>
          <span class="welcome-text">👋 Welcome, <?= htmlspecialchars($_SESSION['username']) ?></span>
          <a href="cart.php" class="cart-icon" title="View Cart">🛒<?= $cart_count > 0 ? "($cart_count)" : "" ?></a>
          <a href="customer_logout.php" class="btn logout-btn">LOG OUT</a>
        <?php else: ?>
          <a href="login.php" class="btn login-btn">LOGIN</a>
          <a href="registration.php" class="btn signup-btn">SIGN UP</a>
        <?php endif; ?>
      </div>
    </div>
  </header>

  <nav>
    <ul class="links">
      <li><a href="index.php">HOME</a></li>
      <li><a href="menu.php" class="active">MENU</a></li>
      <li><a href="#">FEEDBACK</a></li>
      <li><a href="aboutus.php">ABOUT US</a></li>
    </ul>
  </nav>

  <section class="main">
    <aside class="menu-sidebar">
      <h2 class="menu-title">MENU</h2>
      <ul class="menu-list">
        <?php while ($cat = $category_result->fetch_assoc()): ?>
          <li><a href="menu.php?category=<?= $cat['category_id'] ?>">
            <?= htmlspecialchars($cat['category_name']) ?>
          </a></li>
        <?php endwhile; ?>
      </ul>
    </aside>

    <?php if ($selected_category_name): ?>
      <h2 style="text-align:center; margin-bottom:20px;">
        <?= htmlspecialchars($selected_category_name) ?>
      </h2>
    <?php endif; ?>


    <div class="product-grid">
      <?php if ($product_result->num_rows > 0): ?>
        <?php while ($prod = $product_result->fetch_assoc()): ?>
          <div class="product-card">
            <img src="uploads/<?= htmlspecialchars($prod['image']) ?>" alt="<?= htmlspecialchars($prod['product_name']) ?>" />
            <h3><?= htmlspecialchars($prod['product_name']) ?></h3>
            <p>₱<?= htmlspecialchars($prod['price']) ?></p>
            <span><?= htmlspecialchars($prod['description']) ?></span>
            <form method="POST" action="add_to_cart.php" style="margin-top:10px;">
              <input type="hidden" name="product_id" value="<?= $prod['product_id'] ?>">
              <input type="hidden" name="product_name" value="<?= htmlspecialchars($prod['product_name']) ?>">
              <input type="hidden" name="price" value="<?= $prod['price'] ?>">
              <input type="hidden" name="category" value="<?= $category_id ?>">
              <button type="submit" class="btn">🛒 Add to Cart</button>
            </form>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p style="text-align:center; font-style:italic;">No products found in this category.</p>
      <?php endif; ?>
    </div>

  </section>

  <section class="banner">
    <img src="pictures/bg.jpg" alt="bg Kyla's Bistro" />
  </section>
</body>
</html>
