<?php
session_start();
include('connection.php');

if (isset($_SESSION['customer_id'])) {
    $isLoggedIn = true;
} else {
    $isLoggedIn = false;
}
include("connection.php");

// Check if the clear button was pressed to reset the comparison list
if (isset($_POST['clear_compare'])) {
    unset($_SESSION['compare']); // Reset the compare list
}

// Get the products from the compare list stored in session
if (isset($_SESSION['compare']) && count($_SESSION['compare']) >= 2) {
    // Prepare a query to fetch details of the products in the compare list
    $productIds = implode(',', $_SESSION['compare']);
    $stmt = $pdo->prepare("SELECT `product_id`, `name`, `specifications`, `image_url`, `new_price` FROM `products` WHERE `product_id` IN ($productIds)");
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $products = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Compare Products</title>
  <link rel="icon" type="image/png" href="images/logo.jpg">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
</head>
<body>
<!-- Navigation Bar -->
<?php include('nav.php'); ?>

<section id="compare" class="container py-5">
    <h1 class="text-center mb-4 text-white">Compare Products</h1>

    <?php if (isset($products) && count($products) > 0): ?>
        <div class="row">
            <?php foreach ($products as $product): ?>
                <div class="col-md-4">
                    <div class="card shadow-sm">
                        <?php
                            // Check if the image path is a valid URL or a relative path
                            $imagePath = $product['image_url'];
                            if (strpos($imagePath, '../') === 0) {
                                // Remove "../" if it's a relative path
                                $imagePath = substr($imagePath, 3);
                            }
                        ?>
                        <img src="<?php echo htmlspecialchars($imagePath); ?>" class="card-img-top" alt="Product Image">
                        <div class="card-body">
                            <h5 class="card-title"><b><?php echo htmlspecialchars($product['name']); ?></b></h5>
                            <p class="card-text"><?php echo htmlspecialchars($product['specifications']); ?></p>
                            <p><strong>Price: Rs.<?php echo number_format($product['new_price'], 2); ?></strong></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Clear Compare List Button -->
        <form method="POST">
            <button type="submit" name="clear_compare" class="btn btn-warning mt-4">Clear Compare List</button>
        </form>
    <?php else: ?>
        <p class="text-white">Please select at least two products to compare.</p>
    <?php endif; ?>

    <!-- Close Button -->
    <button onclick="window.location.href = 'products.php';" class="btn btn-danger mt-4">Close</button>
</section>

<?php include('footer.php'); ?>

</body>
</html>


