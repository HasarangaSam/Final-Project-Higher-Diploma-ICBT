<?php 
include 'connection.php'; // Include database connection
session_start();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT); // Secure password hashing

    try {
        // Insert the admin into the database
        $stmt = $pdo->prepare("INSERT INTO admin (email, password) VALUES (:email, :password)");
        $stmt->execute([
            ':email' => $email,
            ':password' => $hashedPassword
        ]);

        $_SESSION['success_message'] = "Admin account created successfully!";
        header("Location: login.php"); // Redirect to login page
        exit();
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

<?php include('nav.php'); ?>

<div class="container d-flex justify-content-center align-items-center" style="min-height: 70vh;">
    <div class="card p-4" style="max-width: 400px; width: 100%;">
        <h2 class="text-center">Admin Registration</h2>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger"> <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?> </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group mt-4">
                <label for="email">Email Address</label>
                <input type="email" class="form-control mt-2" id="email" name="email" placeholder="Enter email" required>
            </div>
            
            <div class="form-group mt-4">
                <label for="password">Password</label>
                <input type="password" class="form-control mt-2" id="password" name="password" placeholder="Password" required>
            </div>
            
            <button type="submit" class="btn btn-primary w-100 mt-4">Register</button>
        </form>
        
        <p class="text-center mt-3">Already have an account? <a href="login.php">Login</a></p>
    </div>
</div>

<?php include('footer.php'); ?>

</body>
</html>
