<?php
session_start();
include 'config.php';

// --- DELETE PRODUCT ---
if (isset($_POST['delete_product'])) {
    $product_id = $_POST['product_id'];
    $conn->query("DELETE FROM products WHERE product_id = $product_id");
}

// --- UPDATE PRODUCT ---
if (isset($_POST['update_product'])) {
    $product_id = $_POST['product_id'];
    $name = $_POST['product_name'];
    $desc = $_POST['description'];
    $category = $_POST['category'];
    $brand = $_POST['brand'];
    $supplier = $_POST['supplier'];
    $batch_number = $_POST['batch_number'];
    $quantity = $_POST['quantity'];
    $reorder_level = $_POST['reorder_level'];
    $cost_price = $_POST['cost_price'];
    $selling_price = $_POST['selling_price'];
    $expiry_date = $_POST['expiry_date'];
    $storage_location = $_POST['storage_location'];
    $status = $_POST['status'];


    // If a new image is uploaded
    if (!empty($_FILES['image']['name'])) {
        $imgName = $_FILES['image']['name'];
        $tmp = $_FILES['image']['tmp_name'];
        move_uploaded_file($tmp, "uploads/" . $imgName);

        $conn->query("UPDATE products 
            SET product_name='$name', description='$desc', category='$category', brand='$brand',
                supplier='$supplier', batch_number='$batch_number', quantity='$quantity',
                reorder_level='$reorder_level', cost_price='$cost_price', selling_price='$selling_price',
                expiry_date='$expiry_date', storage_location='$storage_location', status='$status',
                image='$imgName'
            WHERE product_id=$product_id");
    } else {
        $conn->query("UPDATE products 
            SET product_name='$name', description='$desc', category='$category', brand='$brand',
                supplier='$supplier', batch_number='$batch_number', quantity='$quantity',
                reorder_level='$reorder_level', cost_price='$cost_price', selling_price='$selling_price',
                expiry_date='$expiry_date', storage_location='$storage_location', status='$status'
            WHERE product_id=$product_id");
    }
}

// --- ADD PRODUCT ---
if (isset($_POST['add_product'])) {
    $name = $_POST['product_name'];
    $desc = $_POST['description'];
    $category = $_POST['category'];
    $brand = $_POST['brand'];
    $supplier = $_POST['supplier'];
    $batch_number = $_POST['batch_number'];
    $quantity = $_POST['quantity'];
    $reorder_level = $_POST['reorder_level'];
    $cost_price = $_POST['cost_price'];
    $selling_price = $_POST['selling_price'];
    $expiry_date = $_POST['expiry_date'];
    $storage_location = $_POST['storage_location'];
    $status = $_POST['status'];

    $imgName = $_FILES['image']['name'];
    $tmp = $_FILES['image']['tmp_name'];
    move_uploaded_file($tmp, "uploads/" . $imgName);

    $conn->query("INSERT INTO products 
        (product_name, description, category, brand, supplier, batch_number, quantity, reorder_level,
        cost_price, selling_price, expiry_date, storage_location, status, date_added, image)
        VALUES ('$name', '$desc', '$category', '$brand', '$supplier', '$batch_number', '$quantity',
        '$reorder_level', '$cost_price', '$selling_price', '$expiry_date', '$storage_location',
        '$status', NOW(), '$imgName')");
}

// Fetch all products
$result = $conn->query("SELECT * FROM products");

// Include sidebar based on role
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
    <title>Product Management - SkinMedic</title>
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
        .add-product-btn {
            background-color: #80a833; 
            color: #fff; 
            border: none;
            padding: 10px 20px; 
            border-radius: 8px; 
            cursor: pointer;
            font-size: 1rem; 
            transition: 0.2s;
        }
        .add-product-btn:hover { 
            background-color: #829b53; 
        }

        .product-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }

        .product-card {
            border-radius: 15px; 
            background: #fff; 
            box-shadow: 0 3px 8px rgba(0,0,0,0.1);
            padding: 15px; 
            text-align: center;
        }
        .product-card img {
            width: 100%; 
            height: 150px; 
            object-fit: cover;
            border-radius: 12px; 
            margin-bottom: 10px;
        }
        .product-card h3 { 
            color: #333; 
            margin-bottom: 5px; 
        }
        .product-card p { 
            font-size: 0.9rem; 
            color: #666; 
            margin-bottom: 10px; 
        }
        .product-card .status { 
            font-weight: bold; 
            color: #829b53; 
        }
        .product-card .status.off { 
            color: red; 
        }

        .action-buttons { 
            display: flex; 
            justify-content: center; 
            gap: 8px; 
            margin-top: 10px; 
        }
        .edit-btn, .delete-btn {
            border: none; 
            padding: 8px 12px; 
            border-radius: 6px;
            cursor: pointer; 
            font-size: 0.9rem; 
            transition: background 0.2s;
        }
        .edit-btn { 
            background-color: #80a833; 
            color: #fff; 
        }
        .edit-btn:hover { 
            background-color: #829b53; 
        }
        .delete-btn { 
            background-color: #dc2626; 
            color: #fff; 
        }
        .delete-btn:hover { 
            background-color: #b91c1c; 
        }

        /* --- Modal --- */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.4);
            justify-content: center;
            align-items: center;
            }

        .modal-content {
            background-color: #fff;
            border-radius: 15px;
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
            overflow: hidden; /* keeps rounded corners */
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            animation: fadeIn 0.3s ease-in-out;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 25px;
            border-bottom: 1px solid #eee;
            background: #f9f9f9;
        }

        .modal-header h2 {
            margin: 0;
        }

        .close-btn {
            font-size: 22px;
            color: #666;
            cursor: pointer;
            transition: 0.2s;
        }
        .close-btn:hover { color: #000; }

        .modal-body {
            padding: 20px 25px;
            overflow-y: auto; /* scroll inside the body */
            flex-grow: 1;
        }

        .modal-body form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .modal-body input, .modal-body textarea, .modal-body select {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }

        .modal-body button {
            background-color: #80a833;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 12px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .modal-body button:hover {
            background-color: #829b53;
        }

        @keyframes fadeIn {
            from {opacity: 0; transform: translateY(-10px);}
            to {opacity: 1; transform: translateY(0);}
        }

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
        <h2>Product Management</h2>
        <div class="date-box">
            <p>Today's Date</p>
            <strong><?= date("Y-m-d"); ?></strong><br>
            <button class="add-product-btn" onclick="openModal()">+ Add Product</button>
        </div>
    </header>

    <div class="product-list">
        <?php
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $statusClass = $row['status'] === 'available' ? 'on' : 'off';
                echo "
                <div class='product-card'>
                    <img src='uploads/{$row['image']}' alt='{$row['product_name']}'>
                    <h3>{$row['product_name']}</h3>
                    <p>{$row['description']}</p>
                    <p><strong>₱{$row['selling_price']}</strong></p>
                    <p class='status {$statusClass}'>Status: {$row['status']}</p>
                    <div class='action-buttons'>
                        <button class='edit-btn' onclick=\"openEditModal('{$row['product_id']}', '{$row['product_name']}', '{$row['description']}', '{$row['selling_price']}', '{$row['status']}')\">✏ Edit</button>
                        <form method='POST' style='display:inline;' onsubmit=\"return confirm('Delete this product?');\">
                            <input type='hidden' name='product_id' value='{$row['product_id']}'>
                            <button type='submit' name='delete_product' class='delete-btn'>🗑 Delete</button>
                        </form>
                    </div>
                </div>
                ";
            }
        } else {
            echo "<p style='text-align:center; color:#666;'>No products added yet.</p>";
        }
        ?>
    </div>
</main>

<!-- ADD PRODUCT MODAL -->
<div id="addProductModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h2>Add Product</h2>
      <span class="close-btn" onclick="closeModal()">&times;</span>
    </div>
    <div class="modal-body">
      <form method="POST" enctype="multipart/form-data">
        <label for="name">Product Name</label>
        <input type="text" name="product_name" id="name" required>

        <label for="description">Description</label>
        <textarea name="description" id="description" rows="3" required></textarea>

        <label for="category">Category</label>
        <input type="text" name="category" id="category">

        <label for="brand">Brand</label>
        <input type="text" name="brand" id="brand">

        <label for="supplier">Supplier</label>
        <input type="text" name="supplier" id="supplier">

        <label for="batch">Batch Number</label>
        <input type="text" name="batch_number" id="batch">

        <label for="quantity">Quantity</label>
        <input type="number" name="quantity" id="quantity">

        <label for="reorder">Reorder Level</label>
        <input type="number" name="reorder_level" id="reorder">

        <label for="cost">Cost Price</label>
        <input type="number" step="0.01" name="cost_price" id="cost">

        <label for="selling">Selling Price</label>
        <input type="number" step="0.01" name="selling_price" id="selling">

        <label for="expiry">Expiry Date</label>
        <input type="date" name="expiry_date" id="expiry">

        <label for="location">Storage Location</label>
        <input type="text" name="storage_location" id="location">

        <label for="status">Status</label>
        <select name="status" id="status">
          <option value="available">Available</option>
          <option value="not available">Not Available</option>
        </select>

        <label for="image">Change Image</label>
        <input type="file" name="image" id="image" accept="image/*">
        <button type="submit" name="add_product">Add Product</button>
      </form>
    </div>
  </div>
</div>

<!-- EDIT PRODUCT MODAL -->
<div id="editProductModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h2>Edit Product</h2>
      <span class="close-btn" onclick="closeEditModal()">&times;</span>
    </div>
    <div class="modal-body">
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="product_id" id="edit_id">

        <label for="edit_name">Product Name</label>
        <input type="text" name="product_name" id="edit_name" required>

        <label for="edit_description">Description</label>
        <textarea name="description" id="edit_description" rows="3" required></textarea>

        <label for="edit_category">Category</label>
        <input type="text" name="category" id="edit_category">

        <label for="edit_brand">Brand</label>
        <input type="text" name="brand" id="edit_brand">

        <label for="edit_supplier">Supplier</label>
        <input type="text" name="supplier" id="edit_supplier">

        <label for="edit_batch">Batch Number</label>
        <input type="text" name="batch_number" id="edit_batch">

        <label for="edit_quantity">Quantity</label>
        <input type="number" name="quantity" id="edit_quantity">

        <label for="edit_reorder">Reorder Level</label>
        <input type="number" name="reorder_level" id="edit_reorder">

        <label for="edit_cost">Cost Price</label>
        <input type="number" step="0.01" name="cost_price" id="edit_cost">

        <label for="edit_selling">Selling Price</label>
        <input type="number" step="0.01" name="selling_price" id="edit_selling">

        <label for="edit_expiry">Expiry Date</label>
        <input type="date" name="expiry_date" id="edit_expiry">

        <label for="edit_location">Storage Location</label>
        <input type="text" name="storage_location" id="edit_location">

        <label for="edit_status">Status</label>
        <select name="status" id="edit_status">
          <option value="available">Available</option>
          <option value="not available">Not Available</option>
        </select>

        <label for="edit_image">Change Image</label>
        <input type="file" name="image" id="edit_image" accept="image/*">

        <button type="submit" name="update_product">Save Changes</button>
      </form>
    </div>
  </div>
</div>


<script>
  // --- Add Product Modal ---
  const addModal = document.getElementById('addProductModal');
  function openModal() { addModal.style.display = 'flex'; }
  function closeModal() { addModal.style.display = 'none'; }

  // --- Edit Product Modal ---
  const editModal = document.getElementById('editProductModal');
  function openEditModal(
      id, name, desc, category, brand, supplier, batch, quantity,
      reorder, cost, selling, expiry, location, status
  ) {
      document.getElementById('edit_id').value = id;
      document.getElementById('edit_name').value = name;
      document.getElementById('edit_description').value = desc;
      document.getElementById('edit_category').value = category;
      document.getElementById('edit_brand').value = brand;
      document.getElementById('edit_supplier').value = supplier;
      document.getElementById('edit_batch').value = batch;
      document.getElementById('edit_quantity').value = quantity;
      document.getElementById('edit_reorder').value = reorder;
      document.getElementById('edit_cost').value = cost;
      document.getElementById('edit_selling').value = selling;
      document.getElementById('edit_expiry').value = expiry;
      document.getElementById('edit_location').value = location;
      document.getElementById('edit_status').value = status;

      editModal.style.display = 'flex';
  }
  function closeEditModal() { editModal.style.display = 'none'; }

  // --- Close modals when clicking outside ---
  window.onclick = function(event) {
      if (event.target === addModal) closeModal();
      if (event.target === editModal) closeEditModal();
  };
</script>


</body>
</html>
