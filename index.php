<?php
session_start();
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
        <?php if (isset($_SESSION['username'])): ?>
          <span class="welcome-text">👋 Welcome, <?= htmlspecialchars($_SESSION['username']) ?></span>
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
      <li><a href="customer_page.php" class="active">HOME</a></li>
      <li><a href="customer_menu.php">MENU</a></li>
      <li><a href="#">FEEDBACK</a></li>
      <li><a href="aboutus.php">ABOUT US</a></li>
    </ul>
  </nav>

  <section class="main">
    <aside class="menu-sidebar">
      <h2 class="menu-title">MENU</h2>
      <ul class="menu-list">
        <li><a href="#">Special Burgers</a></li>
        <li><a href="#">Chicken Burgers</a></li>
        <li><a href="#">Menu Deals</a></li>
        <li><a href="#">Sandwiches</a></li>
        <li><a href="#">Sides</a></li>
        <li><a href="#">Drinks</a></li>
        <li><a href="#">Special Offers</a></li>
      </ul>
    </aside>

    <div class="product-grid">
      <div class="product-card">
        <img src="pictures/burger1.png" alt="Double Angus Burger" />
        <h3>Double Angus & Bacon Cheeseburger</h3>
        <p>$50.00</p>
        <span>Taste the excellence of our tasty burgers...</span>
      </div>
      <div class="product-card">
        <img src="pictures/burger2.png" alt="Spicy Angus Burger" />
        <h3>Spicy Angus Burger</h3>
        <p>$45.00</p>
        <span>Taste the excellence of our tasty burgers...</span>
      </div>
    </div>

    <aside class="order-summary">
      <h3>MY ORDER</h3>
      <ul>
        <li>Double Angus & Bacon Cheeseburger - $50.00</li>
        <li>Chocolate Mousse - $15.00</li>
        <li>Delivery - Free</li>
      </ul>
      <p><strong>Total: $65.00</strong></p>
      <button class="confirm-btn">Confirm Order</button>
    </aside>
  </section>

  <section class="welcome">
    <h2>Welcome to Kyla's Bistro</h2>
    <p>
      Looking for a cozy, stylish spot for your next special event?<br>
      Kyla's Bistro is now open for event bookings from intimate birthdays, romantic weddings, private meetings, and more.
    </p>
  </section>

  <section class="banner">
    <img src="pictures/bg.jpg" alt="bg Kyla's Bistro" />
  </section>
</body>
</html>
