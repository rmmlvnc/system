<?php
session_start();
include 'database.php';

if (!isset($_SESSION['admin'])) {
  header("Location: admin_login.php");
  exit();
}

$username = $_SESSION['admin'];

// Get admin's first name
$stmt = $conn->prepare("SELECT first_name FROM admin WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();
$first_name = $admin ? $admin['first_name'] : 'Admin';

// Get payment records
$result = $conn->query("SELECT payment_id, order_id, payment_method, payment_status, payment_date FROM payment ORDER BY payment_date DESC");
$payments = [];
if ($result) {
  while ($row = $result->fetch_assoc()) {
    $payments[] = $row;
  }
}

// Get recent orders
$order_result = $conn->query("
  SELECT o.order_id, o.customer_id, o.order_date, o.order_time, o.total_amount,
         c.first_name, c.last_name,
         p.payment_status
  FROM orders o
  JOIN customer c ON o.customer_id = c.customer_id
  LEFT JOIN payment p ON o.order_id = p.order_id
  ORDER BY o.order_date DESC, o.order_time DESC
");

$orders = [];
if ($order_result) {
  while ($row = $order_result->fetch_assoc()) {
    $orders[] = $row;
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: #f4f6f9;
      margin: 0;
      padding: 0;
    }
    header.dashboard-header {
      background: linear-gradient(to right, #2c3e50, #4ca1af);
      color: white;
      padding: 1rem 2rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .logo {
      font-size: 1.5rem;
      font-weight: bold;
    }
    .admin-info {
      display: flex;
      align-items: center;
      gap: 1rem;
    }
    .logout-btn {
      background: #e74c3c;
      color: white;
      padding: 0.4rem 0.8rem;
      border-radius: 6px;
      text-decoration: none;
    }
    main.dashboard-main {
      max-width: 1200px;
      margin: 2rem auto;
      padding: 1rem;
    }
    .dashboard-section {
      margin-bottom: 2rem;
    }
    .card-grid {
      display: flex;
      gap: 1.5rem;
      flex-wrap: wrap;
    }
    .card {
      background: white;
      padding: 1rem;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.05);
      flex: 1;
      min-width: 250px;
      text-align: center;
    }
    .card i {
      font-size: 2rem;
      color: #3498db;
    }
    .card a {
      display: inline-block;
      margin-top: 0.5rem;
      background: #3498db;
      color: white;
      padding: 0.4rem 0.8rem;
      border-radius: 6px;
      text-decoration: none;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      background: white;
      box-shadow: 0 0 10px rgba(0,0,0,0.05);
      border-radius: 8px;
      overflow: hidden;
    }
    th, td {
      padding: 0.75rem;
      border-bottom: 1px solid #eee;
      text-align: left;
    }
    th {
      background: #34495e;
      color: white;
    }
    tr:hover {
      background: #f0f0f0;
    }
    select, button {
      font-size: 0.9rem;
      padding: 0.3rem 0.6rem;
      border: 1px solid #ccc;
      border-radius: 4px;
    }
    button {
      background: #3498db;
      color: white;
      border: none;
      cursor: pointer;
    }
    button:hover {
      background: #2980b9;
    }
  </style>
</head>
<body>
  <header class="dashboard-header">
    <div class="logo">Kyla's Bistro Admin</div>
    <div class="admin-info">
      <span>👋 Welcome, <?= htmlspecialchars($first_name) ?></span>
      <a href="admin_logout.php" class="logout-btn">Logout</a>
    </div>
  </header>

  <main class="dashboard-main">
    <h2>Dashboard Overview</h2>

    <div class="dashboard-section">
      <h3>👥 Staff Management</h3>
      <div class="card-grid">
        <div class="card">
          <i class="fas fa-user-shield"></i>
          <h3>Manage Staff</h3>
          <p>Add, edit, or remove staff accounts</p>
          <a href="manage_staff.php">Go</a>
        </div>
      </div>
    </div>

    <div class="dashboard-section">
      <h3>🧾 Customer Payments</h3>
      <div class="payment-table">
        <?php if (count($payments) === 0): ?>
          <p>No payments have been made yet.</p>
        <?php else: ?>
          <table>
            <thead>
              <tr>
                <th>Payment ID</th>
                <th>Order ID</th>
                <th>Method</th>
                <th>Status</th>
                <th>Date</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($payments as $row): ?>
                <tr>
                  <td><?= $row['payment_id'] ?></td>
                  <td><?= $row['order_id'] ?></td>
                  <td><?= $row['payment_method'] ?></td>
                  <td><?= $row['payment_status'] ?></td>
                  <td><?= $row['payment_date'] ?></td>
                  <td>
                    <form method="POST" action="update_payment_status.php">
                      <input type="hidden" name="payment_id" value="<?= $row['payment_id'] ?>">
                      <select name="payment_status">
                        <option value="Pending" <?= $row['payment_status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="Paid" <?= $row['payment_status'] === 'Paid' ? 'selected' : '' ?>>Paid</option>
                      </select>
                      <button type="submit">Update</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </div>

    <div class="dashboard-section">
      <h3>📦 Recent Orders</h3>
      <div class="order-table">
        <?php if (count($orders) === 0): ?>
          <p>No orders have been placed yet.</p>
        <?php else: ?>
          <table>
            <thead>
              <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Date</th>
                <th>Time</th>
                <th>Total</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($orders as $order): ?>
                <tr>
                  <td><?= $order['order_id'] ?></td>
                  <td><?= htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) ?></td>
                  <td><?= $order['order_date'] ?></td>
                  <td><?= $order['order_time'] ?></td>
                  <td>₱<?= number_format($order['total_amount'], 2) ?></td>
                  <td><?= $order['payment_status'] ?? 'Unpaid' ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </div>
  </main>
</body>
</html>
