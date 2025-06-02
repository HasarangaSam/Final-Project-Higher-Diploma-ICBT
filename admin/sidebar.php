<div class="sidebar">
    <h4 class="text-center text-white mt-3">Dilan Computers</h4>
    <ul class="nav flex-column">
        <li><a href="admin_manage_users.php" class="nav-link <?php if (basename($_SERVER['PHP_SELF']) == 'admin_manage_users.php') echo 'active'; ?>"><i class="bi bi-people"></i> Customer Management</a></li>
        <li><a href="admin_manage_staff.php" class="nav-link <?php if (basename($_SERVER['PHP_SELF']) == 'admin_manage_staff.php') echo 'active'; ?>"><i class="bi bi-person-badge"></i> Staff Management</a></li>
        <li><a href="admin_manage_products.php" class="nav-link <?php if (basename($_SERVER['PHP_SELF']) == 'admin_manage_products.php') echo 'active'; ?>"><i class="bi bi-box-seam"></i> Product Management</a></li>
        <li><a href="admin_manage_compatibility.php" class="nav-link <?php if (basename($_SERVER['PHP_SELF']) == 'admin_manage_compatibility.php') echo 'active'; ?>"><i class="bi bi-tools"></i> Manage Compatibility</a></li>
        <li><a href="admin_manage_blog.php" class="nav-link <?php if (basename($_SERVER['PHP_SELF']) == 'admin_manage_blog.php') echo 'active'; ?>"><i class="bi bi-pencil-square"></i> Blog Management</a></li>
        <li><a href="admin_manage_forum.php" class="nav-link <?php if (basename($_SERVER['PHP_SELF']) == 'admin_manage_forum.php') echo 'active'; ?>"><i class="bi bi-chat-text"></i> Forum Management</a></li>
        <li><a href="admin_order_summary.php" class="nav-link <?php if (basename($_SERVER['PHP_SELF']) == 'admin_order_summary.php') echo 'active'; ?>"><i class="bi bi-receipt"></i> Order Summary</a></li>
        <li><a href="admin_sales_summary.php" class="nav-link <?php if (basename($_SERVER['PHP_SELF']) == 'admin_sales_summary.php') echo 'active'; ?>"><i class="bi bi-graph-up"></i> Sales Summary</a></li>
        <li><a href="admin_quotation_summary.php" class="nav-link <?php if (basename($_SERVER['PHP_SELF']) == 'admin_quotation_summary.php') echo 'active'; ?>"><i class="bi bi-file-earmark-text"></i> Quotation Summary</a></li> 
        <li><a href="logout.php" class="nav-link text-danger"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
    </ul>
</div>

