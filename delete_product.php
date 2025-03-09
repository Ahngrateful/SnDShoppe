<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "db_sdshoppe");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Delete product
if (isset($_GET['id'])) {
    $product_id = intval($_GET['id']);
    $sql = "DELETE FROM products WHERE product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);

    if ($stmt->execute()) {
        echo "Product deleted successfully.";
        header("Location: product_list.php");
        exit;
    } else {
        echo "Error deleting product: " . $conn->error;
    }
} else {
    echo "Invalid request.";
}
?>
