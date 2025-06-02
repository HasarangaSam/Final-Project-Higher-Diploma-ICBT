<?php 
include 'connection.php'; // Include your database connection file

// Check if the form is submitted via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $age = $_POST['age'];
    $gender = $_POST['gender'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash password

    try {
        // Prepare SQL query to insert the new customer into the database
        $stmt = $pdo->prepare("INSERT INTO customer (first_name, last_name, age, gender, email, phone, password, role) 
                               VALUES (:first_name, :last_name, :age, :gender, :email, :phone, :password, 'customer')");
        // Execute the query with the provided form data
        $stmt->execute([
            ':first_name' => $first_name,
            ':last_name' => $last_name,
            ':age' => $age,
            ':gender' => $gender,
            ':email' => $email,
            ':phone' => $phone,
            ':password' => $password
        ]);

        // Redirect to the login page after successful registration
        header("Location: login.php");
        exit();
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage()); // Handle any errors
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <!-- Include Bootstrap 5 and custom CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="styles.css"> <!-- Custom Styles -->
    <style>
        .card {
            background: #fff;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 500px;
        }

        .card h2 {
            color: #ff4b2b;
            font-weight: bold;
        }

        .form-control {
            border-radius: 8px;
            border: 1px solid #ddd;
            transition: 0.3s;
        }

        .form-control:focus {
            border-color: #ff4b2b;
            box-shadow: 0 0 5px rgba(255, 75, 43, 0.5);
        }

        .btn-danger {
            background:rgb(186, 33, 33);
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            transition: 0.3s;
        }

        .btn-danger:hover {
            background: #e13c20;
        }

        .text-white a {
            text-decoration: none;
            font-weight: bold;
            transition: 0.3s;
        }

        .text-white a:hover {
            text-decoration: underline;
            color: #ffc107;
        }
    </style>
    </style>
</head>
<body>

<!-- Navigation Bar -->
<?php include('nav.php'); ?>

<!-- Sign Up Form Section -->
<div class="container d-flex justify-content-center align-items-center" style="min-height: 70vh;">
    <div class="card">
        <h2 class="text-center my-4">Create an Account</h2>
        <form method="POST" onsubmit="return validateForm();">
            <!-- First Name and Last Name Input -->
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mt-4">
                        <label for="first_name">First Name</label>
                        <input type="text" class="form-control mt-2" id="first_name" name="first_name" placeholder="Enter First Name">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mt-4">
                        <label for="last_name" class="text-white">Last Name</label>
                        <input type="text" class="form-control mt-2" id="last_name" name="last_name" placeholder="Enter Last Name">
                    </div>
                </div>
            </div>

            <!-- Age Input -->
            <div class="form-group mt-4">
                <label for="age">Age</label>
                <input type="number" class="form-control mt-2" id="age" name="age" placeholder="Enter Your Age">
            </div>

            <!-- Gender Dropdown -->
            <div class="form-group mt-4">
                <label for="gender">Gender</label>
                <select class="form-control mt-2" id="gender" name="gender">
                    <option value="">Select Gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select>
            </div>

            <!-- Email Input -->
            <div class="form-group mt-4">
                <label for="email">Email Address</label>
                <input type="email" class="form-control mt-2" id="email" name="email" placeholder="Enter email">
            </div>

            <!-- Phone Number Input -->
            <div class="form-group mt-4">
                <label for="phone">Phone Number</label>
                <input type="text" class="form-control mt-2" id="phone" name="phone" placeholder="Enter phone number">
            </div>

            <!-- Password Input -->
            <div class="form-group mt-4">
                <label for="password">Password</label>
                <input type="password" class="form-control mt-2" id="password" name="password" placeholder="Password">
            </div>

            <!-- Confirm Password Input -->
            <div class="form-group mt-4">
                <label for="confirmPassword" >Confirm Password</label>
                <input type="password" class="form-control mt-2" id="confirmPassword" placeholder="Confirm Password">
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-danger w-100 mt-4">Sign Up</button>
        </form>
        <p class="text-center mt-3">Already have an account? <a href="login.php">Login</a></p>
    </div>
</div>

<br>

<script>
    // Function to validate the form data before submission
    function validateForm() {
        let firstName = document.getElementById("first_name").value.trim();
        let lastName = document.getElementById("last_name").value.trim();
        let age = document.getElementById("age").value.trim();
        let gender = document.getElementById("gender").value;
        let email = document.getElementById("email").value.trim();
        let phone = document.getElementById("phone").value.trim();
        let password = document.getElementById("password").value;
        let confirmPassword = document.getElementById("confirmPassword").value;

        let emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;
        let phonePattern = /^[0-9]{10}$/;

        // Check if any field is empty
        if (firstName === "" || lastName === "" || age === "" || gender === "" || email === "" || phone === "" || password === "" || confirmPassword === "") {
            alert("All fields are required.");
            return false;
        }

        // Validate age
        if (isNaN(age) || age < 18) {
            alert("Age must be a number and at least 18.");
            return false;
        }

        // Validate email format
        if (!emailPattern.test(email)) {
            alert("Enter a valid email.");
            return false;
        }

        // Validate phone number format
        if (!phonePattern.test(phone)) {
            alert("Phone number must be 10 digits.");
            return false;
        }

        // Check if password and confirm password match
        if (password !== confirmPassword) {
            alert("Passwords do not match.");
            return false;
        }
        return true;
    }
</script>

<!-- Footer -->
<?php include('footer.php'); ?>

</body>
</html>

