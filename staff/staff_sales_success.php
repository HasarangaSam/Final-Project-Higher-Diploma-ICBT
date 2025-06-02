<?php
session_start();

// Check if user is logged in as staff
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../login.php"); // Redirect to login if not logged in as staff
    exit();
}

// Database Connection
include('connection.php');

// Get sale ID from the URL
if (isset($_GET['sale_id'])) {
    $sale_id = $_GET['sale_id'];

    // Fetch sale details
    $stmt_sale = $pdo->prepare("SELECT * FROM sales WHERE id = :sale_id");
    $stmt_sale->bindParam(':sale_id', $sale_id, PDO::PARAM_INT);
    $stmt_sale->execute();
    $sale = $stmt_sale->fetch(PDO::FETCH_ASSOC);

    // Fetch sale details
    $stmt_details = $pdo->prepare("SELECT sd.number, p.name AS product_name, sd.quantity, sd.unit_price, sd.subtotal 
                                   FROM sales_detail sd
                                   JOIN products p ON sd.product_id = p.product_id
                                   WHERE sd.sales_id = :sale_id
                                   ORDER BY sd.number");
    $stmt_details->bindParam(':sale_id', $sale_id, PDO::PARAM_INT);
    $stmt_details->execute();
    $sale_details = $stmt_details->fetchAll(PDO::FETCH_ASSOC);
} else {
    // If sale_id is not provided, redirect to the staff sales page
    header("Location: staff_make_sales.php");
    exit();
}

// TCPDF Invoice Generation
require_once '../vendor/autoload.php';

if (isset($_GET['download_invoice']) && $_GET['download_invoice'] == 'true') {
    // Create new PDF document
    $pdf = new TCPDF();
    $pdf->SetCreator('Dilan Computers');
    $pdf->SetAuthor('Dilan Computers');
    $pdf->SetTitle('Invoice - Sale ID ' . $sale_id);
    $pdf->SetSubject('Invoice');
    $pdf->SetMargins(10, 10, 10);
    $pdf->AddPage();

    // Add Invoice Title
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, 'Dilan Computers - Invoice', 0, 1, 'C');
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Ln(5);

    // Customer Details
    $pdf->Cell(0, 10, 'Sale ID: ' . $sale['id'], 0, 1);
    $pdf->Cell(0, 10, 'Customer: ' . $sale['customer_first_name'] . ' ' . $sale['customer_last_name'], 0, 1);
    $pdf->Cell(0, 10, 'Phone: ' . $sale['customer_phone'], 0, 1);
    $pdf->Cell(0, 10, 'Staff ID: ' . $sale['staff_id'], 0, 1);
    $pdf->Ln(5);

    // Table Header
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(10, 10, '#', 1);
    $pdf->Cell(80, 10, 'Product Name', 1);
    $pdf->Cell(20, 10, 'Qty', 1);
    $pdf->Cell(30, 10, 'Unit Price (Rs.)', 1);
    $pdf->Cell(30, 10, 'Subtotal (Rs.)', 1);
    $pdf->Ln();

    // Product Details
    $pdf->SetFont('helvetica', '', 12);
    foreach ($sale_details as $detail) {
        $pdf->Cell(10, 10, $detail['number'], 1);
        $pdf->Cell(80, 10, $detail['product_name'], 1);
        $pdf->Cell(20, 10, $detail['quantity'], 1);
        $pdf->Cell(30, 10, number_format($detail['unit_price'], 2), 1);
        $pdf->Cell(30, 10, number_format($detail['subtotal'], 2), 1);
        $pdf->Ln();
    }

    // Total Amount
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(140, 10, 'Total:', 1);
    $pdf->Cell(30, 10, 'Rs. ' . number_format($sale['total_amount'], 2), 1);
    $pdf->Ln(10);

    // Download PDF without saving
    $pdf->Output('invoice_' . $sale_id . '.pdf', 'D'); // Direct download
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sale Success - Dilan Computers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="staff_style.css"> <!-- Add custom CSS for styling -->
</head>
<body>

<!-- Sidebar -->
<div class="d-flex">
    <?php include('staff_sidebar.php'); ?>
</div>

<!-- Main Content -->
<div class="main-content flex-grow-1 p-4">
    <h1>Sale Successful</h1>
    
    <div class="alert alert-success" role="alert">
        Sale ID: <strong><?php echo $sale['id']; ?></strong> has been successfully processed!
    </div>

    <h3>Sale Details</h3>
    <table class="table table-bordered">
        <tr>
            <th>Customer Name</th>
            <td><?php echo $sale['customer_first_name'] . ' ' . $sale['customer_last_name']; ?></td>
        </tr>
        <tr>
            <th>Customer Phone</th>
            <td><?php echo $sale['customer_phone']; ?></td>
        </tr>
        <tr>
            <th>Staff ID</th>
            <td><?php echo $sale['staff_id']; ?></td>
        </tr>
        <tr>
            <th>Total Amount</th>
            <td>Rs. <?php echo number_format($sale['total_amount'], 2); ?></td>
        </tr>
    </table>

    <h3>Products Sold</h3>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Number</th>
                <th>Product Name</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($sale_details as $detail): ?>
                <tr>
                    <td><?php echo $detail['number']; ?></td>
                    <td><?php echo $detail['product_name']; ?></td>
                    <td><?php echo $detail['quantity']; ?></td>
                    <td>Rs. <?php echo number_format($detail['unit_price'], 2); ?></td>
                    <td>Rs. <?php echo number_format($detail['subtotal'], 2); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Download Invoice Button -->
    <a href="staff_sales_success.php?sale_id=<?php echo $sale_id; ?>&download_invoice=true" class="btn btn-success">Download Invoice</a>
    <a href="staff_make_sales.php" class="btn btn-primary">Make Another Sale</a>
</div>

</body>
</html>


