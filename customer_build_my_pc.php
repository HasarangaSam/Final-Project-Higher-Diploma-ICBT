<?php
session_start();
include('connection.php');

// Fetch categories for products (CPU, GPU, Motherboard, RAM, Storage, PSU)
$categories = ['CPU', 'GPU', 'Motherboard', 'RAM', 'Storage', 'PSU'];

// Fetch products for each category
$products = [];
foreach ($categories as $category) {
    $stmt = $pdo->prepare("SELECT product_id, name FROM products WHERE category = :category");
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
        // Check compatibility using the 'compatibility' table
        $incompatible = false;
        foreach ($components as $component => $component_id) {
            if (!empty($component_id)) {
                // Check compatibility against other selected components
                foreach ($components as $other_component => $other_component_id) {
                    if (!empty($other_component_id) && $component_id !== $other_component_id) {
                        $stmt_check = $pdo->prepare("SELECT * FROM compatibility WHERE product_id = :product_id AND compatible_product_id = :compatible_product_id");
                        $stmt_check->bindParam(':product_id', $component_id);
                        $stmt_check->bindParam(':compatible_product_id', $other_component_id);
                        $stmt_check->execute();

                        if ($stmt_check->rowCount() == 0) {
                            $incompatible = true;
                            break 2; // Break out of both loops
                        }
                    }
                }
            }
        }

        // If all products are compatible, insert into quotation table
        if (!$incompatible) {
            $stmt_insert = $pdo->prepare("INSERT INTO quotation (customer_id, cpu_id, gpu_id, motherboard_id, ram_id, storage_id, psu_id) 
            VALUES (:customer_id, :cpu_id, :gpu_id, :motherboard_id, :ram_id, :storage_id, :psu_id)");
            $stmt_insert->bindParam(':customer_id', $_SESSION['customer_id']);
            $stmt_insert->bindParam(':cpu_id', $components['cpu']);
            $stmt_insert->bindParam(':gpu_id', $components['gpu']);
            $stmt_insert->bindParam(':motherboard_id', $components['motherboard']);
            $stmt_insert->bindParam(':ram_id', $components['ram']);
            $stmt_insert->bindParam(':storage_id', $components['storage']);
            $stmt_insert->bindParam(':psu_id', $components['psu']);
            $stmt_insert->execute();

            echo "<script>alert('Quotation submitted successfully!');</script>";
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Linking Bootstrap Icons (for any icon usage) -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

<!-- Linking custom stylesheets for additional styling -->
<link rel="stylesheet" href="styles.css">
</head>
<body>

<!-- Navigation Bar -->
<?php 
include('nav.php');
?>


<div class="container">
    <h1 class="text-white">Build Your PC</h1>

    <form method="POST">
        <!-- CPU Dropdown -->
        <div class="mb-3">
            <label for="cpu" class="form-label text-white">Select CPU</label>
            <select class="form-select" id="cpu" name="cpu" required>
                <option value="">Select CPU</option>
                <?php foreach ($products['CPU'] as $product): ?>
                    <option value="<?php echo $product['product_id']; ?>"><?php echo $product['name']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- GPU Dropdown -->
        <div class="mb-3">
            <label for="gpu" class="form-label text-white">Select GPU</label>
            <select class="form-select" id="gpu" name="gpu" required>
                <option value="">Select GPU</option>
                <?php foreach ($products['GPU'] as $product): ?>
                    <option value="<?php echo $product['product_id']; ?>"><?php echo $product['name']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Motherboard Dropdown -->
        <div class="mb-3">
            <label for="motherboard" class="form-label text-white">Select Motherboard</label>
            <select class="form-select" id="motherboard" name="motherboard" required>
                <option value="">Select Motherboard</option>
                <?php foreach ($products['Motherboard'] as $product): ?>
                    <option value="<?php echo $product['product_id']; ?>"><?php echo $product['name']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- RAM Dropdown -->
        <div class="mb-3">
            <label for="ram" class="form-label text-white">Select RAM</label>
            <select class="form-select" id="ram" name="ram" required>
                <option value="">Select RAM</option>
                <?php foreach ($products['RAM'] as $product): ?>
                    <option value="<?php echo $product['product_id']; ?>"><?php echo $product['name']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Storage Dropdown -->
        <div class="mb-3">
            <label for="storage" class="form-label text-white">Select Storage</label>
            <select class="form-select" id="storage" name="storage" required>
                <option value="">Select Storage</option>
                <?php foreach ($products['Storage'] as $product): ?>
                    <option value="<?php echo $product['product_id']; ?>"><?php echo $product['name']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- PSU Dropdown -->
        <div class="mb-3">
            <label for="psu" class="form-label text-white">Select PSU</label>
            <select class="form-select" id="psu" name="psu" required>
                <option value="">Select PSU</option>
                <?php foreach ($products['PSU'] as $product): ?>
                    <option value="<?php echo $product['product_id']; ?>"><?php echo $product['name']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
</div>

<!-- Bootstrap JS and Popper.js for modal functionality -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
