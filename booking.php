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

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'tenant') {

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

$firstname = $_SESSION['firstname'] ?? '';
$lastname  = $_SESSION['lastname'] ?? '';


/* =========================================
   MESSAGE VARIABLES
========================================= */

$success = "";
$error = "";


/* =========================================
   HANDLE BOOKING
========================================= */

if (isset($_POST['book'])) {

    /* Get form values */

    $house_id = intval($_POST['house_id'] ?? 0);

    $booking_date = $_POST['booking_date'] ?? '';

    $move_in_date = $_POST['move_in_date'] ?? '';

    $message = trim($_POST['message'] ?? '');


    /* =====================================
       BASIC VALIDATION
    ===================================== */

    if ($house_id <= 0 || empty($booking_date) || empty($move_in_date)) {

        $error = "Please fill in all required fields.";

    }

    elseif ($move_in_date < $booking_date) {

        $error = "Move-in date cannot be before the booking date.";

    }

    else {

        /* =================================
           CHECK HOUSE EXISTS
        ================================= */

        /*
           Change `houses` column names here
           if your actual houses table uses
           different names.
        */

        $house_check = mysqli_prepare(
            $conn,
            "SELECT id FROM houses WHERE id = ?"
        );


        mysqli_stmt_bind_param(
            $house_check,
            "i",
            $house_id
        );


        mysqli_stmt_execute($house_check);


        $house_result = mysqli_stmt_get_result($house_check);


        if (mysqli_num_rows($house_result) == 0) {

            $error = "Selected house does not exist.";

        }

        else {

            /* =================================
               CHECK EXISTING BOOKING
            ================================= */

            $check_booking = mysqli_prepare(
                $conn,
                "SELECT id
                 FROM bookings
                 WHERE tenant_id = ?
                 AND house_id = ?
                 AND status IN ('Pending', 'Approved')
                 LIMIT 1"
            );


            mysqli_stmt_bind_param(
                $check_booking,
                "ii",
                $tenant_id,
                $house_id
            );


            mysqli_stmt_execute($check_booking);


            $booking_result =
                mysqli_stmt_get_result($check_booking);


            if (mysqli_num_rows($booking_result) > 0) {

                $error =
                    "You already have an active booking for this house.";

            }

            else {

                /* =============================
                   INSERT BOOKING
                ============================= */

                $status = "Pending";


                $insert = mysqli_prepare(
                    $conn,
                    "INSERT INTO bookings
                    (
                        tenant_id,
                        house_id,
                        booking_date,
                        move_in_date,
                        message,
                        status
                    )
                    VALUES (?, ?, ?, ?, ?, ?)"
                );


                mysqli_stmt_bind_param(
                    $insert,
                    "iissss",
                    $tenant_id,
                    $house_id,
                    $booking_date,
                    $move_in_date,
                    $message,
                    $status
                );


                if (mysqli_stmt_execute($insert)) {

                    $success =
                        "Booking submitted successfully! Your booking is now pending approval.";

                }

                else {

                    $error =
                        "Failed to submit booking: "
                        . mysqli_error($conn);

                }

            }

        }

    }

}


/* =========================================
   GET AVAILABLE HOUSES
========================================= */

$houses = mysqli_query(
    $conn,
    "SELECT id, house_name
     FROM houses
     WHERE status = 'Available'
     ORDER BY id DESC"
);


?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Booking - HRMS</title>


    <!-- YOUR BOOKING CSS -->

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


        <a
            href="booking.php"
            class="active"
        >
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

        <?php

        echo htmlspecialchars(
            $firstname . " " . $lastname
        );

        ?>

    </p>



    <!-- =====================================
         SUCCESS MESSAGE
    ====================================== -->

    <?php if (!empty($success)) { ?>

        <div class="success-message">

            <?php echo htmlspecialchars($success); ?>

        </div>

    <?php } ?>



    <!-- =====================================
         ERROR MESSAGE
    ====================================== -->

    <?php if (!empty($error)) { ?>

        <div class="error-message">

            <?php echo htmlspecialchars($error); ?>

        </div>

    <?php } ?>



    <!-- =====================================
         BOOKING FORM
    ====================================== -->

    <form
        action=""
        method="POST"
    >


        <!-- HOUSE -->

        <label for="house_id">

            Select House

        </label>


        <select
            name="house_id"
            id="house_id"
            required
        >


            <option value="">

                Select an available house

            </option>


            <?php

            if ($houses && mysqli_num_rows($houses) > 0) {

                while ($house = mysqli_fetch_assoc($houses)) {

            ?>

                    <option
                        value="<?php echo $house['id']; ?>"
                    >

                        <?php

                        echo htmlspecialchars(
                            $house['house_name']
                        );

                        ?>

                    </option>

            <?php

                }

            }

            else {

            ?>

                <option value="">

                    No available houses

                </option>

            <?php

            }

            ?>


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



        <!-- MOVE-IN DATE -->

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



    <!-- =====================================
         BACK BUTTON
    ====================================== -->

    <a
        href="tenantdashboard.php"
        class="back-btn"
    >

        ← Back to Dashboard

    </a>


</div>



<!-- =========================================
     DATE VALIDATION
========================================= -->

<script>

const bookingDate =
    document.getElementById("booking_date");

const moveInDate =
    document.getElementById("move_in_date");


/* Set today's date as minimum booking date */

const today =
    new Date().toISOString().split("T")[0];


bookingDate.min = today;

moveInDate.min = today;


/* Make move-in date follow booking date */

bookingDate.addEventListener(
    "change",
    function () {

        moveInDate.min = bookingDate.value;

        if (
            moveInDate.value &&
            moveInDate.value < bookingDate.value
        ) {

            moveInDate.value = "";

        }

    }
);

</script>


</body>

</html>