<?php
session_start();

include('connection.php');
require_once 'vendor/autoload.php';

if (!isset($_SESSION['selected_components']) || !isset($_SESSION['total_amount'])) {
    echo "<script>alert('Session expired or missing data. Please build your PC again.'); window.location.href = 'build_my_pc.php';</script>";
    exit;
}

$components = $_SESSION['selected_components'];
$total_amount = $_SESSION['total_amount'];

// Fetch product details
$selectedProducts = [];
foreach ($components as $category => $product_id) {
    if (!empty($product_id)) {
        $stmt = $pdo->prepare("SELECT name, new_price FROM products WHERE product_id = :product_id");
        $stmt->bindParam(':product_id', $product_id);
        $stmt->execute();
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($product) {
            $selectedProducts[$category] = $product;
        }
    }
}

// Create PDF
$pdf = new TCPDF();
$pdf->AddPage();
$pdf->SetFont('Helvetica', '', 12);
$pdf->Cell(0, 10, 'PC Build Quotation', 0, 1, 'C');
$pdf->Ln(5);

$pdf->SetFont('Helvetica', 'B', 10);
$pdf->Cell(50, 10, 'Component', 1);
$pdf->Cell(80, 10, 'Product Name', 1);
$pdf->Cell(40, 10, 'Price (Rs.)', 1, 1);
$pdf->SetFont('Helvetica', '', 10);

foreach ($selectedProducts as $category => $product) {
    $pdf->Cell(50, 10, ucfirst($category), 1);
    $pdf->Cell(80, 10, $product['name'], 1);
    $pdf->Cell(40, 10, 'Rs. ' . number_format($product['new_price'], 2), 1, 1);
}

$pdf->SetFont('Helvetica', 'B', 12);
$pdf->Cell(130, 10, 'Total Amount', 1);
$pdf->Cell(40, 10, 'Rs. ' . number_format($total_amount, 2), 1, 1);

$pdf->Output('quotation.pdf', 'D'); // Force download
exit;
?>