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

// Get the product details in the bulk shopping cart for the logged-in user
$stmt = $pdo->prepare('SELECT bulk_cart_id, product_id, product, unit_price, roll_price FROM bulk_shopping_cart WHERE customer_id = :user_id');
$stmt->execute(['user_id' => $user_id]);
$bulk_items = $stmt->fetchAll(PDO::FETCH_ASSOC);


$bulk_colors = []; // Initialize the array
foreach ($bulk_items as $bulk) {
    $stmtColors = $pdo->prepare('SELECT color_id, color_name, yards, rolls FROM product_colors WHERE product_id = :product_id');
    $stmtColors->execute(['product_id' => $bulk['product_id']]);
    $colors = $stmtColors->fetchAll(PDO::FETCH_ASSOC);

    if (empty($colors)) {
        error_log("No colors found for product_id: " . $bulk['product_id']);
    }

    $bulk_colors[$bulk['product_id']] = $colors;
}

if (isset($_POST['remove'])) {
    // Get the bulk_cart_id from the form
    $bulk_cart_id = $_POST['bulk_cart_id'];

    // Prepare and execute the query to remove the item from the cart
    $stmt = $pdo->prepare('DELETE FROM bulk_shopping_cart WHERE bulk_cart_id = :bulk_cart_id');
    $stmt->execute(['bulk_cart_id' => $bulk_cart_id]);

    // Optionally, redirect to refresh the page
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Fetch shipping address from the database
$profile_data = [];
$stmt = $pdo->prepare('SELECT firstname, lastname, email, phone, gender, birthdate, address, subdivision,
barangay, postal, city, place FROM users_credentials WHERE id = ?');
$stmt->execute([$user_id]);
$profile_data = $stmt->fetch(PDO::FETCH_ASSOC);
$customer_name = $profile_data['firstname'] . ' ' . $profile_data['lastname'];
$address = $profile_data['address'] . ', ' . $profile_data['subdivision'] . ', ' . $profile_data['barangay'] . ', ' . $profile_data['city'] . ', ' . $profile_data['place'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['request_bulk'])) {
    // Get payment, delivery details, and item quantities
    $paymentMethod = $_POST['payment_option'] ?? null;  // Corrected to 'payment_option'
    $deliveryDate = $_POST['delivery_date'] ?? null;    // Corrected to 'delivery_date'
    $deliveryMethod = $_POST['delivery_method'] ?? null; // Corrected to 'delivery_method'
    $yards = $_POST['yards'] ?? [];
    $rolls = $_POST['rolls'] ?? [];
    $colorOptions = $_POST['color_option'] ?? [];

    // Ensure the required fields are provided
    if (!$paymentMethod || !$deliveryDate || !$deliveryMethod) {
        echo "Please select all required fields.";
        exit;
    }

    // Validate payment method
    $validPaymentMethods = ['COD', 'GCash', 'Maya'];
    if (!in_array($paymentMethod, $validPaymentMethods)) {
        echo "Invalid payment method.";
        exit;
    }

    // Initialize an array to hold the subtotals for each bulk_cart_id
    $totals = [];

    // Iterate through the yards array to update each product
    foreach ($yards as $bulkCartId => $yardsValue) {
        $yardsQuantity = (int)$yardsValue;
        $rollsQuantity = isset($rolls[$bulkCartId]) ? (int)$rolls[$bulkCartId] : 0;
        $color = $colorOptions[$bulkCartId] ?? '';

        // Assuming you have a price per yard in the database, let's fetch it
        try {
            $stmt = $pdo->prepare('SELECT bulk_cart_id, product_id, product, unit_price, roll_price FROM bulk_shopping_cart WHERE bulk_cart_id = :bulk_cart_id');
            $stmt->execute([':bulk_cart_id' => $bulkCartId]);
            $bulk_item = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($bulk_item) {
                // Calculate the subtotal for this item
                $yardSubtotal = $bulk_item['unit_price'] * $yardsQuantity;
                $rollSubtotal = $bulk_item['roll_price'] * $rollsQuantity;
                $itemSubtotal = $yardSubtotal + $rollSubtotal;

                // Add the item subtotal to the total for this bulk_cart_id
                if (!isset($totals[$bulkCartId])) {
                    $totals[$bulkCartId] = 0;
                }
                $totals[$bulkCartId] += $itemSubtotal;
                $color_id = null;
                foreach ($colors as $row) {
                    if ($row['color_name'] === $color) {
                        $color_id = $row['color_id'];
                        break;
                    }
                }

                if ($color_id === null) {
                    die("Invalid color selection.");
                }

                // Prepare and execute the update query using bulk_cart_id
                $stmt = $pdo->prepare("
                    UPDATE bulk_shopping_cart
                    SET 
                        payment_method = :payment_method,
                        delivery_date = :delivery_date,
                        delivery_method = :delivery_method,
                        yards = :yards,
                        rolls = :rolls,
                        color = :color,
                        color_id =:color_id,
                        item_subtotal = :item_subtotal
                    WHERE bulk_cart_id = :bulk_cart_id
                ");
                $stmt->execute([
                    ':payment_method' => $paymentMethod,
                    ':delivery_date' => $deliveryDate,
                    ':delivery_method' => $deliveryMethod,
                    ':yards' => $yardsQuantity,
                    ':rolls' => $rollsQuantity,
                    ':color' => $color,
                    ':color_id' => $color_id,
                    ':item_subtotal' => $itemSubtotal,
                    ':bulk_cart_id' => $bulkCartId
                ]);
            }
        } catch (PDOException $e) {
            error_log("Error updating bulk shopping cart: " . $e->getMessage());
            echo "Failed to save changes for Bulk Cart ID $bulkCartId. Please try again.";
        }
    }

    foreach ($totals as $bulkCartId => $total) {
        try {
            $stmt = $pdo->prepare("
                UPDATE bulk_shopping_cart
                SET item_subtotal = :total
                WHERE bulk_cart_id = :bulk_cart_id
            ");
            $stmt->execute([
                ':total' => $total,
                ':bulk_cart_id' => $bulkCartId
            ]);
        } catch (PDOException $e) {
            error_log("Error updating total for bulk_cart_id $bulkCartId: " . $e->getMessage());
            echo "Failed to save total for Bulk Cart ID $bulkCartId. Please try again.";
        }
    }

    // Redirect to cart.php bulk-cart section
    header("Location: cart.php#bulk-cart");
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
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
      rel="stylesheet"
      integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
      crossorigin="anonymous"
    />
    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
      integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
      crossorigin="anonymous"
    ></script>
    <link rel="icon" href="/Assets/images/sndlogo.png" type="logo" />
    <link rel="stylesheet"/>
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css"
    />
    <title>S&D Fabrics</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Kumbh+Sans:wght@100..900&family=Playfair+Display+SC:ital,wght@0,400;0,700;0,900;1,400;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900&display=swap');

/* General Styles */
body {
    background: url(Assets/bgLogin.png) rgba(0, 0, 0, 0.3);
    background-blend-mode: multiply;
    background-position: center;
    background-size: cover;
    background-repeat:repeat-y;
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

.navbar-toggler-icon {
    background-image: url("data:image/svg+xml;charset=utf8,%3Csvg viewBox='0 0 30 30' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath stroke='rgba(30, 30, 30, 1)' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3E%3C/svg%3E");
}

.input-group-text, .form-control {
    background-color: #f1e8d9;
    border: 1px solid #d9b65d;
    border-radius: 10px;
}

.form-control {
    border-radius: 0 10px 10px 0;
    text-align: left;
}

h1, h3 {
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
}

.navbar .dropdown-item {
    padding: 10px 16px;
    font-size: 14px;
    color: #1e1e1e;
    transition: background-color 0.3s;
}

.navbar .dropdown-item:hover {
    background-color: #f1e8d9;
}

.dropdown-item.text-danger {
    color: #dc3545;
    font-weight: bold;
}

.dropdown-divider {
    margin: 0;
}

/* PAYMENT AND DELIVERY OPTION Styles */
.payment-delivery-options {
    background-color: #f1e8d9;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.payment-delivery-options h3 {
    font-size: 24px;
    font-weight: 700;
}

.form-label {
    font-weight: 600;
}

.form-select,
.form-control {
    border-radius: 8px;
    font-size: 16px;
    padding: 8px 12px;
}

.delivery-method {
    margin-top: 20px;
}

.delivery-method .form-check {
    display: flex;
    align-items: center;
    margin-right: 20px;
}

.form-check-input {
    margin-right: 10px;
}

.form-check-label {
    font-weight: 500;
    font-size: 14px;
}

/* Product Details */
.product-container {
    display: flex;
    gap: 3rem;
    flex-wrap: wrap;
}

.product-details,
.details {
    background-color: #f1e8d9;
    padding: 1.5rem;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.product-details-section {
    background-color: #ddceb4;
}

.product-details h2 {
    font-family: 'Playfair Display SC', serif;
    font-size: 28px;
    color: #333;
    margin-bottom: 1rem;
}

.product-details p {
    font-size: 16px;
    color: #555;
}

.price {
    font-weight: bold;
    font-size: 20px;
}

.colors {
    margin-top: 20px;
}

.color-controls .color-row {
    margin-bottom: 10px;
}

.color-row .color-swatch {
    width: 20px;
    height: 20px;
    margin-right: 10px;
    border-radius: 50%;
}

.form-control,
.form-select {
    border-radius: 8px;
    font-size: 16px;
    padding: 8px 12px;
}

.color-quantity {
    width: 60px;
    text-align: center;
    margin-right: 10px;
}

.decrement,
.increment {
    background-color: #f1e8d9;
    border: 1px solid #d9b65d;
}

.decrement:hover,
.increment:hover {
    background-color: #d9b65d;
    color: white;
}

.details h3 {
    font-size: 24px;
    font-weight: bold;
    color: #333;
    margin-bottom: 1rem;
}

.details p {
    font-size: 16px;
    color: #555;
}

.subtotal {
    background-color: #f9f9f9;
    padding: 1rem;
    border-radius: 8px;
    margin-top: 1rem;
}

.subtotal .total {
    color: #e044a5;
    font-weight: bold;
    font-size: 18px;
}

/* Button Styles */
.order-btn,
.contact-btn {
    padding: 1rem 1.5rem; /* Increased padding for a more uniform look */
    font-size: 18px; /* Slightly larger font size for better readability */
    font-weight: bold;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
    width: 100%; /* Ensures buttons take full width of the parent container */
}

.order-btn {
    background-color: #19583b;
}

.order-btn:hover {
    background-color: #238f4f;
    color: white;
}

.contact-btn {
    background-color: #c9a46b;
    color: white;
}

.contact-btn:hover {
    background-color: #DABC8F;
}

.home-btn {
    background-color: #6c757d;
    color: white;
    border: none;
    margin-top: 17px;
    font-weight: bold;
}

.home-btn:hover {
    background-color: #b6b3ae;
}

/* Responsive Adjustment for Smaller Screens */
@media (max-width: 767px) {
    .order-btn,
    .contact-btn {
        padding: 1rem; /* Ensure appropriate padding on smaller screens */
        font-size: 16px; /* Adjust font size for smaller screens */
    }
}


.btn {
    padding: 4px 12px;
    font-size: 14px;
    border-radius: 4px;
}

.btn-secondary {
    background-color: #6c757d;
    color: #fff;
    border: 1px solid #6c757d;
}

.btn-secondary:hover {
    background-color: #5a6268;
    border-color: #545b62;
}

/* Enhanced Color Section Styling */
.colors {
    padding: 1.5rem;
    background-color: #f9f9f9;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.colors p {
    font-size: 18px;
    color: #444;
    margin-bottom: 1rem;
}

.color-controls {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.color-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 8px;
    background-color: #fff;
}

.color-row .color-swatch {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    margin-right: 20px;
}

.color-quantity {
    width: 80px;
    height: 30px;
    font-size: 14px;
    text-align: center;
    border-radius: 5px;
}

.decrement,
.increment {
    padding: 5px 10px;
    border-radius: 5px;
    font-size: 18px;
    background-color: #ddd;
}

.decrement:hover,
.increment:hover {
    background-color: #ccc;
    cursor: pointer;
}

.rolls {
    display: flex;
    align-items: center;
    background-color: #f1e8d9; /* Light background color for visibility */
    padding: 10px;
    border-radius: 8px;
    border: 1px solid #dcaa2e;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); /* Optional shadow */
}

.quantity-container {
    display: flex;
    align-items: center;
}

#rolls-value {
    width: 60px;
    border: 1px solid #d9b65d; /* Border for visibility */
    outline: none;
    text-align: center;
    background-color: #fff; /* Ensure input field background is visible */
}

#yards-value {
    width: 60px;
    border: 1px solid #d9b65d; /* Border for visibility */
    outline: none;
    text-align: center;
    background-color: #fff; /* Ensure input field background is visible */
}

#rolls-value:focus {
    border-color: #e044a5; /* Highlight input field on focus */
}

#yards-value:focus {
    border-color: #e044a5; /* Highlight input field on focus */
}

.remove-btn {
    background-color: #901A1B;
    border: none;
    margin-top: 15px;
    padding: 10px 20px;
    font-size: 18px;
    font-weight: bold;
    border-radius: 8px;
    cursor: pointer;
    transition: background-color 0.3s ease, transform 0.3s ease;
}

/* Hover effect for the remove button */
.remove-btn:hover {
    background-color: #FF4C4C;  /* Lighter red on hover */
    transform: scale(1.05);  /* Slightly grow the button */
    color: white;  /* Ensure text color changes to white */
}

</style>
  </head>
  <body class="vh-100">
    <!-- Navbar -->

    <nav
      class="navbar navbar-expand-lg navbar-dark"
      style="
        background-color: #f1e8d9;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
      "
    >
      <div
        class="container-fluid d-flex justify-content-between align-items-center"
      >
        <a class="navbar-brand fs-4" href="homepage.php">
          <img src="Assets/sndlogo.png" width="70px" alt="Logo" />
        </a>

        <button
          class="navbar-toggler"
          type="button"
          data-bs-toggle="collapse"
          data-bs-target="#navbarTogglerDemo01"
          aria-controls="navbarTogglerDemo01"
          aria-expanded="false"
          aria-label="Toggle navigation"
        >
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarTogglerDemo01">
          <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
            <li class="nav-item">
              <a class="nav-link nav-link-black" href="#">
                <img src="/Assets/svg(icons)/notifications.svg" alt="notif" />
              </a>
            </li>

            <li class="nav-item">
              <a class="nav-link nav-link-black" href="#">
                <img src="/Assets/svg(icons)/inbox.svg" alt="inbox" />
              </a>
            </li>

            <!-- New Account Dropdown Menu -->
            <li class="nav-item dropdown">
              <a
                class="nav-link nav-link-black dropdown-toggle"
                href="#"
                id="accountDropdown"
                role="button"
                data-bs-toggle="dropdown"
                aria-expanded="false"
              >
                <img
                  src="/Assets/svg(icons)/account_circle.svg"
                  alt="account"
                />
              </a>
              <ul
                class="dropdown-menu dropdown-menu-end"
                aria-labelledby="accountDropdown"
              >
                <li>
                  <a
                    class="dropdown-item"
                    href="/pages/myAccountPage/myPurchase.html"
                    >My Account</a
                  >
                </li>
                <li>
                  <hr class="dropdown-divider" />
                </li>
                <li>
                  <a class="dropdown-item text-danger" href="#">Logout</a>
                </li>
              </ul>
            </li>
          </ul>
        </div>
      </div>
    </nav>

    <!-- Checkout Content -->
    <div class="header-container">
      <div class="card text-center">
        <div class="card-body">
          <h1 class="mb-0 custom-padding">BULK ORDER</h1>
        </div>
      </div>
    </div>

    <section class="delivery-page container my-5">
        <form method="POST" action="">
            <div class="payment-delivery-options shadow p-4 mb-5">
                <h3 class="text-center mb-4">PAYMENT AND DELIVERY OPTION</h3>
                <div class="row g-3">
                    <!-- PAYMENT METHOD -->
                    <div class="col-md-6">
                        <label class="form-label">PAYMENT METHOD:</label>
                        <select class="form-select shadow-sm" id="payment-opt" name="payment_option">
                            <option selected value="GCash">GCash</option>
                            <option value="Maya">Maya</option>
                            <option value="COD">Cash on Delivery (COD)</option>
                        </select>
                    </div>

                    <!-- SELECTED DATE -->
                    <div class="col-md-6">
                        <label class="form-label">SELECT PREFERRED DELIVERY DATE:</label>
                        <input type="date" class="form-control shadow-sm" name="delivery_date" />
                    </div>
                </div>

                <!-- DELIVERY METHOD -->
                <div class="delivery-method mt-4">
                    <label class="form-label">DELIVERY METHOD:</label>
                        <div class="d-flex flex-wrap justify-content-around mt-2">
                        <div class="form-check">
                            <input type="radio" id="pickup" name="delivery_method" value="Pick Up" class="form-check-input" checked />
                            <label for="pickup" class="form-check-label">PICK UP IN STORE</label>
                        </div>
                        <div class="form-check">
                            <input type="radio" id="contact" name="delivery_method" value="Contact Seller" class="form-check-input" />
                            <label for="contact" class="form-check-label">CONTACT SELLER (Preferred option for COD payment method)</label>
                        </div>
                        </div>
                    </div>
                </div>

                <div class="container my-3">
            <div class="row">
                <!-- Product Details -->
                <div class="col-lg-6 col-md-12">
                    <?php foreach ($bulk_items as $bulk): ?>
                        <div class="product-details border rounded shadow-sm p-3 d-flex flex-column" style="margin-bottom: 15px;">
                            <h2 class="text-dark mb-3" style="font-weight: bold;"><?php echo htmlspecialchars($bulk['product']); ?></h2>

                            <!-- Color Selector -->
                            <div class="selector mt-3" style="margin-bottom: 25px; margin-top: -30px;">
                                <label class="form-label">Available Colors:</label>
                                <div class="color-options d-flex flex-wrap">
                                    <select class="form-select shadow-sm" name="color_option[<?php echo $bulk['bulk_cart_id']; ?>]">
                                        <?php if (!empty($bulk_colors[$bulk['product_id']])): ?>
                                        <?php foreach ($bulk_colors[$bulk['product_id']] as $color): ?>
                                            <option value="<?php echo htmlspecialchars($color['color_name']); ?>" 
                                                    <?php echo ($color['yards'] <= 30) ? 'disabled' : ''; ?>>
                                                    <?php echo htmlspecialchars($color['color_name']); ?>
                                                    <?php echo ($color['yards'] <= 30) ? ' - Not Available' : ''; ?>
                                                </option>
                                        <?php endforeach; ?>
                                        <?php else: ?>
                                        <option>No colors available</option>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>

                            <p>
                                <h6 style="font-size: 15px; margin-top: -23px; color:#1e1e1e; text-align: justify">Type in <span style="font-weight: bold; font-size: 20px; color: #198754;">'0'</span> in the number inputs to not select per YARD or per ROLL.</h4>
                            </p>

                            <p class="mb-3">
                                <span class="font-weight-bold text-muted">PRICE: </span>
                                <span class="price text-success" id="price-per-yard-<?php echo $bulk['bulk_cart_id']; ?>">
                                    <?php echo htmlspecialchars($bulk['unit_price']); ?>
                                </span>
                                <span class="text-muted">PER YARD</span><br />
                            </p>

                            <!-- Yards Input -->
                            <div class="rolls d-flex align-items-center mb-4">
                                <span class="font-weight-bold text-muted me-3">YARDS (min. 30 & max. 50):</span>
                                <div class="quantity-container d-flex align-items-center">
                                    <input
                                        type="number"
                                        id="yards-value-<?php echo $bulk['bulk_cart_id']; ?>"
                                        class="form-control form-control-sm text-center"
                                        name="yards[<?php echo $bulk['bulk_cart_id']; ?>]"
                                        value="30"
                                        max="50"
                                        min="0"
                                        style="width: 60px; background-color: white; border: none; outline: none; text-align: center;"
                                    />
                                    <span class="ms-2">YARDS</span>
                                </div>
                            </div>

                            <p class="mb-3">
                                <span class="font-weight-bold text-muted">PRICE: </span>
                                <span class="price text-success" id="price-per-roll-<?php echo $bulk['bulk_cart_id']; ?>">
                                    <?php echo htmlspecialchars($bulk['roll_price']); ?>
                                </span>
                                <span class="text-muted">PER ROLL</span><br />
                            </p>

                            <!-- Rolls Input -->
                            <div class="rolls d-flex align-items-center mb-4">
                                <span class="font-weight-bold text-muted me-3">ROLLS (60 yards per roll):</span>
                                <div class="quantity-container d-flex align-items-center">
                                    <input
                                        type="number"
                                        id="rolls-value-<?php echo $bulk['bulk_cart_id']; ?>"
                                        class="form-control form-control-sm text-center"
                                        name="rolls[<?php echo $bulk['bulk_cart_id']; ?>]"
                                        value="0"
                                        max="10"
                                        min="0"
                                        style="width: 60px; background-color: white; border: none; outline: none; text-align: center;"
                                    />
                                    <span class="ms-2">ROLLS</span>
                                </div>
                            </div>

                            <!-- Subtotal for the product -->
                            <!--p>
                                <strong>SUBTOTAL:</strong>
                                <span id="subtotal-<?php echo $bulk['bulk_cart_id']; ?>" class="price text-success">P 0.00</span>
                            </p-->

                            <!-- Remove Button -->
                            <form method="POST" action="">
                                <input type="hidden" name="bulk_cart_id" value="<?php echo $bulk['bulk_cart_id']; ?>" />
                                <button type="submit" name="remove" class="home-btn btn btn-outline-primary btn-lg w-100" style="background-color: #901A1B; margin-top: 15px;">REMOVE</button>
                            </form>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Customer Details -->
                    <div class="col-lg-6 col-md-12">
                        <div class="details border rounded shadow-sm p-3 d-flex flex-column">
                            <h3 class="text-dark mb-3">CUSTOMER DETAILS</h3>
                            <p>
                                <strong>FULL NAME:</strong>
                                <span class="form-control-plaintext ms-3"><?php echo htmlspecialchars($profile_data['firstname'] . ' ' . $profile_data['lastname'] ?? ''); ?></span>
                            </p>
                            <p>
                                <strong>PHONE:</strong>
                                <span class="form-control-plaintext ms-3"><?php echo htmlspecialchars($profile_data['phone'] ?? ''); ?></span>
                            </p>
                            <p>
                                <strong>SHIPPING ADDRESS:</strong>
                                <span class="form-control-plaintext ms-3">
                                    <?php echo htmlspecialchars($profile_data['address'] . ' ' . $profile_data['subdivision'] . ' ' . $profile_data['barangay'] . ' ' . $profile_data['postal'] . ' ' . $profile_data['city'] . ' ' . $profile_data['place']); ?>
                                </span>
                            </p>
                            <p>
                                <strong>SHIPPING FEE:</strong>
                                <span style="color: #C08C3C;">NOT AVAILABLE</span>
                            </p>

                            <div class="buttons mt-4">
                                <h6 style="font-size: 15px; margin-top: -20px; color:#1e1e1e; padding: 5px; text-align: justify">Please make sure all your details and selected items are correct before proceeding with the request to avoid delays or issues!</h6>
                                <button class="order-btn btn btn-primary btn-lg w-100 mb-3" type="submit" name="request_bulk">START ORDER REQUEST</button>
                                <button class="contact-btn btn btn-outline-primary btn-lg w-100" type="button">CONTACT SELLER</button>
                                <button class="home-btn btn btn-outline-primary btn-lg w-100" type="button" onclick="window.location.href='homepage.php';">RETURN TO HOMEPAGE TO ORDER MORE!</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </section>
    <script src="script.js"></script>
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
