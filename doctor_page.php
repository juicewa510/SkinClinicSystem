<?php
session_start();
include 'config.php';

// Only allow doctor
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'doctor') {
    header('Location: index.php?login=true');
    exit();
}

include 'sidebar_doctor.php';

$doctor_id = intval($_SESSION['user_id']);
$today = date('Y-m-d');
$next_week = date('Y-m-d', strtotime('+7 days'));

// Upcoming appointments in next 7 days
$appt_query = "
    SELECT 
        a.appointment_id,
        CONCAT(p.firstName, ' ', p.lastName) AS patient_name,
        s.name AS service_name,
        a.appointment_date,
        a.appointment_time,
        a.status
    FROM appointments a
    LEFT JOIN users p ON a.user_id = p.user_id
    LEFT JOIN services s ON a.service_id = s.service_id
    WHERE a.doctor_id = ? AND a.appointment_date BETWEEN ? AND ?
    ORDER BY a.appointment_date ASC, a.appointment_time ASC
";

$stmt_appt = $conn->prepare($appt_query);
$stmt_appt->bind_param("iss", $doctor_id, $today, $next_week);
$stmt_appt->execute();
$upcoming_appts = $stmt_appt->get_result();

// Today's sessions
$session_query = "
    SELECT 
        a.appointment_id,
        CONCAT(p.firstName, ' ', p.lastName) AS patient_name,
        s.name AS service_name,
        a.appointment_time,
        a.status
    FROM appointments a
    LEFT JOIN users p ON a.user_id = p.user_id
    LEFT JOIN services s ON a.service_id = s.service_id
    WHERE a.doctor_id = ? AND a.appointment_date = ?
    ORDER BY a.appointment_time ASC
";

$stmt_sess = $conn->prepare($session_query);
$stmt_sess->bind_param("is", $doctor_id, $today);
$stmt_sess->execute();
$today_sessions = $stmt_sess->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SkinMedic - Doctor Home</title>
    <link rel="stylesheet" href="users_style.css">
    <style>
        table { border-collapse: collapse; width: 100%; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: center; }
        th { background: #80a833; color: white; }
        span.status-pending { color: orange; font-weight: bold; }
        span.status-approved { color: green; font-weight: bold; }
        span.status-completed { color: blue; font-weight: bold; }
        span.status-cancelled { color: red; font-weight: bold; }
    </style>
</head>
<body style="background: #fff;">

<main class="content">

    <header class="header">
        <h2>Doctor Dashboard</h2>
        <div class="date-box">
            <p>Today's Date</p>
            <strong><?= $today ?></strong>
        </div>
    </header>

    <div class="welcome-section">
        <div class="welcome-text">
            <h3>Welcome, Dr. <?= htmlspecialchars($_SESSION['firstName'] . ' ' . $_SESSION['lastName']); ?>!</h3>
            <p>Here’s your daily schedule and upcoming appointments.</p>
        </div>
        <img src="skintransparent.png" width="150">
    </div>

        <!-- Today's Sessions -->
        <div class="upcoming-box" style="margin-top:20px;">
            <h3>Today's Sessions</h3>
            <?php if ($today_sessions->num_rows > 0): ?>
                <table>
                    <tr>
                        <th>#</th>
                        <th>Patient</th>
                        <th>Service</th>
                        <th>Time</th>
                        <th>Status</th>
                    </tr>
                    <?php while ($row = $today_sessions->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['appointment_id'] ?></td>
                            <td><?= htmlspecialchars($row['patient_name']) ?></td>
                            <td><?= htmlspecialchars($row['service_name']) ?></td>
                            <td><?= $row['appointment_time'] ?></td>
                            <td>
                                <?php
                                switch($row['status']) {
                                    case 'Pending': echo "<span class='status-pending'>Pending</span>"; break;
                                    case 'Approved': echo "<span class='status-approved'>Approved</span>"; break;
                                    case 'Completed': echo "<span class='status-completed'>Completed</span>"; break;
                                    default: echo "<span class='status-cancelled'>Cancelled</span>"; break;
                                }
                                ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            <?php else: ?>
                <p>No sessions scheduled for today.</p>
            <?php endif; ?>
        </div>


</main>

</body>
</html>