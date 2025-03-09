<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "db_sdshoppe");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch the product ID from the URL
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $color_names = $_POST['color_name'];
    $color_images = $_FILES['color_image'];

    // Directory to save uploaded files
    $upload_dir = 'new_products' . DIRECTORY_SEPARATOR . 'colors' . DIRECTORY_SEPARATOR;
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true); // Create directory if it doesn't exist
    }

    for ($i = 0; $i < count($color_names); $i++) {
        $color_name = htmlspecialchars($color_names[$i]);
        $image_tmp = $color_images['tmp_name'][$i];
        $image_name = uniqid() . "_" . basename($color_images['name'][$i]);
        $image_path = $upload_dir . $image_name;

        // Move uploaded file to the upload directory
        if (move_uploaded_file($image_tmp, $image_path)) {
            // Insert data into the database
            $query = "INSERT INTO product_colors (product_id, color_name, product_pic) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($query);
            if ($stmt) {
                $stmt->bind_param("iss", $product_id, $color_name, $image_path);
                $stmt->execute();
                $stmt->close();
            } else {
                echo "Error preparing the statement: " . $conn->error;
            }
        } else {
            echo "Error uploading file for color: " . $color_name;
        }
    }
    echo "<script> alert('Colors saved successfully!');window.location.href = 'add_colors.php';</script>";
    // Redirect back to product list or refresh the page
    
    exit;
}
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Kumbh+Sans:wght@100..900&family=Playfair+Display+SC:ital,wght@0,400;0,700;0,900;1,400;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900&display=swap');
    
    body {
        background: url(/Assets/images/) rgba(0, 0, 0, 0.3);
        background-blend-mode: multiply;
        background-position: center;
        background-size: cover;
        background-repeat: no-repeat;
        min-height: 100vh;
        overflow-y: auto;
        margin: 0;
        padding: 0;
        font-family: 'Poppins', sans-serif;
        background-color: #faf4e1;
        font-family: "Playfair Display", serif;
    }

    h1 {
        font-family: "Playfair Display SC", serif;
        font-size: 50px;
        color: #1e1e1e;
        text-align: center;
        margin-top: 20px;
    }

    .form-container {
        max-width: 600px;
        margin: 40px auto;
        background-color: #faf4e1;
        padding: 20px;
        border: 1px solid #d1b88a;
        border-radius: 8px;
        box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
    }

    label {
        display: block;
        margin-bottom: 10px;
        font-weight: bold;
        color: #1e1e1e;
    }

    input[type="text"], input[type="file"] {
        width: 100%;
        padding: 10px;
        border: 1px solid #d9b65d;
        border-radius: 5px;
        background-color: #f1e8d9;
        color: #1e1e1e;
        font-size: 16px;
        margin-bottom: 20px;
    }

    input[type="text"]:focus, input[type="file"]:focus {
        outline: none;
        border-color: #e2d1b3;
        background-color: #faf4e1;
    }

    /*button {
        width: 50%;
        padding: 10px;
        background-color: #d9b65d;
        border: none;
        border-radius: 5px;
        color: white;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }*/
    
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
        background-color: #e2d1b3;
        color: #1e1e1e;
    }

    .back-button {
        display: block;
        width: 150px;
        padding: 10px;
        margin: 20px auto;
        text-align: center;
        background-color: #d9b65d;
        border: none;
        border-radius: 5px;
        color: white;
        font-size: 14px;
        font-weight: bold;
        text-decoration: none;
        transition: background-color 0.3s ease;
    }

    .back-button:hover {
        background-color: #e2d1b3;
        color: #1e1e1e;
    }

    .save-button {
        background-color: #d9b65d;
        border: none;
        border-radius: 5px;
        color: white;
        font-size: 14px;
        font-weight: bold;
        padding: 10px 20px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }
    .save-button:hover {
        background-color: #e2d1b3;
        color: #1e1e1e;
    }

</style>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Colors</title>
    
</head>
<body>
<h1>Add Colors for Product:  ID <?= htmlspecialchars($product_id) ?></h1>
<div class="form-container">
    <form method="POST" enctype="multipart/form-data">
        <div class="color-fields">
            <div class="color-entry">
                <label for="color_name_1">Color Name:</label>
                <input type="text" name="color_name[]" id="color_name_1" placeholder="Enter color name" required>

                <label for="color_image_1">Upload Color Image:</label>
                <input type="file" name="color_image[]" id="color_image_1" accept="image/*" required>
            </div>
        </div>

        <button type="button" id="add-color">Add Another Color</button>
       <button type="submit">Save</button>
    </form>
    <a href="product_list.php" class="back-button">Back to Products</a>
</div>

<script>
    document.getElementById('add-color').addEventListener('click', function () {
        const colorFields = document.querySelector('.color-fields');
        const newIndex = colorFields.children.length + 1;

        const newColorEntry = document.createElement('div');
        newColorEntry.classList.add('color-entry');

        newColorEntry.innerHTML = `
            <label for="color_name_${newIndex}">Color Name:</label>
            <input type="text" name="color_name[]" id="color_name_${newIndex}" placeholder="Enter color name" required>
            
            <label for="color_image_${newIndex}">Upload Color Image:</label>
            <input type="file" name="color_image[]" id="color_image_${newIndex}" accept="image/*" required>
        `;

        colorFields.appendChild(newColorEntry);
    });
</script>


</body>
</html>
