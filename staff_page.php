<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') {
    header('Location: index.php?login=true');
    exit();
}

switch ($_SESSION['role']) {
    case 'doctor':
        include 'sidebar_doctor.php';
        break;
    case 'staff':
        include 'sidebar_staff.php';
        break;
    default:
        include 'sidebar_patient.php';
        break;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SkinMedic</title>
    <link rel="stylesheet" href="users_style.css">
</head>
<body style="background: #fff;">

    <!-- Main Content -->
    <main class="content">
        <header class="header">
            <div class="search-box">
                <input type="text" placeholder="Search Doctor name or Email">
                <button>Search</button>
            </div>
            <div class="date-box">
                <p>Today's Date</p>
                <strong id="dateBox"><?= date("Y-m-d"); ?></strong>
            </div>
        </header>

        <section class="status">
            <div class="card">
                <h2>0</h2>
                <p>New Booking</p>
            </div>
            <div class="card">
                <h2>0</h2>
                <p>Today Sessions</p>
            </div>
        </section>

        <section class="upcoming-container">
            <div class="upcoming-box">
                <h3>Upcoming Appointments until Next Friday</h3>
                <p>Here’s quick access to upcoming appointments within 7 days. More details available in the Appointment section.</p>
                <div class="table-placeholder">
                    <p>No appointment data available.</p>
                </div>
                <button class="show-btn">Show all Appointments</button>
            </div>

            <div class="upcoming-box">
                <h3>Upcoming Sessions until Next Friday</h3>
                <p>Here’s quick access to upcoming sessions scheduled within 7 days. More details in the Schedule section.</p>
                <div class="table-placeholder">
                    <p>No session data available.</p>
                </div>
                <button class="show-btn">Show all Sessions</button>
            </div>
        </section>
    </main>

</div>

<script src="script.js"></script>
</body>
</html>