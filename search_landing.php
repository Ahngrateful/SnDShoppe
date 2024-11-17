<?php
session_start();


$servername = "localhost";
$dbname = "db_sdshoppe";
$username = "root";  
$password = "";  

try {
    // Initialize PDO connection
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

 //Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: haveacc.php"); // Redirect to login if not logged in
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link rel="icon" href="Assets/sndlo.ico">
    <link rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <title>S&D Fabrics</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Kumbh+Sans:wght@100..900&family=Playfair+Display+SC:ital,wght@0,400;0,700;0,900;1,400;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900&display=swap');

        body {
    background-color: #f1e8d9;
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

.navbar-collapse {
    display: flex;
    justify-content: center; /* Center aligns the entire navbar content */
}

.search-bar {
    max-width: 500px; 
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

h1{
    font-family: "Playfair Display SC", serif;
    font-size: 50px;
    color: #1e1e1e;
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

/* Card background for the category section */
.category-card {
    max-height: 80vh; 
    background-color: #b6b3ae; 
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15); 
    margin: 10px 0;
    padding: 20px;
    text-decoration: none;
}

/* Styling for the Category heading */
h2 {
    font-family: "Playfair Display SC", serif;
    font-weight: 600;
    font-size: 37px;
    margin: 50px 0;
    text-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    padding-top: 10px;
    padding-bottom: -5px;
}

/* Category section styling */
.category-card {
    max-height: 80vh; 
    overflow-y: auto; 
    background: radial-gradient(circle, rgba(255,241,202,1) 0%, rgba(209,194,157,1) 100%);
    padding: 20px;
    border-radius: 15px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
    margin: 20px 0;
}

/* Row to display categories in a single line */
.row {
    display: flex;
    justify-content: space-around; 
    flex-wrap: wrap; 
    gap: 15px; 
    padding: 0;
    margin: 0; 
}

/* Individual category styling */
.category {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    flex: 1 1 120px; /* Allow the items to take up space evenly */
    max-width: 120px;
    transition: transform 400ms;
}

.category:hover {
    transform: scale(1.2);
}

.category img {
    width: 150px; 
    height: 150px; 
    border-radius: 50%; 
    border: 4px solid #d9b65d; 
    object-fit: cover;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.category p {
    margin-top: 5px; 
    font-weight: bold;
    font-size: 16px; 
    font-family: "Playfair Display SC", serif;
    color: #1e1e1e;
}

.category a{
    color: #d9b65d;
    text-decoration: none;
}

.category a:hover {
    color: #d9b65d;
    text-decoration: underline #d9b65d;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .category {
        flex: 1 1 80px; 
        max-width: 80px; 
    }

    .category img {
        width: 80px;
        height: 80px; 
    }

    .category p {
        font-size: 14px; 
    }
}

/* Filter button styling */
.filter-buttons {
    display: flex;
    flex-wrap: wrap; 
    gap: 10px; 
    margin-bottom: 20px;
    justify-content: center; 
}

.filter-buttons button {
    padding: 8px 12px; 
    color: #c7a754;
    background-color: transparent;
    cursor: pointer;
    border-radius: 10px; 
    font-weight: bold;
    transition: background-color 0.3s;
    height: auto;
    min-width: 10px; 
    width: auto; 
    flex: 0 0 auto;
    border: none;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    border: 1px solid #d9b65d;
}

/* Hover effect */
.filter-buttons button:hover {
    color: #f1e8d9;
    background-color: #d9b65d; 
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .filter-buttons {
        flex-direction: column; 
        gap: 8px;
        align-items: center; 
    }

    .filter-buttons button {
        width: 150px; 
        padding: 10px; 
        font-size: 14px; 
    }
}

@media (max-width: 480px) {
    .filter-buttons button {
        width: 100%; 
        max-width: 200px; 
        padding: 12px 20px; 
        font-size: 18px; 
    }
}

/* Fabric items grid */
.fabric-items {
    display: flex;
    flex-wrap: wrap; 
    justify-content: center; 
    padding: 10px;
    max-width: 1000px; 
    margin: 0 auto; 
    row-gap: 70px;
    column-gap: 50px;
    margin-top:50px;
}

.fabric-card {
    width: 200px; 
    height: auto; 
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    transition: transform 400ms;
    border: 1px solid #d9b65d;
}

.fabric-card:hover {
    transform: scale(1.2);
}

.fabric-content {
    display: flex;
    flex-direction: column; 
    align-items: center; 
}

.fabric-card img {
    width: 100%; 
    height: auto; 
    object-fit: cover; 
    border: 1px solid #d9b65d;
}

.fabric-card p {
    margin-top: 5px;
    font-weight: bold;
    font-size: 14px;
    text-align: center; /* Center text below image */
}

.fabric-card a {
    color: #1e1e1e;
    text-decoration: none;
}

.fabric-card a:hover {
    color: #c7a754;
    text-decoration: underline;
}

        </style>
</head>
<body class="vh-100">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #f1e8d9; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <a class="navbar-brand fs-4" href="homepage.php">
                <img src="Assets/sndlogo.png" width="70px" alt="Logo"/> 
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarTogglerDemo01" aria-controls="navbarTogglerDemo01" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse justify-content-center" id="navbarTogglerDemo01">
                <form class="search-bar" name="search" role="search" method="POST" action="search_landing.php">
                    <div class="input-group">
                        <span class="input-group-text" id="basic-addon1">
                            <i class="bi bi-search search-icon"></i>
                        </span>
                        <input class="form-control" type="search" name="search_term" placeholder="Search..." aria-label="Search" aria-describedby="basic-addon1">
                        <button type="submit" name="search" style="display:none;"></button>
                    </div>
                </form>
            </div>

                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link nav-link-black active" aria-current="page" href="cart.php">
                            <img src="Assets/svg(icons)/shopping_cart.svg" alt="cart">
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-black" href="#">
                            <img src="Assets/svg(icons)/notifications.svg" alt="notif">
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link nav-link-black" href="#">
                            <img src="Assets/svg(icons)/inbox.svg" alt="inbox">
                        </a>
                    </li>

                    <!-- New Account Dropdown Menu -->
                    <li class="nav-item dropdown">
                        <a class="nav-link nav-link-black dropdown-toggle" href="#" id="accountDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="Assets/svg(icons)/account_circle.svg" alt="account">
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="accountDropdown">
                            <li>
                                <a class="dropdown-item" href="accountSettings.php">Account & Security</a>
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

    <div class="fabric-items">
     
            <div class="fabric-content">
                <?php
                if (isset($_GET['category'])) {
                    $category = $_GET['category'];
                    
                    // Prepare and execute the query to fetch products in the selected category
                    $stmt = $pdo->prepare("SELECT * FROM products WHERE category = ?");
                    $stmt->execute([$category]);
                    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    // Display products in the selected category
                    if ($products) {
                        echo "<h3>Products in Category: " . htmlspecialchars($category) . "</h3>";
                        echo '<div class="fabric-items">';
                        foreach ($products as $row) {
                            $productImage = htmlspecialchars($row['product_image']);
                            $productId = htmlspecialchars($row['product_id']);
                            $productName = htmlspecialchars($row['product_name']);
                            
                            echo '<div class="fabric-card">';
                            echo '<div class="fabric-content">';
                            echo '<a href="product.php?product_id=' . $productId . '">';
                            echo '<img src="' . $productImage . '" alt="Fabric Image" style="width: 200px; height: 200px; object-fit: cover; border-radius: 10px;">';
                            echo '</a>';
                            echo '<p>' . $productName . '</p>';
                            echo '</div>';
                            echo '</div>';
                        }
                        echo '</div>';
                    } else {
                        echo "<p>No products found in '<strong>" . htmlspecialchars($category) . "</strong>' category.</p>";
                    }
                } else {
                    echo "<p>Category not specified.</p>";
                }
                
                if (isset($_POST['search'])) {
                    // Get the search term from the form
                    $searchTerm = $_POST['search_term'];
                
                    // Prepare and execute the search query using PDO
                    $stmt = $pdo->prepare("SELECT * FROM products WHERE product_name LIKE ?");
                    $stmt->execute(["%$searchTerm%"]);
                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                    // Check if results were found
                    if (count($result) > 0) {
                        //echo "<h3>Search Results:</h3>";
                        echo '<div class="fabric-items">';
                        foreach ($result as $row) {
                            echo '<div class="fabric-card">';
                            echo '<div class="fabric-content">';
                            echo '<a href="product.php?product_id=' . htmlspecialchars($row['product_id']) . '">';
                            echo '<img src="' . htmlspecialchars($row['product_image']) . '" alt="Fabric Image" style="width: 200px; height: 200px; object-fit: cover; border-radius: 10px;">';
                            echo '</a>';
                            echo '<p>' . htmlspecialchars($row['product_name']) . '</p>';
                            echo '</div>';
                            echo '</div>';
                        }
                        echo '</div>';
                    } else {
                        echo "<p>No results found for '<strong>" . htmlspecialchars($searchTerm) . "</strong>'.</p>";
                    }
                }


                ?>
                </div>
            </div>
        </div>
    
  
</body>
</html>