<?php
session_start();
// Check if user is logged in as staff
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../login.php");
    exit();
}

// Database Connection
include('../connection.php');

// Check if blog_id is provided in the URL
if (isset($_GET['blog_id'])) {
    $blog_id = $_GET['blog_id'];

    // Fetch blog details 
    $stmt = $pdo->prepare("SELECT * FROM blogs WHERE blog_id = :blog_id");
    $stmt->bindParam(':blog_id', $blog_id, PDO::PARAM_INT);
    $stmt->execute();
    $blog = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$blog) {
        echo "<script>alert('Blog not found.'); window.location.href='staff_manage_blog.php';</script>";
        exit();
    }
} else {
    echo "<script>alert('Blog ID not provided.'); window.location.href='staff_manage_blog.php';</script>";
    exit();
}

// Handle comment deletion
if (isset($_GET['delete_comment_id']) && isset($_GET['blog_id'])) {
    $comment_number = $_GET['delete_comment_id'];
    $blog_id = $_GET['blog_id'];

    // Delete the comment from the database based on both blog_id and comment_number
    $stmt = $pdo->prepare("DELETE FROM blog_comment WHERE blog_id = :blog_id AND comment_number = :comment_number");
    $stmt->bindParam(':blog_id', $blog_id, PDO::PARAM_INT);
    $stmt->bindParam(':comment_number', $comment_number, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo "<script>alert('Comment deleted successfully!'); window.location.href='staff_view_blog_comments.php?blog_id={$blog_id}';</script>";
    } else {
        echo "<script>alert('Error deleting comment.'); window.location.href='staff_view_blog_comments.php?blog_id={$blog_id}';</script>";
    }
}

// Fetch all comments for the blog along with customer full name
$stmt = $pdo->prepare("SELECT bc.comment_number, bc.customer_id, bc.comment_content, bc.created_at, c.first_name, c.last_name 
                        FROM blog_comment bc 
                        JOIN customer c ON bc.customer_id = c.customer_id 
                        WHERE bc.blog_id = :blog_id");
$stmt->bindParam(':blog_id', $blog_id, PDO::PARAM_INT);
$stmt->execute();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Blog Comments</title>

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom Styles -->
    <link rel="stylesheet" href="staff_style.css">
</head>
<body>

<div class="wrapper">
    <!-- Sidebar -->
    <?php include('staff_sidebar.php'); ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container mt-5">
            <h1>Comments for Blog: <?php echo htmlspecialchars($blog['title']); ?></h1>

            <!-- Comments Table -->
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Comment #</th>
                        <th>Customer Name</th>
                        <th>Comment Content</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Loop through the comments and display them
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $fullName = htmlspecialchars($row['first_name']) . ' ' . htmlspecialchars($row['last_name']);
                        echo "<tr>";
                        echo "<td>{$row['comment_number']}</td>";
                        echo "<td>{$fullName}</td>"; // Display the full name
                        echo "<td>" . htmlspecialchars($row['comment_content']) . "</td>";
                        echo "<td>{$row['created_at']}</td>";
                        echo "<td>
                                <a href='staff_view_blog_comments.php?blog_id={$blog_id}&delete_comment_id={$row['comment_number']}' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure you want to delete this comment?\")'><i class='bi bi-trash'></i> Delete</a>
                              </td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>

            <a href="staff_manage_blog.php" class="btn btn-secondary">Back to Blog Management</a>
        </div>
    </div>
</div>

</body>
</html>
