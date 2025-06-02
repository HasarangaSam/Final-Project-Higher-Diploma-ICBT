<?php
session_start();
// Check if user is logged in as admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Database Connection
include('../connection.php');

// Initialize variables
$title = $content = $img_src = '';
$title_error = $content_error = $image_error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form input values
    $title = $_POST['title'];
    $content = $_POST['content'];

    // Validate title and content
    if (empty($title)) {
        $title_error = "Title is required.";
    }

    if (empty($content)) {
        $content_error = "Content is required.";
    }

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        // Validate image file type (only allow image types)
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $image_type = $_FILES['image']['type'];
        if (in_array($image_type, $allowed_types)) {
            // Move the uploaded image to the 'uploads' folder
            $image_name = $_FILES['image']['name'];
            $image_tmp = $_FILES['image']['tmp_name'];
            $img_src = '../uploads/' . basename($image_name);
            move_uploaded_file($image_tmp, $img_src);
        } else {
            $image_error = "Invalid image format. Only JPG, PNG, and GIF are allowed.";
        }
    } else {
        // If no image uploaded, use a default image or leave it empty
        $img_src = 'admin/uploads/question.png'; // Set a default image path
    }

    // Insert the blog into the database if there are no errors
    if (empty($title_error) && empty($content_error) && empty($image_error)) {
        // Prepare SQL statement
        $stmt = $pdo->prepare("INSERT INTO blogs (admin_id, title, content, created_at, img_src) 
                               VALUES (:admin_id, :title, :content, NOW(), :img_src)");
        
        // Bind parameters
        $stmt->bindParam(':admin_id', $_SESSION['admin_id'], PDO::PARAM_INT);
        $stmt->bindParam(':title', $title, PDO::PARAM_STR);
        $stmt->bindParam(':content', $content, PDO::PARAM_STR);
        $stmt->bindParam(':img_src', $img_src, PDO::PARAM_STR);

        // Execute the query and check if it was successful
        if ($stmt->execute()) {
            echo "<script>alert('Blog added successfully!'); window.location.href='admin_manage_blog.php';</script>";
        } else {
            echo "<script>alert('Error adding blog.');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Blog</title>

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom Styles -->
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="wrapper">
    <!-- Sidebar -->
    <?php include('sidebar.php'); ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <h1 class="mt-4">Add New Blog</h1>

            <!-- Blog Form -->
            <form method="POST" enctype="multipart/form-data">
                <!-- Title -->
                <div class="mb-3">
                    <label for="title" class="form-label">Blog Title</label>
                    <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($title); ?>" required>
                    <small class="text-danger"><?php echo $title_error; ?></small>
                </div>

                <!-- Content -->
                <div class="mb-3">
                    <label for="content" class="form-label">Blog Content</label>
                    <textarea class="form-control" id="content" name="content" rows="5" required><?php echo htmlspecialchars($content); ?></textarea>
                    <small class="text-danger"><?php echo $content_error; ?></small>
                </div>

                <!-- Image -->
                <div class="mb-3">
                    <label for="image" class="form-label">Upload Image</label>
                    <input type="file" class="form-control" id="image" name="image">
                    <small class="text-danger"><?php echo $image_error; ?></small>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary">Add Blog</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
