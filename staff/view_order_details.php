<?php
session_start();
// Check if user is logged in as staff
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../login.php"); // Redirect to login if not logged in as staff
    exit();
}

// Database Connection
include('connection.php');

// Check if order_id is provided in the URL
if (isset($_GET['order_id'])) {
    $order_id = $_GET['order_id'];

    // Fetch order details from the database
    $stmt = $pdo->prepare("
        SELECT o.*, c.first_name, c.last_name, c.email, c.phone
        FROM orders o
        JOIN customer c ON o.customer_id = c.customer_id
        WHERE o.order_id = :order_id
    ");
    $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
    $stmt->execute();
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        echo "<script>alert('Order not found.'); window.location.href='staff_order_management.php';</script>";
        exit();
    }

    // Fetch order details (products in the order)
    $stmt = $pdo->prepare("
        SELECT od.*, p.name AS product_name
        FROM order_detail od
        JOIN products p ON od.product_id = p.product_id
        WHERE od.order_id = :order_id
    ");
    $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
    $stmt->execute();
    $order_details = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    echo "<script>alert('Order ID not provided.'); window.location.href='staff_order_management.php';</script>";
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Order Details</title>

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
            <h1>Order Details for Order #<?php echo htmlspecialchars($order['order_id']); ?></h1>

            <!-- Customer Information -->
            <div class="card mb-4">
                <div class="card-header">Customer Information</div>
                <div class="card-body">
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($order['first_name']) . ' ' . htmlspecialchars($order['last_name']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
                    <p><strong>Shipping Address:</strong> <?php echo htmlspecialchars($order['shipping_address']); ?></p>
                </div>
            </div>

            <!-- Order Details Table -->
            <h4>Products in this Order</h4>
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
                    // Loop through order details and display products in the order
                    foreach ($order_details as $detail) {
                        echo "<tr>";
                        echo "<td>{$detail['order_detail_number']}</td>";
                        echo "<td>" . htmlspecialchars($detail['product_name']) . "</td>";
                        echo "<td>{$detail['quantity']}</td>";
                        echo "<td>{$detail['unit_price']}</td>";
                        echo "<td>{$detail['subtotal']}</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>

            <!-- Order Summary -->
            <div class="card mb-4">
                <div class="card-header">Order Summary</div>
                <div class="card-body">
                    <p><strong>Total Amount:</strong> Rs. <?php echo htmlspecialchars($order['total_amount']); ?></p>
                    <p><strong>Points Used:</strong> <?php echo htmlspecialchars($order['points_used']); ?> points</p>
                    <p><strong>Order Status:</strong> <?php echo htmlspecialchars($order['order_status']); ?></p>
                </div>
            </div>

            <!-- Back to Orders -->
            <a href="staff_order_management.php" class="btn btn-secondary">Back to Order Management</a>
        </div>
    </div>
</div>

</body>
</html>
