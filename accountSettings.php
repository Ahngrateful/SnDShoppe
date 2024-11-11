<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: homepage.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_email = $_SESSION['user_email'];

// Database connection
$servername = "localhost";
$dbname = "db_sdshoppe";
$username = "root";
$password = "";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user profile data
$profile_data = [];
$stmt = $conn->prepare("SELECT firstname, lastname, email, phone, gender, birthdate FROM users_credentials WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $profile_data = $result->fetch_assoc();
}

// Change password functionality
$message = ""; // Variable to hold any status message

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Fetch the current password from the database
    $stmt = $conn->prepare("SELECT password FROM users_credentials WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row) {
        // Check if the current password entered matches the password in the database
        if ($current_password === $row['password']) {
            // Check if new password and confirm password match
            if ($new_password === $confirm_password) {
                // Update the password in the database
                $stmt = $conn->prepare("UPDATE users_credentials SET password = ? WHERE id = ?");
                $stmt->bind_param("si", $new_password, $user_id);
                
                if ($stmt->execute()) {
                    $message = "<div class='alert alert-success'>Password successfully updated.</div>";
                } else {
                    $message = "<div class='alert alert-danger'>Error updating password: " . htmlspecialchars($stmt->error) . "</div>";
                }
            } else {
                $message = "<div class='alert alert-warning'>New passwords do not match.</div>";
            }
        } else {
            $message = "<div class='alert alert-warning'>Current password is incorrect.</div>";
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_profile'])) {
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $birthdate = $_POST['birthdate'];

    // Update profile data in the database
    $stmt = $conn->prepare("UPDATE users_credentials SET firstname = ?, lastname = ?, email = ?, phone = ?, birthdate = ? WHERE id = ?");
    $stmt->bind_param("sssssi", $firstname, $lastname, $email, $phone, $birthdate, $user_id);

    if ($stmt->execute()) {
        $message = "<div class='alert alert-success'>Profile updated successfully.</div>";
    } else {
        $message = "<div class='alert alert-danger'>Error updating profile: " . htmlspecialchars($stmt->error) . "</div>";
    }
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="\SnD_Shoppe-main\css\accountSettings.css">
    <link rel="icon" href="/SnD_Shoppe-main/PIC/sndlogo.png" type="image/png">
    <title>S&D Fabrics</title>
    <script>
        function toggleEdit() {
    // Toggle between hiding and showing the profile-view and profile-edit sections
        var editSection = document.getElementById('profile-edit');
        if (editSection.style.display === 'none' || editSection.style.display === '') {
            editSection.style.display = 'block';
        } else {
            editSection.style.display = 'none';
        }
    }
        </script>
</head>
<body class="vh-100">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark main-navbar" style="background-color: #f1e8d9; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <a class="navbar-brand fs-4" href="homepage.php">
                <img src="/SnD_Shoppe-main/PIC/sndlogo.png" width="70px" alt="Logo"/>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarTogglerDemo01" aria-controls="navbarTogglerDemo01" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarTogglerDemo01">
                <div class="mx-auto d-flex justify-content-center flex-grow-1">
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
                        <a class="nav-link nav-link-black active" aria-current="page" href="cart.php">
                            <img src="/SnD_Shoppe-main/Assets/svg(icons)/shopping_cart.svg" alt="cart">
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-black" href="#">
                            <img src="/SnD_Shoppe-main/Assets/svg(icons)/notifications.svg" alt="notif">
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-black" href="#">
                            <img src="/SnD_Shoppe-main/Assets/svg(icons)/inbox.svg" alt="inbox">
                        </a>
                    </li>
                    <!-- New Account Dropdown Menu -->
                    <li class="nav-item dropdown">
                        <a class="nav-link nav-link-black dropdown-toggle" href="#" id="accountDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="/SnD_Shoppe-main/Assets/svg(icons)/account_circle.svg" alt="account">
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="accountDropdown">
                            <li>
                                <a class="dropdown-item" href="accountSettings.php">My Account</a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <a class="dropdown-item text-danger" href="haveacc.php">Logout</a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <div class="d-flex flex-column flex-shrink-0 p-3 text-white bg-dark" id="sidebar" style="width: 250px; height: 100vh; position: fixed;">
        <ul class="nav nav-pills flex-column mb-auto">
            </li>
            <li>
                <a href="mypurchase.php" class="nav-link text-white">
                    <i class="bi bi-box-seam"></i> Orders
                </a>
            </li>
            <li>
                <a href="#" class="nav-link text-white">
                    <i class="bi bi-heart"></i> Saved Items
                </a>
            </li>
            <li>
                <a href="accountSettings.php" class="nav-link custom-active" aria-current="page">
                    <i class="bi bi-person"></i> Account Settings
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="d-flex" style="margin-top: 96px; margin-left: 250px;"> 
        <div class="flex-grow-1 p-4"> 
            
        <div class="container mt-4">
        <?php if (!empty($message)) echo $message; ?>

            <!-- Profile Information -->
            <div class="card mb-4" style="background-color: #f1e8d9;">
                <div class="card-body">
                    <h2>My Profile</h2>
                    <p>Manage Your Account</p>
                    
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($profile_data['firstname'] . ' ' . $profile_data['lastname']); ?> <a href="javascript:void(0);" onclick="toggleEdit()" class="link-primary">Change</a></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($profile_data['email']); ?> <a href="javascript:void(0);" onclick="toggleEdit()" class="link-primary">Change</a></p>
                    <p><strong>Phone Number:</strong> <?php echo htmlspecialchars($profile_data['phone']); ?> <a href="javascript:void(0);" onclick="toggleEdit()" class="link-primary">Change</a></p>
                    <p><strong>Gender:</strong> <?php echo htmlspecialchars($profile_data['gender']); ?></p>
                    <p><strong>Date of Birth:</strong> <?php echo htmlspecialchars($profile_data['birthdate']); ?> <a href="javascript:void(0);" onclick="toggleEdit()" class="link-primary">Change</a></p>
                </div>
            </div>

            <!-- Profile Information (Edit Mode) -->
    <div class="card mb-4" style="background-color: #f1e8d9; display: none;" id="profile-edit">
        <div class="card-body">
            <h2>Edit Profile</h2>
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="firstname" class="form-label">First Name</label>
                    <input type="text" class="form-control" name="firstname" value="<?php echo htmlspecialchars($profile_data['firstname']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="lastname" class="form-label">Last Name</label>
                    <input type="text" class="form-control" name="lastname" value="<?php echo htmlspecialchars($profile_data['lastname']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($profile_data['email']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="phone" class="form-label">Phone Number</label>
                    <input type="text" class="form-control" name="phone" value="<?php echo htmlspecialchars($profile_data['phone']); ?>"maxlength="11" required>
                </div>
                <div class="mb-3">
                    <label for="birthdate" class="form-label">Date of Birth</label>
                    <input type="date" class="form-control" name="birthdate" value="<?php echo htmlspecialchars($profile_data['birthdate']); ?>" required>
                </div>
                <button type="submit" class="btn btn-success" name="save_profile">Save Changes</button>
                <button type="button" class="btn btn-secondary" onclick="cancelEdit()">Cancel</button>
            </form>
        </div>
    </div>
</div>

            <!-- Change Password -->
            <div class="card" style="background-color: #f1e8d9;">
                <div class="card-body">
                    <h5 class="card-title">Change Password</h5>
                   <!-- ?php echo $message; ?>--> 
                    <!-- Display message here -->
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="currentPassword" class="form-label">Current Password</label>
                            <input type="password" class="form-control" name="current_password" id="currentPassword" required>
                        </div>
                        <div class="mb-3">
                            <label for="newPassword" class="form-label">New Password</label>
                            <input type="password" class="form-control" name="new_password" id="newPassword" required>
                        </div>
                        <div class="mb-3">
                            <label for="confirmPassword" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" name="confirm_password" id="confirmPassword" required>
                        </div>
                        <button class="btn btn-secondary mt-3" type="submit" name="update">Update Password</button> <!-- Changed class here -->
                    </form>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
