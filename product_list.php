<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "db_sdshoppe");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch products
$sql = "SELECT product_id, product_name, price, quantity, status FROM products";

$result = $conn->query($sql);

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
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css"
    />
    <link rel="stylesheet" href="/admin_pbl/style.css" />
    <link rel="icon" href="\SnD_Shoppe-main\PIC\sndlogo.png" type="logo" />
    <title>ADMIN | Product List</title>
    <style>
       @import url('https://fonts.googleapis.com/css2?family=Kumbh+Sans:wght@100..900&family=Playfair+Display+SC:ital,wght@0,400;0,700;0,900;1,400;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900&display=swap');

body {
    background: url(/Assets/images/) rgba(0, 0, 0, 0.3);
    background-blend-mode: multiply;
    background-position: center;
    background-size: cover;
    background-repeat: no-repeat;
    min-height: 100vh; 
    overflow-y: auto; 
    margin: 0; 
    padding: 0; 
}

/* Navbar and Dropdown Styling */
.navbar {
    background-color: #f1e8d9;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
}

.nav-link-black {
    color: #1e1e1e !important;
}

.nav-link-black:hover {
    color: #e044a5;
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

.search-bar .input-group {
  border-radius: 20px; 
  overflow: hidden; 
}

.search-bar .input-group-text {
  background-color: #f1e8d9;
  border: 1px solid #d9b65d;
  border-radius: 20px 0 0 20px; 
}

.search-bar .form-control {
  border: 1px solid #d9b65d;
  border-radius: 0 20px 20px 0; 
}


h1{
    font-family: "Playfair Display SC", serif;
    font-size: 50px;
    color: #1e1e1e;
}

.dropdown-item:hover {
    background-color: #f1e8d9;
}

.dropdown-item.text-danger {
    color: #dc3545;
    font-weight: bold;
}

.dropdown-divider {
    margin: 0;
}


/* Cards */
.card {
    background-color: #faf4e1;
    border: 1px solid #d1b88a;
    border-radius: 5px;
    font-weight: bold;
}

/* Sidebar Styling */
.sidebar {
    background-color: #f1e8d9;
    padding: 1.5rem;
    position: relative;
    z-index: 1;
}

/* List Group Item Styling */
.list-group-item {
    color: #1e1e1e;
    background-color: #f1e8d9;
    border: 2px solid #d9b65d;
    border-radius: 8px;
    font-size: 14px;
    padding: 8px 16px;
    margin-bottom: 8px; 
    transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
    position: relative;
    z-index: 2; 
}

/* List Group Item Hover */
.list-group-item:hover {
    background-color: #e2d1b3;
    color: #1e1e1e;
    cursor: pointer;
    border-color: #d9b65d;
}


.list-group-item.active {
    background-color: #e2d1b3;
    color: #1e1e1e;
    border-color: #d9b65d;
}


.list-group-item:not(.active):active {
    background-color: #f1e8d9 !important; 
    color: #1e1e1e;
    border-color: #d9b65d;
}

.list-group-item:focus, .list-group-item:active {
    outline: none; 
    box-shadow: none; 
}

.list-group-item:not(.active):focus {
    background-color: #f1e8d9;  
    color: #1e1e1e;
    border-color: #d9b65d;
}

/* Handle active item focus */
.list-group-item.active:focus {
    background-color: #e2d1b3;
    color: #1e1e1e;
    border-color: #d9b65d;
}

.list-group-item:active {
    background-color: #e2d1b3;
    color: #1e1e1e;
    border-color: #d9b65d;
}






    
/* Media Query for Sidebar Responsiveness */
@media (max-width: 768px) {
    .sidebar {
        padding: 1rem;
    }

    .sidebar h5 {
        font-size: 1.1em;
    }

    .list-group-item {
        font-size: 13px;
    }
}

.col-10.p-4 {
    flex: 1;
    padding: 20px;
    background-color: #FFF8DC;
}

.col-10 p-4 h1 {
    font-size: 24px;
    margin-bottom: 15px;
    color: #4b4b4b;
}

.add-new {
    background-color: #ffecd0;
    
}

.product-search{
  display: flex;
justify-content: flex-end; 
gap: 10px; 
margin-bottom: 20px;
}

.product-table {
    width: 100%;
    border-collapse: collapse;
}

.product-table th, .product-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

.product-table th {
    background-color: #fdf6e0;
}

.product-table tbody tr:nth-child(odd) {
    background-color: #f0e2c4;
}

.product-table tbody tr:nth-child(even) {
    background-color: #fdf6e0;
}

.container_addproduct{
    width: 600px;
    background-color: #fdf2d9;
    padding: 20px;
    align-items: center; 
    justify-content: center; 
    border-radius: 10px;
    max-width: 800px;margin: auto;
  }
  
  .color-boxes d-flex gap-3 {
    display: flex;
    gap: 10px;
    margin-bottom: 16px;
  }

  h1 {
    font-size: 24px;
    margin-bottom: 20px;
    text-transform: uppercase;
    font-family: "Playfair Display SC", serif;
    font-size: 50px;
    color: #1e1e1e;
  }
  
  .section {
    background-color: #fef8e6;
    
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
  }
/* 
  .buttons d-flex gap-3 {
    background-color: #d9b65d;
    color: #ffffff;
    font-size: 1rem;
    font-weight: bold;
    padding: 12px 20px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    width: 100%;
    margin-top: 10px;
}

.button:hover {
    background-color: #c49b50;
}

button:active {
    background-color: #b08847;
}  
*/
button {
    background-color: #d9b65d;
    color: #ffffff;
    font-size: 1rem;
    font-weight: bold;
    padding: 12px 20px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    margin-top: 10px;
}

button:hover {
    background-color: #c49b50;
}

button:active {
    background-color: #b08847;
}

/* Center align the buttons within the container */
.buttons {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.buttons .btn {
    width: auto;
}

/* Lower-right align */
.buttons .btn-outline-secondary,
.buttons .btn-primary {
    margin-left: 10px; /* Optional: add a small gap between the Save and Add buttons */
}
 
    </style>
  </head>
  <body class="vh-100">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg">
      <div
        class="container-fluid d-flex justify-content-between align-items-center"
      >
        <!-- Logo -->
        <a class="navbar-brand fs-4" href="homepage.html">
          <img src="\SnD_Shoppe-main\PIC\sndlogo.png" width="70px" alt="Logo" />
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
        
          <ul class="navbar-nav ms-auto mb-2 mb-lg-0 d-flex align-items-center">
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
            <!-- Owner Dropdown with Admin Text Below -->
            <li class="nav-item dropdown d-flex align-items-center">
              <a
                class="nav-link nav-link-black"
                href="#"
                id="accountDropdown"
                role="button"
                data-bs-toggle="dropdown"
                aria-expanded="false"
              >
                <img
                  src="/SnD_Shoppe-main/Assets/svg(icons)/account_circle.svg"
                  alt="account"
                  class="me-2"
                />
              </a>
              <div class="text-center">
                <a
                  class="nav-link nav-link-black"
                  href="#"
                  role="button"
                  data-bs-toggle="dropdown"
                  aria-expanded="false"
                >
                  <div>OWNER</div>
                  <div class="text-muted" style="font-size: 0.85em">Admin</div>
                </a>
              </div>
              <!-- Hamburger Icon for Dropdown -->
              <a
                class="nav-link nav-link-black ms-2"
                href="#"
                id="accountDropdownToggle"
                role="button"
                data-bs-toggle="dropdown"
                aria-expanded="false"
              >
                <i class="bi bi-list"></i>
              </a>
              <!-- Dropdown Menu -->
              <ul
                class="dropdown-menu dropdown-menu-end"
                aria-labelledby="accountDropdown"
              >
                <li>
                  <a
                    class="dropdown-item"
                    href="/pages/myAccountPage/myPurchase.html"
                  >
                    My Account
                  </a>
                </li>
                <li><hr class="dropdown-divider" /></li>
                <li>
                  <a class="dropdown-item text-danger" href="#">Logout</a>
                </li>
              </ul>
            </li>
          </ul>
        </div>
      </div>
    </nav>

    <!-- Sidebar and Content Area -->
    <div class="container-fluid">
      <div class="row vh-100">
        <!-- Main Content Area -->
        <div class="col-10 p-4">
          <h1>Product List</h1>
          <!-- Product Search and Add Button -->
          <div class="d-flex justify-content-between mb-4">
          <div class="search-bar">
            <div class="input-group">
              <span class="input-group-text" id="basic-addon1">
                <i class="bi bi-search search-icon"></i>
              </span>
              <input type="search" 
              class="form-control" 
              placeholder="Search..." 
              id="searchInput" 
              onkeyup="filterProducts()" />
            </div>
          </div>
          <div class="dropdown">
          <button class="btn btn-secondary dropdown-toggle" type="button" id="stockFilterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            Filter by Stock
          </button>
          <ul class="dropdown-menu" aria-labelledby="stockFilterDropdown">
            <li><a class="dropdown-item" href="#" onclick="filterStockStatus('All')">All</a></li>
            <li><a class="dropdown-item" href="#" onclick="filterStockStatus('In Stock')">In Stock</a></li>
            <li><a class="dropdown-item" href="#" onclick="filterStockStatus('Out of Stock')">Out of Stock</a></li>
          </ul>
        </div>


            <button
              class="btn btn-primary"
              onclick="window.location.href='add_product.php'"
            >
              + ADD NEW
            </button>
          </div>

          <div class="card mt-4">
            <div class="card-body">
              <h5 class="card-title">Available Products</h5>
              <table class="table table-bordered">
                <thead>
                  <tr>
                    <th>PRODUCT</th>
                    <th>PRODUCT ID</th>
                    <th>PRICE</th>
                    <th>QUANTITY</th>
                    <th>STOCK</th>
                    
                    <th>ACTION</th>
                  </tr>
                </thead>
                <tbody>
                <?php
                // Dynamically populate product rows
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        // Determine stock status based on quantity
                        $status = $row['quantity'] > 0 ? 'In Stock' : 'Out of Stock';

                        echo "<tr>
                                <td>" . htmlspecialchars($row['product_name']) . "</td>
                                <td>{$row['product_id']}</td>
                                <td>₱" . number_format($row['price'], 2) . "</td>
                                <td>{$row['quantity']}</td>
                                <td>{$status}</td>
                                <td>
                                    <a href='edit_product.php?id={$row['product_id']}' class='btn btn-warning btn-sm'>Edit</a>
                                    <a href='delete_product.php?id={$row['product_id']}' class='btn btn-danger btn-sm'
                                      onclick='return confirm(\"Are you sure you want to delete this product?\");'>Delete</a>
                                </td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='6' class='text-center'>No products found</td></tr>";
                }
                ?>
              </tbody>

              </table>
            </div>
          </div>
        </div>
        <!-- Sidebar -->
        <div class="col-2 sidebar p-3">
          <div class="list-group">
            <a href="index.php" class="list-group-item">
              <img
                src="/SnD_Shoppe-main/Assets/svg(icons)/speedometer.svg" 
                alt="Dashboard Icon"
                class="sidebar-icon"
              />
              Dashboard
            </a>
            <a></a>
            <a href="product_list.php" class="list-group-item active">
              <img
                src="/SnD_Shoppe-main/Assets/svg(icons)/basket.svg"
                alt="Product Icon"
                class="sidebar-icon"
              />
              Product
            </a>
            <a></a>
            <a
              href="orders.php"
              class="list-group-item list-group-item-action"
            >
              <img
                src="/SnD_Shoppe-main/Assets/svg(icons)/bag-fill.svg"
                alt="Order Icon"
                class="sidebar-icon"
              />
              Order
            </a>
            <a></a>
            <a
              href="user_list.php"
              class="list-group-item list-group-item-action"
            >
              <img
                src="/SnD_Shoppe-main/Assets/svg(icons)/person-fill.svg"
                alt="User Icon"
                class="sidebar-icon"
              />
              User List
            </a>
          </div>
        </div>
      </div>
    </div>

          <script>
      function filterProducts() {
        let input = document.getElementById('searchInput').value.toLowerCase();
        let products = document.querySelectorAll('tbody tr');
        
        products.forEach((product) => {
          let productName = product.querySelector('td').textContent.toLowerCase();
          
          if (productName.includes(input)) {
            product.style.display = '';
            product.style.backgroundColor = 'yellow'; // Highlight the matching products
          } else {
            product.style.display = 'none';
            product.style.backgroundColor = ''; // Remove highlight from non-matching products
          }
        });
      }
      </script>
<script>
function filterStockStatus(status) {
  let products = document.querySelectorAll('tbody tr');

  products.forEach((product) => {
    let stockStatus = product.querySelector('td:nth-child(5)').textContent;

    if (status === 'All' || stockStatus === status) {
      product.style.display = '';
    } else {
      product.style.display = 'none';
    }
  });
}
</script> 
  </body>
</html>