<?php
session_start(); // Start the session to manage user data

// Include your database connection
include('connection.php');

// Check if the form is submitted via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    // Check if email exists in any of the user tables (customer, staff, admin)
    $queries = [
        'customer' => "SELECT `customer_id`, `email` FROM `customer` WHERE `email` = :email",
        'staff' => "SELECT `staff_id`, `email` FROM `staff` WHERE `email` = :email",
        'admin' => "SELECT `admin_id`, `email` FROM `admin` WHERE `email` = :email"
    ];

    $user_found = false;
    foreach ($queries as $role => $query) {
        $stmt = $pdo->prepare($query);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // If user is found, set flag and proceed with sending the reset link
            $user_found = true;
            break;
        }
    }

    // If email exists, generate a password reset link and send email (using PHPMailer or similar)
    if ($user_found) {
        // Generate a unique token for password reset
        $reset_token = bin2hex(random_bytes(16)); // Generate a secure token

        // Store the reset token in the database (add a column for reset_token in your user tables)
        $stmt = $pdo->prepare("UPDATE `{$role}` SET `reset_token` = :reset_token WHERE `email` = :email");
        $stmt->bindValue(':reset_token', $reset_token, PDO::PARAM_STR);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        // Generate password reset URL
        $reset_link = "http://localhost/dilan%20computers/reset_password.php?token=" . $reset_token;

        // Send the password reset email
        $subject = "Password Reset Request";
        $body = "Hello,\n\nWe received a request to reset your password. Click the link below to reset your password:\n\n$reset_link\n\nIf you did not request this, please ignore this email.\n\nBest regards,\nDilan Computers";
        $headers = 'From: yourGmailAddress@gmail.com' . "\r\n" .
                   'Reply-To: yourGmailAddress@gmail.com' . "\r\n" .
                   'X-Mailer: PHP/' . phpversion();

        // Send the email
        mail($email, $subject, $body, $headers);

        // Inform the user that the password reset email has been sent
        $message = "If the email address exists in our system, a password reset link has been sent to your email.";
    } else {
        // If email doesn't exist in the system
        $message = "The email address is not registered.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>

    <!-- Include Bootstrap CSS and icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body>

<!-- Include the navigation bar -->
<?php include('nav.php'); ?>

<!-- Forgot Password Form Section -->
<div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="card p-4" style="max-width: 400px; width: 100%;">
        <h2 class="text-center my-4">Forgot Password</h2>
        
        <!-- Display message to the user -->
        <?php if (isset($message)): ?>
            <div class="alert alert-info"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <!-- Forgot Password Form -->
        <form method="POST">
            <!-- Email Input -->
            <div class="form-group mt-4">
                <label for="email" class="text-white">Email Address</label>
                <input type="email" class="form-control mt-2" id="email" name="email" placeholder="Enter your registered email" required>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-primary w-100 mt-4">Submit</button>
        </form>

        <p class="text-center mt-3 text-black">Remember your password? <a href="login.php" class="text-black">Login</a></p>
        
    </div>
</div>

<!-- Include the footer -->
<?php include('footer.php'); ?>

</body>
</html>
