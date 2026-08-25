<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

include "config/database/db.php";

/* Total Users */
$totalUsersQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users");

if (!$totalUsersQuery) {
    die("Total Users Query Error: " . mysqli_error($conn));
}

$totalUsers = mysqli_fetch_assoc($totalUsersQuery)['total'];


/* Total Tenants */
$tenantQuery = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM users WHERE role = 'tenant'"
);

if (!$tenantQuery) {
    die("Tenant Query Error: " . mysqli_error($conn));
}

$totalTenants = mysqli_fetch_assoc($tenantQuery)['total'];


/* Total Landlords */
$landlordQuery = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM users WHERE role = 'landlord'"
);

if (!$landlordQuery) {
    die("Landlord Query Error: " . mysqli_error($conn));
}

$totalLandlords = mysqli_fetch_assoc($landlordQuery)['total'];

?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>HRMS Admin Dashboard</title>

    <!-- Google Material Symbols -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200">

    <!-- Admin Dashboard CSS -->
    <link rel="stylesheet" href="/HRMS/Assets/css/admindashboard.css">
</head>

<body>

<div class="container">

    <!-- Sidebar -->

    <aside>

        <div class="top">

            <div class="logo">
                <h2>
                    <span class="logo-text">HRMS</span>
                </h2>
            </div>

            <div class="close">
                <span class="material-symbols-outlined">
                    close
                </span>
            </div>

        </div>


        <div class="sidebar">

            <a href="admindashboard.php" class="active">

                <span class="material-symbols-outlined">
                    dashboard
                </span>

                <h3>Dashboard</h3>

            </a>


            <a href="#">

                <span class="material-symbols-outlined">
                    home
                </span>

                <h3>Houses</h3>

            </a>


            <a href="#">

                <span class="material-symbols-outlined">
                    real_estate_agent
                </span>

                <h3>Landlords</h3>

            </a>


            <a href="#">

                <span class="material-symbols-outlined">
                    group
                </span>

                <h3>Tenants</h3>

            </a>


            <a href="#">

                <span class="material-symbols-outlined">
                    calendar_month
                </span>

                <h3>Bookings</h3>

            </a>


            <a href="#">

                <span class="material-symbols-outlined">
                    payments
                </span>

                <h3>Payments</h3>

            </a>


            <a href="#">

                <span class="material-symbols-outlined">
                    bar_chart
                </span>

                <h3>Reports</h3>

            </a>


            <a href="#">

                <span class="material-symbols-outlined">
                    manage_accounts
                </span>

                <h3>Users</h3>

            </a>


            <a href="#">

                <span class="material-symbols-outlined">
                    chat
                </span>

                <h3>Messages</h3>

            </a>


            <a href="#">

                <span class="material-symbols-outlined">
                    settings
                </span>

                <h3>Settings</h3>

            </a>


            <a href="logout.php" class="logout">

                <span class="material-symbols-outlined">
                    logout
                </span>

                <h3>Logout</h3>

            </a>

        </div>

    </aside>


    <!-- Main -->

    <main>

        <!-- Header -->

        <div class="top-header">

            <div>

                <h1>Dashboard</h1>

                <p>Welcome back, Admin</p>

            </div>


            <div class="admin-profile">

                <span class="material-symbols-outlined">
                    notifications
                </span>

                <div>

                    <strong>Admin</strong>

                    <small>Administrator</small>

                </div>

            </div>

        </div>


        <!-- Statistics -->

        <div class="stats">


            <div class="card">

                <span class="material-symbols-outlined">
                    home
                </span>

                <div>

                    <h3>Total Houses</h3>

                    <h2>125</h2>

                    <p>Available properties</p>

                </div>

            </div>


            <div class="card">

                <span class="material-symbols-outlined">
                    home_work
                </span>

                <div>

                    <h3>Available Houses</h3>

                    <h2>58</h2>

                    <p>Currently available</p>

                </div>

            </div>


            <div class="card">

                <span class="material-symbols-outlined">
                    group
                </span>

                <div>

                    <h3>Total Tenants</h3>

                    <h2><?php echo $totalTenants; ?></h2>

                    <p>Registered tenants</p>

                </div>

            </div>


            <div class="card">

                <span class="material-symbols-outlined">
                    real_estate_agent
                </span>

                <div>

                    <h3>Total Landlords</h3>

                    <h2><?php echo $totalLandlords; ?></h2>

                    <p>Registered landlords</p>

                </div>

            </div>


            <div class="card">

                <span class="material-symbols-outlined">
                    calendar_month
                </span>

                <div>

                    <h3>Total Bookings</h3>

                    <h2>146</h2>

                    <p>Rental bookings</p>

                </div>

            </div>


            <div class="card">

                <span class="material-symbols-outlined">
                    payments
                </span>

                <div>

                    <h3>Total Payments</h3>

                    <h2>Rs. 1,245,300</h2>

                    <p>Total rent collected</p>

                </div>

            </div>

        </div>


        <!-- Content -->

        <div class="dashboard-content">


            <!-- Revenue -->

            <div class="panel revenue">

                <div class="panel-header">

                    <h2>Revenue Overview</h2>

                    <select>

                        <option>This Year</option>

                        <option>Last Year</option>

                    </select>

                </div>

                <div class="chart-placeholder">

                    Revenue Chart

                </div>

            </div>


            <!-- Booking -->

            <div class="panel booking">

                <div class="panel-header">

                    <h2>Booking Overview</h2>

                    <select>

                        <option>This Year</option>

                        <option>Last Year</option>

                    </select>

                </div>


                <div class="circle-chart">

                    <div>

                        146

                        <small>Bookings</small>

                    </div>

                </div>

            </div>

        </div>


        <!-- Tables -->

        <div class="tables">


            <!-- Recent Bookings -->

            <div class="panel">

                <div class="panel-header">

                    <h2>Recent Bookings</h2>

                    <button>View All</button>

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

                        <tr>

                            <td>Rohan Shrestha</td>
                            <td>Sunshine Apartment</td>
                            <td>20 Aug</td>
                            <td>Rs. 25,000</td>

                            <td>
                                <span class="status approved">
                                    Approved
                                </span>
                            </td>

                        </tr>


                        <tr>

                            <td>Sita Thapa</td>
                            <td>Green Valley House</td>
                            <td>19 Aug</td>
                            <td>Rs. 18,000</td>

                            <td>
                                <span class="status pending">
                                    Pending
                                </span>
                            </td>

                        </tr>


                        <tr>

                            <td>Nabin Karki</td>
                            <td>Modern Family Home</td>
                            <td>18 Aug</td>
                            <td>Rs. 30,000</td>

                            <td>
                                <span class="status approved">
                                    Approved
                                </span>
                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>


            <!-- Recent Payments -->

            <div class="panel">

                <div class="panel-header">

                    <h2>Recent Payments</h2>

                    <button>View All</button>

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

                        <tr>

                            <td>Rohan Shrestha</td>
                            <td>Sunshine Apartment</td>
                            <td>Rs. 25,000</td>
                            <td>20 Aug</td>

                            <td>
                                <span class="status approved">
                                    Paid
                                </span>
                            </td>

                        </tr>


                        <tr>

                            <td>Sita Thapa</td>
                            <td>Green Valley House</td>
                            <td>Rs. 18,000</td>
                            <td>19 Aug</td>

                            <td>
                                <span class="status approved">
                                    Paid
                                </span>
                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>

        </div>


        <!-- Bottom -->

        <div class="bottom-section">


            <div class="panel">

                <h2>House Status</h2>

                <p>
                    Occupied:
                    <strong>67</strong>
                </p>

                <p>
                    Vacant:
                    <strong>45</strong>
                </p>

                <p>
                    Maintenance:
                    <strong>13</strong>
                </p>

            </div>


            <div class="panel">

                <h2>Top Houses</h2>

                <p>
                    Sunshine Apartment — 12 Bookings
                </p>

                <p>
                    Green Valley House — 9 Bookings
                </p>

                <p>
                    Modern Family Home — 8 Bookings
                </p>

            </div>


            <div class="panel">

                <h2>System Overview</h2>

                <p>
                    Total Users:
                    <strong><?php echo $totalUsers; ?></strong>
                </p>

                <p>
                    Active Users:
                    <strong>278</strong>
                </p>

                <p>
                    Unread Messages:
                    <strong>16</strong>
                </p>

                <p>
                    System Status:
                    <strong>Online</strong>
                </p>

            </div>

        </div>

    </main>

</div>

</body>

</html>