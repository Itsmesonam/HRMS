<?php

session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . "/config/database/db.php";


/* =========================================
   TENANT LOGIN CHECK
========================================= */

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'tenant') {
    header("Location: login.php");
    exit();
}

$tenant_id = $_SESSION['user_id'];


/* =========================================
   GET BOOKING ID
========================================= */

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: tenant_bookings.php");
    exit();
}

$booking_id = intval($_GET['id']);

$message = "";
$message_type = "";


/* =========================================
   FETCH BOOKING
========================================= */

$sql = "
    SELECT
        b.booking_id,
        b.property_id,
        b.booking_date,
        b.move_in_date,
        b.duration,
        b.occupants,
        b.message,
        b.booking_status,

        p.property_name,
        p.property_type,
        p.location,
        p.monthly_rent,
        p.max_occupants

    FROM bookings b

    INNER JOIN properties p
        ON b.property_id = p.property_id

    WHERE b.booking_id = ?
      AND b.tenant_id = ?

    LIMIT 1
";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $booking_id,
    $tenant_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$booking = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


/* =========================================
   BOOKING NOT FOUND
========================================= */

if (!$booking) {
    die("Booking request not found.");
}


/* =========================================
   ONLY PENDING BOOKING CAN BE EDITED
========================================= */

if ($booking['booking_status'] !== 'Pending') {
    die("Only pending booking requests can be edited.");
}


/* =========================================
   UPDATE BOOKING
========================================= */

if (isset($_POST['update_booking'])) {

    $move_in_date = trim($_POST['move_in_date']);
    $duration     = intval($_POST['duration']);
    $occupants    = intval($_POST['occupants']);
    $user_message = trim($_POST['message']);


    /* -------------------------
       VALIDATION
    ------------------------- */

    if (
        empty($move_in_date) ||
        $duration <= 0 ||
        $occupants <= 0
    ) {

        $message = "Please fill all required fields correctly.";
        $message_type = "error";

    } elseif (
        strtotime($move_in_date) < strtotime($booking['booking_date'])
    ) {

        $message = "Move-in date cannot be before the booking date.";
        $message_type = "error";

    } elseif (
        $occupants > $booking['max_occupants']
    ) {

        $message =
            "Maximum allowed occupants for this property are "
            . $booking['max_occupants'] . ".";

        $message_type = "error";

    } else {

        /* -------------------------
           UPDATE
        ------------------------- */

        $update_sql = "
            UPDATE bookings

            SET
                move_in_date = ?,
                duration = ?,
                occupants = ?,
                message = ?

            WHERE booking_id = ?
              AND tenant_id = ?
              AND booking_status = 'Pending'
        ";

        $update_stmt = mysqli_prepare($conn, $update_sql);

        mysqli_stmt_bind_param(
            $update_stmt,
            "siisii",
            $move_in_date,
            $duration,
            $occupants,
            $user_message,
            $booking_id,
            $tenant_id
        );

        if (mysqli_stmt_execute($update_stmt)) {

            mysqli_stmt_close($update_stmt);

            header("Location: tenant_bookings.php?updated=1");
            exit();

        } else {

            $message =
                "Failed to update booking: "
                . mysqli_error($conn);

            $message_type = "error";
        }

        mysqli_stmt_close($update_stmt);
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

    <title>Edit Booking - HRMS</title>

    <link
        rel="stylesheet"
        href="assets/css/edit_booking_style.css"
    >

</head>


<body>


<div class="page-container">


    <!-- HEADER -->

    <header class="page-header">

        <div>

            <h1>Edit Booking Request</h1>

            <p>
                Update your pending rental request.
            </p>

        </div>

        <a
            href="tenant_bookings.php"
            class="back-btn"
        >
            ← Back to My Bookings
        </a>

    </header>


    <!-- MAIN -->

    <main>


        <!-- PROPERTY INFORMATION -->

        <section class="property-card">

            <h2>
                <?php echo htmlspecialchars(
                    $booking['property_name']
                ); ?>
            </h2>

            <div class="property-info">

                <div>
                    <strong>Property Type</strong>
                    <span>
                        <?php echo htmlspecialchars(
                            $booking['property_type']
                        ); ?>
                    </span>
                </div>


                <div>
                    <strong>Location</strong>
                    <span>
                        <?php echo htmlspecialchars(
                            $booking['location']
                        ); ?>
                    </span>
                </div>


                <div>
                    <strong>Monthly Rent</strong>
                    <span>
                        Rs.
                        <?php echo number_format(
                            $booking['monthly_rent'],
                            2
                        ); ?>
                    </span>
                </div>


                <div>
                    <strong>Booking Date</strong>
                    <span>
                        <?php echo htmlspecialchars(
                            $booking['booking_date']
                        ); ?>
                    </span>
                </div>

            </div>

        </section>


        <!-- MESSAGE -->

        <?php if (!empty($message)): ?>

            <div class="message <?php echo $message_type; ?>">

                <?php echo htmlspecialchars($message); ?>

            </div>

        <?php endif; ?>


        <!-- FORM -->

        <section class="form-card">

            <div class="form-title">

                <h2>Update Request</h2>

                <p>
                    You can edit your request while it is pending.
                </p>

            </div>


            <form method="POST">


                <!-- MOVE IN DATE -->

                <div class="form-group">

                    <label for="move_in_date">
                        Move-in Date
                    </label>

                    <input
                        type="date"
                        id="move_in_date"
                        name="move_in_date"
                        value="<?php echo htmlspecialchars(
                            $booking['move_in_date']
                        ); ?>"
                        required
                    >

                </div>


                <!-- DURATION -->

                <div class="form-group">

                    <label for="duration">
                        Rental Duration (Months)
                    </label>

                    <input
                        type="number"
                        id="duration"
                        name="duration"
                        min="1"
                        value="<?php echo htmlspecialchars(
                            $booking['duration']
                        ); ?>"
                        required
                    >

                </div>


                <!-- OCCUPANTS -->

                <div class="form-group">

                    <label for="occupants">
                        Number of Occupants
                    </label>

                    <input
                        type="number"
                        id="occupants"
                        name="occupants"
                        min="1"
                        max="<?php echo $booking['max_occupants']; ?>"
                        value="<?php echo htmlspecialchars(
                            $booking['occupants']
                        ); ?>"
                        required
                    >

                    <small>
                        Maximum occupants:
                        <?php echo $booking['max_occupants']; ?>
                    </small>

                </div>


                <!-- MESSAGE -->

                <div class="form-group">

                    <label for="message">
                        Message / Note
                    </label>

                    <textarea
                        id="message"
                        name="message"
                        rows="5"
                        placeholder="Write any additional information..."
                    ><?php echo htmlspecialchars(
                        $booking['message']
                    ); ?></textarea>

                </div>


                <!-- BUTTONS -->

                <div class="form-actions">

                    <a
                        href="tenant_bookings.php"
                        class="cancel-btn"
                    >
                        Cancel
                    </a>

                    <button
                        type="submit"
                        name="update_booking"
                        class="update-btn"
                    >
                        Update Booking
                    </button>

                </div>


            </form>

        </section>


    </main>

</div>


</body>

</html>