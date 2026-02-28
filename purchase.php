<?php
session_start();
include 'config.php';

if (!isset($_SESSION['email'])) {
    header('Location: index.php');
    exit();
}

if (!isset($_SESSION['user_id'])) {
    die("Error: User ID not found in session.");
}

$user_id = intval($_SESSION['user_id']);

// Get product ID
$product_id = $_GET['product_id'] ?? null;
if (!$product_id) {
    die("Error: No product ID provided.");
}

// Fetch user info
$userQuery = $conn->query("SELECT firstName, lastName, email FROM users WHERE user_id = $user_id");
$user = $userQuery->fetch_assoc();

// Fetch product info
$productQuery = $conn->query("SELECT * FROM products WHERE product_id = $product_id");
$product = $productQuery->fetch_assoc();

// Handle purchase
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quantity = intval($_POST['quantity']);
    $address = $_POST['address'];

    if ($quantity <= 0 || $quantity > $product['quantity']) {
        echo "<script>alert('Invalid quantity.');</script>";
    } else {
        $stmt = $conn->prepare("
            INSERT INTO purchases (product_id, user_id, quantity, shipping_address)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("iiis", $product_id, $user_id, $quantity, $address);

        if ($stmt->execute()) {
            // Reduce product stock
            $conn->query("UPDATE products SET quantity = quantity - $quantity WHERE product_id = $product_id");

            echo "
            <script>
                alert('🎉 Purchase successful!');
                window.location.href = 'patient_store.php';
            </script>
            ";
        } else {
            echo "<script>alert('Error processing purchase.');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Purchase Product</title>
    <style>
        body {
            font-family: Arial;
            background: #f5f5f5;
            padding: 20px;
        }
        .container {
            max-width: 500px;
            margin: 50px auto;
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 3px 8px rgba(0,0,0,0.1);
        }
        h2 { text-align: center; }
        form {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        input, select, textarea {
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ccc;
        }
        button {
            background: #80a833;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 10px;
            cursor: pointer;
        }
        button:hover { background: #6f8e2d; }
        .product-name {
            text-align: center;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .back-btn {
            text-align: center;
            margin-top: 10px;
            display: block;
            color: #80a833;
            text-decoration: none;
        }
        img { max-width: 100%; border-radius: 8px; margin-bottom: 10px; }
    </style>
</head>
<body>

<div class="container">
    <h2>Purchase Product</h2>

    <?php if ($product): ?>
        <img src="uploads/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['product_name']) ?>" onerror="this.src='https://i.ibb.co/2s7sG4v/placeholder.png'">
        <p class="product-name"><?= htmlspecialchars($product['product_name']) ?> — ₱<?= htmlspecialchars($product['selling_price']) ?></p>
        <p><?= htmlspecialchars($product['description']) ?></p>

        <form method="POST">
            <label>Full Name</label>
            <input type="text" value="<?= htmlspecialchars($user['firstName'] . ' ' . $user['lastName']) ?>" readonly>

            <label>Email</label>
            <input type="email" value="<?= htmlspecialchars($user['email']) ?>" readonly>

            <label>Quantity</label>
            <input type="number" name="quantity" min="1" max="<?= $product['quantity'] ?>" required>

            <label>Shipping Address</label>
            <textarea name="address" required><?= htmlspecialchars($user['address'] ?? '') ?></textarea>

            <button type="submit">Confirm Purchase</button>
        </form>
    <?php else: ?>
        <p>Product not found.</p>
    <?php endif; ?>

    <a href="patient_store.php" class="back-btn">← Back to Store</a>
</div>

</body>
</html>
