<?php
session_start();
include 'config.php';

// Only allow doctor
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'doctor') {
    header('Location: index.php?login=true');
    exit();
}

$doctor_id = intval($_SESSION['user_id']);

// Get all approved appointments for this doctor (case and space insensitive)
$query = "
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
    WHERE a.doctor_id = ? AND TRIM(LOWER(a.status)) = 'approved'
    ORDER BY a.appointment_date ASC, a.appointment_time ASC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$result = $stmt->get_result();

include 'sidebar_doctor.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - Approved Appointments</title>
    <link rel="stylesheet" href="users_style.css">
    <style>
        table { border-collapse: collapse; width: 100%; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: center; }
        th { background: #80a833; color: white; }
        span.status-approved { color: green; font-weight: bold; }
    </style>
</head>
<body style="background:#fff;">

<main class="content">

    <header class="header">
        <h2>My Approved Bookings</h2>
    </header>

    <section class="approved-appointments">

        <?php if ($result->num_rows > 0): ?>
            <table>
                <tr>
                    <th>#</th>
                    <th>Patient</th>
                    <th>Service</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Status</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['appointment_id'] ?></td>
                        <td><?= htmlspecialchars($row['patient_name']) ?></td>
                        <td><?= htmlspecialchars($row['service_name']) ?></td>
                        <td><?= $row['appointment_date'] ?></td>
                        <td><?= $row['appointment_time'] ?></td>
                        <td><span class="status-approved">Approved</span></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p style="text-align:center;">No approved appointments found.</p>
        <?php endif; ?>

    </section>

</main>

</body>
</html>