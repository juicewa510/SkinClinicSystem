<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: index.php");
  exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_image'])) {
  $file = $_FILES['profile_image'];
  $targetDir = "uploads/profile_pictures/";
  
  // Create folder if it doesn’t exist
  if (!is_dir($targetDir)) {
    mkdir($targetDir, 0777, true);
  }

  $fileName = uniqid() . "_" . basename($file["name"]);
  $targetFile = $targetDir . $fileName;
  $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

  $allowed = ["jpg", "jpeg", "png", "gif"];
  if (!in_array($fileType, $allowed)) {
    die("❌ Invalid file type. Only JPG, PNG, and GIF allowed.");
  }

  if (move_uploaded_file($file["tmp_name"], $targetFile)) {
    $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE user_id = ?");
    $stmt->bind_param("si", $targetFile, $user_id);
    $stmt->execute();

    // Update session picture
    $_SESSION['profile_picture'] = $targetFile;

    header("Location: profile.php?success=1");
    exit();
  } else {
    echo "⚠️ Error uploading file.";
  }
}
?>