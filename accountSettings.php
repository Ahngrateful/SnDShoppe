<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: homepage.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_email = $_SESSION['user_email'];

// Database connection using PDO
$servername = "localhost";
$dbname = "db_sdshoppe";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Fetch user profile data
$profile_data = [];
$stmt = $pdo->prepare("SELECT firstname, lastname, email, phone, gender, birthdate, address, subdivision,
barangay, postal, city, place FROM users_credentials WHERE id = ?");
$stmt->bindParam(1, $user_id, PDO::PARAM_INT);
$stmt->execute();
$profile_data = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch unread notifications
$query_notifications = "SELECT notif_id, message, created_at FROM notifications WHERE id = ? AND is_read = 0 ORDER BY created_at DESC";
$stmt_notifications = $pdo->prepare($query_notifications);
$stmt_notifications->bindParam(1, $user_id, PDO::PARAM_INT);
$stmt_notifications->execute();
$result_notifications = $stmt_notifications->fetchAll(PDO::FETCH_ASSOC);

if (isset($_GET['notif_id']) && is_numeric($_GET['notif_id'])) {
    $notif_id = intval($_GET['notif_id']);

    $query_check_notif = "SELECT notif_id FROM notifications WHERE notif_id = ? AND id = ?";
    $stmt_check_notif = $pdo->prepare($query_check_notif);
    $stmt_check_notif->execute([$notif_id, $user_id]);

    if ($stmt_check_notif->rowCount() > 0) {
        // Mark the notification as read
        $query_update_read = "UPDATE notifications SET is_read = 1 WHERE notif_id = ?";
        $stmt_update_read = $pdo->prepare($query_update_read);
        $stmt_update_read->execute([$notif_id]);

        echo json_encode(['status' => 'success', 'message' => 'Notification marked as read']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Notification not found or does not belong to this user']);
    }
}

// Query to count unread notifications
$query_count_unread = "SELECT COUNT(*) FROM notifications WHERE id = ? AND is_read = 0";
$stmt_count_unread = $pdo->prepare($query_count_unread);
$stmt_count_unread->bindParam(1, $user_id, PDO::PARAM_INT);
$stmt_count_unread->execute();
$unread_count = $stmt_count_unread->fetchColumn();

// Change password functionality
$message = ""; // Variable to hold any status message

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Fetch the current password from the database
    $stmt = $pdo->prepare("SELECT password FROM users_credentials WHERE id = ?");
    $stmt->bindParam(1, $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        // Check if the current password entered matches the password in the database
        if ($current_password === $row['password']) {
            // Check if new password and confirm password match
            if ($new_password === $confirm_password) {
                // Update the password in the database
                $stmt = $pdo->prepare("UPDATE users_credentials SET password = ? WHERE id = ?");
                $stmt->execute([$new_password, $user_id]);
                
                echo "<script>alert('Password successfully updated.');</script>";
            } else {
                echo "<script>alert('New passwords do not match.');</script>";
            }
        } else {
            echo "<script>alert('Current password is incorrect.');</script>";
            
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_profile'])) {
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $birthdate = $_POST['birthdate'];
    $address = $_POST['address'];
    $subdivision =  $_POST['subdivision'];
    $barangay = $_POST['barangay'];
    $postal =  $_POST['postal'];
    $city =  $_POST['city'];
    $place =  $_POST['place'];

    // Update profile data in the database
    $stmt = $pdo->prepare("UPDATE users_credentials SET firstname = ?, lastname = ?, email = ?, phone = ?, birthdate = ?, address = ? WHERE id = ?");
    $stmt->execute([$firstname, $lastname, $email, $phone, $birthdate, $address, $user_id]);
    echo "<script>alert('Profile updated successfully.');</script>";
    
}

$pdo = null; // Close the PDO connection
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet">
    <link rel="icon" href="/SnD_Shoppe-main/PIC/sndlogo.png" type="image/png">
    <title>S&D Fabrics</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Kumbh+Sans:wght@100..900&family=Playfair+Display+SC:ital,wght@0,400;0,700;0,900;1,400;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900&display=swap');

body {
    background: url(Assets/bgLogin.png) rgba(0, 0, 0, 0.3);
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

/* Sidebar Styles */
#sidebar {
    background-color: #343a40; 
    color: #f1e8d9; 
}

#sidebar .nav-link {
    color: #f1e8d9; 
}

#sidebar .nav-link:hover {
    background-color: #e0cbab; 
    color: #1e1e1e;
}

#sidebar .nav-link.custom-active {
    background-color: #f1e8d9 !important; 
    color: #1e1e1e !important; 
}

/* Active Link Style for Sidebar */
#sidebar .nav-link.active {
    background-color: #e0cbab; 
    color: #1e1e1e !important; 
}

/* General Styles */
h1 {
    font-family: "Playfair Display SC", serif;
    font-size: 50px;
    color: #1e1e1e;
}

.table-striped tbody tr:nth-of-type(odd) {
    background-color: rgba(255, 255, 255, 0.05);
}

.card {
    border: none;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.rect-form-control {
    border-radius: 0 !important; 
}

/* Custom button color for gray buttons */
.btn-gray {
    background-color: #7a7a7a; 
    border: none; 
    color: white; 
}

.btn-gray:hover {
    background-color: white; 
}

#notification-dropdown {
    position: absolute;
    top: 70px; /* Adjust as per your layout */
    right: 20px;
    width: 500px;
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
    display: none; /* Initially hidden */
    max-height: 300px;
    overflow-y: auto;
}

#notification-dropdown li {
    padding: 10px 16px;
    color: #333;
    cursor: pointer;
}

#notification-dropdown li:hover {
    background-color: #f1e8d9;
}

.badge-danger {
    background-color: #dc3545;
    color: white;
    font-size: 14px;
    padding: 4px 8px;
    border-radius: 50%;
}
#unread-count {
    display: inline-block; /* Ensures it's visible initially */
    background: red;
    color: white;
    border-radius: 50%;
    padding: 2px 5px;
    font-size: 12px;
    position: absolute;
    margin-left:-10px;
  
}

    </style>
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


                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link nav-link-black active" aria-current="page" href="cart.php">
                            <img src="/SnD_Shoppe-main/Assets/svg(icons)/shopping_cart.svg" alt="cart">
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-black" href="#" id="notification-icon">
                            <img src="Assets/svg(icons)/notifications.svg" alt="notif">
                            <?php if ($unread_count > 0): ?>
                                <span class="badge badge-danger" id="unread-count"><?php echo $unread_count; ?></span>
                            <?php endif; ?>
                        </a>
                        <ul id="notification-dropdown">
                            <?php
                            if (empty($result_notifications)) {
                                echo '<li>No new notifications</li>';
                            } else {
                            foreach ($result_notifications as $notification) {
                                echo '<li data-notif-id="' . htmlspecialchars($notification['notif_id']) . '">' 
                                    . htmlspecialchars($notification['message']) . '</br>' . date('m-d-y', strtotime($notification['created_at'])) .'</li>';
                            } }
                            ?>
                        </ul>
                    </li>
                    <!-- New Account Dropdown Menu -->
                    <li class="nav-item dropdown">
                        <a class="nav-link nav-link-black dropdown-toggle" href="#" id="accountDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="/SnD_Shoppe-main/Assets/svg(icons)/account_circle.svg" alt="account">
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="accountDropdown">
                            <li>
                                <a class="dropdown-item" href="mypurchase.php">My Account</a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <a class="dropdown-item" href="homepage.php">Home</a>
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
                <a href="cart.php" class="nav-link text-white">
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
                    <p><strong>Address:</strong> <?php echo htmlspecialchars($profile_data['address'] . ' ' . $profile_data['subdivision'] . ' ' . $profile_data['barangay']
                . ' ' . $profile_data['postal'] . ' ' . $profile_data['city'] . ' ' . $profile_data['place']); ?> <a href="javascript:void(0);" onclick="toggleEdit()" class="link-primary">Change</a></p>
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
                <div class="mb-3">
                    <label for="address" class="form-label">Address</label>
                    <input type="text" class="form-control" name="address" value="<?php echo htmlspecialchars($profile_data['address']); ?>" required>
                    <label for="address" class="form-label">Subdivsion</label>
                    <input type="text" class="form-control" name="subdivision" value="<?php echo htmlspecialchars($profile_data['subdivision']); ?>" required>
                    <label for="address" class="form-label">Barangay</label>
                    <input type="text" class="form-control" name="barangay" value="<?php echo htmlspecialchars($profile_data['barangay']); ?>" required>
                    <label for="address" class="form-label">Postal</label>
                    <input type="text" class="form-control" name="postal" value="<?php echo htmlspecialchars($profile_data['postal']); ?>" required>
                    <label for="address" class="form-label">City</label>
                    <input type="text" class="form-control" name="city" value="<?php echo htmlspecialchars($profile_data['city']); ?>" required>
                    <label for="address" class="form-label">Place</label>
                    <input type="text" class="form-control" name="place" value="<?php echo htmlspecialchars($profile_data['place']); ?>" required>
                
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
    <script>
function toggleEdit() {
    // Show the edit mode and hide the regular profile view
    document.getElementById("profile-edit").style.display = "block";
}

function cancelEdit() {
    // Hide the edit mode and show the regular profile view
    document.getElementById("profile-edit").style.display = "none";
}

$(document).ready(function () {
    // Handle notification item click
    $('#notification-dropdown').on('click', 'li', function () {
        var notifId = $(this).data('notif-id'); // Get notif_id from the clicked notification

        if (notifId) {
            // Make an AJAX request to mark the notification as read
            $.ajax({
                url: 'homepage.php', // PHP script to handle notification read
                method: 'GET',
                data: { notif_id: notifId },
                success: function (response) {
                    var result = JSON.parse(response);

                    if (result.status === 'success') {
                        // Remove the clicked notification
                        $('li[data-notif-id="' + notifId + '"]').remove();

                        // Update the unread count
                        var unreadCountElement = $('#unread-count');
                        var unreadCount = parseInt(unreadCountElement.text(), 10);

                        if (unreadCount > 1) {
                            unreadCountElement.text(unreadCount - 1);
                        } else {
                            unreadCountElement.fadeOut(); // Hide the badge when count reaches 0
                        }
                    } else {
                        console.error('Error: ' + result.message);
                    }
                },
                error: function (xhr, status, error) {
                    console.error('AJAX error:', error);
                }
            });
        }
    });

    // Toggle notification dropdown visibility
    $('#notification-icon').on('click', function (e) {
        e.preventDefault();
        $('#notification-dropdown').toggle(); // Toggle dropdown visibility
    });

    // Close dropdown when clicking outside
    $(document).on('click', function (e) {
        if (!$('#notification-icon').is(e.target) && $('#notification-icon').has(e.target).length === 0 &&
            !$('#notification-dropdown').is(e.target) && $('#notification-dropdown').has(e.target).length === 0) {
            $('#notification-dropdown').hide();
        }
    });
});
</script>
</body>
</html>
