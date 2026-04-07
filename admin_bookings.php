<?php
session_start();
include 'config.php';

/* -------------------------
ALLOW ONLY STAFF OR ADMIN
--------------------------*/
if (!isset($_SESSION['role']) || 
   ($_SESSION['role'] !== 'staff' && $_SESSION['role'] !== 'admin')) {
    header('Location: index.php');
    exit();
}

/* =========================================
AUTO COMPLETE + FIFO INVENTORY DEDUCTION
========================================= */

$appointments = $conn->query("
SELECT appointment_id, service_id 
FROM appointments
WHERE status='Approved'
AND TIMESTAMP(appointment_date, appointment_time) <= (NOW() - INTERVAL 1 HOUR)
");

while($appt = $appointments->fetch_assoc()){

    $appointment_id = $appt['appointment_id'];
    $service_id = $appt['service_id'];

    /* 1. UPDATE STATUS TO COMPLETED */
    $conn->query("
    UPDATE appointments 
    SET status='Completed'
    WHERE appointment_id = $appointment_id
    ");

    /* 2. DEDUCT PRODUCTS (ONLY ONCE) */
    $check = $conn->query("
    SELECT * FROM inventory_logs 
    WHERE appointment_id = $appointment_id
    ");

    if($check->num_rows == 0){

        $products = $conn->query("
        SELECT product_id, quantity_used 
        FROM service_products 
        WHERE service_id = $service_id
        ");

        while($prod = $products->fetch_assoc()){

            $product_id = $prod['product_id'];
            $qty = $prod['quantity_used'];

            $remaining = $qty;

            // 🔥 FIFO: GET OLDEST STOCK FIRST
            $stocks = $conn->query("
                SELECT * FROM inventory_logs
                WHERE product_id = $product_id
                AND type = 'IN'
                AND quantity > 0
                ORDER BY expiry_date ASC
            ");

            while($stock = $stocks->fetch_assoc()){

                if($remaining <= 0) break;

                $stock_id = $stock['id'];
                $available = $stock['quantity'];

                if($available <= 0) continue;

                if($available >= $remaining){

                    // deduct partial
                    $conn->query("
                    UPDATE inventory_logs
                    SET quantity = quantity - $remaining
                    WHERE id = $stock_id
                    ");

                    $remaining = 0;

                } else {

                    // consume whole batch
                    $conn->query("
                    UPDATE inventory_logs
                    SET quantity = 0
                    WHERE id = $stock_id
                    ");

                    $remaining -= $available;
                }
            }

            // ❗ Not enough stock protection
            if($remaining > 0){
                continue;
            }

            // Update total quantity (for UI display)
            $conn->query("
            UPDATE products 
            SET quantity = quantity - $qty
            WHERE product_id = $product_id
            ");

            // Log OUT
            $conn->query("
            INSERT INTO inventory_logs (product_id, quantity, type, appointment_id, created_at)
            VALUES ($product_id, $qty, 'OUT', $appointment_id, NOW())
            ");
        }
    }
}

/* -------------------------
UPDATE STATUS (APPROVE ONLY)
--------------------------*/
if(isset($_POST['update_status'])){

    $id = intval($_POST['appointment_id']);
    $status = $_POST['status'];

    $check = $conn->query("SELECT status FROM appointments WHERE appointment_id=$id");
    $row = $check->fetch_assoc();

    if($row['status'] == "Cancelled"){
        exit("This booking was cancelled.");
    }

    $conn->query("UPDATE appointments SET status='$status' WHERE appointment_id=$id");

    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

/* -------------------------
GET ALL BOOKINGS
--------------------------*/
$query = "
SELECT 
a.appointment_id,
s.name AS service_name,
CONCAT(p.firstName,' ',p.lastName) AS patient_name,
CONCAT(d.firstName,' ',d.lastName) AS doctor_name,
a.appointment_date,
a.appointment_time,
a.status
FROM appointments a
LEFT JOIN services s ON a.service_id = s.service_id
LEFT JOIN users p ON a.user_id = p.user_id
LEFT JOIN users d ON a.doctor_id = d.user_id
ORDER BY a.appointment_date DESC, a.appointment_time DESC
";

$result = $conn->query($query);

include 'sidebar_admin.php';
?>

<!DOCTYPE html>
<html>
<head>

<title>SkinMedic - All Appointments</title>
<link rel="stylesheet" href="users_style.css">

<style>
table{
width:100%;
border-collapse:collapse;
}
th,td{
padding:10px;
border:1px solid #ddd;
text-align:center;
}
th{
background:#80a833;
color:white;
}
tr:hover{
background:#f5f5f5;
cursor:pointer;
}

.badge{
padding:5px 10px;
border-radius:5px;
color:white;
font-size:13px;
}
.pending{background:orange;}
.approved{background:green;}
.completed{background:#007bff;}
.cancelled{background:red;}

.modal{
display:none;
position:fixed;
left:0;
top:0;
width:100%;
height:100%;
background:rgba(0,0,0,0.6);
}
.modal-content{
background:white;
width:400px;
margin:10% auto;
padding:20px;
border-radius:10px;
text-align:center;
}
.close{
float:right;
font-size:22px;
cursor:pointer;
}
button{
padding:8px 15px;
border:none;
border-radius:5px;
margin:5px;
cursor:pointer;
}
.approve-btn{
background:#28a745;
color:white;
}
</style>

</head>

<body>

<div class="main">

<h2>All Appointments</h2>

<table>
<tr>
<th>ID</th>
<th>Patient</th>
<th>Service</th>
<th>Doctor</th>
<th>Date</th>
<th>Time</th>
<th>Status</th>
</tr>

<?php while($row = $result->fetch_assoc()): ?>

<tr onclick="openModal(
'<?= $row['appointment_id']?>',
'<?= htmlspecialchars($row['patient_name'])?>',
'<?= htmlspecialchars($row['service_name'])?>',
'<?= htmlspecialchars($row['doctor_name'])?>',
'<?= $row['appointment_date']?>',
'<?= $row['appointment_time']?>',
'<?= $row['status']?>'
)">

<td><?= $row['appointment_id']?></td>
<td><?= htmlspecialchars($row['patient_name'])?></td>
<td><?= htmlspecialchars($row['service_name'])?></td>

<td>
<?= $row['doctor_name'] ? "Dr. ".htmlspecialchars($row['doctor_name']) : "Not Assigned" ?>
</td>

<td><?= $row['appointment_date']?></td>
<td><?= $row['appointment_time']?></td>

<td>
<?php
if($row['status']=="Pending"){
echo "<span class='badge pending'>Pending</span>";
}
elseif($row['status']=="Approved"){
echo "<span class='badge approved'>Approved</span>";
}
elseif($row['status']=="Completed"){
echo "<span class='badge completed'>Completed</span>";
}
else{
echo "<span class='badge cancelled'>Cancelled</span>";
}
?>
</td>

</tr>

<?php endwhile; ?>

</table>

</div>

<div id="bookingModal" class="modal">
<div class="modal-content">

<span class="close" onclick="closeModal()">&times;</span>

<h3>Booking Details</h3>

<p><b>ID:</b> <span id="m_id"></span></p>
<p><b>Patient:</b> <span id="m_patient"></span></p>
<p><b>Service:</b> <span id="m_service"></span></p>
<p><b>Doctor:</b> <span id="m_doctor"></span></p>
<p><b>Date:</b> <span id="m_date"></span></p>
<p><b>Time:</b> <span id="m_time"></span></p>
<p><b>Status:</b> <span id="m_status"></span></p>

<form method="POST">
<input type="hidden" name="appointment_id" id="appointment_id">
<input type="hidden" name="status" id="status_value">

<div id="actionButtons">
<button type="submit" name="update_status"
class="approve-btn"
onclick="setStatus('Approved')">
Approve
</button>
</div>

</form>

</div>
</div>

<script>

function openModal(id,patient,service,doctor,date,time,status){

document.getElementById("bookingModal").style.display="block";

document.getElementById("m_id").innerText=id;
document.getElementById("m_patient").innerText=patient;
document.getElementById("m_service").innerText=service;
document.getElementById("m_doctor").innerText=doctor;
document.getElementById("m_date").innerText=date;
document.getElementById("m_time").innerText=time;
document.getElementById("m_status").innerText=status;

document.getElementById("appointment_id").value=id;

if(status === "Cancelled" || status === "Completed"){
document.getElementById("actionButtons").style.display="none";
}else{
document.getElementById("actionButtons").style.display="block";
}
}

function closeModal(){
document.getElementById("bookingModal").style.display="none";
}

function setStatus(status){
document.getElementById("status_value").value=status;
}

</script>

</body>
</html>
