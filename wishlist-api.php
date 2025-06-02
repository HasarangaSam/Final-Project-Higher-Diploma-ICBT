<?php
session_start();
include('connection.php'); // Your database connection

// Default empty array if no products in sessionStorage
$products = [];

try {
    if (isset($_POST['wishlist'])) {
        $wishlist = json_decode($_POST['wishlist'], true); // Decode the JSON array sent from JS

        if (empty($wishlist)) {
            throw new Exception('Wishlist is empty.');
        }

        // Convert wishlist array into a comma-separated string
        $wishlistIds = implode(',', array_map('intval', $wishlist));

        // SQL query to get product details based on the product IDs
        $sql = "SELECT `product_id`, `name`, `category`, `specifications`, `old_price`, `new_price`, `old_availability`, `new_availability`, `stock_quantity`, `image_url`
                FROM `products`
                WHERE `product_id` IN ($wishlistIds)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute();

        // Fetch all the products
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // If no products are found, throw an error
        if (empty($products)) {
            throw new Exception('No products found for the given wishlist IDs.');
        }
    } else {
        throw new Exception('No wishlist data received.');
    }
} catch (Exception $e) {
    // Log detailed error message for debugging
    error_log("Error: " . $e->getMessage()); // Log error to the server
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}

// If we get here, output the product data as JSON
header('Content-Type: application/json');
echo json_encode($products);
?>