<?php
//database
session_start();
$servername = "localhost";
$dbname = "db_sdshoppe";
$username = "root";  
$password = "";  

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if(isset($_POST['continue'])) {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $firstname = $_POST['firstname'];
        $lastname = $_POST['lastname'];

        $stmt = $conn->prepare("INSERT INTO users_credentials (FIRSTNAME, LASTNAME) VALUES (?, ?)");
        $stmt->bind_param("ss", $firstname, $lastname);  // "ss" means two string parameters
        
        if ($stmt->execute()) {
            $user_id = $conn->insert_id;  
            $_SESSION['user_id'] = $user_id;  

            header("Location: SignUp.php");
            exit();
            //echo "<script>window.location.href = 'SignUp.php?';</script>";
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    }    
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link rel="icon" href="/Assets/sndlogo.png" type="logo">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <title>S&D FABRIC SHOPPE</title>
    <link rel="icon" href="Assets/sndlo.ico">
    <link rel="stylesheet" href="\SnD_Shoppe-main\sndLandingpage-style.css"> 
    
</head>
<body>
    <!--NAVBAR-->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #f1e8d9; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);">
        <div class="container-fluid d-flex" style="justify-content:between; align-items:center;">
            <a class="navbar-brand fs-4" href="#">
                <img src="Assets/sndlogo.png" width="70px" alt="Logo"/>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarTogglerDemo01" aria-controls="navbarTogglerDemo01" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarTogglerDemo01">
                <div class="mx-auto d-flex" style="justify-content:center; flex-grow:1">
                    <form class="search-bar" role="search">
                        <div class="input-group">
                            <span class="input-group-text" id="basic-addon1">
                                <i class="bi bi-search search-icon"></i>
                            </span>
                            <input class="form-control" type="search" placeholder="Search..." aria-label="Search" aria-describedby="basic-addon1">
                        </div>
                    </form>
                </div>

                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link nav-link-black active" aria-current="page" href="#">
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
                            <img src="Assets/svg(icons)/inbox.svg" alt="cart">
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-black" href="#">
                            <img src="Assets/svg(icons)/account_circle.svg" alt="cart">
                        </a>
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
            <a href="" class="btn">Explore Now &#8594;</a>
        </div>
        <div class="col-2">
            <img src="Assets/intro-pic.png" alt="whole-pic">
        </div>
    </div>

    <!--CATEGORIES-->
    <div class="categories">

        <div class="available-row" style="background: linear-gradient(360deg, rgba(234,217,149,0.6694327389158788) -84%, rgba(255,255,255,0) 100%); height: 100px; box-shadow: -1px 4px 5px rgba(0, 0, 0, 0.15);">
            <h2>Available Fabrics</h2>
        </div>
        <div class="available-row" style="margin-bottom: 20px; margin-top: 20px;">
            <a href="url">New Arrivals</a>
            <a href="url">Popular</a>
            <a href="url">Order Here</a>
        </div>

        <div class="row">
            <div class="categories-col">
                <img src="Assets/fabrics-edited/design1.png">
                <a href="url">Beaded Lace</a>
                <p>info info info</p>
            </div>
            <div class="categories-col">
                <img src="Assets/fabrics-edited/design2.png">
                <a href="url">Lace</a>
                <p>info info info</p>
            </div>
            <div class="categories-col">
                <img src="Assets/fabrics-edited/design3.png">
                <a href="url">Satin</a>
                <p>info info info</p>
            </div>
        </div> 
        <div class="row" style="margin-top: 50px;">
            <div class="categories-col">
                <img src="Assets/fabrics-edited/design4.png">
                <a href="url">Sequence</a>
                <p>info info info</p>
            </div>
            <div class="categories-col">
                <img src="Assets/fabrics-edited/design5.png">
                <a href="url">Silk</a>
                <p>info info info</p>
            </div>
            <div class="categories-col">
                <img src="Assets/fabrics-edited/design6.png">
                <a href="url">Velvet</a>
                <p>info info info</p>
            </div>
            <!--SHADOW-->
            <div class="available-row" style="background: linear-gradient(360deg, rgba(255,255,255,0) 0%, rgba(234,217,149,0.4901610302324054) 84%); height: 70px; box-shadow: -1px -4px 5px rgba(0, 0, 0, 0.15);"></div>
        </div> 

    </div>

    <div class="about-us">
        <div class="content-abt">  
            <div class="col-3">
                <h1>ABOUT US</h1>
                <strong>From Classic to Trendy, We Have the Fabric You Need</strong>
                <cite><br>Lorem ipsum dolor sit amet. Ad architecto iusto ut quasi voluptatum<br> in maiores ipsum. Qui illum enim et quaerat velit quo temporibus<br> aperiam et consectetur modi et cupiditate numquam ex expedita omnis.
                <br><br></cite>
                <button type="submit">SHOP</button>
            </div>

            <div class="col-4">
                <h1>CONTACT US</h1>
                <div class="contact-item">
                    <img src="Assets/fb-icon.png" width="50">
                    <em><a href="https://www.facebook.com/nasfia.batuaanman" target="_blank" rel="noopener noreferrer">https://www.facebook.com/nasfia.batuaanman</a>
                    </em>   
                </div>

                <div class="contact-item">
                    <em><img src="Assets/call-icon.png" width="30">
                    <a href="tel:+639183116920" target="_blank" rel="noopener noreferrer">+639183116920</a>
                    </em>
                </div>
                
                <div class="contact-item">
                    <img src="Assets/google-icon.png" width="20">
                    <em><a href="mailto:dmangadang@yahoo.com" target="_blank" rel="noopener noreferrer">dmangadang@yahoo.com</a>
                    </em> 
                </div>
            </div>
        </div>
        <div class="aboutUs-pic">

        </div>
    </div>    
                
            <div class="loc"> 
                <strong><br>VISIT US: Jan lang sa tabi tabi</strong><br></strong>
            </div>

        </div>
    <div class="sign-up">

        <!--<div class="top-container">
        </div> -->

    <div class="login-container">
        <form action="" method="POST">
            <h2>Register now and be a member of S&D Fabric Shoppe</h2></br></br>
            <div class="input-group">
                <label for="firstName">First Name</label>
                <input type="text" name="firstname" id="fName" required>
            </div>
            <div class="input-group">
                <label for="lastName">Last Name</label>
                <input type="text" name="lastname" id="lName" required>
            </div>
            
            <button type="submit" class="btn" name="continue" required>Continue</button></br></br></br></br>
            <a href="haveacc.php">HAVE AN ACCOUNT? <span class="underline-link">LOGIN HERE</span> </a> <!--inalis k muna yung br-->
        </form>
    </div>
</body>
</html>


