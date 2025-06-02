<?php
session_start();
// Check if user is logged in as admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Database Connection
include('connection.php');

// Check if product_id is passed in the URL
if (isset($_GET['id'])) {
    $product_id = $_GET['id'];

    // Prepare the SQL statement to delete the product from the database
    $stmt = $pdo->prepare("DELETE FROM products WHERE product_id = :product_id");
    $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);

    // Execute the deletion
    if ($stmt->execute()) {
        // Redirect to the product management page after successful deletion
        echo "<script>alert('Product deleted successfully!'); window.location.href='admin_manage_products.php';</script>";
    } else {
        echo "<script>alert('Error deleting product.'); window.location.href='admin_manage_products.php';</script>";
    }
} else {
    echo "<script>alert('Product ID not specified.'); window.location.href='admin_manage_products.php';</script>";
}
?>
