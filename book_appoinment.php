<?php
session_start();
include 'config.php';

if (!isset($_SESSION['email'])) {
    header('Location: index.php');
    exit();
}

if (!isset($_SESSION['user_id'])) {
    die("Error: User ID not found in session.");
}

$user_id = intval($_SESSION['user_id']);

// Get service ID
$service_id = $_GET['service_id'] ?? $_GET['id'] ?? $_GET['treatment_id'] ?? null;
if (!$service_id) {
    die("Error: No service ID provided.");
}

// Fetch user info (SAFE)
$userStmt = $conn->prepare("SELECT firstName, lastName, email FROM users WHERE user_id = ?");
$userStmt->bind_param("i", $user_id);
$userStmt->execute();
$user = $userStmt->get_result()->fetch_assoc();

// Fetch service info (SAFE)
$serviceStmt = $conn->prepare("SELECT * FROM services WHERE service_id = ?");
$serviceStmt->bind_param("i", $service_id);
$serviceStmt->execute();
$service = $serviceStmt->get_result()->fetch_assoc();

// Fetch doctors
$doctorQuery = $conn->query("SELECT user_id, firstName, lastName FROM users WHERE role='doctor'");

// Handle booking
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $doctor_id = intval($_POST['doctor_id']);
    $date = $_POST['appointment_date'];
    $time = $_POST['appointment_time'];

    $stmt = $conn->prepare("
        INSERT INTO appointments 
        (service_id, user_id, doctor_id, appointment_date, appointment_time, status, created_at)
        VALUES (?, ?, ?, ?, ?, 'Pending', NOW())
    ");

    $stmt->bind_param("iiiss", $service_id, $user_id, $doctor_id, $date, $time);

    if ($stmt->execute()) {
        echo "
        <script>
            alert('🎉 Appointment booked successfully!');
            window.location.href = 'patient_services.php';
        </script>
        ";
        exit();
    } else {
        echo "<script>alert('Error booking appointment.');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Book Appointment</title>
    <style>
        body { font-family: Arial; background: #f5f5f5; padding: 20px; }
        .container {
            max-width: 500px;
            margin: 50px auto;
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 3px 8px rgba(0,0,0,0.1);
        }
        h2 { text-align: center; }
        form { display: flex; flex-direction: column; gap: 12px; }
        input, select {
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ccc;
        }
        button {
            background: #80a833;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 10px;
            cursor: pointer;
        }
        button:hover { background: #6f8e2d; }
        .service-name {
            text-align: center;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .back-btn {
            text-align: center;
            margin-top: 10px;
            display: block;
            color: #80a833;
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Book Your Appointment</h2>

    <?php if ($service): ?>
        <p class="service-name">
            <?= htmlspecialchars($service['name']) ?> — ₱<?= htmlspecialchars($service['price']) ?>
        </p>
    <?php endif; ?>

    <form method="POST">

        <label>Full Name</label>
        <input type="text" value="<?= htmlspecialchars($user['firstName'] . ' ' . $user['lastName']) ?>" readonly>

        <label>Email</label>
        <input type="email" value="<?= htmlspecialchars($user['email']) ?>" readonly>

        <!-- NEW DOCTOR DROPDOWN -->
        <label>Select Doctor</label>
        <select name="doctor_id" required>
            <option value="">Select Doctor</option>
            <?php while ($doc = $doctorQuery->fetch_assoc()): ?>
                <option value="<?= $doc['user_id'] ?>">
                    Dr. <?= htmlspecialchars($doc['firstName'] . ' ' . $doc['lastName']) ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label>Appointment Date</label>
        <input type="date" name="appointment_date" id="appointment_date" required>

        <label>Available Time</label>
        <select name="appointment_time" id="appointment_time" required>
            <option value="">Select date first</option>
        </select>

        <button type="submit">Confirm Booking</button>
    </form>

    <a href="patient_services.php" class="back-btn">← Back to Services</a>
</div>

<script>
document.getElementById('appointment_date').addEventListener('change', function () {
    const date = this.value;
    const serviceId = <?= $service_id ?>;
    const timeSelect = document.getElementById('appointment_time');

    timeSelect.innerHTML = '<option>Loading...</option>';

    fetch(`get_available_times.php?date=${date}&service_id=${serviceId}`)
        .then(response => response.json())
        .then(times => {
            timeSelect.innerHTML = '<option value="">Select available time</option>';

            if (times.length === 0) {
                timeSelect.innerHTML = '<option>No available time</option>';
                return;
            }

            times.forEach(time => {
                const option = document.createElement('option');
                option.value = time;
                option.textContent = time;
                timeSelect.appendChild(option);
            });
        });
});
</script>

</body>
</html>