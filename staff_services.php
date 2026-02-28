<?php
session_start();
include 'config.php';

// --- DELETE SERVICE ---
if (isset($_POST['delete_service'])) {
    $id = $_POST['service_id'];
    $conn->query("DELETE FROM services WHERE service_id = $service_id");
}

// --- UPDATE SERVICE ---
if (isset($_POST['update_service'])) {
    $service_id = $_POST['service_id'];
    $name = $_POST['name'];
    $desc = $_POST['description'];
    $price = $_POST['price'];
    $status = $_POST['status'];

    // If a new image is uploaded
    if (!empty($_FILES['image']['name'])) {
        $imgName = $_FILES['image']['name'];
        $tmp = $_FILES['image']['tmp_name'];
        move_uploaded_file($tmp, "uploads/" . $imgName);
        $conn->query("UPDATE services SET name='$name', description='$desc', price='$price', image='$imgName', status='$status' WHERE service_id=$service_id");
    } else {
        $conn->query("UPDATE services SET name='$name', description='$desc', price='$price', status='$status' WHERE service_id=$service_id");
    }
}

// --- ADD SERVICE ---
if (isset($_POST['add_service'])) {
    $service_id = $_POST['service_id'];
    $name = $_POST['name'];
    $desc = $_POST['description'];
    $price = $_POST['price'];
    $status = $_POST['status'];

    $imgName = $_FILES['image']['name'];
    $tmp = $_FILES['image']['tmp_name'];
    move_uploaded_file($tmp, "uploads/" . $imgName);

    $conn->query("INSERT INTO services (name, description, price, image, status) 
                  VALUES ('$name', '$desc', '$price', '$imgName', '$status')");
}

// Fetch all services
$result = $conn->query("SELECT * FROM services");

// Include sidebar (optional based on role)
if (isset($_SESSION['role']) && $_SESSION['role'] === 'staff') {
    include 'sidebar_staff.php';
} else {
    include 'sidebar_admin.php';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Service Management - SkinMedic</title>
    <link rel="stylesheet" href="users_style.css">

    <style>
        .content { padding: 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .date-box { text-align: right; }
        .add-service-btn {
            background-color: #80a833; color: #fff; border: none;
            padding: 10px 20px; border-radius: 8px; cursor: pointer;
            font-size: 1rem; transition: 0.2s;
        }
        .add-service-btn:hover { background-color: #829b53; }

        .service-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }

        .service-card {
            border-radius: 15px; background: #fff; box-shadow: 0 3px 8px rgba(0,0,0,0.1);
            padding: 15px; text-align: center;
        }
        .service-card img {
            width: 100%; height: 150px; object-fit: cover;
            border-radius: 12px; margin-bottom: 10px;
        }
        .service-card h3 { color: #333; margin-bottom: 5px; }
        .service-card p { font-size: 0.9rem; color: #666; margin-bottom: 10px; }
        .service-card .status { font-weight: bold; color: #829b53; }
        .service-card .status.off { color: red; }

        .action-buttons { display: flex; justify-content: center; gap: 8px; margin-top: 10px; }
        .edit-btn, .delete-btn {
            border: none; padding: 8px 12px; border-radius: 6px;
            cursor: pointer; font-size: 0.9rem; transition: background 0.2s;
        }
        .edit-btn { background-color: #80a833; color: #fff; }
        .edit-btn:hover { background-color: #829b53; }
        .delete-btn { background-color: #dc2626; color: #fff; }
        .delete-btn:hover { background-color: #b91c1c; }

        /* --- Modal --- */
        .modal {
            display: none; position: fixed; z-index: 1000;
            left: 0; top: 0; width: 100%; height: 100%;
            background-color: rgba(0,0,0,0.4);
            justify-content: center; align-items: center;
        }
        .modal-content {
            background-color: #fff; border-radius: 15px;
            padding: 30px; width: 90%; max-width: 600px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            position: relative; animation: fadeIn 0.3s ease-in-out;
        }
        .close-btn {
            position: absolute; top: 12px; right: 15px;
            font-size: 22px; color: #666; cursor: pointer; transition: 0.2s;
        }
        .close-btn:hover { color: #000; }

        @keyframes fadeIn { from {opacity: 0; transform: translateY(-10px);} to {opacity: 1; transform: translateY(0);} }
        .modal form { 
            display: flex; 
            flex-direction: column; 
            gap: 15px; }
        .modal input, .modal textarea, .modal select { 
            padding: 10px; 
            border: 1px solid #ccc; 
            border-radius: 8px; }
        .modal button { 
            background-color: #80a833; 
            color: #fff; 
            border: none; 
            border-radius: 8px; 
            padding: 12px; 
            cursor: pointer; }
        .modal button:hover { 
            background-color: #829b53; }
    </style>
</head>

<body style="background: #f8f8f8;">
    <main class="content">
        <header class="header">
            <h2>Staff Service Management</h2>
            <div class="date-box">
                <p>Today's Date</p>
                <strong><?= date("Y-m-d"); ?></strong><br>
                <button class="add-service-btn" onclick="openModal()">+ Add New Service</button>
            </div>
        </header>

        <div class="service-list">
            <?php
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $statusClass = $row['status'] == 'available' ? 'on' : 'off';
                    echo "
                    <div class='service-card'>
                        <img src='image/{$row['image']}' alt='{$row['name']}'>
                        <h3>{$row['name']}</h3>
                        <p>{$row['description']}</p>
                        <p><strong>₱{$row['price']}</strong></p>
                        <p class='status {$statusClass}'>Status: {$row['status']}</p>
                        <div class='action-buttons'>
                            <button class='edit-btn' onclick=\"openEditModal('{$row['service_id']}', '{$row['name']}', '{$row['description']}', '{$row['price']}', '{$row['status']}')\">✏ Edit</button>
                            <form method='POST' style='display:inline;' onsubmit=\"return confirm('Delete this service?');\">
                                <input type='hidden' name='service_id' value='{$row['service_id']}'>
                                <button type='submit' name='delete_service' class='delete-btn'>🗑 Delete</button>
                            </form>
                        </div>
                    </div>
                    ";
                }
            } else {
                echo "<p style='text-align:center; color:#666;'>No services added yet.</p>";
            }
            ?>
        </div>
    </main>

    <!-- ADD SERVICE MODAL -->
    <div id="addServiceModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal()">&times;</span>
            <h2>Add New Service</h2>
            <form method="POST" enctype="multipart/form-data">
                <label for="name">Service Name</label>
                <input type="text" name="name" id="name" required>

                <label for="description">Description</label>
                <textarea name="description" id="description" rows="4" required></textarea>

                <label for="price">Price</label>
                <input type="number" name="price" id="price" step="0.01" required>

                <label for="image">Upload Image</label>
                <input type="file" name="image" id="image" accept="image/*" required>

                <label for="status">Status</label>
                <select name="status" id="status">
                    <option value="available">Available</option>
                    <option value="not available">Not Available</option>
                </select>

                <button type="submit" name="add_service">Add Service</button>
            </form>
        </div>
    </div>

    <!-- EDIT SERVICE MODAL -->
    <div id="editServiceModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeEditModal()">&times;</span>
            <h2>Edit Service</h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="service_id" id="edit_id">

                <label for="edit_name">Service Name</label>
                <input type="text" name="name" id="edit_name" required>

                <label for="edit_description">Description</label>
                <textarea name="description" id="edit_description" rows="4" required></textarea>

                <label for="edit_price">Price</label>
                <input type="number" name="price" id="edit_price" step="0.01" required>

                <label for="edit_image">Change Image</label>
                <input type="file" name="image" id="edit_image" accept="image/*">

                <label for="edit_status">Status</label>
                <select name="status" id="edit_status">
                    <option value="available">Available</option>
                    <option value="not available">Not Available</option>
                </select>

                <button type="submit" name="update_service">Save Changes</button>
            </form>
        </div>
    </div>

    <script>
        // Add modal
        const modal = document.getElementById('addServiceModal');
        function openModal() { modal.style.display = 'flex'; }
        function closeModal() { modal.style.display = 'none'; }

        // Edit modal
        const editModal = document.getElementById('editServiceModal');
        function openEditModal(treatment_id, name, desc, price, status) {
            document.getElementById('edit_id').value = treatment_id;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_description').value = desc;
            document.getElementById('edit_price').value = price;
            document.getElementById('edit_status').value = status;
            editModal.style.display = 'flex';
        }
        function closeEditModal() { editModal.style.display = 'none'; }

        // Close modals when clicking outside
        window.onclick = function(event) {
            if (event.target === modal) closeModal();
            if (event.target === editModal) closeEditModal();
        };
    </script>
</body>
</html>