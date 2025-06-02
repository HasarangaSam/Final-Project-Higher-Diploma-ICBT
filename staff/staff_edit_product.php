<?php
session_start();
// Check if user is logged in as staff
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../login.php");
    exit();
}

// Database Connection
include('../connection.php');

// Fetch existing product data for editing
if (isset($_GET['id'])) {
    $product_id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = :product_id");
    $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        echo "<script>alert('Product not found.'); window.location.href='staff_manage_products.php';</script>";
        exit();
    }
} else {
    echo "<script>alert('Invalid Product ID.'); window.location.href='staff_manage_products.php';</script>";
    exit();
}

// Handling Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $category = $_POST['category'];
    $specifications = $_POST['specifications'];
    $old_price = $product['new_price']; // Keep the previous price for comparison
    $new_price = $_POST['price'];
    $old_availability = $product['new_availability']; // Keep previous availability for comparison
    $new_availability = $_POST['availability'];
    $stock_quantity = $_POST['stock_quantity'];

    // Validate stock and availability
    if ($new_availability == 'in stock' && $stock_quantity == 0) {
        echo "<script>alert('Product cannot be set as In Stock with 0 stock.');</script>";
        exit();
    }

    if ($new_availability == 'out of stock') {
        $stock_quantity = 0; // Automatically set stock quantity to 0 if set to out of stock
    }

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        // Save the uploaded image to the 'uploads' directory inside staff folder
        $image_name = $_FILES['image']['name'];
        $image_tmp = $_FILES['image']['tmp_name'];

        // Define the image path (relative to the root directory for customer access)
    $image_path = '../uploads/' . basename($image_name); // Public path

    // Move the uploaded image to the 'uploads' directory in the root directory
    move_uploaded_file($image_tmp, '../uploads/' . basename($image_name)); 
    } else {
        // If no file is uploaded, use the Google link (if provided)
        $image_path = $_POST['image_url']; // If using a URL for the image
    }

    // Update product data in the database
    $stmt = $pdo->prepare("UPDATE products SET name = :name, category = :category, specifications = :specifications, 
        old_price = :old_price, new_price = :new_price, old_availability = :old_availability, new_availability = :new_availability, 
        stock_quantity = :stock_quantity, image_url = :image_url WHERE product_id = :product_id");

    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':category', $category);
    $stmt->bindParam(':specifications', $specifications);
    $stmt->bindParam(':old_price', $old_price);
    $stmt->bindParam(':new_price', $new_price);
    $stmt->bindParam(':old_availability', $old_availability);
    $stmt->bindParam(':new_availability', $new_availability);
    $stmt->bindParam(':stock_quantity', $stock_quantity);
    $stmt->bindParam(':image_url', $image_path); // Save relative path
    $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        // Check if the price dropped
        if ($new_price < $old_price) {
            // Add a notification for price drop
            $message = "Price dropped for the product: $name. New Price: Rs. $new_price";
            $stmt = $pdo->prepare("SELECT c.email, c.customer_id FROM notify_wishlist nw 
                                   INNER JOIN customer c ON nw.customer_id = c.customer_id 
                                   WHERE nw.product_id = :product_id");
            $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
            $stmt->execute();

            // Loop through all customers in the wishlist
            while ($wishlist_user = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // Insert notification for each user in the wishlist
                $stmt_insert = $pdo->prepare("INSERT INTO notifications (customer_id, product_id, message, created_at) 
                                              VALUES (:customer_id, :product_id, :message, NOW())");
                $stmt_insert->bindParam(':customer_id', $wishlist_user['customer_id']);
                $stmt_insert->bindParam(':product_id', $product_id, PDO::PARAM_INT);
                $stmt_insert->bindParam(':message', $message);
                $stmt_insert->execute();

                // Send email to the customer about the price drop
                $to = $wishlist_user['email'];  // Fetch the email from the customer table
                $subject = "Price Drop Notification for $name";
                $body = "Hello,\n\nThe price of $name has dropped. New Price: Rs. $new_price.\n\nBest regards,\nDilan Computers";
                $headers = 'From: yourGmailAddress@gmail.com' . "\r\n" .
                    'Reply-To: yourGmailAddress@gmail.com' . "\r\n" .
                    'X-Mailer: PHP/' . phpversion();

                // Send the email
                mail($to, $subject, $body, $headers);
            }
        }

        // Check if availability changed to 'in stock' and stock is now > 0
        if ($old_availability == 'out of stock' && $new_availability == 'in stock' && $stock_quantity > 0) {
            $message = "The product: $name is now back in stock!";
            $stmt = $pdo->prepare("SELECT c.email, c.customer_id FROM notify_wishlist nw 
                                   INNER JOIN customer c ON nw.customer_id = c.customer_id 
                                   WHERE nw.product_id = :product_id");
            $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
            $stmt->execute();

            // Loop through all customers in the wishlist
            while ($wishlist_user = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // Insert notification for each user in the wishlist
                $stmt_insert = $pdo->prepare("INSERT INTO notifications (customer_id, product_id, message, created_at) 
                                              VALUES (:customer_id, :product_id, :message, NOW())");
                $stmt_insert->bindParam(':customer_id', $wishlist_user['customer_id']);
                $stmt_insert->bindParam(':product_id', $product_id, PDO::PARAM_INT);
                $stmt_insert->bindParam(':message', $message);
                $stmt_insert->execute();

                // Send email to the customer about the stock update
                $to = $wishlist_user['email'];  // Fetch the email from the customer table
                $subject = "Stock Update for $name";
                $body = "Hello,\n\nThe product $name is now back in stock! Hurry up and buy now.\n\nBest regards,\nDilan Computers";
                $headers = 'From: yourGmailAddress@gmail.com' . "\r\n" .
                    'Reply-To: yourGmailAddress@gmail.com' . "\r\n" .
                    'X-Mailer: PHP/' . phpversion();

                // Send the email
                mail($to, $subject, $body, $headers);
            }
        }

        echo "<script>alert('Product updated successfully!'); window.location.href='staff_manage_products.php';</script>";
    } else {
        echo "<script>alert('Error updating product.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="staff_style.css">

    <script>
        function validateForm() {
            var price = document.getElementById("price").value;
            var availability = document.getElementById("availability").value;
            var stock_quantity = document.getElementById("stock_quantity").value;

            if (availability == 'in stock' && stock_quantity == 0) {
                alert("Product cannot be set as In Stock with 0 stock.");
                return false;
            }
            return true;
        }
    </script>
</head>
<body>

<div class="wrapper">
    <?php include('staff_sidebar.php'); ?>
    <div class="main-content">
        <div class="container mt-5">
            <h2>Edit Product</h2>
            <form method="POST" onsubmit="return validateForm()" enctype="multipart/form-data">
                
                <!-- Product Name -->
                <div class="mb-3">
                    <label for="name" class="form-label">Product Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                </div>

                <!-- Category Selection -->
                <div class="mb-3">
                    <label for="category" class="form-label">Category</label>
                    <select class="form-select" id="category" name="category" required>
                        <?php
                        $categories = ['CPU', 'GPU', 'Motherboard', 'RAM', 'Storage', 'Mouse', 'Keyboard', 'Laptop', 'PSU', 'Monitor'];
                        foreach ($categories as $cat) {
                            echo "<option value='$cat' " . ($product['category'] == $cat ? 'selected' : '') . ">$cat</option>";
                        }
                        ?>
                    </select>
                </div>

                <!-- Specifications -->
                <div class="mb-3">
                    <label for="specifications" class="form-label">Specifications</label>
                    <textarea class="form-control" id="specifications" name="specifications" rows="3" required><?php echo htmlspecialchars($product['specifications']); ?></textarea>
                </div>

                <!-- Price -->
                <div class="mb-3">
                    <label for="price" class="form-label">Price</label>
                    <input type="number" class="form-control" id="price" name="price" value="<?php echo htmlspecialchars($product['new_price']); ?>" step="any" required>
                </div>

                <!-- Availability -->
                <div class="mb-3">
                    <label for="availability" class="form-label">Availability</label>
                    <select class="form-select" id="availability" name="availability" required>
                        <option value="in stock" <?php echo ($product['new_availability'] == 'in stock' ? 'selected' : ''); ?>>In Stock</option>
                        <option value="out of stock" <?php echo ($product['new_availability'] == 'out of stock' ? 'selected' : ''); ?>>Out of Stock</option>
                    </select>
                </div>

                <!-- Stock Quantity -->
                <div class="mb-3">
                    <label for="stock_quantity" class="form-label">Stock Quantity</label>
                    <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" value="<?php echo htmlspecialchars($product['stock_quantity']); ?>" required>
                </div>

                <!-- Image Upload -->
                <div class="mb-3">
                    <label for="image" class="form-label">Product Image (Upload New)</label>
                    <input type="file" class="form-control" id="image" name="image">
                    <small class="form-text text-muted">Upload an image OR use an existing Google image URL.</small>
                </div>

                <!-- Image URL -->
                <div class="mb-3">
                    <label for="image_url" class="form-label">Or Enter Image URL</label>
                    <input type="text" class="form-control" id="image_url" name="image_url" value="<?php echo htmlspecialchars($product['image_url']); ?>">
                </div>

                <!-- Display Current Image -->
                <?php if (!empty($product['image_url'])): ?>
                <div class="mb-3">
                    <label class="form-label">Current Image</label>
                    <div>
                        <?php
                        // Check if image exists in the folder
                        if (file_exists('../' . $product['image_url'])) {
                            echo '<img src="../' . htmlspecialchars($product['image_url']) . '" alt="Product Image" class="img-thumbnail" width="200">';
                        } else {
                            // If not, display the URL image
                            echo '<img src="' . htmlspecialchars($product['image_url']) . '" alt="Product Image" class="img-thumbnail" width="200">';
                        }
                        ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary">Update Product</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
