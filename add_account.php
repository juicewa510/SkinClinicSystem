<?php
session_start();
require_once 'config.php';

// Helper function to return JSON response for AJAX
function ajaxResponse($success, $error = '', $redirect = '') {
    header('Content-Type: application/json');
    echo json_encode(['success' => $success, 'error' => $error, 'redirect' => $redirect]);
    exit();
}

// Check if request is AJAX
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if (isset($_POST['signup'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $firstName = trim($_POST['firstname'] ?? '');
    $lastName = trim($_POST['lastname'] ?? '');

    
    // Basic validation
    if (empty($email) || empty($password) || empty($confirmPassword) || empty($firstName) || empty($lastName)) {
        $error = 'All fields are required.';
        if ($isAjax) ajaxResponse(false, $error);
        $_SESSION['signup_error'] = $error;
        header("Location: admin_add-account.php");
        exit();
    }
    if ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
        if ($isAjax) ajaxResponse(false, $error);
        $_SESSION['signup_error'] = $error;
        header("Location: admin_add-account.php");
        exit();
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
        if ($isAjax) ajaxResponse(false, $error);
        $_SESSION['signup_error'] = $error;
        header("Location: admin_add-account.php");
        exit();
    }
    
   
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Check for existing email
    $stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $error = 'Email is already registered!';
        if ($isAjax) ajaxResponse(false, $error);
        $_SESSION['signup_error'] = $error;
        header("Location: admin_add-account.php");
        exit();
    }
    
    // Insert new user with defaults for missing fields
    $gender   = $_POST['gender'] ?? 'Not specified';
    $address  = trim($_POST['address'] ?? 'Not provided');
    $phone_no = trim($_POST['phone_no'] ?? 'Not provided');
    $role     = $_POST['role'] ?? 'patient'; // <-- use the selected role from form

    $stmt = $conn->prepare("INSERT INTO users (email, firstName, lastName, password_hash, gender, address, phone_no, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", $email, $firstName, $lastName, $hashedPassword, $gender, $address, $phone_no, $role);
    if ($stmt->execute()) {
    // Redirect new users to login page instead of auto-login
        if ($isAjax) {
            ajaxResponse(true, '', 'admin_add-account.php?login=true');
        } else {
        header("Location: admin_add-account.php?login=true");
        }
    exit();
    } else {
        $error = 'Signup failed. Please try again.';
        if ($isAjax) ajaxResponse(false, $error);
        $_SESSION['signup_error'] = $error;
        header("Location: admin_add-account.php");
        exit();
    }

}