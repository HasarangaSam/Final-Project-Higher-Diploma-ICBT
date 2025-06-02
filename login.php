<?php
session_start(); // Start the session to manage user data

// Include your database connection
include('connection.php');

// Check if the form is submitted via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prepare the SQL queries to check for matching credentials in each table (customer, staff, admin)
    $queries = [
        'customer' => "SELECT `customer_id`, `email`, `password` FROM `customer` WHERE `email` = :email",
        'staff' => "SELECT `staff_id`, `email`, `password` FROM `staff` WHERE `email` = :email",
        'admin' => "SELECT `admin_id`, `email`, `password` FROM `admin` WHERE `email` = :email"
    ];

    foreach ($queries as $role => $query) {
        // Prepare and execute query for each role (customer, staff, admin)
        $stmt = $pdo->prepare($query);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // If credentials are correct, store user_id and role in the session
            $_SESSION['role'] = $role; // Store the role (customer, staff, admin)
            
            // Store user_id for the respective role
            if ($role === 'customer') {
                $_SESSION['customer_id'] = $user['customer_id']; // Store customer ID
            } elseif ($role === 'staff') {
                $_SESSION['staff_id'] = $user['staff_id']; // Store staff ID
            } elseif ($role === 'admin') {
                $_SESSION['admin_id'] = $user['admin_id']; // Store admin ID
            }

            // Redirect based on the role
              // Show success alert and redirect using JavaScript
            echo "<script>
            alert('Login Successful!');
            window.location.href = '" . ($role === 'customer' ? 'home.php' : ($role === 'staff' ? 'staff/staff_manage_customers.php' : 'admin/admin_manage_users.php')) . "';
            </script>";
            exit();
        }
    }

    // If login fails
    $login_error = "Invalid email or password!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="icon" type="image/png" href="images/logo.jpg">
    <!-- Include Bootstrap CSS and icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body>

<!-- Include the navigation bar -->
<?php include('nav.php'); ?>

<!-- Login Form Section -->
<div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="card p-4" style="max-width: 400px; width: 100%;">
        <h2 class="text-center my-4">Login</h2>
        
        <!-- Display error message if login fails -->
        <?php if (isset($login_error)): ?>
            <div class="alert alert-danger"><?php echo $login_error; ?></div>
        <?php endif; ?>
        
        <!-- Login Form -->
        <form method="POST">
            <!-- Email Input -->
            <div class="form-group mt-4">
                <label for="email" >Email Address</label>
                <input type="email" class="form-control mt-2" id="email" name="email" placeholder="Enter email" required>
            </div>

            <!-- Password Input -->
            <div class="form-group mt-4">
                <label for="password">Password</label>
                <input type="password" class="form-control mt-2" id="password" name="password" placeholder="Password" required>
            </div>

                    <!-- Forgot Password Link -->
        <div class="text-center mt-3">
            <a href="forgot_password.php" class="text-black">Forgot password?</a>
        </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-danger w-100 mt-4">Login</button>
        </form>

        <p class="text-center mt-3 text-black">Don't have an account? <a href="signup.php" class="text-black">Sign Up</a></p>
        
    </div>
</div>


<!-- Include the footer -->
<?php include('footer.php'); ?>

</body>
</html>

