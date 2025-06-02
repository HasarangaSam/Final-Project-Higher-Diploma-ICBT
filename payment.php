<?php
session_start();

// Check if the cart is empty or if the user is not logged in
if (empty($_SESSION['cart'])) {
    header("Location: cart.php"); // Redirect to cart if the cart is empty
    exit;
}

if (empty($_SESSION['customer_id'])) {
    header("Location: logout.php"); // Redirect if user is not logged in
    exit;
}

// Include database connection and loyalty functions
require_once 'connection.php';
require_once 'loyalty.php';

// Encryption settings (using AES-256-CBC)
define('ENCRYPTION_KEY', bin2hex(openssl_random_pseudo_bytes(32)));; // This generates a 256-bit key
define('ENCRYPTION_METHOD', 'aes-256-cbc'); // AES-256-CBC encryption method

// Function to get product details from the database
function getProductDetails($productId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT product_id, name, category, specifications, old_price, new_price, old_availability, new_availability, stock_quantity, image_url FROM products WHERE product_id = ?");
    $stmt->execute([$productId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Function to calculate the total of the cart
function getCartTotal() {
    global $pdo;
    $total = 0;
    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $productId => $quantity) {
            $product = getProductDetails($productId);
            if ($product) {
                $total += $product['new_price'] * $quantity;
            }
        }
    }
    return $total;
}

// Get the user's loyalty points
$userId = $_SESSION['customer_id']; // Ensure the user is logged in
$loyaltyPoints = getLoyaltyPoints($userId);
$currentPoints = $loyaltyPoints ? $loyaltyPoints['points'] : 0;

// Handle POST request from the checkout form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capture user information from the session and form
    $customerId = $_SESSION['customer_id'];
    $stmt = $pdo->prepare("SELECT first_name, last_name, email, phone FROM customer WHERE customer_id = ?");
    $stmt->execute([$customerId]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get payment details from form
    $shippingAddress = $_POST['shipping_address']; // Shipping address from form
    $cardNumber = $_POST['card_number']; // Debit card number
    $expiryDate = $_POST['expiry_date']; // Expiry Date (MM/YY)
    $cvv = $_POST['cvv']; // CVV Code
    $pointsToApply = $_POST['loyalty_points']; // Loyalty points to apply

    // Validate loyalty points
    if ($pointsToApply > $currentPoints) {
        echo "<script>alert('Error: You do not have enough loyalty points.'); window.history.back();</script>";
        exit;
    }

    // Ensure loyalty points do not exceed the total bill amount
    $totalAmount = getCartTotal();
    if ($pointsToApply > $totalAmount) {
        $pointsToApply = $totalAmount; // Limit to total bill amount
    }

    $totalAfterPoints = $totalAmount - $pointsToApply;

    // Insert order into the orders table
    $stmt = $pdo->prepare("INSERT INTO orders (customer_id, shipping_address, order_status, order_date, total_amount, points_used) 
                            VALUES (?, ?, 'Pending', NOW(), ?, ?)");
    $stmt->execute([$customerId, $shippingAddress, $totalAfterPoints, $pointsToApply]);

    // Get the newly inserted order_id
    $orderId = $pdo->lastInsertId();

    // Insert products into the order_details table
    $cartItems = $_SESSION['cart'];
    foreach ($cartItems as $productId => $quantity) {
        $stmt = $pdo->prepare("SELECT name, new_price FROM products WHERE product_id = ?");
        $stmt->execute([$productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        $unitPrice = $product['new_price'];
        $subtotal = $unitPrice * $quantity;
    
        // Get the maximum order_detail_number for the given order_id
        $stmt = $pdo->prepare("SELECT MAX(order_detail_number) FROM order_detail WHERE order_id = ?");
        $stmt->execute([$orderId]);
        $maxDetailNumber = $stmt->fetchColumn();
    
        // If there is no record, set the order_detail_number to 1
        $orderDetailNumber = $maxDetailNumber ? $maxDetailNumber + 1 : 1;
    
        // Insert the product into the order_detail table with the generated order_detail_number
        $stmt = $pdo->prepare("INSERT INTO order_detail (order_id, product_id, order_detail_number, quantity, unit_price, subtotal) 
                               VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$orderId, $productId, $orderDetailNumber, $quantity, $unitPrice, $subtotal]);
    
        // Reduce stock quantity
        $stmt = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE product_id = ?");
        $stmt->execute([$quantity, $productId]);
    }

    // Update loyalty points after the purchase (add 1% of the remaining amount as new points)
    $newLoyaltyPoints = $currentPoints - $pointsToApply + ($totalAfterPoints * 0.01);
    updateLoyaltyPoints($customerId, $newLoyaltyPoints);

    // Clear the cart after successful order insertion
    unset($_SESSION['cart']);

    // Save payment details for future use (encrypt card details)
    if (!empty($cardNumber) && !empty($expiryDate) && !empty($cvv)) {
        // Encrypt the card number, expiry date, and CVV before saving
        $iv = random_bytes(openssl_cipher_iv_length(ENCRYPTION_METHOD)); // Generate a secure IV
        $encryptedCardNumber = openssl_encrypt($cardNumber, ENCRYPTION_METHOD, ENCRYPTION_KEY, 0, $iv);
        $encryptedExpiryDate = openssl_encrypt($expiryDate, ENCRYPTION_METHOD, ENCRYPTION_KEY, 0, $iv);
        $encryptedCvv = openssl_encrypt($cvv, ENCRYPTION_METHOD, ENCRYPTION_KEY, 0, $iv);

        // Check if payment details already exist for the customer
        $stmt_payment_check = $pdo->prepare("SELECT payment_token FROM customer_payment_details WHERE customer_id = ?");
        $stmt_payment_check->execute([$customerId]);
        $payment_check = $stmt_payment_check->fetch(PDO::FETCH_ASSOC);

        if (!$payment_check) {
            // Insert encrypted payment details into the database
            $paymentToken = bin2hex(random_bytes(16)); // Generate a random token for future payments
            $stmt_payment_insert = $pdo->prepare("INSERT INTO customer_payment_details (customer_id, payment_token, card_number, expiry_date, cvv) 
                                                 VALUES (?, ?, ?, ?, ?)");
            $stmt_payment_insert->execute([$customerId, $paymentToken, $encryptedCardNumber, $encryptedExpiryDate, $encryptedCvv]);
        }
    }

    // Store order details in session for the PDF script
    $_SESSION['order_id'] = $orderId;
    $_SESSION['customer_name'] = $customer['first_name'] . " " . $customer['last_name'];
    $_SESSION['shipping_address'] = $shippingAddress;
    $_SESSION['cart_items'] = $cartItems;
    $_SESSION['total_after_points'] = $totalAfterPoints;
    $_SESSION['customer_email'] = $customer['email'];
    $_SESSION['customer_phone'] = $customer['phone'];
    // Store loyalty points in session
    $_SESSION['loyalty_points_used'] = $pointsToApply;

    // Show success message and trigger PDF download using JavaScript
    echo "<h2>Thank you, " . htmlspecialchars($customer['first_name']) . "!</h2>";
    echo "<p>Your order has been successfully placed.</p>";
    echo "<p>Total After Loyalty Points: Rs." . number_format($totalAfterPoints, 2) . "</p>";
    echo "<p>Your invoice will be downloaded shortly...</p>";

    // JavaScript to download PDF and redirect after 5 seconds
    echo "<script>
            setTimeout(function() {
                window.location.href = 'generate_invoice.php'; // Open PDF download
            }, 2000); // Delay download slightly for smooth user experience

            setTimeout(function() {
                window.location.href = 'cart.php'; // Redirect after 5 seconds
            }, 5000);
        </script>";

    exit;
}
?>



