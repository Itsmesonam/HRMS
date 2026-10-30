<?php
session_start();

require_once __DIR__ . "/config/database/db.php";

/* Admin login check */
if (!isset($_SESSION['admin_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: adminlogin.php");
    exit();
}


/* =====================================================
   FILTER VALUES
===================================================== */

$search = trim($_GET['search'] ?? '');
$status = trim($_GET['status'] ?? '');
$method = trim($_GET['method'] ?? '');


/* =====================================================
   PAYMENT QUERY
===================================================== */

$sql = "
    SELECT
        pay.payment_id,
        pay.booking_id,
        pay.property_id,
        pay.tenant_id,
        pay.landlord_id,
        pay.amount,
        pay.payment_method,
        pay.payment_status,
        pay.transaction_id,
        pay.payment_date,
        pay.notes,

        p.property_name,
        p.location,

        t.firstname AS tenant_firstname,
        t.lastname AS tenant_lastname,
        t.email AS tenant_email,

        l.firstname AS landlord_firstname,
        l.lastname AS landlord_lastname

    FROM payments pay

    LEFT JOIN properties p
        ON pay.property_id = p.property_id

    LEFT JOIN users t
        ON pay.tenant_id = t.id

    LEFT JOIN users l
        ON pay.landlord_id = l.id

    WHERE 1=1
";

$params = [];
$types = "";


/* =====================================================
   SEARCH
===================================================== */

if ($search !== '') {

    $sql .= "
        AND (
            p.property_name LIKE ?
            OR p.location LIKE ?

            OR t.firstname LIKE ?
            OR t.lastname LIKE ?
            OR t.email LIKE ?

            OR l.firstname LIKE ?
            OR l.lastname LIKE ?

            OR pay.transaction_id LIKE ?
        )
    ";

    $term = "%" . $search . "%";

    $params[] = $term;
    $params[] = $term;

    $params[] = $term;
    $params[] = $term;
    $params[] = $term;

    $params[] = $term;
    $params[] = $term;

    $params[] = $term;

    $types .= "ssssssss";
}


/* =====================================================
   STATUS FILTER
===================================================== */

if ($status !== '') {

    $sql .= " AND pay.payment_status = ?";

    $params[] = $status;
    $types .= "s";
}


/* =====================================================
   PAYMENT METHOD FILTER
===================================================== */

if ($method !== '') {

    $sql .= " AND pay.payment_method = ?";

    $params[] = $method;
    $types .= "s";
}


/* =====================================================
   ORDER
===================================================== */

$sql .= " ORDER BY pay.payment_id DESC";


/* =====================================================
   PREPARE QUERY
===================================================== */

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Unable to load payments. Please check the payments, properties and users tables.");
}


/* Bind parameters when filters are used */

if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}


mysqli_stmt_execute($stmt);

$payments = mysqli_stmt_get_result($stmt);


/* Admin name */

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

    <title>Payments | HRMS Admin</title>


    <!-- Google Material Icons -->

    <link
        rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined"
    >


    <!-- Page CSS -->

    <link
        rel="stylesheet"
        href="Assets/css/admin_payments_style.css"
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


            <a href="admin_payments.php" class="active">

                <span class="material-symbols-outlined">
                    payments
                </span>

                <h3>
                    Payments
                </h3>

            </a>


            <a href="admin_reports.php">

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
         MAIN CONTENT
    ================================================== -->

    <main>


        <!-- PAGE HEADER -->

        <header class="page-header">


            <div>

                <h1>
                    Payment Management
                </h1>

                <p>
                    Monitor rental payment records and transactions.
                </p>

            </div>


            <div class="profile">

                <?= htmlspecialchars($adminName) ?>

                · Administrator

            </div>


        </header>



        <!-- =================================================
             PAYMENT PANEL
        ================================================== -->

        <section class="panel">


            <!-- FILTERS -->

            <form
                method="GET"
                class="filters"
            >


                <input
                    type="search"
                    name="search"
                    value="<?= htmlspecialchars($search) ?>"
                    placeholder="Search property, tenant or transaction"
                >


                <select name="status">

                    <option value="">
                        All Statuses
                    </option>

                    <option
                        value="Pending"
                        <?= $status === 'Pending' ? 'selected' : '' ?>
                    >
                        Pending
                    </option>

                    <option
                        value="Completed"
                        <?= $status === 'Completed' ? 'selected' : '' ?>
                    >
                        Completed
                    </option>

                    <option
                        value="Failed"
                        <?= $status === 'Failed' ? 'selected' : '' ?>
                    >
                        Failed
                    </option>

                    <option
                        value="Refunded"
                        <?= $status === 'Refunded' ? 'selected' : '' ?>
                    >
                        Refunded
                    </option>

                </select>


                <select name="method">

                    <option value="">
                        All Methods
                    </option>

                    <option
                        value="Cash"
                        <?= $method === 'Cash' ? 'selected' : '' ?>
                    >
                        Cash
                    </option>

                    <option
                        value="Card"
                        <?= $method === 'Card' ? 'selected' : '' ?>
                    >
                        Card
                    </option>

                    <option
                        value="eSewa"
                        <?= $method === 'eSewa' ? 'selected' : '' ?>
                    >
                        eSewa
                    </option>

                    <option
                        value="Khalti"
                        <?= $method === 'Khalti' ? 'selected' : '' ?>
                    >
                        Khalti
                    </option>

                    <option
                        value="Bank Transfer"
                        <?= $method === 'Bank Transfer' ? 'selected' : '' ?>
                    >
                        Bank Transfer
                    </option>

                </select>


                <button type="submit">
                    Search
                </button>


                <a
                    href="admin_payments.php"
                    class="reset"
                >
                    Reset
                </a>


            </form>



            <!-- =================================================
                 PAYMENT TABLE
            ================================================== -->

            <div class="table-wrap">


                <table>


                    <thead>

                        <tr>

                            <th>
                                ID
                            </th>

                            <th>
                                Booking
                            </th>

                            <th>
                                Tenant
                            </th>

                            <th>
                                Property
                            </th>

                            <th>
                                Landlord
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
                                Transaction
                            </th>

                            <th>
                                Payment Date
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php if (mysqli_num_rows($payments) > 0): ?>


                        <?php while ($payment = mysqli_fetch_assoc($payments)): ?>


                            <?php

                            $paymentStatus =
                                $payment['payment_status']
                                ?? 'Pending';


                            $statusClass =
                                strtolower($paymentStatus);


                            $tenantName = trim(
                                ($payment['tenant_firstname'] ?? '') .
                                ' ' .
                                ($payment['tenant_lastname'] ?? '')
                            );


                            if ($tenantName === '') {
                                $tenantName = 'Unknown Tenant';
                            }


                            $landlordName = trim(
                                ($payment['landlord_firstname'] ?? '') .
                                ' ' .
                                ($payment['landlord_lastname'] ?? '')
                            );


                            if ($landlordName === '') {
                                $landlordName = 'Unknown Landlord';
                            }

                            ?>


                            <tr>


                                <!-- Payment ID -->

                                <td>

                                    #<?= (int)$payment['payment_id'] ?>

                                </td>



                                <!-- Booking -->

                                <td>

                                    #<?= (int)$payment['booking_id'] ?>

                                </td>



                                <!-- Tenant -->

                                <td>

                                    <strong>
                                        <?= htmlspecialchars($tenantName) ?>
                                    </strong>

                                    <small>
                                        <?= htmlspecialchars(
                                            $payment['tenant_email'] ?? ''
                                        ) ?>
                                    </small>

                                </td>



                                <!-- Property -->

                                <td>

                                    <strong>
                                        <?= htmlspecialchars(
                                            $payment['property_name']
                                            ?? 'Unknown Property'
                                        ) ?>
                                    </strong>

                                    <small>
                                        <?= htmlspecialchars(
                                            $payment['location'] ?? ''
                                        ) ?>
                                    </small>

                                </td>



                                <!-- Landlord -->

                                <td>

                                    <?= htmlspecialchars($landlordName) ?>

                                </td>



                                <!-- Amount -->

                                <td class="amount">

                                    Rs.
                                    <?= number_format(
                                        (float)$payment['amount'],
                                        2
                                    ) ?>

                                </td>



                                <!-- Payment Method -->

                                <td>

                                    <span class="method">

                                        <?= htmlspecialchars(
                                            $payment['payment_method']
                                        ) ?>

                                    </span>

                                </td>



                                <!-- Status -->

                                <td>

                                    <span
                                        class="status <?= htmlspecialchars(
                                            $statusClass
                                        ) ?>"
                                    >

                                        <?= htmlspecialchars(
                                            $paymentStatus
                                        ) ?>

                                    </span>

                                </td>



                                <!-- Transaction ID -->

                                <td>

                                    <?php if (!empty($payment['transaction_id'])): ?>

                                        <span class="transaction">

                                            <?= htmlspecialchars(
                                                $payment['transaction_id']
                                            ) ?>

                                        </span>

                                    <?php else: ?>

                                        <span class="no-data">
                                            No transaction ID
                                        </span>

                                    <?php endif; ?>

                                </td>



                                <!-- Payment Date -->

                                <td>

                                    <?= htmlspecialchars(
                                        $payment['payment_date']
                                    ) ?>

                                </td>


                            </tr>


                        <?php endwhile; ?>


                    <?php else: ?>


                        <tr>

                            <td
                                colspan="10"
                                class="empty"
                            >

                                No payments found.

                            </td>

                        </tr>


                    <?php endif; ?>


                    </tbody>


                </table>


            </div>



            <!-- NOTE -->

            <p class="note">

                This page is for administrative monitoring.
                Payment processing and payment status changes are handled
                through the appropriate rental workflow.

            </p>


        </section>


    </main>


</div>


</body>

</html>