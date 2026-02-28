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

// ✅ Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $firstName = trim($_POST['firstName']);
    $lastName = trim($_POST['lastName']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $gender = trim($_POST['gender']);
    $birthdate = $_POST['birthdate'] ?: null;
    $address = trim($_POST['address']);

    $stmt = $conn->prepare("
        UPDATE users 
        SET firstName=?, lastName=?, email=?, phone=?, gender=?, birthdate=?, address=? 
        WHERE user_id=?
    ");
    $stmt->bind_param("sssssssi", $firstName, $lastName, $email, $phone, $gender, $birthdate, $address, $user_id);

    if ($stmt->execute()) {
        $successMsg = "✅ Profile updated successfully!";
    } else {
        $errorMsg = "⚠️ Failed to update profile. Please try again.";
    }
}

// ✅ Fetch user info
$userQuery = $conn->prepare("SELECT firstName, lastName, email, phone, birthdate, gender, address FROM users WHERE user_id = ?");
$userQuery->bind_param("i", $user_id);
$userQuery->execute();
$user = $userQuery->get_result()->fetch_assoc();

// ✅ Fetch appointment history
$apptQuery = $conn->prepare("
    SELECT a.appointment_id, s.name AS service_name, a.appointment_date, a.appointment_time, a.status 
    FROM appointments a 
    JOIN services s ON a.service_id = s.service_id 
    WHERE a.user_id = ?
    ORDER BY a.appointment_date DESC
");
$apptQuery->bind_param("i", $user_id);
$apptQuery->execute();
$appointments = $apptQuery->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile | SkinMedic</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f3e8df;
            margin: 0;
            padding: 0;
            color: #2f2a27;
        }

        .profile-container {
            max-width: 1000px;
            margin: 60px auto;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            padding: 40px;
        }

        .profile-header {
            display: flex;
            align-items: center;
            gap: 25px;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 20px;
        }

        .profile-header img {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #80a833;
        }

        .profile-header h2 {
            margin: 0;
            font-size: 28px;
            color: #3a3a3a;
        }

        .profile-header p {
            margin: 5px 0;
            color: #666;
        }

        .info-section {
            margin-top: 30px;
        }

        .info-section h3 {
            color: #80a833;
            margin-bottom: 10px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
        }

        .info-box {
            background: #fafafa;
            border: 1px solid #eee;
            border-radius: 12px;
            padding: 15px;
        }

        label {
            font-weight: 500;
            font-size: 14px;
            color: #555;
        }

        input, select, textarea {
            width: 100%;
            margin-top: 5px;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 14px;
            background: #fff;
        }

        input:disabled, select:disabled, textarea:disabled {
            background: #f9f9f9;
            color: #555;
        }

        .buttons {
            margin-top: 20px;
            text-align: right;
        }

        .edit-btn, .save-btn {
            background: #80a833;
            color: #fff;
            border: none;
            padding: 10px 18px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 15px;
        }

        .edit-btn:hover, .save-btn:hover {
            background: #6f8e2d;
        }

        .appt-section {
            margin-top: 40px;
        }

        .appt-section table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
        }

        .appt-section th, .appt-section td {
            text-align: left;
            padding: 12px;
            border-bottom: 1px solid #eee;
        }

        .appt-section th {
            background: #80a833;
            color: #fff;
        }

        .status {
            padding: 5px 10px;
            border-radius: 8px;
            font-size: 14px;
        }

        .status.confirmed { background: #c6f6c6; color: #2e7d32; }
        .status.cancelled { background: #f6c6c6; color: #a32a2a; }
        .status.completed { background: #c6e2f6; color: #2a5aa3; }

        .msg {
            text-align: center;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .success { color: #4c8c2b; }
        .error { color: #b71c1c; }
    </style>
</head>
<body>
    <div class="profile-container">
        <div class="profile-header">
            <img src="https://cdn-icons-png.flaticon.com/512/847/847969.png" alt="Profile Picture">
            <div>
                <h2><?= htmlspecialchars($user['firstName'] . ' ' . $user['lastName']) ?></h2>
                <p><?= htmlspecialchars($user['email']) ?></p>
            </div>
        </div>

        <?php if (isset($successMsg)): ?>
            <div class="msg success"><?= $successMsg ?></div>
        <?php elseif (isset($errorMsg)): ?>
            <div class="msg error"><?= $errorMsg ?></div>
        <?php endif; ?>

        <div class="info-section">
            <h3>Personal Information</h3>

            <form method="POST" id="profileForm">
                <div class="info-grid">
                    <div class="info-box">
                        <label>First Name</label>
                        <input type="text" name="firstName" value="<?= htmlspecialchars($user['firstName']) ?>" disabled required>
                    </div>
                    <div class="info-box">
                        <label>Last Name</label>
                        <input type="text" name="lastName" value="<?= htmlspecialchars($user['lastName']) ?>" disabled required>
                    </div>
                    <div class="info-box">
                        <label>Email</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" disabled required>
                    </div>
                    <div class="info-box">
                        <label>Phone</label>
                        <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" disabled>
                    </div>
                    <div class="info-box">
                        <label>Gender</label>
                        <select name="gender" disabled>
                            <option value="">Select Gender</option>
                            <option value="Male" <?= $user['gender'] === 'Male' ? 'selected' : '' ?>>Male</option>
                            <option value="Female" <?= $user['gender'] === 'Female' ? 'selected' : '' ?>>Female</option>
                            <option value="Other" <?= $user['gender'] === 'Other' ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>
                    <div class="info-box">
                        <label>Birthdate</label>
                        <input type="date" name="birthdate" value="<?= htmlspecialchars($user['birthdate']) ?>" disabled>
                    </div>
                    <div class="info-box" style="grid-column: span 2;">
                        <label>Address</label>
                        <textarea name="address" rows="2" disabled><?= htmlspecialchars($user['address']) ?></textarea>
                    </div>
                </div>

                <div class="buttons">
                    <button type="button" class="edit-btn" id="editBtn">✏️ Edit Profile</button>
                    <button type="submit" name="update_profile" class="save-btn" id="saveBtn" style="display:none;">💾 Save Changes</button>
                </div>
            </form>
        </div>

        <div class="appt-section">
            <h3>Appointment History</h3>
            <table>
                <tr>
                    <th>Service</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Status</th>
                </tr>
                <?php while ($appt = $appointments->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($appt['service_name']) ?></td>
                        <td><?= htmlspecialchars($appt['appointment_date']) ?></td>
                        <td><?= htmlspecialchars($appt['appointment_time']) ?></td>
                        <td>
                            <span class="status <?= strtolower($appt['status']) ?>">
                                <?= ucfirst($appt['status']) ?>
                            </span>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>

    <script>
        const editBtn = document.getElementById('editBtn');
        const saveBtn = document.getElementById('saveBtn');
        const inputs = document.querySelectorAll('input, select, textarea');

        editBtn.addEventListener('click', () => {
            inputs.forEach(el => el.disabled = false);
            editBtn.style.display = 'none';
            saveBtn.style.display = 'inline-block';
        });
    </script>
</body>
</html>