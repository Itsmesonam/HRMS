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

if (
    !isset($_SESSION['role']) ||
    strtolower($_SESSION['role']) !== 'tenant'
) {
    header("Location: login.php");
    exit();
}

$tenant_id = $_SESSION['user_id'];

$message = "";
$message_type = "";


/* =========================================
   GET BOOKING ID
========================================= */

$booking_id = isset($_GET['booking_id'])
    ? intval($_GET['booking_id'])
    : intval($_POST['booking_id'] ?? 0);

if ($booking_id <= 0) {
    header("Location: tenant_bookings.php");
    exit();
}


/* =========================================
   PROCESS PAYMENT
========================================= */

if (isset($_POST['make_payment'])) {

    $payment_method = $_POST['payment_method'] ?? '';
    $transaction_id = trim($_POST['transaction_id'] ?? '');
    $notes = trim($_POST['notes'] ?? '');


    /* =====================================
       VALIDATE PAYMENT METHOD
    ===================================== */

    $allowed_methods = [
        'Cash',
        'Card',
        'eSewa',
        'Khalti',
        'Bank Transfer'
    ];

    if (!in_array($payment_method, $allowed_methods)) {

        $message = "Please select a valid payment method.";
        $message_type = "error";

    } else {


        /* =====================================
           GET BOOKING
        ===================================== */

        $sql = "
            SELECT
                b.booking_id,
                b.property_id,
                b.tenant_id,
                b.booking_status,

                p.property_name,
                p.location,
                p.monthly_rent,
                p.landlord_id

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


        if (!$booking) {

            $message = "Booking not found.";
            $message_type = "error";

        } elseif (
            $booking['booking_status'] !== 'Confirmed'
        ) {

            $message =
                "Payment is available only for confirmed bookings.";

            $message_type = "error";

        } else {


            /* =====================================
               CHECK EXISTING PAYMENT
            ===================================== */

            $check_sql = "
                SELECT payment_id, payment_status
                FROM payments
                WHERE booking_id = ?
                  AND tenant_id = ?
                ORDER BY payment_id DESC
                LIMIT 1
            ";

            $stmt = mysqli_prepare(
                $conn,
                $check_sql
            );

            mysqli_stmt_bind_param(
                $stmt,
                "ii",
                $booking_id,
                $tenant_id
            );

            mysqli_stmt_execute($stmt);

            $payment_result =
                mysqli_stmt_get_result($stmt);

            $existing_payment =
                mysqli_fetch_assoc($payment_result);

            mysqli_stmt_close($stmt);


            if (
                $existing_payment &&
                $existing_payment['payment_status']
                === 'Completed'
            ) {

                $message =
                    "Payment for this booking has already been completed.";

                $message_type = "error";

            } else {


                /* =====================================
                   INSERT PAYMENT
                ===================================== */

                $amount =
                    $booking['monthly_rent'];

                $payment_status = 'Completed';


                $insert_sql = "
                    INSERT INTO payments (
                        booking_id,
                        property_id,
                        tenant_id,
                        landlord_id,
                        amount,
                        payment_method,
                        payment_status,
                        transaction_id,
                        notes
                    )

                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ";

                $stmt = mysqli_prepare(
                    $conn,
                    $insert_sql
                );

                mysqli_stmt_bind_param(
                    $stmt,
                    "iiiidssss",
                    $booking_id,
                    $booking['property_id'],
                    $tenant_id,
                    $booking['landlord_id'],
                    $amount,
                    $payment_method,
                    $payment_status,
                    $transaction_id,
                    $notes
                );


                if (mysqli_stmt_execute($stmt)) {

                    mysqli_stmt_close($stmt);

                    header(
                        "Location: tenant_payments.php?success=1"
                    );

                    exit();

                } else {

                    $message =
                        "Payment failed: " .
                        mysqli_error($conn);

                    $message_type = "error";

                    mysqli_stmt_close($stmt);
                }
            }
        }
    }
}


/* =========================================
   GET BOOKING INFORMATION
========================================= */

$sql = "
    SELECT
        b.booking_id,
        b.booking_date,
        b.move_in_date,
        b.duration,
        b.occupants,
        b.booking_status,

        p.property_id,
        p.property_name,
        p.property_type,
        p.location,
        p.monthly_rent,
        p.image

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


if (!$booking) {

    header("Location: tenant_bookings.php");
    exit();

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

    <title>Make Payment | HRMS</title>

    <link
        rel="stylesheet"
        href="assets/css/payment_style.css"
    >

</head>


<body>


<div class="page-container">


    <!-- =================================
         HEADER
    ================================== -->

    <header class="page-header">

        <div>

            <h1>Make Payment</h1>

            <p>
                Complete your rental payment.
            </p>

        </div>


        <a
            href="tenant_bookings.php"
            class="back-btn"
        >
            ← My Bookings
        </a>

    </header>


    <main>


        <?php if (!empty($message)): ?>

            <div class="message <?php echo $message_type; ?>">

                <?php
                echo htmlspecialchars($message);
                ?>

            </div>

        <?php endif; ?>


        <div class="payment-layout">


            <!-- =================================
                 BOOKING DETAILS
            ================================== -->

            <div class="booking-card">

                <h2>
                    Booking Details
                </h2>


                <div class="property-preview">

                    <?php if (!empty($booking['image'])): ?>

                        <img
                            src="uploads/properties/<?php
                            echo htmlspecialchars(
                                $booking['image']
                            );
                            ?>"
                            alt="Property"
                        >

                    <?php else: ?>

                        <div class="image-placeholder">
                            🏠
                        </div>

                    <?php endif; ?>


                    <div>

                        <h3>
                            <?php
                            echo htmlspecialchars(
                                $booking['property_name']
                            );
                            ?>
                        </h3>

                        <p>
                            <?php
                            echo htmlspecialchars(
                                $booking['location']
                            );
                            ?>
                        </p>

                    </div>

                </div>


                <div class="details">


                    <div class="detail-row">

                        <span>
                            Booking ID
                        </span>

                        <strong>
                            #<?php
                            echo $booking['booking_id'];
                            ?>
                        </strong>

                    </div>


                    <div class="detail-row">

                        <span>
                            Property Type
                        </span>

                        <strong>
                            <?php
                            echo htmlspecialchars(
                                $booking['property_type']
                            );
                            ?>
                        </strong>

                    </div>


                    <div class="detail-row">

                        <span>
                            Move-in Date
                        </span>

                        <strong>
                            <?php
                            echo htmlspecialchars(
                                $booking['move_in_date']
                            );
                            ?>
                        </strong>

                    </div>


                    <div class="detail-row">

                        <span>
                            Duration
                        </span>

                        <strong>
                            <?php
                            echo $booking['duration'];
                            ?> month(s)
                        </strong>

                    </div>


                    <div class="detail-row">

                        <span>
                            Occupants
                        </span>

                        <strong>
                            <?php
                            echo $booking['occupants'];
                            ?>
                        </strong>

                    </div>


                    <div class="detail-row total">

                        <span>
                            Monthly Rent
                        </span>

                        <strong>
                            Rs.
                            <?php
                            echo number_format(
                                $booking['monthly_rent'],
                                2
                            );
                            ?>
                        </strong>

                    </div>

                </div>

            </div>


            <!-- =================================
                 PAYMENT FORM
            ================================== -->

            <div class="payment-card">

                <h2>
                    Payment Information
                </h2>


                <form method="POST">


                    <input
                        type="hidden"
                        name="booking_id"
                        value="<?php
                        echo $booking['booking_id'];
                        ?>"
                    >


                    <!-- AMOUNT -->

                    <div class="form-group">

                        <label>
                            Payment Amount
                        </label>

                        <div class="amount-box">

                            Rs.
                            <?php
                            echo number_format(
                                $booking['monthly_rent'],
                                2
                            );
                            ?>

                        </div>

                    </div>


                    <!-- PAYMENT METHOD -->

                    <div class="form-group">

                        <label>
                            Payment Method *
                        </label>

                        <select
                            name="payment_method"
                            id="payment_method"
                            required
                        >

                            <option value="">
                                Select Payment Method
                            </option>

                            <option value="Cash">
                                Cash
                            </option>

                            <option value="Card">
                                Card
                            </option>

                            <option value="eSewa">
                                eSewa
                            </option>

                            <option value="Khalti">
                                Khalti
                            </option>

                            <option value="Bank Transfer">
                                Bank Transfer
                            </option>

                        </select>

                    </div>


                    <!-- TRANSACTION ID -->

                    <div class="form-group">

                        <label>
                            Transaction ID
                        </label>

                        <input
                            type="text"
                            name="transaction_id"
                            placeholder="Enter transaction ID if available"
                        >

                        <small>
                            Required for online payments if applicable.
                        </small>

                    </div>


                    <!-- NOTES -->

                    <div class="form-group">

                        <label>
                            Notes
                        </label>

                        <textarea
                            name="notes"
                            rows="4"
                            placeholder="Optional payment notes..."
                        ></textarea>

                    </div>


                    <!-- BUTTON -->

                    <button
                        type="submit"
                        name="make_payment"
                        class="pay-btn"
                    >
                        Make Payment
                    </button>


                </form>

            </div>


        </div>


    </main>


</div>


</body>

</html>