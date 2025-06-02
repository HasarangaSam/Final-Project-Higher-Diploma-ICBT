<?php
session_start();
// Check if user is logged in as admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Database Connection
include('../connection.php');

// Allowed categories for compatibility
$allowed_categories = ['CPU', 'GPU', 'Motherboard', 'RAM', 'Storage', 'PSU'];

// Fetch categories for products
$stmt = $pdo->query("SELECT DISTINCT category FROM products WHERE category IN ('" . implode("','", $allowed_categories) . "')");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch main component products when category is selected
if (isset($_POST['category_main'])) {
    $category_main = $_POST['category_main'];
} else {
    $category_main = '';
}

// Fetch compatibility component products when category is selected
if (isset($_POST['category_compatibility'])) {
    $category_compatibility = $_POST['category_compatibility'];
} else {
    $category_compatibility = '';
}

// Handle form submission to insert compatibility
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['main_component']) && isset($_POST['compatible_products'])) {
    $main_component_id = $_POST['main_component'];
    $compatible_products = $_POST['compatible_products'];

    // Loop through the selected compatible products
    foreach ($compatible_products as $compatible_id) {
        // Check if the compatibility relationship already exists in the database (both directions)
        $stmt_check = $pdo->prepare("SELECT * FROM compatibility WHERE product_id = :product_id AND compatible_product_id = :compatible_product_id");
        $stmt_check->bindParam(':product_id', $main_component_id);
        $stmt_check->bindParam(':compatible_product_id', $compatible_id);
        $stmt_check->execute();

        if ($stmt_check->rowCount() == 0) {
            // Insert the compatibility relationship (main component -> compatible component)
            $stmt_insert = $pdo->prepare("INSERT INTO compatibility (product_id, compatible_product_id) VALUES (:product_id, :compatible_product_id)");
            $stmt_insert->bindParam(':product_id', $main_component_id);
            $stmt_insert->bindParam(':compatible_product_id', $compatible_id);
            $stmt_insert->execute();
        }

        // Now check the reverse relationship (compatible -> main) to ensure both directions are added
        $stmt_check_reverse = $pdo->prepare("SELECT * FROM compatibility WHERE product_id = :product_id AND compatible_product_id = :compatible_product_id");
        $stmt_check_reverse->bindParam(':product_id', $compatible_id);
        $stmt_check_reverse->bindParam(':compatible_product_id', $main_component_id);
        $stmt_check_reverse->execute();

        if ($stmt_check_reverse->rowCount() == 0) {
            // Insert the reverse compatibility relationship (compatible component -> main component)
            $stmt_reverse = $pdo->prepare("INSERT INTO compatibility (product_id, compatible_product_id) VALUES (:product_id, :compatible_product_id)");
            $stmt_reverse->bindParam(':product_id', $compatible_id);
            $stmt_reverse->bindParam(':compatible_product_id', $main_component_id);
            $stmt_reverse->execute();
        }
    }

    echo "<script>alert('Compatibility rules updated successfully!'); window.location.href = 'admin_manage_compatibility.php';</script>";
}

// Handle filter form submission
$filtered_compatibility = [];
if (isset($_POST['filter_main_component'])) {
    $filter_main_component = $_POST['filter_main_component'];

    // Fetch filtered compatibility rules
    $stmt_filter = $pdo->prepare("SELECT p1.name AS main_product, GROUP_CONCAT(p2.name SEPARATOR ', ') AS compatible_products
                                  FROM compatibility c
                                  JOIN products p1 ON c.product_id = p1.product_id
                                  JOIN products p2 ON c.compatible_product_id = p2.product_id
                                  WHERE c.product_id = :product_id
                                  GROUP BY c.product_id");
    $stmt_filter->bindParam(':product_id', $filter_main_component);
    $stmt_filter->execute();
    $filtered_compatibility = $stmt_filter->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Compatibility - Build My PC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="wrapper">
    <?php include('sidebar.php'); ?>

    <div class="main-content">
        <div class="container mt-5">
            <h1>Manage Compatibility</h1>
            
            <!-- Step-by-step Form to select main component and compatible components -->
            <form method="POST" action="admin_manage_compatibility.php" id="compatibilityForm">
                <!-- Main Component Category Dropdown -->
                <div class="mb-3">
                    <label for="category_main" class="form-label">Select Main Component Category</label>
                    <select class="form-select" id="category_main" name="category_main" onchange="this.form.submit()" required>
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['category']; ?>" <?php echo ($category_main == $category['category']) ? 'selected' : ''; ?>><?php echo $category['category']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Main Component Selection -->
                <?php if ($category_main != ''): ?>
                    <div class="mb-3">
                        <label for="main_component" class="form-label">Select Main Component</label>
                        <select class="form-select" id="main_component" name="main_component" required>
                            <option value="">Select Product</option>
                            <?php
                            $stmt = $pdo->prepare("SELECT * FROM products WHERE category = :category");
                            $stmt->bindParam(':category', $category_main);
                            $stmt->execute();
                            $products_main = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($products_main as $product):
                            ?>
                                <option value="<?php echo $product['product_id']; ?>" <?php echo (isset($_POST['main_component']) && $_POST['main_component'] == $product['product_id']) ? 'selected' : ''; ?> >
                                    <?php echo $product['name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <!-- Compatibility Component Category Dropdown -->
                <div class="mb-3">
                    <label for="category_compatibility" class="form-label">Select Compatibility Category</label>
                    <select class="form-select" id="category_compatibility" name="category_compatibility" onchange="this.form.submit()" required>
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['category']; ?>" <?php echo ($category_compatibility == $category['category']) ? 'selected' : ''; ?>><?php echo $category['category']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Compatibility Component Selection -->
                <?php if ($category_compatibility != ''): ?>
                    <div class="mb-3">
                        <label for="compatible_products" class="form-label">Select Compatible Products</label>
                        <select class="form-select" id="compatible_products" name="compatible_products[]" multiple required>
                            <?php
                            $stmt = $pdo->prepare("SELECT * FROM products WHERE category = :category");
                            $stmt->bindParam(':category', $category_compatibility);
                            $stmt->execute();
                            $products_compatibility = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($products_compatibility as $product):
                            ?>
                                <option value="<?php echo $product['product_id']; ?>" <?php echo (isset($_POST['compatible_products']) && in_array($product['product_id'], $_POST['compatible_products'])) ? 'selected' : ''; ?>>
                                    <?php echo $product['name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <button type="submit" class="btn btn-primary">Confirm Compatibility</button>
            </form>

            <!-- Filter Compatibility Section -->
            <div class="mt-5 p-4" style="background-color: #f0f8ff; border-radius: 8px; border: 1px solid #ddd;">
                <h3>Filter Compatibility</h3>
                <form method="POST" action="admin_manage_compatibility.php">
                    <div class="mb-3">
                        <label for="filter_main_component" class="form-label">Filter by Main Component</label>
                        <select class="form-select" id="filter_main_component" name="filter_main_component" required>
                            <option value="">Select Main Component</option>
                            <?php
                            $stmt = $pdo->query("SELECT DISTINCT product_id, name FROM products WHERE category IN ('" . implode("','", $allowed_categories) . "')");
                            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($products as $product):
                            ?>
                                <option value="<?php echo $product['product_id']; ?>" <?php echo (isset($filter_main_component) && $filter_main_component == $product['product_id']) ? 'selected' : ''; ?>>
                                    <?php echo $product['name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-info">Filter</button>
                </form>

                <?php if (!empty($filtered_compatibility)): ?>
                    <h4 class="mt-4">Filtered Compatibility Rules</h4>
                    <table class="table table-bordered table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>Main Component</th>
                                <th>Compatible Products</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($filtered_compatibility as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['main_product']); ?></td>
                                    <td>
                                        <ul>
                                            <?php 
                                                $compatible_products = explode(', ', $row['compatible_products']);
                                                foreach ($compatible_products as $compatible_product):
                                            ?>
                                                <li><?php echo htmlspecialchars($compatible_product); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <!-- Display Compatibility Rules -->
            <h3 class="mt-4">Current Compatibility Rules</h3>
<table class="table table-bordered table-striped">
    <thead class="table-dark">
        <tr>
            <th>Main Component</th>
            <th>Compatible Products</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $stmt_compatibility = $pdo->prepare("SELECT c.product_id, c.compatible_product_id, p1.name AS main_product, p2.name AS compatible_product
                                            FROM compatibility c
                                            JOIN products p1 ON c.product_id = p1.product_id
                                            JOIN products p2 ON c.compatible_product_id = p2.product_id");
        $stmt_compatibility->execute();
        while ($row = $stmt_compatibility->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['main_product']) . "</td>";
            echo "<td>" . htmlspecialchars($row['compatible_product']) . "</td>";
            echo "<td><button class='btn btn-danger btn-sm delete-btn' data-main='" . $row['product_id'] . "' data-compatible='" . $row['compatible_product_id'] . "'>Delete</button></td>";
            echo "</tr>";
        }
        ?>
    </tbody>
</table>

        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll(".delete-btn").forEach(button => {
        button.addEventListener("click", function() {
            let productId = this.getAttribute("data-main");
            let compatibleId = this.getAttribute("data-compatible");

            if (confirm("Are you sure you want to delete this compatibility rule?")) {
                fetch("delete_compatibility.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: `product_id=${productId}&compatible_product_id=${compatibleId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === "success") {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => console.error("Error:", error));
            }
        });
    });
});
</script>

</body>
</html>










