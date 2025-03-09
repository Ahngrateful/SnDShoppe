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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name = $_POST['product_name'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    $roll_price = $_POST['roll_price'];
    $colors = isset($_POST['colors']) ? $_POST['colors'] : [];

    // Check if an image is uploaded
    $product_image = null;
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "new_products/";
        $file_name = basename($_FILES['product_image']['name']);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if (in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif'])) {
            $unique_name = uniqid() . "_" . $file_name;
            $target_file = $target_dir . $unique_name;

            if (move_uploaded_file($_FILES['product_image']['tmp_name'], $target_file)) {
                $product_image = $target_file;
            } else {
                echo "Error uploading the file.";
                exit;
            }
        } else {
            echo "Invalid file type. Only JPG, JPEG, PNG, and GIF are allowed.";
            exit;
        }
    } else {
        echo "No image uploaded or upload error.";
        exit;
    }

    $conn->begin_transaction();
    try {
        // Insert the new product
        $stmt = $conn->prepare("INSERT INTO products (product_name, category, price, roll_price, product_image) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdds", $product_name, $category, $price, $roll_price, $product_image);
        $stmt->execute();
        $product_id = $conn->insert_id;

        // Insert colors
        $stmt_colors = $conn->prepare("INSERT INTO product_colors (product_id, color_name) VALUES (?, ?)");
        foreach ($colors as $color) {
            $stmt_colors->bind_param("is", $product_id, $color);
            $stmt_colors->execute();
        }

        $conn->commit();

        // Redirect to product_list.php with success message
        header("Location: product_list.php?success=true&product_id=$product_id");
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        error_log($e->getMessage());
        echo "Error adding product. Please try again.";
    }
}



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
    <link rel="stylesheet" href="add_p.css" />
    <link rel="icon" href="\SnD_Shoppe-main\PIC\sndlogo.png" type="logo" />
    <title>ADMIN | Add Product</title>
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
        <div class="col-10 p-4">
          <h1>ADD PRODUCT</h1>
          <form
          action="add_product.php"
          method="POST"
          enctype="multipart/form-data"
          class="mt-4"
          >
        
    <!-- Hidden input for product_id (for editing existing products) -->
    <input type="hidden" name="product_id" value="<?php echo $_POST['product_id'] ?? ''; ?>">


          <!-- Product Information Section -->
          <div class="section">
            <label for="productName" class="form-label"><strong>Product Name</strong></label>
          <input
            type="text"
            id="productName"
            name="product_name"
            class="form-control"
            placeholder="Enter product name"
            required
          />
      
            <div class="row">
              <div class="col">
              <label for="category" class="form-label"><strong>Category</strong></label>
              <select
                id="category"
                name="category"
                class="form-control"
                required
              >
              <option value="" disabled selected>Select a category</option>
              <option value="Beaded Lace">Beaded Lace</option>
              <option value="Corded Lace">Corded Lace</option>
              <option value="Caviar">Caviar</option>
              <option value="Candy Crush">Candy Crush</option>
              <option value="Panel">Panel</option>
              <option value="Velvet">Velvet</option>
            </select>
            </div>
              <div class="col">
              <label for="price" class="form-label"><strong>Price</strong></label>
            <input
              type="number"
              id="price"
              name="price"
              class="form-control"
              placeholder="Enter price"
              step="1.00"
              required
                />
              </div>
            </div>

            <label><strong>Description</strong></label>
            <textarea
              class="form-control"
              rows="4"
              name="description"
              placeholder="Enter description"
            ></textarea>
            
            <div>
          </div>
            <label for="price_roll" class="form-label"><strong>Price per roll</strong></label>
          <input
            type="number"
            id="roll_price"
            name="roll_price"
            class="form-control"
            placeholder="Enter price per roll"
            step="1.00"
            required
            />
            <label for="product_image" class="form-label"><strong>Insert Product Image</strong></label>
<input type="file" class="form-control" name="product_image" id="product_image" accept="image/*" required>
    
          <div class="mt-4 d-flex justify-content-between">
 
    <button type="button" class="btn btn-secondary" onclick="window.location.href='product_list.php'">Back</button>
    <div>
      <button type="submit" name="add_product" class="btn btn-primary">
        Add Product
      </button>
    </div>
  </div>
</form>
          <div class="section">
          </div>
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
            <a href="product_list.php" class="list-group-item list-group-item-action active">
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
    <script src="script.js"></script>
 <script>
  document.addEventListener("DOMContentLoaded", () => {
  const toggleButton = document.getElementById("accountDropdownToggle");
  const sidebar = document.querySelector(".sidebar");

  toggleButton.addEventListener("click", () => {
    sidebar.classList.toggle("hidden");
  });
});

</script>

  </body>
</html>
