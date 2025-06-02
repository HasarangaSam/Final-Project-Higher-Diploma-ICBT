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
    <title>Staff - Forum Management</title>

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
            <h1>Forum Management</h1>

            <!-- Forums Table -->
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Posted By</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Fetch all forum posts with customer details 
                    $stmt = $pdo->prepare("SELECT f.*, c.first_name, c.last_name FROM forums f
                                           JOIN customer c ON f.customer_id = c.customer_id");
                    $stmt->execute();
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $fullName = htmlspecialchars($row['first_name']) . ' ' . htmlspecialchars($row['last_name']);
                        echo "<tr>";
                        echo "<td>{$row['post_id']}</td>";
                        echo "<td>{$fullName}</td>";
                        echo "<td>{$row['title']}</td>";
                        echo "<td>{$row['category']}</td>";
                        echo "<td>{$row['created_at']}</td>";

                        // Actions (View Answers Only for staff)
                        echo "<td>
                                <a href='staff_view_forum_answers.php?post_id={$row['post_id']}' class='btn btn-info btn-sm'><i class='bi bi-chat-dots'></i> View Answers</a>
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
