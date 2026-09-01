<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

include "config/database/db.php";

/*
    Temporary values.
    We will connect these to the logged-in tenant
    after the dashboard UI is working.
*/

$availableHouses = 0;
$myBookings = 0;
$activeRental = 0;
$monthlyRent = 0;
$pendingPayments = 0;
$totalPayments = 0;

?>
<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION['role'] !== 'tenant') {
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

    <title>HRMS Tenant Dashboard</title>


    <!-- Material Symbols -->

    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200">


    <!-- Tenant Dashboard CSS -->

    <link rel="stylesheet"
          href="assets/css/tenantdashboard_style.css">

</head>


<body>


<div class="container">


    <!-- ================= SIDEBAR ================= -->

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

            <a href="tenantdashboard.php"
               class="active">

                <span class="material-symbols-outlined">
                    dashboard
                </span>

                <h3>
                    Dashboard
                </h3>

            </a>


            <!-- Browse Houses -->

            <a href="houses.php">

                <span class="material-symbols-outlined">
                    home
                </span>

                <h3>
                    Browse Houses
                </h3>

            </a>


            <!-- My Bookings -->

            <a href="tenant_bookings.php">

                <span class="material-symbols-outlined">
                    calendar_month
                </span>

                <h3>
                    My Bookings
                </h3>

            </a>


            <!-- My Rental -->

            <a href="tenant_rental.php">

                <span class="material-symbols-outlined">
                    house
                </span>

                <h3>
                    My Rental
                </h3>

            </a>


            <!-- Payments -->

            <a href="tenant_payments.php">

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

            <a href="tenant_profile.php">

                <span class="material-symbols-outlined">
                    manage_accounts
                </span>

                <h3>
                    Profile
                </h3>

            </a>


            <!-- Settings -->

            <a href="settings.php">

                <span class="material-symbols-outlined">
                    settings
                </span>

                <h3>
                    Settings
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


    <!-- MAIN -->

    <main>


        <!-- Header -->

        <div class="top-header">


            <div>

                <h1>
                    Tenant Dashboard
                </h1>

                <p>
                    Welcome back, Tenant
                </p>

            </div>


            <div class="tenant-profile">


                <span class="material-symbols-outlined">
                    notifications
                </span>


                <div>

                    <strong>
                        Tenant
                    </strong>

                    <small>
                        Renter
                    </small>

                </div>


            </div>


        </div>



        <!-- STATISTICS -->

        <div class="stats">


            <!-- Available Houses -->

            <div class="card">

                <span class="material-symbols-outlined">
                    home
                </span>

                <div>

                    <h3>
                        Available Houses
                    </h3>

                    <h2>
                        <?php echo $availableHouses; ?>
                    </h2>

                    <p>
                        Houses available for rent
                    </p>

                </div>

            </div>


            <!-- My Bookings -->

            <div class="card">

                <span class="material-symbols-outlined">
                    calendar_month
                </span>

                <div>

                    <h3>
                        My Bookings
                    </h3>

                    <h2>
                        <?php echo $myBookings; ?>
                    </h2>

                    <p>
                        Total bookings
                    </p>

                </div>

            </div>


            <!-- Active Rental -->

            <div class="card">

                <span class="material-symbols-outlined">
                    house
                </span>

                <div>

                    <h3>
                        Active Rental
                    </h3>

                    <h2>
                        <?php echo $activeRental; ?>
                    </h2>

                    <p>
                        Current rental
                    </p>

                </div>

            </div>


            <!-- Monthly Rent -->

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
                        Current monthly rent
                    </p>

                </div>

            </div>


            <!-- Pending Payments -->

            <div class="card">

                <span class="material-symbols-outlined">
                    pending_actions
                </span>

                <div>

                    <h3>
                        Pending Payments
                    </h3>

                    <h2>
                        <?php echo $pendingPayments; ?>
                    </h2>

                    <p>
                        Payments pending
                    </p>

                </div>

            </div>


            <!-- Total Payments -->

            <div class="card">

                <span class="material-symbols-outlined">
                    account_balance_wallet
                </span>

                <div>

                    <h3>
                        Total Payments
                    </h3>

                    <h2>
                        Rs. <?php echo number_format($totalPayments); ?>
                    </h2>

                    <p>
                        Total amount paid
                    </p>

                </div>

            </div>


        </div>



        <!--OVERVIEW -->

        <div class="dashboard-content">


            <!-- Rental Overview -->

            <div class="panel">


                <div class="panel-header">

                    <h2>
                        Rental Overview
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

                    Rental Payment Chart

                </div>


            </div>



            <!-- Booking Overview -->

            <div class="panel">


                <div class="panel-header">

                    <h2>
                        Booking Overview
                    </h2>

                </div>


                <div class="circle-chart">


                    <div>

                        <?php echo $myBookings; ?>

                        <small>
                            Bookings
                        </small>

                    </div>


                </div>


            </div>


        </div>



        <!-- TABLES -->

        <div class="tables">


            <!-- Current Rental -->

            <div class="panel">


                <div class="panel-header">

                    <h2>
                        Current Rental
                    </h2>


                    <button>
                        View Details
                    </button>

                </div>


                <table>


                    <thead>

                        <tr>

                            <th>
                                House
                            </th>

                            <th>
                                Landlord
                            </th>

                            <th>
                                Rent
                            </th>

                            <th>
                                Status
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        <tr>

                            <td>
                                No active rental
                            </td>

                            <td>
                                -
                            </td>

                            <td>
                                -
                            </td>

                            <td>

                                <span class="status pending">
                                    No Rental
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
                                House
                            </th>

                            <th>
                                Date
                            </th>

                            <th>
                                Rent
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



        <!-- BOTTOM SECTION -->

        <div class="bottom-section">


            <!-- Payment Summary -->

            <div class="panel">

                <h2>
                    Payment Summary
                </h2>

                <p>
                    Total Paid:
                    <strong>
                        Rs. <?php echo number_format($totalPayments); ?>
                    </strong>
                </p>

                <p>
                    Pending:
                    <strong>
                        Rs. 0
                    </strong>
                </p>

                <p>
                    Next Payment:
                    <strong>
                        -
                    </strong>
                </p>

            </div>


            <!-- Rental Information -->

            <div class="panel">

                <h2>
                    Rental Information
                </h2>

                <p>
                    Active Rental:
                    <strong>
                        <?php echo $activeRental; ?>
                    </strong>
                </p>

                <p>
                    Monthly Rent:
                    <strong>
                        Rs. <?php echo number_format($monthlyRent); ?>
                    </strong>
                </p>

                <p>
                    Rental Status:
                    <strong>
                        -
                    </strong>
                </p>

            </div>


            <!-- Account Overview -->

            <div class="panel">

                <h2>
                    Account Overview
                </h2>

                <p>
                    Bookings:
                    <strong>
                        <?php echo $myBookings; ?>
                    </strong>
                </p>

                <p>
                    Payments:
                    <strong>
                        Rs. <?php echo number_format($totalPayments); ?>
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