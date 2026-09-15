<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

require_once __DIR__ . "/config/database/db.php";

/*-- Landlord login check */

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'landlord') {
    header("Location: login.php");
    exit();
}

$landlord_id = (int) $_SESSION['user_id'];

/*-- Automatically expire properties older than 1 month */

$expire_sql = "
    UPDATE properties
    SET property_status = 'Expired'
    WHERE landlord_id = ?
      AND property_status = 'Available'
      AND created_at <= DATE_SUB(NOW(), INTERVAL 1 MONTH)
";

if ($stmt = mysqli_prepare($conn, $expire_sql)) {
    mysqli_stmt_bind_param($stmt, "i", $landlord_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

/*-- Dashboard statistics */

$totalHouses = 0;
$availableHouses = 0;
$occupiedHouses = 0;
$totalTenants = 0;
$pendingBookings = 0;
$monthlyRent = 0;

$sql = "
    SELECT
        COUNT(*) AS total_houses,
        SUM(property_status = 'Available') AS available_houses,
        SUM(property_status = 'Occupied') AS occupied_houses
    FROM properties
    WHERE landlord_id = ?
";

if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $landlord_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        $totalHouses = (int) ($row['total_houses'] ?? 0);
        $availableHouses = (int) ($row['available_houses'] ?? 0);
        $occupiedHouses = (int) ($row['occupied_houses'] ?? 0);
    }

    mysqli_stmt_close($stmt);
}

/*-- Pending rental requests */

$sql = "
    SELECT COUNT(*) AS pending
    FROM bookings b
    INNER JOIN properties p
        ON b.property_id = p.property_id
    WHERE p.landlord_id = ?
      AND b.booking_status = 'Pending'
";

if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $landlord_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        $pendingBookings = (int) ($row['pending'] ?? 0);
    }

    mysqli_stmt_close($stmt);
}

/*-- Active tenants */

$sql = "
    SELECT COUNT(DISTINCT b.tenant_id) AS tenant_count
    FROM bookings b
    INNER JOIN properties p
        ON b.property_id = p.property_id
    WHERE p.landlord_id = ?
      AND b.booking_status = 'Confirmed'
";

if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $landlord_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        $totalTenants = (int) ($row['tenant_count'] ?? 0);
    }

    mysqli_stmt_close($stmt);
}

/*-- Expected monthly rent from occupied properties */

$sql = "
    SELECT COALESCE(SUM(monthly_rent), 0) AS monthly_rent
    FROM properties
    WHERE landlord_id = ?
      AND property_status = 'Occupied'
";

if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $landlord_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        $monthlyRent = (float) ($row['monthly_rent'] ?? 0);
    }

    mysqli_stmt_close($stmt);
}

/*-- Landlord email for the dashboard header */

$landlordEmail = "Landlord";

$sql = "SELECT email FROM users WHERE id = ? LIMIT 1";

if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $landlord_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        $landlordEmail = $row['email'];
    }

    mysqli_stmt_close($stmt);
}

/*-- My houses */

$myHouses = [];

$sql = "
    SELECT
        p.property_id,
        p.property_name,
        p.monthly_rent,
        p.property_status,
        (
            SELECT u.email
            FROM bookings b2
            INNER JOIN users u ON b2.tenant_id = u.id
            WHERE b2.property_id = p.property_id
              AND b2.booking_status = 'Confirmed'
            ORDER BY b2.booking_id DESC
            LIMIT 1
        ) AS tenant_email
    FROM properties p
    WHERE p.landlord_id = ?
    ORDER BY p.created_at DESC
    LIMIT 5
";

if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $landlord_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        $myHouses[] = $row;
    }

    mysqli_stmt_close($stmt);
}

/*-- Recent rental requests */

$recentBookings = [];

$sql = "
    SELECT
        b.booking_id,
        b.booking_date,
        b.booking_status,
        p.property_name,
        u.email AS tenant_email
    FROM bookings b
    INNER JOIN properties p
        ON b.property_id = p.property_id
    INNER JOIN users u
        ON b.tenant_id = u.id
    WHERE p.landlord_id = ?
    ORDER BY b.created_at DESC
    LIMIT 5
";

if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $landlord_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        $recentBookings[] = $row;
    }

    mysqli_stmt_close($stmt);
}

/*-- Payment summary */

$paidAmount = 0;
$pendingAmount = 0;

$sql = "
    SELECT
        COALESCE(SUM(CASE WHEN py.payment_status = 'Completed' THEN py.amount ELSE 0 END), 0) AS paid_amount,
        COALESCE(SUM(CASE WHEN py.payment_status = 'Pending' THEN py.amount ELSE 0 END), 0) AS pending_amount
    FROM payments py
    WHERE py.landlord_id = ?
";

if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $landlord_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        $paidAmount = (float) ($row['paid_amount'] ?? 0);
        $pendingAmount = (float) ($row['pending_amount'] ?? 0);
    }

    mysqli_stmt_close($stmt);
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

            <a href="manage_property.php">
                <span class="material-symbols-outlined">
                    home
                </span>

                <h3>
                    My Houses
                </h3>

            </a>


     <!-- Add Property -->

     <a href="add_property.php">

      <span class="material-symbols-outlined">
        add_home
      </span>

     <h3>
        Add Property
      </h3>

  
            <!-- Bookings -->

            <a href="rental_requests.php">

                <span class="material-symbols-outlined">
                    calendar_month
                </span>

                <h3>
                    Rental Requests
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
                        <?php echo htmlspecialchars($landlordEmail); ?>
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


                    <button type="button"
                            onclick="window.location.href='manage_property.php'">
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

                    <?php if (!empty($myHouses)): ?>

                        <?php foreach ($myHouses as $house): ?>

                        <tr>

                            <td>
                                <?php echo htmlspecialchars($house['property_name']); ?>
                            </td>

                            <td>
                                Rs. <?php echo number_format((float)$house['monthly_rent']); ?>
                            </td>

                            <td>
                                <?php
                                echo !empty($house['tenant_email'])
                                    ? htmlspecialchars($house['tenant_email'])
                                    : 'Vacant';
                                ?>
                            </td>

                            <td>
                                <?php
                                $statusClass = strtolower($house['property_status']);
                                ?>

                                <span class="status <?php echo htmlspecialchars($statusClass); ?>">
                                    <?php echo htmlspecialchars($house['property_status']); ?>
                                </span>

                            </td>

                        </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>

                            <td>
                                No houses yet
                            </td>

                            <td>-</td>
                            <td>-</td>

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



            <!-- Recent Bookings -->

            <div class="panel">


                <div class="panel-header">

                    <h2>
                        Recent Bookings
                    </h2>


                    <button type="button"
                            onclick="window.location.href='rental_requests.php'">
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

                    <?php if (!empty($recentBookings)): ?>

                        <?php foreach ($recentBookings as $booking): ?>

                        <tr>

                            <td>
                                <?php echo htmlspecialchars($booking['tenant_email']); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($booking['property_name']); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($booking['booking_date']); ?>
                            </td>

                            <td>

                                <span class="status <?php echo strtolower($booking['booking_status']); ?>">
                                    <?php echo htmlspecialchars($booking['booking_status']); ?>
                                </span>

                            </td>

                        </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>

                            <td>
                                No bookings
                            </td>

                            <td>-</td>
                            <td>-</td>

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
                        Rs. <?php echo number_format($paidAmount); ?>
                    </strong>
                </p>

                <p>
                    Pending:
                    <strong>
                        Rs. <?php echo number_format($pendingAmount); ?>
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