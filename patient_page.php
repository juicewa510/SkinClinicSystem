<?php
session_start();
include 'config.php';

/* ===============================
CHECK LOGIN
================================ */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'patient') {
    header('Location: index.php?login=true');
    exit();
}

if (!isset($_SESSION['user_id'])) {
    die("User ID not found in session.");
}

$user_id = intval($_SESSION['user_id']);
$today = date('Y-m-d');


/* ===============================
CANCEL APPOINTMENT
================================ */
if (isset($_POST['cancel_id'])) {

    $cancel_id = intval($_POST['cancel_id']);

    $check = $conn->prepare("SELECT status FROM appointments WHERE appointment_id=? AND user_id=?");
    $check->bind_param("ii", $cancel_id, $user_id);
    $check->execute();
    $check_result = $check->get_result();

    if ($check_result->num_rows > 0) {

        $row = $check_result->fetch_assoc();

        if ($row['status'] === 'Pending' || $row['status'] === 'Approved') {

            $update = $conn->prepare("UPDATE appointments SET status='Cancelled' WHERE appointment_id=?");
            $update->bind_param("i", $cancel_id);
            $update->execute();
        }
    }

    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}


/* ===============================
GET UPCOMING APPOINTMENTS
================================ */

$query = "
SELECT 
    a.appointment_id,
    s.name AS service_name,
    CONCAT(d.firstName,' ',d.lastName) AS doctor_name,
    a.appointment_date,
    a.appointment_time,
    a.status
FROM appointments a
LEFT JOIN services s ON a.service_id = s.service_id
LEFT JOIN users d ON a.doctor_id = d.user_id
WHERE a.user_id = ? AND a.appointment_date >= ?
ORDER BY a.appointment_date ASC, a.appointment_time ASC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("is", $user_id, $today);
$stmt->execute();
$result = $stmt->get_result();

include 'sidebar_patient.php';
?>

<!DOCTYPE html>
<html>
<head>

<title>SkinMedic - Patient Home</title>
<link rel="stylesheet" href="users_style.css">

<style>

.clickable-row{
cursor:pointer;
transition:0.2s;
}

.clickable-row:hover{
background:#f9f9f9;
}

/* MODAL */

.modal{
display:none;
position:fixed;
z-index:1000;
left:0;
top:0;
width:100%;
height:100%;
background:rgba(0,0,0,0.5);
}

.modal-content{
background:white;
padding:20px;
width:400px;
margin:8% auto;
border-radius:8px;
position:relative;
}

.close{
position:absolute;
right:15px;
top:10px;
font-size:18px;
cursor:pointer;
font-weight:bold;
}

.cancel-btn{
margin-top:15px;
padding:8px 15px;
background:red;
color:white;
border:none;
border-radius:4px;
cursor:pointer;
}

.cancel-btn:hover{
background:darkred;
}

</style>

</head>

<body style="background:#fff;">

<div class="main">

<div class="topbar">
<h2>Home</h2>

<div class="date-box">
<p>Today's Date</p>
<strong><?= date("Y-m-d"); ?></strong>
</div>
</div>


<div class="welcome-section">
<div class="welcome-text">
<h3>Welcome, <?= htmlspecialchars($_SESSION['firstName']); ?>!</h3>
</div>

<img src="skintransparent.png" width="150">
</div>


<h3 style="margin-top:30px;">Your Upcoming Appointments</h3>


<table border="1" width="100%" cellpadding="10" cellspacing="0">

<tr style="background:#f2f2f2;">
<th>Service</th>
<th>Doctor</th>
<th>Date</th>
<th>Time</th>
<th>Status</th>
</tr>

<?php if ($result->num_rows > 0): ?>

<?php while ($row = $result->fetch_assoc()): ?>

<tr class="clickable-row"

onclick="openModal(
'<?= htmlspecialchars($row['service_name']) ?>',
'<?= htmlspecialchars($row['doctor_name']) ?>',
'<?= $row['appointment_date'] ?>',
'<?= $row['appointment_time'] ?>',
'<?= $row['status'] ?>',
'<?= $row['appointment_id'] ?>'
)">

<td><?= htmlspecialchars($row['service_name']) ?></td>

<td>
<?= $row['doctor_name'] ? "Dr. ".htmlspecialchars($row['doctor_name']) : "Not Assigned" ?>
</td>

<td><?= $row['appointment_date'] ?></td>
<td><?= $row['appointment_time'] ?></td>

<td>

<?php

if ($row['status'] == 'Pending') {
echo "<span style='color:orange;font-weight:bold;'>Pending</span>";
}
elseif ($row['status'] == 'Approved') {
echo "<span style='color:green;font-weight:bold;'>Approved</span>";
}
elseif ($row['status'] == 'Completed') {
echo "<span style='color:blue;font-weight:bold;'>Completed</span>";
}
else {
echo "<span style='color:red;font-weight:bold;'>Cancelled</span>";
}

?>

</td>

</tr>

<?php endwhile; ?>

<?php else: ?>

<tr>
<td colspan="5" style="text-align:center;">No upcoming appointments found.</td>
</tr>

<?php endif; ?>

</table>

</div>


<!-- MODAL -->

<div id="bookingModal" class="modal">

<div class="modal-content">

<span class="close" onclick="closeModal()">&times;</span>

<h3>Booking Details</h3>

<p><strong>Service:</strong> <span id="modalService"></span></p>
<p><strong>Doctor:</strong> <span id="modalDoctor"></span></p>
<p><strong>Date:</strong> <span id="modalDate"></span></p>
<p><strong>Time:</strong> <span id="modalTime"></span></p>
<p><strong>Status:</strong> <span id="modalStatus"></span></p>


<form method="POST" id="cancelForm">

<input type="hidden" name="cancel_id" id="modalCancelId">

<button type="submit" class="cancel-btn"
onclick="return confirm('Are you sure you want to cancel this appointment?');">

Cancel Appointment

</button>

</form>

</div>

</div>


<script>

function openModal(service,doctor,date,time,status,id){

document.getElementById("modalService").innerText = service;
document.getElementById("modalDoctor").innerText = doctor ? "Dr. "+doctor : "Not Assigned";
document.getElementById("modalDate").innerText = date;
document.getElementById("modalTime").innerText = time;
document.getElementById("modalStatus").innerText = status;

document.getElementById("modalCancelId").value = id;

/* hide cancel button if completed or cancelled */

if(status === "Completed" || status === "Cancelled"){
document.getElementById("cancelForm").style.display = "none";
}
else{
document.getElementById("cancelForm").style.display = "block";
}

document.getElementById("bookingModal").style.display = "block";

}


function closeModal(){
document.getElementById("bookingModal").style.display = "none";
}


window.onclick = function(event){

let modal = document.getElementById("bookingModal");

if(event.target == modal){
modal.style.display = "none";
}

}

</script>

</body>
</html>
