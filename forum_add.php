<?php
// Start session to manage customer data
session_start();

// Check if the customer is logged in
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php"); // Redirect to login page if customer is not logged in
    exit();
}

if (isset($_SESSION['customer_id'])) {
    $isLoggedIn = true;
} else {
    $isLoggedIn = false;
}

// Include the database connection
include 'connection.php';

// Handle form submission when 'title', 'category', and 'content' are provided
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['title'], $_POST['category'], $_POST['content'])) {
    // Get the form data
    $title = $_POST['title'];
    $category = $_POST['category'];
    $content = $_POST['content'];

    // Get the customer_id from the session
    $customer_id = $_SESSION['customer_id'];

    // Insert the new forum post into the database
    $stmt = $pdo->prepare("INSERT INTO `forums` (`customer_id`, `title`, `category`, `content`, `created_at`) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$customer_id, $title, $category, $content]);

    // Redirect to the forum page after adding the post
    header("Location: forums.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create New Forum</title>
  <link rel="icon" type="image/png" href="images/logo.jpg">
  <!-- Bootstrap CSS from CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body style="background-color: black;">

<!-- Navigation Bar -->
<nav class="navbar navbar-expand-lg bg-nav">
  <div class="container">
    <img src="images/logo.jpg" class="rounded d-block" style="max-width: 50px; height: 50px;" alt="Logo">
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse cyberpunk-font" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <a class="nav-link" href="home.php">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="about.php">About</a>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="productsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Products
          </a>
          <ul class="dropdown-menu" aria-labelledby="productsDropdown">
            <li><a class="dropdown-item" href="products.php?category=Motherboard">Motherboard</a></li>
            <li><a class="dropdown-item" href="products.php?category=Storage">Hard Disk</a></li>
            <li><a class="dropdown-item" href="products.php?category=RAM">RAM</a></li>
            <li><a class="dropdown-item" href="products.php?category=CPU">CPU</a></li>
            <li><a class="dropdown-item" href="products.php?category=GPU">GPU</a></li>
            <li><a class="dropdown-item" href="products.php?category=PSU">PSU</a></li>
            <li><a class="dropdown-item" href="products.php?category=Mouse">Mouse</a></li>
            <li><a class="dropdown-item" href="products.php?category=Mouse">Keyboard</a></li>
          </ul>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="build_my_pc.php">Build My PC</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="blogs.php">Blogs</a>
        </li>
        <li class="nav-item active">
          <a class="nav-link" href="forums.php">Forum</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="contact.php">Contact</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="my_account.php">My Account</a>
        </li>
        <!-- Cart and Wishlist -->
        <li class="nav-item">
          <a class="nav-link" href="cart.php" id="cart-link"><i class="bi bi-cart"></i></a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="wishlist.php" id="wishlist-link"><i class="bi bi-heart"></i></a>
        </li>
        <!-- Login Icon -->
        <li class="nav-item">
          <a class="nav-link" href="login.php" id="login-link"><i class="bi bi-person"></i></a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<?php
  include('chatbot.php');
?>
<!-- Forum Add Section -->
<div class="container my-5">
    <h2 class="text-center mb-4 text-white">Create a New Forum</h2>

    <!-- Forum creation form -->
    <form action="forum_add.php" method="POST">
        <!-- Forum Title -->
        <div class="mb-3">
            <label for="title" class="form-label text-white">Forum Title</label>
            <input type="text" class="form-control" id="title" name="title" required>
        </div>

        <!-- Forum Category Dropdown -->
        <div class="mb-3">
            <label for="category" class="form-label text-white">Category</label>
            <select class="form-select" id="category" name="category" required>
                <option value="Software Talks">Software Talks</option>
                <option value="Hardware Talks">Hardware Talks</option>
                <option value="Latest Tech News">Latest Tech News</option>
                <option value="General Help">General Help</option>
            </select>
        </div>

        <!-- Forum Content -->
        <div class="mb-3">
            <label for="content" class="form-label text-white">Content</label>
            <textarea class="form-control" id="content" name="content" rows="6" required></textarea>
        </div>

        <!-- Submit Button -->
        <div class="text-center">
            <button type="submit" class="btn btn-danger">Submit Forum</button>
        </div>
    </form>
</div>

<!-- Bootstrap JS and Popper.js for modal functionality -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


</body>
</html>
