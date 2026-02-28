<?php
session_start();
include 'config.php';

// Allow only staff or admin
if (!isset($_SESSION['role']) || 
   ($_SESSION['role'] !== 'staff' && $_SESSION['role'] !== 'admin')) {
    header('Location: index.php');
    exit();
}

// Update appointment status if form submitted
if (isset($_POST['update_status']) && isset($_POST['appointment_id'])) {
    $appointment_id = intval($_POST['appointment_id']);
    $new_status = $_POST['status'];

    // Validate status
    $valid_statuses = ['Pending', 'Approved', 'Completed', 'Cancelled'];
    if (in_array($new_status, $valid_statuses)) {
        $stmt = $conn->prepare("UPDATE appointments SET status = ? WHERE appointment_id = ?");
        $stmt->bind_param("si", $new_status, $appointment_id);
        $stmt->execute();
        $stmt->close();
        header("Location: ".$_SERVER['PHP_SELF']); // refresh page
        exit();
    }
}

// Get all appointments with patient, doctor, and service
$query = "
    SELECT 
        a.appointment_id,
        s.name AS service_name,
        CONCAT(p.firstName, ' ', p.lastName) AS patient_name,
        CONCAT(d.firstName, ' ', d.lastName) AS doctor_name,
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
        select { padding: 5px; }
        button.update-btn { padding: 4px 8px; margin-left: 5px; }
    </style>
</head>
<body style="background:#fff;">

<div class="main">

    <div class="topbar">
        <h2>All Appointments</h2>
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

    <h3 style="margin-top: 30px;">Appointments Overview</h3>

    <table border="1" width="100%" cellpadding="10" cellspacing="0">
        <tr style="background:#f2f2f2;">
            <th>Appointment #</th>
            <th>Patient</th>
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
                    <td><?= htmlspecialchars($row['patient_name']) ?></td>
                    <td><?= htmlspecialchars($row['service_name']) ?></td>
                    <td>
                        <?= $row['doctor_name'] 
                            ? "Dr. " . htmlspecialchars($row['doctor_name']) 
                            : "Not Assigned" ?>
                    </td>
                    <td><?= $row['appointment_date'] ?></td>
                    <td><?= $row['appointment_time'] ?></td>
                    <td>
                        <form method="POST" style="display:flex; justify-content:center; align-items:center;">
                            <input type="hidden" name="appointment_id" value="<?= $row['appointment_id'] ?>">
                            <select name="status">
                                <?php
                                $statuses = ['Pending', 'Approved', 'Completed', 'Cancelled'];
                                foreach ($statuses as $status) {
                                    $selected = ($row['status'] === $status) ? 'selected' : '';
                                    echo "<option value='$status' $selected>$status</option>";
                                }
                                ?>
                            </select>
                            <button type="submit" name="update_status" class="update-btn">Update</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="7" style="text-align:center;">No appointments found.</td>
            </tr>
        <?php endif; ?>
    </table>

</div>

</body>
</html>