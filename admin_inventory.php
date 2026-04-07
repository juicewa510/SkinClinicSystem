<?php
session_start();
include 'config.php';

/* =====================
ACCESS CONTROL
===================== */
if (!isset($_SESSION['role']) || 
($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'staff')) {
    header('Location:index.php');
    exit();
}

/* =====================
ADD STOCK
===================== */
if(isset($_POST['add_stock'])){

    $id = intval($_POST['product_id']);
    $qty = intval($_POST['quantity']);
    $expiry = $_POST['expiry_date'];

    // 1. UPDATE STOCK
    $conn->query("
        UPDATE products 
        SET quantity = quantity + $qty
        WHERE product_id = $id
    ");

    // 2. SAVE LOG WITH EXPIRY
    $conn->query("
        INSERT INTO inventory_logs (product_id, quantity, type, expiry_date, created_at)
        VALUES ($id, $qty, 'IN', '$expiry', NOW())
    ");


    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

/* =====================
GET PRODUCTS
===================== */
$products = $conn->query("
SELECT 
    p.*,
    MAX(l.created_at) AS last_added,
    MIN(l.expiry_date) AS oldest_expiry,
    MAX(l.expiry_date) AS newest_expiry
FROM products p
LEFT JOIN inventory_logs l 
    ON p.product_id = l.product_id AND l.type = 'IN'
GROUP BY p.product_id
ORDER BY p.product_name ASC
");

include 'sidebar_admin.php';
?>

<!DOCTYPE html>
<html>
<head>
<title>Inventory</title>
<link rel="stylesheet" href="users_style.css">

<style>
table{width:100%;border-collapse:collapse;}
th,td{padding:10px;border:1px solid #ddd;text-align:center;}
th{background:#80a833;color:white;}
.low{color:red;font-weight:bold;}
.add-btn{padding:5px 10px;background:#80a833;color:white;border:none;}
</style>

</head>
<body>

<div class="main">

<h2>Inventory System</h2>

<!-- LOW STOCK ALERT -->
<?php
$low = $conn->query("SELECT * FROM products WHERE quantity <= reorder_level");

if($low->num_rows > 0){
echo "<div style='background:#ffe6e6;padding:10px;margin-bottom:10px;'>";
echo "<b>⚠ Low Stock Alert</b><br>";

while($l = $low->fetch_assoc()){
echo $l['product_name']." (".$l['quantity'].")<br>";
}
echo "</div>";
}
?>

<table>

<tr>
<th>ID</th>
<th>Product</th>
<th>Stock</th>
<th>Reorder</th>
<th>Status</th>
<th>Last Added</th>
<th>Old Stock Expiry</th>
<th>New Stock Expiry</th>
<th>Add Stock (Qty + Expiry)</th>
</tr>

<?php while($row = $products->fetch_assoc()): ?>

<tr>

<td><?= $row['product_id'] ?></td>
<td><?= $row['product_name'] ?></td>
<td><?= $row['quantity'] ?></td>
<td><?= $row['reorder_level'] ?></td>

<td>
<?php
if($row['quantity'] <= $row['reorder_level']){
echo "<span class='low'>⚠ Reorder Needed</span>";
}else{
echo "In Stock";
}
?>
</td>

<td>
<?= $row['last_added'] ? date("Y-m-d H:i", strtotime($row['last_added'])) : '—' ?>
</td>

<td>
<?= $row['oldest_expiry'] ? $row['oldest_expiry'] : '—' ?>
</td>

<td>
<?= $row['newest_expiry'] ? $row['newest_expiry'] : '—' ?>
</td>

<td>
<form method="POST">
<input type="hidden" name="product_id" value="<?= $row['product_id'] ?>">
<input type="number" name="quantity" required min="1" style="width:60px;">
<input type="date" name="expiry_date" required style="width:140px;">
<button name="add_stock" class="add-btn">Add</button>
</form>
</td>

</tr>

<?php endwhile; ?>

</table>

</div>
</body>
</html>
