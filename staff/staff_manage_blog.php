<?php
session_start();
// Check if user is logged in as staff
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../login.php");
    exit();
}

// Database Connection
include('connection.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff - Blog Management</title>

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
        <div class="container">
            <h1 class="mt-4">Blog Management</h1>

            <!-- Blogs Table -->
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Content</th>
                        <th>Created At</th>
                        <th>Image</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Fetch all blogs from the 'blogs' table
                    $stmt = $pdo->query("SELECT * FROM blogs");
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        // Display blog details
                        echo "<tr>";
                        echo "<td>{$row['blog_id']}</td>";
                        echo "<td>{$row['title']}</td>";
                        echo "<td>" . substr($row['content'], 0, 100) . "...</td>";  // Show only the first 100 characters of content
                        echo "<td>{$row['created_at']}</td>";

                        // Display the image
                        $image_path = $row['img_src']; // Path to the image in the 'admin/uploads/' directory
                        echo "<td><img src='{$image_path}' alt='Blog Image' style='width: 100px; height: auto;'></td>";

                        // Actions (View Comments only for staff)
                        echo "<td>
                                <a href='staff_view_blog_comments.php?blog_id={$row['blog_id']}' class='btn btn-info btn-sm'><i class='bi bi-chat-dots'></i> View Comments</a>
                              </td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>




