<?php
session_start();

// Check if the product_id is provided
if (isset($_GET['product_id'])) {
    $productId = $_GET['product_id'];

    // Initialize compare session array if not set
    if (!isset($_SESSION['compare'])) {
        $_SESSION['compare'] = [];
    }

    // Add the product ID to the compare list if it's not already added
    if (!in_array($productId, $_SESSION['compare'])) {
        $_SESSION['compare'][] = $productId;
    }

    // Respond with success
    echo "Product added to compare list.";
}
?>
