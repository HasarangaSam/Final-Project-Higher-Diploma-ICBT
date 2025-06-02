<?php 
session_start(); // Start the session to manage user data

// Redirect to login page if the customer is not logged in but tries to submit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($_SESSION['customer_id'])) {
    echo "<script>alert('You must be logged in to submit a query.'); window.location.href='login.php';</script>";
    exit;
}

//store user logged_in
if (isset($_SESSION['customer_id'])) {
  $isLoggedIn = true;
} else {
  $isLoggedIn = false;
}


// Database Connection
include('connection.php');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect form data
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $message = $_POST['message'];
    
    // Get the logged-in customer's ID
    $customer_id = isset($_SESSION['customer_id']) ? $_SESSION['customer_id'] : null;

    // Insert the query into the customer_queries table with customer_id
    $stmt = $pdo->prepare("INSERT INTO customer_queries (customer_id, query, email, phone, query_date) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$customer_id, $message, $email, $phone]);

     // Success message via alert
     echo "<script>alert('Thank you for contacting us. We will get back to you shortly!');</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contact Us</title>
  <link rel="icon" type="image/png" href="images/logo.jpg">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="styles.css">

  <style>
    /* Custom Submit button styles */
    .submit-btn {
      background-color: #ff0000;
      color: white;
      font-size: 20px;
      transition: all 0.3s ease;
    }

    .submit-btn:hover {
      background-color: white;
      color: black;
    }
  </style>
</head>

<body class="bg-dark">

  <!-- Navigation Bar -->
  <?php 
    include('nav.php'); // Including navigation bar from nav.php
  ?>

  <!-- Contact Form Section -->
  <section id="contact-form" class="container py-5 text-white">
    <h2 class="text-center mb-4 display-4">Contact Us</h2>

    <!-- Contact Form -->
    <form method="POST">
      <div class="row">
        <div class="col-md-6">
          <div class="mb-3">
            <label for="name" class="form-label text-white">Name</label>
            <input type="text" class="form-control" name="name" id="name" required>
          </div>
        </div>
        <div class="col-md-6">
          <div class="mb-3">
            <label for="email" class="form-label text-white">Email</label>
            <input type="email" class="form-control" name="email" id="email" required>
          </div>
        </div>
      </div>
      <div class="mb-3">
        <label for="phone" class="form-label text-white">Phone Number</label>
        <input type="text" class="form-control" name="phone" id="phone" required>
      </div>
      <div class="mb-3">
        <label for="message" class="form-label text-white">Message</label>
        <textarea class="form-control" name="message" id="message" rows="4" required></textarea>
      </div>
      <button type="submit" class="btn btn-color-main submit-btn w-100">Submit</button>
    </form>
  </section>

  <!-- Google map Section -->
  <section id="google-map" class="container py-5">
    <h3 class="text-center text-white">Find Us Here</h3>
    <div class="embed-responsive embed-responsive-16by9">
      <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2353.527246293461!2d80.01739501538881!3d7.2242685235566935!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3ae2e3f26babce13%3A0x9bed6c6034b74de0!2sDilan%20Computers!5e0!3m2!1sen!2slk!4v1741190423402!5m2!1sen!2slk" 
        width="1300" 
        height="450" 
        style="border:0;" 
        allowfullscreen="" 
        loading="lazy" 
        referrerpolicy="no-referrer-when-downgrade">
      </iframe>
    </div>
  </section>


     <!-- Include Footer -->
     <?php include('footer.php'); ?>

<!-- Bootstrap JS and Popper.js for modal functionality -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>


</html>  

