<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php?login=true');
    exit();
}

switch ($_SESSION['role']) {
    case 'admin':
        include 'sidebar_admin.php';
        break;
    case 'doctor':
        include 'sidebar_doctor.php';
        break;
    case 'staff':
        include 'sidebar_staff.php';
        break;
    default:
        include 'sidebar_patient.php';
        break;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SkinMedic</title>
    <link rel="stylesheet" href="users_style.css">
</head>
<body style="background: #fff;">
    
    <!-- Main Content -->
    <div class="main">
        <div class="topbar">
            <h2>Home</h2>
            <div class="date-box" id="dateBox">
                <p>Today's Date</p>
                <strong id="dateBox"><?= date("Y-m-d"); ?></strong>
        </div>
    </div>

        <div class="welcome-section">
            <div class="welcome-text">
                <h3>Welcome, <?= $_SESSION['firstName']; ?>!</h3>
            </div>
            <img src="skintransparent.png" width="150" alt="Medical Mask">
        </div>

        <h3 style="margin-top: 30px;">Your Upcoming Booking</h3>
        <table>
            <tr>
                <th>Appointment Number</th>
                <th>Session Title</th>
                <th>Doctor</th>
                <th>Scheduled Date & Time</th>
            </tr>
            <tr>
                <td>1</td>
                <td>Test Session</td>
                <td>Test Doctor</td>
                <td>2050-01-01 18:00</td>
            </tr>
        </table>
    </div>

</body>
</html>