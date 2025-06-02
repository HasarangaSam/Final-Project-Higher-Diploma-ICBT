<?php
session_start();
include('connection.php');

if (isset($_SESSION['customer_id'])) {
    $isLoggedIn = true;
} else {
    $isLoggedIn = false;
}?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Blogs</title>
  <link rel="icon" type="image/png" href="images/logo.jpg">
  <!-- Linking Bootstrap CSS from CDN (Bootstrap is already imported, no need for node_modules here) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  
  <!-- Linking Bootstrap Icons (for any icon usage) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    /* Blog Page Custom Styles */
    .page-title {
      color: #fff;
      font-size: 2.5rem;
      font-weight: bold;
      text-align: center;
    }

    .blog-card {
      background-color: #1d1d1d;
      color: #fff;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      margin-bottom: 20px;
      transition: transform 0.3s ease;
    }

    .blog-card:hover {
      transform: translateY(-10px);
    }

    .blog-image {
      width: 100%;
      height: 200px;
      object-fit: cover;
    }

    .blog-content {
      padding: 20px;
    }

    .blog-title {
      font-size: 1.5rem;
      font-weight: bold;
      color: #f8f9fa;
      margin-bottom: 10px;
    }

    .blog-excerpt {
      font-size: 1rem;
      color: #ccc;
      margin-bottom: 15px;
    }

    .read-more-btn {
      font-size: 1rem;
      color: #007bff;
      text-decoration: none;
    }

    .read-more-btn:hover {
      text-decoration: underline;
    }

    /* Responsive Blog Cards */
    @media (max-width: 768px) {
      .blog-card {
        margin-bottom: 15px;
      }
    }
  </style>

  <link rel="stylesheet" href="styles.css"> <!-- General styles -->

  <!-- Removing unnecessary imports for jQuery, Bootstrap JS, and Popper as Bootstrap 5 uses bundle version (included in the CDN above) -->
</head>

<body>

<!-- Navigation Bar (including dynamic navigation) -->
<?php 
  include('nav.php'); 
?>

<!-- Fetching and Displaying Blogs -->
<?php
  // Connect to the database and fetch blog data
  include("connection.php");
  $query = "SELECT `blog_id`, `title`, `content`, `created_at`, `img_src` FROM `blogs` ORDER BY `created_at` DESC";
  $stmt = $pdo->query($query);
  $blogs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Main Content Section -->
<div class="container">
  <!-- Page Title -->
  <h1 class="page-title mt-4">Latest Blogs</h1>
  <br>
  
  <div class="row">
    <!-- Dynamically display each blog card -->
    <?php foreach ($blogs as $blog): ?>
      <div class="col-md-3">
        <div class="blog-card">
           <!-- Blog Image -->
           <?php
                    // Check if the image path starts with "../"
                    if (strpos($blog['img_src'], '../') === 0) {
                        // Remove "../" from the start of the image path
                        $imagePath = substr($blog['img_src'], 3); // Remove the first 3 characters (../)
                    } else {
                        $imagePath = $blog['img_src']; // If no "../", keep the path as is
                    }
                ?>
                <img src="<?= htmlspecialchars($imagePath) ?>" alt="Image for <?= htmlspecialchars($blog['title']) ?>" class="blog-image">

          <div class="blog-content">
            <!-- Blog Title -->
            <h3 class="blog-title"><?= htmlspecialchars($blog['title']) ?></h3>

            <!-- Blog Excerpt (first 100 characters of content) -->
            <p class="blog-excerpt"><?= substr($blog['content'], 0, 500) ?>...</p>
            
            <!-- Read More Link -->
            <a href="blog_reads.php?id=<?= $blog['blog_id'] ?>" class="read-more-btn">Read More</a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<br>
<br>

<?php 
  include('footer.php'); 
?>

<!-- Bootstrap JS and Popper.js (only need this for Bootstrap functionality, which is included via CDN) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

</body>
</html>
