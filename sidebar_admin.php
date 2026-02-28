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
        <a href="admin_page.php" class="<?= basename($_SERVER['PHP_SELF']) === 'admin_page.php' ? 'active' : '' ?>">🏠 Home</a>
        <a href="admin_products.php" class="<?= basename($_SERVER['PHP_SELF']) === 'admin_products.php' ? 'active' : '' ?>">🧴 Products</a>
        <a href="admin_services.php" class="<?= basename($_SERVER['PHP_SELF']) === 'admin_services.php' ? 'active' : '' ?>">💆 Services</a>
        <a href="admin_bookings.php" class="<?= basename($_SERVER['PHP_SELF']) === 'admin_bookings.php' ? 'active' : '' ?>">🧾 Bookings</a>
        <a href="admin_add-account.php" class="<?= basename($_SERVER['PHP_SELF']) === 'admin_add-account.php' ? 'active' : '' ?>">👤 Add Account </a>
    </nav>
</aside>