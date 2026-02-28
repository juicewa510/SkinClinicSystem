<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = $_POST['product_name'];
    $desc = $_POST['description'];
    $category = $_POST['category'];
    $brand = $_POST['brand'];
    $supplier = $_POST['supplier'];
    $batch_number = $_POST['batch_number'];
    $quantity = $_POST['quantity'];
    $reorder_level = $_POST['reorder_level'];
    $cost_price = $_POST['cost_price'];
    $selling_price = $_POST['selling_price'];
    $expiry_date = $_POST['expiry_date'];
    $storage_location = $_POST['storage_location'];
    $status = $_POST['status'];

    $imgName = $_FILES['image']['name'];
    $tmp = $_FILES['image']['tmp_name'];
    move_uploaded_file($tmp, "uploads/" . $imgName);

    $conn->query("
        INSERT INTO products 
        (product_name, description, category, brand, supplier, batch_number, quantity,
        reorder_level, cost_price, selling_price, expiry_date, storage_location,
        status, date_added, image)
        VALUES (
            '$name', '$desc', '$category', '$brand', '$supplier', '$batch_number',
            '$quantity', '$reorder_level', '$cost_price', '$selling_price',
            '$expiry_date', '$storage_location', '$status', NOW(), '$imgName'
        )
    ");

    header("Location: product_management.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Product - SkinMedic</title>
    <link rel="stylesheet" href="users_style.css">

    <style>
        .add-product-container {
            margin: 40px auto;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            padding: 40px;
            max-width: 700px;
        }

        .add-product-container h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        .add-product-container form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .add-product-container input,
        .add-product-container textarea,
        .add-product-container select {
            padding: 10px;
            font-size: 1rem;
            border-radius: 10px;
            border: 1px solid #ccc;
        }

        .add-product-container button {
            background-color: #80a833;
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-size: 1rem;
            cursor: pointer;
        }

        .add-product-container button:hover {
            background-color: #829b53;
        }
    </style>
</head>

<body style="background:#fff;">

<a href="product_management.php"
   style="text-decoration:none; color:#80a833; font-weight:bold;">
   ← Back to Products
</a>

<main class="content">
    <header class="header">
        <h2>Add New Product</h2>
        <div class="date-box">
            <p>Today's Date</p>
            <strong><?= date("Y-m-d"); ?></strong>
        </div>
    </header>

    <section class="add-product-container">
        <h2>Product Details</h2>

        <form method="POST" enctype="multipart/form-data">

            <label>Product Name</label>
            <input type="text" name="product_name" required>

            <label>Description</label>
            <textarea name="description" rows="4" required></textarea>

            <label>Category</label>
            <input type="text" name="category">

            <label>Brand</label>
            <input type="text" name="brand">

            <label>Supplier</label>
            <input type="text" name="supplier">

            <label>Batch Number</label>
            <input type="text" name="batch_number">

            <label>Quantity</label>
            <input type="number" name="quantity" min="0">

            <label>Reorder Level</label>
            <input type="number" name="reorder_level" min="0">

            <label>Cost Price</label>
            <input type="number" step="0.01" name="cost_price">

            <label>Selling Price</label>
            <input type="number" step="0.01" name="selling_price">

            <label>Expiry Date</label>
            <input type="date" name="expiry_date">

            <label>Storage Location</label>
            <input type="text" name="storage_location">

            <label>Status</label>
            <select name="status">
                <option value="available">Available</option>
                <option value="not available">Not Available</option>
            </select>

            <label>Upload Image</label>
            <input type="file" name="image" accept="image/*">

            <button type="submit">Add Product</button>
        </form>
    </section>
</main>

</body>
</html>
