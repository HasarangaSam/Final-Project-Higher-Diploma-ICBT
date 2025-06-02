<?php
session_start();

include('connection.php');

if (isset($_SESSION['customer_id'])) {
    $isLoggedIn = true;
} else {
    $isLoggedIn = false;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Wishlist</title> 
  <link rel="icon" type="image/png" href="images/logo.jpg">
      <!-- Linking Bootstrap CSS from CDN (Bootstrap 5) -->
      <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Linking Bootstrap Icons (for any icon usage) -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

<!-- Linking custom stylesheets for additional styling -->
<link rel="stylesheet" href="styles.css">
</head>
<body>

 
<!-- Navigation Bar -->
<?php 
include('nav.php');
?>
  <section id="products" class="container py-5">


  <div class="container mt-5">
    <h1 class="text-center text-white">Your Wishlist</h1>
    
    <!-- Table to display the wishlist products -->
    <div id="wishlist-products">
        <!-- Product details will be populated here by JavaScript -->
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>

<script>
 $(document).ready(function() {
    // Fetch wishlist from sessionStorage when the page loads
    let wishlist = JSON.parse(sessionStorage.getItem('wishlist')) || [];

    // Check if wishlist is empty and show a message
    if (wishlist.length === 0) {
        $('#wishlist-products').html('<p class="text-white">No products in your wishlist.</p>');
        return;
    }
    // Log the wishlist to check its contents
    console.log('Wishlist:', wishlist); // Debugging line

    // Send the wishlist to the server to fetch product details
    $.ajax({
        url: 'wishlist-api.php', // The same PHP file to process the wishlist
        type: 'POST',
        data: { wishlist: JSON.stringify(wishlist) }, // Sending data as a JSON string
        success: function(response) {
            // Log the response to check its structure
            console.log('Response:', response);

            // If the response contains an error, log and show the error message
            if (response.error) {
                console.error("Error fetching wishlist products: " + response.error);
                $('#wishlist-products').html('<p>Error loading wishlist products.</p>');
            } else {
                // Create the HTML table for displaying products
                let productTable = '<table class="table table-bordered table-striped">';
                productTable += '<thead class="thead-dark">';
                productTable += '<tr><th>Product ID</th><th>Name</th><th>Category</th><th>Price</th><th>Availability</th><th>Image</th></tr>';
                productTable += '</thead><tbody>';

                // Ensure that response is an array and loop through each product
                if (Array.isArray(response)) {
                    $.each(response, function(index, product) {
                        var imagePath = product.image_url;

                        // Check if the image is a relative path (starts with "../")
                        if (imagePath.startsWith('../')) {
                            // Remove "../" and adjust the path
                            imagePath = imagePath.substring(3); // Remove the first 3 characters (../)
                        }

                        // Append the table row
                        productTable += '<tr>';
                        productTable += '<td>' + product.product_id + '</td>';
                        productTable += '<td>' + product.name + '</td>';
                        productTable += '<td>' + product.category + '</td>';
                        productTable += '<td>' + product.new_price + '</td>';
                        productTable += '<td>' + (product.new_availability ? 'In Stock' : 'Out of Stock') + '</td>';
                        productTable += '<td><img src="' + imagePath + '" alt="Product Image" width="100"></td>';
                        productTable += '</tr>';
                    });
                } else {
                    // Handle case where response is not an array (fallback)
                    $('#wishlist-products').html('<p>Unexpected data format received.</p>');
                    console.error('Expected array, but received:', response);
                    return;
                }

                productTable += '</tbody></table>';

                // Append the table to the wishlist div
                $('#wishlist-products').html(productTable);
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            // Log AJAX errors to the console
            console.error("AJAX request failed: " + textStatus + ", " + errorThrown);
            $('#wishlist-products').html('<p>Error loading wishlist products.</p>');
        }
    });
});

</script>
   
</div>  
</div>
    </section>


<script src="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.js"></script>
 
</body>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>

</html>