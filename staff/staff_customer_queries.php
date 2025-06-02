<?php
session_start();

// Check if user is logged in as staff
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../login.php"); // Redirect to login if not logged in as staff
    exit();
}

// Database Connection
include('connection.php');

// Fetch all customer queries
$stmt = $pdo->prepare("SELECT cq.query_id, cq.customer_id, cq.email, cq.phone, cq.query_date, cq.query, cq.response, c.first_name, c.last_name 
                       FROM customer_queries cq
                       JOIN customer c ON cq.customer_id = c.customer_id");
$stmt->execute();
$queries = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle response submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['query_id']) && isset($_POST['response'])) {
    $query_id = $_POST['query_id'];
    $response = $_POST['response'];
    $staff_id = $_SESSION['staff_id']; // Assuming staff_id is stored in session

    // Update the response in the database
    $update_stmt = $pdo->prepare("UPDATE customer_queries SET response = :response, staff_id = :staff_id WHERE query_id = :query_id");
    $update_stmt->bindParam(':query_id', $query_id, PDO::PARAM_INT);
    $update_stmt->bindParam(':response', $response, PDO::PARAM_STR);
    $update_stmt->bindParam(':staff_id', $staff_id, PDO::PARAM_INT);

    if ($update_stmt->execute()) {
        echo "<script>alert('Response added successfully!'); window.location.href='staff_customer_queries.php';</script>";
    } else {
        echo "<script>alert('Error updating response.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff - Customer Queries</title>

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom Styles -->
    <link rel="stylesheet" href="staff_style.css">
</head>
<body>

<div class="wrapper">
    <!-- Sidebar -->
    <?php include('staff_sidebar.php'); ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container mt-5">
            <h1>Customer Queries</h1>

            <!-- Queries Table -->
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Customer Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Query Date</th>
                        <th>Query</th>
                        <th>Response</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Loop through the customer queries and display them
                    foreach ($queries as $query) {
                        $customer_name = htmlspecialchars($query['first_name']) . ' ' . htmlspecialchars($query['last_name']);
                        $response = $query['response'] ? htmlspecialchars($query['response']) : 'No response yet';
                        echo "<tr>";
                        echo "<td>{$query['query_id']}</td>";
                        echo "<td>{$customer_name}</td>";
                        echo "<td>{$query['email']}</td>";
                        echo "<td>{$query['phone']}</td>";
                        echo "<td>{$query['query_date']}</td>";
                        echo "<td>" . htmlspecialchars($query['query']) . "</td>";
                        echo "<td>{$response}</td>";
                        echo "<td>
                                <a href='#' class='btn btn-info btn-sm' data-bs-toggle='modal' data-bs-target='#responseModal{$query['query_id']}'><i class='bi bi-pencil'></i> Respond</a>
                              </td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Response Modal -->
<?php foreach ($queries as $query): ?>
<div class="modal fade" id="responseModal<?php echo $query['query_id']; ?>" tabindex="-1" aria-labelledby="responseModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="responseModalLabel">Respond to Query</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form method="POST">
            <input type="hidden" name="query_id" value="<?php echo $query['query_id']; ?>">
            <div class="mb-3">
                <label for="response" class="form-label">Your Response</label>
                <textarea class="form-control" id="response" name="response" rows="5" required><?php echo htmlspecialchars($query['response']); ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Submit Response</button>
        </form>
      </div>
    </div>
  </div>
</div>
<?php endforeach; ?>

</body>
</html>
