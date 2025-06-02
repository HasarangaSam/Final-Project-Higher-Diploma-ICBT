<?php
session_start();
// Check if user is logged in as admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Database Connection
include('../connection.php');

// Check if a valid customer ID is provided
if (isset($_GET['id'])) {
    $customer_id = $_GET['id'];

    // Prepare and execute the deletion query
    $stmt = $pdo->prepare("DELETE FROM customer WHERE customer_id = :customer_id");
    $stmt->bindParam(':customer_id', $customer_id, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        echo "<script>alert('Customer deleted successfully!'); window.location.href='admin_manage_users.php';</script>";
    } else {
        echo "<script>alert('Error deleting customer.'); window.location.href='admin_manage_users.php';</script>";
    }
} else {
    echo "<script>alert('Invalid Customer ID.'); window.location.href='admin_manage_users.php';</script>";
}
?>
