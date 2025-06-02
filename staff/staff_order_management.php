<?php
session_start();
// Check if user is logged in as staff
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../login.php"); // Redirect to login if not logged in as staff
    exit();
}

// Database Connection
include('connection.php');

// Initialize filter status
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Base query for fetching orders
$query = "SELECT o.order_id, o.customer_id, o.shipping_address, o.order_status, o.order_date, o.total_amount, o.points_used,
                c.first_name, c.last_name, c.email, c.phone, od.order_detail_number, od.product_id, od.quantity, od.unit_price, od.subtotal, p.name AS product_name
          FROM orders o
          JOIN customer c ON o.customer_id = c.customer_id
          JOIN order_detail od ON o.order_id = od.order_id
          JOIN products p ON od.product_id = p.product_id";

// Modify query if a status filter is applied
if (!empty($status_filter)) {
    $query .= " WHERE o.order_status = :status";
}

// Prepare and execute the query
$stmt = $pdo->prepare($query);

// Bind parameter if status filter is applied
if (!empty($status_filter)) {
    $stmt->bindParam(':status', $status_filter);
}

$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle order status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['order_id']) && isset($_POST['order_status'])) {
    $order_id = $_POST['order_id'];
    $order_status = $_POST['order_status'];

    // Update the order status in the database
    $update_stmt = $pdo->prepare("UPDATE orders SET order_status = :order_status WHERE order_id = :order_id");
    $update_stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
    $update_stmt->bindParam(':order_status', $order_status, PDO::PARAM_STR);
    
    if ($update_stmt->execute()) {
        echo "<script>alert('Order status updated successfully!'); window.location.href='staff_order_management.php';</script>";
    } else {
        echo "<script>alert('Error updating order status.');</script>";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff - Order Management</title>

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
            <h1>Order Management</h1>

            <!-- Filter Form -->
            <form method="GET" class="mb-4">
                <div class="row">
                    <div class="col-md-4">
                        <select class="form-select" name="status">
                            <option value="">All Orders</option>
                            <option value="Pending" <?php echo ($status_filter == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                            <option value="Shipped" <?php echo ($status_filter == 'Shipped') ? 'selected' : ''; ?>>Shipped</option>
                            <option value="Delivered" <?php echo ($status_filter == 'Delivered') ? 'selected' : ''; ?>>Delivered</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </div>
            </form>

            <!-- Orders Table -->
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Order #</th>
                        <th>Customer Name</th>
                        <th>Order Date</th>
                        <th>Total Amount</th>
                        <th>Order Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Loop through the orders and display them
                    foreach ($orders as $order) {
                        $customer_name = htmlspecialchars($order['first_name']) . ' ' . htmlspecialchars($order['last_name']);
                        $status_class = '';

                        // Assign color classes based on the status
                        if ($order['order_status'] == 'Pending') {
                            $status_class = 'bg-danger text-white'; // Red
                        } elseif ($order['order_status'] == 'Shipped') {
                            $status_class = 'bg-primary text-white'; // Blue
                        } elseif ($order['order_status'] == 'Delivered') {
                            $status_class = 'bg-success text-white'; // Green
                        }

                        // Disable update button if order status is not "Pending"
                        $disabled_button = ($order['order_status'] != 'Pending') ? 'disabled' : '';

                        echo "<tr>";
                        echo "<td>{$order['order_id']}</td>";
                        echo "<td>{$customer_name}</td>";
                        echo "<td>{$order['order_date']}</td>";
                        echo "<td>Rs. {$order['total_amount']}</td>";
                        echo "<td class='{$status_class}'>{$order['order_status']}</td>";
                        echo "<td>
                                <a href='view_order_details.php?order_id={$order['order_id']}' class='btn btn-info btn-sm'><i class='bi bi-eye'></i> View Details</a>
                                <form method='POST' style='display:inline;'>
                                    <input type='hidden' name='order_id' value='{$order['order_id']}'>
                                    <select name='order_status' class='form-select form-select-sm' required {$disabled_button}>
                                        <option value='Pending' " . ($order['order_status'] == 'Pending' ? 'selected' : '') . ">Pending</option>
                                        <option value='Shipped' " . ($order['order_status'] == 'Shipped' ? 'selected' : '') . ">Shipped</option>
                                        <option value='Delivered' " . ($order['order_status'] == 'Delivered' ? 'selected' : '') . ">Delivered</option>
                                    </select>
                                    <button type='submit' class='btn btn-warning btn-sm' {$disabled_button}><i class='bi bi-pencil'></i> Update</button>
                                </form>
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

