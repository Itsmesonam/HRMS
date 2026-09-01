<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

include "config/database/db.php";

/*
    Temporary landlord values.

    We will connect these to the logged-in landlord
    and database after the dashboard UI is working.
*/

$totalHouses = 0;
$availableHouses = 0;
$occupiedHouses = 0;
$totalTenants = 0;
$pendingBookings = 0;
$monthlyRent = 0;

?>
<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION['role'] !== 'landlord') {
    header("Location: login.php");
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>HRMS Landlord Dashboard</title>


    <!-- Google Material Symbols -->

    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200">


    <!-- Landlord Dashboard CSS -->

    <link rel="stylesheet"
          href="assets/css/landlorddashboard_style.css">

</head>


<body>


<div class="container">


    <!-- sidebar -->

    <aside>


        <div class="top">


            <div class="logo">

                <h2>

                    <span class="logo-text">
                        HRMS
                    </span>

                </h2>

            </div>


            <div class="close">

                <span class="material-symbols-outlined">
                    close
                </span>

            </div>


        </div>


        <div class="sidebar">


            <!-- Dashboard -->

            <a href="landlorddashboard.php"
               class="active">

                <span class="material-symbols-outlined">
                    dashboard
                </span>

                <h3>
                    Dashboard
                </h3>

            </a>


            <!-- My Houses -->

            <a href="landlord_houses.php">

                <span class="material-symbols-outlined">
                    home
                </span>

                <h3>
                    My Houses
                </h3>

            </a>


            <!-- Add House -->

            <a href="add_house.php">

                <span class="material-symbols-outlined">
                    add_home
                </span>

                <h3>
                    Add House
                </h3>

            </a>


            <!-- Tenants -->

            <a href="landlord_tenants.php">

                <span class="material-symbols-outlined">
                    group
                </span>

                <h3>
                    Tenants
                </h3>

            </a>


            <!-- Bookings -->

            <a href="landlord_bookings.php">

                <span class="material-symbols-outlined">
                    calendar_month
                </span>

                <h3>
                    Bookings
                </h3>

            </a>


            <!-- Payments -->

            <a href="landlord_payments.php">

                <span class="material-symbols-outlined">
                    payments
                </span>

                <h3>
                    Payments
                </h3>

            </a>


            <!-- Messages -->

            <a href="messages.php">

                <span class="material-symbols-outlined">
                    chat
                </span>

                <h3>
                    Messages
                </h3>

            </a>


            <!-- Profile -->

            <a href="landlord_profile.php">

                <span class="material-symbols-outlined">
                    manage_accounts
                </span>

                <h3>
                    Profile
                </h3>

            </a>


            <!-- Logout -->

            <a href="logout.php" class="logout">

                <span class="material-symbols-outlined">
                    logout
                </span>

                <h3>
                    Logout
                </h3>

            </a>


        </div>

    </aside>


    <!-- Main -->

    <main>


        <!-- Header -->

        <div class="top-header">


            <div>

                <h1>
                    Landlord Dashboard
                </h1>

                <p>
                    Manage your rental properties
                </p>

            </div>


            <div class="landlord-profile">


                <span class="material-symbols-outlined">
                    notifications
                </span>


                <div>

                    <strong>
                        Landlord
                    </strong>

                    <small>
                        Property Owner
                    </small>

                </div>


            </div>


        </div>



        <!-- STATISTICS -->

        <div class="stats">


            <!-- My Houses -->

            <div class="card">

                <span class="material-symbols-outlined">
                    home
                </span>

                <div>

                    <h3>
                        My Houses
                    </h3>

                    <h2>
                        <?php echo $totalHouses; ?>
                    </h2>

                    <p>
                        Total properties
                    </p>

                </div>

            </div>


            <!-- Available Houses -->

            <div class="card">

                <span class="material-symbols-outlined">
                    home_work
                </span>

                <div>

                    <h3>
                        Available Houses
                    </h3>

                    <h2>
                        <?php echo $availableHouses; ?>
                    </h2>

                    <p>
                        Available for rent
                    </p>

                </div>

            </div>


            <!-- Occupied -->

            <div class="card">

                <span class="material-symbols-outlined">
                    house
                </span>

                <div>

                    <h3>
                        Occupied Houses
                    </h3>

                    <h2>
                        <?php echo $occupiedHouses; ?>
                    </h2>

                    <p>
                        Currently rented
                    </p>

                </div>

            </div>


            <!-- Tenants -->

            <div class="card">

                <span class="material-symbols-outlined">
                    group
                </span>

                <div>

                    <h3>
                        My Tenants
                    </h3>

                    <h2>
                        <?php echo $totalTenants; ?>
                    </h2>

                    <p>
                        Active tenants
                    </p>

                </div>

            </div>


            <!-- Bookings -->

            <div class="card">

                <span class="material-symbols-outlined">
                    calendar_month
                </span>

                <div>

                    <h3>
                        Pending Bookings
                    </h3>

                    <h2>
                        <?php echo $pendingBookings; ?>
                    </h2>

                    <p>
                        Need approval
                    </p>

                </div>

            </div>


            <!-- Rent -->

            <div class="card">

                <span class="material-symbols-outlined">
                    payments
                </span>

                <div>

                    <h3>
                        Monthly Rent
                    </h3>

                    <h2>
                        Rs. <?php echo number_format($monthlyRent); ?>
                    </h2>

                    <p>
                        Expected income
                    </p>

                </div>

            </div>


        </div>



        <!-- DASHBOARD CONTENT -->

        <div class="dashboard-content">


            <!-- Revenue -->

            <div class="panel revenue">


                <div class="panel-header">

                    <h2>
                        Revenue Overview
                    </h2>


                    <select>

                        <option>
                            This Year
                        </option>

                        <option>
                            Last Year
                        </option>

                    </select>

                </div>


                <div class="chart-placeholder">

                    Revenue Chart

                </div>


            </div>



            <!-- Booking -->

            <div class="panel booking">


                <div class="panel-header">

                    <h2>
                        Booking Overview
                    </h2>


                    <select>

                        <option>
                            This Year
                        </option>

                        <option>
                            Last Year
                        </option>

                    </select>

                </div>


                <div class="circle-chart">

                    <div>

                        <?php echo $pendingBookings; ?>

                        <small>
                            Pending
                        </small>

                    </div>

                </div>


            </div>


        </div>



        <!-- TABLES -->

        <div class="tables">


            <!-- My Houses -->

            <div class="panel">


                <div class="panel-header">

                    <h2>
                        My Houses
                    </h2>


                    <button>
                        View All
                    </button>

                </div>


                <table>


                    <thead>

                        <tr>

                            <th>
                                House
                            </th>

                            <th>
                                Rent
                            </th>

                            <th>
                                Tenant
                            </th>

                            <th>
                                Status
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                        <tr>

                            <td>
                                No houses yet
                            </td>

                            <td>
                                -
                            </td>

                            <td>
                                -
                            </td>

                            <td>

                                <span class="status pending">
                                    No Data
                                </span>

                            </td>

                        </tr>


                    </tbody>


                </table>


            </div>



            <!-- Recent Bookings -->

            <div class="panel">


                <div class="panel-header">

                    <h2>
                        Recent Bookings
                    </h2>


                    <button>
                        View All
                    </button>

                </div>


                <table>


                    <thead>

                        <tr>

                            <th>
                                Tenant
                            </th>

                            <th>
                                House
                            </th>

                            <th>
                                Date
                            </th>

                            <th>
                                Status
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                        <tr>

                            <td>
                                No bookings
                            </td>

                            <td>
                                -
                            </td>

                            <td>
                                -
                            </td>

                            <td>

                                <span class="status pending">
                                    No Data
                                </span>

                            </td>

                        </tr>


                    </tbody>


                </table>


            </div>


        </div>



        <!-- Bottom Section -->

        <div class="bottom-section">


            <!-- House Status -->

            <div class="panel">

                <h2>
                    House Status
                </h2>

                <p>
                    Occupied:
                    <strong>
                        <?php echo $occupiedHouses; ?>
                    </strong>
                </p>

                <p>
                    Vacant:
                    <strong>
                        <?php echo $availableHouses; ?>
                    </strong>
                </p>

                <p>
                    Maintenance:
                    <strong>
                        0
                    </strong>
                </p>

            </div>


            <!-- Rent Summary -->

            <div class="panel">

                <h2>
                    Rent Summary
                </h2>

                <p>
                    Monthly Rent:
                    <strong>
                        Rs. <?php echo number_format($monthlyRent); ?>
                    </strong>
                </p>

                <p>
                    Paid:
                    <strong>
                        Rs. 0
                    </strong>
                </p>

                <p>
                    Pending:
                    <strong>
                        Rs. 0
                    </strong>
                </p>

            </div>


            <!-- Account -->

            <div class="panel">

                <h2>
                    Account Overview
                </h2>

                <p>
                    Houses:
                    <strong>
                        <?php echo $totalHouses; ?>
                    </strong>
                </p>

                <p>
                    Tenants:
                    <strong>
                        <?php echo $totalTenants; ?>
                    </strong>
                </p>

                <p>
                    Account Status:
                    <strong>
                        Active
                    </strong>
                </p>

            </div>


        </div>


    </main>


</div>


</body>

</html>