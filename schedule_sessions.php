<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$user_id = intval($_SESSION['user_id']);

// ✅ Handle delete request
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $stmt = $conn->prepare("DELETE FROM appointments WHERE appointment_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $delete_id, $user_id);
    $stmt->execute();
    echo "<script>alert('Appointment deleted successfully!'); window.location.href='scheduled_sessions.php';</script>";
    exit();
}

// ✅ Fetch all appointments for this user (latest first)
$query = "
    SELECT a.appointment_id, 
           s.name AS service_name, 
           a.appointment_date, 
           a.appointment_time, 
           a.status
    FROM appointments a
    JOIN services s ON a.service_id = s.service_id
    WHERE a.user_id = ?
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Scheduled Sessions</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }
        table {
            width: 90%;
            margin: 0 auto;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-radius: 10px;
            overflow: hidden;
        }
        th, td {
            padding: 15px;
            text-align: center;
            border-bottom: 1px solid #eee;
        }
        th {
            background: #80a833;
            color: white;
        }
        tr:hover {
            background: #f1f1f1;
        }
        .delete-btn {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 6px;
            cursor: pointer;
        }
        .delete-btn:hover {
            background: #c0392b;
        }
    </style>
</head>
<body>

    <h2>📅 Your Scheduled Sessions</h2>

    <table>
        <tr>
            <th>Service</th>
            <th>Date</th>
            <th>Time</th>
            <th>Status</th>
            <th>Action</th>
        </tr>

        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['service_name']) ?></td>
                    <td><?= htmlspecialchars($row['appointment_date']) ?></td>
                    <td><?= htmlspecialchars($row['appointment_time']) ?></td>
                    <td><?= htmlspecialchars($row['status']) ?></td>
                    <td>
                        <button class="delete-btn" onclick="confirmDelete(<?= $row['appointment_id'] ?>)">Delete</button>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="5">No scheduled sessions found.</td></tr>
        <?php endif; ?>
    </table>

    <script>
        function confirmDelete(id) {
            if (confirm('Are you sure you want to delete this appointment?')) {
                window.location.href = 'scheduled_sessions.php?delete_id=' + id;
            }
        }
    </script>

</body>
</html>