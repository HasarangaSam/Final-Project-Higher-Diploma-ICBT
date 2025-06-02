<?php
// Include database connection
include('../connection.php');

// Check if the category is set in the request
if (isset($_POST['category'])) {
    $category = $_POST['category'];

    // Prepare the SQL query to fetch products based on the selected category
    $stmt = $pdo->prepare("SELECT * FROM products WHERE category = :category");
    $stmt->bindParam(':category', $category);
    $stmt->execute();

    // Fetch the products and return them as an array
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return the products as JSON
    echo json_encode($products);
} else {
    // If no category is provided, return an empty array
    echo json_encode([]);
}
?>
