<?php
// Start session and initialize database connection
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

if(isset($_POST['continue'])) {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $firstname = $_POST['firstname'];
        $lastname = $_POST['lastname'];

        // Use PDO to prepare and execute the statement
        $stmt = $pdo->prepare("INSERT INTO users_credentials (FIRSTNAME, LASTNAME) VALUES (:firstname, :lastname)");
        $stmt->bindParam(':firstname', $firstname);
        $stmt->bindParam(':lastname', $lastname);

        if ($stmt->execute()) {
            $user_id = $pdo->lastInsertId();  
            $_SESSION['user_id'] = $user_id;  

            header("Location: SignUp.php");
            exit();
        } else {
            echo "Error: " . $stmt->errorInfo()[2];
        }
    }  
}

$stmt = $pdo->prepare('SELECT product_id, product_image, product_name, category FROM products GROUP BY category');
$stmt->execute();
$product = $stmt->fetchAll(PDO::FETCH_ASSOC);

/*  To go to category pages kaso nageerror pa toh
if (isset($_GET['category'])) {
    $category = htmlspecialchars($_GET['category']);
    // Fetch and display products for this category from your database
    echo "<h1>Products in " . $category . " category</h1>";
    // Your product fetching logic here
} else {
    echo "Category not specified.";
} */

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link rel="icon" href="/Assets/images/sndlogo.png" type="logo">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <title>S&D FABRIC SHOPPE</title>
    <link rel="icon" href="Assets/sndlo.ico">
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Kumbh+Sans:wght@100..900&family=Playfair+Display+SC:ital,wght@0,400;0,700;0,900;1,400;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900&display=swap');

        body{
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            background-color: #FFF9E6; 
            font-family: "Playfair Display", serif;
            overflow-x: hidden;
            overflow-y: auto;
        }
        /* NAVBAR */
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

        /* ACCOUNT DROPDOWN */
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
            color: #bc9c22;
            font-weight: bold;
        }

        /* Dropdown Divider */
        .dropdown-divider {
            margin: 0;
        }

        /* WELCOME/INTRO */

        .welcome-row{
            display: flex; 
            gap: 30px;
            margin-top: -3px;
            transition-duration: 0.7s;
            max-width: autp;
            justify-items: center;
            align-content: center;
        }
        .col-1{
            width: 710px;
            padding-left: 75px;
            padding-top: 35px; 
        }
        .col-2{ 
            align-items: center;
            margin-left: 30px;
            width: auto;
        }
        .col-2 img{
            width: 710px;
            padding-top: 60px;
        }
        .col-1 h1{
            font-size: 53px;
            font-family: "Playfair Display SC", serif;
            padding-top: 90px;
            text-shadow: 1px 1px 20px #7c7c7c;
        }
        summary{
            font-size: 19px;
        }
        .col-1 button{
            display: inline-block;
            background:#bc9c22;
            color: #fff;
            padding: 8px 30px;
            border-radius: 20px;
            align-self: center;
            font-size: 20px;
            border: none;
            transition-duration: 0.4s;
            margin-left: 200px;
            margin-top: 50px;
        }
        .col-1 button:hover{
            background: #6d4a25;
            color: #fff;
        }
        .header{
            background: radial-gradient(#fff,#F9F9D5);
            /*background: #313131 url(images/bgLogin.png) left center/contain no-repeat padding-box; */
            
        }

        /* CATEGORIES */
        .fabric-items {
            display: flex;
            flex-wrap: wrap; 
            justify-content: center; 
            padding: 10px;
            max-width: 1000px; 
            margin: 0 auto; 
            gap: 20px;
        }
        .categories{ 
            margin: 10px auto;
            width: 100%;
            margin-top: 110px;
            align-items: center;
        }
        .categories h1{
            align-items: center;
        }
        .available-row{
            gap: 40px;
            display: flex;
            justify-content: center;
            align-items: center;
            vertical-align: middle;
            width: 100%;
        }
        .available-row a{
            font-size: 20px;
            text-decoration: none;
            padding: 15px;
            color: #bc9c22;
            transition-duration: 0.4s;
        }
        .available-row a:hover{
            color: #FFF9E6;
            background-color: #bc9c22;
            padding: 15px;
            font-size: 20px;
            border-radius: 5px;
        }
        .available-col{
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }
        .available-col a{
            font-size: 25px;
            padding: 10px;
            color: #1e1e1e;
        }
        .available-row h2{
            font-size: 45px;
            padding: 20px;
            margin-top: 10px;
            font-family: "Playfair Display SC", serif;
            text-shadow: 1px 1px 20px #7c7c7c;
        }
        .row {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
            padding: 20px;
            margin-top: -26px;
        }
        .categories-col {
            flex-basis: 20%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border-radius: 5px;
            border: 1px solid #7c7c7c;
            box-shadow: 0.5px 0.5px 5px #7c7c7c;
            height: auto;
            padding: 15px;
            transition: transform 400ms;
            background-color: #FFF9E6;
        }
        .categories-col:hover {
            transform: scale(1.2);
        }
        .categories-col img{
            width: 100%;
            height: 100%;
            border-radius: 5px;
        }
        .row p{
            padding: 10px;
            font-size: 20px;
            font-weight: bold;
            text-decoration: none;
            color: #6d4a25;
            transition-duration: 0.3s;
            margin-top: 10px;
            font-family: "Playfair Display SC", serif;
            padding-bottom: 5px;
            text-align: center;
        }
        .row p:hover{
            text-decoration: underline #bc9c22;
            color: #bc9c22;
        }
        .row a{
            text-decoration: none;
        }
        .btn1{
            display: inline-block;
            background:#bc9c22;
            color: #fff;
            padding: 8px 10px;
            border-radius: 10px;
            align-self: center;
            font-size: 20px;
            border: none;
            transition-duration: 0.4s;
        }
        .btn1:hover{
            background: #6d4a25;
            color: #fff;
        }

        /* ABOUT US */
        .aboutUs{
            display: flex;
            justify-content: center;
            justify-items: center;
            align-content: center;
            margin-top: 50px;
        }
        .pic-abt img{
            height: 650px;
            margin-top: 80px;
        }
        .content-abt{
            width: 45%;
            height: auto;
            flex-direction: column;
            justify-items: center;
            align-content: center;
            margin-top: 80px;
        }
        .abt1{
            justify-items: center;
            padding: 20px;
            width: 90%;
            text-align: center;
            background: #2E2E31;
            border-radius: 5px;
            box-shadow: 1px 1px 10px #7c7c7c;
        }
        .abt1 h1{
            color: #bc9c22;
            font-size: 50px;
            text-shadow: 1px 1px 25px rgb(32, 32, 32);
        }
        .abt1 strong{
            font-size: 25px;
            color: #FFF9E6;
            text-shadow: 1px 1px 15px rgb(32, 32, 32);
        }
        .abt1 cite{
            font-size: 15px;
            color: white;
            text-shadow: 1px 1px 15px rgb(32, 32, 32);
        }
        .abt1 button{
            display: inline-block;
            background:#bc9c22;
            color: #fff;
            padding: 8px 30px;
            border-radius: 10px;
            align-self: center;
            font-size: 20px;
            transition-duration: 0.4s;
        }
        .abt1 button:hover{
            background: #6d4a25;
            color: #fff;
        }
        .abt2{
            justify-items: center;
            padding: 20px;
            text-align: center;
            width: 90%;
            margin-top: 30px;
            border: 1px solid #1e1e1e;
            border-radius: 5px;
            box-shadow: 1px 1px 5px #7c7c7c;
        }
        .abt2 h1{
            color: #1e1e1e;
            font-size: 40px;

        }
        .contact-item svg{
            width: 25px;
            margin-right: 5px;
            color: #bc9c22;
        }
        .contact-item a{
            color: #bc9c22;
        }
        .contact-item a:hover{
            color:#6d4a25;
            text-decoration: underline #6d4a25;
        }

        /* PRE-SIGN UP */
        .presignup {
            display: flex;
            padding: 60px;
            padding-top: 130px;
            padding-bottom: 120px;
            background-image: url(PIC/bgLogin.png);
            height: auto; 
            box-shadow: inset 0px 0px 10px #000000;
            align-items: start;
        }
        .presignup-container {
            background-color: #282727;
            color: #fff;
            padding: 30px;
            border-radius: 5px;
            box-shadow: 1px 1px 15px #7c7c7c;
            height: auto;
            width: 50%;
            margin-left: 30px;
            margin-top: auto;
        }
        .presignup-container img{
            margin-bottom: 10px;
        }
        .presignup-container h2 {
            text-align: center;
            margin-bottom: 20px;
            margin-top: 20px;
        }

        .input-group {
            margin-bottom: 15px;
        }

        .input-group label {
            display: block;
            margin-bottom: 5px;
        }

        .input-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;   
        }
        .presignup-container a{
            color: white;
            text-decoration: none; 
        }
        .underline-link {
            color: #bc9c22;
        }
        .underline-link:hover {
            text-decoration: underline;
        }
        .btn {
            width: 25%;
            padding: 10px;
            background-color: #282727;
            color: white;
            border: 2px solid #000000; 
            border-radius: 5px;
            cursor: pointer;
            margin-top: 7px;   
        }
        .btn:hover {
            background-color: #bc9c22;       
        }
        .presignup-abt {
            width: 60%;
            display: flex;
            flex-direction: column;
        }
        .presignup-abt h2 {
            font-size: 50px;
            text-align: center;
            text-shadow: 1px 1px 10px #7c7c7c;
            font-family: "Playfair Display SC", serif;
            margin-top: 100px;
        }
        html {
        scroll-behavior: smooth;
        }
    </style>
    
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #f1e8d9; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <a class="navbar-brand fs-4" href="#">
                <img src="Assets/sndlogo.png" width="70px" alt="Logo"/>
            </a>

            <!--button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarTogglerDemo01" aria-controls="navbarTogglerDemo01" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!- idk the function so remove ko muna for the meantime
            <div class="collapse navbar-collapse" id="navbarTogglerDemo01">
                <div class="mx-auto d-flex justify-content-center flex-grow-1">
                    <form class="search-bar" name="search" role="search" method="POST" action="">
                        <div class="input-group">
                            <span class="input-group-text" name="search" id="basic-addon1">
                                <i class="bi bi-search search-icon"></i>
                            </span>
                            <input class="form-control" type="search" placeholder="Search..." aria-label="Search" aria-describedby="basic-addon1">
                        </div>
                    </form>
                </div> ->

                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link nav-link-black active" aria-current="page" href="cart.html">
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

                    <!- New Account Dropdown Menu -->
                    <li class="nav-item dropdown">
                        <a class="nav-link nav-link-black dropdown-toggle" href="#" id="accountDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="Assets/svg(icons)/account_circle.svg" alt="account">
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="accountDropdown">
                            <!--inalis ko yung settings-->
                            <!--<li>
                                <a class="dropdown-item" href="settings.html">Settings</a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li> -->
                            <li>
                                <a class="dropdown-item text-danger" href="haveacc.php">Login</a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!--WELCOME/INTRO-->
    <div class="welcome-row">
        <div class="col-1">
            <h1>Where Quality Fabrics<br>Meet Creativity</h1>
            <summary style="list-style-type: none;font-weight: lighter;">Discover a world of premium fabrics tailored to inspire your creative journey. Whether you’re crafting clothing, home décor, or unique accessories, we’re here to provide the finest materials to bring your ideas to life.</summary>
            <form action="#categories">
                <button type="submit">Explore Now!</button>
            </form>
        </div>
        <div class="col-2">
            <img src="Assets/intro-pic.png" alt="whole-pic">
        </div>
    </div>

    <!--CATEGORIES-->
    <div class="categories" id="categories">
        <div class="available-row" style="background: linear-gradient(360deg, rgba(234,217,149,0.6694327389158788) -84%, rgba(255,255,255,0) 100%); height: 100px; box-shadow: -1px 4px 5px rgba(0, 0, 0, 0.15);">
            <h2>Available Fabrics</h2>
        </div>

        <div class="available-row" style="margin-bottom: 20px; margin-top: 20px;">
            <a href="url">All Fabrics</a>
            <a href="url">New Arrivals</a>
            <a href="url">Popular</a>
        </div>

        <div class="row">

            <?php 
            $count = 0;
            foreach ($product as $item):    
                if ($count >= 4) break;
            ?>
                <div class="categories-col">
                    <a href="dupesearchlanding.php?category=<?= urlencode($item['category']) ?>">
                        <img src="<?= htmlspecialchars($item['product_image']) ?>" alt="Fabric Image" >
                        <p><?= htmlspecialchars($item['category']) ?></p>
                    </a>
                </div>
            <?php 
                $count++;
            endforeach; 
            ?>
    
        </div> 
    </div>

    <!--ABOUT US-->
    
    <div class="aboutUs" style="background: linear-gradient(180deg, rgba(234,217,149,0.6694327389158788) -84%, rgba(255,255,255,0) 100%); height: auto; box-shadow: -1px -4px 5px rgba(0, 0, 0, 0.15);">
        <div class="pic-abt">
            <img src="Assets/aboutUs-pic.png">
        </div>
        <div class="content-abt">
            <div class="abt1">
                <h1>ABOUT US</h1>
                <strong>From Classic to Trendy, We Have the<br>Fabric You Need!</strong>
                <cite><br>Lorem ipsum dolor sit amet. Ad architecto iusto ut quasi voluptatum<br> in maiores ipsum. Qui illum enim et quaerat velit quo temporibus<br> aperiam et consectetur modi et cupiditate numquam ex expedita omnis.
                <br><br></cite>
                <form action="haveacc.php">
                    <button type="submit">Shop Now!</button>
                </form>
            </div>

            <div class="abt2">
                <h1>CONTACT US</h1>
                <div class="contact-item">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><!--!Font Awesome Free 6.6.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path fill="#202020" d="M64 32C28.7 32 0 60.7 0 96V416c0 35.3 28.7 64 64 64h98.2V334.2H109.4V256h52.8V222.3c0-87.1 39.4-127.5 125-127.5c16.2 0 44.2 3.2 55.7 6.4V172c-6-.6-16.5-1-29.6-1c-42 0-58.2 15.9-58.2 57.2V256h83.6l-14.4 78.2H255V480H384c35.3 0 64-28.7 64-64V96c0-35.3-28.7-64-64-64H64z"/></svg>
                    <a href="https://www.facebook.com/nasfia.batuaanman" target="_blank" rel="noopener noreferrer">https://www.facebook.com/nasfia.batuaanman</a>
                </div>
                <div class="contact-item" style="margin-top: 10px;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.6.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path fill="#202020" d="M164.9 24.6c-7.7-18.6-28-28.5-47.4-23.2l-88 24C12.1 30.2 0 46 0 64C0 311.4 200.6 512 448 512c18 0 33.8-12.1 38.6-29.5l24-88c5.3-19.4-4.6-39.7-23.2-47.4l-96-40c-16.3-6.8-35.2-2.1-46.3 11.6L304.7 368C234.3 334.7 177.3 277.7 144 207.3L193.3 167c13.7-11.2 18.4-30 11.6-46.3l-40-96z"/></svg>
                    <a href="tel:+639183116920" target="_blank" rel="noopener noreferrer">+639183116920</a>
                </div>
                <div class="contact-item" style="margin-top: 10px;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.6.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M48 64C21.5 64 0 85.5 0 112c0 15.1 7.1 29.3 19.2 38.4L236.8 313.6c11.4 8.5 27 8.5 38.4 0L492.8 150.4c12.1-9.1 19.2-23.3 19.2-38.4c0-26.5-21.5-48-48-48L48 64zM0 176L0 384c0 35.3 28.7 64 64 64l384 0c35.3 0 64-28.7 64-64l0-208L294.4 339.2c-22.8 17.1-54 17.1-76.8 0L0 176z"/></svg>
                    <a href="mailto:dmangadang@yahoo.com" target="_blank" rel="noopener noreferrer">dmangadang@yahoo.com</a>
                </div>
            </div>
        </div>
    </div>

    <!--SHADOW-->
    <div class="available-row" style="background: linear-gradient(360deg, rgba(234,217,149,0.6694327389158788) -84%, rgba(255,255,255,0) 100%); height: 100px; box-shadow: -1px 4px 5px rgba(0, 0, 0, 0.15);"></div>

      <!--PRE-SIGN UP-->
    <div class="presignup">
        <div class="presignup-container">
            <form action="" method="POST">
                <img src="Assets/sndlogo-wShadow.png" width="80px" alt="logo"/>
                <div class="input-group">
                <label for="firstName">First Name</label>
                <input type="text" name="firstname" id="fName" required>
            </div>
            <div class="input-group">
                <label for="lastName">Last Name</label>
                <input type="text" name="lastname" id="lName" required>
            </div>
            
            <button type="submit" class="btn" name="continue" required>Register Now</button></br></br>
                        
                <a href="haveacc.php">HAVE AN ACCOUNT?<span class="underline-link"><br>LOGIN HERE</span> </a> 
            </form>
        </div>
        <div class="presignup-abt">
            <h2>Register Today to Experience the Finest Fabrics with S&D!</h2>
        </div>
    </div>
<script>
    document.querySelector('a[href="#categories"]').addEventListener('click', function (e) {
    e.preventDefault();
    document.querySelector('#categories').scrollIntoView({
        behavior: 'smooth'
    });
});
</script>
</body>
</html>
