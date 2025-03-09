<?php
session_start();

$servername = "localhost";
$dbname = "db_sdshoppe";
$username = "root";  
$password = "";  

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: haveacc.php"); // Redirect to login if not logged in
    exit;
}

// Retrieve the user ID from the session
$user_id = $_SESSION['user_id'];

// Get the product details in the shopping cart for the logged-in user
$stmt = $pdo->prepare('SELECT cart_id, product, color, unit_price, quantity, total_price FROM shopping_cart WHERE customer_id = :user_id');
$stmt->execute(['user_id' => $user_id]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmtSubtotal = $pdo->prepare('SELECT SUM(total_price) AS subtotal FROM shopping_cart WHERE customer_id = :user_id');
$stmtSubtotal->execute(['user_id' => $user_id]);
$result = $stmtSubtotal->fetch(PDO::FETCH_ASSOC);
$subtotal = $result['subtotal'] ?? 0;

if (isset($_POST['remove_single'])) {
    $cart_id = $_POST['remove_single'];

    $query = "DELETE FROM shopping_cart WHERE cart_id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$cart_id]);

    header("Location: cart.php");
    exit;
}

// Get the product details in the bulk shopping cart for the logged-in user
$stmt = $pdo->prepare('SELECT bulk_cart_id, product_id, product, unit_price, roll_price, rolls, yards, color, item_subtotal FROM bulk_shopping_cart WHERE customer_id = :user_id');
$stmt->execute(['user_id' => $user_id]);
$bulk_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmtBulkSubtotal = $pdo->prepare('SELECT SUM(item_subtotal) AS bulksubtotal FROM bulk_shopping_cart WHERE customer_id = :user_id');
$stmtBulkSubtotal->execute(['user_id' => $user_id]);
$result = $stmtBulkSubtotal->fetch(PDO::FETCH_ASSOC);
$bulkGrandTotal = $result['bulksubtotal'] ?? 0;

if (isset($_POST['remove_single2'])) {
    $bulk_cart_id = $_POST['remove_single2'];

    $query = "DELETE FROM bulk_shopping_cart WHERE bulk_cart_id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$bulk_cart_id]);

    header("Location: cart.php");
    exit;
}

// Fetch unread notifications
$query_notifications = "SELECT notif_id, message, created_at FROM notifications WHERE id = ? AND is_read = 0 ORDER BY created_at DESC";
$stmt_notifications = $pdo->prepare($query_notifications);
$stmt_notifications->bindValue(1, $user_id, PDO::PARAM_INT);
$stmt_notifications->execute();
$result_notifications = $stmt_notifications->fetchAll(PDO::FETCH_ASSOC);

if (isset($_GET['notif_id']) && is_numeric($_GET['notif_id'])) {
    $notif_id = intval($_GET['notif_id']);

    $query_check_notif = "SELECT notif_id FROM notifications WHERE notif_id = ? AND id = ?";
    $stmt_check_notif = $pdo->prepare($query_check_notif);
    $stmt_check_notif->execute([$notif_id, $user_id]);

    if ($stmt_check_notif->rowCount() > 0) {
        // Mark the notification as read
        $query_update_read = "UPDATE notifications SET is_read = 1 WHERE notif_id = ?";
        $stmt_update_read = $pdo->prepare($query_update_read);
        $stmt_update_read->execute([$notif_id]);

        echo json_encode(['status' => 'success', 'message' => 'Notification marked as read']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Notification not found or does not belong to this user']);
    }
}

// Query to count unread notifications
$query_count_unread = "SELECT COUNT(*) FROM notifications WHERE id = ? AND is_read = 0";
$stmt_count_unread = $pdo->prepare($query_count_unread);
$stmt_count_unread->bindValue(1, $user_id, PDO::PARAM_INT);
$stmt_count_unread->execute();
$unread_count = $stmt_count_unread->fetchColumn();


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link rel="icon" href="\SnD_Shoppe-main\PIC\sndlogo.png" type="logo">
    <title>Shopping Cart</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Kumbh+Sans:wght@100..900&family=Playfair+Display+SC:ital,wght@0,400;0,700;0,900;1,400;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900&display=swap');

body {
    background: url(Assets/bgLogin.png) rgba(0, 0, 0, 0.3);
    background-blend-mode: multiply;
    background-position: center;
    background-repeat: repeat;
    background-size: cover;
    min-height: 100vh; 
    overflow-y: auto; 
    margin: 0; 
    padding: 150px 0 0; /* Add top padding for fixed header */
    font-family: "Playfair Display", serif;
}

.navbar {
    position: fixed; 
    top: 0; 
    left: 0; 
    right: 0; 
    z-index: 1000; 
    background-color: #f1e8d9; 
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15); 
}

.nav-link-black {
    color: #1e1e1e !important;
}

.nav-link-black:hover {
    color: #e044a5;
}


/* Hamburger icon color */
.navbar-toggler-icon {
    background-image: url("data:image/svg+xml;charset=utf8,%3Csvg viewBox='0 0 30 30' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath stroke='rgba(30, 30, 30, 1)' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3E%3C/svg%3E");
}

.search-bar {
    max-width: 300px; 
    width: 100%; 
}

.input-group-text {
    background-color: #f1e8d9; 
    border: 1px solid #d9b65d; 
    border-radius: 20px 0 0 20px; 
}

.form-control {
    border: 1px solid #d9b65d; 
    border-radius: 0 20px 20px 0; 
    text-align: center; 
}

h1 {
    font-family: "Playfair Display SC", serif;
    font-size: 40px;
    color: #1e1e1e;
}

.header-container {
    position: fixed; 
    top: 60px; 
    left: 0;
    right: 0;
    z-index: 999; 
    background-color: #b6b3ae; 
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15); 
    border-radius: 18px;
}

.card {
    background-color: transparent; 
    margin: 0; 
    text-align: center; 
    border-radius: 17px;
}

.table-bordered {
    border-radius: 5px; 
    overflow: hidden; 
    background-color: #f1e8d9 !important;
}

.bg-light {
    background-color: #f1e8d9 !important;
}

.custom-padding {
    padding-top: 30px; 
}

/* Account Dropdown Styling */
.navbar .dropdown-menu {
    border-radius: 8px;
    padding: 0;
    min-width: 150px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
}

/* Account Dropdown Styling */
.navbar .dropdown-menu {
    border-radius: 11px; 
    padding: 0;
    min-width: 150px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
    overflow: hidden; 
}

/* Dropdown Item Styling */
.navbar .dropdown-item {
    padding: 10px 16px;
    font-size: 14px;
    color: #1e1e1e;
    transition: background-color 0.3s;
}

/* Hover Effect with Matching Border Radius */
.navbar .dropdown-item:hover {
    background-color: #f1e8d9;
    border-radius: 0;
}

/* Logout Text */
.dropdown-item.text-danger {
    color: #dc3545;
    font-weight: bold;
}

/* Dropdown Divider */
.dropdown-divider {
    margin: 0;
}

.cart-items .cart-item {
    background-color: #f1e8d9;
    border-radius: 10px;
    margin-bottom: 15px;
    padding: 15px;
}

.quantity-select {
    border-radius: 10px;
    border: 1px solid #d1b894;
    text-align: center;
    width: 50px;
}

.item-total-price {
    color: #a70000;
    font-weight: bold;
    font-size: 20px;
}

.total-price {
    font-size: 22px;
    font-weight: bold;
    color: #a70000;
}

.btn-primary,
.btn-secondary {
    font-weight: bold;
    border-radius: 20px;
}

.btn-outline-danger {
    font-size: 14px;
    border-radius: 10px;
}

.return-button {
    background-color: #b6b3ae; 
    color: #1e1e1e; 
    border: none; 
    border-radius: 8px; 
    padding: 10px 20px; 
    font-size: 16px; 
    transition: background-color 0.3s ease;
}

.return-button:hover {
    background-color: #eed19e; 
    color: #ffffff; 
}

.table-light {
    border-collapse: separate;
    border-spacing: 0 0.4rem; 
    background-color: transparent;
    border: #d9b65d;
}

.table thead th {
    font-size: 0.95rem;
    background-color: #d9b65d;
    border: none;
    color: white;
}

.nav-tabs .nav-item button {
    background-color: white; 
    color: #d9b65d; 
    border-color: #d9b65d; 
    border-bottom: 2px solid transparent; 
    font-weight: 500;
    padding: 10px 20px;
    transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
}

.nav-tabs .nav-item button:hover {
    background-color: #f7e9c8; /* Light background on hover */
    color: #d9b65d; /* Matches the hover theme */
    border-color: #d9b65d; /* Adds an underline when hovered */
}

.nav-tabs .nav-item button.active,
.nav-tabs .nav-item button[aria-selected="true"] {
    background-color: #d9b65d; 
    color: #ffffff; 
    border-color: #d9b65d; 
    border-radius: 5px 5px 0 0; 
}

.nav-tabs {
    border-bottom: 2px solid #e0e0e0; /* Optional bottom border for the tab container */
}

#notification-dropdown {
    position: absolute;
    top: 70px; /* Adjust as per your layout */
    right: 20px;
    width: 500px;
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
    display: none; /* Initially hidden */
    max-height: 300px;
    overflow-y: auto;
}

#notification-dropdown li {
    padding: 10px 16px;
    color: #333;
    cursor: pointer;
}

#notification-dropdown li:hover {
    background-color: #f1e8d9;
}

.badge-danger {
    background-color: #dc3545;
    color: white;
    font-size: 14px;
    padding: 4px 8px;
    border-radius: 50%;
}
#unread-count {
    display: inline-block; /* Ensures it's visible initially */
    background: red;
    color: white;
    border-radius: 50%;
    padding: 2px 5px;
    font-size: 12px;
    position: absolute;
    margin-left:-10px;
  
}

    </style>
</head>

<body class="vh-100">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #f1e8d9; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <a class="navbar-brand fs-4" href="homepage.php">
                <img src="\SnD_Shoppe-main\PIC\sndlogo.png" width="70px" alt="Logo" />
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarTogglerDemo01"
                aria-controls="navbarTogglerDemo01" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarTogglerDemo01">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <li class="nav-item">
                        <a class="nav-link nav-link-black" href="#" id="notification-icon">
                            <img src="Assets/svg(icons)/notifications.svg" alt="notif">
                            <?php if ($unread_count > 0): ?>
                                <span class="badge badge-danger" id="unread-count"><?php echo $unread_count; ?></span>
                            <?php endif; ?>
                        </a>
                        <ul id="notification-dropdown">
                            <?php
                            if (empty($result_notifications)) {
                                echo '<li>No new notifications</li>';
                            } else {
                            foreach ($result_notifications as $notification) {
                                echo '<li data-notif-id="' . htmlspecialchars($notification['notif_id']) . '">' 
                                    . htmlspecialchars($notification['message']) . '</br>' . date('m-d-y', strtotime($notification['created_at'])) .'</li>';
                            } }
                            ?>
                        </ul>
                    </li>

                    <!-- New Account Dropdown Menu -->
                    <li class="nav-item dropdown">
                        <a class="nav-link nav-link-black dropdown-toggle" href="#" id="accountDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="/SnD_Shoppe-main/Assets/svg(icons)/account_circle.svg" alt="account">
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="accountDropdown">
                            <li>
                                <a class="dropdown-item" href="mypurchase.php">My Account</a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <a class="dropdown-item" href="homepage.php">Home</a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <a class="dropdown-item text-danger" href="logout.php">Logout</a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Cart Content -->

    <div class="header-container"> 
        <div class="card text-center">
            <div class="card-body">
                <h1 class="mb-0 custom-padding">SHOPPING CART</h1>
            </div>
        </div>
    </div>

    <div class="container my-5">
    <!-- Tab Navigation -->
    <ul class="nav nav-tabs" id="cartTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="shopping-cart-tab" data-bs-toggle="tab" data-bs-target="#shopping-cart"
                type="button" role="tab" aria-controls="shopping-cart" aria-selected="true">Shopping Cart</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="bulk-cart-tab" data-bs-toggle="tab" data-bs-target="#bulk-cart" type="button"
                role="tab" aria-controls="bulk-cart" aria-selected="false">Bulk Cart</button>
        </li>
    </ul>

    <div class="tab-content mt-3" id="cartTabContent">
        <!-- Shopping Cart Section -->
        <div class="tab-pane fade show active" id="shopping-cart" role="tabpanel" aria-labelledby="shopping-cart-tab">
            <div class="row">
                <div class="col-lg-8 col-md-12">
                    <form method="POST" action="">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">Product</th>
                                    <th scope="col">Color</th>
                                    <th scope="col">Unit Price</th>
                                    <th scope="col">Quantity</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cart_items as $item): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['product']); ?></td>
                                        <td><?php echo htmlspecialchars($item['color']); ?></td>
                                        <td>₱<?php echo number_format($item['unit_price'], 2); ?></td>
                                        <td><?php echo $item['quantity']; ?></td>
                                        <td>
                                            <button class="btn btn-danger btn-sm" name="remove_single"
                                                value="<?php echo $item['cart_id']; ?>">Remove</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </form>
                </div>
                <div class="col-lg-4 col-md-12">
                    <div class="p-4 border rounded shadow-sm bg-light">
                        <h4 class="mb-4" style="font-size: 30px;">Order Summary</h4>  
                        <?php foreach ($cart_items as $item): ?>
                            <?php 
                                if (($item['unit_price'] == 0 )) {
                                    continue;
                                }
                            ?>
                            <div class="d-flex justify-content-between" style="border-top: solid 1px #b6b3ae; font-size: 17px;">
                                <p style="font-weight: bold; margin-top: 15px;"><?php echo htmlspecialchars($item['product']); ?></p>
                            </div>
                            <div class="quantity">
                                <?php if ($item['unit_price'] > 0): ?>
                                    <p style="margin-top: -10px;">₱<?php echo number_format($item['unit_price'], 2); ?><span> x <?php echo $item['quantity']; ?> Yards</span></p>
                                <?php endif; ?>
                            </div>
                            <div class="d-flex justify-content-between" style="margin-bottom: 15px; margin-top: -8px;">
                                <h5 style="font-size: 17px; font-weight:normal">ITEM SUBTOTAL</h5>
                                <h5 class="subtotal fw-bold" style="font-size: 17px; color:#dcaa2e;">₱<?php echo number_format($item['total_price'], 2); ?></h5>
                            </div>
                        <?php endforeach; ?>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <h5>Grand Total</h5>
                            <h5 class="total-price fw-bold">₱<?php echo number_format($subtotal, 2); ?></h5>
                        </div>
                        <button class="btn btn-success btn-lg w-100 mt-4" style="background-color: #d9b65d; border:none" onclick="window.location.href='checkout.php'">Proceed to Checkout</button>
                        <button class="btn return-button w-100 mt-3" onclick="window.location.href='homepage.php'">Return</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bulk Cart Section -->
        <div class="tab-pane fade" name="bulk-cart" id="bulk-cart" role="tabpanel" aria-labelledby="bulk-cart-tab">
            <div class="row">
                <div class="col-lg-8 col-md-12">
                    <form method="POST" action="">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">Product</th>
                                    <th scope="col">Color</th>
                                    <th scope="col">Yard Unit Price</th>
                                    <th scope="col">Yards</th>
                                    <th scope="col">Roll Unit Price</th>
                                    <th scope="col">Rolls</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bulk_items as $bulk): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($bulk['product']); ?></td>
                                        <td><?php echo htmlspecialchars($bulk['color']); ?></td>
                                        <td>₱<?php echo number_format($bulk['unit_price'], 2); ?></td>
                                        <td><?php echo $bulk['yards']; ?></td>
                                        <td>₱<?php echo number_format($bulk['roll_price'], 2); ?></td>
                                        <td><?php echo $bulk['rolls']; ?></td>
                                        <td>
                                            <button class="btn btn-danger btn-sm" name="remove_single2"
                                            value="<?php echo $bulk['bulk_cart_id']; ?>">Remove</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </form>
                </div>
                <div class="col-lg-4 col-md-12">
                    <div class="p-4 border rounded shadow-sm bg-light">
                        <h4 class="mb-4" style="font-size: 30px;">Order Summary</h4> 
                        <?php foreach ($bulk_items as $bulk): ?>
                            <?php 
                                if (($bulk['unit_price'] == 0 || $bulk['yards'] == 0) && ($bulk['roll_price'] == 0 || $bulk['rolls'] == 0)) {
                                    continue;
                                }
                            ?>
                            <div class="d-flex justify-content-between" style="border-top: solid 1px #b6b3ae; font-size: 17px;">
                                <p style="font-weight: bold; margin-top: 15px;"><?php echo htmlspecialchars($bulk['product']); ?></p>
                            </div>
                            <div class="quantity">
                                <?php if ($bulk['unit_price'] > 0 && $bulk['yards'] > 0): ?>
                                    <p style="margin-top: -10px;">₱<?php echo $bulk['unit_price']; ?><span> x <?php echo $bulk['yards']; ?> Yards</span></p>
                                <?php endif; ?>
                                <?php if ($bulk['roll_price'] > 0 && $bulk['rolls'] > 0): ?>
                                    <p style="margin-top: -10px;">₱<?php echo $bulk['roll_price']; ?><span> x <?php echo $bulk['rolls']; ?> Rolls</span></p>
                                <?php endif; ?>
                            </div>
                            <div class="d-flex justify-content-between" style="margin-bottom: 15px; margin-top: -8px;">
                                <h5 style="font-size: 17px; font-weight:normal">ITEM SUBTOTAL</h5>
                                <h5 class="subtotal fw-bold" style="font-size: 17px; color:#dcaa2e;">₱<?php echo number_format($bulk['item_subtotal'], 2); ?></h5>
                            </div>
                        <?php endforeach; ?>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <h5>Grand Total</h5>
                            <h5 class="total-price fw-bold">₱<?php echo number_format($bulkGrandTotal, 2); ?></h5>
                        </div>
                        <button class="btn btn-success btn-lg w-100 mt-4" style="background-color: #d9b65d; border:none" onclick="window.location.href='bulk_checkout.php'">Proceed to Checkout</button>
                        <button class="btn return-button w-100 mt-3" onclick="window.location.href='homepage.php'">Return</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


    <script>
        function toggleAll(source) {
            checkboxes = document.getElementsByClassName('item-checkbox');
            for (var i = 0; i < checkboxes.length; i++) {
                checkboxes[i].checked = source.checked;
            }
        }

        document.addEventListener("DOMContentLoaded", function() {
                // Define values
                const subtotal = <?php echo $subtotal; ?>;
                //const shipping = 40.00;
                //const tax = 4.00;

                // Calculate total
                const total = subtotal;

                // Update the total in the HTML
                document.querySelector(".total-price").innerText = "₱" + total.toFixed(2);
        });

        
        // Check if the URL contains the '#bulk-cart' fragment
        if (window.location.hash === '#bulk-cart') {
            // Ensure the Bulk Cart tab is activated (assuming you are using Bootstrap)
            var bulkCartTab = new bootstrap.Tab(document.querySelector('#bulk-cart-tab'));
            bulkCartTab.show();

            // Optionally scroll the page to the bulk-cart section
            document.querySelector('#bulk-cart').scrollIntoView({ behavior: 'smooth' });
        }

        $(document).ready(function () {
    // Handle notification item click
    $('#notification-dropdown').on('click', 'li', function () {
        var notifId = $(this).data('notif-id'); // Get notif_id from the clicked notification

        if (notifId) {
            // Make an AJAX request to mark the notification as read
            $.ajax({
                url: 'homepage.php', // PHP script to handle notification read
                method: 'GET',
                data: { notif_id: notifId },
                success: function (response) {
                    var result = JSON.parse(response);

                    if (result.status === 'success') {
                        // Remove the clicked notification
                        $('li[data-notif-id="' + notifId + '"]').remove();

                        // Update the unread count
                        var unreadCountElement = $('#unread-count');
                        var unreadCount = parseInt(unreadCountElement.text(), 10);

                        if (unreadCount > 1) {
                            unreadCountElement.text(unreadCount - 1);
                        } else {
                            unreadCountElement.fadeOut(); // Hide the badge when count reaches 0
                        }
                    } else {
                        console.error('Error: ' + result.message);
                    }
                },
                error: function (xhr, status, error) {
                    console.error('AJAX error:', error);
                }
            });
        }
    });

    // Toggle notification dropdown visibility
    $('#notification-icon').on('click', function (e) {
        e.preventDefault();
        $('#notification-dropdown').toggle(); // Toggle dropdown visibility
    });

    // Close dropdown when clicking outside
    $(document).on('click', function (e) {
        if (!$('#notification-icon').is(e.target) && $('#notification-icon').has(e.target).length === 0 &&
            !$('#notification-dropdown').is(e.target) && $('#notification-dropdown').has(e.target).length === 0) {
            $('#notification-dropdown').hide();
        }
    });
});
    </script>

</body>

</html>
