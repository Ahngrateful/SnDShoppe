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

if (!isset($_SESSION['user_id'])) {
    header("Location: haveacc.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Get the product details in the bulk shopping cart for the logged-in user

$stmt = $pdo->prepare('SELECT bulk_cart_id, product_id, product, unit_price, roll_price, rolls, yards, color, color_id, item_subtotal, delivery_method, delivery_date, payment_method FROM bulk_shopping_cart WHERE customer_id = :user_id');
$stmt->execute(['user_id' => $user_id]);
$bulk_items = $stmt->fetchAll(PDO::FETCH_ASSOC);


$stmt = $pdo->prepare('SELECT delivery_method, delivery_date, payment_method FROM bulk_shopping_cart WHERE customer_id = :user_id LIMIT 1');
$stmt->execute(['user_id' => $user_id]);
$bulk_display = $stmt->fetch(PDO::FETCH_ASSOC);

$profile_data = [];
$stmt = $pdo->prepare('SELECT firstname, lastname, email, phone, gender, birthdate, address, subdivision,
barangay, postal, city, place FROM users_credentials WHERE id = ?');
$stmt->execute([$user_id]);
$profile_data = $stmt->fetch(PDO::FETCH_ASSOC);
$customer_name = $profile_data['firstname'] . ' ' . $profile_data['lastname'];
$address = $profile_data['address'] . ', ' . $profile_data['subdivision'] . ', ' . $profile_data['barangay'] . ', ' . $profile_data['city'] . ', ' . $profile_data['place'];

$stmtBulkSubtotal = $pdo->prepare('SELECT SUM(item_subtotal) AS bulksubtotal FROM bulk_shopping_cart WHERE customer_id = :user_id');
$stmtBulkSubtotal->execute(['user_id' => $user_id]);
$result = $stmtBulkSubtotal->fetch(PDO::FETCH_ASSOC);
$bulkGrandTotal = $result['bulksubtotal'] ?? 0;

$payment_id = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['proof'])) {
    try {
        $pdo->beginTransaction();

        // Handle payment details
        if (isset($_FILES['proof']) && $_FILES['proof']['error'] === UPLOAD_ERR_OK) {
            $target_dir = "uploads/";
            $file_name = basename($_FILES["proof"]["name"]);
            $target_file = $target_dir . uniqid() . "_" . $file_name;
            $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            if (in_array($file_type, ['jpg', 'jpeg', 'png', 'gif'])) {
                if (!move_uploaded_file($_FILES["proof"]["tmp_name"], $target_file)) {
                    throw new Exception('Error uploading the file.');
                }
            } else {
                throw new Exception('Invalid file type. Please upload an image.');
            }

            $stmt = $pdo->prepare("
                INSERT INTO bulk_payment (customer_id, customer_name, method, acc_name, number, ref_num, proof) 
                VALUES (:customer_id, :customer_name, :method, :acc_name, :number, :ref_num, :proof)
            ");
            $stmt->execute([
                ':customer_id' => $user_id,
                ':customer_name' => htmlspecialchars($_POST['customer_name'] ?? ''),
                ':method' => htmlspecialchars($_POST['method'] ?? ''),
                ':acc_name' => htmlspecialchars($_POST['acc_name'] ?? ''),
                ':number' => htmlspecialchars($_POST['number'] ?? ''),
                ':ref_num' => htmlspecialchars($_POST['ref_num'] ?? ''),
                ':proof' => $target_file
            ]);
            $payment_id = $pdo->lastInsertId();
            $pdo->commit();

        } 
        }catch (Exception $e) {
            $pdo->rollBack();
            echo '<div class="alert alert-danger">' . htmlspecialchars($e->getMessage()) . '</div>';
    
        exit; // Stop further processing for this POST request
    }
}

// Assuming $bulk_display contains the delivery method and delivery date from the bulk cart
$delivery_method = $bulk_display['delivery_method'] ?? ''; // Default to empty if not set
$delivery_date = $bulk_display['delivery_date'] ?? ''; // Default to empty if not set

// Ensure both delivery_method and delivery_date are not empty or null
if (empty($delivery_method) || empty($delivery_date)) {
    echo "<div class='alert alert-danger'>Error: Delivery details are missing. Please check your cart.</div>";
    exit;
}

// REGULAR ORDER
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    try {
        $pdo->beginTransaction();

        // Insert into bulk_order_details table
            $stmt = $pdo->prepare("
                INSERT INTO bulk_order_details (customer_id, grand_total, delivery_method, delivery_date, payment_id) 
                VALUES (:customer_id, :sub_total, :delivery_option, :delivery_date, :payment)
            ");
            $stmt->execute([
                ':customer_id' => $user_id,
                ':sub_total' => $bulkGrandTotal,
                ':delivery_option' => $delivery_method, // Use the correct delivery method
                ':delivery_date' => $delivery_date, // Use the correct delivery date
                ':payment' => $payment_id ?? null
            ]);
        
        // Get the auto-incremented bulk_order_id
        $bulk_order_id = $pdo->lastInsertId();

        // Insert each cart item into bulk_order_items table
        foreach ($bulk_items as $product) {

            $color_id = $product['color_id'] ?? null;

    if (!$color_id) {
        throw new Exception("Color ID not found for product {$product['product']}.");
    }

    // Check inventory
    $stmt = $pdo->prepare("SELECT yards, rolls FROM product_colors WHERE color_id = :color_id");
    $stmt->execute([':color_id' => $color_id]);
    $current_stock = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$current_stock) {
        throw new Exception("Color ID {$color_id} not found in inventory.");
    }

    $current_yards = $current_stock['yards'];
    $current_rolls = $current_stock['rolls'];

    if ($current_yards < $product['yards']) {
        throw new Exception("Insufficient yards for Color ID {$color_id}. Available: $current_yards, Required: {$product['yards']}.");
    }

    if ($current_rolls < $product['rolls']) {
        throw new Exception("Insufficient rolls for Color ID {$color_id}. Available: $current_rolls, Required: {$product['rolls']}.");
    }

    // Update stock
    $new_yards = $current_yards - $product['yards'];
    $new_rolls = $current_rolls - $product['rolls'];

    $stmt = $pdo->prepare("
        UPDATE product_colors 
        SET yards = :new_yards, rolls = :new_rolls 
        WHERE color_id = :color_id
    ");
    $stmt->execute([
        ':new_yards' => $new_yards,
        ':new_rolls' => $new_rolls,
        ':color_id' => $color_id
    ]);

             // Update product_revenue table
           $stmt = $pdo->prepare("
           SELECT items_sold, total_revenue FROM product_revenue WHERE product_id = :product_id
           ");
           $stmt->execute([':product_id' => $product['product_id']]);
           $revenue = $stmt->fetch(PDO::FETCH_ASSOC);

           if ($revenue) {
               // Existing revenue record
               $new_items_sold = $revenue['items_sold'] + $product['yards'];
               $new_total_revenue = $revenue['total_revenue'] + $product['item_subtotal'];

               $stmt = $pdo->prepare("
                   UPDATE product_revenue 
                   SET items_sold = :items_sold, total_revenue = :total_revenue 
                   WHERE product_id = :product_id
               ");
               $stmt->execute([
                   ':items_sold' => $new_items_sold,
                   ':total_revenue' => $new_total_revenue,
                   ':product_id' => $product['product_id']
               ]);
           }
           
           ///

            $stmt = $pdo->prepare("
                INSERT INTO bulk_order_items (bulk_order_id, product_id, product_name, color, yards, rolls, item_subtotal) 
                VALUES (:order_num, :product_id, :product_name, :color, :yards, :rolls, :itemsubtotal)
            ");
            $stmt->execute([
                ':order_num' => $bulk_order_id,
                ':product_id' => $product['product_id'],
                ':product_name' => $product['product'],
                ':color' => $product['color'],
                ':yards' => $product['yards'],
                ':rolls' => $product['rolls'],
                ':itemsubtotal' => $product['item_subtotal'],
            ]);
        }

        // Optionally clear the shopping cart after placing the order
        $stmt = $pdo->prepare("DELETE FROM bulk_shopping_cart WHERE customer_id = :customer_id");
        $stmt->execute([':customer_id' => $user_id]);

        $pdo->commit();

        echo "<script>
            alert('Order placed successfully!');
            window.location.href = 'mypurchase.php';
        </script>";
    } catch (Exception $e) {
        $pdo->rollBack();
        echo '<div id="message" class="alert alert-danger">' . htmlspecialchars($e->getMessage()) . '</div>';
    }
    exit;
}

// COD ORDER
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cod_place_order'])) {
    try {
        $pdo->beginTransaction();
       $payment_id = 11220000; // Default payment_id for COD payment_method
      
$payment_id = $pdo->lastInsertId();

            // Insert into bulk_order_details table
            $stmt = $pdo->prepare("
                INSERT INTO bulk_order_details (customer_id, grand_total, delivery_method, delivery_date, payment_id) 
                VALUES (:customer_id, :sub_total, :delivery_option, :delivery_date, :payment_id)
            ");
            $stmt->execute([
                ':customer_id' => $user_id,
                ':sub_total' => $bulkGrandTotal,
                ':delivery_option' => $delivery_method, // Use the correct delivery method
                ':delivery_date' => $delivery_date, // Use the correct delivery date
                ':payment_id' => $payment_id
            ]); 
        
        // Get the auto-incremented bulk_order_id
        $bulk_order_id = $pdo->lastInsertId();

        // Insert each cart item into bulk_order_items table
        foreach ($bulk_items as $product) {
            $color_id = $product['color_id'] ?? null;

    if (!$color_id) {
        throw new Exception("Color ID not found for product {$product['product']}.");
    }

    // Check inventory
    $stmt = $pdo->prepare("SELECT yards, rolls FROM product_colors WHERE color_id = :color_id");
    $stmt->execute([':color_id' => $color_id]);
    $current_stock = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$current_stock) {
        throw new Exception("Color ID {$color_id} not found in inventory.");
    }

    $current_yards = $current_stock['yards'];
    $current_rolls = $current_stock['rolls'];

    if ($current_yards < $product['yards']) {
        throw new Exception("Insufficient yards for Color ID {$color_id}. Available: $current_yards, Required: {$product['yards']}.");
    }

    if ($current_rolls < $product['rolls']) {
        throw new Exception("Insufficient rolls for Color ID {$color_id}. Available: $current_rolls, Required: {$product['rolls']}.");
    }

    // Update stock
    $new_yards = $current_yards - $product['yards'];
    $new_rolls = $current_rolls - $product['rolls'];

    $stmt = $pdo->prepare("
        UPDATE product_colors 
        SET yards = :new_yards, rolls = :new_rolls 
        WHERE color_id = :color_id
    ");
    $stmt->execute([
        ':new_yards' => $new_yards,
        ':new_rolls' => $new_rolls,
        ':color_id' => $color_id
    ]);

             // Update product_revenue table
           $stmt = $pdo->prepare("
           SELECT items_sold, total_revenue FROM product_revenue WHERE product_id = :product_id
           ");
           $stmt->execute([':product_id' => $product['product_id']]);
           $revenue = $stmt->fetch(PDO::FETCH_ASSOC);

           if ($revenue) {
               // Existing revenue record
               $new_items_sold = $revenue['items_sold'] + $product['yards'];
               $new_total_revenue = $revenue['total_revenue'] + $product['item_subtotal'];

               $stmt = $pdo->prepare("
                   UPDATE product_revenue 
                   SET items_sold = :items_sold, total_revenue = :total_revenue 
                   WHERE product_id = :product_id
               ");
               $stmt->execute([
                   ':items_sold' => $new_items_sold,
                   ':total_revenue' => $new_total_revenue,
                   ':product_id' => $product['product_id']
               ]);
           }
           
           ///
            $stmt = $pdo->prepare("
                INSERT INTO bulk_order_items (bulk_order_id, product_id, product_name, color, yards, rolls, item_subtotal) 
                VALUES (:order_num, :product_id, :product_name, :color, :yards, :rolls, :itemsubtotal)
            ");
            $stmt->execute([
                ':order_num' => $bulk_order_id,
                ':product_id' => $product['product_id'],
                ':product_name' => $product['product'],
                ':color' => $product['color'],
                ':yards' => $product['yards'],
                ':rolls' => $product['rolls'], 
                ':itemsubtotal' => $product['item_subtotal'],
            ]);


        }

        // Optionally clear the shopping cart after placing the order
        $stmt = $pdo->prepare("DELETE FROM bulk_shopping_cart WHERE customer_id = :customer_id");
        $stmt->execute([':customer_id' => $user_id]);

        $pdo->commit();

        echo "<script>
            alert('Order placed successfully!');
            window.location.href = 'mypurchase.php';
        </script>";
    } catch (Exception $e) {
        $pdo->rollBack();
        echo '<div id="message" class="alert alert-danger">' . htmlspecialchars($e->getMessage()) . '</div>';
    }
    exit;
}

// Fetch unread notifications
$query_notifications = "SELECT notif_id, message FROM notifications WHERE id = ? AND is_read = 0";
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
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet"/>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link rel="icon" href="PIC/sndlogo.png" type="logo" />
    <title>Checkout</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Kumbh+Sans:wght@100..900&family=Playfair+Display+SC:ital,wght@0,400;0,700;0,900;1,400;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900&display=swap');

/* General Styles */
body {
    background-color:#FFFFFF;
    background-blend-mode: multiply;
    background-position: center;
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

/* Search Bar */
.search-bar {
    max-width: 300px;
    width: 100%;
}

.input-group-text {
    background-color: #f1e8d9;
    border: 1px solid #d9b65d;
    border-radius: 10px 0 0 20px;
}

.form-control {
    border: 1px solid #d9b65d;
    border-radius: 0 10px 10px 0;
    text-align: left;
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
    border-radius: 10px;
}

.card {
    background-color: transparent;
    margin: 0;
    text-align: center;
    border-radius: 10px;
}

.table-bordered {
    border-radius: 10px;
    overflow: hidden;
}

.custom-padding {
    padding-top: 30px;
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

.navbar .dropdown-item:hover {
    background-color: #f1e8d9;
    border-radius: 0;
}

.dropdown-item.text-danger {
    color: #dc3545;
    font-weight: bold;
}

.dropdown-divider {
    margin: 0;
}

/* Cart Items Table */
.table {
    background-color: #f1e8d9;
    border-color: #d9b65d;
}

/* Cart Items */
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
    border-radius: 10px;
}

.btn-outline-danger {
    font-size: 14px;
    border-radius: 10px;
}

/* Return Button */
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

/* Order Summary Card */
.bg-light {
    background-color: #ffffff !important;
    border-color: #d9b65d;
    border-radius: 5px;
}

/* Checkout Page Styling */
h4 {
    font-family: "Playfair Display SC", serif;
    font-size: 24px;
    color: #1e1e1e;
}

form .form-label {
    font-weight: bold;
}

.form-control {
    border: 1px solid #d9b65d;
    border-radius: 5px;
    font-size: 16px;
}

textarea.form-control {
    resize: none;
}

/* Buttons in Checkout */
.btn-success {
    background-color: #157347;
    border-radius: 10px;
}

.btn-success button {
    background-color: #157347;
    border-radius: 10px;
    width: 300px;
}

.btn-success:hover {
    background-color: #19583b;
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

/* Order Summary */
.order-summary {
    background-color: #f1e8d9 !important;
}

.total-price {
    font-size: 22px;
    font-weight: bold;
    color: #a70000;
}

.center-message {
  padding: 100px;
  margin-bottom: 15px;
}

/* Checkout Form */
form {
    background-color: #f1e8d9 ;
    padding: 20px;
    border-radius: 5px;
}

form input, form textarea {
    font-size: 16px;
    padding: 20px;
}

form .btn-success {
    font-size: 18px;
    box-shadow: none;
}


/* Small Adjustments for Mobile */
@media (max-width: 767px) {
    .header-container {
        top: 120px;
    }

    .custom-padding {
        padding-top: 20px;
    }
}
#overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: none;
    justify-content: center;
    align-items: center;
    color: #fff;
    font-size: 2em;
    z-index: 9999;
    }
    
    #notification-dropdown {
    position: absolute;
    top: 70px; /* Adjust as per your layout */
    right: 20px;
    width: 200px;
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

<!-- Navbar --> 
<nav class="navbar navbar-expand-lg navbar-light" style="background-color: #f1e8d9;">
        <div class="container-fluid">
            <a class="navbar-brand" href="homepage.php">
                <img src="PIC/sndlogo.png" width="70" alt="Logo">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarContent">
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
                                    . htmlspecialchars($notification['message']) . '</li>';
                            } }
                            ?>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="accountDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="Assets/svg(icons)/account_circle.svg" alt="Account">
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="accountDropdown">
                            <li><a class="dropdown-item" href="mypurhase.php">My Account</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="homepage.php">Home</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Checkout Content-->
    <div class="header-container">
      <div class="card text-center">
        <div class="card-body">
          <h1 class="mb-0 custom-padding">CHECKOUT</h1>
        </div>
      </div>
    </div>

    <div class="container my-5">
    <div class="row">
        <div class="col-lg-8 col-md-12">
            <div class="p-4 border rounded shadow-sm bg-light mb-4">
                <h4 class="mb-4">SHIPPING INFORMATION</h4>
                <div class="mb-3">
                    <label class="form-label fw-bold">Full Name:</label>
                    <p id="full-name" class="form-control-plaintext ms-3">
                        <?php echo htmlspecialchars($profile_data['firstname'] . ' ' . $profile_data['lastname'] ?? ''); ?>
                    </p>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Email Address:</label>
                    <p id="email" class="form-control-plaintext ms-3">
                        <?php echo htmlspecialchars($profile_data['email'] ?? ''); ?>
                    </p>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Phone:</label>
                    <p id="contact" class="form-control-plaintext ms-3">
                        <?php echo htmlspecialchars($profile_data['phone'] ?? ''); ?>
                    </p>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Address</label>
                    <p id="address" name= "address" class="form-control-plaintext ms-3">
                        <?php echo htmlspecialchars($profile_data['address'] . ' ' . $profile_data['subdivision'] . ' ' . $profile_data['barangay']
                        . ' ' . $profile_data['postal'] . ' ' . $profile_data['city'] . ' ' . $profile_data['place']); ?> 
                    </p>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Delivery Method</label>
                    <?php if (!empty($bulk_items)): ?>
                        <p class="form-control-plaintext ms-3">
                            <?php echo htmlspecialchars($bulk_items[0]['delivery_method']); ?>
                        </p>
                    <?php else: ?>
                        <p>No delivery method found.</p>
                    <?php endif; ?>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Preferred Delivery Date</label>
                    <?php if (!empty($bulk_display)): ?>
                        <p class="form-control-plaintext ms-3">
                            <?php 
                            // Convert the delivery_date to the desired format (Month Day, Year)
                            $formatted_date = date("F j, Y", strtotime($bulk_display['delivery_date']));
                            echo htmlspecialchars($formatted_date);
                            ?>
                        </p>
                    <?php else: ?>
                        <p>No delivery date found.</p>
                    <?php endif; ?>
                </div>
                <div class="mb-3">
                <label class="form-label fw-bold">Selected Payment Method from Bulk Order Request: 
                    <p class="form-control-plaintext ms-3" style="color: #157347;"><?php echo htmlspecialchars($bulk_items[0]['payment_method']); ?></p>
                </label>   
                </div>

            <?php
                $payment_method = htmlspecialchars($bulk_items[0]['payment_method']);
                
                // If the selected payment method is GCash or Maya, show the respective form
                if ($payment_method == 'GCash') {
            ?>
                    <!-- GCash Payment Form -->
                    <form id="gcash-form" action="" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                        <input type="hidden" name="customer_name" value="<?php echo htmlspecialchars($profile_data['firstname'] . ' ' . $profile_data['lastname']); ?>">
                        <input type="hidden" name="method" value="Gcash">
                        <div class="mb-3 text-center">
                            <label for="gcash-qr-code" class="form-label">GCash Payment Verification</label>
                            <div id="gcash-qr-code" class="qr-code-container">
                                <img src="images/gcashqr.jpg" alt="GCash QR Code" class="qr-code-image" width="100%" />
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="acc_name" class="form-label">Account Name</label>
                            <input type="text" class="form-control" name="acc_name" id="acc_name" placeholder="Enter GCash Account Name" required>
                        </div>

                        <div class="mb-3">
                            <label for="number" class="form-label">Mobile Number</label>
                            <input type="text" class="form-control" name="number" id="number" maxlength="11" placeholder="e.g 09xxxxxxxxx" required>
                        </div>

                        <div class="mb-3">
                            <label for="ref_num" class="form-label">Reference Number</label>
                            <input type="text" class="form-control" name="ref_num" id="ref_num" placeholder="Enter GCash Reference Number" required>
                        </div>

                        <div class="mb-3">
                            <label for="proof" class="form-label">Upload Proof of Payment</label>
                            <input type="file" class="form-control" name="proof" id="proof" accept="image/*" required>
                        </div>

                        <button type="submit" name="place_order" class="btn btn-success w-100">Place Order</button>
                    </form>

            <?php
                } elseif ($payment_method == 'Maya') {
            ?>
                    <!-- Maya Payment Form -->
                    <form id="maya-form" action="" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                        <input type="hidden" name="customer_name" value="<?php echo htmlspecialchars($profile_data['firstname'] . ' ' . $profile_data['lastname']); ?>">
                        <input type="hidden" name="method" value="Maya">
                        <div class="mb-3 text-center">
                            <label for="maya-qr-code" class="form-label">Maya Payment Verification</label>
                            <div id="maya-qr-code" class="qr-code-container">
                                <img src="images/paymayaqr.jpg" alt="Maya QR Code" class="qr-code-image" width="100%" />
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="acc_name" class="form-label">Account Name</label>
                            <input type="text" class="form-control" name="acc_name" id="acc_name" placeholder="Enter Maya Account Name" required>
                        </div>

                        <div class="mb-3">
                            <label for="number" class="form-label">Mobile Number</label>
                            <input type="text" class="form-control" name="number" id="number" maxlength="11" placeholder="e.g 09xxxxxxxxx" required>
                        </div>

                        <div class="mb-3">
                            <label for="ref_num" class="form-label">Reference Number</label>
                            <input type="text" class="form-control" name="ref_num" id="ref_num" placeholder="Enter Maya Reference Number" required>
                        </div>

                        <div class="mb-3">
                            <label for="proof" class="form-label">Upload Proof of Payment</label>
                            <input type="file" class="form-control" name="proof" id="proof" accept="image/*" required>
                        </div>

                        <button type="submit" name="place_order" class="btn btn-success w-100">Place Order</button>
                    </form>

            <?php
                } else {
                    echo "<p>Contact the Seller for the COD Payment and Delivery Methods. <a style='color: #dcaa2e;' href='#'>Contact Details Here!</a></p>"; 
                }
            ?>

            </div>
        </div>

        
          <!-- Order Summary -->
<div class="col-lg-4 col-md-12">
    <div class="p-4 border rounded shadow-sm bg-light order-summary">
        <h4 class="mb-4">Order Summary</h4>
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
        
            <form method="POST" action="">
                <?php if ($payment_method == 'COD') { ?>
                    <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                    <input type="hidden" name="customer_name" value="<?php echo htmlspecialchars($profile_data['firstname'] . ' ' . $profile_data['lastname']); ?>">
                    <input type="hidden" name="method" value="COD">
                    <button class="btn btn-success btn-lg w-100 mt-4" name="cod_place_order">Place Order</button--> <!--inalis para isahang save na lang sa payment-->
                <?php
                    } else {
                        echo "<p>Answer the GCash Payment Verification Form.</p>";
                    }
                ?>
            </form>            

        <button type="button" class="btn return-button w-100 mt-3" onclick="window.location.href='cart.php'">Back to Cart</button>
        </form>
    </div>
</div>



    <script src="product.js"></script>
    <script>
    document
    .getElementById("payment-option")
    .addEventListener("change", function () {
        const selectedOption = this.value;
        document.querySelectorAll('.modal form input[name="method"]').forEach(input => {
            input.value = selectedOption; // Sync method in modals
        });

        if (selectedOption === "Gcash") {
            const gcashModal = new bootstrap.Modal(document.getElementById("gcashModal"));
            gcashModal.show();
        } else if (selectedOption === "Maya") {
            const mayaModal = new bootstrap.Modal(document.getElementById("mayaModal"));
            mayaModal.show();
        }
    });
</script>
<script>
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
