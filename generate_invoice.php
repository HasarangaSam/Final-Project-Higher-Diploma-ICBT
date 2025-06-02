<?php
session_start();
require_once 'vendor/autoload.php'; // Include TCPDF
require_once 'connection.php'; // Include database connection

// Ensure order data exists
if (!isset($_SESSION['order_id'])) {
    die("Invalid request. No order data found.");
}

// Function to get product details from the database
function getProductDetails($productId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT name, new_price FROM products WHERE product_id = ?");
    $stmt->execute([$productId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Retrieve order details from session
$orderId = $_SESSION['order_id'];
$customerName = $_SESSION['customer_name'];
$shippingAddress = $_SESSION['shipping_address'];
$cartItems = $_SESSION['cart_items'];
$totalAfterPoints = $_SESSION['total_after_points'];
$customerEmail = $_SESSION['customer_email'];
$customerPhone = $_SESSION['customer_phone'];
$loyaltyPointsUsed = $_SESSION['loyalty_points_used']; // Added loyalty points used

// Clear order session to avoid re-downloading
unset($_SESSION['order_id']);
unset($_SESSION['cart_items']);

// Create a new PDF instance
$pdf = new \TCPDF();
$pdf->SetTitle("Invoice #$orderId");
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 12);

// **Company Details**
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, "Dilan Computers - Divulapitiya", 0, 1, 'C');
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(0, 10, "Contact: 0112323243", 0, 1, 'C');
$pdf->Ln(5);

// **Invoice Title**
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, "Invoice - Order #$orderId", 0, 1, 'C');
$pdf->Ln(5);

// **Customer Details**
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(0, 10, "Customer: $customerName", 0, 1);
$pdf->Cell(0, 10, "Shipping Address: $shippingAddress", 0, 1);
$pdf->Cell(0, 10, "Email: $customerEmail", 0, 1);
$pdf->Cell(0, 10, "Phone: $customerPhone", 0, 1);
$pdf->Ln(5);

// **Order Table Header**
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(70, 10, "Product Name", 1, 0, 'C');
$pdf->Cell(30, 10, "Quantity", 1, 0, 'C');
$pdf->Cell(40, 10, "Unit Price (Rs.)", 1, 0, 'C');
$pdf->Cell(40, 10, "Subtotal (Rs.)", 1, 1, 'C');

// **Order Items**
$pdf->SetFont('helvetica', '', 12);
$totalAmount = 0;

foreach ($cartItems as $productId => $quantity) {
    $product = getProductDetails($productId);
    $productName = $product['name'] ?? 'Unknown Product';
    $unitPrice = $product['new_price'] ?? 0;
    $subtotal = $unitPrice * $quantity;
    $totalAmount += $subtotal;

    // Add row to table
    $pdf->Cell(70, 10, $productName, 1, 0, 'C');
    $pdf->Cell(30, 10, $quantity, 1, 0, 'C');
    $pdf->Cell(40, 10, number_format($unitPrice, 2), 1, 0, 'C');
    $pdf->Cell(40, 10, number_format($subtotal, 2), 1, 1, 'C');
}

// **Summary Section**
$pdf->Ln(5);
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(140, 10, "Total Amount (Rs.):", 1, 0, 'R');
$pdf->Cell(40, 10, number_format($totalAmount, 2), 1, 1, 'C');

$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(140, 10, "Loyalty Points Used:", 1, 0, 'R');
$pdf->Cell(40, 10, number_format($loyaltyPointsUsed, 2), 1, 1, 'C');

$pdf->Cell(140, 10, "Final Total (Rs.):", 1, 0, 'R');
$pdf->Cell(40, 10, number_format($totalAfterPoints, 2), 1, 1, 'C');


// Output PDF as file download
$pdf->Output('invoice_' . $orderId . '.pdf', 'D'); // 'D' forces download

exit;
?>
