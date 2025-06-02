<?php
session_start();
include('connection.php');

// Fetch categories for products
$categories = ['CPU', 'GPU', 'Motherboard', 'RAM', 'Storage', 'PSU'];

// Fetch products for each category
$products = [];
foreach ($categories as $category) {
    $stmt = $pdo->prepare("SELECT product_id, name, new_price FROM products WHERE category = :category");
    $stmt->bindParam(':category', $category);
    $stmt->execute();
    $products[$category] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get selected components from the form
    $components = [
        'cpu' => $_POST['cpu'] ?? '',
        'gpu' => $_POST['gpu'] ?? '',
        'motherboard' => $_POST['motherboard'] ?? '',
        'ram' => $_POST['ram'] ?? '',
        'storage' => $_POST['storage'] ?? '',
        'psu' => $_POST['psu'] ?? ''
    ];

    // Check if all components are selected
    if (in_array('', $components)) {
        echo "<script>alert('Please select all required components.');</script>";
    } else {
        // Define necessary compatibility checks
        $compatibility_rules = [
            'cpu' => ['motherboard'],
            'motherboard' => ['cpu', 'ram', 'gpu', 'storage'],
            'ram' => ['motherboard'],
            'gpu' => ['motherboard', 'psu'],
            'storage' => ['motherboard'],
            'psu' => ['cpu', 'gpu']
        ];

        $incompatible = false;

        // Check compatibility based on defined rules
        foreach ($compatibility_rules as $component => $dependencies) {
            if (!empty($components[$component])) {
                foreach ($dependencies as $dependency) {
                    if (!empty($components[$dependency])) {
                        // Check if the selected component is compatible with its required dependency
                        $stmt_check = $pdo->prepare("SELECT * FROM compatibility WHERE product_id = :product_id AND compatible_product_id = :compatible_product_id");
                        $stmt_check->bindParam(':product_id', $components[$component]);
                        $stmt_check->bindParam(':compatible_product_id', $components[$dependency]);
                        $stmt_check->execute();

                        if ($stmt_check->rowCount() == 0) {
                            $incompatible = true;
                            break 2; // Exit both loops if an incompatibility is found
                        }
                    }
                }
            }
        }

        if (!$incompatible) {
            // Store components and calculate total price
            $_SESSION['selected_components'] = $components;
            $_SESSION['total_amount'] = 0;

            foreach ($components as $category => $product_id) {
                if (!empty($product_id)) {
                    $stmt = $pdo->prepare("SELECT new_price FROM products WHERE product_id = :product_id");
                    $stmt->bindParam(':product_id', $product_id);
                    $stmt->execute();
                    $product = $stmt->fetch(PDO::FETCH_ASSOC);
                    $_SESSION['total_amount'] += $product['new_price'];
                }
            }

            // Redirect to quotation details
            header('Location: quotation_details.php');
            exit;
        } else {
            echo "<script>alert('Incompatible components selected. Please choose compatible products.');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Build My PC</title>
    <link rel="icon" type="image/png" href="images/logo.jpg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<?php include('nav.php'); ?>

<div class="container">
    <br>
    <h1 class="text-white"><center>Build My PC</center></h1>
    
    <form id="pcBuildForm" method="POST">
        <?php foreach ($categories as $category): ?>
            <div class="mb-3">
                <label for="<?php echo strtolower($category); ?>" class="form-label text-white">Select <?php echo $category; ?></label>
                <select class="form-select component" id="<?php echo strtolower($category); ?>" name="<?php echo strtolower($category); ?>">
                    <option value="" data-price="0">Select <?php echo $category; ?></option>
                    <?php foreach ($products[$category] as $product): ?>
                        <option value="<?php echo $product['product_id']; ?>" data-price="<?php echo $product['new_price']; ?>">
                            <?php echo $product['name']; ?> - Rs. <?php echo number_format($product['new_price'], 2); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endforeach; ?>

        <h3 class="text-white">Total: <span id="totalPrice">Rs. 0.00</span></h3>
        
        <button type="submit" class="btn btn-danger">Confirm Quotation</button>
        <br><br>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.component').forEach(select => {
        select.addEventListener('change', function () {
            let total = 0;
            document.querySelectorAll('.component').forEach(sel => {
                if (sel.value) {
                    let price = sel.options[sel.selectedIndex].dataset.price;
                    total += parseFloat(price);
                }
            });
            document.getElementById('totalPrice').innerText = "Rs. " + total.toFixed(2);
        });
    });
});
</script>

<?php include('footer.php'); ?>

<!-- Bootstrap JS and Popper.js -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
