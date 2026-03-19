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

$conn->query("
UPDATE products 
SET quantity = quantity + $qty
WHERE product_id = $id
");

header("Location: ".$_SERVER['PHP_SELF']);
exit();
}

/* =====================
GET PRODUCTS
===================== */
$products = $conn->query("SELECT * FROM products ORDER BY product_name ASC");

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
<th>Add Stock</th>
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
<form method="POST">
<input type="hidden" name="product_id" value="<?= $row['product_id'] ?>">
<input type="number" name="quantity" required min="1" style="width:60px;">
<button name="add_stock" class="add-btn">Add</button>
</form>
</td>

</tr>

<?php endwhile; ?>

</table>

</div>
</body>
</html>
