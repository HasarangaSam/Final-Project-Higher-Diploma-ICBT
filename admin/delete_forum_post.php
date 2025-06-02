<?php
session_start();
// Check if user is logged in as admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Database Connection
include('../connection.php');

// Check if post_id is passed in the URL
if (isset($_GET['id'])) {
    $post_id = $_GET['id'];

    // Delete all answers related to this forum post
    $stmt = $pdo->prepare("DELETE FROM forum_answer WHERE post_id = :post_id");
    $stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
    $stmt->execute();

    // Delete the forum post from the forums table
    $stmt = $pdo->prepare("DELETE FROM forums WHERE post_id = :post_id");
    $stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
    if ($stmt->execute()) {
        echo "<script>alert('Forum post and related answers deleted successfully!'); window.location.href='admin_manage_forum.php';</script>";
    } else {
        echo "<script>alert('Error deleting forum post.'); window.location.href='admin_manage_forum.php';</script>";
    }
} else {
    echo "<script>alert('Forum Post ID not specified.'); window.location.href='admin_manage_forum.php';</script>";
}
?>
