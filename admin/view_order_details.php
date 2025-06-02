<?php
session_start();
// Check if user is logged in as admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php"); // Redirect to login if not logged in as admin
    exit();
}

// Database Connection
include('../connection.php');

// Check if order_id is provided in the URL
if (isset($_GET['order_id'])) {
    $order_id = $_GET['order_id'];

    // Fetch order details along with customer name
    $stmt = $pdo->prepare("SELECT o.*, c.first_name, c.last_name FROM orders o
                           JOIN customer c ON o.customer_id = c.customer_id
                           WHERE o.order_id = :order_id");
    $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
    $stmt->execute();
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        echo "<script>alert('Order not found.'); window.location.href='admin_order_summary.php';</script>";
        exit();
    }

    // Fetch order items (order_detail)
    $stmt_details = $pdo->prepare("SELECT od.order_detail_number, od.product_id, od.quantity, od.unit_price, od.subtotal, p.name AS product_name
                                   FROM order_detail od
                                   JOIN products p ON od.product_id = p.product_id
                                   WHERE od.order_id = :order_id");
    $stmt_details->bindParam(':order_id', $order_id, PDO::PARAM_INT);
    $stmt_details->execute();
    $order_details = $stmt_details->fetchAll(PDO::FETCH_ASSOC);
} else {
    echo "<script>alert('Order ID not provided.'); window.location.href='admin_order_summary.php';</script>";
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Order Details</title>

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
            <h1>Order Details</h1>

            <!-- Order Info -->
            <div class="card mb-4">
                <div class="card-header">Order Information</div>
                <div class="card-body">
                    <p><strong>Order ID:</strong> <?php echo htmlspecialchars($order['order_id']); ?></p>
                    <p><strong>Customer Name:</strong> <?php echo htmlspecialchars($order['first_name']) . ' ' . htmlspecialchars($order['last_name']); ?></p>
                    <p><strong>Shipping Address:</strong> <?php echo htmlspecialchars($order['shipping_address']); ?></p>
                    <p><strong>Order Status:</strong> <?php echo htmlspecialchars($order['order_status']); ?></p>
                    <p><strong>Order Date:</strong> <?php echo htmlspecialchars($order['order_date']); ?></p>
                    <p><strong>Total Amount:</strong> Rs. <?php echo htmlspecialchars($order['total_amount']); ?></p>
                    <p><strong>Points Used:</strong> <?php echo htmlspecialchars($order['points_used']); ?> points</p>
                </div>
            </div>

            <!-- Order Items Table -->
            <h3>Order Items</h3>
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Product Name</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Loop through the order details and display them
                    foreach ($order_details as $detail) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($detail['order_detail_number']) . "</td>";
                        echo "<td>" . htmlspecialchars($detail['product_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($detail['quantity']) . "</td>";
                        echo "<td>Rs. " . htmlspecialchars($detail['unit_price']) . "</td>";
                        echo "<td>Rs. " . htmlspecialchars($detail['subtotal']) . "</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>

            <a href="admin_order_summary.php" class="btn btn-secondary">Back to Order Summary</a>
        </div>
    </div>
</div>

</body>
</html>

