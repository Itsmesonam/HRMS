
<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . "/config/database/db.php";

/*-- Protect Admin Page --*/
if (
    !isset($_SESSION['admin_id']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'admin'
) {
    header("Location: adminlogin.php");
    exit();
}

/*-- Search and Filter --*/
$search = trim($_GET['search'] ?? '');
$roleFilter = strtolower(trim($_GET['role'] ?? ''));

$allowedRoles = ['tenant', 'landlord'];
if (!in_array($roleFilter, $allowedRoles, true)) {
    $roleFilter = '';
}

$sql = "SELECT id, firstname, lastname, email, role
        FROM users
        WHERE role IN ('tenant', 'landlord')";

$params = [];
$types = '';

if ($search !== '') {
    $sql .= " AND (
        firstname LIKE ?
        OR lastname LIKE ?
        OR email LIKE ?
        OR role LIKE ?
    )";

    $term = "%{$search}%";
    $params = [$term, $term, $term, $term];
    $types = 'ssss';
}

if ($roleFilter !== '') {
    $sql .= " AND role = ?";
    $params[] = $roleFilter;
    $types .= 's';
}

$sql .= " ORDER BY id DESC";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Unable to prepare user query.");
}

if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);
$users = mysqli_stmt_get_result($stmt);

/*-- Total User Counts --*/
$countQuery = mysqli_query(
    $conn,
    "SELECT
        COUNT(*) AS total,
        SUM(role = 'tenant') AS tenants,
        SUM(role = 'landlord') AS landlords
     FROM users
     WHERE role IN ('tenant', 'landlord')"
);

if (!$countQuery) {
    die("Unable to load user statistics.");
}

$counts = mysqli_fetch_assoc($countQuery);

$totalUsers = (int) ($counts['total'] ?? 0);
$totalTenants = (int) ($counts['tenants'] ?? 0);
$totalLandlords = (int) ($counts['landlords'] ?? 0);

$adminName = $_SESSION['admin_name'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>User Management | HRMS Admin</title>

    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined">

    <link rel="stylesheet" href="Assets/css/admin_users_style.css">
</head>
<body>

<div class="container">

    <!-- Sidebar: kept directly in this page -->
    <aside>
        <div class="top">
            <div class="logo">
                <h2><span class="logo-text">HRMS</span></h2>
            </div>
            <div class="close">
                <span class="material-symbols-outlined">close</span>
            </div>
        </div>

        <nav class="sidebar">
            <a href="admindashboard.php">
                <span class="material-symbols-outlined">dashboard</span>
                <h3>Dashboard</h3>
            </a>

            <a href="admin_houses.php">
                <span class="material-symbols-outlined">home</span>
                <h3>Houses</h3>
            </a>

            <a href="admin_landlords.php">
                <span class="material-symbols-outlined">real_estate_agent</span>
                <h3>Landlords</h3>
            </a>

            <a href="admin_tenants.php">
                <span class="material-symbols-outlined">group</span>
                <h3>Tenants</h3>
            </a>

            <a href="admin_bookings.php">
                <span class="material-symbols-outlined">calendar_month</span>
                <h3>Bookings</h3>
            </a>

            <a href="admin_payments.php">
                <span class="material-symbols-outlined">payments</span>
                <h3>Payments</h3>
            </a>

            <a href="admin_reports.php">
                <span class="material-symbols-outlined">bar_chart</span>
                <h3>Reports</h3>
            </a>

            <a href="admin_users.php" class="active">
                <span class="material-symbols-outlined">manage_accounts</span>
                <h3>Users</h3>
            </a>

            <a href="admin_messages.php">
                <span class="material-symbols-outlined">chat</span>
                <h3>Messages</h3>
            </a>

            <a href="admin_settings.php">
                <span class="material-symbols-outlined">settings</span>
                <h3>Settings</h3>
            </a>

            <a href="logout.php" class="logout">
                <span class="material-symbols-outlined">logout</span>
                <h3>Logout</h3>
            </a>
        </nav>
    </aside>

    <!-- Main Content -->
    <main>

        <div class="top-header">
            <div>
                <h1>User Management</h1>
                <p>Manage registered tenants and landlords.</p>
            </div>

            <div class="admin-profile">
                <span class="material-symbols-outlined">account_circle</span>
                <div>
                    <strong><?= htmlspecialchars($adminName) ?></strong>
                    <small>Administrator</small>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <section class="stats">
            <div class="stat-card">
                <span class="material-symbols-outlined">groups</span>
                <div>
                    <p>Total Users</p>
                    <h2><?= $totalUsers ?></h2>
                </div>
            </div>

            <div class="stat-card">
                <span class="material-symbols-outlined">person</span>
                <div>
                    <p>Tenants</p>
                    <h2><?= $totalTenants ?></h2>
                </div>
            </div>

            <div class="stat-card">
                <span class="material-symbols-outlined">real_estate_agent</span>
                <div>
                    <p>Landlords</p>
                    <h2><?= $totalLandlords ?></h2>
                </div>
            </div>
        </section>

        <!-- User Table -->
        <section class="panel">

            <div class="panel-header">
                <div>
                    <h2>Registered Users</h2>
                    <p>Search and filter registered accounts.</p>
                </div>
            </div>

            <form method="GET" action="admin_users.php" class="filter-form">
                <input
                    type="search"
                    name="search"
                    placeholder="Search name, email or role..."
                    value="<?= htmlspecialchars($search) ?>"
                >

                <select name="role">
                    <option value="">All account types</option>
                    <option value="tenant"
                        <?= $roleFilter === 'tenant' ? 'selected' : '' ?>>
                        Tenant
                    </option>
                    <option value="landlord"
                        <?= $roleFilter === 'landlord' ? 'selected' : '' ?>>
                        Landlord
                    </option>
                </select>

                <button type="submit">Search</button>
                <a class="reset-btn" href="admin_users.php">Reset</a>
            </form>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Account Type</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php if (mysqli_num_rows($users) > 0): ?>
                        <?php while ($user = mysqli_fetch_assoc($users)): ?>
                            <tr>
                                <td><?= (int) $user['id'] ?></td>

                                <td>
                                    <?= htmlspecialchars(
                                        trim(
                                            ($user['firstname'] ?? '') . ' ' .
                                            ($user['lastname'] ?? '')
                                        )
                                    ) ?>
                                </td>

                                <td><?= htmlspecialchars($user['email'] ?? '') ?></td>

                                <td>
                                    <span class="role-badge <?= strtolower($user['role']) ?>">
                                        <?= htmlspecialchars(ucfirst($user['role'])) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="empty-message">
                                No matching users found.
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <p class="table-note">
                Account information is read-only in this version.
            </p>
        </section>

    </main>
</div>

</body>
</html>
