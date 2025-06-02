<?php 
include 'connection.php'; // Include database connection
session_start();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $age = trim($_POST['age']);
    $gender = trim($_POST['gender']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT); // Secure password hashing

    try {
        // Insert the staff member into the database
        $stmt = $pdo->prepare("INSERT INTO staff (first_name, last_name, age, gender, email, phone, password, role) 
                               VALUES (:first_name, :last_name, :age, :gender, :email, :phone, :password, 'staff')");
        $stmt->execute([
            ':first_name' => $first_name,
            ':last_name' => $last_name,
            ':age' => $age,
            ':gender' => $gender,
            ':email' => $email,
            ':phone' => $phone,
            ':password' => $password
        ]);

        $_SESSION['success_message'] = "Staff account created successfully!";
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
    <title>Staff Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

<?php include('nav.php'); ?>

<div class="container d-flex justify-content-center align-items-center" style="min-height: 70vh;">
    <div class="card p-4" style="max-width: 400px; width: 100%;">
        <h2 class="text-center">Staff Registration</h2>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger"> <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?> </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group mt-4">
                <label for="first_name">First Name</label>
                <input type="text" class="form-control mt-2" id="first_name" name="first_name" placeholder="Enter First Name" required>
            </div>
            
            <div class="form-group mt-4">
                <label for="last_name">Last Name</label>
                <input type="text" class="form-control mt-2" id="last_name" name="last_name" placeholder="Enter Last Name" required>
            </div>
            
            <div class="form-group mt-4">
                <label for="age">Age</label>
                <input type="number" class="form-control mt-2" id="age" name="age" placeholder="Enter Age" required>
            </div>
            
            <div class="form-group mt-4">
                <label for="gender">Gender</label>
                <select class="form-control mt-2" id="gender" name="gender" required>
                    <option value="">Select Gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            
            <div class="form-group mt-4">
                <label for="email">Email Address</label>
                <input type="email" class="form-control mt-2" id="email" name="email" placeholder="Enter email" required>
            </div>
            
            <div class="form-group mt-4">
                <label for="phone">Phone Number</label>
                <input type="text" class="form-control mt-2" id="phone" name="phone" placeholder="Enter phone number" required>
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
