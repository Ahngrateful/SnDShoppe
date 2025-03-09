<?php
session_start();
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "db_sdshoppe";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
  header("Location: haveacc.php"); // Redirect to login if not logged in
  exit;
}

// Fetch product details
if (isset($_GET['id'])) {
    $product_id = intval($_GET['id']);
    $sql = "SELECT * FROM products WHERE product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
    } else {
        echo "Product not found.";
        exit;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name = $_POST['product_name'];
    $price = $_POST['price'];

    $sql = "UPDATE products SET product_name = ?, price = ? WHERE product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sdi", $product_name, $price, $product_id);

    if ($stmt->execute()) {
        echo "Product updated successfully.";
        header("Location: product_list.php");
        exit;
    } else {
        echo "Error updating product: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<link rel="stylesheet" href="edit_product.css">
<html>
   

<head>
    <title>Edit Product</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Kumbh+Sans:wght@100..900&family=Playfair+Display+SC:ital,wght@0,400;0,700;0,900;1,400;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900&display=swap');

body {
    font-family: 'Poppins', sans-serif;
    background-color: #FFF8DC;
    background-blend-mode: multiply;
    background-position: center;
    background-size: cover;
    background-repeat: no-repeat;
    min-height: 100vh; 
    margin: 0; 
    padding: 0; 
    display: flex;
    align-items: center;
    justify-content: center;
}

.container_editproduct {
    background-color: #fdf6e0;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    width: 90%;
    max-width: 500px;
    text-align: center;
}

h1 {
    font-family: 'Playfair Display SC', serif;
    font-size: 2rem;
    margin-bottom: 20px;
    color: #1e1e1e;
    position: absolute;
    top: 50px; /* Move the title above the container */
    left: 50%;
    transform: translateX(-50%);
    background-color: #fdf6e0;
    padding: 0 10px;
}

form label {
    font-size: 1rem;
    color: #4b4b4b;
    display: block;
    margin-bottom: 5px;
    text-align: left;
}

form input {
    width: 100%;
    padding: 10px 15px;
    margin-bottom: 15px;
    border: 1px solid #d9b65d;
    border-radius: 8px;
    font-size: 1rem;
    box-sizing: border-box;
    background-color: #fef8e6;
    transition: all 0.3s ease;
}

form input:focus {
    outline: none;
    border-color: #e2c391;
    background-color: #fff5e3;
}

button {
    background-color: #d9b65d;
    color: #ffffff;
    font-size: 1rem;
    font-weight: bold;
    padding: 12px 20px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    width: 100%;
    margin-top: 10px;
}

button:hover {
    background-color: #c49b50;
}

button:active {
    background-color: #b08847;
}

@media (max-width: 768px) {
    .container_editproduct {
        padding: 20px;
    }

    h1 {
        font-size: 1.5rem;
    }

    button {
        font-size: 0.9rem;
    }
}
    </style>
</head>
<body>
    <h1>Edit Product</h1>
    <form method="POST">
        <label>Product Name:</label>
        <input type="text" name="product_name" value="<?= htmlspecialchars($product['product_name']) ?>" required><br>
        <label>Price:</label>
        <input type="number" step="0.01" name="price" value="<?= $product['price'] ?>" required><br>
        <button type="submit">Save Changes</button>
    </form>
</body>
</html>
