<?php
session_start();
require_once '../vendor/autoload.php'; // Include TCPDF

// Check if user is logged in as staff
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../login.php"); // Redirect to login if not logged in as staff
    exit();
}

// Database Connection
include('connection.php');

// Fetch categories for products
$categories = ['CPU', 'GPU', 'Motherboard', 'RAM', 'Storage', 'PSU', 'Laptop', 'Mouse', 'Keyboard'];

// Fetch products for each category
$products = [];
foreach ($categories as $category) {
    $stmt = $pdo->prepare("SELECT product_id, name, new_price FROM products WHERE category = :category");
    $stmt->bindParam(':category', $category);
    $stmt->execute();
    $products[$category] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Initialize total price
$total_price = 0;  // Initialize total price

// Handle form submission to generate quotation
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customer_first_name = $_POST['customer_first_name'];
    $customer_last_name = $_POST['customer_last_name'];
    $customer_phone = $_POST['customer_phone'];

    // Get the selected products and quantities
    $selected_products = [];
    foreach ($_POST['product_id'] as $category => $product_ids) {
        foreach ($product_ids as $index => $product_id) {
            if (!empty($product_id) && !empty($_POST['quantity'][$category][$index])) {
                $quantity = $_POST['quantity'][$category][$index];
                $stmt_product = $pdo->prepare("SELECT new_price, name FROM products WHERE product_id = :product_id");
                $stmt_product->bindParam(':product_id', $product_id, PDO::PARAM_INT);
                $stmt_product->execute();
                $product = $stmt_product->fetch(PDO::FETCH_ASSOC);

                $subtotal = $product['new_price'] * $quantity;
                $total_price += $subtotal;

                $selected_products[] = [
                    'name' => $product['name'],
                    'quantity' => $quantity,
                    'price' => $product['new_price'],
                    'subtotal' => $subtotal,
                ];
            }
        }
    }

    // Start output buffering to prevent any output before sending the PDF
    ob_start();

    // Handle PDF generation
    if (isset($_POST['generate_pdf'])) {

        $pdf = new TCPDF();
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 12);

        // Set title and header
        $pdf->Cell(0, 10, 'Dilan Computers - Quotation', 0, 1, 'C');
        $pdf->Ln(5);

        // Customer Details
        $pdf->Cell(0, 10, "Customer: $customer_first_name $customer_last_name", 0, 1);
        $pdf->Cell(0, 10, "Phone: $customer_phone", 0, 1);
        $pdf->Ln(10);

        // Product Table
        $pdf->Cell(60, 10, 'Product Name', 1);
        $pdf->Cell(30, 10, 'Quantity', 1);
        $pdf->Cell(30, 10, 'Unit Price', 1);
        $pdf->Cell(30, 10, 'Subtotal', 1);
        $pdf->Ln();

        foreach ($selected_products as $product) {
            $pdf->Cell(60, 10, $product['name'], 1);
            $pdf->Cell(30, 10, $product['quantity'], 1, 0, 'C');
            $pdf->Cell(30, 10, 'Rs. ' . number_format($product['price'], 2), 1, 0, 'R');
            $pdf->Cell(30, 10, 'Rs. ' . number_format($product['subtotal'], 2), 1, 0, 'R');
            $pdf->Ln();
        }

        // Total Price
        $pdf->Cell(120, 10, 'Total:', 1);
        $pdf->Cell(30, 10, 'Rs. ' . number_format($total_price, 2), 1, 0, 'R');
        $pdf->Ln(10);

        $pdf->Output('Dilan_Computers_Quotation.pdf', 'D'); // Download PDF
        exit();
    }

    // End output buffering
    ob_end_clean();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff - Quotation Generation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="staff_style.css"> <!-- Add custom CSS for styling -->
</head>
<body>

<!-- Sidebar -->
<div class="d-flex">
      <?php include('staff_sidebar.php'); ?>
    </div>

    <!-- Main Content -->
    <div class="main-content flex-grow-1 p-4">
        <h1>Generate Quotations</h1>
        <form method="POST" id="quotationForm">
            <div class="mb-3">
                <label for="customer_first_name" class="form-label">Customer First Name</label>
                <input type="text" class="form-control" id="customer_first_name" name="customer_first_name" required>
            </div>
            <div class="mb-3">
                <label for="customer_last_name" class="form-label">Customer Last Name</label>
                <input type="text" class="form-control" id="customer_last_name" name="customer_last_name" required>
            </div>
            <div class="mb-3">
                <label for="customer_phone" class="form-label">Customer Phone</label>
                <input type="text" class="form-control" id="customer_phone" name="customer_phone" required>
            </div>

            <?php foreach ($categories as $category): ?>
                <div class="category-section mb-4" data-category="<?php echo $category; ?>">
                    <label class="form-label"><?php echo $category; ?></label>
                    <div class="product-group">
                        <div class="product-item">
                            <select class="form-select product-select" name="product_id[<?php echo $category; ?>][]">
                                <option value="">Select <?php echo $category; ?></option>
                                <?php foreach ($products[$category] as $product): ?>
                                    <option value="<?php echo $product['product_id']; ?>" data-price="<?php echo $product['new_price']; ?>">
                                        <?php echo $product['name']; ?> - Rs. <?php echo number_format($product['new_price'], 2); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <input type="number" class="form-control mt-2" name="quantity[<?php echo $category; ?>][]" placeholder="Quantity">
                        </div>
                    </div>
                    <button type="button" class="btn btn-secondary add-product-btn">Add More</button>
                </div>
            <?php endforeach; ?>

            <h3>Total: <span id="totalPrice">Rs. 0.00</span></h3>
            <button type="submit" name="generate_pdf" class="btn btn-primary">Download Quotation as PDF</button>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        // Initialize Select2 for product select fields
        $('.product-select').select2({ placeholder: "Search for a product", allowClear: true });

        // Add More Products dynamically
        $('.add-product-btn').on('click', function() {
            let categorySection = $(this).closest('.category-section');
            let category = categorySection.data('category');
            let productOptions = categorySection.find('select:first').html();
            
            let newProductItem = `
                <div class="product-item">
                    <select class="form-select product-select" name="product_id[${category}][]">${productOptions}</select>
                    <input type="number" class="form-control mt-2" name="quantity[${category}][]" placeholder="Quantity">
                </div>`;
            
            categorySection.find('.product-group').append(newProductItem);
            categorySection.find('.product-select').select2({ placeholder: "Search for a product", allowClear: true });
        });

        // Dynamic total price calculation
        $('form').on('change input', '.product-select, input[name^="quantity"]', function() {
            let total = 0;
            $('select.product-select').each(function() {
                let selectedOption = $(this).find(':selected');
                if (selectedOption.val()) {
                    let price = parseFloat(selectedOption.data('price'));
                    let quantity = parseInt($(this).closest('.product-item').find('input[name^="quantity"]').val()) || 0;
                    total += price * quantity;
                }
            });
            $('#totalPrice').text("Rs. " + total.toFixed(2));
        });
    });
</script>

</body>
</html>







