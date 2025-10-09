<?php
session_start();
include 'database.php';

if (!isset($_SESSION['staff_id'])) {
  header("Location: staff_login.php");
  exit();
}

$staff_id = $_SESSION['staff_id'];

$staff_stmt = $conn->prepare("SELECT * FROM staff WHERE staff_id = ?");
$staff_stmt->bind_param("i", $staff_id);
$staff_stmt->execute();
$staff_result = $staff_stmt->get_result();
$staff = $staff_result->fetch_assoc();
$staff_stmt->close();


// Fetch reservations
$res_stmt = $conn->prepare("
  SELECT r.*, t.table_number, t.capacity
  FROM reservation r
  LEFT JOIN tables t ON r.table_id = t.table_id
  ORDER BY r.date, r.time
");

$res_stmt->execute();
$res_result = $res_stmt->get_result();

// Fetch products
$product_result = $conn->query("SELECT * FROM product");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Staff Dashboard</title>
  <style>
    body { font-family: 'Segoe UI', sans-serif; background: #f4f6f8; margin: 0; padding: 40px; }
    .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
    .btn { padding: 6px 12px; background: #4db8ff; color: white; border: none; border-radius: 6px; text-decoration: none; }
    .btn:hover { background: #3399ff; }
    .section { background: white; padding: 20px; border-radius: 10px; margin-bottom: 30px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th, td { padding: 10px; border-bottom: 1px solid #ddd; text-align: left; }
    th { background-color: #f0f2f5; }
  </style>
</head>
<body>
  <div class="header">
    <h1>Welcome, <?php echo htmlspecialchars($staff['first_name']); ?> 👋</h1>
    <a href="logout_staff.php" class="btn">Logout</a>
  </div>

  <div class="section">
    <h2>Your Info</h2>
    <p><strong>Username:</strong> <?php echo htmlspecialchars($staff['username']); ?></p>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($staff['email']); ?></p>
    <p><strong>Contact Number:</strong> <?php echo htmlspecialchars($staff['contact_number']); ?></p>
  </div>

  <div class="section">
    <h2>Customer Reservations</h2>
    <table>
      <tr>
        <th>Customer</th><th>Contact</th><th>Date</th><th>Time</th><th>Table</th><th>Capacity</th><th>Status</th>
      </tr>
      <?php while ($row = $res_result->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($row['customer_name']) ?></td>
          <td><?= htmlspecialchars($row['contact']) ?></td>
          <td><?= htmlspecialchars($row['reservation_date']) ?></td>
          <td><?= htmlspecialchars($row['reservation_time']) ?></td>
          <td>Table <?= htmlspecialchars($row['table_number']) ?></td>
          <td><?= htmlspecialchars($row['capacity']) ?> seats</td>
          <td><?= htmlspecialchars($row['status']) ?></td>
        </tr>
      <?php endwhile; ?>
    </table>
  </div>

  <div class="section">
    <h2>Product List</h2>
    <a href="staff_add_product.php" class="btn">➕ Add Product</a>
    <table>
      <tr>
        <th>Name</th><th>Description</th><th>Image</th><th>Price</th><th>Category</th><th>Action</th>
      </tr>
      <?php while ($prod = $product_result->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($prod['product_name']) ?></td>
          <td><?= htmlspecialchars($prod['description']) ?></td>
          <td>
            <img src="uploads/<?= htmlspecialchars($prod['image']) ?>" alt="<?= htmlspecialchars($prod['product_name']) ?>" style="width: 80px; height: auto; border-radius: 6px;" />
          </td>
          <td>₱<?= htmlspecialchars($prod['price']) ?></td>
          <td><?= htmlspecialchars($prod['category_id']) ?></td>
          <td>
            <a href="staff_edit_product.php?id=<?= $prod['product_id'] ?>" class="btn">✏️ Edit</a>
          </td>
        </tr>
      <?php endwhile; ?>
    </table>
  </div>
</body>
</html>
