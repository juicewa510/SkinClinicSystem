<?php
include 'config.php';
$query = "SELECT name, description, price, image FROM services LIMIT 6";
$result = $conn->query($query);
$year = date("Y");
$nav = [
  'Book Appointment','AR Skin Analysis','Treatment and services',
  'Shop','My profile','Reviews','Settings'
];

session_start();
if (isset($_SESSION['email']) && isset($_SESSION['role'])) {
    switch ($_SESSION['role']) {
        case 'doctor':
            header('Location: doctor_page.php');
            exit();
        case 'staff':
            header('Location: staff_page.php');
            exit();
        case 'patient':
            header('Location: patient_page.php');
            exit();
    }
}
$showLoginPopup = isset($_GET['login']); // ?login=true triggers client popup
$showAdminPopup = isset($_GET['admin']); // ?admin=true triggers admin popup

$servicesQuery = "SELECT * FROM services ORDER BY service_id DESC LIMIT 6";
$servicesResult = $conn->query($servicesQuery);

// Fetch latest available products for Shop
$shopQuery = "SELECT * FROM products WHERE status='available' AND quantity > 0 ORDER BY date_added DESC LIMIT 6";
$shopResult = $conn->query($shopQuery);

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>Skin Medic — Home</title>

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="index.css">
</head>
<body>
  <aside class="sidebar">
    <div class="logo-wrap">
      <!-- use local asset if available -->
      <img src="skintransparent.png" alt="Skin Medic logo" onerror="this.src='https://i.ibb.co/1bZ1kRS/lotus.png'"/>
      <div class="brand">
      </div>
    </div>

    <nav class="menu">
  <a href="#book-appointment">Book Appointment</a>
  <a href="#ar-skin-analysis">AR Skin Analysis</a>
  <a href="#treatments">Treatment and Services</a>
  <a href="#shop">Shop</a>
  <a href="#reviews">Reviews</a>
  <a href="#locations">Location</a>
</nav>
  </aside>

  <main class="main-content">
    <header class="topbar">
      <div class="top-actions">
      </div>
      <a class="login-pill" href="index.php?login=true">Log in/Sign up</a>
    </header>

    <section id="book-appointment" class="hero">
      <h4 class="pre">Welcome to</h4>
      <h1 class="title">Skin Medic</h1>
      <p class="subtitle">Your journey to radiant, healthy skin begins here</p>

      <div class="hero-cta">
        <a class="cta cta-primary" href="#">Book a Session</a>
        <a class="cta cta-secondary" href="#">AR Skin Analysis</a>
      </div>
    </section>

<!-- LOGIN POPUP (Client/Patient, Doctor, and Staff  Only) -->
<div id="loginPopup" class="popup">
  <div class="popup-content">
    <div class="popup-left">
      <img src="skintransparent.png" alt="Skin Medic Logo" class="logo">
      <h2>Skin Medic</h2>
      <p>A Complete Skin Care Clinic</p>
    </div>

    <div class="popup-right">
      <span class="close" onclick="closePopup()">&times;</span>
      <h3>Login</h3>
      <div id="loginError" class="error-message" style="color: red; margin-bottom: 10px;"></div>

     <form id="loginForm">
        <div class="input-group">
          <input type="email" name="email" placeholder="Email" required>
        </div>
        <div class="input-group">
          <input type="password" name="password" placeholder="Password" required>
        </div> 

        <button type="submit" class="login-btn">Login</button>
        <a href="#" class="create-link" onclick="openSignupPopup()">Create an Account</a>
      </form>

      <button class="admin-btn" onclick="openAdminPopup()">Are you an admin?</button>
    </div>
  </div>
</div>

<!-- ADMIN LOGIN POPUP (Admin Only) -->
<div id="adminPopup" class="popup">
  <div class="popup-content">
    <div class="popup-left">
      <img src="skintransparent.png" alt="Skin Medic Logo" class="logo">
      <h2>Skin Medic</h2>
      <p>Admin Access</p>
    </div>

    <div class="popup-right">
      <span class="close" onclick="closeAdminPopup()">&times;</span>
      <h3>Admin Login</h3>
      <div id="adminError" class="error-message" style="color: red; margin-bottom: 10px;"></div>

     <form id="adminForm">
        <div class="input-group">
          <input type="email" name="email" placeholder="Email" required>
        </div>
        <div class="input-group">
          <input type="password" name="password" placeholder="Password" required>
        </div>

        <button type="submit" class="login-btn">Login as Admin</button>
      </form>

      <button class="back-btn" onclick="closeAdminPopup(); openPopup();">← Back to Client Login</button>
    </div>
  </div>
</div>

<!-- SIGNUP POPUP (Client/Patient Only) -->
<div id="signupPopup" class="popup">
  <div class="popup-content">
    <div class="popup-left">
      <img src="skintransparent.png" alt="Skin Medic Logo" class="logo">
      <h2>Skin Medic</h2>
      <p>Join Our Community</p>
    </div>

    <div class="popup-right">
      <span class="close" onclick="closeSignupPopup()">&times;</span>
      <h3>Create an Account</h3>
      <div id="signupError" class="error-message" style="color: red; margin-bottom: 10px;"></div>

     <form id="signupForm">
        <div class="input-group">
          <input type="email" name="email" placeholder="Email" required>
        </div>
        <div class="input-group">
          <input type="text" name="firstname" placeholder="Firstname" required>
        </div>
        <div class="input-group">
          <input type="text" name="lastname" placeholder="Lastname" required>
        </div>
        <div class="input-group">
          <input type="password" id="signup_password" name="password" placeholder="Password" required>
        </div>
        <div class="input-group">
          <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
        </div>
        <div class="input-group">
          <select name="gender" required>
              <option value="">Gender</option>
              <option value="male">Male</option>
              <option value="female">Female</option>
              <option value="others">Others</option>
            </select>
        </div>
        <div class="input-group">
          <input type="text" name="address" placeholder="Address" required>
        </div>
        <div class="input-group">
          <input type="text" name="phone_no" placeholder="Phone_No" required>
        </div>

        <button type="submit" class="login-btn">Sign Up</button>
      </form>

      <button class="back-btn" onclick="closeSignupPopup(); openPopup();">← Back to Login</button>
    </div>
  </div>
</div>


<script>
function closePopup() {
  document.getElementById('loginPopup').style.display = 'none';
  document.getElementById('loginError').textContent = '';
}

function openPopup() {
  document.getElementById('loginPopup').style.display = 'flex';
}

function closeAdminPopup() {
  document.getElementById('adminPopup').style.display = 'none';
  document.getElementById('adminError').textContent = '';
}

function openAdminPopup() {
  closePopup();
  document.getElementById('adminPopup').style.display = 'flex';
}

function closeSignupPopup() {
  document.getElementById('signupPopup').style.display = 'none';
  document.getElementById('signupError').textContent = '';
}

function openSignupPopup() {
  closePopup();
  document.getElementById('signupPopup').style.display = 'flex';
}

// AJAX for Client Login
document.getElementById('loginForm').addEventListener('submit', function(e) {
  e.preventDefault();
  const formData = new FormData(this);
  formData.append('login', '1');
  fetch('skinmedic.php', {
    method: 'POST',
    body: formData,
    headers: { 'X-Requested-With': 'XMLHttpRequest' }
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      closePopup();
      window.location.href = data.redirect;
    } else {
      document.getElementById('loginError').textContent = data.error;
    }
  })
  .catch(() => {
    document.getElementById('loginError').textContent = 'An error occurred. Please try again.';
  });
});

// AJAX for Admin Login
document.getElementById('adminForm').addEventListener('submit', function(e) {
  e.preventDefault();
  const formData = new FormData(this);
  formData.append('admin_login', '1');
  fetch('skinmedic.php', {
    method: 'POST',
    body: formData,
    headers: { 'X-Requested-With': 'XMLHttpRequest' }
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      closeAdminPopup();
      window.location.href = data.redirect;
    } else {
      document.getElementById('adminError').textContent = data.error;
    }
  })
  .catch(() => {
    document.getElementById('adminError').textContent = 'Only an admin can access this. Please try again.';
  });
});

// AJAX for Signup
document.getElementById('signupForm').addEventListener('submit', function(e) {
  e.preventDefault();
  const password = document.getElementById('signup_password').value;
  const confirmPassword = document.getElementById('confirm_password').value;
  if (password !== confirmPassword) {
    document.getElementById('signupError').textContent = 'Passwords do not match.';
    return;
  }
  const formData = new FormData(this);
  formData.append('signup', '1');
  formData.append('role', 'patient'); 
  fetch('skinmedic.php', {
    method: 'POST',
    body: formData,
    headers: { 'X-Requested-With': 'XMLHttpRequest' }
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      closeSignupPopup();
      window.location.href = data.redirect;
    } else {
      document.getElementById('signupError').textContent = data.error;
    }
  })
  .catch(() => {
    document.getElementById('signupError').textContent = 'An error occurred. Please try again.';
  });
});

// Auto-open popups based on URL
if (window.location.search.includes('login=true')) {
  document.getElementById('loginPopup').style.display = 'flex';
} else if (window.location.search.includes('admin=true')) {
  document.getElementById('adminPopup').style.display = 'flex';
}
</script>

    <!-- AR card -->
    <section id="ar-skin-analysis" class="ar-card">
      <div class="ar-left">
        <h2>AR Skin Analysis</h2>
        <p class="lead">
          Experience our advanced AR skin analysis technology. Get instant insights about your skin type,
          concerns, and personalized treatment recommendations.
        </p>
        <ul class="ar-list">
          <li>Instant skin type detection</li>
          <li>Identify skin concerns and issues</li>
          <li>Personalized treatment recommendations</li>
        </ul>
        <div class="ar-cta-row">
          <a class="small-pill" href="#">Start Analysis</a>
        </div>
      </div>

      <div class="ar-right">
        <div class="ar-image">
          <img src="skin-analysis.jpg" alt="Skin analysis" onerror="this.src='https://i.ibb.co/FhGvxFY/skin-analysis.jpg'"/>
        </div>
      </div>
    </section>

    <!-- Signature treatments -->
<section id="treatments" class="section treatments-section">
  <div class="section-header">
    <p class="kicker">OUR EXPERTISE</p>
    <h2 class="section-title">Signature Treatments</h2>
    <p class="section-sub">DISCOVER TRANSFORMATIVE TREATMENTS DESIGNED FOR YOUR UNIQUE SKINCARE NEEDS</p>
  </div>

  <div class="treatments-grid">
    <?php if ($servicesResult && $servicesResult->num_rows > 0): ?>
      <?php while ($row = $servicesResult->fetch_assoc()): ?>
        <article class="treatment-card">
          <div class="treatment-image">
            <img src="uploads/<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['name']) ?>" 
                 onerror="this.src='https://i.ibb.co/2s7sG4v/placeholder.png'"/>
          </div>
          <h3><?= htmlspecialchars($row['name']) ?></h3>
          <p><?= htmlspecialchars($row['description']) ?></p>
          <p class="price">₱<?= number_format($row['price'], 2) ?></p>
        </article>
      <?php endwhile; ?>
    <?php else: ?>
      <p style="text-align:center; color:#666;">No services found.</p>
    <?php endif; ?>
  </div>

  <div class="center-btn">
    <a class="view-all" href="#">View All Treatments</a>
  </div>
</section>

<!-- Shop -->
<section id="shop" class="shop-products">
  <div class="section-header">
    <p class="kicker">OUR PRODUCTS</p>
    <h2 class="section-title">PRODUCTS</h2>
    <p class="section-sub">Check out our latest skincare products available for purchase</p>
  </div>

  <div class="shop-grid">
    <?php if ($shopResult && $shopResult->num_rows > 0): ?>
      <?php while ($product = $shopResult->fetch_assoc()): ?>
        <article class="shop-card">
          <div class="shop-image">
            <img src="uploads/<?= htmlspecialchars($product['image']) ?>" 
                 alt="<?= htmlspecialchars($product['product_name']) ?>" 
                 onerror="this.src='https://i.ibb.co/2s7sG4v/placeholder.png'"/>
          </div>
          <h3><?= htmlspecialchars($product['product_name']) ?></h3>
          <p>₱<?= number_format($product['selling_price'], 2) ?></p>
        </article>
      <?php endwhile; ?>
    <?php else: ?>
      <p style="text-align:center; color:#666;">No products available.</p>
    <?php endif; ?>
  </div>
</section>