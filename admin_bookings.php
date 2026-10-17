<?php
session_start();

require_once __DIR__ . "/config/database/db.php";

/* Admin login check */
if (!isset($_SESSION['admin_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: adminlogin.php");
    exit();
}

$search = trim($_GET['search'] ?? '');
$status = trim($_GET['status'] ?? '');

/*
|--------------------------------------------------------------------------
| Booking query
|--------------------------------------------------------------------------
| We join:
| bookings -> properties
| bookings -> users (tenant)
|
| The booking table fields are based on your actual database structure.
*/

$sql = "
    SELECT
        b.booking_id,
        b.booking_date,
        b.move_in_date,
        b.duration,
        b.occupants,
        b.message,
        b.booking_status,
        b.created_at,

        p.property_name,
        p.location,

        u.firstname,
        u.lastname,
        u.email

    FROM bookings b

    LEFT JOIN properties p
        ON b.property_id = p.property_id

    LEFT JOIN users u
        ON b.tenant_id = u.id

    WHERE 1=1
";

$params = [];
$types = "";

/* Search */
if ($search !== '') {
    $sql .= "
        AND (
            p.property_name LIKE ?
            OR p.location LIKE ?
            OR u.firstname LIKE ?
            OR u.lastname LIKE ?
            OR u.email LIKE ?
        )
    ";

    $term = "%" . $search . "%";

    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;

    $types .= "sssss";
}

/* Status filter */
if ($status !== '') {
    $sql .= " AND b.booking_status = ?";
    $params[] = $status;
    $types .= "s";
}

$sql .= " ORDER BY b.booking_id DESC";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Unable to load bookings. Please check the bookings, properties and users tables.");
}

if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);

$bookings = mysqli_stmt_get_result($stmt);

/* Admin name */
$adminName = $_SESSION['admin_name'] ?? 'Admin';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Bookings | HRMS Admin</title>

    <!-- Google Material Icons -->
    <link
        rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined"
    >

    <!-- Page CSS -->
    <link rel="stylesheet" href="Assets/css/admin_bookings_style.css">
</head>

<body>

<div class="container">

    <!-- =====================================================
         SIDEBAR
    ====================================================== -->
    <aside>

        <div class="top">
            <h2 class="logo-text">HRMS</h2>
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

            <a href="admin_bookings.php" class="active">
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

            <a href="admin_users.php">
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


    <!-- =====================================================
         MAIN CONTENT
    ====================================================== -->
    <main>

        <!-- Page Header -->
        <header class="page-header">

            <div>
                <h1>Booking Management</h1>
                <p>Monitor rental booking requests made by tenants.</p>
            </div>

            <div class="profile">
                <?= htmlspecialchars($adminName) ?> · Administrator
            </div>

        </header>


        <!-- =================================================
             FILTER PANEL
        ================================================== -->
        <section class="panel">

            <form method="GET" class="filters">

                <input
                    type="search"
                    name="search"
                    value="<?= htmlspecialchars($search) ?>"
                    placeholder="Search property, tenant or email"
                >

                <select name="status">

                    <option value="">All Statuses</option>

                    <option value="Pending"
                        <?= $status === 'Pending' ? 'selected' : '' ?>>
                        Pending
                    </option>

                    <option value="Confirmed"
                        <?= $status === 'Confirmed' ? 'selected' : '' ?>>
                        Confirmed
                    </option>

                    <option value="Rejected"
                        <?= $status === 'Rejected' ? 'selected' : '' ?>>
                        Rejected
                    </option>

                    <option value="Cancelled"
                        <?= $status === 'Cancelled' ? 'selected' : '' ?>>
                        Cancelled
                    </option>

                </select>

                <button type="submit">
                    Search
                </button>

                <a href="admin_bookings.php" class="reset">
                    Reset
                </a>

            </form>


            <!-- =================================================
                 BOOKINGS TABLE
            ================================================== -->

            <div class="table-wrap">

                <table>

                    <thead>

                        <tr>
                            <th>ID</th>
                            <th>Tenant</th>
                            <th>Property</th>
                            <th>Booking Date</th>
                            <th>Move-in Date</th>
                            <th>Duration</th>
                            <th>Occupants</th>
                            <th>Status</th>
                            <th>Message</th>
                        </tr>

                    </thead>

                    <tbody>

                    <?php if (mysqli_num_rows($bookings) > 0): ?>

                        <?php while ($booking = mysqli_fetch_assoc($bookings)): ?>

                            <?php
                            $bookingStatus = $booking['booking_status'] ?? 'Pending';

                            $statusClass = strtolower($bookingStatus);

                            $tenantName = trim(
                                ($booking['firstname'] ?? '') . ' ' .
                                ($booking['lastname'] ?? '')
                            );

                            if ($tenantName === '') {
                                $tenantName = 'Unknown Tenant';
                            }
                            ?>

                            <tr>

                                <!-- Booking ID -->
                                <td>
                                    #<?= (int)$booking['booking_id'] ?>
                                </td>


                                <!-- Tenant -->
                                <td>

                                    <strong>
                                        <?= htmlspecialchars($tenantName) ?>
                                    </strong>

                                    <small>
                                        <?= htmlspecialchars($booking['email'] ?? '') ?>
                                    </small>

                                </td>


                                <!-- Property -->
                                <td>

                                    <strong>
                                        <?= htmlspecialchars($booking['property_name'] ?? 'Unknown Property') ?>
                                    </strong>

                                    <small>
                                        <?= htmlspecialchars($booking['location'] ?? '') ?>
                                    </small>

                                </td>


                                <!-- Booking Date -->
                                <td>
                                    <?= htmlspecialchars($booking['booking_date']) ?>
                                </td>


                                <!-- Move-in Date -->
                                <td>
                                    <?= htmlspecialchars($booking['move_in_date']) ?>
                                </td>


                                <!-- Duration -->
                                <td>
                                    <?= (int)$booking['duration'] ?> month(s)
                                </td>


                                <!-- Occupants -->
                                <td>
                                    <?= (int)$booking['occupants'] ?>
                                </td>


                                <!-- Status -->
                                <td>

                                    <span class="status <?= htmlspecialchars($statusClass) ?>">
                                        <?= htmlspecialchars($bookingStatus) ?>
                                    </span>

                                </td>


                                <!-- Message -->
                                <td>

                                    <?php if (!empty($booking['message'])): ?>

                                        <span
                                            class="message"
                                            title="<?= htmlspecialchars($booking['message']) ?>"
                                        >
                                            <?= htmlspecialchars($booking['message']) ?>
                                        </span>

                                    <?php else: ?>

                                        <span class="no-message">
                                            No message
                                        </span>

                                    <?php endif; ?>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>

                            <td colspan="9" class="empty">
                                No bookings found.
                            </td>

                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>


            <p class="note">
                Booking approval and rejection are handled by landlords.
                The administrator monitors booking activity through this page.
            </p>

        </section>

    </main>

</div>

</body>
</html>