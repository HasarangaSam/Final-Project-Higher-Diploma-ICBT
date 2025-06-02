<?php
session_start();
// Check if user is logged in as admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Database Connection
include('../connection.php');

// Fetch existing staff data for editing
if (isset($_GET['id'])) {
    $staff_id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM staff WHERE staff_id = :staff_id");
    $stmt->bindParam(':staff_id', $staff_id, PDO::PARAM_INT);
    $stmt->execute();
    $staff = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$staff) {
        echo "<script>alert('Staff member not found.'); window.location.href='admin_manage_staff.php';</script>";
        exit();
    }
} else {
    echo "<script>alert('Invalid Staff ID.'); window.location.href='admin_manage_staff.php';</script>";
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
    $role = $_POST['role'];

    // Only update password if a new password is provided
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    } else {
        $password = $staff['password']; // Keep existing password
    }

    // Prepare SQL query to update staff data
    $stmt = $pdo->prepare("UPDATE staff SET first_name = :first_name, last_name = :last_name, age = :age, gender = :gender, email = :email, phone = :phone, password = :password, role = :role WHERE staff_id = :staff_id");
    $stmt->bindParam(':first_name', $first_name);
    $stmt->bindParam(':last_name', $last_name);
    $stmt->bindParam(':age', $age);
    $stmt->bindParam(':gender', $gender);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':password', $password);
    $stmt->bindParam(':role', $role);
    $stmt->bindParam(':staff_id', $staff_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo "<script>alert('Staff member updated successfully!'); window.location.href='admin_manage_staff.php';</script>";
    } else {
        echo "<script>alert('Error updating staff member.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Staff</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom Styles -->
    <link rel="stylesheet" href="style.css">

    <script>
        function validateForm() {
            var first_name = document.getElementById("first_name").value;
            var last_name = document.getElementById("last_name").value;
            var age = document.getElementById("age").value;
            var gender = document.getElementById("gender").value;
            var email = document.getElementById("email").value;
            var phone = document.getElementById("phone").value;
            var password = document.getElementById("password").value;
            var confirm_password = document.getElementById("confirm_password").value;

            if (first_name == "" || last_name == "" || age == "" || gender == "" || email == "" || phone == "") {
                alert("All fields except password are required.");
                return false;
            }

            var phone_pattern = /^[0-9]{10}$/;
            if (!phone_pattern.test(phone)) {
                alert("Please enter a valid 10-digit phone number.");
                return false;
            }

            if (password !== "" && password !== confirm_password) {
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
            <h2>Edit Staff Member</h2>
            <form method="POST" onsubmit="return validateForm()">
                <div class="mb-3">
                    <label for="first_name" class="form-label">First Name</label>
                    <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo $staff['first_name']; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="last_name" class="form-label">Last Name</label>
                    <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo $staff['last_name']; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="age" class="form-label">Age</label>
                    <input type="number" class="form-control" id="age" name="age" value="<?php echo $staff['age']; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="gender" class="form-label">Gender</label>
                    <select class="form-select" id="gender" name="gender" required>
                        <option value="Male" <?php if ($staff['gender'] == 'Male') echo 'selected'; ?>>Male</option>
                        <option value="Female" <?php if ($staff['gender'] == 'Female') echo 'selected'; ?>>Female</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo $staff['email']; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="phone" class="form-label">Phone</label>
                    <input type="text" class="form-control" id="phone" name="phone" value="<?php echo $staff['phone']; ?>" maxlength="10" required>
                </div>
                <div class="mb-3">
                    <label for="role" class="form-label">Role</label>
                    <select class="form-select" id="role" name="role" required>
                        <option value="staff" <?php if ($staff['role'] == 'staff') echo 'selected'; ?>>Staff</option>
                        <option value="manager" <?php if ($staff['role'] == 'manager') echo 'selected'; ?>>Manager</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">New Password (Leave blank to keep current password)</label>
                    <input type="password" class="form-control" id="password" name="password">
                </div>
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" id="confirm_password">
                </div>
                <button type="submit" class="btn btn-primary">Update Staff</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
