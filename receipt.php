<?php
session_start();
include 'config.php';

if (!isset($_GET['id'])) {
    die("Invalid receipt");
}

$id = intval($_GET['id']);

$query = "
SELECT p.*, pr.product_name, u.firstName, u.lastName
FROM purchases p
LEFT JOIN products pr ON p.product_id = pr.product_id
LEFT JOIN users u ON p.user_id = u.user_id
WHERE p.purchase_id = $id
";

$result = $conn->query($query);

if (!$result || $result->num_rows == 0) {
    die("Receipt not found.");
}

$row = $result->fetch_assoc();

?>


<!DOCTYPE html>
<html>
<head>
<title>Invoice Receipt</title>

<style>
body{
    font-family: Arial;
    background:#f0f0f0;
    padding:20px;
}

.invoice{
    max-width:500px;
    margin:auto;
    background:#fff;
    padding:25px;
    border-radius:10px;
    box-shadow:0 5px 15px rgba(0,0,0,0.1);
}

/* HEADER */
.header{
    text-align:center;
    border-bottom:2px dashed #ccc;
    padding-bottom:10px;
    margin-bottom:15px;
}

.header h2{
    margin:0;
    color:#80a833;
}

.header p{
    font-size:13px;
    color:#666;
}

/* DETAILS */
.details{
    font-size:14px;
    margin-bottom:15px;
}

.details p{
    margin:5px 0;
}

/* TABLE */
table{
    width:100%;
    border-collapse:collapse;
    margin-top:10px;
}

th, td{
    padding:8px;
    text-align:left;
    border-bottom:1px solid #ddd;
}

th{
    background:#f8f8f8;
}

/* TOTAL */
.total{
    text-align:right;
    margin-top:10px;
    font-size:18px;
    font-weight:bold;
    color:#80a833;
}

/* FOOTER */
.footer{
    text-align:center;
    margin-top:20px;
    font-size:13px;
    color:#777;
}

/* BUTTONS */
.actions{
    text-align:center;
    margin-top:20px;
}

.btn{
    display:inline-block;
    margin:5px;
    background:#80a833;
    color:#fff;
    padding:10px 15px;
    border-radius:6px;
    text-decoration:none;
}

.btn:hover{
    background:#6f8e2d;
}

.print-btn{
    background:#333;
}
</style>

</head>

<body>

<div class="invoice">

<!-- HEADER -->
<div class="header">
    <h2>SkinMedic Clinic</h2>
    <p>Official Receipt</p>
</div>

<!-- CUSTOMER DETAILS -->
<div class="details">
    <p><strong>Name:</strong> <?= $row['firstName'].' '.$row['lastName'] ?></p>
    <p><strong>Date:</strong> <?= date("F d, Y h:i A", strtotime($row['purchase_date'])) ?></p>
    <p><strong>Payment:</strong> <?= $row['payment_method'] ?></p>
    <p><strong>Address:</strong> <?= $row['shipping_address'] ?></p>
</div>

<!-- PRODUCT TABLE -->
<table>
    <tr>
        <th>Product</th>
        <th>Qty</th>
        <th>Price</th>
    </tr>
    <tr>
        <td><?= $row['product_name'] ?></td>
        <td><?= $row['quantity'] ?></td>
        <td>₱<?= number_format($row['total_price'],2) ?></td>
    </tr>
</table>

<!-- TOTAL -->
<div class="total">
    Total: ₱<?= number_format($row['total_price'],2) ?>
</div>

<!-- FOOTER -->
<div class="footer">
    <p>Thank you for your purchase!</p>
    <p>SkinMedic Clinic System</p>
</div>

<!-- ACTION BUTTONS -->
<div class="actions">
    <a href="patient_store.php" class="btn">Back to Store</a>
</div>

</div>

</body>
</html>
