<?php
session_start();
if (isset($_SESSION['customer_id'])) {
    $isLoggedIn = true;
} else {
    $isLoggedIn = false;
}
include('connection.php');

// Fetch selected components from session
if (!isset($_SESSION['selected_components']) || !isset($_SESSION['total_amount'])) {
    echo "<script>alert('Session expired or missing data. Please build your PC again.'); window.location.href = 'build_my_pc.php';</script>";
    exit;
}

$components = $_SESSION['selected_components'];
$totalAmount = $_SESSION['total_amount'];

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

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quotation Details</title>
    <link rel="icon" type="image/png" href="images/logo.jpg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="styles.css">
    <style>
    @media print {
        body {
            background: white;
            color: black;
        }
        .btn, nav {
            display: none; /* Hides buttons and navbar */
        }
        .container {
            width: 100%;
        }
    }
</style>

<script>
        function printQuotation() {
            window.print();
        }
</script>

</head>
<body>

<?php include('nav.php'); ?>

<div class="container">
    <br>
    <h1 class="text-white">Quotation Details</h1>
    <table class="table table-dark">
        <thead>
            <tr>
                <th>Component</th>
                <th>Product Name</th>
                <th>Price (Rs.)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($selectedProducts as $category => $product): ?>
                <tr>
                    <td><?php echo ucfirst($category); ?></td>
                    <td><?php echo $product['name']; ?></td>
                    <td>Rs. <?php echo number_format($product['new_price'], 2); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <h3 class="text-white">Total Amount: Rs. <?php echo number_format($totalAmount, 2); ?></h3>
    
    <form method="POST" action="submit_quotation.php">
        <button type="submit" class="btn btn-success">Submit Quotation</button>
    </form>
    
    <form method="POST" action="download_quotation.php">
        <button type="submit" class="btn btn-primary mt-2">Download Quotation (PDF)</button>
    </form>

    <button onclick="printQuotation()" class="btn btn-warning mt-2">Print Quotation</button>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
