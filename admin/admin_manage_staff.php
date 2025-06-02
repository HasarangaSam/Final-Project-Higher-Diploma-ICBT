<?php
session_start();
// Check if user is logged in as admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Database Connection
include('../connection.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Staff Management</title>

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
        <div class="container">
            <h1 class="mt-4">Staff Management</h1>

            <!-- Add New Staff Button -->
            <a href="add_staff.php" class="btn btn-primary mb-3"><i class="bi bi-person-plus"></i> Add New Staff</a>

            <!-- Staff Table -->
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Age</th>
                        <th>Gender</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $pdo->query("SELECT * FROM staff");
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<tr>";
                        echo "<td>{$row['staff_id']}</td>";
                        echo "<td>{$row['first_name']}</td>";
                        echo "<td>{$row['last_name']}</td>";
                        echo "<td>{$row['age']}</td>";
                        echo "<td>{$row['gender']}</td>";
                        echo "<td>{$row['email']}</td>";
                        echo "<td>{$row['phone']}</td>";
                        echo "<td>{$row['role']}</td>";
                        echo "<td>
                                <a href='edit_staff.php?id={$row['staff_id']}' class='btn btn-warning btn-sm'><i class='bi bi-pencil'></i> Edit</a>
                                <a href='delete_staff.php?id={$row['staff_id']}' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure you want to delete this staff member?\")'><i class='bi bi-trash'></i> Delete</a>
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
