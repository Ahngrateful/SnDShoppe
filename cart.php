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

//$stmtColors = $pdo->prepare('SELECT product_pic FROM product_colors WHERE product_id = :product_id');
//$stmtColors->execute(['product_id' => $product_id]);
//$product_colors = $stmtColors->fetchAll(PDO::FETCH_ASSOC);

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


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="icon" href="\SnD_Shoppe-main\PIC\sndlogo.png" type="logo">
    <title>Shopping Cart</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Kumbh+Sans:wght@100..900&family=Playfair+Display+SC:ital,wght@0,400;0,700;0,900;1,400;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900&display=swap');

body {
    background: url(/Assets/images/bgLogin.png) rgba(0, 0, 0, 0.3);
    background-blend-mode: multiply;
    background-position: center;
    background-size: cover;
    background-repeat: no-repeat;
    min-height: 100vh; 
    overflow-y: auto; 
    margin: 0; 
    padding: 150px 0 0; /* Add top padding for fixed header */
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
                        <a class="nav-link nav-link-black" href="#">
                            <img src="/SnD_Shoppe-main/Assets/svg(icons)/notifications.svg" alt="notif">
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link nav-link-black" href="#">
                            <img src="/SnD_Shoppe-main/Assets/svg(icons)/inbox.svg" alt="inbox">
                        </a>
                    </li>

                    <!-- New Account Dropdown Menu -->
                    <li class="nav-item dropdown">
                        <a class="nav-link nav-link-black dropdown-toggle" href="#" id="accountDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="/SnD_Shoppe-main/Assets/svg(icons)/account_circle.svg" alt="account">
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="accountDropdown">
                            <li>
                                <a class="dropdown-item" href="accountSettings.php">My Account</a>
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
    <div class="row">
        <!-- Cart Items -->
        <div class="col-lg-8 col-md-12">
        <form method="POST" action="">
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th scope="col">Product</th>
                        <th scope="col">Color</th>
                        <th scope="col">Unit Price</th>
                        <th scope="col">Quantity</th>
                        <th scope="col">Total Price</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <!--<img src="<php echo htmlspecialchars($item['product_pic']); ?>" alt="Product Image" width="80" class="rounded me-3"> -->
                                    <span><?php echo htmlspecialchars($item['product']); ?></span>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($item['color']); ?></td>
                            <td class="unit-price">₱<?php echo number_format($item['unit_price'], 2); ?></td>
                            <td><input type="number" class="form-control quantity-select" value="<?php echo $item['quantity']; ?>" min="1" max="9"></td>
                            <td class="item-total-price">₱<?php echo number_format($item['total_price'], 2); ?></td>
                            <td><button class="btn btn-danger btn-sm remove-item" name="remove_single" value="<?php echo $item['cart_id']; ?>" >Remove</button></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        </form>

            <!-- Order Summary -->
            <div class="col-lg-4 col-md-12">
                <div class="p-4 border rounded shadow-sm bg-light">
                    <h4 class="mb-4">Order Summary</h4>
                    <div class="d-flex justify-content-between">
                        <p>Subtotal</p>
                        <p class="subtotal fw-bold">₱<?php echo number_format($subtotal, 2); ?></p>
                    </div>
                    <div class="d-flex justify-content-between">
                        <!--p>Shipping</p>
                        <p class="fw-bold">₱40.00</p>
                    </div>
                    <div class="d-flex justify-content-between">
                        <p>Tax</p>
                        <p class="fw-bold">₱4.00</p-->
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <h5>Total</h5>
                        <h5 class="total-price fw-bold">₱0.00</h5> <!-- This will be updated by JavaScript -->
                    </div>
                    <button class="btn btn-success btn-lg w-100 mt-4" onclick="window.location.href='checkout.php'">Proceed to Checkout</button>
                    <button class="btn return-button w-100 mt-3" onclick="window.location.href='homepage.php'">Return</button>
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
    </script>

</body>

</html>
