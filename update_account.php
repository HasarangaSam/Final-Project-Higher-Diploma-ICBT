<?php
session_start();

// Ensure the user is logged in
if (empty($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit;
}

// Include the database connection
require_once 'connection.php';

// Fetch the current user details
$customer_id = $_SESSION['customer_id'];
$stmt = $pdo->prepare("SELECT first_name, last_name, email, phone FROM customer WHERE customer_id = ?");
$stmt->execute([$customer_id]);
$customer = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle form submission for updating the account
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_first_name = $_POST['first_name'];
    $new_last_name = $_POST['last_name'];
    $new_email = $_POST['email'];
    $new_phone = $_POST['phone'];

    // Update the customer details
    $stmt = $pdo->prepare("UPDATE customer SET first_name = ?, last_name = ?, email = ?, phone = ? WHERE customer_id = ?");
    $stmt->execute([$new_first_name, $new_last_name, $new_email, $new_phone, $customer_id]);

    // Redirect to the account page after successful update
    header("Location: my_account.php?message=Account updated successfully.");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Account</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body>

    <!-- Include the navigation bar -->
    <?php include('nav.php'); ?>

    <div class="container mt-5">
        <h2 class="text-center">Update Account Information</h2>

        <!-- Update Account Form -->
        <div class="card mb-4">
            <div class="card-header">Update Your Details</div>
            <div class="card-body">
                <!-- Display success message if any -->
                <?php if (isset($_GET['message'])): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($_GET['message']); ?></div>
                <?php endif; ?>

                <form action="update_account.php" method="POST">
                    <div class="mb-3">
                        <label for="first_name" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($customer['first_name']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="last_name" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($customer['last_name']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($customer['email']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($customer['phone']); ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Update Info</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Include the footer -->
    <?php include('footer.php'); ?>

</body>
</html>
