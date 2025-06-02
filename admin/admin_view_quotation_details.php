<?php
session_start();

// Check if user is logged in as admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php"); // Redirect to login if not logged in as admin
    exit();
}

// Database Connection
include('connection.php');

// Fetch quotation details by ID
if (isset($_GET['quotation_id'])) {
    $quotation_id = $_GET['quotation_id'];

    // Fetch quotation info
    $stmt = $pdo->prepare("SELECT * FROM build_my_pc_quotations WHERE quotation_id = :quotation_id");
    $stmt->bindParam(':quotation_id', $quotation_id, PDO::PARAM_INT);
    $stmt->execute();
    $quotation = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch quotation products
    $stmt_details = $pdo->prepare("SELECT bqd.number, bqd.product_id, p.name AS product_name, bqd.price
                                   FROM build_my_pc_quotation_detail bqd
                                   JOIN products p ON bqd.product_id = p.product_id
                                   WHERE bqd.quotation_id = :quotation_id");
    $stmt_details->bindParam(':quotation_id', $quotation_id, PDO::PARAM_INT);
    $stmt_details->execute();
    $quotation_details = $stmt_details->fetchAll(PDO::FETCH_ASSOC);
} else {
    echo "<script>alert('Quotation ID not provided.'); window.location.href='admin_quotation_summary.php';</script>";
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Quotation Details</title>

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
            <h1>Quotation Details for Order #<?php echo htmlspecialchars($quotation['quotation_id']); ?></h1>

            <!-- Quotation Info -->
            <div class="card mb-4">
                <div class="card-header">Quotation Information</div>
                <div class="card-body">
                    <p><strong>Total Amount:</strong> Rs. <?php echo htmlspecialchars($quotation['total_amount']); ?></p>
                    <p><strong>Created At:</strong> <?php echo htmlspecialchars($quotation['created_at']); ?></p>
                    <p><strong>Quotation Status:</strong> <?php echo htmlspecialchars($quotation['quotation_status']); ?></p>
                </div>
            </div>

            <!-- Quotation Items Table -->
            <h3>Products in the Quotation</h3>
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Product Name</th>
                        <th>Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Loop through the quotation details and display them
                    foreach ($quotation_details as $detail) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($detail['number']) . "</td>";
                        echo "<td>" . htmlspecialchars($detail['product_name']) . "</td>";
                        echo "<td>Rs. " . htmlspecialchars($detail['price']) . "</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>

            <a href="admin_quotation_summary.php" class="btn btn-secondary">Back to Quotations</a>
        </div>
    </div>
</div>

</body>
</html>
