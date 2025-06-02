<?php
session_start();
// Check if user is logged in as admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
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
    <title>Admin Dashboard - Dilan Computers</title>

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="wrapper">
    <!-- Sidebar -->
    <?php include('sidebar.php'); ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <h1 class="mt-4">Admin Dashboard - Dilan Computers</h1>

            <!-- Customer Management Table -->
            <h3 class="mt-4">Customer Management</h3>
            <a href="add_customer.php" class="btn btn-primary mb-3"><i class="bi bi-plus-lg"></i> Add New Customer</a>
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $pdo->query("SELECT * FROM customer");
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<tr>";
                        echo "<td>{$row['customer_id']}</td>";
                        echo "<td>{$row['first_name']}</td>";
                        echo "<td>{$row['last_name']}</td>";
                        echo "<td>{$row['email']}</td>";
                        echo "<td>{$row['phone']}</td>";
                        echo "<td>
                            <a href='edit_customer.php?id={$row['customer_id']}' class='btn btn-warning btn-sm'><i class='bi bi-pencil'></i> Update</a>
                            <a href='delete_customer.php?id={$row['customer_id']}' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure you want to delete this customer?\")'><i class='bi bi-trash'></i> Delete</a>
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
