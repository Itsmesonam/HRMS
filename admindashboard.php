php
<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . "/config/database/db.php";

/*-- Protect Admin Dashboard --*/
if (
    !isset($_SESSION['admin_id']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'admin'
) {
    header("Location: adminlogin.php");
    exit();
}

$adminName = $_SESSION['admin_name'] ?? 'Admin';

/*-- Dashboard Statistics --*/
$totalUsersQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users");
$totalTenantsQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE role = 'tenant'");
$totalLandlordsQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE role = 'landlord'");
$totalHousesQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM properties");
$availableHousesQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM properties WHERE property_status = 'Available'");
$totalBookingsQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM bookings");

$totalPaymentsQuery = mysqli_query(
    $conn,
    "SELECT COALESCE(SUM(amount), 0) AS total
     FROM payments
     WHERE payment_status = 'Completed'"
);

if (
    !$totalUsersQuery || !$totalTenantsQuery ||
    !$totalLandlordsQuery || !$totalHousesQuery ||
    !$availableHousesQuery || !$totalBookingsQuery ||
    !$totalPaymentsQuery
) {
    die("Dashboard query error: " . mysqli_error($conn));
}

$totalUsers = (int) mysqli_fetch_assoc($totalUsersQuery)['total'];
$totalTenants = (int) mysqli_fetch_assoc($totalTenantsQuery)['total'];
$totalLandlords = (int) mysqli_fetch_assoc($totalLandlordsQuery)['total'];
$totalHouses = (int) mysqli_fetch_assoc($totalHousesQuery)['total'];
$availableHouses = (int) mysqli_fetch_assoc($availableHousesQuery)['total'];
$totalBookings = (int) mysqli_fetch_assoc($totalBookingsQuery)['total'];
$totalPayments = (float) mysqli_fetch_assoc($totalPaymentsQuery)['total'];

/*-- Monthly Revenue for Bar Graph --*/
$monthlyRevenue = array_fill(1, 12, 0);

$revenueQuery = mysqli_query(
    $conn,
    "SELECT
        MONTH(payment_date) AS month_number,
        SUM(amount) AS total_revenue
     FROM payments
     WHERE payment_status = 'Completed'
       AND payment_date >= DATE_FORMAT(CURDATE(), '%Y-01-01')
       AND payment_date < DATE_FORMAT(CURDATE() + INTERVAL 1 YEAR, '%Y-01-01')
     GROUP BY MONTH(payment_date)
     ORDER BY MONTH(payment_date)"
);

if (!$revenueQuery) {
    die("Revenue query error: " . mysqli_error($conn));
}

while ($revenue = mysqli_fetch_assoc($revenueQuery)) {
    $monthlyRevenue[(int) $revenue['month_number']] =
        (float) $revenue['total_revenue'];
}

/*-- Recent Bookings --*/
$recentBookingsQuery = mysqli_query(
    $conn,
    "SELECT
        b.booking_id,
        b.booking_date,
        b.booking_status,
        u.firstname,
        u.lastname,
        p.property_name,
        p.monthly_rent
     FROM bookings b
     INNER JOIN users u ON b.tenant_id = u.id
     INNER JOIN properties p ON b.property_id = p.property_id
     ORDER BY b.booking_id DESC
     LIMIT 5"
);

if (!$recentBookingsQuery) {
    die("Recent bookings query error: " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>HRMS Admin Dashboard</title>

    <!-- Material Symbols -->
    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined">

    <!-- Admin Dashboard CSS -->
    <link rel="stylesheet" href="Assets/css/admindashboard_style.css">
</head>

<body>
<div class="container">

    <!-- Sidebar -->
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
            <a href="admindashboard.php" class="active">
                <span class="material-symbols-outlined">dashboard</span>
                <h3>Dashboard</h3>
            </a>

            <a href="#">
                <span class="material-symbols-outlined">home</span>
                <h3>Houses</h3>
            </a>

            <a href="#">
                <span class="material-symbols-outlined">real_estate_agent</span>
                <h3>Landlords</h3>
            </a>

            <a href="#">
                <span class="material-symbols-outlined">group</span>
                <h3>Tenants</h3>
            </a>

            <a href="#">
                <span class="material-symbols-outlined">calendar_month</span>
                <h3>Bookings</h3>
            </a>

            <a href="#">
                <span class="material-symbols-outlined">payments</span>
                <h3>Payments</h3>
            </a>

            <a href="#">
                <span class="material-symbols-outlined">bar_chart</span>
                <h3>Reports</h3>
            </a>

            <a href="admin_users.php">
                <span class="material-symbols-outlined">manage_accounts</span>
                <h3>Users</h3>
            </a>

            <a href="#">
                <span class="material-symbols-outlined">chat</span>
                <h3>Messages</h3>
            </a>

            <a href="#">
                <span class="material-symbols-outlined">settings</span>
                <h3>Settings</h3>
            </a>

            <a href="logout.php" class="logout">
                <span class="material-symbols-outlined">logout</span>
                <h3>Logout</h3>
            </a>
        </nav>
    </aside>

    <!-- Main Dashboard -->
    <main>

        <!-- Header -->
        <div class="top-header">
            <div>
                <h1>Dashboard</h1>
                <p>Welcome back, <?= htmlspecialchars($adminName) ?></p>
            </div>

            <div class="admin-profile">
                <span class="material-symbols-outlined">notifications</span>
                <div>
                    <strong><?= htmlspecialchars($adminName) ?></strong>
                    <small>Administrator</small>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="stats">

            <div class="card">
                <span class="material-symbols-outlined">home</span>
                <div>
                    <h3>Total Houses</h3>
                    <h2><?= $totalHouses ?></h2>
                    <p>All properties</p>
                </div>
            </div>

            <div class="card">
                <span class="material-symbols-outlined">home_work</span>
                <div>
                    <h3>Available Houses</h3>
                    <h2><?= $availableHouses ?></h2>
                    <p>Currently available</p>
                </div>
            </div>

            <div class="card">
                <span class="material-symbols-outlined">group</span>
                <div>
                    <h3>Total Tenants</h3>
                    <h2><?= $totalTenants ?></h2>
                    <p>Registered tenants</p>
                </div>
            </div>

            <div class="card">
                <span class="material-symbols-outlined">real_estate_agent</span>
                <div>
                    <h3>Total Landlords</h3>
                    <h2><?= $totalLandlords ?></h2>
                    <p>Registered landlords</p>
                </div>
            </div>

            <div class="card">
                <span class="material-symbols-outlined">calendar_month</span>
                <div>
                    <h3>Total Bookings</h3>
                    <h2><?= $totalBookings ?></h2>
                    <p>Rental bookings</p>
                </div>
            </div>

            <div class="card">
                <span class="material-symbols-outlined">payments</span>
                <div>
                    <h3>Total Payments</h3>
                    <h2>Rs. <?= number_format($totalPayments, 2) ?></h2>
                    <p>Completed payments</p>
                </div>
            </div>

        </div>

        <!-- Revenue Overview Bar Graph -->
        <div class="dashboard-content">
            <div class="panel revenue-panel">

                <div class="panel-header">
                    <div>
                        <h2>Revenue Overview</h2>
                        <p class="chart-subtitle">
                            Monthly completed payments this year
                        </p>
                    </div>

                    <span class="material-symbols-outlined revenue-icon">
                        bar_chart
                    </span>
                </div>

                <div class="revenue-chart-container">
                    <canvas id="revenueChart"></canvas>
                </div>

            </div>
        </div>

        <!-- Tables -->
        <div class="tables">

            <!-- Recent Bookings -->
            <div class="panel">
                <div class="panel-header">
                    <h2>Recent Bookings</h2>
                    <button type="button">View All</button>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>Tenant</th>
                            <th>House</th>
                            <th>Date</th>
                            <th>Rent</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php if (mysqli_num_rows($recentBookingsQuery) > 0): ?>
                        <?php while ($booking = mysqli_fetch_assoc($recentBookingsQuery)): ?>
                            <?php
                            $rawStatus = strtolower(
                                (string) $booking['booking_status']
                            );

                            if (in_array($rawStatus, ['confirmed', 'approved'], true)) {
                                $statusClass = 'approved';
                            } elseif ($rawStatus === 'rejected') {
                                $statusClass = 'rejected';
                            } else {
                                $statusClass = 'pending';
                            }
                            ?>

                            <tr>
                                <td>
                                    <?= htmlspecialchars(
                                        $booking['firstname'] . ' ' .
                                        $booking['lastname']
                                    ) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($booking['property_name']) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($booking['booking_date'] ?? 'N/A') ?>
                                </td>

                                <td>
                                    Rs. <?= number_format(
                                        (float) $booking['monthly_rent'],
                                        2
                                    ) ?>
                                </td>

                                <td>
                                    <span class="status <?= $statusClass ?>">
                                        <?= htmlspecialchars(ucfirst($rawStatus)) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No bookings found.</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Recent Payments -->
            <div class="panel">
                <div class="panel-header">
                    <h2>Recent Payments</h2>
                    <button type="button">View All</button>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>Tenant</th>
                            <th>House</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php
                    $recentPaymentsQuery = mysqli_query(
                        $conn,
                        "SELECT
                            u.firstname,
                            u.lastname,
                            p.property_name,
                            pay.amount,
                            pay.payment_date,
                            pay.payment_status
                         FROM payments pay
                         INNER JOIN users u ON pay.tenant_id = u.id
                         INNER JOIN properties p ON pay.property_id = p.property_id
                         ORDER BY pay.payment_id DESC
                         LIMIT 5"
                    );
                    ?>

                    <?php if ($recentPaymentsQuery && mysqli_num_rows($recentPaymentsQuery) > 0): ?>
                        <?php while ($payment = mysqli_fetch_assoc($recentPaymentsQuery)): ?>
                            <?php
                            $paymentStatus = strtolower(
                                (string) $payment['payment_status']
                            );

                            if ($paymentStatus === 'completed') {
                                $paymentClass = 'completed';
                            } elseif ($paymentStatus === 'failed') {
                                $paymentClass = 'failed';
                            } else {
                                $paymentClass = 'pending';
                            }
                            ?>

                            <tr>
                                <td>
                                    <?= htmlspecialchars(
                                        $payment['firstname'] . ' ' .
                                        $payment['lastname']
                                    ) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($payment['property_name']) ?>
                                </td>

                                <td>
                                    Rs. <?= number_format(
                                        (float) $payment['amount'],
                                        2
                                    ) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($payment['payment_date'] ?? 'N/A') ?>
                                </td>

                                <td>
                                    <span class="status <?= $paymentClass ?>">
                                        <?= htmlspecialchars(ucfirst($paymentStatus)) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No payments found.</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>

        <!-- Bottom Panels -->
        <div class="bottom-section">

            <div class="panel">
                <h2>House Status</h2>
                <p>Occupied: <strong>—</strong></p>
                <p>Vacant: <strong><?= $availableHouses ?></strong></p>
                <p>Maintenance: <strong>—</strong></p>
            </div>

            <div class="panel">
                <h2>Top Houses</h2>
                <p>Property booking statistics can be added here.</p>
                <p>Use real booking data for accurate results.</p>
            </div>

            <div class="panel">
                <h2>System Overview</h2>
                <p>Total Users: <strong><?= $totalUsers ?></strong></p>
                <p>Active Users: <strong><?= $totalUsers ?></strong></p>
                <p>System Status: <strong>Online</strong></p>
            </div>

        </div>

    </main>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const revenueCanvas = document.getElementById('revenueChart');

if (revenueCanvas && typeof Chart !== 'undefined') {
    new Chart(revenueCanvas, {
        type: 'bar',

        data: {
            labels: [
                'Jan', 'Feb', 'Mar', 'Apr',
                'May', 'Jun', 'Jul', 'Aug',
                'Sep', 'Oct', 'Nov', 'Dec'
            ],

            datasets: [{
                label: 'Revenue (Rs.)',
                data: <?= json_encode(array_values($monthlyRevenue)) ?>,
                backgroundColor: '#2e9d63',
                hoverBackgroundColor: '#23834f',
                borderRadius: 6,
                maxBarThickness: 42
            }]
        },

        options: {
            responsive: true,
            maintainAspectRatio: false,

            plugins: {
                legend: {
                    display: false
                },

                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Rs. ' +
                                Number(context.raw).toLocaleString('en-IN', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                });
                        }
                    }
                }
            },

            scales: {
                y: {
                    beginAtZero: true,

                    ticks: {
                        callback: function(value) {
                            return 'Rs. ' +
                                Number(value).toLocaleString('en-IN');
                        }
                    },

                    grid: {
                        color: '#edf0f4'
                    }
                },

                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
}
</script>

</body>
</html>
