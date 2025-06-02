<?php
session_start();

// Check if user is logged in as admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php"); // Redirect to login if not logged in as admin
    exit();
}

// Database Connection
include('connection.php');

// Get sale ID from the URL
if (isset($_GET['sale_id'])) {
    $sale_id = $_GET['sale_id'];

    // Fetch sale details
    $stmt_sale = $pdo->prepare("SELECT s.*, CONCAT(st.first_name, ' ', st.last_name) AS staff_name 
                                FROM sales s 
                                JOIN staff st ON s.staff_id = st.staff_id 
                                WHERE s.id = :sale_id");
    $stmt_sale->bindParam(':sale_id', $sale_id, PDO::PARAM_INT);
    $stmt_sale->execute();
    $sale = $stmt_sale->fetch(PDO::FETCH_ASSOC);

    // Fetch sale detail products
    $stmt_details = $pdo->prepare("SELECT sd.number, p.name AS product_name, sd.quantity, sd.unit_price, sd.subtotal 
                                   FROM sales_detail sd
                                   JOIN products p ON sd.product_id = p.product_id
                                   WHERE sd.sales_id = :sale_id
                                   ORDER BY sd.number");
    $stmt_details->bindParam(':sale_id', $sale_id, PDO::PARAM_INT);
    $stmt_details->execute();
    $sale_details = $stmt_details->fetchAll(PDO::FETCH_ASSOC);
} else {
    // If sale_id is not provided, redirect to the sales summary page
    header("Location: admin_sales_summary.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sale Details - Dilan Computers</title>

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
            <h1>Sale Details</h1>
            
            <!-- Sale Information -->
            <h3>Sale Information</h3>
            <table class="table table-bordered">
                <tr>
                    <th>Sale ID</th>
                    <td><?php echo $sale['id']; ?></td>
                </tr>
                <tr>
                    <th>Customer Name</th>
                    <td><?php echo $sale['customer_first_name'] . ' ' . $sale['customer_last_name']; ?></td>
                </tr>
                <tr>
                    <th>Customer Phone</th>
                    <td><?php echo $sale['customer_phone']; ?></td>
                </tr>
                <tr>
                    <th>Staff Name</th>
                    <td><?php echo $sale['staff_name']; ?></td>
                </tr>
                <tr>
                    <th>Sale Date</th>
                    <td><?php echo $sale['sale_date']; ?></td>
                </tr>
                <tr>
                    <th>Total Amount</th>
                    <td>Rs. <?php echo number_format($sale['total_amount'], 2); ?></td>
                </tr>
            </table>

            <!-- Products in Sale -->
            <h3>Products in Sale</h3>
            <table class="table table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Number</th>
                        <th>Product Name</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Loop through the sale details and display the products
                    foreach ($sale_details as $detail) {
                        echo "<tr>";
                        echo "<td>{$detail['number']}</td>";
                        echo "<td>{$detail['product_name']}</td>";
                        echo "<td>{$detail['quantity']}</td>";
                        echo "<td>Rs. " . number_format($detail['unit_price'], 2) . "</td>";
                        echo "<td>Rs. " . number_format($detail['subtotal'], 2) . "</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>

            <!-- Back Button -->
            <a href="admin_sales_summary.php" class="btn btn-primary">Back to Sales Summary</a>
        </div>
    </div>
</div>

</body>
</html>
