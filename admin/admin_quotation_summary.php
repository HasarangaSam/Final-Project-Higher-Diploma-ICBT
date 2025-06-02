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

// Base query for fetching quotations
$query = "SELECT bq.quotation_id, bq.customer_id, bq.total_amount, bq.created_at, bq.quotation_status, c.first_name, c.last_name
          FROM build_my_pc_quotations bq
          JOIN customer c ON bq.customer_id = c.customer_id";

// Modify query if a status filter is applied
if (!empty($status_filter)) {
    $query .= " WHERE bq.quotation_status = :status";
}

// Prepare and execute the query
$stmt = $pdo->prepare($query);

// Bind parameter if status filter is applied
if (!empty($status_filter)) {
    $stmt->bindParam(':status', $status_filter);
}

$stmt->execute();
$quotations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Prepare data for Pie chart (quotation statuses)
$status_count = ['Pending' => 0, 'Accepted' => 0, 'Rejected' => 0];
foreach ($quotations as $quotation) {
    $status_count[$quotation['quotation_status']]++;
}

// Prepare data for total amount by date chart
$order_dates = [];
$order_amounts = [];
foreach ($quotations as $quotation) {
    $order_dates[] = $quotation['created_at'];
    $order_amounts[] = $quotation['total_amount'];
}
$dates = array_unique($order_dates);  // Get unique dates for chart X axis
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Quotation Summary</title>

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
            <h1>Quotation Summary</h1>

            <!-- Filter Form -->
            <form method="GET" class="mb-4">
                <div class="row">
                    <div class="col-md-4">
                        <select class="form-select" name="status">
                            <option value="">All Quotations</option>
                            <option value="Pending" <?php echo ($status_filter == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                            <option value="Accepted" <?php echo ($status_filter == 'Accepted') ? 'selected' : ''; ?>>Accepted</option>
                            <option value="Rejected" <?php echo ($status_filter == 'Rejected') ? 'selected' : ''; ?>>Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </div>
            </form>

            <!-- Quotation Status Pie Chart -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <h3>Quotation Status Distribution</h3>
                    <canvas id="statusChart"></canvas>
                </div>

                <!-- Total Amount by Date Chart -->
                <div class="col-md-6">
                    <h3>Total Quotation Amount by Date</h3>
                    <canvas id="amountChart"></canvas>
                </div>
            </div>

            <!-- Quotations Table -->
            <h3 class="mt-4">Quotation Details</h3>
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Customer Name</th>
                        <th>Total Amount</th>
                        <th>Created At</th>
                        <th>Quotation Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Loop through quotations and display them
                    foreach ($quotations as $quotation) {
                        $customer_name = htmlspecialchars($quotation['first_name']) . ' ' . htmlspecialchars($quotation['last_name']);
                        echo "<tr>";
                        echo "<td>{$quotation['quotation_id']}</td>";
                        echo "<td>{$customer_name}</td>";
                        echo "<td>Rs. " . htmlspecialchars($quotation['total_amount']) . "</td>";
                        echo "<td>" . htmlspecialchars($quotation['created_at']) . "</td>";
                        echo "<td>" . htmlspecialchars($quotation['quotation_status']) . "</td>";
                        echo "<td>
                                <a href='admin_view_quotation_details.php?quotation_id={$quotation['quotation_id']}' class='btn btn-info btn-sm'>
                                    <i class='bi bi-eye'></i> View Details
                                </a>
                              </td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Quotation Status Pie Chart
const statusChart = new Chart(document.getElementById('statusChart'), {
    type: 'pie',
    data: {
        labels: ['Pending', 'Accepted', 'Rejected'],
        datasets: [{
            label: 'Quotation Status Distribution',
            data: [<?php echo $status_count['Pending']; ?>, <?php echo $status_count['Accepted']; ?>, <?php echo $status_count['Rejected']; ?>],
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

