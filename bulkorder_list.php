<?php
session_start();
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "db_sdshoppe";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
  header("Location: haveacc.php"); // Redirect to login if not logged in
  exit;
}

$admin_id = $_SESSION['admin_id'];
// Prepare and bind statement
$stmt = $conn->prepare("SELECT name, position, pics FROM admins WHERE adminID = ?");
$stmt->bind_param("i", $admin_id); 
$stmt->execute();

// Fetch result
$result = $stmt->get_result();
$admin = $result->fetch_assoc(); 

$sql = "SELECT bulk_order_id, customer_id, grand_total, delivery_method, delivery_date, status, order_date FROM bulk_order_details";
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
    <link rel="stylesheet" href="/admin_pbl/orders_l.css" />
    <link rel="icon" href="\SnD_Shoppe-main\PIC\sndlogo.png" type="logo" />
    <title>ADMIN | Order List</title>
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
    font-family: "Playfair Display", serif;
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

  .hidden {
  display: none;
}

    </style>
  </head>
  <body class="vh-100">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg">
      <div
        class="container-fluid d-flex justify-content-between align-items-center"
      >
        <a class="navbar-brand fs-4" href="/pages/homepage.html">
          <img src="/SnD_Shoppe-main/PIC/sndlogo.png" width="70px" alt="Logo" /> 
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
        
            <img src="<?= htmlspecialchars($admin['pics']) ?>" alt="admin" style="width: 50px; height:45px;  border: 2px solid ; border-radius: 50%; margin-left:1250px;">
              <div class="text-center">
                <a
                  class="nav-link nav-link-black"
                  href="#"
                  role="button"
                  data-bs-toggle="dropdown"
                  aria-expanded="false"
                >
                  <div><?php echo htmlspecialchars($admin['name']); ?></div>
                  <div class="text-muted" style="font-size: 0.85em"><?php echo htmlspecialchars($admin['position']); ?></div>
                </a>
              </div>
              <a
                class="nav-link nav-link-black ms-2"
                href="#"
                id="accountDropdownToggle"
                role="button"
                
                aria-expanded="false"
              >
                <i class="bi bi-list"></i>
              </a>

          </ul>
        </div>
      </div>
    </nav>

    <!-- Sidebar and Content Area -->
    <div class="container-fluid">
      <div class="row vh-100">
        <!-- Main Content Area -->
        <div class="col-10 main-content p-4">
          <div class="container">
            <h1 class="mb-2">Bulk Order List</h1>
            <div class="d-flex justify-content-between mb-3">
            <div class="search-bar">
  <div class="input-group">
    <span class="input-group-text" id="basic-addon1">
      <i class="bi bi-search search-icon"></i>
    </span>
    <input type="search" 
      class="form-control" 
      placeholder="Search..." 
      id="orderSearchInput" 
      onkeyup="filterOrders()" />
  </div>
</div>

              <div class="dropdown">
                <button
                  class="btn btn-secondary dropdown-toggle"
                  type="button"
                  id="statusFilterDropdown"
                  data-bs-toggle="dropdown"
                  aria-expanded="false"
                >
                  Filter by Status
                </button>
                <ul class="dropdown-menu" aria-labelledby="statusFilterDropdown">
                  <li><a class="dropdown-item" href="#" onclick="statusFilterDropdown('All')">All</a></li>
                  <li><a class="dropdown-item" href="#" onclick="statusFilterDropdown('To Pay')">To Pay</a></li>
                  <li><a class="dropdown-item" href="#" onclick="statusFilterDropdown('To Ship')">To Ship</a></li>
                  <li><a class="dropdown-item" href="#" onclick="statusFilterDropdown('To Receive')">To Receive</a></li>
                  <li><a class="dropdown-item" href="#" onclick="statusFilterDropdown('Completed')">Completed</a></li>
                </ul>
              </div>
              <button id="exportPdf" class="btn btn-warning">
                Export to PDF
              </button>
            </div>

            <!-- Order List Table -->
            <div class="table-responsive">
              <table class="table table-bordered text-center order-table">
                <thead class="table-light">
                  <tr>
                    <th scope="col">Order ID</th>
                    <th scope="col">Customer ID</th>
                    <th scope="col">Price</th>
                    <th scope="col">Delivery Method</th>
                    <th scope="col">Delivery Date</th>
                    <th scope="col">Status</th>
                    <th scope="col">Date</th>
                    
                  </tr>
                </thead>
                <tbody>
                <?php
if ($result->num_rows > 0) {
    // Output data for each row
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td><a href='bulkorder_details.php?bulk_order_id=" . urlencode($row['bulk_order_id']) . "'>" . htmlspecialchars($row['bulk_order_id']) . "</a></td>";
        echo "<td>" . htmlspecialchars($row['customer_id']) . "</td>";
        echo "<td>₱" . number_format($row['grand_total'], 2) . "</td>";
        echo "<td>" . htmlspecialchars($row['delivery_method']) . "</td>";
        echo "<td>" . htmlspecialchars($row['delivery_date']) . "</td>";

        // Format status with badges
        $status_class = match ($row['status']) {
            'To Pay' => 'bg-warning',
            'To Ship' => 'bg-warning',
            'To Receive' => 'bg-warning',
            'Completed' => 'bg-primary',
            default => 'bg-secondary',
        };
        echo "<td><span class='badge $status_class'>" . htmlspecialchars($row['status']) . "</span></td>";
        echo "<td>" . htmlspecialchars($row['order_date']) . "</td>";
        

        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='8'>No orders found.</td></tr>";  // Updated colspan to match total columns
}
?>
                </tbody>
            </table>
        </div>
        <button id="back" class="btn btn-warning" onclick="window.location.href='orders.php';">
            Back
        </button>
    </div>
        </div>

        <!-- Sidebar on the Right -->
        <div class="col-2 sidebar p-3 hidden">
          <div class="list-group">
            <a href="landing.php" class="list-group-item">
              <img
                src="/SnD_Shoppe-main/Assets/svg(icons)/speedometer.svg"
                alt="Dashboard Icon"
                class="sidebar-icon"
              />
              Dashboard
            </a>
            <a></a>
            <a href="product_list.php" class="list-group-item">
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
              class="list-group-item list-group-item-action active"
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
            <!-- Logout-->
            <a></a>
            <a
              href="logout.php"
              class="list-group-item list-group-item-action"
            >
            <img
                src="\SnD_Shoppe-main\Assets\svg(icons)\logout.png"
                alt="User Icon"
                class="sidebar-icon"
                style="width: 20px; height: 20px;"
              />
              <span style = "color: #dc3545; font-weight: bold;">
              Logout </span>
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Include jsPDF for PDF export 
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script>
      // Code for Export to PDF functionality
      const { jsPDF } = window.jspdf;
      document.getElementById("exportPdf").addEventListener("click", () => {
        const doc = new jsPDF();
        doc.html(document.querySelector(".table-responsive"), {
          callback: function (doc) {
            doc.save("order-list.pdf");
          },
        });
      });
    </script>-->

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>

<script> //css jaVA SCRIPT
document.addEventListener("DOMContentLoaded", () => {
  const toggleButton = document.getElementById("accountDropdownToggle");
  const sidebar = document.querySelector(".sidebar");

  toggleButton.addEventListener("click", () => {
    sidebar.classList.toggle("hidden");
  });
});
</script>
<script>
  document.getElementById("exportPdf").addEventListener("click", () => {
    // Initialize jsPDF
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();

    // Define the HTML table to export
    const table = document.querySelector(".order-table");

    // Use autoTable plugin to export the table
    doc.autoTable({
      html: table, // Target the HTML table
      theme: 'grid', // Choose a theme (default, grid, strip, plain)
      styles: {
        fontSize: 10, // Adjust font size if needed
        cellPadding: 4, // Adjust cell padding
      },
      headStyles: {
        fillColor: [220, 220, 220], // Light gray background for headers
      },
    });

    // Save the PDF
    doc.save("bulk-order-list.pdf");
  });

function filterOrders() {
  let input = document.getElementById('orderSearchInput').value.toLowerCase();
  let orders = document.querySelectorAll('tbody tr');

  orders.forEach((order) => {
    let orderName = order.querySelector('td').textContent.toLowerCase();
    
    if (orderName.includes(input)) {
      order.style.display = '';
      order.style.backgroundColor = 'yellow'; // Highlight the matching orders
    } else {
      order.style.display = 'none';
      order.style.backgroundColor = ''; // Remove highlight from non-matching orders
    }
  });
}

function statusFilterDropdown(status) {
  let rows = document.querySelectorAll('tbody tr');

  rows.forEach((row) => {
    let orderStatus = row.querySelector('td:nth-child(6)').textContent.trim(); // Added .trim()

    if (status === 'All' || orderStatus === status) {
      row.style.display = '';
    } else {
      row.style.display = 'none';
    }
  });
}
</script>

  </body>
</html>
