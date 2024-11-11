<?php
session_start(); // Start the session at the beginning

$servername = "localhost";
$dbname = "db_sdshoppe";
$username = "root";  
$password = "";  

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password1 = $_POST['psw']; 

    $stmt = $conn->prepare("SELECT * FROM users_credentials WHERE EMAIL = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        if (isset($row['PASSWORD'])) {
            if ($password1 === $row['PASSWORD']) {
                // Set session variables for user ID and email
                $_SESSION['user_id'] = $row['ID'];
                $_SESSION['user_email'] = $row['EMAIL'];
                
                header("Location: homepage.php");
                exit; 
            } else {
                $message = "Invalid password. Please try again.";
            }
        }
    } else {
        $message = "Create an account first.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>S&D | LOGIN</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.0/css/all.min.css">
    <link rel="icon" href="/SnD_Shoppe-main/Assets/sndlogo.png" type="logo">
    <style>
    *{
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    /* =======================================index==================================================================*/
    .navbar{
        display: flex;
        align-items: center;
        padding: 20px;
    }

    .far fa-eye{
        margin-right: -30px; 
        cursor: pointer;
    }

    nav{
        flex: 1;
        text-align: right;
    }

    nav ul{
        display: inline-block;
        list-style: none;
    }

    nav ul li {
        display: inline-block;
        margin-right: 20px;
    }
    a{ 
        text-decoration: none;
        color: #bc9c22;
    }
    p{
        color: #555;
    }

    .container{
        max-width: 1300px;
        margin: auto;
        padding-left: 25px;
        padding-right: 25px;
    }
    .row{
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        justify-content: space-around;
    }
    .col-2{
        flex-basis: 50%;    
        min-width: 300px;
    }
    .col-2 img{
        max-width: 100%;
        padding: 50px 0;
    }
    .col-2 h1{
        font-size: 50px;
        line-height: 60px;
        margin: 25px 0;
    }
    .btn{
        display: inline-block;
        background:#bc9c22;
        color: #fff;
        padding: 8px 30px;
        margin: 30px 0;
        border-radius: 30px;
        transition: background 0.5s;
    }

    .btn:hover{
        background: #77430a;
    }
    .header{
        background: radial-gradient(#fff,#F9F9D5);
        /*background: #313131 url(images/bgLogin.png) left center/contain no-repeat padding-box; */
        
    }

    .categories{
        
        margin: 70px auto;
        max-width: 2224px;
        padding: 30px 0;
        width: 90%;
        margin-top: 80px;
    }
    .row {
        max-width: 1000px;
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        margin-left: auto;
    }
    .col-6 {
        flex-basis: 25%;
        min-width: 25px;
        margin-bottom: 30px;
        align-items: center;
        opacity: 1; 
    

    }
    .col-6 img{
        width: 100%;
        align-items: center;
        height: 100%;
        border-radius: 5px;
        
        opacity: 1;


    }
    .list{
        display:flex;
        text-align: left;
        text-transform: uppercase;
        margin-left: 20;
        align-items: center;
        
    }

    /* Gallery Section */
    .gallery {
        display: flex;
        flex-wrap: wrap;
        gap: 10px; /* Spacing between images */
        justify-content: center; /* Centers the gallery items horizontally */
        max-width: 600px; /* Width limit to match the design */
        margin: 20px auto; /* Center align and add top/bottom spacing */
    }
    .content {
        flex: 1; /* Makes the content section take the remaining space */
        display: flex;
        flex-direction: column;
        gap: 20px;
    }


    .gallery img {
        width: 100%;
        max-width: 280px; /* Sets a max width for each image */
        border-radius: 8px; /* Rounds the corners of each image */
    }

    .col-3{
        max-width: 1300px;
        display: block;
        background-color: #282727;
        color: #FFFFFF;
        overflow: auto;
        
        display: flex;
                flex-direction: column;
                align-items: center;
                margin-top: 50px;
                padding: 30px;
                border-radius: 10px;
                
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
                width: 500px;
                margin-bottom: 60px;
                margin-left: 1000px;
                text-align: center;
    }
    .abtpic{
        max-width: 1000px;
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        margin-left: auto;
        width: 100%;
        align-items: left;
        height: 100%;
        border-radius: 5px;
    }
    .submit{
        background-color: #B99470
    }
    .col-4{
        margin-top: 20px;
        border: 5px solid #282727;
        box-shadow: 5px 5px 10px 1px #000000;
        line-height: 2;
        margin-right: 10px;
        display: flex;
                flex-direction: column;
                align-items: center;
                margin-top: 30px;
                padding: 30px;
                border-radius: 1px;
        display: flex;
                align-items: center;
                margin-bottom: 10px;
                color: #333;
                text-decoration: none;width: 500px;
        margin-left: 1000px;
        margin-bottom: 30px;
    
    }.col-4 {
        margin-top: 20px;
        bottom: 20px;
        left: 20px;
        border: 5px solid #282727;
        box-shadow: 5px 5px 10px 1px #000000;
        line-height: 2;
        padding: 30px;
        width: 500px;
        margin-left: 1000px;
        margin-bottom: 30px;
        border-radius: 1px;
        display: flex;
        flex-direction: column;
        align-items: left; 
        /*background-color: #F9F9D5;*/
    }

    .contact-item {
        display: flex;
        align-items: center;
        margin-bottom: 10px;
    }

    .contact-item img {
        margin-right: 10px;
        
    }

    .contact-item a {
        color: #333;
        text-decoration: none;
    }

    .contact-item a:hover {
        text-decoration: underline;
        text-align: center;
    }

    .loc{
        display: block;
        background-color: #7E7E7E;
        color: #2E2E31;  padding-left: 20px; padding-bottom: 20px;
        }
        
    .sign-up{
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        body {
            justify-content: left;
            align-items: center;
            height: 100vh;
            
            font-family: Arial, sans-serif;
            color: #ccc;
            text-align: center;
        }

        .top-container {
            height: 75px;
            background-color: white;
            padding: 20px;
            margin-left: 0;
        }

        .bottom-container{
            height: 75px;
            background-color: white;
            padding: 20px;
            margin-left: 0;
        }

        .login-container {
            background-color: #282727;
            color: #fff;
            padding: 20px;
            /*border-radius: 5px;*/
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            height: 700px;
            width: 350px;
            margin-left: 150px;
            
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            margin-top: 175px;
            
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

        a:link {
            color: white;
            background-color: transparent;
            text-decoration: none;
            
        }
        a:visited {
            color: white;
            background-color: transparent;
            text-decoration: none;
            
        }
        

        button {
            width: 30%;
            padding: 10px;
            background-color: #282727;
            color: white;
            border: 2px solid #000000; 
            border-radius: 5px;
            cursor: pointer;
            
        }

        button:hover {
            background-color: #353635;
            
            
        }}


    /* =======================================================LOGIN========================================*/

    body {
        font-family: 'Playfair', sans-serif;
        margin: 0;
        width: 100%;
        padding: 0;
        background-image:url(images/bgLogin.png); 
    }

    .containerlogin {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh; 
    }

    .login-form {
        background-color: #333;
        padding: 40px;
        border-radius: 10px;
        box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.1);
        max-width: 400px;
        width: 100%;
    }

    .login-form h1 {
        text-align: center;
        font-size: 36px;
        color: #fff;
        margin-bottom: 20px;
        text-transform: uppercase;
        letter-spacing: 2px;
    }

    .login-form label {
        display: block;
        margin-bottom: 10px;
        color: #fff;
        font-size: 14px;
        letter-spacing: 1px;
        text-transform: uppercase;
    }

    .login-form input[type="text"] {
        width: 100%;
        padding: 12px;
        margin-bottom: 20px;
        background-color: #ccc;
        border: none;
        border-radius: 5px;
        color: #000;
        font-size: 16px;
    }

    .input-container {
        position: relative; /* Allows for absolute positioning of child elements */
        margin-bottom: 15px; /* Space between inputs */
    }

    .input-container input[type="password"] {
        width: 100%;
        padding: 12px; 
        padding-right: 40px; 
        background-color: #ccc;
        border: none;
        border-radius: 5px;
        color: #000;
        font-size: 16px;
        }

    .input-container i {
        position: absolute; 
        right: 10px; 
        top: 50%; 
        transform: translateY(-50%); 
        cursor: pointer; 
        color: #333; 
    }


    .login-form label[for="remember"] {
        color: #fff;
        margin-bottom: 10px;
        font-size: 14px;
    }

    .login-form p {
        color: #ccc;
        font-size: 12px;
        margin-bottom: 15px;
        text-align: center;
    }

    .login-form p a {
        color: #f3c552;
        text-decoration: none;
    }

    .buttons {
        display: flex;
        justify-content: space-between;
        margin-top: 20px;
    }


    .buttons .cancelbtn,
    .buttons .signupbtn {
        width: 48%;
        padding: 12px;
        font-size: 16px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        text-transform: uppercase;
    }

    .buttons .cancelbtn {
        background-color: #555;
        color: white;
    }

    .buttons .signupbtn {
        background-color: #f3c552;
        color: #333;
    }

    .social-buttons {
        display: flex;
        justify-content: space-around;
        margin-top: 20px;
    }

    .social-buttons button {
        display: flex;
        align-items: center;
        background-color: #E6E9F5;
        color: #000;
        border: none;
        padding: 10px;
        font-size: 14px;
        cursor: pointer;
        border-radius: 5px;
        width: 45%;
    }

    .social-buttons button img {
        margin-right: 10px;
    }

    .social-buttons button.facebook {
        background-color: #4267B2;
        color: white;
    }

    .social-buttons button.google {
        background-color: #fff;
        border: 1px solid #ccc;
    }

    </style>
</head>
<body>

      </div>
      <div class="containerlogin">
        <div class="login-form">
        <form action="" method="POST">
            <h1>Login</h1>
            <p>New to S&D Shop? 
                <a href="sndLandingpage.php">Sign Up</a>
            </p>

            <label for="email">Email</label>
            <input type="text" placeholder="Enter Email" name="email" required />

            <label for="psw">Password</label>
                <div class="input-container">
                    <input type="password" id="id_password" placeholder="Enter Password" name="psw" required/>
                    <i class="far fa-eye" id="togglePassword"></i> <!-- Eye icon inside input -->
                </div>
            <label>
            <!--<input type="checkbox" checked="checked" name="remember" style="margin-bottom: 15px"/> Remember me </label> -->
            <a href="forgot_pass.php" class="forgot-password">  Forgot Password<br></a>
         <!--  <p>
            <Br>By creating an account you agree to our
            <a href="#">Terms & Privacy</a>.
          </p> -->

        <div class="buttons">
            <button type="button" class="cancelbtn" onclick="window.location.href='sndLandingpage.php';">Cancel</button>
            <button type="submit" name ="login" class="signupbtn">Login</button>
        </div>
        </form>

        <div class="social-buttons">
            <button class="google">
                <img src="images/google-icon.png" alt="Google" width="20">
                Continue with Google
            </button>
            <button class="facebook">
                <img src="images/fb-icon.png" alt="Facebook" width="20">
                Continue with Facebook
            </button>
        </div>
      </div>
      
    </div>
    
    <!--javascript-->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const togglePassword = document.querySelector('#togglePassword');
            const password = document.querySelector('#id_password');

            togglePassword.addEventListener('click', function () {
                // toggle the type attribute
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);
                // toggle the eye slash icon
                this.classList.toggle('fa-eye-slash');
            });
        });
    </script>
    <?php if (!empty($message)): ?>
        <script>
            alert("<?php echo $message; ?>");
        </script>
    <?php endif; ?>

</body>
</html>
