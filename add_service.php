<?php
include 'config.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $desc = $_POST['description'];
    $status = $_POST['status'];

    $imgName = $_FILES['image']['name'];
    $tmp = $_FILES['image']['tmp_name'];
    move_uploaded_file($tmp, "uploads/" . $imgName);

    $query = "INSERT INTO services (name, description, image, status) VALUES ('$name', '$desc', '$imgName', '$status')";
    $conn->query($query);
    header('Location: doctor_services.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Service - SkinMedic</title>
    <link rel="stylesheet" href="users_style.css">
    <style>
        .add-service-container {
            margin: 40px auto;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            padding: 40px;
            max-width: 600px;
        }

        .add-service-container h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        .add-service-container form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .add-service-container input, 
        .add-service-container textarea, 
        .add-service-container select {
            padding: 10px;
            font-size: 1rem;
            border-radius: 10px;
            border: 1px solid #ccc;
            outline: none;
        }

        .add-service-container button {
            background-color: #008080;
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.2s ease;
        }

        .add-service-container button:hover {
            background-color: #006666;
        }
    </style>
</head>
<body style="background: #fff;">
    <a href="doctor_services.php" style="text-decoration:none; color:#008080; font-weight:bold;">← Back to Services</a>

    <!-- Main Content -->
    <main class="content">
        <header class="header">
            <h2>Add New Service</h2>
            <div class="date-box">
                <p>Today's Date</p>
                <strong id="dateBox"><?= date("Y-m-d"); ?></strong>
            </div>
        </header>

        <section class="add-service-container">
            <h2>Add Service Details</h2>
            <form method="POST" enctype="multipart/form-data">
                <label for="name">Service Name</label>
                <input type="text" name="name" id="name" placeholder="Enter service name" required>

                <label for="description">Description</label>
                <textarea name="description" id="description" rows="4" placeholder="Write a short description..." required></textarea>

                <label for="image">Upload Image</label>
                <input type="file" name="image" id="image" accept="image/*">

                <label for="status">Status</label>
                <select name="status" id="status">
                    <option value="available">Available</option>
                    <option value="not available">Not Available</option>
                </select>

                <button type="submit">Add Service</button>
            </form>
        </section>
    </main>

<script src="script.js"></script>
</body>
</html>