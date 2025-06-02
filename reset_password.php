<?php
session_start();
include('connection.php');

// Check if the token is provided in the URL
if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Check if the token exists in the database and is valid (not expired)
    $stmt = $pdo->prepare("SELECT * FROM customer WHERE reset_token = :reset_token");
    $stmt->bindValue(':reset_token', $token, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // If the token is valid, show the password reset form
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Get the new password from the form
            $new_password = password_hash($_POST['password'], PASSWORD_DEFAULT);

            // Update the user's password in the database and remove the reset token
            $update_stmt = $pdo->prepare("UPDATE customer SET password = :password, reset_token = NULL WHERE reset_token = :reset_token");
            $update_stmt->bindValue(':password', $new_password, PDO::PARAM_STR);
            $update_stmt->bindValue(':reset_token', $token, PDO::PARAM_STR);
            $update_stmt->execute();

            // Redirect the user to login page after successful password reset
            header("Location: login.php");
            exit();
        }
    } else {
        // If the token is invalid, show an error message
        $message = "Invalid or expired reset token.";
    }
} else {
    // If no token is provided, show an error message
    $message = "No reset token provided.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>

    <!-- Include Bootstrap CSS and icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body>

<div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="card p-4" style="max-width: 400px; width: 100%;">
        <h2 class="text-center my-4">Reset Password</h2>

        <!-- Display message if token is invalid or expired -->
        <?php if (isset($message)): ?>
            <div class="alert alert-danger"><?php echo $message; ?></div>
        <?php endif; ?>

        <!-- Reset Password Form -->
        <form method="POST">
            <!-- New Password Input -->
            <div class="form-group mt-4">
                <label for="password" class="text-white">New Password</label>
                <input type="password" class="form-control mt-2" id="password" name="password" placeholder="Enter your new password" required>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-primary w-100 mt-4">Reset Password</button>
        </form>
    </div>
</div>

</body>
</html>
