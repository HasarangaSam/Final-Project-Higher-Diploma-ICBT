<?php  
session_start(); // Start the session to manage user data

// Redirect to cart if the cart is empty
if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit;
}

if (isset($_SESSION['customer_id'])) {
    $isLoggedIn = true;
} else {
    $isLoggedIn = false;
}

// Include the database connection and loyalty function
require_once 'connection.php';
require_once 'loyalty.php';

// Function to get product details from the database
function getProductDetails($productId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT `product_id`, `name`, `category`, `specifications`, `old_price`, `new_price`, `old_availability`, `new_availability`, `stock_quantity`, `image_url` FROM `products` WHERE `product_id` = ?");
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

// Get the user's loyalty points (if applicable)
$userId = $_SESSION['customer_id']; // Ensure the user is logged in
$loyaltyPoints = getLoyaltyPoints($userId);
$currentPoints = $loyaltyPoints ? $loyaltyPoints['points'] : 0;

// Check if the user already has saved payment details
$stmt_payment = $pdo->prepare("SELECT payment_token FROM customer_payment_details WHERE customer_id = ?");
$stmt_payment->execute([$userId]);
$payment_method = $stmt_payment->fetch(PDO::FETCH_ASSOC);
$paymentToken = $payment_method ? $payment_method['payment_token'] : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="icon" type="image/png" href="images/logo.jpg">
    <!-- Linking Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="styles.css">

    <!-- JavaScript for Formatting Card Number -->
    <script>
        function formatCardNumber(event) {
            let cardNumber = event.target.value.replace(/\D/g, '');  // Remove non-digits
            if (cardNumber.length > 4) {
                cardNumber = cardNumber.slice(0, 4) + '-' + cardNumber.slice(4);
            }
            if (cardNumber.length > 9) {
                cardNumber = cardNumber.slice(0, 9) + '-' + cardNumber.slice(9);
            }
            if (cardNumber.length > 14) {
                cardNumber = cardNumber.slice(0, 14) + '-' + cardNumber.slice(14);
            }
            event.target.value = cardNumber;  // Set formatted value back to input
        }
    </script>
</head>
<body>

    <!-- Include the navigation bar -->
    <?php include('nav.php'); ?>

    <!-- Checkout Section -->
    <section id="checkout" class="container py-5">
        <h1 class="text-center text-white mb-4">Checkout</h1>

        <div class="row">
            <!-- Cart Summary Section -->
            <div class="col-md-6">
                <h4 class="text-white">Cart Summary</h4>
                <ul class="list-group">
                    <?php
                    // Display the products in the cart
                    $cartItems = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
                    foreach ($cartItems as $productId => $quantity):
                        $product = getProductDetails($productId);
                        if ($product):
                    ?>
                        <li class="list-group-item">
                            <?php echo htmlspecialchars($product['name']); ?> - Rs. <?php echo number_format($product['new_price'], 2); ?> x <?php echo $quantity; ?>
                            <span class="badge badge-secondary float-right">Rs. <?php echo number_format($product['new_price'] * $quantity, 2); ?></span>
                        </li>
                    <?php
                        endif;
                    endforeach;
                    ?>
                </ul>
                <hr>
                <h4 class="text-white">Total: Rs. <?php echo number_format(getCartTotal(), 2); ?></h4>
            </div>

            <!-- Payment and User Info Section -->
            <div class="col-md-6">
                <h4 class="text-white">Enter Your Payment Information</h4>
                <form action="payment.php" method="POST">
                    <!-- Shipping Address -->
                    <div class="form-group">
                        <label for="shipping_address" class="text-white mt-2">Shipping Address</label>
                        <input type="text" class="form-control" id="shipping_address" name="shipping_address" placeholder="Enter Shipping Address" required>
                    </div>

                    <!-- One-Click Payment Option -->
                    <div class="form-group mt-4">
                        <label class="text-white mt-2"><b>One-Click Payment</b></label>
                        <?php if ($paymentToken): ?>
                            <button type="submit" name="payment_token" value="<?= $paymentToken ?>" class="btn btn-success btn-lg btn-block">Click Here</button>
                        <?php else: ?>
                            <p class="text-white">You don't have a saved payment method. Please enter your details below to save for future use.</p>
                        <?php endif; ?>
                    </div>

                    <!-- Debit Card Number (only if no saved payment) -->
                    <div class="form-group">
                        <label for="card_number" class="text-white mt-2">Credit/Debit Card Number</label>
                        <input type="text" class="form-control" id="card_number" name="card_number" placeholder="Enter Card Number" maxlength="19" oninput="formatCardNumber(event)">
                    </div>

                    <!-- Expiry Date -->
                    <div class="form-group">
                        <label for="expiry_date" class="text-white mt-2">Expiry Date</label>
                        <input type="text" class="form-control" id="expiry_date" name="expiry_date" placeholder="MM/YY">
                    </div>

                    <!-- CVV Code -->
                    <div class="form-group">
                        <label for="cvv" class="text-white mt-2">CVV Code</label>
                        <input type="text" class="form-control" id="cvv" name="cvv" placeholder="Enter CVV">
                    </div>

                    <!-- Loyalty Points -->
                    <div class="form-group">
                        <label for="loyalty_points" class="text-white mt-2">Loyalty Points to Apply</label>
                        <input type="number" class="form-control" id="loyalty_points" name="loyalty_points" max="<?php echo $currentPoints; ?>" min="0" value="0">
                        <small class="form-text text-white">You have <?php echo $currentPoints; ?> points.</small>
                    </div>

                    <!-- Hidden Product Details (for processing) -->
                    <?php
                    foreach ($cartItems as $productId => $quantity):
                        $product = getProductDetails($productId);
                        if ($product):
                    ?>
                        <input type="hidden" name="product_ids[]" value="<?php echo $productId; ?>">
                        <input type="hidden" name="quantities[]" value="<?php echo $quantity; ?>">
                        <input type="hidden" name="prices[]" value="<?php echo $product['new_price']; ?>">
                    <?php
                        endif;
                    endforeach;
                    ?>

                    <button type="submit" class="btn btn-danger btn-lg btn-block mt-4">Proceed to Payment</button>
                </form>
            </div>
        </div>
    </section>

    <!-- Include Footer -->
    <?php include('footer.php'); ?>

</body>
</html>



