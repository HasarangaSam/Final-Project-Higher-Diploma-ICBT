<?php 
session_start();
// Check if user is logged in as staff
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../login.php");
    exit();
}

// Database Connection
include('connection.php');

// Initialize filter variables
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';
$availability_filter = isset($_GET['availability']) ? $_GET['availability'] : '';

// Base SQL query
$sql = "SELECT * FROM products WHERE 1";

// Add category filter if selected
if (!empty($category_filter)) {
    $sql .= " AND category = :category";
}

// Add availability filter if selected
if (!empty($availability_filter)) {
    $sql .= " AND new_availability = :availability";
}

// Prepare the query
$stmt = $pdo->prepare($sql);

// Bind parameters if filters are set
if (!empty($category_filter)) {
    $stmt->bindParam(':category', $category_filter);
}

if (!empty($availability_filter)) {
    $stmt->bindParam(':availability', $availability_filter);
}

// Execute the query
$stmt->execute();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff - Product Management</title>

    <!-- Bootstrap & Icons -->
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
        <div class="container">
            <h1 class="mt-4">Product Management</h1>

            <!-- Filter Form -->
            <form method="GET" class="mb-3">
                <div class="row">
                    <div class="col-md-4">
                        <select class="form-select" name="category">
                            <option value="">Select Category</option>
                            <option value="CPU" <?php echo ($category_filter == 'CPU') ? 'selected' : ''; ?>>CPU</option>
                            <option value="GPU" <?php echo ($category_filter == 'GPU') ? 'selected' : ''; ?>>GPU</option>
                            <option value="Motherboard" <?php echo ($category_filter == 'Motherboard') ? 'selected' : ''; ?>>Motherboard</option>
                            <option value="RAM" <?php echo ($category_filter == 'RAM') ? 'selected' : ''; ?>>RAM</option>
                            <option value="Storage" <?php echo ($category_filter == 'Storage') ? 'selected' : ''; ?>>Storage</option>
                            <option value="Mouse" <?php echo ($category_filter == 'Mouse') ? 'selected' : ''; ?>>Mouse</option>
                            <option value="Keyboard" <?php echo ($category_filter == 'Keyboard') ? 'selected' : ''; ?>>Keyboard</option>
                            <option value="PSU" <?php echo ($category_filter == 'PSU') ? 'selected' : ''; ?>>PSU</option>
                            <option value="Monitor" <?php echo ($category_filter == 'Monitor') ? 'selected' : ''; ?>>Monitor</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <select class="form-select" name="availability">
                            <option value="">Select Availability</option>
                            <option value="in stock" <?php echo ($availability_filter == 'in stock') ? 'selected' : ''; ?>>In Stock</option>
                            <option value="out of stock" <?php echo ($availability_filter == 'out of stock') ? 'selected' : ''; ?>>Out of Stock</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </div>
            </form>

            <!-- Add New Product Button -->
            <a href="staff_add_product.php" class="btn btn-primary mb-3"><i class="bi bi-plus-circle"></i> Add New Product</a>

            <!-- Products Table -->
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock Quantity</th>
                        <th>Availability</th>
                        <th>Image</th> <!-- Display Image -->
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        // Check if image is a URL or a relative path
                        if (filter_var($row['image_url'], FILTER_VALIDATE_URL)) {
                            // If it's a URL, use it directly
                            $image = $row['image_url'];
                        } else {
                            // If it's a relative path 
                            $image = $row['image_url']; 
                        }

                        // Display the product details
                        echo "<tr>";
                        echo "<td>{$row['product_id']}</td>";
                        echo "<td>{$row['name']}</td>";
                        echo "<td>{$row['category']}</td>";
                        echo "<td>{$row['new_price']}</td>";
                        echo "<td>{$row['stock_quantity']}</td>";
                        echo "<td>{$row['new_availability']}</td>";
                        echo "<td><img src='{$image}' alt='Product Image' style='width: 100px; height: auto;'></td>"; // Display product image
                        echo "<td>
                                <a href='staff_edit_product.php?id={$row['product_id']}' class='btn btn-warning btn-sm'><i class='bi bi-pencil'></i> Edit</a>
                                <a href='staff_delete_product.php?id={$row['product_id']}' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure you want to delete this product?\")'><i class='bi bi-trash'></i> Delete</a>
                              </td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
