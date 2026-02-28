<?php
// sidebar_patient.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<aside class="sidebar">
    <div class="profile">
        <img src="<?= $_SESSION['profile_image'] ?: 'default.jpg'; ?>" alt="Profile" class="profile-img">
        <h3><?= $_SESSION['firstName'] . ' ' . $_SESSION['lastName']; ?></h3>
        <p><?= $_SESSION['email']; ?></p>
        <button class="logout-btn" onclick="window.location.href='logout.php'">Logout</button>
    </div>

    <nav class="menu">
        <a href="doctor_page.php" class="<?= basename($_SERVER['PHP_SELF']) === 'doctor_page.php' ? 'active' : '' ?>">🏠 Home</a>
        <a href="doctor_bookings.php" class="<?= basename($_SERVER['PHP_SELF']) === 'doctor_bookings.php' ? 'active' : '' ?>">🧾 My Bookings</a>
        <a href="doctor_profile.php" class="<?= basename($_SERVER['PHP_SELF']) === 'doctor_profile.php' ? 'active' : '' ?>">👤 Profile</a>
    </nav>
</aside>