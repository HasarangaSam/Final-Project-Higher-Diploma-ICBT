<?php 
session_start();
// Check if user is logged in as admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php"); // Redirect to login if not logged in as admin
    exit();
}

// Database Connection
include('connection.php');

// Initialize filter status (in case you want to filter by specific staff or date)
$staff_filter = isset($_GET['staff_id']) ? $_GET['staff_id'] : '';

// Base query for fetching sales
$query = "SELECT s.*, CONCAT(st.first_name, ' ', st.last_name) AS staff_name 
          FROM sales s 
          JOIN staff st ON s.staff_id = st.staff_id";

// Modify query if a staff filter is applied
if (!empty($staff_filter)) {
    $query .= " WHERE s.staff_id = :staff_id";
}

// Add ORDER BY to get sales in descending order of sale_date
$query .= " ORDER BY s.sale_date DESC";

// Prepare and execute the query
$stmt = $pdo->prepare($query);

// Bind parameter if staff filter is applied
if (!empty($staff_filter)) {
    $stmt->bindParam(':staff_id', $staff_filter);
}

$stmt->execute();

// Prepare data for chart (total amount per sale date)
$sale_dates = [];
$sale_amounts = [];

while ($sale = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $sale_dates[] = $sale['sale_date'];
    $sale_amounts[] = $sale['total_amount'];
}

$dates = array_unique($sale_dates);  // Get unique dates for chart X axis
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Sales Summary</title>

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
            <h1>Sales Summary</h1>

            <!-- Filter Form -->
            <form method="GET" class="mb-4">
                <div class="row">
                    <div class="col-md-4">
                        <select class="form-select" name="staff_id">
                            <option value="">All Staff</option>
                            <?php
                                // Fetch staff list for filter
                                $stmt_staff = $pdo->prepare("SELECT staff_id, CONCAT(first_name, ' ', last_name) AS staff_name FROM staff");
                                $stmt_staff->execute();
                                while ($staff = $stmt_staff->fetch(PDO::FETCH_ASSOC)) {
                                    $selected = ($staff['staff_id'] == $staff_filter) ? 'selected' : '';
                                    echo "<option value='{$staff['staff_id']}' {$selected}>{$staff['staff_name']}</option>";
                                }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </div>
            </form>

            <!-- Total Amount Chart (Bar Chart) -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <h3>Total Sale Amount by Date</h3>
                    <canvas id="amountChart"></canvas>
                </div>
            </div>

            <!-- Sales Table -->
            <h3 class="mt-4">Sales Details</h3>
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Staff Name</th>
                        <th>Customer Name</th>
                        <th>Customer Phone</th>
                        <th>Sale Date</th>
                        <th>Total Amount</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Loop through the sales and display them
                    $stmt->execute();
                    while ($sale = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $staff_name = htmlspecialchars($sale['staff_name']);
                        $customer_name = htmlspecialchars($sale['customer_first_name']) . ' ' . htmlspecialchars($sale['customer_last_name']);
                        echo "<tr>";
                        echo "<td>{$sale['id']}</td>";
                        echo "<td>{$staff_name}</td>";
                        echo "<td>{$customer_name}</td>";
                        echo "<td>{$sale['customer_phone']}</td>";
                        echo "<td>{$sale['sale_date']}</td>";
                        echo "<td>Rs. {$sale['total_amount']}</td>";
                        echo "<td><a href='view_sale_details.php?sale_id={$sale['id']}' class='btn btn-info btn-sm'><i class='bi bi-eye'></i> View Sale Details</a></td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Total Amount by Date Chart (Bar Chart)
const amountChart = new Chart(document.getElementById('amountChart'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($dates); ?>,
        datasets: [{
            label: 'Total Amount by Date',
            data: <?php echo json_encode($sale_amounts); ?>,
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
