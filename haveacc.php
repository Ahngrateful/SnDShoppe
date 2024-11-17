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
        font-family: "Playfair Display", serif;
    };

    /* =======================================================LOGIN========================================*/

    body {
        font-family: 'Playfair', sans-serif;
        margin: 0;
        width: 100%;
        padding: 0;
    }

    .containerlogin {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh; 
        background-image: url(PIC/bgLogin.png);
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
        margin-top: 30px;
        padding: 15px;
        position: relative;
    }

    .buttons .signupbtn:hover {
        background-color: #bc9c22;
    }
   
    .buttons .cancelbtn:hover {
        background-color: #2E2E31;
    }

    .buttons .cancelbtn,
    .buttons .signupbtn {
        width: 48%;
        padding: 10px;
        font-size: 16px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: all 0.3s ease; /* Smooth transition */
        border: 1px solid #000000;
    }

    .buttons .cancelbtn {
        background-color: #555;
        color: white;
    }

    .buttons .signupbtn {
        background-color: #f3c552;
        color: #333;
    }

    .login-form img {
        display: block;
        margin-left: auto;
        margin-right: auto;
        margin-bottom: 10px;
        width: 25%; 
    }

    .forgot-password {
        color: #bc9c22;
        text-decoration: none;
        margin-bottom: 50px;
        padding: 10px;

    }

    .forgot-password:hover {
        text-decoration: underline;
    }

    .signup:hover {
        text-decoration: underline;
    }

    </style>
</head>
<body>

      <div class="containerlogin">
        <div class="login-form">
        <form action="" method="POST">
            <img src="Assets/sndlogo-wShadow.png" alt="logo"/>
            <h1>Login</h1>
            <p>New to S&D Shop? 
                <a href="sndLandingpage.php" class="signup">Sign Up</a>
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
            <a href="forgot_pass.php" class="forgot-password">Forgot Password<br></a>
         <!--  <p>
            <Br>By creating an account you agree to our
            <a href="#">Terms & Privacy</a>.
          </p> -->

            <div class="buttons">
                <button type="button" class="cancelbtn" onclick="window.location.href='sndLandingpage.php';">Cancel</button>
                <button type="submit" name ="login" class="signupbtn">Login</button>
            </div>
        </form>
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
