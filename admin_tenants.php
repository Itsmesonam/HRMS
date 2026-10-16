
<?php
session_start();
require_once __DIR__ . "/config/database/db.php";

if (!isset($_SESSION['admin_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: adminlogin.php");
    exit();
}

$search = trim($_GET['search'] ?? '');
$sql = "SELECT id, firstname, lastname, email, phone, address
        FROM users WHERE role = 'tenant'";
$params = [];
$types = '';

if ($search !== '') {
    $sql .= " AND (firstname LIKE ? OR lastname LIKE ? OR email LIKE ?)";
    $term = "%$search%";
    $params = [$term, $term, $term];
    $types = 'sss';
}
$sql .= " ORDER BY id DESC";

$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) die("Unable to prepare tenant query. Check your users table.");
if ($params) mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$tenants = mysqli_stmt_get_result($stmt);
$adminName = $_SESSION['admin_name'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Tenants | HRMS Admin</title>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined">
<link rel="stylesheet" href="Assets/css/admin_tenants_style.css">
</head>
<body>
<div class="container">
<aside>
    <div class="top"><h2 class="logo-text">HRMS</h2></div>
    <nav class="sidebar">
        <a href="admindashboard.php"><span class="material-symbols-outlined">dashboard</span><h3>Dashboard</h3></a>
        <a href="admin_houses.php"><span class="material-symbols-outlined">home</span><h3>Houses</h3></a>
        <a href="admin_landlords.php"><span class="material-symbols-outlined">real_estate_agent</span><h3>Landlords</h3></a>
        <a href="admin_tenants.php" class="active"><span class="material-symbols-outlined">group</span><h3>Tenants</h3></a>
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
        <div><h1>Tenant Management</h1><p>View registered tenants and their contact information.</p></div>
        <div class="profile"><?= htmlspecialchars($adminName) ?> · Administrator</div>
    </header>
    <section class="panel">
        <form method="GET" class="filters">
            <input type="search" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search tenant name or email">
            <button type="submit">Search</button>
            <a class="reset" href="admin_tenants.php">Reset</a>
        </form>
        <div class="table-wrap">
        <table>
            <thead><tr><th>ID</th><th>Tenant Name</th><th>Email</th><th>Phone</th><th>Address</th></tr></thead>
            <tbody>
            <?php if (mysqli_num_rows($tenants)): ?>
                <?php while ($person = mysqli_fetch_assoc($tenants)): ?>
                <tr>
                    <td><?= (int)$person['id'] ?></td>
                    <td><?= htmlspecialchars(trim(($person['firstname'] ?? '') . ' ' . ($person['lastname'] ?? ''))) ?></td>
                    <td><?= htmlspecialchars($person['email'] ?? '') ?></td>
                    <td><?= htmlspecialchars($person['phone'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($person['address'] ?? '—') ?></td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="5" class="empty">No tenants found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
        </div>
    </section>
</main>
</div>
</body>
</html>
