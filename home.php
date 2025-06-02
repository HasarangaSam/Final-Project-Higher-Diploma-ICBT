<?php
session_start();

//store user id
if (isset($_SESSION['customer_id'])) {
    $customer_id = $_SESSION['customer_id'];
    $isLoggedIn = true;
} else {
    $customer_id = null;
    $isLoggedIn = false;
}?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Home</title> 
  <link rel="icon" type="image/png" href="images/logo.jpg">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="styles.css">
  <style>
    .pagination .page-link {
    background-color: white;
    color: black;
    border-color: red;
    }

    .pagination .page-item.active .page-link {
        background-color: #ed1c35;
        border-color: darkred;
}
  </style>
</head>
<body>
<!-- Navigation Bar -->
<?php 
include('nav.php');
?>

<!-- Carousel -->
<div id="imageCarousel" class="carousel slide" data-ride="carousel">
  <div class="carousel-inner">
    <div class="carousel-item active">
      <img src="images/bg_image1.jpg" class="d-block w-100" alt="Image 1">
    </div>
    <div class="carousel-item">
      <img src="images/bg_image3.png" class="d-block w-100" alt="Image 2">
    </div>
    <div class="carousel-item">
      <img src="images/bg_image2.png" class="d-block w-100" alt="Image 3">
    </div>
  </div>
  <a class="carousel-control-prev" href="#imageCarousel" role="button" data-slide="prev">
    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
    <span class="sr-only">Previous</span>
  </a>
  <a class="carousel-control-next" href="#imageCarousel" role="button" data-slide="next">
    <span class="carousel-control-next-icon" aria-hidden="true"></span>
    <span class="sr-only">Next</span>
  </a>
</div>

<?php
include("connection.php"); // Ensure your connection is correct

// Define how many products per page
$products_per_page = 3; 

// Get the current page number from the query parameter (defaults to 1 if not set)
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $products_per_page;

// Fetch total number of products for pagination calculation
$total_stmt = $pdo->prepare("SELECT COUNT(*) FROM `products`");
$total_stmt->execute();
$total_products = $total_stmt->fetchColumn();

// Calculate total pages
$total_pages = ceil($total_products / $products_per_page);

// Fetch the products for the current page
$stmt = $pdo->prepare("SELECT `product_id`, `name`, `category`,`specifications`, `old_price`, `new_price`, `old_availability`, `new_availability`, `stock_quantity`, `image_url` FROM `products` LIMIT :offset, :limit");
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->bindParam(':limit', $products_per_page, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Helper function to calculate discount percentage
function getDiscountPercentage($old_price, $new_price) {
    if ($old_price > 0 && $new_price < $old_price) {
        return round(100 * ($old_price - $new_price) / $old_price);
    }
    return 0;
}
?>

  <section id="products" class="container py-5">
    <!-- Product Grid -->
    <div class="container mt-5">
    <h1 class="text-center mb-4 text-white">Our Products</h1>

    <!-- Product Grid -->
    <div class="container mt-5">
        <div class="row">
            <?php foreach ($products as $product): ?>
                <div class="col-md-4 mt-5">
                    <div class="card product-card shadow-sm position-relative">
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
                        <img src="<?php echo htmlspecialchars($imagePath); ?>" class="card-img-top product-image" alt="Product Image" style="width: 100%;height: 250px;">
                        <div class="card-body">
                            <a href="product_view.php?product_id=<?php echo $product['product_id']; ?>" class="text-decoration-none">
                                <h5 class="card-title product-name"><?php echo htmlspecialchars($product['name']); ?></h5>
                            </a>
                            <p class="card-text product-category">Category: <?php echo htmlspecialchars($product['category']); ?></p>

                            <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <?php if ($product['old_price'] > 0 && $product['old_price'] != $product['new_price']): ?>
                                            <span class="old-price">Rs. <?php echo number_format($product['old_price'], 2); ?></span>
                                        <?php endif; ?>
                                        <span class="product-price">Rs. <?php echo number_format($product['new_price'], 2); ?></span>
                                    </div>

                                    <?php 
                                        $discount_percentage = getDiscountPercentage($product['old_price'], $product['new_price']);
                                        if ($discount_percentage > 0): 
                                    ?>
                                        <div class="discount-badge">
                                            <?php echo $discount_percentage; ?>% OFF
                                        </div>
                                    <?php endif; ?>
                                    <span class="availability-badge <?php echo (strtolower($product['new_availability']) === 'in stock') ? 'available' : 'out-of-stock'; ?>">
                                        <?php echo (strtolower($product['new_availability']) === 'in stock') ? 'In Stock' : 'Out of Stock'; ?>
                                    </span>
                            </div>

                        </div>

                        <div class="card-footer">
                            <small class="text-muted">Stock: <?php echo $product['stock_quantity']; ?> units</small>
                        </div>

                        <div class="card-footer text-center">
                            <!-- Add to Cart Button with Alert -->
                            <form action="cart.php" method="POST" onsubmit="return validateQuantity(<?php echo $product['product_id']; ?>)">
                                <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                                <input type="number" name="quantity" id="quantity-<?php echo $product['product_id']; ?>" value="1" min="1" class="form-control mb-2" style="width: 100px; display: inline-block;">
                                <span id="available-stock-<?php echo $product['product_id']; ?>" style="display:none;"><?php echo $product['stock_quantity']; ?></span> <!-- Store available stock -->
                                <button type="submit" name="add_to_cart" class="btn btn-primary btn-sm">
                                    <i class="bi bi-cart-plus"></i> Add to Cart
                                </button>
                            </form>


                            <button class="btn btn-warning btn-sm" onclick="addToWishlist(<?php echo $product['product_id']; ?>)">
                                <i class="bi bi-heart"></i>
                            </button>

                            <!-- Compare Button -->
                            <button type="button" class="btn btn-secondary btn-sm" onclick="addToCompare(<?php echo $product['product_id']; ?>)">
                                <i class="bi bi-arrow-right-left"></i> Compare
                            </button>

                            <!-- Notify Button (for future updates) -->
                            <button type="button" class="btn btn-danger btn-sm" onclick="notifyUser(<?php echo $product['product_id']; ?>)">
                                <i class="bi bi-bell"></i> Notify
                            </button>
                        </div>

                    </div>
                </div>
            <?php endforeach; ?>
        </div>


        <!-- Pagination -->
        <nav aria-label="Page navigation example">
            <ul class="pagination justify-content-center mt-4">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
        </div>
    </section>

<!-- Why Choose Dilan Computers Section -->
<section id="why-choose" class="container py-5">
    <h2 class="text-center mb-4 text-white">Why Choose Dilan Computers?</h2>
    <div class="row">
        <!-- Card 1 -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm text-center">
                <div class="card-body">
                    <h5 class="card-title">Sri Lanka's First AI Chatbot</h5>
                    <p class="card-text">Experience personalized support with Sri Lanka's first AI chatbot in a computer shop website.</p>
                </div>
            </div>
        </div>
        <!-- Card 2 -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm text-center">
                <div class="card-body">
                    <h5 class="card-title">Loyalty Program</h5>
                    <p class="card-text">Enjoy exclusive rewards and discounts through our customer loyalty program.</p>
                </div>
            </div>
        </div>
        <!-- Card 3 -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm text-center">
                <div class="card-body">
                    <h5 class="card-title">Real-Time Stock Updates</h5>
                    <p class="card-text">Stay informed with real-time stock updates and price drop notifications.</p>
                </div>
            </div>
        </div>
        <!-- Card 4 -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm text-center">
                <div class="card-body">
                    <h5 class="card-title">Advanced Build My PC Tool</h5>
                    <p class="card-text">Customize your perfect PC with our advanced 'Build My PC' tool, tailored to your needs.</p>
                </div>
            </div>
        </div>
        <!-- Card 5 -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm text-center">
                <div class="card-body">
                    <h5 class="card-title">Blogs</h5>
                    <p class="card-text">Stay updated with the latest news and tips through our informative blogs.</p>
                </div>
            </div>
        </div>
        <!-- Card for High-Quality Service -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm text-center">
                <div class="card-body">
                    <h5 class="card-title">High-Quality Service</h5>
                    <p class="card-text">We provide top-notch customer service, ensuring your needs are met with professionalism and care.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Community Section with Large Image and Text Overlay -->
<section id="community-forums" class="container-fluid py-5" style="background-image: url('images/bg_image1.jpg'); background-size: cover; color: white; position: relative; text-align: center;">
    <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, 0.5); z-index: 1;"></div>
    <div style="position: relative; z-index: 2;">
        <h2>Join Our Community Forums</h2>
        <p>Connect with other tech enthusiasts, share knowledge, and get expert advice. Be a part of our growing community.</p>
        <a href="forums.php" class="btn btn-danger btn-lg">Visit Forums</a>
    </div>
</section>



<script src="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.js"></script>
<script>
    function addToCart(productId) {
        alert("Product " + productId + " added to cart.");
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
    alert("Product " + productId + " added to wishlist.");
    console.error(sessionStorage);
    }

    function buyNow(productId) {
        alert("Proceeding to buy product " + productId + ".");
    }
</script>

<script>
function addToCompare(productId) {
    // Send the product ID to a PHP file to store it in session
    var xhr = new XMLHttpRequest();
    xhr.open("GET", "add_to_compare.php?product_id=" + productId, true);
    xhr.onload = function() {
        // Inform the user that the product was added
        alert("Product " + productId + " added to compare list.");
    };
    xhr.send();
}

</script>

<script>
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

<!-- jQuery and Bootstrap JS (for the carousel functionality) -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

      <!-- Include Footer -->
      <?php include('footer.php'); ?>

</body>
</html>
