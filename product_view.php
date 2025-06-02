<?php
// Start session to manage user data
session_start();

include('connection.php');

// Function to calculate the discount percentage
function getDiscountPercentage($old_price, $new_price) {
    // Check if the old price is greater than zero and the new price is lower than the old one
    if ($old_price > 0 && $new_price < $old_price) {
        return round(((($old_price - $new_price) / $old_price) * 100), 2); // Calculate the discount percentage
    }
    return 0; // Return 0 if there's no discount
}

// Get the product_id from the query string (URL parameter)
$product_id = isset($_GET['product_id']) ? $_GET['product_id'] : 0;

// Fetch the product details from the database
$query = "SELECT `product_id`, `name`, `category`, `specifications`, `old_price`, `new_price`, `old_availability`, `new_availability`, `stock_quantity`, `image_url`
          FROM `products` WHERE `product_id` = :product_id"; // Prepare the SQL query to fetch product details
$stmt = $pdo->prepare($query); // Prepare the query to be executed
$stmt->bindValue(':product_id', $product_id, PDO::PARAM_INT); // Bind the product_id value to the query
$stmt->execute(); // Execute the query
$product = $stmt->fetch(PDO::FETCH_ASSOC); // Fetch the product data as an associative array

// If the product is not found, show an error message and exit
if (!$product) {
    echo "Product not found!";
    exit();
}

// Get the discount percentage for the product
$discount_percentage = getDiscountPercentage($product['old_price'], $product['new_price']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Product Details</title>
  <link rel="icon" type="image/png" href="images/logo.jpg">
  <!-- Linking Bootstrap CSS from CDN (Bootstrap 5) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Linking Bootstrap Icons (for any icon usage) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

  <!-- Linking custom stylesheets for additional styling -->
  <link rel="stylesheet" href="styles.css">
</head>
<body>

<!-- Include Navigation Bar -->
<?php include('nav.php'); ?>

<!-- Product Details Section -->
<section id="product-details" class="container py-5">
  <h1 class="text-center mb-4 text-white">Product Details</h1>

  <div class="row">
    <!-- Product Image -->
    <div class="col-md-6">
    <?php
                        // Check if the image is a URL or relative path
                        if (filter_var($product['image_url'], FILTER_VALIDATE_URL)) {
                            // If it's a URL, use it directly
                            $imagePath = $product['image_url'];
                        } else {
                            // If it's a relative path, remove the "../" part
                            $imagePath = substr($product['image_url'], 3); // Remove "../"
                        }
      ?>
      <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="Product Image" class="img-fluid">
    </div>

    <!-- Product Information -->
    <div class="col-md-6">
      <h3 class="text-white"><?php echo htmlspecialchars($product['name']); ?></h3>

      <!-- Price Section -->
      <div class="mb-3">
        <?php if ($product['old_price'] > 0 && $product['old_price'] != $product['new_price']): ?>
          <span class="old-price text-white">Rs. <?php echo number_format($product['old_price'], 2); ?></span>
        <?php endif; ?>
        <span class="product-price text-white">Rs. <?php echo number_format($product['new_price'], 2); ?></span>
      </div>

      <!-- Availability Badge -->
      <div class="availability-badge <?php echo (strtolower($product['new_availability']) === 'in stock') ? 'available' : 'out-of-stock'; ?>">
        <?php echo (strtolower($product['new_availability']) === 'in stock') ? 'In Stock' : 'Out of Stock'; ?>
      </div>

      <!-- Product Specifications -->
      <h4 class="text-white">Specifications</h4>
      <p class="text-white"><?php echo nl2br(htmlspecialchars($product['specifications'])); ?></p>

      <!-- Action Buttons (Add to Cart, Wishlist) -->
      <div class="card-footer text-center">
        <form action="cart.php" method="POST" onsubmit="return validateQuantity(<?php echo $product['product_id']; ?>)">
          <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
          <input type="number" name="quantity" id="quantity-<?php echo $product['product_id']; ?>" value="1" min="1" class="form-control mb-2" style="width: 100px; display: inline-block;">
          <span id="available-stock-<?php echo $product['product_id']; ?>" style="display:none;"><?php echo $product['stock_quantity']; ?></span> <!-- Store available stock -->
          <button type="submit" name="add_to_cart" class="btn btn-primary btn-sm">
            <i class="bi bi-cart-plus"></i> Add to Cart
          </button>
        </form>
        <button class="btn btn-warning btn-sm" onclick="addToWishlist(<?php echo $product['product_id']; ?>)">
          <i class="bi bi-heart"></i> Add to Wishlist
        </button>
        <button type="button" class="btn btn-warning btn-sm" onclick="notifyUser(<?php echo $product['product_id']; ?>)">
          <i class="bi bi-bell"></i> Notify
        </button>
      </div>
    </div>
  </div>
</section>

<!-- Bootstrap JS and Popper.js for modal functionality -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Add JavaScript for Wishlist, Buy Now, and Cart -->
<script>
// Function to add products to comparison list in the session
function compareProduct(productId) {
    // Get the current compare list from sessionStorage
    let compareList = JSON.parse(sessionStorage.getItem('compareList')) || [];

    // Add the productId to the compare list if it's not already in the list
    if (!compareList.includes(productId)) {
        compareList.push(productId);
    }

    // Save the updated compare list back to sessionStorage
    sessionStorage.setItem('compareList', JSON.stringify(compareList));

    // Inform the user that the product was added to the comparison list
    alert("Product " + productId + " added to compare list.");
}

function addToWishlist(productId) {
    // Get the current wishlist from sessionStorage
    let wishlist = JSON.parse(sessionStorage.getItem('wishlist')) || [];

    // Add the productId to the wishlist if it's not already in the list
    if (!wishlist.includes(productId)) {
        wishlist.push(productId);
    }

    // Save the updated wishlist back to sessionStorage
    sessionStorage.setItem('wishlist', JSON.stringify(wishlist));

    // Inform the user that the product was added
    alert("Product added to wishlist.");
}

function addToCompare(productId) {
    // Send the product ID to a PHP file to store it in session
    var xhr = new XMLHttpRequest();
    xhr.open("GET", "add_to_compare.php?product_id=" + productId, true);
    xhr.onload = function() {
        // Inform the user that the product was added
        alert("Product added to compare list.");
    };
    xhr.send();
}


function notifyUser(productId) {
    // Get the logged-in user ID from PHP
    var customerId = <?php echo isset($_SESSION['customer_id']) ? $_SESSION['customer_id'] : 'null'; ?>;
    if (customerId === null) {
        alert('Please log in to get notifications.');
        return;
    }

    // Send the AJAX request
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "add_to_notify_wishlist.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onload = function() {
        if (xhr.status === 200) {
            alert(xhr.responseText); // Show server response
        } else {
            alert('An error occurred while adding the product.');
        }
    };
    xhr.send("product_id=" + productId + "&customer_id=" + customerId);
}

function validateQuantity(productId) {
    // Get the available stock for the product from the page
    var availableStock = parseInt(document.getElementById("available-stock-" + productId).innerText);

    // Get the quantity entered by the customer
    var quantity = document.getElementById("quantity-" + productId).value;

    // Validate the quantity
    if (quantity > availableStock) {
        alert("Quantity exceeds available stock! Only " + availableStock + " items are available.");
        return false; // Prevent form submission
    }

    return true; // Allow form submission
}
</script>

<!-- Include Footer -->
<?php include('footer.php'); ?>

<!-- Bootstrap JS and Popper.js -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

