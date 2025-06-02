<?php
session_start();
require 'connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Fetch the necessary data from the form
    $post_id = $_POST['post_id']; // Forum post ID
    $content = $_POST['content']; // The answer content
    $customer_id = $_SESSION['customer_id']; // Get the customer ID from the session (make sure the user is logged in)

    // Ensure the user is logged in before proceeding
    if (!$customer_id) {
        // Redirect to login if not logged in
        header("Location: login.php");
        exit;
    }

    // Get the last answer_number for the specific post (for auto-increment)
    $stmt = $pdo->prepare("SELECT MAX(answer_number) AS max_answer_number FROM forum_answer WHERE post_id = ?");
    $stmt->execute([$post_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $answer_number = $result['max_answer_number'] ? $result['max_answer_number'] + 1 : 1; // Start from 1 if no previous answers

    // Insert the new answer into the forum_answer table
    $stmt = $pdo->prepare("INSERT INTO forum_answer (answer_number, post_id, customer_id, content, created_at) 
                           VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$answer_number, $post_id, $customer_id, $content]);

    // Redirect back to the forum view page where the post is displayed
    header("Location: forum_view.php?post_id=$post_id");
    exit;
}
?>
