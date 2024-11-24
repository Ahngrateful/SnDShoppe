<?php 
session_start();
$servername = "localhost";
$dbname = "db_sdshoppe";
$username = "root";
$password = "";

// Use PDO for consistency
$pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if (isset($_POST['create'])) {
    $id = $_SESSION['user_id'] ?? rand(1, 1000); // Fallback for testing
    $email = $_POST['email'];
    $password = $_POST['psw'];
    $repeat_password = $_POST['psw-repeat'];
    $birthdate = $_POST['dob'];
    $gender = $_POST['gender'];
    $address = $_POST['address'];
    $subdivision =  $_POST['subdivision'];
    $barangay = $_POST['brgy'];
    $postal =  $_POST['postal'];
    $city =  $_POST['city'];
    $place =  $_POST['place'];
    $phone = $_POST['phone'];

    // Check if passwords match
    if ($password !== $repeat_password) {
        echo '<p style="color:red; text-align:center;">Passwords do not match!</p>';
    } else {
        $hashed_password = $password;
        $checkEmailQuery = "SELECT * FROM users_credentials WHERE EMAIL = ?";
        $stmt = $pdo->prepare($checkEmailQuery);
        $stmt->execute([$email]);

        if ($stmt->rowCount() > 0) {
            echo '<p style="color:red; text-align:center;">Email already exists. Please use a different email.</p>';
        } else {
            $sql = "UPDATE users_credentials SET EMAIL = ?, PASSWORD = ?, GENDER = ?, BIRTHDATE = ?, ADDRESS = ?, SUBDIVISION = ?, BARANGAY = ?, POSTAL = ?,
            CITY= ?, PLACE = ?, PHONE = ? WHERE ID = ?";
            $stmt = $pdo->prepare($sql);
           if ($stmt->execute([$email, $hashed_password, $gender, $birthdate, $address, $subdivision, $barangay, $postal, $city, $place, $phone, $id])) {
            echo "<div id='overlay' style='display:none;'>Loading...</div>";
            echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        document.getElementById('overlay').style.display = 'flex';
                        setTimeout(function() {
                            window.location.href = 'sndLandingpage.php';
                        }, 3000);
                    });
                  </script>";
            } else {
                echo "Error: " . $stmt->errorInfo()[2];
            }
        }
    }
}

if (isset($_POST['cancel'])) {
    // Get the last row's ID
    $stmt = $pdo->query("SELECT id FROM users_credentials ORDER BY id DESC LIMIT 1");
    $lastRow = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($lastRow) {
        // Prepare the DELETE statement with the last row's ID
        $sql = "DELETE FROM users_credentials WHERE id = ?";
        $deleteStmt = $pdo->prepare($sql);
        $rowsAffected = $deleteStmt->execute([$lastRow['id']]);

        // Check if a row was deleted and redirect
        if ($rowsAffected) {
            header("Location: sndLandingpage.php");
            exit;
        } else {
            echo "No rows were deleted.";
        }
    } else {
        echo "No rows found to delete.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="/SnD_Shoppe-main/Assets/sndlogo.png" type="logo">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.0/css/all.min.css">
    <link rel="stylesheet" href="css_files/style.css">
    <title>S&D | SIGN UP HERE </title>
    <style>
        
    /*========================================================SIGNUP===================================*/
    .containersignup {
        width: 100%;
        padding: 16px;
        background: radial-gradient(#fff,#F9F9D5);
    }

    .s_login-form {
        background-color: #333;
        color: white;
        padding: 20px;
        margin: 0 auto;
        max-width: 600px;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    h1 {
        text-align: center;
        font-size: 24px;
        margin-bottom: 20px;
    }

    label {
        display: block;
        margin-bottom: 8px;
        font-weight: bold;
    }

    input[type="text"],
    input[type="password"],
    input[type="date"],
    input[type="tel"] {
        width: 100%;
        padding: 10px;
        margin: 8px 0 20px 0;
        display: inline-block;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-sizing: border-box;
    }

    .gender-options {
        margin-bottom: 20px;
    }

    .gender-options input {
        margin-right: 10px;
    }

    .s_buttons {
        text-align: center;
        margin-top: 20px;
    }

    button.signupbtn,
    button.cancelbtn {
        background-color: #4CAF50;
        color: white;
        padding: 14px 20px;
        margin: 8px 0;
        border: none;
        cursor: pointer;
        width: 45%;
        border-radius: 5px;
    }

    button.cancelbtn {
        background-color: #f44336;
    }

    button.signupbtn:hover,
    button.cancelbtn:hover {
        opacity: 0.8;
    }

    a {
        color: #f1c40f;
        text-decoration: none;
    }

    a:hover {
        text-decoration: underline;
    }

    p {
        text-align: center;
    }

    .s_login-form {
        background-color: #2d2d2d;
        color: white;
        padding: 40px;
        max-width: 700px;
        margin: 50px auto;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
    }

    .s_login-form input[type="text"],
    .s_login-form input[type="password"],
    .s_login-form input[type="date"],
    .s_login-form input[type="tel"] {
        width: calc(100% - 20px);
        padding: 10px;
        background-color: #fff;
        color: #555;
        border: none;   
        margin-bottom: 20px;
        border-radius: 5px;
    }

    .select-box select {
            height: 20px;
            width: 150px;
            outline: #473C38;
            border: none;
            color: black;
            background:  #f1e8d9;
            border-bottom: 1px solid #44362A;
            font-size: 15px;
        }

    .password-container {
            position: relative;
        }

        .password-container i {
            position: absolute;
            right: 30px; 
            top: 40%; 
            transform: translateY(-50%); 
            color: #333; 
        }

        .password-container i:hover {
            color: #333; 
        }

    input[type="radio"] {
        margin-right: 10px;
    }
    #overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: none;
    justify-content: center;
    align-items: center;
    color: #fff;
    font-size: 2em;
    z-index: 9999;
    }

    </style>
</head>
<body>
<div class="containersignup">
    <div class="s_login-form">
        <h1>SIGN UP</h1>
        <p>Please fill in this form to create an account or <a href="haveacc.php">Login</a></p>

        <form action="" method="post" class="form" id="signupForm">


            <label for="email">Email</label>
            <input type="text" placeholder="Enter Email" name="email" autocomplete="off" required />

            <label for="psw">Password</label>
            <div class="password-container">
                <input type="password" placeholder="Enter Password" name="psw" id="password" autocomplete="off" required />
                <i class="far fa-eye" id="togglePassword1" style="cursor: pointer;"></i>
            </div>

            <label for="psw-repeat">Repeat Password</label>
            <div class="password-container">
                <input type="password" placeholder="Repeat Password" name="psw-repeat" id="passwordRepeat" autocomplete="off" required />
                <i class="far fa-eye" id="togglePassword2" style="cursor: pointer;"></i>
            </div>

            <label for="dob">Date of Birth</label>
            <input type="date" name="dob" required />

            <label for="gender">Gender</label>
            <div class="gender-options">
                <label><input type="radio" name="gender" value="Male" required /> Male</label><br/>
                <label><input type="radio" name="gender" value="Female" required /> Female</label><br/>
                <label><input type="radio" name="gender" value="Prefer_not_say" required /> Prefer not to say</label>
            </div>

            <label for="ADDRESS">Address</label>
            <label for="address">House No. & Street</label>
            <input type="text" placeholder="Enter House No. & Street" name="address" autocomplete="off" required />

            <label for="subdivision">Subdivision/Village(OPTIONAL)</label>
            <input type="text" placeholder="Enter Subdivision/Village" name="subdivision" autocomplete="off"/>

            <label for="brgy">Barangay</label>
            <input type="text" placeholder="Enter Barangay" name="brgy" autocomplete="off" required/>
            
            <label for="postal">Postal</label>
            <input type="text" placeholder="Enter Postal" name="postal" autocomplete="off" required/>
            
            <label for="city">City</label>
            <input type="text" placeholder="Enter City" name="city" autocomplete="off" required/>

            <label for="place">PLACE</label>
            <div class="select-box">
                    <select name="place" required>
                        <option hidden>Place</option>
                        <option selected>Metro Manila</option>
                        <option>Luzon</option>
                        <option>Visayas</option>
                        <option>Mindanao</option>    
                    </select>
                </div>
            </br>
            <label for="phone">Phone Number</label>
            <input type="tel" placeholder="Enter Phone Number" name="phone" autocomplete="off" maxlength="11" required/>

            <!--<label>
                <input type="checkbox" checked="checked" name="remember" style="margin-bottom: 15px" />
                Remember me
            </label> -->

            <p>By creating an account you agree to our <a href="#">Terms & Privacy</a>.</p>

            <div class="s_buttons">
                <button type="button" class="cancelbtn" onclick="submitCancelForm()">Cancel</button>
                <button type="submit" name="create" class="signupbtn">Sign Up</button>
            </div>
        </form>
    </div>
</div>
<script>
    function submitCancelForm() {
    // Add a hidden input field to indicate cancel action
    const cancelInput = document.createElement('input');
    cancelInput.type = 'hidden';
    cancelInput.name = 'cancel';
    cancelInput.value = '1';

    // Append the hidden input to the form
    const form = document.getElementById('signupForm');
    form.appendChild(cancelInput);

    // Submit the form programmatically
    form.submit();
}
document.addEventListener('DOMContentLoaded', function() {
    // Password toggle visibility
    const togglePassword1 = document.querySelector('#togglePassword1');
    const togglePassword2 = document.querySelector('#togglePassword2');
    const password = document.querySelector('#password');
    const passwordRepeat = document.querySelector('#passwordRepeat');

    if (togglePassword1 && togglePassword2) {
        togglePassword1.addEventListener('click', function () {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('fa-eye-slash');
        });

        togglePassword2.addEventListener('click', function () {
            const type = passwordRepeat.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordRepeat.setAttribute('type', type);
            this.classList.toggle('fa-eye-slash');
        });
    }
});

</script>
</body>
</html>
