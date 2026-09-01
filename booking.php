<?php

session_start();

/* =========================================
   CHECK LOGIN
========================================= */

if (!isset($_SESSION['user_id'])) {

    header("Location: login.php");
    exit();

}


/* =========================================
   CHECK TENANT ROLE
========================================= */

if ($_SESSION['role'] !== 'tenant') {

    header("Location: index.php");
    exit();

}


/* =========================================
   DATABASE CONNECTION
========================================= */

$conn = mysqli_connect(
    "localhost",
    "root",
    "",
    "hrms"
);

if (!$conn) {

    die(
        "Database connection failed: "
        . mysqli_connect_error()
    );

}


/* =========================================
   TENANT INFORMATION
========================================= */

$tenant_id = $_SESSION['user_id'];

$firstname = $_SESSION['firstname'];
$lastname  = $_SESSION['lastname'];

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Booking - HRMS</title>

    <link
        rel="stylesheet"
        href="Assets/css/booking_style.css"
    >

</head>


<body>


<!-- =========================================
     NAVIGATION
========================================= -->

<nav>

    <div class="logo">
        HRMS
    </div>

    <div class="nav-links">

        <a href="tenantdashboard.php">
            Dashboard
        </a>

        <a href="houses.php">
            Houses
        </a>

        <a href="booking.php">
            Booking
        </a>

        <a href="rent.php">
            Rent
        </a>

        <a href="logout.php">
            Logout
        </a>

    </div>

</nav>


<!-- =========================================
     BOOKING CONTAINER
========================================= -->

<div class="booking-container">


    <h1>
        Book a House
    </h1>


    <p class="welcome">

        Welcome,
        <?php echo htmlspecialchars($firstname . " " . $lastname); ?>

    </p>


    <!-- =====================================
         BOOKING FORM
    ====================================== -->

    <form
        action=""
        method="POST"
    >


        <!-- HOUSE -->

        <label for="house">
            Select House
        </label>

        <select
            name="house"
            id="house"
            required
        >

            <option value="">
                Select a house
            </option>

            <option value="1">
                House 1
            </option>

            <option value="2">
                House 2
            </option>

            <option value="3">
                House 3
            </option>

        </select>


        <!-- BOOKING DATE -->

        <label for="booking_date">
            Booking Date
        </label>

        <input
            type="date"
            name="booking_date"
            id="booking_date"
            required
        >


        <!-- MOVE IN DATE -->

        <label for="move_in_date">
            Move-in Date
        </label>

        <input
            type="date"
            name="move_in_date"
            id="move_in_date"
            required
        >


        <!-- MESSAGE -->

        <label for="message">
            Message
        </label>

        <textarea
            name="message"
            id="message"
            placeholder="Enter any additional information"
            rows="4"
        ></textarea>


        <!-- BUTTON -->

        <button
            type="submit"
            name="book"
        >
            Submit Booking
        </button>


    </form>


    <!-- BACK -->

    <a
        href="tenantdashboard.php"
        class="back-btn"
    >
        ← Back to Dashboard
    </a>


</div>


</body>

</html>