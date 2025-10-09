<?php
session_start();
include("database.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $first = $_POST['first_name'];
    $middle = $_POST['middle_name'];
    $last = $_POST['last_name'];
    $email = $_POST['email'];
    $contact = $_POST['contact_number'];
    $address = $_POST['address'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $plain_password = $password;

        // Check for duplicate email or username
        $check = $pdo->prepare("SELECT * FROM staff WHERE email = :email OR username = :username");
        $check->execute(['email' => $email, 'username' => $username]);
        $result = $check->fetch();

        if ($result) {
            $error = "Email or username already registered.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO staff (username, first_name, middle_name, last_name, email, contact_number, address, password) 
                                   VALUES (:username, :first, :middle, :last, :email, :contact, :address, :password)");
            $stmt->execute([
                'username' => $username,
                'first' => $first,
                'middle' => $middle,
                'last' => $last,
                'email' => $email,
                'contact' => $contact,
                'address' => $address,
                'password' => $plain_password
            ]);
            header("Location: staff_login.php");
            exit();
        }
    }
}
?>



<!DOCTYPE html>
<html>
<head>
    <title>Staff Registration</title>
    <link rel="stylesheet" href="staff_style.css">
</head>
<body class="form-body">
    <div class="form-container">
        <h2>Create Staff Account</h2>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="POST">
            <div class="form-grid">
                <div class="form-column">
                    <input type="text" name="first_name" placeholder="First Name" required>
                    <input type="text" name="last_name" placeholder="Last Name" required>
                    <input type="text" name="username" placeholder="Username" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                </div>
                <div class="form-column">
                    <input type="text" name="middle_name" placeholder="Middle Name">
                    <input type="text" name="contact_number" placeholder="Contact Number" required>
                    <input type="text" name="address" placeholder="Address" required>
                    <input type="email" name="email" placeholder="Email" required>
                </div>
            </div>
            <button type="submit">Register</button>
        </form>
        <p>Already registered? <a href="staff_login.php">Login here</a></p>
    </div>
</body>
</html>
