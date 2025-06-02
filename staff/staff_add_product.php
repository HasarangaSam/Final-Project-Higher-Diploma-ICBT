<?php 
session_start();
// Check if user is logged in as staff
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../login.php");
    exit();
}

// Database Connection
include('connection.php');

// Handling Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $category = $_POST['category'];
    $specifications = $_POST['specifications'];
    $price = $_POST['price']; // Single price input field for both old and new prices
    $availability = $_POST['availability']; // Single availability input field for both old and new availability
    $stock_quantity = $_POST['stock_quantity'];

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        // Save the uploaded image to the 'uploads' directory
        $image_name = $_FILES['image']['name'];
        $image_tmp = $_FILES['image']['tmp_name'];
        $image_path = '../uploads/' . basename($image_name);
        move_uploaded_file($image_tmp, $image_path);
    } else {
        // If no file is uploaded, use the Google link (if provided)
        $image_path = $_POST['image_url']; // If using a URL for the image
    }

    // Insert product data into the database
    $stmt = $pdo->prepare("INSERT INTO products (name, category, specifications, old_price, new_price, old_availability, new_availability, stock_quantity, image_url) 
                           VALUES (:name, :category, :specifications, :price, :price, :availability, :availability, :stock_quantity, :image_url)");
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':category', $category);
    $stmt->bindParam(':specifications', $specifications);
    $stmt->bindParam(':price', $price);
    $stmt->bindParam(':availability', $availability);
    $stmt->bindParam(':stock_quantity', $stock_quantity);
    $stmt->bindParam(':image_url', $image_path);

    if ($stmt->execute()) {
        echo "<script>alert('New product added successfully!'); window.location.href='staff_manage_products.php';</script>";
    } else {
        echo "<script>alert('Error adding product.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom Styles -->
    <link rel="stylesheet" href="staff_style.css">
</head>
<body>

<div class="wrapper">
    <!-- Sidebar -->
    <?php include('staff_sidebar.php'); ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container mt-5">
            <h2>Add New Product</h2>
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="name" class="form-label">Product Name</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>

                <div class="mb-3">
                    <label for="category" class="form-label">Category</label>
                    <select class="form-select" id="category" name="category" required>
                        <option value="CPU">CPU</option>
                        <option value="GPU">GPU</option>
                        <option value="Motherboard">Motherboard</option>
                        <option value="RAM">RAM</option>
                        <option value="Storage">Storage</option>
                        <option value="Mouse">Mouse</option>
                        <option value="Keyboard">Keyboard</option>
                        <option value="PSU">PSU</option>
                        <option value="Monitor">Monitor</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="specifications" class="form-label">Specifications</label>
                    <textarea class="form-control" id="specifications" name="specifications" rows="3" required></textarea>
                </div>

                <div class="mb-3">
                    <label for="price" class="form-label">Price</label>
                    <input type="number" class="form-control" id="price" name="price" step="any" required>
                </div>

                <div class="mb-3">
                    <label for="availability" class="form-label">Availability</label>
                    <select class="form-select" id="availability" name="availability" required>
                        <option value="in stock">In Stock</option>
                        <option value="out of stock">Out of Stock</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="stock_quantity" class="form-label">Stock Quantity</label>
                    <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" required>
                </div>

                <div class="mb-3">
                    <label for="image" class="form-label">Product Image</label>
                    <input type="file" class="form-control" id="image" name="image">
                    <small class="form-text text-muted">You can upload an image or use a Google image URL below.</small>
                </div>

                <div class="mb-3">
                    <label for="image_url" class="form-label">Or paste a Google Image URL</label>
                    <input type="text" class="form-control" id="image_url" name="image_url">
                </div>

                <button type="submit" class="btn btn-primary">Add Product</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
