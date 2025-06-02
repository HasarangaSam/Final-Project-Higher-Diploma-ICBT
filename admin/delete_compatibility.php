<?php
session_start();
include('../connection.php');

// Check if user is logged in as admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit();
}

// Check if product_id and compatible_product_id are set
if (isset($_POST['product_id']) && isset($_POST['compatible_product_id'])) {
    $product_id = $_POST['product_id'];
    $compatible_product_id = $_POST['compatible_product_id'];

    // Delete the compatibility in both directions
    $stmt = $pdo->prepare("DELETE FROM compatibility WHERE (product_id = :product_id AND compatible_product_id = :compatible_product_id) 
                        OR (product_id = :compatible_product_id AND compatible_product_id = :product_id)");
    $stmt->bindParam(':product_id', $product_id);
    $stmt->bindParam(':compatible_product_id', $compatible_product_id);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Compatibility rule deleted successfully."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to delete compatibility rule."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request."]);
}
?>
