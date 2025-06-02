<?php
session_start();
if (empty($_SESSION['customer_id'])) {
    header("Location: login.php"); // Redirect to login if the customer is not logged in
    exit;
}

if (isset($_SESSION['customer_id'])) {
    $isLoggedIn = true;
} else {
    $isLoggedIn = false;
}

// Include the database connection
require_once 'connection.php';

// Fetch customer details
$customer_id = $_SESSION['customer_id'];
$stmt = $pdo->prepare("SELECT first_name, last_name, email, phone FROM customer WHERE customer_id = ?");
$stmt->execute([$customer_id]);
$customer = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch loyalty points
$stmt = $pdo->prepare("SELECT points FROM loyalty WHERE customer_id = ?");
$stmt->execute([$customer_id]);
$loyalty_points = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch recent orders (10) along with the products in the order
$stmt = $pdo->prepare("SELECT o.order_id, o.order_date, o.total_amount, o.order_status, o.points_used, 
                              od.order_detail_number, od.product_id, od.quantity, p.name
                       FROM orders o
                       JOIN order_detail od ON o.order_id = od.order_id
                       JOIN products p ON od.product_id = p.product_id
                       WHERE o.customer_id = ? 
                       ORDER BY o.order_date DESC LIMIT 10");
$stmt->execute([$customer_id]);
$recent_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch customer queries
$stmt = $pdo->prepare("SELECT query_id, query, response, query_date FROM customer_queries WHERE customer_id = ?");
$stmt->execute([$customer_id]);
$customer_queries = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch notifications
$stmt = $pdo->prepare("SELECT n.id, n.message, n.created_at, p.name AS product_name
                       FROM notifications n
                       JOIN products p ON n.product_id = p.product_id
                       WHERE n.customer_id = ?
                       ORDER BY n.created_at DESC");
$stmt->execute([$customer_id]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account</title>
    <link rel="icon" type="image/png" href="images/logo.jpg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body>

    <?php include('nav.php'); ?>

    <!-- Account Details Section -->
    <div class="container mt-5">
        <h2 class="text-center text-white">My Account</h2>

        <!-- User Account Details -->
        <div class="card mb-4">
            <div class="card-header">Your Account Information</div>
            <div class="card-body">
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
                    <button type="submit" class="btn btn-danger w-100">Update Info</button>
                </form>
            </div>
        </div>

        <!-- Loyalty Points -->
        <div class="card mb-4">
            <div class="card-header">Loyalty Points</div>
            <div class="card-body">
                <p>You have <?php echo isset($loyalty_points['points']) ? htmlspecialchars($loyalty_points['points']) : '0'; ?> points.</p>
            </div>
        </div>

<!-- Recent Orders -->
<div class="card mb-4">
    <div class="card-header">Recent Orders</div>
    <div class="card-body">
        <?php if (count($recent_orders) > 0): ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Date</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th>Product(s)</th>
                        <th>Points Used</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // Group orders by order_id to combine product details in one row
                    $orders_by_id = [];
                    foreach ($recent_orders as $order) {
                        $orders_by_id[$order['order_id']][] = $order;
                    }

                    // Loop through each grouped order
                    foreach ($orders_by_id as $order_id => $order_details): ?>
                        <tr>
                            <!-- Display Order ID, Date, Total Amount, and Status in a single row -->
                            <td rowspan="<?= count($order_details) ?>" class="align-middle"><?= htmlspecialchars($order_id) ?></td>
                            <td rowspan="<?= count($order_details) ?>" class="align-middle"><?= htmlspecialchars($order_details[0]['order_date']) ?></td>
                            <td rowspan="<?= count($order_details) ?>" class="align-middle">Rs. <?= number_format($order_details[0]['total_amount'], 2) ?></td>
                            <td rowspan="<?= count($order_details) ?>" class="align-middle"><?= htmlspecialchars($order_details[0]['order_status']) ?></td>

                            <!-- Display Products in a single column (bullet points for multiple products) -->
                            <td><?php echo htmlspecialchars($order_details[0]['name']); ?> (x<?php echo $order_details[0]['quantity']; ?>)</td>
                            
                            <!-- Display Points Used for the entire order (fetched from orders table) -->
                            <td rowspan="<?= count($order_details) ?>" class="align-middle"><?= htmlspecialchars($order_details[0]['points_used']) ?></td>
                        </tr>

                        <!-- Additional product rows for the same order (if there are multiple products) -->
                        <?php for ($i = 1; $i < count($order_details); $i++): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($order_details[$i]['name']); ?> (x<?php echo $order_details[$i]['quantity']; ?>)</td>
                            </tr>
                        <?php endfor; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No recent orders found.</p>
        <?php endif; ?>
    </div>
</div>

        <!-- Customer Queries -->
        <div class="card mb-4">
            <div class="card-header">Your Queries</div>
            <div class="card-body">
                <?php if (count($customer_queries) > 0): ?>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Query</th>
                                <th>Response</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($customer_queries as $query): ?>
                                <tr>
                                    <td><?php echo nl2br(htmlspecialchars($query['query'])); ?></td>
                                    <td><?php echo nl2br(htmlspecialchars($query['response'])); ?></td>
                                    <td><?php echo htmlspecialchars($query['query_date']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No queries found.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Notifications -->
        <div class="card mb-4">
            <div class="card-header">Notifications</div>
            <div class="card-body">
                <?php if (count($notifications) > 0): ?>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Message</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($notifications as $notification): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($notification['product_name']); ?></td>
                                    <td><?php echo nl2br(htmlspecialchars($notification['message'])); ?></td>
                                    <td><?php echo htmlspecialchars($notification['created_at']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No notifications found.</p>
                <?php endif; ?>
            </div>
        </div>
   
        <!-- Forums Customer Posted -->
        <div class="card mb-4">
            <div class="card-header">Your Forums</div>
            <div class="card-body">
                <!-- Fetch and display the forums the customer posted -->
                <?php
                    $stmt_forums = $pdo->prepare("SELECT post_id, title FROM forums WHERE customer_id = ?");
                    $stmt_forums->execute([$customer_id]);
                    $customer_forums = $stmt_forums->fetchAll(PDO::FETCH_ASSOC);
                ?>
                
                <!-- Display Forums Posted (titles only) -->
                <h5>Forums You Posted:</h5>
                <?php if (count($customer_forums) > 0): ?>
                    <ul>
                        <?php foreach ($customer_forums as $forum): ?>
                            <li>
                                <a href="forum_view.php?post_id=<?php echo htmlspecialchars($forum['post_id']); ?>">
                                    <?php echo htmlspecialchars($forum['title']); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>You have not posted any forums yet.</p>
                <?php endif; ?>
            </div>
        </div>
 </div>
    <!-- Include Footer -->
    <?php include('footer.php'); ?>

    <!-- Bootstrap JS and Popper.js for modal functionality -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

