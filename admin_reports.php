<?php
session_start();

require_once __DIR__ . "/config/database/db.php";


/* =====================================================
   ADMIN LOGIN CHECK
===================================================== */

if (!isset($_SESSION['admin_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: adminlogin.php");
    exit();
}


/* =====================================================
   DATE FILTERS
===================================================== */

$fromDate = $_GET['from_date'] ?? '';
$toDate   = $_GET['to_date'] ?? '';

$dateConditionBookings = "";
$dateConditionPayments = "";

$bookingParams = [];
$bookingTypes = "";

$paymentParams = [];
$paymentTypes = "";


/* =====================================================
   BOOKING DATE FILTER
===================================================== */

if ($fromDate !== '') {
    $dateConditionBookings .= " AND DATE(booking_date) >= ?";
    $bookingParams[] = $fromDate;
    $bookingTypes .= "s";
}

if ($toDate !== '') {
    $dateConditionBookings .= " AND DATE(booking_date) <= ?";
    $bookingParams[] = $toDate;
    $bookingTypes .= "s";
}


/* =====================================================
   PAYMENT DATE FILTER
===================================================== */

if ($fromDate !== '') {
    $dateConditionPayments .= " AND DATE(payment_date) >= ?";
    $paymentParams[] = $fromDate;
    $paymentTypes .= "s";
}

if ($toDate !== '') {
    $dateConditionPayments .= " AND DATE(payment_date) <= ?";
    $paymentParams[] = $toDate;
    $paymentTypes .= "s";
}


/* =====================================================
   USER REPORT
===================================================== */

$totalUsers = 0;
$totalTenants = 0;
$totalLandlords = 0;


/* Total users */

$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM users"
);

if ($result) {
    $row = mysqli_fetch_assoc($result);
    $totalUsers = (int)$row['total'];
}


/* Total tenants */

$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM users
     WHERE role = 'tenant'"
);

if ($result) {
    $row = mysqli_fetch_assoc($result);
    $totalTenants = (int)$row['total'];
}


/* Total landlords */

$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM users
     WHERE role = 'landlord'"
);

if ($result) {
    $row = mysqli_fetch_assoc($result);
    $totalLandlords = (int)$row['total'];
}


/* =====================================================
   PROPERTY REPORT
===================================================== */

$totalProperties = 0;

$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM properties"
);

if ($result) {
    $row = mysqli_fetch_assoc($result);
    $totalProperties = (int)$row['total'];
}


/* =====================================================
   BOOKING REPORT
===================================================== */

$totalBookings = 0;
$pendingBookings = 0;
$confirmedBookings = 0;
$rejectedBookings = 0;
$cancelledBookings = 0;


/* Total bookings */

$sql = "
    SELECT COUNT(*) AS total
    FROM bookings
    WHERE 1=1
    $dateConditionBookings
";

$stmt = mysqli_prepare($conn, $sql);

if ($stmt) {

    if (!empty($bookingParams)) {
        mysqli_stmt_bind_param(
            $stmt,
            $bookingTypes,
            ...$bookingParams
        );
    }

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $totalBookings = (int)$row['total'];
    }

    mysqli_stmt_close($stmt);
}


/* =====================================================
   BOOKING STATUS REPORT
===================================================== */

$bookingStatusData = [];

$sql = "
    SELECT
        booking_status,
        COUNT(*) AS total
    FROM bookings
    WHERE 1=1
    $dateConditionBookings
    GROUP BY booking_status
";

$stmt = mysqli_prepare($conn, $sql);

if ($stmt) {

    if (!empty($bookingParams)) {
        mysqli_stmt_bind_param(
            $stmt,
            $bookingTypes,
            ...$bookingParams
        );
    }

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {

        $bookingStatusData[$row['booking_status']] =
            (int)$row['total'];
    }

    mysqli_stmt_close($stmt);
}


$pendingBookings =
    $bookingStatusData['Pending'] ?? 0;

$confirmedBookings =
    $bookingStatusData['Confirmed'] ?? 0;

$rejectedBookings =
    $bookingStatusData['Rejected'] ?? 0;

$cancelledBookings =
    $bookingStatusData['Cancelled'] ?? 0;


/* =====================================================
   PAYMENT REPORT
===================================================== */

$totalPayments = 0;
$completedPayments = 0;
$pendingPayments = 0;
$failedPayments = 0;
$refundedPayments = 0;

$totalRevenue = 0;


/* Total payments */

$sql = "
    SELECT COUNT(*) AS total
    FROM payments
    WHERE 1=1
    $dateConditionPayments
";

$stmt = mysqli_prepare($conn, $sql);

if ($stmt) {

    if (!empty($paymentParams)) {
        mysqli_stmt_bind_param(
            $stmt,
            $paymentTypes,
            ...$paymentParams
        );
    }

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $totalPayments = (int)$row['total'];
    }

    mysqli_stmt_close($stmt);
}


/* =====================================================
   PAYMENT STATUS REPORT
===================================================== */

$paymentStatusData = [];

$sql = "
    SELECT
        payment_status,
        COUNT(*) AS total
    FROM payments
    WHERE 1=1
    $dateConditionPayments
    GROUP BY payment_status
";

$stmt = mysqli_prepare($conn, $sql);

if ($stmt) {

    if (!empty($paymentParams)) {
        mysqli_stmt_bind_param(
            $stmt,
            $paymentTypes,
            ...$paymentParams
        );
    }

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {

        $paymentStatusData[$row['payment_status']] =
            (int)$row['total'];
    }

    mysqli_stmt_close($stmt);
}


$completedPayments =
    $paymentStatusData['Completed'] ?? 0;

$pendingPayments =
    $paymentStatusData['Pending'] ?? 0;

$failedPayments =
    $paymentStatusData['Failed'] ?? 0;

$refundedPayments =
    $paymentStatusData['Refunded'] ?? 0;


/* =====================================================
   COMPLETED PAYMENT AMOUNT
===================================================== */

$sql = "
    SELECT COALESCE(SUM(amount), 0) AS total
    FROM payments
    WHERE payment_status = 'Completed'
    $dateConditionPayments
";

$stmt = mysqli_prepare($conn, $sql);

if ($stmt) {

    if (!empty($paymentParams)) {
        mysqli_stmt_bind_param(
            $stmt,
            $paymentTypes,
            ...$paymentParams
        );
    }

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $totalRevenue = (float)$row['total'];
    }

    mysqli_stmt_close($stmt);
}


/* =====================================================
   PAYMENT METHOD REPORT
===================================================== */

$paymentMethods = [];

$sql = "
    SELECT
        payment_method,
        COUNT(*) AS total,
        COALESCE(SUM(amount), 0) AS amount
    FROM payments
    WHERE 1=1
    $dateConditionPayments
    GROUP BY payment_method
    ORDER BY total DESC
";

$stmt = mysqli_prepare($conn, $sql);

if ($stmt) {

    if (!empty($paymentParams)) {
        mysqli_stmt_bind_param(
            $stmt,
            $paymentTypes,
            ...$paymentParams
        );
    }

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        $paymentMethods[] = $row;
    }

    mysqli_stmt_close($stmt);
}


/* =====================================================
   RECENT PAYMENT REPORT
===================================================== */

$recentPayments = [];

$sql = "
    SELECT
        pay.payment_id,
        pay.amount,
        pay.payment_method,
        pay.payment_status,
        pay.payment_date,

        p.property_name,

        u.firstname,
        u.lastname

    FROM payments pay

    LEFT JOIN properties p
        ON pay.property_id = p.property_id

    LEFT JOIN users u
        ON pay.tenant_id = u.id

    WHERE 1=1
    $dateConditionPayments

    ORDER BY pay.payment_id DESC

    LIMIT 10
";

$stmt = mysqli_prepare($conn, $sql);

if ($stmt) {

    if (!empty($paymentParams)) {
        mysqli_stmt_bind_param(
            $stmt,
            $paymentTypes,
            ...$paymentParams
        );
    }

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        $recentPayments[] = $row;
    }

    mysqli_stmt_close($stmt);
}


/* =====================================================
   ADMIN NAME
===================================================== */

$adminName = $_SESSION['admin_name'] ?? 'Admin';

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Reports | HRMS Admin</title>


    <!-- Material Icons -->

    <link
        rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined"
    >


    <!-- CSS -->

    <link
        rel="stylesheet"
        href="Assets/css/admin_reports_style.css"
    >

</head>


<body>


<div class="container">


    <!-- =================================================
         SIDEBAR
    ================================================== -->

    <aside>

        <div class="top">

            <h2 class="logo-text">
                HRMS
            </h2>

        </div>


        <nav class="sidebar">


            <a href="admindashboard.php">

                <span class="material-symbols-outlined">
                    dashboard
                </span>

                <h3>
                    Dashboard
                </h3>

            </a>


            <a href="admin_houses.php">

                <span class="material-symbols-outlined">
                    home
                </span>

                <h3>
                    Houses
                </h3>

            </a>


            <a href="admin_landlords.php">

                <span class="material-symbols-outlined">
                    real_estate_agent
                </span>

                <h3>
                    Landlords
                </h3>

            </a>


            <a href="admin_tenants.php">

                <span class="material-symbols-outlined">
                    group
                </span>

                <h3>
                    Tenants
                </h3>

            </a>


            <a href="admin_bookings.php">

                <span class="material-symbols-outlined">
                    calendar_month
                </span>

                <h3>
                    Bookings
                </h3>

            </a>


            <a href="admin_payments.php">

                <span class="material-symbols-outlined">
                    payments
                </span>

                <h3>
                    Payments
                </h3>

            </a>


            <a href="admin_reports.php" class="active">

                <span class="material-symbols-outlined">
                    bar_chart
                </span>

                <h3>
                    Reports
                </h3>

            </a>


            <a href="admin_users.php">

                <span class="material-symbols-outlined">
                    manage_accounts
                </span>

                <h3>
                    Users
                </h3>

            </a>


            <a href="admin_messages.php">

                <span class="material-symbols-outlined">
                    chat
                </span>

                <h3>
                    Messages
                </h3>

            </a>


            <a href="admin_settings.php">

                <span class="material-symbols-outlined">
                    settings
                </span>

                <h3>
                    Settings
                </h3>

            </a>


            <a href="logout.php" class="logout">

                <span class="material-symbols-outlined">
                    logout
                </span>

                <h3>
                    Logout
                </h3>

            </a>


        </nav>

    </aside>



    <!-- =================================================
         MAIN
    ================================================== -->

    <main>


        <!-- HEADER -->

        <header class="page-header">


            <div>

                <h1>
                    Reports
                </h1>

                <p>
                    View summaries of users, properties, bookings and payments.
                </p>

            </div>


            <div class="header-actions">

                <div class="profile">

                    <?= htmlspecialchars($adminName) ?>

                    · Administrator

                </div>


                <button
                    type="button"
                    class="print-btn"
                    onclick="window.print()"
                >

                    <span class="material-symbols-outlined">
                        print
                    </span>

                    Print Report

                </button>

            </div>


        </header>



        <!-- =================================================
             DATE FILTER
        ================================================== -->

        <section class="filter-panel">


            <form method="GET" class="date-filter">


                <div class="date-field">

                    <label for="from_date">
                        From
                    </label>

                    <input
                        type="date"
                        id="from_date"
                        name="from_date"
                        value="<?= htmlspecialchars($fromDate) ?>"
                    >

                </div>


                <div class="date-field">

                    <label for="to_date">
                        To
                    </label>

                    <input
                        type="date"
                        id="to_date"
                        name="to_date"
                        value="<?= htmlspecialchars($toDate) ?>"
                    >

                </div>


                <button type="submit">
                    Generate Report
                </button>


                <a
                    href="admin_reports.php"
                    class="reset"
                >
                    Clear
                </a>


            </form>


        </section>



        <!-- =================================================
             USER & PROPERTY SUMMARY
        ================================================== -->

        <section class="summary-grid">


            <div class="summary-card">

                <div class="summary-icon users-icon">

                    <span class="material-symbols-outlined">
                        group
                    </span>

                </div>

                <div>

                    <span>
                        Total Users
                    </span>

                    <strong>
                        <?= number_format($totalUsers) ?>
                    </strong>

                </div>

            </div>



            <div class="summary-card">

                <div class="summary-icon tenant-icon">

                    <span class="material-symbols-outlined">
                        person
                    </span>

                </div>

                <div>

                    <span>
                        Tenants
                    </span>

                    <strong>
                        <?= number_format($totalTenants) ?>
                    </strong>

                </div>

            </div>



            <div class="summary-card">

                <div class="summary-icon landlord-icon">

                    <span class="material-symbols-outlined">
                        real_estate_agent
                    </span>

                </div>

                <div>

                    <span>
                        Landlords
                    </span>

                    <strong>
                        <?= number_format($totalLandlords) ?>
                    </strong>

                </div>

            </div>



            <div class="summary-card">

                <div class="summary-icon property-icon">

                    <span class="material-symbols-outlined">
                        home
                    </span>

                </div>

                <div>

                    <span>
                        Properties
                    </span>

                    <strong>
                        <?= number_format($totalProperties) ?>
                    </strong>

                </div>

            </div>


        </section>



        <!-- =================================================
             BOOKING REPORT
        ================================================== -->

        <section class="report-section">


            <div class="section-header">

                <div>

                    <h2>
                        Booking Report
                    </h2>

                    <p>
                        Booking activity and current booking statuses.
                    </p>

                </div>


                <div class="total-label">

                    Total:
                    <strong>
                        <?= number_format($totalBookings) ?>
                    </strong>

                </div>

            </div>


            <div class="status-grid">


                <div class="status-card pending-card">

                    <span>
                        Pending
                    </span>

                    <strong>
                        <?= number_format($pendingBookings) ?>
                    </strong>

                </div>


                <div class="status-card confirmed-card">

                    <span>
                        Confirmed
                    </span>

                    <strong>
                        <?= number_format($confirmedBookings) ?>
                    </strong>

                </div>


                <div class="status-card rejected-card">

                    <span>
                        Rejected
                    </span>

                    <strong>
                        <?= number_format($rejectedBookings) ?>
                    </strong>

                </div>


                <div class="status-card cancelled-card">

                    <span>
                        Cancelled
                    </span>

                    <strong>
                        <?= number_format($cancelledBookings) ?>
                    </strong>

                </div>


            </div>


        </section>



        <!-- =================================================
             PAYMENT REPORT
        ================================================== -->

        <section class="report-section">


            <div class="section-header">

                <div>

                    <h2>
                        Payment Report
                    </h2>

                    <p>
                        Payment activity and collected revenue.
                    </p>

                </div>


                <div class="total-label">

                    Payments:
                    <strong>
                        <?= number_format($totalPayments) ?>
                    </strong>

                </div>

            </div>


            <!-- PAYMENT SUMMARY -->

            <div class="payment-summary">


                <div class="revenue-card">

                    <span>
                        Completed Payment Amount
                    </span>

                    <strong>
                        Rs. <?= number_format($totalRevenue, 2) ?>
                    </strong>

                </div>


                <div class="payment-status-item">

                    <span>
                        Completed
                    </span>

                    <strong>
                        <?= number_format($completedPayments) ?>
                    </strong>

                </div>


                <div class="payment-status-item">

                    <span>
                        Pending
                    </span>

                    <strong>
                        <?= number_format($pendingPayments) ?>
                    </strong>

                </div>


                <div class="payment-status-item">

                    <span>
                        Failed
                    </span>

                    <strong>
                        <?= number_format($failedPayments) ?>
                    </strong>

                </div>


                <div class="payment-status-item">

                    <span>
                        Refunded
                    </span>

                    <strong>
                        <?= number_format($refundedPayments) ?>
                    </strong>

                </div>


            </div>


        </section>



        <!-- =================================================
             PAYMENT METHOD REPORT
        ================================================== -->

        <section class="report-section">


            <div class="section-header">

                <div>

                    <h2>
                        Payment Methods
                    </h2>

                    <p>
                        Number of payments and amount by payment method.
                    </p>

                </div>

            </div>


            <div class="table-wrap">


                <table>


                    <thead>

                        <tr>

                            <th>
                                Payment Method
                            </th>

                            <th>
                                Transactions
                            </th>

                            <th>
                                Amount
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php if (!empty($paymentMethods)): ?>


                        <?php foreach ($paymentMethods as $paymentMethod): ?>


                            <tr>

                                <td>

                                    <span class="method">
                                        <?= htmlspecialchars(
                                            $paymentMethod['payment_method']
                                        ) ?>
                                    </span>

                                </td>


                                <td>

                                    <?= number_format(
                                        (int)$paymentMethod['total']
                                    ) ?>

                                </td>


                                <td class="amount">

                                    Rs.
                                    <?= number_format(
                                        (float)$paymentMethod['amount'],
                                        2
                                    ) ?>

                                </td>

                            </tr>


                        <?php endforeach; ?>


                    <?php else: ?>


                        <tr>

                            <td
                                colspan="3"
                                class="empty"
                            >

                                No payment data available.

                            </td>

                        </tr>


                    <?php endif; ?>


                    </tbody>


                </table>


            </div>


        </section>



        <!-- =================================================
             RECENT PAYMENTS
        ================================================== -->

        <section class="report-section">


            <div class="section-header">

                <div>

                    <h2>
                        Recent Payments
                    </h2>

                    <p>
                        Latest payment records within the selected period.
                    </p>

                </div>

            </div>


            <div class="table-wrap">


                <table>


                    <thead>

                        <tr>

                            <th>
                                ID
                            </th>

                            <th>
                                Tenant
                            </th>

                            <th>
                                Property
                            </th>

                            <th>
                                Amount
                            </th>

                            <th>
                                Method
                            </th>

                            <th>
                                Status
                            </th>

                            <th>
                                Date
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php if (!empty($recentPayments)): ?>


                        <?php foreach ($recentPayments as $payment): ?>


                            <?php

                            $tenantName = trim(
                                ($payment['firstname'] ?? '') .
                                ' ' .
                                ($payment['lastname'] ?? '')
                            );

                            if ($tenantName === '') {
                                $tenantName = 'Unknown Tenant';
                            }

                            $statusClass = strtolower(
                                $payment['payment_status'] ?? 'Pending'
                            );

                            ?>


                            <tr>


                                <td>
                                    #<?= (int)$payment['payment_id'] ?>
                                </td>


                                <td>
                                    <?= htmlspecialchars($tenantName) ?>
                                </td>


                                <td>
                                    <?= htmlspecialchars(
                                        $payment['property_name']
                                        ?? 'Unknown Property'
                                    ) ?>
                                </td>


                                <td class="amount">

                                    Rs.
                                    <?= number_format(
                                        (float)$payment['amount'],
                                        2
                                    ) ?>

                                </td>


                                <td>

                                    <?= htmlspecialchars(
                                        $payment['payment_method']
                                    ) ?>

                                </td>


                                <td>

                                    <span
                                        class="status <?= htmlspecialchars(
                                            $statusClass
                                        ) ?>"
                                    >

                                        <?= htmlspecialchars(
                                            $payment['payment_status']
                                        ) ?>

                                    </span>

                                </td>


                                <td>

                                    <?= htmlspecialchars(
                                        $payment['payment_date']
                                    ) ?>

                                </td>


                            </tr>


                        <?php endforeach; ?>


                    <?php else: ?>


                        <tr>

                            <td
                                colspan="7"
                                class="empty"
                            >

                                No payment records found.

                            </td>

                        </tr>


                    <?php endif; ?>


                    </tbody>


                </table>


            </div>


        </section>


        <!-- REPORT NOTE -->

        <p class="report-note">

            This report is generated from the current HOMY database records.
            The selected date range applies to booking dates and payment dates.

        </p>


    </main>


</div>


</body>

</html>