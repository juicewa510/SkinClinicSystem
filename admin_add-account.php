<?php 
session_start();
require_once 'config.php';

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'staff'])) {
    header("Location: admin_add-account.php");
    exit();
}

if (isset($_POST['create_user'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $firstName = trim($_POST['firstname']);
    $lastName = trim($_POST['lastname']);
    $gender = $_POST['gender'] ?? 'Not specified';
    $address = trim($_POST['address']);
    $phone_no = trim($_POST['phone_no']);
    $role = $_POST['role'] ?? 'staff'; // default role

    // Validation
    if (empty($email) || empty($password) || empty($confirmPassword) || empty($firstName) || empty($lastName) || empty($role)) {
        $_SESSION['signup_error'] = "All fields are required.";
        header("Location: add_account.php");
        exit();
    }

    if ($password !== $confirmPassword) {
        $_SESSION['signup_error'] = "Passwords do not match.";
        header("Location: add_account.php");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['signup_error'] = "Invalid email format.";
        header("Location: add_account.php");
        exit();
    }

    // Check if email exists
    $stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $_SESSION['signup_error'] = "Email is already registered!";
        header("Location: add_account.php");
        exit();
    }

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert new user
    $stmt = $conn->prepare("INSERT INTO users (email, firstName, lastName, password_hash, gender, address, phone_no, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", $email, $firstName, $lastName, $hashedPassword, $gender, $address, $phone_no, $role);
    if ($stmt->execute()) {
        $_SESSION['success'] = "User account created successfully!";
        header("Location: add_account.php");
        exit();
    } else {
        $_SESSION['signup_error'] = "Failed to create account.";
        header("Location: add_account.php");
        exit();
    }
}


// Include sidebar based on role if logged in
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        include 'sidebar_admin.php';
    } elseif ($_SESSION['role'] === 'staff') {
        include 'sidebar_staff.php';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account — Skin Medic</title>
    <link rel="stylesheet" href="users_style.css">
    <style>
        .content { 
            padding: 20px; 
        }
        .header { 
            display: flex;
            justify-content: space-between;
            align-items: center; 
            margin-bottom: 30px; 
        }
        .date-box { 
            text-align: right; 
        }
        .input-group {
            margin-bottom: 15px;
        }
        .input-group input, .input-group select {
            width: 1000px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .create-btn {
            background-color: #80a833; 
            color: #fff; 
            border: none;
            padding: 10px 20px; 
            border-radius: 8px; 
            cursor: pointer;
            font-size: 1rem; 
            transition: 0.2s;
        }
        .create-btn:hover {
            background-color: #80a833;
        }
    </style>
</head>
<body>
    <main class="content">
    <header class="header">
        <h2>Create an Account</h2>
        <div class="date-box">
            <p>Today's Date</p>
            <strong><?= date("Y-m-d"); ?></strong><br>
        </div>
    </header>
        <section class="signup-form">
            

            <?php if (!empty($_SESSION['signup_error'])): ?>
                <div class="error-message" style="color:red;">
                    <?= htmlspecialchars($_SESSION['signup_error']); ?>
                    <?php unset($_SESSION['signup_error']); ?>
                </div>
            <?php endif; ?>

            <form action="add_account.php" method="POST">
                <div class="input-group">
                    <input type="email" name="email" placeholder="Email" required
                        value="<?= isset($_SESSION['old']['email']) ? htmlspecialchars($_SESSION['old']['email']) : '' ?>">
                </div>
                <div class="input-group">
                    <input type="text" name="firstname" placeholder="Firstname" required
                        value="<?= isset($_SESSION['old']['firstname']) ? htmlspecialchars($_SESSION['old']['firstname']) : '' ?>">
                </div>
                <div class="input-group">
                    <input type="text" name="lastname" placeholder="Lastname" required
                        value="<?= isset($_SESSION['old']['lastname']) ? htmlspecialchars($_SESSION['old']['lastname']) : '' ?>">
                </div>
                <div class="input-group">
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                <div class="input-group">
                    <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                </div>
                <div class="input-group">
                    <select name="gender" required>
                        <option value="">Gender</option>
                        <option value="male" <?= (isset($_SESSION['old']['gender']) && $_SESSION['old']['gender'] === 'male') ? 'selected' : '' ?>>Male</option>
                        <option value="female" <?= (isset($_SESSION['old']['gender']) && $_SESSION['old']['gender'] === 'female') ? 'selected' : '' ?>>Female</option>
                        <option value="others" <?= (isset($_SESSION['old']['gender']) && $_SESSION['old']['gender'] === 'others') ? 'selected' : '' ?>>Others</option>
                    </select>
                </div>
                <div class="input-group">
                    <input type="text" name="address" placeholder="Address" required
                        value="<?= isset($_SESSION['old']['address']) ? htmlspecialchars($_SESSION['old']['address']) : '' ?>">
                </div>
                <div class="input-group">
                    <input type="text" name="phone_no" placeholder="Phone Number" required
                        value="<?= isset($_SESSION['old']['phone_no']) ? htmlspecialchars($_SESSION['old']['phone_no']) : '' ?>">
                </div>
                <div class="input-group">
                    <select name="role" required>
                        <option value="">Select Role</option>
                        <option value="admin" <?= (isset($_SESSION['old']['role']) && $_SESSION['old']['role'] === 'admin') ? 'selected' : '' ?>>Admin</option>
                        <option value="staff" <?= (isset($_SESSION['old']['role']) && $_SESSION['old']['role'] === 'staff') ? 'selected' : '' ?>>Staff</option>
                        <option value="doctor" <?= (isset($_SESSION['old']['role']) && $_SESSION['old']['role'] === 'doctor') ? 'selected' : '' ?>>Doctor</option>
                    </select>
                </div>

                <button type="submit" name="signup" class="create-btn">Create Account</button>
            </form>
        </section>
    </main>
</body>
</html>

<?php
unset($_SESSION['old']); // clear old input
?>
