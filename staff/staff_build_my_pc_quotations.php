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

// Base query for fetching quotations
$query = "SELECT bq.quotation_id, bq.customer_id, bq.total_amount, bq.created_at, bq.quotation_status, bq.staff_id, c.first_name, c.last_name
          FROM build_my_pc_quotations bq
          JOIN customer c ON bq.customer_id = c.customer_id";

// Modify query if a status filter is applied
if (!empty($status_filter)) {
    $query .= " WHERE bq.quotation_status = :status";
}

// Fetch all quotations and customer details
$stmt = $pdo->prepare($query);
if (!empty($status_filter)) {
    $stmt->bindParam(':status', $status_filter);
}
$stmt->execute();
$quotations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch quotation details (products included in the quotation)
$quotation_details = [];
foreach ($quotations as $quotation) {
    $stmt_details = $pdo->prepare("SELECT bqd.number, bqd.product_id, p.name AS product_name, bqd.price
        FROM build_my_pc_quotation_detail bqd
        JOIN products p ON bqd.product_id = p.product_id
        WHERE bqd.quotation_id = :quotation_id");
    $stmt_details->bindParam(':quotation_id', $quotation['quotation_id'], PDO::PARAM_INT);
    $stmt_details->execute();
    $quotation_details[$quotation['quotation_id']] = $stmt_details->fetchAll(PDO::FETCH_ASSOC);
}

// Handle quotation status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['quotation_id']) && isset($_POST['quotation_status'])) {
    $quotation_id = $_POST['quotation_id'];
    $quotation_status = $_POST['quotation_status'];
    $staff_id = $_SESSION['staff_id']; // Get the staff ID from the session

    // Update the quotation status in the database
    $update_stmt = $pdo->prepare("UPDATE build_my_pc_quotations SET quotation_status = :quotation_status, staff_id = :staff_id WHERE quotation_id = :quotation_id");
    $update_stmt->bindParam(':quotation_id', $quotation_id, PDO::PARAM_INT);
    $update_stmt->bindParam(':quotation_status', $quotation_status, PDO::PARAM_STR);
    $update_stmt->bindParam(':staff_id', $staff_id, PDO::PARAM_INT);

    if ($update_stmt->execute()) {
        echo "<script>alert('Quotation status updated successfully!'); window.location.href='staff_build_my_pc_quotations.php';</script>";
    } else {
        echo "<script>alert('Error updating quotation status.');</script>";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff - Build My PC Quotations</title>

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
            <h1>Manage Build My PC Quotations</h1>

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

            <!-- Quotations Table -->
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Customer Name</th>
                        <th>Total Amount</th>
                        <th>Created At</th>
                        <th>Quotation Status</th>
                        <th>Staff Assigned</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Loop through quotations and display them
                    foreach ($quotations as $quotation) {
                        $customer_name = htmlspecialchars($quotation['first_name']) . ' ' . htmlspecialchars($quotation['last_name']);
                        $staff_name = ""; // Default staff name
                        if (!empty($quotation['staff_id'])) {
                            $stmt_staff = $pdo->prepare("SELECT first_name, last_name FROM staff WHERE staff_id = :staff_id");
                            $stmt_staff->bindParam(':staff_id', $quotation['staff_id'], PDO::PARAM_INT);
                            $stmt_staff->execute();
                            $staff = $stmt_staff->fetch(PDO::FETCH_ASSOC);
                            $staff_name = htmlspecialchars($staff['first_name']) . ' ' . htmlspecialchars($staff['last_name']);
                        }

                        echo "<tr>";
                        echo "<td>{$quotation['quotation_id']}</td>";
                        echo "<td>{$customer_name}</td>";
                        echo "<td>Rs. " . htmlspecialchars($quotation['total_amount']) . "</td>";
                        echo "<td>" . htmlspecialchars($quotation['created_at']) . "</td>";
                        echo "<td>
                                <form method='POST' style='display:inline;'>
                                    <input type='hidden' name='quotation_id' value='{$quotation['quotation_id']}'>
                                    <select name='quotation_status' class='form-select form-select-sm' required>
                                        <option value='Pending' " . ($quotation['quotation_status'] == 'Pending' ? 'selected' : '') . ">Pending</option>
                                        <option value='Accepted' " . ($quotation['quotation_status'] == 'Accepted' ? 'selected' : '') . ">Accepted</option>
                                        <option value='Rejected' " . ($quotation['quotation_status'] == 'Rejected' ? 'selected' : '') . ">Rejected</option>
                                    </select>
                                    <button type='submit' class='btn btn-warning btn-sm'><i class='bi bi-pencil'></i> Update</button>
                                </form>
                              </td>";
                        echo "<td>{$staff_name}</td>";
                        echo "<td>
                                <a href='view_quotation_details.php?quotation_id={$quotation['quotation_id']}' class='btn btn-info btn-sm'><i class='bi bi-eye'></i> View Details</a>
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
