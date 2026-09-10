<?php

session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . "/config/database/db.php";


/* =========================================
   TENANT ACCESS ONLY
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
   GET TENANT BOOKINGS
========================================= */

$sql = "
    SELECT
        b.booking_id,
        b.booking_date,
        b.move_in_date,
        b.duration,
        b.occupants,
        b.message,
        b.booking_status,

        p.property_id,
        p.property_name,
        p.property_type,
        p.location,
        p.monthly_rent

    FROM bookings b

    INNER JOIN properties p
        ON b.property_id = p.property_id

    WHERE b.tenant_id = ?

    ORDER BY b.created_at DESC
";


$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $tenant_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>My Bookings - HRMS</title>

    <!-- CSS -->

    <link
        rel="stylesheet"
        href="assets/css/tenant_bookings_style.css"
    >

</head>


<body>


<div class="page-container">


    <!-- ================= HEADER ================= -->

    <header class="page-header">

        <div>

            <h1>
                My Bookings
            </h1>

            <p>
                View and manage your rental booking requests.
            </p>

        </div>


        <a
            href="properties.php"
            class="browse-btn"
        >
            Browse Properties
        </a>

    </header>


    <!-- ================= MAIN CONTENT ================= -->

    <main>

        <section class="booking-section">


            <div class="section-header">

                <div>

                    <h2>
                        Booking Requests
                    </h2>

                    <p>
                        Your recent rental requests
                    </p>

                </div>

            </div>


            <!-- ================= TABLE ================= -->

            <div class="table-container">

                <table>

                    <thead>

                        <tr>

                            <th>
                                Property
                            </th>

                            <th>
                                Location
                            </th>

                            <th>
                                Booking Date
                            </th>

                            <th>
                                Move-in Date
                            </th>

                            <th>
                                Duration
                            </th>

                            <th>
                                Occupants
                            </th>

                            <th>
                                Rent
                            </th>

                            <th>
                                Status
                            </th>

                            <th>
                                Action
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php if (mysqli_num_rows($result) > 0) { ?>


                        <?php while ($booking = mysqli_fetch_assoc($result)) { ?>


                            <tr>


                                <!-- PROPERTY -->

                                <td>

                                    <strong>

                                        <?php
                                        echo htmlspecialchars(
                                            $booking['property_name']
                                        );
                                        ?>

                                    </strong>

                                    <span class="property-type">

                                        <?php
                                        echo htmlspecialchars(
                                            $booking['property_type']
                                        );
                                        ?>

                                    </span>

                                </td>


                                <!-- LOCATION -->

                                <td>

                                    <?php
                                    echo htmlspecialchars(
                                        $booking['location']
                                    );
                                    ?>

                                </td>


                                <!-- BOOKING DATE -->

                                <td>

                                    <?php
                                    echo date(
                                        "d M Y",
                                        strtotime(
                                            $booking['booking_date']
                                        )
                                    );
                                    ?>

                                </td>


                                <!-- MOVE-IN DATE -->

                                <td>

                                    <?php
                                    echo date(
                                        "d M Y",
                                        strtotime(
                                            $booking['move_in_date']
                                        )
                                    );
                                    ?>

                                </td>


                                <!-- DURATION -->

                                <td>

                                    <?php
                                    echo $booking['duration'];
                                    ?>

                                    Month(s)

                                </td>


                                <!-- OCCUPANTS -->

                                <td>

                                    <?php
                                    echo $booking['occupants'];
                                    ?>

                                </td>


                                <!-- RENT -->

                                <td>

                                    Rs.
                                    <?php
                                    echo number_format(
                                        $booking['monthly_rent'],
                                        2
                                    );
                                    ?>

                                </td>


                                <!-- STATUS -->

                                <td>

                                    <span
                                        class="status
                                        <?php
                                        echo strtolower(
                                            $booking['booking_status']
                                        );
                                        ?>"
                                    >

                                        <?php
                                        echo htmlspecialchars(
                                            $booking['booking_status']
                                        );
                                        ?>

                                    </span>

                                </td>


                                <!-- ACTION -->

                                <td>


                                    <?php

                                    if (
                                        $booking['booking_status']
                                        === 'Pending'
                                    ) {

                                    ?>

                                        <a
                                            href="edit_booking.php?id=<?php
                                            echo $booking['booking_id'];
                                            ?>"
                                            class="edit-btn"
                                        >
                                            Edit
                                        </a>


                                    <?php

                                    }

                                    elseif (
                                        $booking['booking_status']
                                        === 'Confirmed'
                                    ) {

                                    ?>

                                        <a
                                            href="payment.php?booking_id=<?php
                                            echo $booking['booking_id'];
                                            ?>"
                                            class="pay-btn"
                                        >
                                            Pay
                                        </a>


                                    <?php

                                    }

                                    else {

                                    ?>

                                        <span class="no-action">
                                            —
                                        </span>

                                    <?php

                                    }

                                    ?>


                                </td>


                            </tr>


                        <?php } ?>


                    <?php } else { ?>


                        <!-- =========================
                             EMPTY STATE
                        ========================== -->

                        <tr>

                            <td colspan="9">

                                <div class="empty-state">

                                    <div class="empty-icon">
                                        📋
                                    </div>

                                    <h3>
                                        No Booking Requests
                                    </h3>

                                    <p>
                                        You have not submitted
                                        any rental booking requests yet.
                                    </p>

                                    <a
                                        href="properties.php"
                                        class="find-btn"
                                    >
                                        Find a Property
                                    </a>

                                </div>

                            </td>

                        </tr>


                    <?php } ?>


                    </tbody>

                </table>

            </div>


        </section>


    </main>


</div>


</body>

</html>

<?php

mysqli_stmt_close($stmt);

?>