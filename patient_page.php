<?php
session_start();
include 'config.php';

// Check if logged in as patient
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'patient') {
    header('Location: index.php?login=true');
    exit();
}

if (!isset($_SESSION['user_id'])) {
    die("User ID not found in session.");
}

$user_id = intval($_SESSION['user_id']);
$today = date('Y-m-d'); // current date

// Get upcoming appointments (today or later) for the patient
$query = "
    SELECT 
        a.appointment_id,
        s.name AS service_name,
        CONCAT(d.firstName, ' ', d.lastName) AS doctor_name,
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

    <h3 style="margin-top: 30px;">Your Upcoming Appointments</h3>

    <table border="1" width="100%" cellpadding="10" cellspacing="0">
        <tr style="background:#f2f2f2;">
            <th>Appointment #</th>
            <th>Service</th>
            <th>Doctor</th>
            <th>Date</th>
            <th>Time</th>
            <th>Status</th>
        </tr>

        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['appointment_id'] ?></td>
                    <td><?= htmlspecialchars($row['service_name']) ?></td>
                    <td>
                        <?= $row['doctor_name'] 
                            ? "Dr. " . htmlspecialchars($row['doctor_name']) 
                            : "Not Assigned" ?>
                    </td>
                    <td><?= $row['appointment_date'] ?></td>
                    <td><?= $row['appointment_time'] ?></td>
                    <td>
                        <?php
                        if ($row['status'] == 'Pending') {
                            echo "<span style='color:orange;font-weight:bold;'>Pending</span>";
                        } elseif ($row['status'] == 'Approved') {
                            echo "<span style='color:green;font-weight:bold;'>Approved</span>";
                        } elseif ($row['status'] == 'Completed') {
                            echo "<span style='color:blue;font-weight:bold;'>Completed</span>";
                        } else {
                            echo "<span style='color:red;font-weight:bold;'>Cancelled</span>";
                        }
                        ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="6" style="text-align:center;">No upcoming appointments found.</td>
            </tr>
        <?php endif; ?>

    </table>

</div>

</body>
</html>