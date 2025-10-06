<?php 
session_start(); 
include 'database.php'; 
if (!isset($_SESSION['admin'])) { 
    header("Location: admin.php"); 
    exit(); 
    } 
    
    $username = $_SESSION['admin']; 
    $stmt = $conn->prepare("SELECT first_name, last_name FROM admin WHERE username = ?"); 
    $stmt->bind_param("s", $username); 
    $stmt->execute(); 
    $result = $stmt->get_result(); 
    $admin = $result->fetch_assoc(); 
    $full_name = $admin['first_name'] . ' ' . $admin['last_name']; 
    $stmt->close(); 
    $conn->close(); 
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
                <div class="logo"> 
                    <img src="pictures/kyla-logo.png" alt="Kyla Logo" class="logo-img" /> 
                    <span class="logo-text">Kyla's Bistro Admin</span> 
                </div> 
                <div class="admin-info"> 
                    <span>👋 Welcome, <?= htmlspecialchars($full_name) ?></span> <a href="logout_admin.php" class="logout-btn">Logout</a> </div> </header> <main class="dashboard-main"> <h2>Dashboard Overview</h2> <div class="main_grid"> <div class="card"> <i class="fas fa-users"></i> <h3>Manage Users</h3> <p>View and edit customer accounts</p> <a href="manage_users.php">Go</a> </div> <div class="card"> <i class="fas fa-box"></i> <h3>Manage Products</h3> <p>Update menu items and pricing</p> <a href="manage_products.php">Go</a> </div> <div class="card"> <i class="fas fa-receipt"></i> <h3>Orders</h3> <p>Track and fulfill customer orders</p> <a href="manage_orders.php">Go</a> </div> <div class="card"> <i class="fas fa-user-tie"></i> <h3>Manage Staff</h3> <p>View and assign staff roles</p> <a href="manage_staff.php">Go</a> </div> <div class="card"> <i class="fas fa-calendar-check"></i> <h3>Reservations</h3> <p>View and manage table bookings</p> <a href="manage_reservations.php">Go</a> </div> </div> </main> </body> </html>