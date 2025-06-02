<?php
// loyalty.php
include 'connection.php';

//store user id
if (isset($_SESSION['customer_id'])) {
    $customer_id = $_SESSION['customer_id'];
} else {
    $customer_id = null;
}

// Function to get loyalty points for a user
function getLoyaltyPoints($customer_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT `points` FROM `loyalty` WHERE `customer_id` = ?");
    $stmt->execute([$customer_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Function to update loyalty points for a user
function updateLoyaltyPoints($customer_id, $newPoints) {
    global $pdo;
    // Check if user already has loyalty points
    $stmt = $pdo->prepare("SELECT `points` FROM `loyalty` WHERE `customer_id` = ?");
    $stmt->execute([$customer_id]);
    $existingPoints = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existingPoints) {
        // Update points if they already exist
        $stmt = $pdo->prepare("UPDATE `loyalty` SET `points` = ? WHERE `customer_id` = ?");
        $stmt->execute([$newPoints, $customer_id]);
    } else {
        // Insert new loyalty points record if it doesn't exist
        $stmt = $pdo->prepare("INSERT INTO `loyalty` (`customer_id`, `points`) VALUES (?, ?)");
        $stmt->execute([$customer_id, $newPoints]);
    }
}
?>