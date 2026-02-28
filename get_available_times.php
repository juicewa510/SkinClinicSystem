<?php
include 'config.php';

if (!isset($_GET['date']) || !isset($_GET['service_id'])) {
    echo json_encode([]);
    exit;
}

$date = $_GET['date'];
$service_id = intval($_GET['service_id']);


$allTimes = [
    '08:00', '09:00', '10:00', '11:00',
    '12:00', '13:00', '14:00', '15:00',
    '16:00', '17:00', '18:00', '19:00'
];


$stmt = $conn->prepare("
    SELECT DATE_FORMAT(appointment_time, '%H:%i') AS appointment_time
    FROM appointments
    WHERE appointment_date = ? AND service_id = ?
");
$stmt->bind_param("si", $date, $service_id);
$stmt->execute();
$result = $stmt->get_result();

$bookedTimes = [];
while ($row = $result->fetch_assoc()) {
    $bookedTimes[] = $row['appointment_time'];
}

// Remove booked slots
$availableTimes = array_values(array_diff($allTimes, $bookedTimes));

// Return clean JSON
header('Content-Type: application/json');
echo json_encode($availableTimes);
