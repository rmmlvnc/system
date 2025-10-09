<?php
session_start();
include 'database.php';

if (!isset($_SESSION['admin'])) {
  header("Location: admin_login.php");
  exit();
}

$username = $_SESSION['admin'];

$stmt = $pdo->prepare("SELECT first_name FROM admin WHERE username = ?");
$stmt->execute([$username]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);
$first_name = $admin ? $admin['first_name'] : 'Admin';

$stmt = $pdo->query("SELECT * FROM payment ORDER BY payment_date DESC");
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="dashboard.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
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
      <h3>🧾 Customer Operations</h3>
      <div class="card-grid">
        <div class="payment-table">
          <?php if (count($payments) === 0): ?>
            <p>No payments have been made yet.</p>
          <?php else: ?>
            <table>
              <thead>
                <tr>
                  <th>Payment ID</th>
                  <th>Order ID</th>
                  <th>Date</th>
                  <th>Time</th>
                  <th>Method</th>
                  <th>Amount</th>
                  <th>Receipt</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($payments as $row): ?>
                  <tr>
                    <td><?= $row['payment_id'] ?></td>
                    <td><?= $row['order_id'] ?></td>
                    <td><?= $row['payment_date'] ?></td>
                    <td><?= $row['payment_time'] ?></td>
                    <td><?= $row['payment_method'] ?></td>
                    <td>₱<?= number_format($row['amount'], 2) ?></td>
                    <td>
                      <?php if (!empty($row['receipt'])): ?>
                        <a href="uploads/<?= $row['receipt'] ?>" target="_blank">View</a>
                      <?php else: ?>
                        No receipt
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php endif; ?>
        </div>

      </div>
    </div>

  </main>
</body>
</html>
