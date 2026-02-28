<?php
include 'config.php';

if (isset($_POST['service_id']) && isset($_POST['available'])) {
    $id = intval($_POST['service_id']);
    $available = intval($_POST['available']);

    $sql = "UPDATE services SET available = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $available, $id);
    $stmt->execute();

    echo "success";
}
?>