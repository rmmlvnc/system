<?php
session_start();
include 'database.php';

if (!isset($_SESSION['staff_id'])) {
  header("Location: staff_login.php");
  exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $product_name = $_POST['product_name'];
  $description = $_POST['description'];
  $price = $_POST['price'];
  $category_id = $_POST['category_id'];

  // Handle image upload
  $image_name = basename($_FILES['image']['name']);
  $image_tmp = $_FILES['image']['tmp_name'];
  $upload_dir = 'uploads/';
  $image_path = $upload_dir . $image_name;

  // Ensure the uploads folder exists
  if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
  }

  // Move uploaded file
  if (move_uploaded_file($image_tmp, $image_path)) {
    $stmt = $conn->prepare("INSERT INTO product (product_name, description, image, price, category_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssdi", $product_name, $description, $image_name, $price, $category_id);
    $stmt->execute();

    echo "<script>alert('Product added successfully!'); window.location.href='staff.php';</script>";
    exit();
  } else {
    echo "<script>alert('Image upload failed. Please check folder permissions.');</script>";
  }
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Add Product</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: linear-gradient(to right, #e0f7fa, #f1f8e9);
      margin: 0;
      padding: 40px;
    }
    .form-container {
      max-width: 720px;
      margin: auto;
      background: white;
      padding: 30px;
      border-radius: 14px;
      box-shadow: 0 6px 20px rgba(0,0,0,0.1);
      transition: transform 0.2s ease;
    }

    h2 {
      margin-bottom: 20px;
      color: #2e7d32;
      text-align: center;
    }
    .form-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
    }
    label {
      font-weight: bold;
      margin-bottom: 6px;
      display: block;
      color: #555;
    }
    input, textarea, select {
      width: 100%;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 14px;
      box-sizing: border-box;
    }
    textarea {
      resize: vertical;
      height: 100px;
    }
    .btn {
      margin-top: 20px;
      padding: 12px 24px;
      background: #4db8ff;
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-weight: bold;
      letter-spacing: 0.5px;
    }
    .btn:hover {
      background: #3399ff;
    }
    .back-link {
      display: block;
      margin-top: 20px;
      text-align: center;
      color: #666;
      text-decoration: none;
    }
    .back-link:hover {
      text-decoration: underline;
    }
    .image-preview {
      margin-top: 10px;
      max-width: 100%;
      border-radius: 6px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
  </style>
  <script>
    function previewImage(event) {
      const reader = new FileReader();
      reader.onload = function(){
        const output = document.getElementById('preview');
        output.src = reader.result;
      };
      reader.readAsDataURL(event.target.files[0]);
    }
  </script>
</head>
<body>
  <div class="form-container">
    <h2>➕ Add New Product</h2>
    <form method="POST" enctype="multipart/form-data">
      <div class="form-grid">
        <div>
          <label>Product Name</label>
          <input type="text" name="product_name" required>

          <label>Price (₱)</label>
          <input type="number" step="0.01" name="price" required>

          <label>Image</label>
          <input type="file" name="image" accept="image/*" onchange="previewImage(event)" required>
          <img id="preview" class="image-preview" />
        </div>
        <div>
          <label>Category</label>
          <select name="category_id" required>
            <option value="">-- Select Category --</option>
            <?php
              $cat_result = $conn->query("SELECT * FROM category");
              while ($cat = $cat_result->fetch_assoc()) {
                echo "<option value='{$cat['category_id']}'>{$cat['category_name']}</option>";
              }
            ?>
          </select>
          <label>Description</label>
          <textarea name="description" required></textarea>
        </div>
      </div>
      <button type="submit" class="btn">Add Product</button>
    </form>
    <a href="staff.php" class="back-link">← Back to Dashboard</a>
  </div>
</body>
</html>
