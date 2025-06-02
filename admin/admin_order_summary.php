<?php
session_start();
// Check if user is logged in as admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php"); // Redirect to login if not logged in as admin
    exit();
}

// Database Connection
include('connection.php');

// Initialize filter status
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Base query for fetching orders
$query = "SELECT o.*, c.first_name, c.last_name FROM orders o JOIN customer c ON o.customer_id = c.customer_id";

// Modify query if a status filter is applied
if (!empty($status_filter)) {
    $query .= " WHERE order_status = :status";
}

// Add ORDER BY to get orders in descending order of order_date
$query .= " ORDER BY o.order_date DESC";

// Prepare and execute the query
$stmt = $pdo->prepare($query);

// Bind parameter if status filter is applied
if (!empty($status_filter)) {
    $stmt->bindParam(':status', $status_filter);
}

$stmt->execute();

// Prepare data for chart (order statuses and total amount per order date)
$order_statuses = ['Pending' => 0, 'Shipped' => 0, 'Delivered' => 0];
$order_dates = [];
$order_amounts = [];

while ($order = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $order_statuses[$order['order_status']]++;
    $order_dates[] = $order['order_date'];
    $order_amounts[] = $order['total_amount'];
}

$dates = array_unique($order_dates);  // Get unique dates for chart X axis
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Order Summary</title>

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
            <h1>Order Summary</h1>

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

            <!-- Order Status Chart (Pie Chart) -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <h3>Order Status Distribution</h3>
                    <canvas id="statusChart"></canvas>
                </div>

                <!-- Total Amount Chart (Bar Chart) -->
                <div class="col-md-6">
                    <h3>Total Order Amount by Date</h3>
                    <canvas id="amountChart"></canvas>
                </div>
            </div>

            <!-- Orders Table -->
            <h3 class="mt-4">Order Details</h3>
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Customer Name</th>
                        <th>Shipping Address</th>
                        <th>Order Status</th>
                        <th>Order Date</th>
                        <th>Total Amount</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Loop through the orders and display them
                    $stmt->execute();
                    while ($order = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $customer_name = htmlspecialchars($order['first_name']) . ' ' . htmlspecialchars($order['last_name']);
                        echo "<tr>";
                        echo "<td>{$order['order_id']}</td>";
                        echo "<td>{$customer_name}</td>";
                        echo "<td>{$order['shipping_address']}</td>";
                        echo "<td>{$order['order_status']}</td>";
                        echo "<td>{$order['order_date']}</td>";
                        echo "<td>{$order['total_amount']}</td>";
                        echo "<td><a href='view_order_details.php?order_id={$order['order_id']}' class='btn btn-info btn-sm'><i class='bi bi-eye'></i> View Order Details</a></td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Order Status Chart (Pie Chart)
const statusChart = new Chart(document.getElementById('statusChart'), {
    type: 'pie',
    data: {
        labels: ['Pending', 'Shipped', 'Delivered'],
        datasets: [{
            label: 'Order Status Distribution',
            data: [<?php echo $order_statuses['Pending']; ?>, <?php echo $order_statuses['Shipped']; ?>, <?php echo $order_statuses['Delivered']; ?>],
            backgroundColor: ['#ff9999', '#66b3ff', '#99ff99'],
        }]
    }
});

// Total Amount by Date Chart (Bar Chart)
const amountChart = new Chart(document.getElementById('amountChart'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($dates); ?>,
        datasets: [{
            label: 'Total Amount by Date',
            data: <?php echo json_encode($order_amounts); ?>,
            backgroundColor: '#66b3ff',
        }]
    },
    options: {
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>

</body>
</html>



