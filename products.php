<?php
session_start();

//store user id
if (isset($_SESSION['customer_id'])) {
    $customer_id = $_SESSION['customer_id'];
    $isLoggedIn = true;
} else {
    $customer_id = null;
    $isLoggedIn = false;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Products</title>
  <link rel="icon" type="image/png" href="images/logo.jpg">
  <!-- Linking Bootstrap CSS from CDN (Bootstrap 5) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Linking Bootstrap Icons (for any icon usage) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

  <!-- Linking custom stylesheets for additional styling -->
  <link rel="stylesheet" href="styles.css">
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

<?php
include("connection.php");

// Define how many products per page
$products_per_page = 6;

// Get the category, search keyword, and price range from the query parameters (if set)
$category = isset($_GET['category']) ? $_GET['category'] : null;
$search = isset($_GET['search']) ? $_GET['search'] : ''; // Retrieve search query
$min_price = isset($_GET['min_price']) ? $_GET['min_price'] : 0; // Minimum price
$max_price = isset($_GET['max_price']) ? $_GET['max_price'] : 1000000; // Maximum price

// Get the current page number (defaults to 1 if not set)
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $products_per_page;

// Fetch total number of products for pagination calculation (considering category, search, and price range)
if ($category && $search) {
    $total_stmt = $pdo->prepare("SELECT COUNT(*) FROM `products` WHERE `category` = :category AND (`name` LIKE :search OR `specifications` LIKE :search) AND `new_price` BETWEEN :min_price AND :max_price");
    $total_stmt->bindParam(':category', $category, PDO::PARAM_STR);
    $total_stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
    $total_stmt->bindParam(':min_price', $min_price, PDO::PARAM_INT);
    $total_stmt->bindParam(':max_price', $max_price, PDO::PARAM_INT);
} elseif ($category) {
    $total_stmt = $pdo->prepare("SELECT COUNT(*) FROM `products` WHERE `category` = :category AND `new_price` BETWEEN :min_price AND :max_price");
    $total_stmt->bindParam(':category', $category, PDO::PARAM_STR);
    $total_stmt->bindParam(':min_price', $min_price, PDO::PARAM_INT);
    $total_stmt->bindParam(':max_price', $max_price, PDO::PARAM_INT);
} elseif ($search) {
    $total_stmt = $pdo->prepare("SELECT COUNT(*) FROM `products` WHERE (`name` LIKE :search OR `specifications` LIKE :search) AND `new_price` BETWEEN :min_price AND :max_price");
    $total_stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
    $total_stmt->bindParam(':min_price', $min_price, PDO::PARAM_INT);
    $total_stmt->bindParam(':max_price', $max_price, PDO::PARAM_INT);
} else {
    $total_stmt = $pdo->prepare("SELECT COUNT(*) FROM `products` WHERE `new_price` BETWEEN :min_price AND :max_price");
    $total_stmt->bindParam(':min_price', $min_price, PDO::PARAM_INT);
    $total_stmt->bindParam(':max_price', $max_price, PDO::PARAM_INT);
}
$total_stmt->execute();
$total_products = $total_stmt->fetchColumn();

// Calculate total pages
$total_pages = ceil($total_products / $products_per_page);

// Fetch products for the current page
if ($category && $search) {
    $stmt = $pdo->prepare("SELECT `product_id`, `name`, `category`, `specifications`, `old_price`, `new_price`, `old_availability`, `new_availability`, `stock_quantity`, `image_url` 
                           FROM `products` WHERE `category` = :category AND (`name` LIKE :search OR `specifications` LIKE :search) AND `new_price` BETWEEN :min_price AND :max_price 
                           LIMIT :offset, :limit");
    $stmt->bindParam(':category', $category, PDO::PARAM_STR);
    $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
    $stmt->bindParam(':min_price', $min_price, PDO::PARAM_INT);
    $stmt->bindParam(':max_price', $max_price, PDO::PARAM_INT);
} elseif ($category) {
    $stmt = $pdo->prepare("SELECT `product_id`, `name`, `category`, `specifications`, `old_price`, `new_price`, `old_availability`, `new_availability`, `stock_quantity`, `image_url` 
                           FROM `products` WHERE `category` = :category AND `new_price` BETWEEN :min_price AND :max_price LIMIT :offset, :limit");
    $stmt->bindParam(':category', $category, PDO::PARAM_STR);
    $stmt->bindParam(':min_price', $min_price, PDO::PARAM_INT);
    $stmt->bindParam(':max_price', $max_price, PDO::PARAM_INT);
} elseif ($search) {
    $stmt = $pdo->prepare("SELECT `product_id`, `name`, `category`, `specifications`, `old_price`, `new_price`, `old_availability`, `new_availability`, `stock_quantity`, `image_url` 
                           FROM `products` WHERE (`name` LIKE :search OR `specifications` LIKE :search) AND `new_price` BETWEEN :min_price AND :max_price LIMIT :offset, :limit");
    $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
    $stmt->bindParam(':min_price', $min_price, PDO::PARAM_INT);
    $stmt->bindParam(':max_price', $max_price, PDO::PARAM_INT);
} else {
    $stmt = $pdo->prepare("SELECT `product_id`, `name`, `category`, `specifications`, `old_price`, `new_price`, `old_availability`, `new_availability`, `stock_quantity`, `image_url` 
                           FROM `products` WHERE `new_price` BETWEEN :min_price AND :max_price LIMIT :offset, :limit");
    $stmt->bindParam(':min_price', $min_price, PDO::PARAM_INT);
    $stmt->bindParam(':max_price', $max_price, PDO::PARAM_INT);
}
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

// Category Heading
$category_heading = $category ? ucfirst($category) : "All Products";
?>

<!-- Navigation Bar -->
<?php 
include('nav.php');
?>

<section id="products" class="container py-5">
    <!-- Search Bar -->
    <div class="row mb-4">
        <div class="col-md-12">
            <form class="d-flex" method="GET" action="products.php">
                <input type="text" class="form-control" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search products by name or specifications...">
                <button type="submit" class="btn btn-danger ms-2">Search</button>
            </form>
        </div>
    </div>

<!-- Price Filter -->
<div class="row mb-4 justify-content-center">
    <div class="col-md-8">
        <form method="GET" action="products.php">
            <input type="hidden" name="category" value="<?php echo $category; ?>">
            <div class="d-flex justify-content-between">
                <div class="d-flex flex-column align-items-start">
                    <label for="min_price" class="text-white mb-1">Min Price</label>
                    <input type="number" class="form-control mb-2" name="min_price" id="min_price" placeholder="Min Price" value="<?php echo htmlspecialchars($min_price); ?>" style="width: 150px;">
                </div>
                <div class="d-flex flex-column align-items-start ms-3">
                    <label for="max_price" class="text-white mb-1">Max Price</label>
                    <input type="number" class="form-control mb-2" name="max_price" id="max_price" placeholder="Max Price" value="<?php echo htmlspecialchars($max_price); ?>" style="width: 150px;">
                </div>
                <button type="submit" class="btn btn-danger ms-2">Filter by Price</button>
            </div>
        </form>
    </div>
</div>

    <!-- Product Grid -->
    <div class="container mt-5">
        <h1 class="text-center mb-4 text-white"><?php echo $category_heading; ?></h1>

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
                        <a class="page-link" href="?category=<?php echo $category; ?>&search=<?php echo htmlspecialchars($search); ?>&min_price=<?php echo $min_price; ?>&max_price=<?php echo $max_price; ?>&page=<?php echo $page - 1; ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                        <a class="page-link" href="?category=<?php echo $category; ?>&search=<?php echo htmlspecialchars($search); ?>&min_price=<?php echo $min_price; ?>&max_price=<?php echo $max_price; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?category=<?php echo $category; ?>&search=<?php echo htmlspecialchars($search); ?>&min_price=<?php echo $min_price; ?>&max_price=<?php echo $max_price; ?>&page=<?php echo $page + 1; ?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </section>

<!-- Compare Modal -->
<div class="modal fade" id="compareModal" tabindex="-1" aria-labelledby="compareModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="compareModalLabel">Compare Products</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="comparisonContent" class="row">
                    <!-- Comparison content will be dynamically injected here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php
include("footer.php"); 
?>

<!-- Bootstrap JS and Popper.js for modal functionality -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

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


</body>
</html>


