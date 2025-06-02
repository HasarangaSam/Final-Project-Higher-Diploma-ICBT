<?php
session_start();
// Check if user is logged in as staff
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../login.php");
    exit();
}

// Database Connection
include('connection.php');

// Check if a valid customer ID is provided
if (isset($_GET['id'])) {
    $customer_id = $_GET['id'];

    // Prepare and execute the deletion query
    $stmt = $pdo->prepare("DELETE FROM customer WHERE customer_id = :customer_id");
    $stmt->bindParam(':customer_id', $customer_id, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        echo "<script>alert('Customer deleted successfully!'); window.location.href='staff_manage_customers.php';</script>";
    } else {
        echo "<script>alert('Error deleting customer.'); window.location.href='staff_manage_customers.php';</script>";
    }
} else {
    echo "<script>alert('Invalid Customer ID.'); window.location.href='staff_manage_customers.php';</script>";
}
?>
