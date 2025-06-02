<?php 
    session_start(); 
    //store user id
if (isset($_SESSION['customer_id'])) {
    $customer_id = $_SESSION['customer_id'];
    $isLoggedIn = true;
} else {
    $customer_id = null;
    $isLoggedIn = false;
}?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us</title>
    <link rel="icon" type="image/png" href="images/logo.jpg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="styles.css">
</head>

<body class="bg-dark">

<!-- Navigation Bar -->
    <?php 
    include('nav.php'); // Including navigation bar from nav.php
    ?>

<!-- About Us Section -->
<section id="about-us" class="container py-5 text-white">
    <!-- Title of the About Us Section -->
    <h2 class="text-center mb-4 display-4">About Dilan Computers</h2>

    <!-- About the company -->
    <p class="lead">Dilan Computers, established in 2020, is a local computer shop located in Divulapitiya, Sri Lanka. Our mission is to provide high-quality computer hardware, accessories, and repair services to customers in the surrounding areas. Whether you're looking for a new laptop, desktop, or need help with computer repairs, we've got you covered!</p>

    <!-- Our Vision -->
    <h3 class="mt-4 text-warning">Our Vision</h3>
    <p>Our vision is to be the leading computer and technology solutions provider in Sri Lanka, known for our commitment to excellence, customer satisfaction, and innovative services. We aim to help individuals and businesses stay ahead in the ever-evolving world of technology.</p>

    <!-- Our Mission -->
    <h3 class="mt-4 text-warning">Our Mission</h3>
    <p>Our mission is to offer high-quality products and services that meet the diverse needs of our customers. We are committed to providing expert advice, timely repairs, and competitive prices. Our goal is to ensure every customer has the best possible technology solutions for their needs.</p>

    <!-- Customer-Focused Approach -->
    <h3 class="mt-4 text-warning">Customer-Centered Approach</h3>
    <p>At Dilan Computers, we always prioritize our customers' needs. We understand the vital role technology plays in daily life, and that's why we work hard to ensure that our customers always have access to the best products and services. Our website offers a seamless experience for browsing products, checking availability, and booking repair services online, making it easier for our customers to find solutions at their convenience.</p>
</section>

    <!-- Customer Testimonials Section -->
    <section id="testimonials" class="container py-5 bg-secondary text-white">
        <!-- Section Title -->
        <h3 class="text-center mb-4 display-4">What Our Customers Say</h3>

        <!-- Testimonial Cards (Each column represents a customer's review) -->
        <div class="row">
            <div class="col-md-4">
                <div class="card bg-light text-dark">
                    <div class="card-body">
                        <p class="card-text">"Dilan Computers helped me choose the perfect laptop for my business. The service was excellent, and the prices were unbeatable. Highly recommend!"</p>
                        <footer class="blockquote-footer">Samantha Perera</footer>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-light text-dark">
                    <div class="card-body">
                        <p class="card-text">"I had a problem with my desktop, and Dilan Computers fixed it quickly. The team was very professional, and the customer service was exceptional."</p>
                        <footer class="blockquote-footer">Kasun Silva</footer>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-light text-dark">
                    <div class="card-body">
                        <p class="card-text">"Fantastic store for all your computer needs. I bought my new PC here, and it’s been working flawlessly. Thank you for the great service!"</p>
                        <footer class="blockquote-footer">Ruwan Wickramasinghe</footer>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer Section -->
    <?php
    include("footer.php"); 
    ?>

</body>

</html>
