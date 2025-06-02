<?php
session_start();
// Check if user is logged in as admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Database Connection
include('../connection.php');

// Check if a valid staff ID is provided
if (isset($_GET['id'])) {
    $staff_id = $_GET['id'];

    // Prepare and execute the deletion query
    $stmt = $pdo->prepare("DELETE FROM staff WHERE staff_id = :staff_id");
    $stmt->bindParam(':staff_id', $staff_id, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        echo "<script>alert('Staff member deleted successfully!'); window.location.href='admin_manage_staff.php';</script>";
    } else {
        echo "<script>alert('Error deleting staff member.'); window.location.href='admin_manage_staff.php';</script>";
    }
} else {
    echo "<script>alert('Invalid Staff ID.'); window.location.href='admin_manage_staff.php';</script>";
}
?>
