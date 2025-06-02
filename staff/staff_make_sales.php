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
$categories = ['CPU', 'GPU', 'Motherboard', 'RAM', 'Storage', 'PSU', 'Laptop', 'Mouse', 'Keyboard', 'Monitor'];

// Fetch products for each category
$products = [];
foreach ($categories as $category) {
    $stmt = $pdo->prepare("SELECT product_id, name, new_price, stock_quantity FROM products WHERE category = :category");
    $stmt->bindParam(':category', $category);
    $stmt->execute();
    $products[$category] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Initialize total price
$total_price = 0;  // Initialize total price

// Handle form submission to make sale
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customer_first_name = $_POST['customer_first_name'];
    $customer_last_name = $_POST['customer_last_name'];
    $customer_phone = $_POST['customer_phone'];

    // Get the selected products and quantities
    $selected_products = [];
    $is_stock_available = true; // Flag to check if stock is available

    foreach ($_POST['product_id'] as $category => $product_ids) {
        foreach ($product_ids as $index => $product_id) {
            if (!empty($product_id) && !empty($_POST['quantity'][$category][$index])) {
                $quantity = $_POST['quantity'][$category][$index];
                $stmt_product = $pdo->prepare("SELECT new_price, name, stock_quantity FROM products WHERE product_id = :product_id");
                $stmt_product->bindParam(':product_id', $product_id, PDO::PARAM_INT);
                $stmt_product->execute();
                $product = $stmt_product->fetch(PDO::FETCH_ASSOC);

                // Check stock availability
                if ($product['stock_quantity'] < $quantity) {
                    $is_stock_available = false;
                    break;
                }

                // Calculate subtotal
                $subtotal = $product['new_price'] * $quantity;
                $total_price += $subtotal;

                $selected_products[] = [
                    'name' => $product['name'],
                    'quantity' => $quantity,
                    'price' => $product['new_price'],
                    'subtotal' => $subtotal,
                    'product_id' => $product_id
                ];
            }
        }
        if (!$is_stock_available) {
            break;
        }
    }

    // If stock is not available, show an error
    if (!$is_stock_available) {
        $error_message = "Sorry, one or more products are out of stock. Please reduce the quantity or choose different products.";
    } else {
        // Proceed with sale and update stock quantity
        // Insert sale record
        $stmt_sale = $pdo->prepare("INSERT INTO sales (customer_first_name, customer_last_name, customer_phone, staff_id, total_amount) 
                                    VALUES (:first_name, :last_name, :phone, :staff_id, :total_price)");
        $stmt_sale->bindParam(':first_name', $customer_first_name);
        $stmt_sale->bindParam(':last_name', $customer_last_name);
        $stmt_sale->bindParam(':phone', $customer_phone);
        $stmt_sale->bindParam(':staff_id', $_SESSION['staff_id']); // Assuming staff_id is stored in session
        $stmt_sale->bindParam(':total_price', $total_price);
        $stmt_sale->execute();
        $sale_id = $pdo->lastInsertId(); // Get the last inserted sale ID

        // Insert records in sales_detail and update stock quantities
        foreach ($selected_products as $index => $product) {
            // Get the next available number for sales_detail
            $stmt_max_number = $pdo->prepare("SELECT MAX(number) AS max_number FROM sales_detail WHERE sales_id = :sales_id");
            $stmt_max_number->bindParam(':sales_id', $sale_id, PDO::PARAM_INT);
            $stmt_max_number->execute();
            $max_number = $stmt_max_number->fetch(PDO::FETCH_ASSOC)['max_number'];
            $next_number = $max_number + 1;

            // Insert sale detail
            $stmt_detail = $pdo->prepare("INSERT INTO sales_detail (sales_id, number, product_id, quantity, unit_price, subtotal) 
                VALUES (:sales_id, :number, :product_id, :quantity, :unit_price, :subtotal)");
            $stmt_detail->bindParam(':sales_id', $sale_id, PDO::PARAM_INT);
            $stmt_detail->bindParam(':number', $next_number, PDO::PARAM_INT);
            $stmt_detail->bindParam(':product_id', $product['product_id'], PDO::PARAM_INT);
            $stmt_detail->bindParam(':quantity', $product['quantity'], PDO::PARAM_INT);
            $stmt_detail->bindParam(':unit_price', $product['price'], PDO::PARAM_STR);
            $stmt_detail->bindParam(':subtotal', $product['subtotal'], PDO::PARAM_STR);
            $stmt_detail->execute();

            // Update product stock quantity
            $stmt_update_stock = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - :quantity WHERE product_id = :product_id");
            $stmt_update_stock->bindParam(':quantity', $product['quantity'], PDO::PARAM_INT);
            $stmt_update_stock->bindParam(':product_id', $product['product_id'], PDO::PARAM_INT);
            $stmt_update_stock->execute();
        }

        // Redirect or display success message
        header("Location: staff_sales_success.php?sale_id=$sale_id");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff - Make Sales</title>
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
    <h1>Make Sale</h1>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>

    <form method="POST" id="salesForm">
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
                                <option value="<?php echo $product['product_id']; ?>" data-price="<?php echo $product['new_price']; ?>" data-stock="<?php echo $product['stock_quantity']; ?>">
                                    <?php echo $product['name']; ?> - Rs. <?php echo number_format($product['new_price'], 2); ?> (Stock: <?php echo $product['stock_quantity']; ?>)
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
        <button type="submit" class="btn btn-primary">Complete Sale</button>
    </form>
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



