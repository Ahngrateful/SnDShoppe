<?php
//database
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$servername = "localhost";
$dbname = "db_sdshoppe";
$username = "root";  
$password = "";  

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$msg = "";
if(isset($_REQUEST['pwdrst']))
{
  $email = $_REQUEST['email'];
  $check_email = mysqli_query($conn,"select email from users_credentials where email='$email'");
  $res = mysqli_num_rows($check_email);
  if($res>0)
  {
    $message = '<div>
     <p><b>Hi customer!</b></p>
     <p>We received a request to reset your password. Select the button below to reset it. This link is valid for 10 minutes only.</p>
     <br>
     <p><button class="btn btn-primary"><a href="http://localhost/SnD_Shoppe-main/password_reset.php?secret='.base64_encode($email).'">Reset Password</a></button></p>
     <br>
     <p>If you did not want to change your password, you can ignore this email. Your password will not change.</p>
     <p>Please note that the above link is time sensitive. If it doesn’t work, you may request a new one.</p>
     <p>By resetting your password, you will also confirm your email associated with the account.</p>
     <p>Thank you,</p>
     <p>S&D Fabrics Shoppe</p>
    </div>';

    // Including PHPMailer
    require 'vendor/autoload.php';  // If using Composer
    ///OR use the following lines if you manually included PHPMailer files
    //include_once 'PHPMailer/src/PHPMailer.php';
    //include_once 'PHPMailer/src/SMTP.php';
    //include_once 'PHPMailer/src/Exception.php';

$mail = new PHPMailer();
$mail->IsSMTP();
//$mail->SMTPDebug = 2;            // Enable verbose debug output
//$mail->Debugoutput = 'html';     // Output debug info in HTML format
$mail->SMTPAuth = true;
$mail->SMTPSecure = "ssl";
$mail->Host = 'smtp.gmail.com';
$mail->Port = 465;
$mail->Username = "sndshoppe11@gmail.com";       // Your Gmail address
$mail->Password = "nmbd uctm myxc pshv";              // Your Gmail password or App Password
$mail->From = "sndshoppe11@gmail.com";           // Same Gmail address for sender
$mail->FromName = "S&D SHOPPE";
$mail->AddAddress($email);                     // Recipient's email address
$mail->Subject = "Reset Password";
$mail->isHTML(true);
$mail->Body = $message;
if($mail->send())
{
  $msg = "We have e-mailed your password reset link!";
}
}
else
{
  $msg = "We can't find a user with that email address";
}
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="css\forgot_pass.css">
    <link rel="icon" href="PIC/sndlogo.png" type="logo">
    <title>S&D Fabrics</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Kumbh+Sans:wght@100..900&family=Playfair+Display+SC:ital,wght@0,400;0,700;0,900;1,400;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900&display=swap');

body {
    background: url(PIC/bgLogin.png) rgba(0, 0, 0, 0.3);
    background-blend-mode: multiply;
    background-position: center;
    background-size: cover;
    background-repeat: no-repeat;
    font-family: 'Poppins', sans-serif;
    margin: 0;
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

/* Centered container styling */
.forgot-password-container {
    max-width: 400px;
    width: 100%;
    background-color: #f1e8d9;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
    margin-top: 100px;
}

/* Title styling */
.forgot-password-container h2 {
    font-family: 'Playfair Display SC', serif;
    color: #1e1e1e;
    font-weight: 600;
    font-size: 28px;
}

/* Label and input styling */
.form-label {
    font-weight: bold;
    color: #1e1e1e;
}

.form-control {
    border: 1px solid #d9b65d;
    border-radius: 8px;
    padding: 10px;
}

/* Button styling */
.btn-primary {
    background-color: #1e1e1e;
    border: none;
    padding: 10px;
    border-radius: 8px;
    font-weight: bold;
    transition: background-color 0.3s ease;
}

.btn-primary:hover {
    background-color: #525150;
}

/* Responsive styles for smaller screens */
@media (max-width: 576px) {
    .forgot-password-container {
        padding: 20px;
        font-size: 0.9rem;
    }

    .forgot-password-container h2 {
        font-size: 24px;
    }
}

    </style>
</head>
<body class="vh-100 d-flex align-items-center justify-content-center">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <a class="navbar-brand fs-4" href="#">
                <img src="PIC/sndlogo.png" width="70px" alt="Logo"/>
            </a>
        </div>
    </nav>

    <!-- Forgot Password Container -->
    <div class="forgot-password-container p-4 rounded shadow">
    <?php if (!empty($msg)) echo "<div class='alert alert-info text-center'>$msg</div>"; ?>
        <h2 class="text-center mb-3">forgot your password?</h2>
        <p class="text-center mb-4">Enter your email address, and we’ll send you a link to reset your password.</p>
        
        <form class="text-center">
            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" name="email" id="email" class="form-control" placeholder="Enter your email address" required>
            </div>
            <button type="submit" name="pwdrst" class="btn btn-primary w-100">Send Reset Link</button>
        </form>

        <div class="text-center mt-3">
            <a href="haveacc.php" class="text-decoration-none">Back to Login</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
