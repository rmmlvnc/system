<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Kyla's Bistro | Home</title>
  <link rel="stylesheet" href="style.css" />
  <style>
    /* Additional styles that extend style.css */
    
    /* Hero Section */
    .hero {
      text-align: center;
      padding: 60px 40px;
      background: linear-gradient(135deg, #fff7f0 0%, #ffe0e0 100%);
    }

    .hero h1 {
      font-size: 3rem;
      color: #c00;
      margin-bottom: 15px;
      text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
    }

    .hero p {
      font-size: 1.2rem;
      color: #333;
      max-width: 700px;
      margin: 0 auto;
      line-height: 1.6;
    }

    /* Reservation Highlight Section */
    .reservation-highlight {
      max-width: 1200px;
      margin: 50px auto;
      padding: 0 40px;
    }

    .reservation-card {
      background: white;
      border-radius: 16px;
      box-shadow: 0 8px 24px rgba(0,0,0,0.12);
      overflow: hidden;
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 0;
    }

    .reservation-content {
      padding: 50px 40px;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .reservation-content h2 {
      font-size: 2.5rem;
      color: #c00;
      margin-bottom: 20px;
      border-bottom: 3px solid #c00;
      padding-bottom: 15px;
    }

    .reservation-content p {
      font-size: 1.05rem;
      color: #555;
      line-height: 1.7;
      margin-bottom: 25px;
    }

    .reservation-features {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 15px;
      margin-bottom: 30px;
    }

    .feature-item {
      display: flex;
      align-items: center;
      gap: 10px;
      color: #333;
      font-size: 0.95rem;
      font-weight: 600;
    }

    .feature-item::before {
      content: '✓';
      display: flex;
      align-items: center;
      justify-content: center;
      width: 26px;
      height: 26px;
      background-color: #c00;
      color: white;
      border-radius: 50%;
      font-weight: bold;
      font-size: 0.85rem;
      flex-shrink: 0;
    }

    .reserve-btn-large {
      display: inline-block;
      padding: 16px 40px;
      background-color: #c00;
      color: white;
      text-decoration: none;
      border-radius: 25px;
      font-weight: bold;
      font-size: 1.1rem;
      box-shadow: 0 6px 16px rgba(204, 0, 0, 0.3);
      align-self: flex-start;
    }

    .reservation-image {
      position: relative;
      height: 100%;
      min-height: 400px;
      overflow: hidden;
    }

    .reservation-image img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    /* Featured Menu Section */
    .featured-menu {
      max-width: 1400px;
      margin: 60px auto;
      padding: 0 40px;
    }

    .section-header {
      text-align: center;
      margin-bottom: 50px;
    }

    .section-header h2 {
      font-size: 2.5rem;
      color: #253745;
      margin-bottom: 10px;
      position: relative;
      display: inline-block;
    }

    .section-header h2::after {
      content: '';
      position: absolute;
      bottom: -10px;
      left: 50%;
      transform: translateX(-50%);
      width: 80px;
      height: 4px;
      background-color: #c00;
      border-radius: 2px;
    }

    .section-header p {
      font-size: 1.1rem;
      color: #666;
      margin-top: 20px;
    }

    .menu-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 30px;
    }

    .menu-card {
      background: white;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 6px 16px rgba(0,0,0,0.1);
      cursor: pointer;
    }

    .menu-card-image {
      width: 100%;
      height: 220px;
      object-fit: cover;
    }
    .menu-card-content {
      padding: 20px;
    }

    .menu-card-content h3 {
      font-size: 1.3rem;
      color: #253745;
      margin-bottom: 10px;
      font-weight: 700;
    }

    .menu-card-content p {
      color: #666;
      font-size: 0.9rem;
      line-height: 1.5;
      margin-bottom: 15px;
      min-height: 40px;
    }

    .menu-card-footer {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding-top: 15px;
      border-top: 1px solid #f0f0f0;
    }

    .price {
      font-size: 1.5rem;
      font-weight: bold;
      color: #c00;
    }

    .order-btn-small {
      padding: 10px 20px;
      background-color: #c00;
      color: white;
      border: none;
      border-radius: 20px;
      font-weight: 600;
      font-size: 0.9rem;
      cursor: pointer;
      box-shadow: 0 4px 10px rgba(204, 0, 0, 0.2);
    }

    /* CTA Section */
    .cta-section {
      max-width: 1200px;
      margin: 60px auto 80px;
      padding: 0 40px;
    }

    .cta-box {
      background: linear-gradient(135deg, #253745 0%, #2f4f4f 100%);
      border-radius: 16px;
      padding: 60px 40px;
      text-align: center;
      box-shadow: 0 10px 30px rgba(0,0,0,0.15);
      position: relative;
      overflow: hidden;
    }

    .cta-box::before {
      content: '';
      position: absolute;
      top: -50%;
      right: -10%;
      width: 300px;
      height: 300px;
      background: rgba(204, 0, 0, 0.15);
      border-radius: 50%;
    }

    .cta-box::after {
      content: '';
      position: absolute;
      bottom: -30%;
      left: -5%;
      width: 250px;
      height: 250px;
      background: rgba(204, 0, 0, 0.1);
      border-radius: 50%;
    }

    .cta-box h2 {
      font-size: 2.2rem;
      color: white;
      margin-bottom: 15px;
      position: relative;
      z-index: 1;
    }

    .cta-box p {
      color: rgba(255, 255, 255, 0.9);
      font-size: 1.1rem;
      margin-bottom: 35px;
      position: relative;
      z-index: 1;
    }

    .cta-buttons {
      display: flex;
      justify-content: center;
      gap: 20px;
      flex-wrap: wrap;
      position: relative;
      z-index: 1;
    }

    .cta-btn {
      padding: 14px 35px;
      text-decoration: none;
      border-radius: 25px;
      font-weight: bold;
      font-size: 1rem;
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }

    .cta-btn-primary {
      background-color: #c00;
      color: white;
      box-shadow: 0 6px 16px rgba(204, 0, 0, 0.3);
    }

    .cta-btn-secondary {
      background: white;
      color: #253745;
      border: 2px solid white;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
      .hero h1 {
        font-size: 2rem;
      }

      .hero p {
        font-size: 1rem;
      }

      .reservation-card {
        grid-template-columns: 1fr;
      }

      .reservation-content {
        padding: 30px 25px;
      }

      .reservation-content h2 {
        font-size: 1.8rem;
      }

      .reservation-features {
        grid-template-columns: 1fr;
      }

      .reservation-image {
        min-height: 300px;
      }

      .menu-grid {
        grid-template-columns: 1fr;
      }

      .section-header h2 {
        font-size: 2rem;
      }

      .cta-box {
        padding: 40px 25px;
      }

      .cta-box h2 {
        font-size: 1.7rem;
      }

      .cta-buttons {
        flex-direction: column;
        align-items: stretch;
      }

      .cta-btn {
        justify-content: center;
      }
    }
  </style>
</head>
<body class="index">
  <header>
    <div class="nav-bar">
      <img src="pictures/logo.jpg" alt="Kyla Logo" class="logo" />
      <div class="nav-actions">
        <?php if (isset($_SESSION['username'])): ?>
          <span class="welcome-text">👋 Welcome, <?= htmlspecialchars($_SESSION['username']) ?></span>
          <a href="customer_logout.php" class="logout-btn">LOG OUT</a>
        <?php else: ?>
          <a href="login.php" class="btn login-btn">LOGIN</a>
          <a href="registration.php" class="btn signup-btn">SIGN UP</a>
        <?php endif; ?>
      </div>
    </div>
  </header>

  <nav>
    <ul class="links">
      <li><a href="index.php" class="active">HOME</a></li>
      <li><a href="menu.php">MENU</a></li>
      <li><a href="#">FEEDBACK</a></li>
      <li><a href="aboutus.php">ABOUT US</a></li>
    </ul>
  </nav>

  <section class="hero">
    <h1>Welcome to Kyla's Bistro</h1>
    <p>Experience culinary excellence in every bite. Where great food meets unforgettable moments.</p>
  </section>

  <section class="reservation-highlight">
    <div class="reservation-card">
      <div class="reservation-content">
        <h2>Reserve Your Table</h2>
        <p>Looking for a cozy, stylish spot for your next special event? Kyla's Bistro is now open for event bookings from intimate birthdays, romantic weddings, private meetings, and more.</p>
        
        <div class="reservation-features">
          <div class="feature-item">Intimate Birthday Parties</div>
          <div class="feature-item">Romantic Weddings</div>
          <div class="feature-item">Private Meetings</div>
          <div class="feature-item">Corporate Events</div>
        </div>

        <a href="reservation.php" class="reserve-btn-large">Reserve Now</a>
      </div>
      
      <div class="reservation-image">
        <img src="pictures/bg.jpg" alt="Kyla's Bistro Interior" />
      </div>
    </div>
  </section>

  <section class="featured-menu">
    <div class="section-header">
      <h2>Featured Dishes</h2>
      <p>Discover our chef's signature creations</p>
    </div>

    <div class="menu-grid">
      <div class="menu-card">
        <img src="pictures/pizza/kassy-kass.jpg" alt="Kassy Kass" class="menu-card-image" />
        <div class="menu-card-content">
          <h3>Kassy Kass</h3>
          <p>Heavy ground beef, pineapple, mushroom, black olives</p>
          <div class="menu-card-footer">
            <span class="price">₱378.00</span>
            <button class="order-btn-small" onclick="window.location.href='menu.php'">Order Now</button>
          </div>
        </div>
      </div>

      <div class="menu-card">
        <img src="pictures/Pork/back-ribs.jpg" alt="Baby Back Ribs" class="menu-card-image" />
        <div class="menu-card-content">
          <h3>Baby Back Ribs</h3>
          <p>Pugon roasted baby back ribs in smokey barbeque sauce</p>
          <div class="menu-card-footer">
            <span class="price">₱368.00</span>
            <button class="order-btn-small" onclick="window.location.href='menu.php'">Order Now</button>
          </div>
        </div>
      </div>

      <div class="menu-card">
        <img src="pictures/milkshake.png" alt="Milkshake" class="menu-card-image" />
        <div class="menu-card-content">
          <h3>Classic Milkshake</h3>
          <p>Thick, creamy, and topped with whipped cream</p>
          <div class="menu-card-footer">
            <span class="price">₱120.00</span>
            <button class="order-btn-small" onclick="window.location.href='menu.php'">Order Now</button>
          </div>
        </div>
      </div>

      <div class="menu-card">
        <img src="pictures/steak.png" alt="Steak" class="menu-card-image" />
        <div class="menu-card-content">
          <h3>Grilled Ribeye Steak</h3>
          <p>Juicy, tender, and served with garlic butter</p>
          <div class="menu-card-footer">
            <span class="price">₱450.00</span>
            <button class="order-btn-small" onclick="window.location.href='menu.php'">Order Now</button>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="cta-section">
    <div class="cta-box">
      <h2>Ready to Enjoy Kyla's Bistro?</h2>
      <p>Whether you're dining in or planning a special event, we've got you covered.</p>
      <div class="cta-buttons">
        <a href="menu.php" class="cta-btn cta-btn-primary">
          🛍️ Browse Full Menu
        </a>
        <a href="reservation.php" class="cta-btn cta-btn-secondary">
          📅 Book an Event
        </a>
      </div>
    </div>
  </section>

</body>
</html>
