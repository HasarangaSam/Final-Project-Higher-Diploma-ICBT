<?php
// Start session to manage user data (for cart management)
session_start();

//store user logged_in
if (isset($_SESSION['customer_id'])) {
    $isLoggedIn = true;
  } else {
    $isLoggedIn = false;
  }
  
// Include database connection 
require_once 'connection.php'; 

// Function to calculate discount percentage based on old and new price
function getDiscountPercentage($old_price, $new_price) {
    if ($old_price > 0 && $new_price < $old_price) {
        return round(((($old_price - $new_price) / $old_price) * 100), 2); // Calculate the percentage discount
    }
    return 0; // Return 0 if there's no discount
}

// Function to add a product to the cart
function addToCart($productId, $quantity = 1) {
    // Initialize the cart if it doesn't exist
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // If the product is already in the cart, increase the quantity
    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId] += $quantity;
    } else {
        $_SESSION['cart'][$productId] = $quantity;
    }
}

// Function to remove a product from the cart
function removeFromCart($productId) {
    // Check if the product exists in the cart, then remove it
    if (isset($_SESSION['cart'][$productId])) {
        unset($_SESSION['cart'][$productId]);
    }
}

// Function to get product details from the database using the product ID
function getProductDetails($productId) {
    global $pdo;
    // Prepare and execute the query to fetch the product details from the database
    $stmt = $pdo->prepare("SELECT `product_id`, `name`, `category`, `specifications`, `old_price`, `new_price`, `old_availability`, `new_availability`, `stock_quantity`, `image_url` FROM `products` WHERE `product_id` = ?");
    $stmt->execute([$productId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Function to calculate the total price of items in the cart
function getCartTotal() {
    global $pdo;
    $total = 0;
    // Check if the cart exists and calculate the total price of the products in the cart
    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $productId => $quantity) {
            $product = getProductDetails($productId);
            if ($product) {
                $total += $product['new_price'] * $quantity; // Add the product total price to the total
            }
        }
    }
    return $total; // Return the total price
}

// Handle adding a product to the cart via POST request
if (isset($_POST['add_to_cart'])) {
    $productId = $_POST['product_id']; // Get the product ID from the form
    $quantity = isset($_POST['quantity']) ? (int) $_POST['quantity'] : 1; // Get the quantity (default to 1)
    addToCart($productId, $quantity); // Add the product to the cart
    header("Location: cart.php");  // Redirect to the cart page
    exit; // Stop the script after redirection
}

// Handle removing a product from the cart via GET request
if (isset($_GET['remove'])) {
    $productId = (int) $_GET['remove']; // Get the product ID from the query parameter
    removeFromCart($productId); // Remove the product from the cart
    header("Location: cart.php"); // Redirect back to the cart page
    exit; // Stop the script after redirection
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cart</title>
  <link rel="icon" type="image/png" href="images/logo.jpg">
  <!-- Link Bootstrap CSS from the CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body>

<!-- Include Navigation Bar -->
<?php include('nav.php'); ?>

<!-- Cart Section -->
<section id="cart-section" class="container py-5">
    <h1 class="text-center text-white mb-4">Your Cart</h1>

    <!-- Cart Table -->
    <div class="table-responsive">
        <table class="table table-bordered text-white">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total</th>
                    <th>Remove</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $cartItems = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
                if (count($cartItems) > 0):
                    foreach ($cartItems as $productId => $quantity):
                        $product = getProductDetails($productId); // Get product details
                        if ($product):
                ?>
                            <tr>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td>Rs.<?php echo number_format($product['new_price'], 2); ?></td>
                                <td><?php echo $quantity; ?></td>
                                <td>Rs. <?php echo number_format($product['new_price'] * $quantity, 2); ?></td>
                                <td>
                                    <!-- Remove product from cart -->
                                    <a href="cart.php?remove=<?php echo $productId; ?>" class="btn btn-danger btn-sm">Remove</a>
                                </td>
                            </tr>
                <?php
                        endif;
                    endforeach;
                else:
                ?>
                    <tr>
                        <td colspan="5" class="text-center">Your cart is empty.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Cart Total -->
    <div class="d-flex justify-content-between">
        <h3 class="text-white">Total: Rs. <?php echo number_format(getCartTotal(), 2); ?></h3>
        <?php if ($isLoggedIn): ?>
    <a href="checkout.php" class="btn btn-success btn-lg">Proceed to Checkout</a>
        <?php else: ?>
            <button class="btn btn-success btn-lg" onclick="alertLogin()">Proceed to Checkout</button>
        <?php endif; ?>
    </div>
</section>

<!-- Include Footer -->
<?php include('footer.php'); ?>

<script>
function alertLogin() {
    alert("You must be logged in to proceed to checkout.");
    window.location.href = "login.php";
}
</script>


<!-- Bootstrap JS and Popper.js for modal functionality -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Bootstrap JS and Popper.js -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
