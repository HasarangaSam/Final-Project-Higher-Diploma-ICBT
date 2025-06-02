<?php
session_start();
// Check if user is logged in as admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Database Connection
include('../connection.php');

// Check if blog_id is passed in the URL
if (isset($_GET['id'])) {
    $blog_id = $_GET['id'];

    // Delete all comments related to this blog from the blog_comment table
    $stmt = $pdo->prepare("DELETE FROM blog_comment WHERE blog_id = :blog_id");
    $stmt->bindParam(':blog_id', $blog_id, PDO::PARAM_INT);
    $stmt->execute();

    // Delete the blog from the blogs table
    $stmt = $pdo->prepare("DELETE FROM blogs WHERE blog_id = :blog_id");
    $stmt->bindParam(':blog_id', $blog_id, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        echo "<script>alert('Blog and related comments deleted successfully!'); window.location.href='admin_manage_blog.php';</script>";
    } else {
        echo "<script>alert('Error deleting blog.'); window.location.href='admin_manage_blog.php';</script>";
    }
} else {
    echo "<script>alert('Blog ID not specified.'); window.location.href='admin_manage_blog.php';</script>";
}
?>
