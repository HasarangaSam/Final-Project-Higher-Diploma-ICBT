<?php
// Start session to manage customer data
session_start();

// Check if the customer is logged in
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php"); // Redirect to login page if the customer is not logged in
    exit();
}

// Include the database connection
require 'connection.php';

// If the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the data from the form
    $blog_id = $_POST['blog_id'];
    $comment_content = $_POST['comment'];
    $customer_id = $_SESSION['customer_id']; // Logged-in user

    // Get the current date for comment creation
    $created_at = date('Y-m-d H:i:s');

    // Find the next available comment_number for this blog_id
    $stmt = $pdo->prepare("SELECT MAX(comment_number) AS max_comment_number FROM blog_comment WHERE blog_id = ?");
    $stmt->execute([$blog_id]);
    $max_comment_number = $stmt->fetchColumn();

    // Calculate the next comment_number. If there are no comments, start from 1
    $comment_number = ($max_comment_number) ? $max_comment_number + 1 : 1;

    // Insert the comment into the database
    $stmt = $pdo->prepare("INSERT INTO blog_comment (comment_number, blog_id, customer_id, comment_content, created_at) 
                           VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$comment_number, $blog_id, $customer_id, $comment_content, $created_at]);

    // Redirect back to the blog view page with the correct blog_id
    header("Location: blogs.php"); // URL encode the blog_id
    exit();}
?>

