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

$stmt = $pdo->prepare('SELECT cart_id, product, color, unit_price, quantity, total_price FROM shopping_cart WHERE customer_id = :user_id');
$stmt->execute(['user_id' => $user_id]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$profile_data = [];
$stmt = $pdo->prepare('SELECT firstname, lastname, email, phone, address FROM users_credentials WHERE id = ?');
$stmt->execute([$user_id]);
$profile_data = $stmt->fetch(PDO::FETCH_ASSOC);

$stmtSubtotal = $pdo->prepare('SELECT SUM(total_price) AS subtotal FROM shopping_cart WHERE customer_id = :user_id');
$stmtSubtotal->execute(['user_id' => $user_id]);
$result = $stmtSubtotal->fetch(PDO::FETCH_ASSOC);
$subtotal = $result['subtotal'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
      rel="stylesheet"
    />
    <link rel="stylesheet"/>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
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

/* Checkout Form */
form {
    background-color: #f1e8d9 ;
    padding: 20px;
    border-radius: 5px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

form input, form textarea {
    font-size: 16px;
}

form .btn-success {
    font-size: 18px;
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
          <img src="PIC/sndlogo.png" width="70px" alt="Logo" />
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
                <img src="/SnD_Shoppe-main/Assets/svg(icons)/notifications.svg" alt="notif" /> 
              </a>
            </li>

            <li class="nav-item">
              <a class="nav-link nav-link-black" href="#">
                <img src="/SnD_Shoppe-main/Assets/svg(icons)/inbox.svg" alt="inbox" />
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
                  src="/SnD_Shoppe-main/Assets/svg(icons)/account_circle.svg"
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
                    href="accountSettings.php"
                    >My Account</a
                  >
                </li>
                <li>
                  <hr class="dropdown-divider" />
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

    <!-- Checkout Content -->
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
                    <label class="form-label fw-bold">Shipping Address:</label>
                    <p id="address" class="form-control-plaintext ms-3">
                        <?php echo htmlspecialchars($profile_data['address'] ?? ''); ?>
                    </p>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Phone:</label>
                    <p id="contact" class="form-control-plaintext ms-3">
                        <?php echo htmlspecialchars($profile_data['phone'] ?? ''); ?>
                    </p>
                </div>
                 <!-- Shipping Option -->
                 <h4 class="mb-4">DELIVERY OPTION</h4>
                    <div class="mb-3">
                        <label for="shipping-option" class="form-label">Select Shipping Option</label>
                        <select class="form-select" id="shipping-option" required>
                        <option value="ninja-van">Ninja Van</option>
                        <option value="lbc">LBC</option>
                        <option value="j-t-express">J&T Express</option>
                        </select>
                    </div>
            </div>
        </div>
        
            
                    <!-- Payment Option 
                    <h4 class="mb-4">PAYMENT OPTION</h4>
                    <div class="mb-3">
                        <label for="payment-option" class="form-label">Select Payment Option</label>
                        <select class="form-select" id="payment-option" required>
                        <option value="gcash">GCash</option>
                        <option value="maya">Maya</option>
                        <option value="online-banking">Online Banking</option>
                        </select>
                    </div>
                    </form>
                </div>
            </div>
        
          <!-- Order Summary -->
<div class="col-lg-4 col-md-12">
    <div class="p-4 border rounded shadow-sm bg-light order-summary">
        <h4 class="mb-4">Order Summary</h4>
        <?php foreach ($cart_items as $item): ?>
            <div class="d-flex justify-content-between">
                <p><?php echo htmlspecialchars($item['product']); ?> (<?php echo htmlspecialchars($item['color']); ?>)</p>
                <p class="fw-bold"><?php echo htmlspecialchars($item['quantity']); ?></p>
            </div>
        <?php endforeach; ?>
        <hr />
        <div class="d-flex justify-content-between">
            <p>Subtotal</p>
            <p class="subtotal fw-bold">₱<?php echo number_format($subtotal, 2); ?></p>
        </div>
        <div class="d-flex justify-content-between">
            <h5>Total</h5>
            <h5 class="total-price fw-bold">₱<?php echo number_format($subtotal, 2); ?></h5>
        </div>
        <button class="btn btn-success btn-lg w-100 mt-4">Proceed to Payment
        </button>
        <button class="btn return-button w-100 mt-3" onclick="window.location.href='cart.php'">Back to Cart</button>
    </div>
</div>
      
      <script src="/Assets/js/product.js"></script>
      
  </body>
</html>