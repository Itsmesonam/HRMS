
<?php
session_start();
require_once __DIR__ . "/config/database/db.php";

if (!isset($_SESSION['admin_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: adminlogin.php");
    exit();
}

$search = trim($_GET['search'] ?? '');
$sql = "SELECT property_id, property_name, property_type, location,
               monthly_rent, property_status
        FROM properties";
$params = [];
$types = '';

if ($search !== '') {
    $sql .= " WHERE property_name LIKE ? OR location LIKE ? OR property_type LIKE ?";
    $term = "%$search%";
    $params = [$term, $term, $term];
    $types = 'sss';
}
$sql .= " ORDER BY property_id DESC";

$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    die("Unable to prepare property query. Check your properties table.");
}
if ($params) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$houses = mysqli_stmt_get_result($stmt);
$adminName = $_SESSION['admin_name'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Houses | HRMS Admin</title>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined">
<link rel="stylesheet" href="Assets/css/admin_houses_style.css">
</head>
<body>
<div class="container">
<aside>
    <div class="top"><h2 class="logo-text">HRMS</h2></div>
    <nav class="sidebar">
        <a href="admindashboard.php"><span class="material-symbols-outlined">dashboard</span><h3>Dashboard</h3></a>
        <a href="admin_houses.php" class="active"><span class="material-symbols-outlined">home</span><h3>Houses</h3></a>
        <a href="admin_landlords.php"><span class="material-symbols-outlined">real_estate_agent</span><h3>Landlords</h3></a>
        <a href="admin_tenants.php"><span class="material-symbols-outlined">group</span><h3>Tenants</h3></a>
        <a href="admin_bookings.php"><span class="material-symbols-outlined">calendar_month</span><h3>Bookings</h3></a>
        <a href="admin_payments.php"><span class="material-symbols-outlined">payments</span><h3>Payments</h3></a>
        <a href="admin_reports.php"><span class="material-symbols-outlined">bar_chart</span><h3>Reports</h3></a>
        <a href="admin_users.php"><span class="material-symbols-outlined">manage_accounts</span><h3>Users</h3></a>
        <a href="admin_messages.php"><span class="material-symbols-outlined">chat</span><h3>Messages</h3></a>
        <a href="admin_settings.php"><span class="material-symbols-outlined">settings</span><h3>Settings</h3></a>
        <a href="logout.php" class="logout"><span class="material-symbols-outlined">logout</span><h3>Logout</h3></a>
    </nav>
</aside>
<main>
    <header class="page-header">
        <div><h1>House Management</h1><p>View properties listed on HOMY.</p></div>
        <div class="profile"><?= htmlspecialchars($adminName) ?> · Administrator</div>
    </header>

    <section class="panel">
        <form method="GET" class="filters">
            <input type="search" name="search" value="<?= htmlspecialchars($search) ?>"
                   placeholder="Search property, location or type">
            <button type="submit">Search</button>
            <a class="reset" href="admin_houses.php">Reset</a>
        </form>
        <div class="table-wrap">
        <table>
            <thead><tr><th>ID</th><th>Property</th><th>Type</th><th>Location</th><th>Monthly Rent</th><th>Status</th></tr></thead>
            <tbody>
            <?php if (mysqli_num_rows($houses)): ?>
                <?php while ($house = mysqli_fetch_assoc($houses)): ?>
                <tr>
                    <td><?= (int)$house['property_id'] ?></td>
                    <td><?= htmlspecialchars($house['property_name'] ?? '') ?></td>
                    <td><?= htmlspecialchars($house['property_type'] ?? '') ?></td>
                    <td><?= htmlspecialchars($house['location'] ?? '') ?></td>
                    <td>Rs. <?= number_format((float)$house['monthly_rent'], 2) ?></td>
                    <td><span class="status"><?= htmlspecialchars($house['property_status'] ?? 'Unknown') ?></span></td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6" class="empty">No properties found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
        </div>
        <p class="note">This page is read-only. Landlords remain responsible for managing their properties.</p>
    </section>
</main>
</div>
</body>
</html>
