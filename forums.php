<?php
session_start();
include('connection.php');

if (isset($_SESSION['customer_id'])) {
    $isLoggedIn = true;
} else {
    $isLoggedIn = false;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Forum Community - Dilan Computers</title> 
  <link rel="icon" type="image/png" href="images/logo.jpg">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">

  <style>
    /* Global Body Styles */
    body {
        background-color: #121212; /* Dark background for the entire page */
        color: #f5f5f5; /* Light text color for readability */
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    /* Heading Styles */
    h2 {
        color: #ffffff;
        font-size: 32px;
        text-align: center;
        margin-bottom: 30px;
    }

    /* Forum Card Design */
    .forum-card, .answer-card {
        background-color: #1e1e1e; /* Dark background for cards */
        border: none; /* Remove card border */
        border-radius: 10px; /* Rounded corners for a sleek look */
        margin-bottom: 20px;
        box-shadow: 0px 10px 15px rgba(0, 0, 0, 0.3); /* Soft shadows for depth */
    }

    .forum-card:hover, .answer-card:hover {
        background-color: #333; /* Lighter dark background on hover */
        box-shadow: 0px 10px 20px rgba(0, 0, 0, 0.5); /* Increase shadow effect on hover */
    }

    /* Card Header Styles */
    .card-header {
        background-color: #2c2c2c;
        color: #ffffff;
        font-size: 18px;
        font-weight: bold;
        padding: 15px;
        border-top-left-radius: 10px;
        border-top-right-radius: 10px;
    }

    /* Card Body Styles */
    .card-body {
        background-color: #222;
        color: #e0e0e0; /* Slightly lighter text for better readability */
        padding: 20px;
        font-size: 14px;
    }

    .card-body a {
        color: #ff4081; /* Vibrant pink for links */
    }

    .card-body a:hover {
        text-decoration: underline;
    }

    /* Answer Card Design */
    .answer-card {
        margin-left: 30px;
    }

    /* Text and Subtext Styling */
    .card-subtitle {
        color: #a0a0a0; /* Muted gray for subtitle */
        font-size: 14px;
    }

    /* Button Styles */
    .btn-success {
        background-color: #007bff;
        border-color: #007bff;
    }

    .btn-primary {
        background-color:rgb(205, 17, 17);
        border-color: #ff4081;
    }

    .btn-secondary {
        background-color: #333;
        border-color: #333;
    }

    /* Pagination Styles */
    .pagination .page-link {
        background-color: #222;
        color: #f5f5f5;
        border-color: #444;
    }

    .pagination .page-link:hover {
        background-color: #444;
        color: #fff;
    }

    .pagination .page-item.active .page-link {
        background-color: #ff4081;
        border-color: #ff4081;
    }

    /* Modal Customization */
    .modal-content {
        background-color: #222;
        color: #f5f5f5;
    }

    .modal-header {
        border-bottom: 1px solid #444;
    }

    .modal-footer {
        border-top: 1px solid #444;
    }

    .modal-header .btn-close {
        color: #f5f5f5;
    }

    /* Forum Button */
    .forum-create-btn {
        background-color: #007bff;
        color: #fff;
        border-radius: 5px;
        padding: 10px 20px;
        text-decoration: none;
        font-weight: bold;
    }

    .forum-create-btn:hover {
        background-color: #0056b3;
    }
  </style>
</head>
<body>

<?php 
include('nav.php');
require 'connection.php';

// Pagination logic
$posts_per_page = 2; 
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1; 
$offset = ($current_page - 1) * $posts_per_page; 

// Fetch forum posts with customer names and pagination
$stmt = $pdo->prepare("SELECT f.post_id, f.customer_id, f.title, f.category, f.content, f.created_at, c.first_name, c.last_name 
                       FROM forums f
                       JOIN customer c ON f.customer_id = c.customer_id
                       LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $posts_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$forums = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total number of posts for pagination
$total_posts_stmt = $pdo->query("SELECT COUNT(*) FROM `forums`");
$total_posts = $total_posts_stmt->fetchColumn();
$total_pages = ceil($total_posts / $posts_per_page); 
?>

<!-- Forum Section -->
<div class="container my-5">
    <h2>Forum Community - Dilan Computers</h2>

    <!-- Add "Create Forum" Button -->
    <div class="text-center mb-4">
        <a href="forum_add.php" class="forum-create-btn">Create a New Forum</a>
    </div>

    <!-- Loop through the forum posts and display them -->
    <?php foreach ($forums as $forum): ?>
        <div class="card forum-card">
            <div class="card-header">
                <h5 class="card-title">
                    <a href="forum_view.php?post_id=<?= $forum['post_id'] ?>" class="text-decoration-none text-white"><?= htmlspecialchars($forum['title']) ?></a>
                </h5>
                <p class="card-subtitle mb-2 text-white">Posted by <span class="text-white"><?= htmlspecialchars($forum['first_name']) . ' ' . htmlspecialchars($forum['last_name']) ?></span> | <?= htmlspecialchars($forum['category']) ?> | Posted on <?= $forum['created_at'] ?></p>
            </div>
            <div class="card-body">
                <p class="card-text"><?= nl2br(htmlspecialchars($forum['content'])) ?></p>
                
                <!-- Fetch and display answers for the forum post -->
                <?php
                $stmt_answers = $pdo->prepare("SELECT fa.answer_number, fa.post_id, fa.customer_id, fa.content, fa.created_at, c.first_name, c.last_name 
                                              FROM forum_answer fa
                                              JOIN customer c ON fa.customer_id = c.customer_id
                                              WHERE fa.post_id = ?");
                $stmt_answers->execute([$forum['post_id']]);
                $answers = $stmt_answers->fetchAll(PDO::FETCH_ASSOC);
                ?>

                <?php if (count($answers) > 0): ?>
                    <h5>Answers:</h5>
                    <?php foreach ($answers as $answer): ?>
                        <div class="card answer-card mt-4">
                            <div class="card-header">
                                <p class="card-subtitle mb-2 text-white">Answered by <span class="text-white"><?= htmlspecialchars($answer['first_name']) . ' ' . htmlspecialchars($answer['last_name']) ?></span> on <?= $answer['created_at'] ?></p>
                            </div>
                            <div class="card-body">
                                <p class="card-text"><?= nl2br(htmlspecialchars($answer['content'])) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No answers yet. Be the first to answer!</p>
                <?php endif; ?>

                <!-- Button to open modal and add an answer (only for logged-in users) -->
                <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#answerModal<?= $forum['post_id'] ?>">Add an Answer</button>

                <!-- Answer Modal -->
                <div class="modal fade" id="answerModal<?= $forum['post_id'] ?>" tabindex="-1" aria-labelledby="answerModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="text_model" id="answerModalLabel">Answer to: <?= htmlspecialchars($forum['title']) ?></h4>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form action="submit_answer.php" method="POST">
                                    <input type="hidden" name="post_id" value="<?= $forum['post_id'] ?>">
                                    <textarea name="content" class="form-control" rows="4" placeholder="Write your answer here..." required></textarea>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary">Submit Answer</button>
                            </div>
                        </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <!-- Pagination Controls -->
    <div class="d-flex justify-content-between mt-4">
        <div>
            <span class="text-white">Page <?= $current_page ?> of <?= $total_pages ?></span>
        </div>
        <div>
            <ul class="pagination">
                <?php if ($current_page > 1): ?>
                    <li class="page-item"><a class="page-link" href="?page=<?= $current_page - 1 ?>">Previous</a></li>
                <?php endif; ?>
                <?php if ($current_page < $total_pages): ?>
                    <li class="page-item"><a class="page-link" href="?page=<?= $current_page + 1 ?>">Next</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

