<?php
session_start();
include('connection.php');

// Check if session data exists before proceeding
if (!isset($_SESSION['selected_components']) || !isset($_SESSION['total_amount'])) {
    echo "<script>alert('Session expired or missing data. Please build your PC again.'); window.location.href = 'build_my_pc.php';</script>";
    exit;
}

$components = $_SESSION['selected_components'];
$total_amount = $_SESSION['total_amount'];

// Allow only logged-in users to submit the quotation
if (!isset($_SESSION['customer_id'])) {
    echo "<script>alert('You must be logged in to submit a quotation.'); window.location.href = 'quotation_details.php';</script>";
    exit;
}

$customer_id = $_SESSION['customer_id'];

try {
    // Insert into build_my_pc_quotations table
    $stmt = $pdo->prepare("INSERT INTO build_my_pc_quotations (customer_id, total_amount, created_at) VALUES (:customer_id, :total_amount, NOW())");
    $stmt->bindParam(':customer_id', $customer_id);
    $stmt->bindParam(':total_amount', $total_amount);
    $stmt->execute();
    $quotation_id = $pdo->lastInsertId();

    // Insert each selected component into build_my_pc_quotation_detail table
    foreach ($components as $category => $product_id) {
        if (!empty($product_id)) {
            // Get the maximum number for the weak entity table (build_my_pc_quotation_detail)
            $stmt = $pdo->prepare("SELECT MAX(number) FROM build_my_pc_quotation_detail WHERE quotation_id = :quotation_id");
            $stmt->bindParam(':quotation_id', $quotation_id);
            $stmt->execute();
            $maxNumber = $stmt->fetchColumn();
            $next_number = $maxNumber ? $maxNumber + 1 : 1;  // If no records exist, start at 1

            // Insert the product details into the weak entity table (build_my_pc_quotation_detail)
            $stmt = $pdo->prepare("INSERT INTO build_my_pc_quotation_detail (number, quotation_id, product_id, price) 
            VALUES (:number, :quotation_id, :product_id, (SELECT new_price FROM products WHERE product_id = :product_id))");
            $stmt->bindParam(':number', $next_number);
            $stmt->bindParam(':quotation_id', $quotation_id);
            $stmt->bindParam(':product_id', $product_id);
            $stmt->execute();
        }
    }

    // Clear session data after successful submission
    unset($_SESSION['selected_components']);
    unset($_SESSION['total_amount']);

    // Success alert and redirect
    echo "<script>alert('Quotation submitted successfully!'); window.location.href = 'home.php';</script>";
} catch (PDOException $e) {
    echo "<script>alert('Error submitting quotation: " . $e->getMessage() . "'); window.history.back();</script>";
    exit;
}
?>



