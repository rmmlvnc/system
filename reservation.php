<?php
session_start();
include("database.php");

if (!isset($_SESSION['username'])) {
  header("Location: login.php");
  exit();
}

$customer = $_SESSION['username'];
$message = "";
$messageType = "";

// Get customer_id from username
$cust_stmt = $conn->prepare("SELECT customer_id, first_name, middle_name, last_name, email, phone_number FROM customer WHERE username = ?");
$cust_stmt->bind_param("s", $customer);
$cust_stmt->execute();
$cust_result = $cust_stmt->get_result();
$cust_row = $cust_result->fetch_assoc();
$customer_id = $cust_row['customer_id'];
$fullname = trim($cust_row['first_name'] . ' ' . $cust_row['middle_name'] . ' ' . $cust_row['last_name']);
$email = $cust_row['email'];
$phone = $cust_row['phone_number'];

// Cancel reservation
if (isset($_POST['cancel_reservation_id'])) {
  $reservation_id = $_POST['cancel_reservation_id'];
  $cancel_stmt = $conn->prepare("DELETE FROM reservation WHERE reservation_id = ? AND customer_id = ?");
  $cancel_stmt->bind_param("ii", $reservation_id, $customer_id);
  $cancel_stmt->execute();
  $message = "Reservation cancelled successfully!";
  $messageType = "success";
}

// Make reservation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['table_id'])) {
  $table_id = $_POST['table_id'];
  $date = $_POST['reservation_date'];
  $time = $_POST['reservation_time'];
  $event_type = $_POST['event_type'];
  $total_hours = $_POST['total_hours'] ?? 2;
  $status = 'Pending';
  
  // Get table info
  $table_stmt = $conn->prepare("SELECT capacity, price_per_hour FROM tables WHERE table_id = ?");
  $table_stmt->bind_param("i", $table_id);
  $table_stmt->execute();
  $table_result = $table_stmt->get_result();
  $table_row = $table_result->fetch_assoc();
  $guest_count = $table_row['capacity'];
  $price_per_hour = $table_row['price_per_hour'] ?? 0;
  $total_price = $price_per_hour * $total_hours;
  $table_stmt->close();

  $stmt = $conn->prepare("INSERT INTO reservation (customer_id, table_id, reservation_date, reservation_time, event_type, status, total_hours, total_price) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
  $stmt->bind_param("iissssiid", $customer_id, $table_id, $date, $time, $event_type, $status, $total_hours, $total_price);
  
  if ($stmt->execute()) {
    if ($total_price > 0) {
      $message = "Reservation submitted successfully! Total Cost: ₱" . number_format($total_price, 2) . ". Our staff will confirm your booking shortly.";
    } else {
      $message = "Reservation submitted successfully! Our staff will confirm your booking shortly.";
    }
    $messageType = "success";
  } else {
    $message = "Error: " . $stmt->error;
    $messageType = "error";
  }
  $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Reserve a Table | Kyla's Bistro</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #f9f6f2;
      min-height: 100vh;
      padding: 20px;
      color: #2c1810;
    }

    .container {
      max-width: 1000px;
      margin: 0 auto;
    }

    .back-link {
      display: inline-block;
      padding: 10px 20px;
      background-color: #8b4513;
      color: white;
      text-decoration: none;
      border-radius: 6px;
      margin-bottom: 20px;
      transition: all 0.3s ease;
      box-shadow: 0 2px 5px rgba(139, 69, 19, 0.2);
      font-weight: 600;
    }

    h1 {
      text-align: center;
      color: #8b4513;
      margin-bottom: 10px;
      font-size: 2.2rem;
      font-weight: 600;
    }

    .subtitle {
      text-align: center;
      color: #555;
      margin-bottom: 30px;
      font-size: 1rem;
    }

    .message {
      padding: 12px;
      border-radius: 8px;
      margin-bottom: 20px;
      text-align: center;
      font-weight: 600;
    }

    .message.success {
      background-color: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }

    .message.error {
      background-color: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }

    .card {
      background: white;
      border-radius: 12px;
      padding: 25px;
      margin-bottom: 25px;
      box-shadow: 0 4px 12px rgba(44, 24, 16, 0.1);
      border: 1px solid #f5e6d3;
    }

    .card h2 {
      color: #8b4513;
      margin-bottom: 20px;
      padding-bottom: 12px;
      border-bottom: 3px solid #d4a574;
      font-size: 1.5rem;
      font-weight: 600;
    }

    .form-group {
      margin-bottom: 15px;
    }

    .form-group label {
      display: block;
      font-weight: 600;
      color: #2c1810;
      margin-bottom: 8px;
      font-size: 0.95rem;
    }

    .form-group input[type="date"],
    .form-group input[type="time"],
    .form-group input[type="number"],
    .form-group select {
      width: 100%;
      padding: 10px 12px;
      border: 2px solid #d4a574;
      border-radius: 8px;
      font-size: 14px;
      transition: all 0.3s ease;
      background-color: #f9f9f9;
    }

    .form-group input:focus {
      outline: none;
      border-color: #8b4513;
      box-shadow: 0 0 0 3px rgba(139, 69, 19, 0.1);
      background-color: white;
    }

    .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 15px;
    }

    .event-types {
      display: flex;
      gap: 10px;
      margin-bottom: 15px;
    }

    .event-option {
      flex: 1;
    }

    .event-option input[type="radio"] {
      display: none;
    }

    .event-label {
      display: block;
      padding: 15px 12px;
      background: #f9f6f2;
      border: 2px solid #d4a574;
      border-radius: 8px;
      text-align: center;
      cursor: pointer;
      transition: all 0.3s ease;
      font-weight: 500;
      color: #2c1810;
    }

    .event-option input[type="radio"]:checked + .event-label {
      background: #8b4513;
      border-color: #8b4513;
      color: white;
      font-weight: 600;
      box-shadow: 0 4px 10px rgba(139, 69, 19, 0.3);
    }

    .event-label:hover {
      border-color: #000000ff;
    }

    .table-list {
      margin: 15px 0;
    }

    .table-category h4 {
      margin: 15px 0 10px 0;
      color: #8b4513;
      font-weight: 600;
    }

    .table-item {
      display: flex;
      align-items: center;
      padding: 12px;
      background: white;
      border: 2px solid #f5e6d3;
      border-radius: 8px;
      margin-bottom: 10px;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .table-item:hover {
      background: #f9f6f2;
      border-color: #d4a574;
      transform: translateX(5px);
    }

    .table-item input[type="radio"]:checked + .table-info {
      font-weight: 600;
    }

    .table-item input[type="radio"] {
      margin-right: 12px;
      width: 18px;
      height: 18px;
      accent-color: #8b4513;
    }

    .table-info {
      flex: 1;
    }

    .table-name {
      font-weight: bold;
      color: #2c1810;
    }

    .table-desc {
      font-size: 12px;
      color: #555;
    }

    .table-price {
      font-weight: bold;
      color: #8b4513;
      margin-left: 10px;
    }

    .price-display {
      background: #f5e6d3;
      padding: 15px;
      border-radius: 8px;
      border: 2px solid #d4a574;
      text-align: center;
      margin: 15px 0;
      display: none;
      box-shadow: 0 2px 8px rgba(139, 69, 19, 0.15);
    }

    .total-price {
      color: #8b4513;
      font-size: 24px;
      font-weight: 700;
    }

    .submit-btn {
      width: 100%;
      padding: 14px;
      background: #8b4513;
      color: white;
      border: none;
      border-radius: 8px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 4px 12px rgba(139, 69, 19, 0.3);
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .reservations-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 15px;
    }

    .reservations-table th {
      background: #2c1810;
      color: white;
      font-weight: 600;
    }

    .reservations-table th,
    .reservations-table td {
      padding: 12px;
      text-align: left;
      border-bottom: 1px solid #f5e6d3;
    }

    .reservations-table tbody tr {
      transition: background 0.2s ease;
    }

    .reservations-table tbody tr:hover {
      background: #f9f6f2;
    }

    .status-badge {
      display: inline-block;
      padding: 4px 10px;
      border-radius: 3px;
      font-size: 12px;
      font-weight: bold;
    }

    .status-pending {
      background-color: #fff3cd;
      color: #856404;
    }

    .status-confirmed {
      background-color: #d4edda;
      color: #155724;
    }

    .cancel-btn {
      padding: 6px 12px;
      background-color: #dc3545;
      color: white;
      border: none;
      border-radius: 3px;
      cursor: pointer;
      transition: background 0.3s ease;
    }

    .cancel-btn:hover {
      background-color: #c82333;
    }

    .no-reservations {
      text-align: center;
      padding: 30px;
      color: #999;
    }

    #durationField {
      display: none;
    }

    @media (max-width: 768px) {
      .event-types,
      .form-row {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <a href="index.php" class="back-link">← Back to Home</a>

    <h1>Reserve Your Table</h1>
    <p class="subtitle">Choose your perfect dining experience at Kyla's Bistro</p>

    <?php if ($message): ?>
      <div class="message <?= $messageType ?>">
        <?= $message ?>
      </div>
    <?php endif; ?>

    <div class="card">
      <h2>Make a Reservation</h2>
      <form method="POST" id="reservationForm">
        
        <!-- Event Type -->
        <div class="form-group">
          <label>Select Dining Type</label>
          <div class="event-types">
            <div class="event-option">
              <input type="radio" id="regular" name="event_type" value="Regular Dining" checked onchange="handleEventChange()">
              <label for="regular" class="event-label">🍽️ Regular Dining</label>
            </div>
            <div class="event-option">
              <input type="radio" id="birthday" name="event_type" value="Birthday Party" onchange="handleEventChange()">
              <label for="birthday" class="event-label">🎂 Birthday Party</label>
            </div>
            <div class="event-option">
              <input type="radio" id="meeting" name="event_type" value="Meeting" onchange="handleEventChange()">
              <label for="meeting" class="event-label">💼 Meeting</label>
            </div>
          </div>
        </div>

        <!-- Date & Time -->
        <div class="form-row">
          <div class="form-group">
            <label>Date</label>
            <input type="date" name="reservation_date" required min="<?= date('Y-m-d') ?>" />
          </div>
          <div class="form-group">
            <label>Time</label>
            <input type="time" name="reservation_time" required />
          </div>
        </div>

        <!-- Duration Field (only for Birthday & Meeting) -->
        <div class="form-group" id="durationField">
          <label>Duration (Hours)</label>
          <input type="number" name="total_hours" id="total_hours" min="1" max="12" value="2" onchange="updatePrice()" />
          <small style="color: #666;">Specify how many hours you need the room</small>
        </div>

        <!-- Select Table -->
        <div class="form-group">
          <label>Select Table/Room</label>
          <div class="table-list" id="tablesList">
            <?php
            $tables_query = "SELECT table_id, table_number, capacity, table_type, description, price_per_hour 
                             FROM tables 
                             WHERE status = 'Available' 
                             ORDER BY table_type, table_number";
            
            $tables_result = $conn->query($tables_query);
            $grouped_tables = [];
            while ($table = $tables_result->fetch_assoc()) {
              $type = $table['table_type'];
              $grouped_tables[$type][] = $table;
            }
            
            foreach ($grouped_tables as $type => $tables):
            ?>
              <div class="table-category" data-table-type="<?= htmlspecialchars($type) ?>">
                <h4><?= htmlspecialchars($type) ?></h4>
                <?php foreach ($tables as $table): ?>
                  <label class="table-item">
                    <input type="radio" name="table_id" value="<?= $table['table_id'] ?>" 
                           required data-price="<?= $table['price_per_hour'] ?>" onchange="updatePrice()">
                    <div class="table-info">
                      <div class="table-name">Table <?= htmlspecialchars($table['table_number']) ?></div>
                      <div class="table-desc">
                        <?php if ($table['description']): ?>
                          <?= htmlspecialchars($table['description']) ?> • 
                        <?php endif; ?>
                        Capacity: <?= $table['capacity'] ?> guests
                      </div>
                    </div>
                    <div class="table-price">
                      <?= $table['price_per_hour'] > 0 ? '₱' . number_format($table['price_per_hour'], 2) . '/hr' : 'Free' ?>
                    </div>
                  </label>
                <?php endforeach; ?>
              </div>
            <?php endforeach; ?>
          </div>

          <div id="priceDisplay" class="price-display">
            <strong>Total Cost:</strong> 
            <span id="totalPrice" class="total-price">₱0.00</span>
          </div>
        </div>

        <button type="submit" class="submit-btn">Submit Reservation</button>
      </form>
    </div>

    <!-- My Reservations -->
    <div class="card">
      <h2>My Reservations</h2>
      <?php
      $reservations_query = $conn->prepare("SELECT r.reservation_id, r.reservation_date, r.reservation_time, r.event_type, r.status, r.total_hours, r.total_price, t.table_number, t.table_type 
                                             FROM reservation r 
                                             JOIN tables t ON r.table_id = t.table_id 
                                             WHERE r.customer_id = ? 
                                             ORDER BY r.reservation_date DESC, r.reservation_time DESC");
      $reservations_query->bind_param("i", $customer_id);
      $reservations_query->execute();
      $reservations_result = $reservations_query->get_result();

      if ($reservations_result->num_rows > 0):
      ?>
        <table class="reservations-table">
          <thead>
            <tr>
              <th>Date</th>
              <th>Time</th>
              <th>Table/Room</th>
              <th>Event Type</th>
              <th>Duration</th>
              <th>Total Price</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($res = $reservations_result->fetch_assoc()): ?>
              <tr>
                <td><?= date('M d, Y', strtotime($res['reservation_date'])) ?></td>
                <td><?= date('h:i A', strtotime($res['reservation_time'])) ?></td>
                <td><?= htmlspecialchars($res['table_number']) ?></td>
                <td><?= htmlspecialchars($res['event_type']) ?></td>
                <td><?= $res['total_hours'] ?> hr<?= $res['total_hours'] > 1 ? 's' : '' ?></td>
                <td>
                  <?= $res['total_price'] > 0 ? '₱' . number_format($res['total_price'], 2) : 'Free' ?>
                </td>
                <td>
                  <span class="status-badge status-<?= strtolower($res['status']) ?>">
                    <?= htmlspecialchars($res['status']) ?>
                  </span>
                </td>
                <td>
                  <?php if ($res['status'] === 'Pending'): ?>
                    <form method="POST" style="display: inline;" onsubmit="return confirm('Cancel this reservation?');">
                      <input type="hidden" name="cancel_reservation_id" value="<?= $res['reservation_id'] ?>">
                      <button type="submit" class="cancel-btn">Cancel</button>
                    </form>
                  <?php else: ?>
                    -
                  <?php endif; ?>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      <?php else: ?>
        <div class="no-reservations">
          <p>You don't have any reservations yet.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <script>
    function handleEventChange() {
      const selectedEvent = document.querySelector('input[name="event_type"]:checked').value;
      const durationField = document.getElementById('durationField');
      const categories = document.querySelectorAll('.table-category');
      
      // Show/hide duration field based on event type
      if (selectedEvent === 'Birthday Party' || selectedEvent === 'Meeting') {
        durationField.style.display = 'block';
      } else {
        durationField.style.display = 'none';
      }
      
      // Filter tables based on event type
      categories.forEach(category => {
        const tableType = category.getAttribute('data-table-type');
        
        if (selectedEvent === 'Regular Dining') {
          category.style.display = tableType.includes('Regular') ? 'block' : 'none';
        } else if (selectedEvent === 'Birthday Party') {
          category.style.display = tableType === 'Birthday Party Room' ? 'block' : 'none';
        } else if (selectedEvent === 'Meeting') {
          category.style.display = tableType === 'Meeting Room' ? 'block' : 'none';
        }
      });

      // Reset table selection and price
      document.querySelectorAll('input[name="table_id"]').forEach(radio => radio.checked = false);
      updatePrice();
    }

    function updatePrice() {
      const selectedTable = document.querySelector('input[name="table_id"]:checked');
      const selectedEvent = document.querySelector('input[name="event_type"]:checked').value;
      const totalHours = parseInt(document.getElementById('total_hours').value) || 2;
      const priceDisplay = document.getElementById('priceDisplay');
      const totalPriceElement = document.getElementById('totalPrice');
      
      if (selectedTable) {
        const pricePerHour = parseFloat(selectedTable.getAttribute('data-price')) || 0;
        
        // Calculate total based on event type
        let totalPrice = 0;
        if (selectedEvent === 'Birthday Party' || selectedEvent === 'Meeting') {
          totalPrice = pricePerHour * totalHours;
        } else {
          totalPrice = 0; // Regular dining is free
        }
        
        if (totalPrice > 0) {
          totalPriceElement.textContent = '₱' + totalPrice.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
          priceDisplay.style.display = 'block';
        } else {
          priceDisplay.style.display = 'none';
        }
      } else {
        priceDisplay.style.display = 'none';
      }
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
      handleEventChange();
    });
  </script>
</body>
</html>
<?php $conn->close(); ?>
