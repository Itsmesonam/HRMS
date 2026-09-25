<?php

session_start();

require_once __DIR__ . "/config/database/db.php";


/*-- Show PHP Errors While Testing --*/

error_reporting(E_ALL);
ini_set('display_errors', 1);


/*-- Login Check --*/

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}


/*-- Tenant Check --*/

if (strtolower(trim($_SESSION['role'])) !== 'tenant') {
    header("Location: login.php");
    exit();
}


$user_id = (int) $_SESSION['user_id'];


/*-- Dashboard Values --*/

$availableHouses = 0;
$myBookings = 0;
$activeRental = 0;
$monthlyRent = 0;
$pendingPayments = 0;
$totalPayments = 0;


/*-- Available Houses --*/

$sql = "
    SELECT COUNT(*) AS total
    FROM properties
    WHERE property_status = 'Available'
";

$result = mysqli_query($conn, $sql);

if ($result) {

    $row = mysqli_fetch_assoc($result);

    $availableHouses = (int) ($row['total'] ?? 0);
}


/*-- My Bookings --*/

$sql = "
    SELECT COUNT(*) AS total
    FROM bookings
    WHERE tenant_id = $user_id
";

$result = mysqli_query($conn, $sql);

if ($result) {

    $row = mysqli_fetch_assoc($result);

    $myBookings = (int) ($row['total'] ?? 0);
}


/*-- Active Rental --*/

$sql = "
    SELECT COUNT(*) AS total
    FROM bookings
    WHERE tenant_id = $user_id
      AND booking_status = 'Confirmed'
";

$result = mysqli_query($conn, $sql);

if ($result) {

    $row = mysqli_fetch_assoc($result);

    $activeRental = (int) ($row['total'] ?? 0);
}


/*-- Current Monthly Rent --*/

$sql = "
    SELECT p.monthly_rent
    FROM bookings b
    INNER JOIN properties p
        ON b.property_id = p.property_id
    WHERE b.tenant_id = $user_id
      AND b.booking_status = 'Confirmed'
    ORDER BY b.created_at DESC
    LIMIT 1
";

$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {

    $row = mysqli_fetch_assoc($result);

    $monthlyRent = (float) ($row['monthly_rent'] ?? 0);
}


/*-- Pending Payments --*/

$sql = "
    SELECT COUNT(*) AS total
    FROM payments
    WHERE tenant_id = $user_id
      AND payment_status = 'Pending'
";

$result = mysqli_query($conn, $sql);

if ($result) {

    $row = mysqli_fetch_assoc($result);

    $pendingPayments = (int) ($row['total'] ?? 0);
}


/*-- Total Completed Payments --*/

$sql = "
    SELECT COALESCE(SUM(amount), 0) AS total
    FROM payments
    WHERE tenant_id = $user_id
      AND payment_status = 'Completed'
";

$result = mysqli_query($conn, $sql);

if ($result) {

    $row = mysqli_fetch_assoc($result);

    $totalPayments = (float) ($row['total'] ?? 0);
}


/*-- Current Rental --*/

$currentRental = null;

$currentRentalSQL = "
    SELECT
        p.property_name,
        p.monthly_rent,
        p.property_status,
        u.firstname,
        u.lastname
    FROM bookings b
    INNER JOIN properties p
        ON b.property_id = p.property_id
    INNER JOIN users u
        ON p.landlord_id = u.id
    WHERE b.tenant_id = $user_id
      AND b.booking_status = 'Confirmed'
    ORDER BY b.created_at DESC
    LIMIT 1
";

$currentRentalResult = mysqli_query(
    $conn,
    $currentRentalSQL
);

if (
    $currentRentalResult &&
    mysqli_num_rows($currentRentalResult) > 0
) {

    $currentRental = mysqli_fetch_assoc(
        $currentRentalResult
    );
}


/*-- Recent Bookings --*/

$recentBookings = [];

$recentBookingsSQL = "
    SELECT
        p.property_name,
        p.monthly_rent,
        b.booking_date,
        b.booking_status
    FROM bookings b
    INNER JOIN properties p
        ON b.property_id = p.property_id
    WHERE b.tenant_id = $user_id
    ORDER BY b.created_at DESC
    LIMIT 5
";

$recentBookingsResult = mysqli_query(
    $conn,
    $recentBookingsSQL
);

if ($recentBookingsResult) {

    while (
        $booking = mysqli_fetch_assoc(
            $recentBookingsResult
        )
    ) {

        $recentBookings[] = $booking;
    }
}

?>


<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>HRMS Tenant Dashboard</title>


    <!-- Material Symbols -->

    <link
        rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200"
    >


    <!-- Tenant Dashboard CSS -->

    <link
        rel="stylesheet"
        href="/hrms/Assets/css/tenantdashboard_style.css"
    >

</head>


<body>


<div class="container">


    <!-- ================= SIDEBAR ================= -->

    <aside>


        <div class="top">


            <!-- Logo -->
<div class="logo">

    <img src=" images/logo.png" alt="HOMY Logo">

</div>

            <!-- Close -->

            <div class="close">

                <span class="material-symbols-outlined">
                    close
                </span>

            </div>


        </div>


        <div class="sidebar">


            <!-- Dashboard -->

            <a
                href="tenantdashboard.php"
                class="active"
            >

                <span class="material-symbols-outlined">
                    dashboard
                </span>

                <h3>
                    Dashboard
                </h3>

            </a>


            <!-- Browse Houses -->

            <a href="properties.php">

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


            <!-- Make Payment -->

            <a href="payment.php">

                <span class="material-symbols-outlined">
                    payments
                </span>

                <h3>
                    Make Payment
                </h3>

            </a>


            <!-- Payment History -->

            <a href="tenant_payments.php">

                <span class="material-symbols-outlined">
                    history
                </span>

                <h3>
                    Payment History
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

            <a
                href="logout.php"
                class="logout"
            >

                <span class="material-symbols-outlined">
                    logout
                </span>

                <h3>
                    Logout
                </h3>

            </a>


        </div>

    </aside>


    <!-- ================= MAIN ================= -->

    <main>


        <!-- ================= HEADER ================= -->

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


        <!-- ================= STATISTICS ================= -->

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


        <!-- ================= RENTAL OVERVIEW ================= -->

        <div class="dashboard-content">


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


        </div>


        <!-- ================= TABLES ================= -->

        <div class="tables">


            <!-- Current Rental -->

            <div class="panel">


                <div class="panel-header">


                    <h2>
                        Current Rental
                    </h2>


                    <a href="tenant_rental.php">

                        <button type="button">
                            View Details
                        </button>

                    </a>


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


                    <?php if ($currentRental): ?>


                        <tr>


                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $currentRental['property_name']
                                );

                                ?>

                            </td>


                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $currentRental['firstname']
                                    . " "
                                    . $currentRental['lastname']
                                );

                                ?>

                            </td>


                            <td>

                                Rs.

                                <?php

                                echo number_format(
                                    $currentRental['monthly_rent']
                                );

                                ?>

                            </td>


                            <td>

                                <span class="status confirmed">

                                    Confirmed

                                </span>

                            </td>


                        </tr>


                    <?php else: ?>


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


                    <?php endif; ?>


                    </tbody>


                </table>


            </div>


            <!-- Recent Bookings -->

            <div class="panel">


                <div class="panel-header">


                    <h2>
                        Recent Bookings
                    </h2>


                    <a href="tenant_bookings.php">

                        <button type="button">
                            View All
                        </button>

                    </a>


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


                    <?php if (!empty($recentBookings)): ?>


                        <?php foreach ($recentBookings as $booking): ?>


                            <tr>


                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $booking['property_name']
                                    );

                                    ?>

                                </td>


                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $booking['booking_date']
                                    );

                                    ?>

                                </td>


                                <td>

                                    Rs.

                                    <?php

                                    echo number_format(
                                        $booking['monthly_rent']
                                    );

                                    ?>

                                </td>


                                <td>


                                    <?php

                                    $status =
                                        strtolower(
                                            $booking['booking_status']
                                        );

                                    ?>


                                    <span
                                        class="status <?php echo htmlspecialchars($status); ?>"
                                    >

                                        <?php

                                        echo htmlspecialchars(
                                            $booking['booking_status']
                                        );

                                        ?>

                                    </span>


                                </td>


                            </tr>


                        <?php endforeach; ?>


                    <?php else: ?>


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


                    <?php endif; ?>


                    </tbody>


                </table>


            </div>


        </div>


        <!-- ================= BOTTOM SECTION ================= -->

        <div class="bottom-section">


            <!-- Payment Summary -->

            <div class="panel">


                <h2>
                    Payment Summary
                </h2>


                <p>

                    Total Paid:

                    <strong>

                        Rs.

                        <?php

                        echo number_format(
                            $totalPayments
                        );

                        ?>

                    </strong>

                </p>


                <p>

                    Pending:

                    <strong>

                        <?php

                        echo number_format(
                            $pendingPayments
                        );

                        ?>

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

                        <?php

                        echo $activeRental;

                        ?>

                    </strong>

                </p>


                <p>

                    Monthly Rent:

                    <strong>

                        Rs.

                        <?php

                        echo number_format(
                            $monthlyRent
                        );

                        ?>

                    </strong>

                </p>


                <p>

                    Rental Status:

                    <strong>

                        <?php

                        echo $activeRental > 0
                            ? "Active"
                            : "No Rental";

                        ?>

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

                        <?php

                        echo $myBookings;

                        ?>

                    </strong>

                </p>


                <p>

                    Payments:

                    <strong>

                        Rs.

                        <?php

                        echo number_format(
                            $totalPayments
                        );

                        ?>

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