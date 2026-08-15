<?php

session_start();

// Check whether the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Only landlords can access this page
if ($_SESSION['role'] !== 'landlord') {
    header("Location: login.php");
    exit();
}

$firstname = $_SESSION['firstname'];
$lastname = $_SESSION['lastname'];

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Landlord Dashboard - HRMS</title>

    <!-- Landlord CSS -->
    <link rel="stylesheet" href="Assets/css/landlord_style.css">

</head>

<body>

    <!-- =========================
         HEADER
    ========================== -->

    <header class="header">

        <div class="logo">
            House Rental Management System
        </div>

        <div class="header-right">

            <span class="user-name">
                <?php echo htmlspecialchars($firstname . " " . $lastname); ?>
            </span>

            <a href="logout.php" class="logout">
                Logout
            </a>

        </div>

    </header>


    <!-- =========================
         MAIN CONTAINER
    ========================== -->

    <main class="container">


        <!-- WELCOME SECTION -->

        <section class="welcome">

            <h1>
                Welcome, <?php echo htmlspecialchars($firstname); ?>!
            </h1>

            <p>
                Welcome to your Landlord Dashboard.
                Manage your properties, bookings and tenants from here.
            </p>

        </section>


        <!-- =========================
             DASHBOARD CARDS
        ========================== -->

        <section class="cards">


            <!-- PROPERTIES -->

            <div class="card">

                <div class="card-icon">
                    🏠
                </div>

                <h3>
                    My Properties
                </h3>

                <p>
                    Add, view and manage your rental properties.
                </p>

                <a href="properties.php">
                    Manage Properties
                </a>

            </div>


            <!-- BOOKINGS -->

            <div class="card">

                <div class="card-icon">
                    📅
                </div>

                <h3>
                    Booking Requests
                </h3>

                <p>
                    View and manage booking requests from tenants.
                </p>

                <a href="booking.php">
                    View Bookings
                </a>

            </div>


            <!-- TENANTS -->

            <div class="card">

                <div class="card-icon">
                    👥
                </div>

                <h3>
                    Tenants
                </h3>

                <p>
                    View and manage tenants renting your properties.
                </p>

                <a href="tenants.php">
                    View Tenants
                </a>

            </div>


            <!-- PAYMENTS -->

            <div class="card">

                <div class="card-icon">
                    💰
                </div>

                <h3>
                    Rent & Payments
                </h3>

                <p>
                    View rent and payment records.
                </p>

                <a href="payments.php">
                    View Payments
                </a>

            </div>


            <!-- PROFILE -->

            <div class="card">

                <div class="card-icon">
                    👤
                </div>

                <h3>
                    My Profile
                </h3>

                <p>
                    View and update your landlord information.
                </p>

                <a href="profile.php">
                    My Profile
                </a>

            </div>


            <!-- REPORTS -->

            <div class="card">

                <div class="card-icon">
                    📊
                </div>

                <h3>
                    Reports
                </h3>

                <p>
                    View rental and payment reports.
                </p>

                <a href="#">
                    View Reports
                </a>

            </div>

        </section>

    </main>

</body>

</html>