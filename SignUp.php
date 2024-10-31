<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="/SnD_Shoppe-main/Assets/sndlogo.png" type="logo">
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
<body>
<div id="overlay">Account Created</div>
<div class="containersignup">
    <div class="s_login-form">
        <h1>SIGN UP</h1>
        <p>
            Please fill in this form to create an account or 
            <a href="haveacc.php">Login</a>
        </p>

        <form action="" method="post" class="form">
            <?php
            session_start();
            $servername = "localhost";
            $dbname = "db_sdshoppe";
            $username = "root";
            $password = "";

            $conn = new mysqli($servername, $username, $password, $dbname);
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            //if (!isset($_SESSION['user_id'])) {
                //$_SESSION['user_id'] = rand(1, 1000); // Fallback for testing

            if (isset($_POST['create'])) {
                $id = $_SESSION['user_id'];
                $email = $_POST['email'];
                $password = $_POST['psw'];
                $repeat_password = $_POST['psw-repeat'];
                $birthdate = $_POST['dob'];
                $gender = $_POST['gender'];
                $address = $_POST['address'];
                $phone = $_POST['phone'];

                // Check if passwords match
                if ($password !== $repeat_password) {
                    echo '<p style="color:red; text-align:center;">Passwords do not match!</p>';
                } else {
                    $hashed_password = $password;

                    $checkEmailQuery = "SELECT * FROM users_credentials WHERE EMAIL = ?";
                    $stmt = $conn->prepare($checkEmailQuery);
                    $stmt->bind_param("s", $email);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0) {
                        echo '<p style="color:red; text-align:center;">Email already exists. Please use a different email.</p>';
                    } else {
                        $sql = "UPDATE users_credentials 
                                SET EMAIL = ?, PASSWORD = ?, GENDER = ?, BIRTHDATE = ?, ADDRESS = ?, PHONE = ? 
                                WHERE ID = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("ssssssi", $email, $hashed_password, $gender, $birthdate, $address, $phone, $id);

                        if ($stmt->execute()) {
                            echo "<script>
                                    document.getElementById('overlay').style.display = 'flex';
                                    setTimeout(function() {
                                        window.location.href = 'sndLandingpage.php';
                                    }, 3000);
                                  </script>";
                        } else {
                            echo "Error: " . $stmt->error;
                        }
                        $stmt->close();
                    }
                }
            }
            $conn->close();
            ?>

            <label for="email">Email</label>
            <input type="text" placeholder="Enter Email" name="email" autocomplete="off" required />

            <label for="psw">Password</label>
            <input type="password" placeholder="Enter Password" name="psw" autocomplete="off" required />

            <label for="psw-repeat">Repeat Password</label>
            <input type="password" placeholder="Repeat Password" name="psw-repeat" autocomplete="off" required />

            <label for="dob">Date of Birth</label>
            <input type="date" name="dob" required />

            <label for="gender">Gender</label>
            <div class="gender-options">
                <label><input type="radio" name="gender" value="male" required /> Male</label><br/>
                <label><input type="radio" name="gender" value="female" required /> Female</label><br/>
                <label><input type="radio" name="gender" value="prefer_not_say" required /> Prefer not to say</label>
            </div>

            <label for="address">Address</label>
            <input type="text" placeholder="Enter Address" name="address" autocomplete="off" required />

            <label for="phone">Phone Number</label>
            <input type="tel" placeholder="Enter Phone Number" name="phone" autocomplete="off" required />

            <label>
                <input type="checkbox" checked="checked" name="remember" style="margin-bottom: 15px" />
                Remember me
            </label>

            <p>
                By creating an account you agree to our
                <a href="#">Terms & Privacy</a>.
            </p>

            <div class="s_buttons">
                <button type="button" class="cancelbtn" onclick="window.location.href='index.php';">Cancel</button>
                <button type="submit" name="create" class="signupbtn">Sign Up</button>
            </div>
        </form>
    </div>
</div>

</body>
</html>