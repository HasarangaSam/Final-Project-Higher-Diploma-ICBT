<?php
session_start();
include('connection.php');

if (isset($_SESSION['customer_id'])) {
    $isLoggedIn = true;
} else {
    $isLoggedIn = false;
}

// Database connection
require 'connection.php';

// Fetch blog data using the blog_id from the URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $blog_id = $_GET['id'];

    // Fetch the blog data based on the blog_id
    $stmt = $pdo->prepare("SELECT `blog_id`, `title`, `content`, `created_at`, `img_src` FROM `blogs` WHERE `blog_id` = ?");
    $stmt->execute([$blog_id]);
    $blog = $stmt->fetch(PDO::FETCH_ASSOC);

    // If blog is not found, exit with error
    if (!$blog) {
        die("Blog not found.");
    }

    // Fetch comments related to this blog post
    $comment_stmt = $pdo->prepare("
        SELECT bc.comment_number, bc.blog_id, bc.customer_id, bc.comment_content, bc.created_at, c.first_name, c.last_name 
        FROM `blog_comment` bc 
        JOIN `customer` c ON bc.customer_id = c.customer_id 
        WHERE bc.blog_id = ?");
    $comment_stmt->execute([$blog_id]);
    $comments = $comment_stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    die("Invalid Blog ID.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Post</title>
    <link rel="icon" type="image/png" href="images/logo.jpg">
    <!-- Linking Bootstrap CSS from CDN (Bootstrap 5) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Linking Bootstrap Icons (for any icon usage) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background-color: #1e1e1e;
            color: white;
            font-family: Arial, sans-serif;
        }

        .page-title {
            font-size: 36px;
            font-weight: bold;
            color: #f8f9fa;
        }

        .blog-post {
            background-color: #333;
            padding: 20px;
            border-radius: 8px;
        }

        .blog-title {
            font-size: 28px;
            color: #f8f9fa;
        }

        .blog-content {
            margin-top: 20px;
            color: #f8f9fa;
        }

        .hr {
            border-color: #f8f9fa;
            width: 100%;
            margin-top: 40px;
        }

        .comments-section {
            margin-top: 40px;
        }

        .comment-item {
            background-color: #444;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .comment-item img {
            border-radius: 50%;
        }

        .comment-item .text-muted {
            color: #888;
        }

        .comment-item .text-dark {
            color: #f8f9fa;
        }

        .comment-item .bg-light {
            background-color: #222;
        }

        .read-more-btn {
            color: #fff;
            background-color: #c00;
            padding: 5px 10px;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .read-more-btn:hover {
            background-color: #ff6b6b;
        }

        .btn-danger {
            background-color: #ff0000;
            border: none;
        }

        .btn-danger:hover {
            background-color: #d60000;
        }
    </style>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="bg-dark text-white">

<!-- Navigation Bar -->
<?php include('nav.php'); ?>

<!-- Blog Section -->
<div class="container my-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <!-- Blog Post -->
            <div class="blog-post mb-5">
                <h2 class="display-4 font-weight-bold"><?= htmlspecialchars($blog['title']) ?></h2>
                <h4 class="mb-4"><small>Posted on <?= date('F j, Y', strtotime($blog['created_at'])); ?></small></h4>

                <!-- Blog Image -->
                <?php if (!empty($blog['img_src'])): ?>
                    <img src="admin/<?= htmlspecialchars($blog['img_src']); ?>" class="img-fluid rounded shadow-sm mb-4" alt="Blog Image">
                <?php endif; ?>

                <!-- Blog Content -->
                <div class="blog-content">
                    <p class="lead"><?= nl2br(htmlspecialchars($blog['content'])) ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<hr class="hr">

<!-- Comment Section -->
<div class="comments-section mt-5">
    <div class="container">
        <h4 class="font-weight-bold mb-4">Comments</h4>

        <!-- Display existing comments -->
        <?php if (count($comments) > 0): ?>
            <div class="bg-dark p-4 rounded-lg shadow-sm">
                <?php foreach ($comments as $comment): ?>
                    <div class="comment-item mb-4 p-4 rounded-lg border shadow-sm bg-light hover:bg-gray-100 transition-colors">
                        <div class="d-flex align-items-center mb-3">
                            <img src="images/user.png" alt="User Avatar" class="rounded-circle" width="40" height="40">
                            <div class="ms-3">
                                <strong class="text-dark"><?= htmlspecialchars($comment['first_name']) . " " . htmlspecialchars($comment['last_name']); ?></strong>
                                <small class="text-muted d-block"><?= date('F j, Y', strtotime($comment['created_at'])); ?></small>
                            </div>
                        </div>
                        <p class="mt-2 text-muted"><?= nl2br(htmlspecialchars($comment['comment_content'])); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>No comments yet. Be the first to comment!</p>
        <?php endif; ?>

        <!-- Comment form -->
<h5 class="font-weight-bold mt-5">Add a Comment</h5>
<form action="blogs_add_answer.php" method="POST">
    <div class="form-group">
        <textarea class="form-control" name="comment" rows="4" placeholder="Write your comment here..." required></textarea>
    </div>
    <!-- Hidden field for blog_id -->
    <input type="hidden" name="blog_id" value="<?= $blog_id ?>">

    <!-- Button to submit comment -->
    <button type="submit" class="btn btn-danger mt-3" 
        <?php if (!$isLoggedIn) { echo 'onclick="alert(\'Please log in to comment.\');"'; } ?>>Submit Comment</button>
</form>

    </div>
</div>

<?php include('footer.php'); ?>

<!-- Bootstrap JS and Popper.js -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>



