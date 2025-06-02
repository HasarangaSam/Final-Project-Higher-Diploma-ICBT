<?php
session_start();
// Check if user is logged in as admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Database Connection
include('../connection.php');

// Fetch existing customer data for editing
if (isset($_GET['id'])) {
    $customer_id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM customer WHERE customer_id = :customer_id");
    $stmt->bindParam(':customer_id', $customer_id, PDO::PARAM_INT);
    $stmt->execute();
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$customer) {
        echo "<script>alert('Customer not found.'); window.location.href='admin_manage_users.php';</script>";
        exit();
    }
} else {
    echo "<script>alert('Invalid Customer ID.'); window.location.href='admin_manage_users.php';</script>";
    exit();
}

// Handling Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get data from the form
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $age = $_POST['age'];
    $gender = $_POST['gender'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = ($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : $customer['password']; // Only update password if provided

    // Prepare SQL query to update customer data
    $stmt = $pdo->prepare("UPDATE customer SET first_name = :first_name, last_name = :last_name, age = :age, gender = :gender, email = :email, phone = :phone, password = :password WHERE customer_id = :customer_id");
    $stmt->bindParam(':first_name', $first_name);
    $stmt->bindParam(':last_name', $last_name);
    $stmt->bindParam(':age', $age);
    $stmt->bindParam(':gender', $gender);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':password', $password);
    $stmt->bindParam(':customer_id', $customer_id, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        echo "<script>alert('Customer updated successfully!'); window.location.href='admin_manage_users.php';</script>";
    } else {
        echo "<script>alert('Error updating customer.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Customer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom Styles -->
    <link rel="stylesheet" href="style.css">

    <script>
        // JavaScript validation
        function validateForm() {
            var first_name = document.getElementById("first_name").value;
            var last_name = document.getElementById("last_name").value;
            var age = document.getElementById("age").value;
            var gender = document.getElementById("gender").value;
            var email = document.getElementById("email").value;
            var phone = document.getElementById("phone").value;
            var password = document.getElementById("password").value;
            var confirm_password = document.getElementById("confirm_password").value;

            // Name validation
            if (first_name == "" || last_name == "") {
                alert("Please fill in the first and last name.");
                return false;
            }

            // Age validation
            if (age == "" || isNaN(age)) {
                alert("Please enter a valid age.");
                return false;
            }

            // Gender validation
            if (gender == "") {
                alert("Please select a gender.");
                return false;
            }

            // Email validation
            var email_pattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
            if (!email_pattern.test(email)) {
                alert("Please enter a valid email address.");
                return false;
            }

            // Phone number validation (10 digits)
            var phone_pattern = /^[0-9]{10}$/;
            if (!phone_pattern.test(phone)) {
                alert("Please enter a valid 10-digit phone number.");
                return false;
            }

            // Password validation (only if password is provided)
            if (password != "" && password !== confirm_password) {
                alert("Passwords do not match.");
                return false;
            }

            return true;
        }
    </script>
</head>
<body>

<div class="wrapper">
    <!-- Sidebar -->
    <?php include('sidebar.php'); ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container mt-5">
            <h2>Edit Customer</h2>
            <form method="POST" onsubmit="return validateForm()">
                <!-- First Name -->
                <div class="mb-3">
                    <label for="first_name" class="form-label">First Name</label>
                    <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo $customer['first_name']; ?>" required>
                </div>

                <!-- Last Name -->
                <div class="mb-3">
                    <label for="last_name" class="form-label">Last Name</label>
                    <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo $customer['last_name']; ?>" required>
                </div>

                <!-- Age -->
                <div class="mb-3">
                    <label for="age" class="form-label">Age</label>
                    <input type="number" class="form-control" id="age" name="age" value="<?php echo $customer['age']; ?>" required>
                </div>

                <!-- Gender -->
                <div class="mb-3">
                    <label for="gender" class="form-label">Gender</label>
                    <select class="form-select" id="gender" name="gender" required>
                        <option value="">Select Gender</option>
                        <option value="Male" <?php if ($customer['gender'] == 'Male') echo 'selected'; ?>>Male</option>
                        <option value="Female" <?php if ($customer['gender'] == 'Female') echo 'selected'; ?>>Female</option>
                    </select>
                </div>

                <!-- Email -->
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo $customer['email']; ?>" required>
                </div>

                <!-- Phone -->
                <div class="mb-3">
                    <label for="phone" class="form-label">Phone Number</label>
                    <input type="text" class="form-control" id="phone" name="phone" value="<?php echo $customer['phone']; ?>" maxlength="10" required>
                </div>

                <!-- Password -->
                <div class="mb-3">
                    <label for="password" class="form-label">Password (Leave blank to keep current password)</label>
                    <input type="password" class="form-control" id="password" name="password">
                </div>

                <!-- Confirm Password -->
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" id="confirm_password">
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary">Update Customer</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
