<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forum - View Post</title>
    <link rel="icon" type="image/png" href="images/logo.jpg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="styles.css">
</head>

<body>

    <?php 
    include('nav.php');
    require 'connection.php';

    // Get the post_id from the URL
    $post_id = isset($_GET['post_id']) ? (int)$_GET['post_id'] : 0;

    if ($post_id > 0) {
        // Fetch the forum post details and customer name of the person who posted the forum
        $stmt = $pdo->prepare("SELECT f.post_id, f.customer_id, f.title, f.category, f.content, f.created_at, c.first_name, c.last_name
                               FROM forums f
                               JOIN customer c ON f.customer_id = c.customer_id
                               WHERE f.post_id = ?");
        $stmt->execute([$post_id]);
        $forum = $stmt->fetch(PDO::FETCH_ASSOC);

        // Fetch answers for the forum post
        $stmt_answers = $pdo->prepare("SELECT `answer_number`, `post_id`, `customer_id`, `content`, `created_at` FROM `forum_answer` WHERE `post_id` = ?");
        $stmt_answers->execute([$post_id]);
        $answers = $stmt_answers->fetchAll(PDO::FETCH_ASSOC);
    } else {
        // Redirect to the forum page if no valid post_id
        header("Location: forums.php");
        exit;
    }
    ?>

    <!-- Forum Post Section -->
    <div class="container my-5" data-aos="fade-up">
        <h2 class="text-center mb-4 text-white"><?= htmlspecialchars($forum['title']) ?></h2>
        <!-- Display the customer name who posted the forum -->
        <p class="text-center text-white">Posted by <?= htmlspecialchars($forum['first_name']) ?> <?= htmlspecialchars($forum['last_name']) ?> | <?= htmlspecialchars($forum['category']) ?> | Posted on <?= $forum['created_at'] ?></p>
        <p class="lead text-white"><?= nl2br(htmlspecialchars($forum['content'])) ?></p>

        <hr>

        <!-- Answers Section -->
        <h4 class="mb-3 text-white">Answers:</h4>

        <?php foreach ($answers as $answer): ?>
            <!-- Fetch user details for profile icon and name -->
            <?php
            $stmt_user = $pdo->prepare("SELECT `first_name`, `last_name` FROM `customer` WHERE `customer_id` = ?");
            $stmt_user->execute([$answer['customer_id']]);
            $user = $stmt_user->fetch(PDO::FETCH_ASSOC);
            ?>
        
            <!-- Display Answer -->
            <div class="card mt-3">
                <div class="card-body d-flex align-items-start">
                    <!-- User Profile Icon -->
                    <img src="images/user.png" alt="User Profile" class="rounded-circle" width="50" height="50" style="object-fit: cover; margin-right: 15px;">

                    <!-- Answer Content -->
                    <div>
                        <p class="text-dark"><?= nl2br(htmlspecialchars($answer['content'])) ?></p>
                        <p class="text-muted">Answered by <?= htmlspecialchars($user['first_name']) ?> <?= htmlspecialchars($user['last_name']) ?> on <?= $answer['created_at'] ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- If no answers, prompt to add an answer -->
        <?php if (count($answers) === 0): ?>
            <p class="text-white">No answers yet. Be the first to answer!</p>
        <?php endif; ?>

        <!-- Button to open the modal for adding a new answer, only for logged-in users -->
        <?php if (isset($_SESSION['customer_id'])): ?>
            <button class="btn btn-danger mt-3" data-bs-toggle="modal" data-bs-target="#answerModal<?= $forum['post_id'] ?>">Add an Answer</button>
        <?php else: ?>
            <script>
                alert("You need to be logged in to add an answer.");
            </script>
        <?php endif; ?>

        <!-- Answer Modal (for submitting a new answer) -->
        <div class="modal fade" id="answerModal<?= $forum['post_id'] ?>" tabindex="-1" aria-labelledby="answerModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-white" id="answerModalLabel">Answer to: <?= htmlspecialchars($forum['title']) ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Answer Form -->
                        <form action="submit_answer.php" method="POST">
                            <input type="hidden" name="post_id" value="<?= $forum['post_id'] ?>">
                            <textarea name="content" class="form-control" rows="4" placeholder="Write your answer here..." required></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-danger">Submit Answer</button>
                    </div>
                        </form>
                </div>
            </div>
        </div>

    </div>

<!-- Bootstrap JS and Popper.js for modal functionality -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>

