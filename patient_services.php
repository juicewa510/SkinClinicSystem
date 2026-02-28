<?php
session_start();
if (!isset($_SESSION['email'])) {
    header('Location: index.php');
    exit();
}

include 'config.php';

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
    <title>SkinMedic Services</title>
    <link rel="stylesheet" href="users_style.css">
    <style>
        .treatments-section { margin-top: 30px; }
        .treatments-container {
            display: flex; flex-wrap: wrap; gap: 20px;
        }
        .treatment-card {
            background: #fff; border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            overflow: hidden; width: 230px; cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .treatment-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.2);
        }
        .treatment-card img {
            width: 100%; height: 150px; object-fit: cover;
        }
        .treatment-card .details {
            padding: 15px; text-align: center;
        }
        .treatment-card h4 {
            margin: 10px 0 5px; font-size: 1.1rem; color: #333;
        }
        .treatment-card p {
            margin: 0; font-size: 0.9rem; color: #666;
        }

        /* --- Modal --- */
        .modal {
            display: none; position: fixed; z-index: 1000;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5); justify-content: center; align-items: center;
        }
        .modal-content {
            background: #fff; border-radius: 15px;
            padding: 25px; max-width: 600px; width: 90%;
            position: relative; animation: fadeIn 0.3s ease-in-out;
        }
        .modal-content img {
            width: 100%; border-radius: 10px; margin-bottom: 15px;
        }
        .modal-content h3 { margin-bottom: 10px; }
        .modal-content p { color: #555; }
        .modal-content .price {
            color: #80a833; font-weight: bold; margin-top: 10px;
        }
        .close-btn {
            position: absolute; top: 10px; right: 15px;
            font-size: 24px; color: #666; cursor: pointer;
        }
        .close-btn:hover { color: #000; }
        .book-btn {
            display: inline-block; margin-top: 15px;
            background-color: #80a833; color: white;
            padding: 10px 20px; border-radius: 8px;
            text-decoration: none; transition: 0.2s;
        }
        .book-btn:hover { background-color: #829b53; }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="main">
        <div class="topbar">
            <h2>Services</h2>
            <div class="date-box" id="dateBox">
                <p>Today's Date</p>
                <strong><?= date("Y-m-d"); ?></strong>
            </div>
        </div>

      

        <div class="treatments-section">
            <h3>Here are the treatments available</h3>
            <div class="treatments-container">
                <?php
                $query = "SELECT * FROM services WHERE status = 'available'";
                $result = mysqli_query($conn, $query);

                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $service_id = $row['service_id'];
                        $name = htmlspecialchars($row['name']);
                        $description = htmlspecialchars($row['description']);
                        $image = htmlspecialchars($row['image']);
                        $price = htmlspecialchars($row['price']);
                        echo "
                        <div class='treatment-card' onclick=\"openModal('$name','$description','$price','image/$image',$service_id)\">
                            <img src='image/$image' alt='$name'>
                            <div class='details'>
                                <h4>$name</h4>
                                <p>₱$price</p>
                            </div>
                        </div>
                        ";
                    }
                } else {
                    echo '<p>No services available at the moment.</p>';
                }
                ?>
            </div>
        </div>
    </div>

    <!-- MODAL -->
    <div id="serviceModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal()">&times;</span>
            <img id="modalImage" src="" alt="">
            <h3 id="modalName"></h3>
            <p id="modalDesc"></p>
            <p class="price" id="modalPrice"></p>
            <a id="bookButton" class="book-btn">Book Appointment</a>
        </div>
    </div>

    <script>
        const modal = document.getElementById('serviceModal');
        const modalName = document.getElementById('modalName');
        const modalDesc = document.getElementById('modalDesc');
        const modalPrice = document.getElementById('modalPrice');
        const modalImage = document.getElementById('modalImage');
        const bookButton = document.getElementById('bookButton');

        function openModal(name, desc, price, image, treatment_id) {
            modal.style.display = 'flex';
            modalName.textContent = name;
            modalDesc.textContent = desc;
            modalPrice.textContent = "₱" + price;
            modalImage.src = image;
            bookButton.href = 'book_appoinment.php?treatment_id=' + treatment_id;
        }

        function closeModal() {
            modal.style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target === modal) closeModal();
        }
    </script>
</body>
</html>