<?php
session_start();
// Check if user is logged in as admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Database Connection
include('connection.php');

// Check if post_id is provided in the URL
if (isset($_GET['post_id'])) {
    $post_id = $_GET['post_id'];

    // Fetch forum post details 
    $stmt = $pdo->prepare("SELECT * FROM forums WHERE post_id = :post_id");
    $stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
    $stmt->execute();
    $post = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$post) {
        echo "<script>alert('Forum post not found.'); window.location.href='admin_manage_forum.php';</script>";
        exit();
    }
} else {
    echo "<script>alert('Post ID not provided.'); window.location.href='admin_manage_forum.php';</script>";
    exit();
}

// Handle answer deletion
if (isset($_GET['delete_answer_id']) && isset($_GET['post_id'])) {
    $answer_number = $_GET['delete_answer_id'];
    $post_id = $_GET['post_id'];

    // Delete the answer from the database based on both post_id and answer_number
    $stmt = $pdo->prepare("DELETE FROM forum_answer WHERE post_id = :post_id AND answer_number = :answer_number");
    $stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
    $stmt->bindParam(':answer_number', $answer_number, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo "<script>alert('Answer deleted successfully!'); window.location.href='view_forum_answers.php?post_id={$post_id}';</script>";
    } else {
        echo "<script>alert('Error deleting answer.');</script>";
    }
}

    // Fetch all answers for the forum post
    $stmt = $pdo->prepare("SELECT fa.*, c.first_name, c.last_name FROM forum_answer fa
                        JOIN customer c ON fa.customer_id = c.customer_id
                        WHERE fa.post_id = :post_id");
    $stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
    $stmt->execute();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Forum Answers</title>

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom Styles -->
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="wrapper">
    <!-- Sidebar -->
    <?php include('sidebar.php'); ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container mt-5">
            <h1>Answers for Forum Post: <?php echo htmlspecialchars($post['title']); ?></h1>

            <!-- Answers Table -->
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Answer #</th>
                        <th>Answerd By</th>
                        <th>Answer Content</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Loop through the answers and display them
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $fullName = htmlspecialchars($row['first_name']) . ' ' . htmlspecialchars($row['last_name']);
                        echo "<tr>";
                        echo "<td>{$row['answer_number']}</td>";
                        echo "<td>{$fullName}</td>";
                        echo "<td>" . htmlspecialchars($row['content']) . "</td>";
                        echo "<td>{$row['created_at']}</td>";
                        echo "<td>
                                <a href='view_forum_answers.php?post_id={$post_id}&delete_answer_id={$row['answer_number']}' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure you want to delete this answer?\")'><i class='bi bi-trash'></i> Delete Answer</a>
                              </td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>

            <a href="admin_manage_forum.php" class="btn btn-secondary">Back to Forum Management</a>
        </div>
    </div>
</div>

</body>
</html>
