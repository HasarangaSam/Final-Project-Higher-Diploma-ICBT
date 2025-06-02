<?php
session_start();
include("connection.php"); // Include the PDO connection file

// Check if product_id and customer_id are sent via POST
if (isset($_POST['product_id']) && isset($_POST['customer_id'])) {
    $product_id = $_POST['product_id'];
    $customer_id = $_POST['customer_id'];

    try {
        // Check if the product is already in the wishlist for this customer
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM notify_wishlist WHERE customer_id = :customer_id AND product_id = :product_id");
        $checkStmt->bindParam(':customer_id', $customer_id, PDO::PARAM_INT);
        $checkStmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $checkStmt->execute();
        $exists = $checkStmt->fetchColumn();

        if ($exists > 0) {
            echo "This product is already in your Notify Wishlist.";
        } else {
            // Prepare the SQL statement to insert into notify_wishlist table
            $stmt = $pdo->prepare("INSERT INTO notify_wishlist (customer_id, product_id) VALUES (:customer_id, :product_id)");
            $stmt->bindParam(':customer_id', $customer_id, PDO::PARAM_INT);
            $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);

            // Execute the query
            if ($stmt->execute()) {
                echo "Product added to your Notify Wishlist successfully!";
            } else {
                echo "Error: Could not add the product to the wishlist.";
            }
        }
    } catch (PDOException $e) {
        // Handle any PDO errors
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "Error: Missing required parameters.";
}
?>

