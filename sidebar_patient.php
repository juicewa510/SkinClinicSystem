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
        <a href="patient_page.php" class="<?= basename($_SERVER['PHP_SELF']) === 'patient_page.php' ? 'active' : '' ?>">🏠 Home</a>
        <a href="patient_services.php" class="<?= basename($_SERVER['PHP_SELF']) === 'patient_services.php' ? 'active' : '' ?>">💆 Services</a>
        <a href="patient_store.php" class="<?= basename($_SERVER['PHP_SELF']) === 'patient_store.php' ? 'active' : '' ?>">🛍️ Store</a>
        <a href="patient_bookings.php" class="<?= basename($_SERVER['PHP_SELF']) === 'patient_bookings.php' ? 'active' : '' ?>">🧾 My Bookings</a>
        <a href="patient_profile.php" class="<?= basename($_SERVER['PHP_SELF']) === 'patient_profile.php' ? 'active' : '' ?>">👤 Profile</a>
    </nav>
</aside>
</div>