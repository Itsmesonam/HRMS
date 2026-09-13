<?php

session_start();

require_once __DIR__ . "/config/database/db.php";


/*-- Landlord Login Check */

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


/*-- Landlord Role Check */

if (
    !isset($_SESSION['role']) ||
    strtolower($_SESSION['role']) !== 'landlord'
) {
    header("Location: index.php");
    exit();
}


$landlord_id = $_SESSION['user_id'];

$message = "";
$message_type = "";


/*-- Approve Booking */

if (isset($_POST['approve_booking'])) {

    $booking_id = (int) $_POST['booking_id'];

    mysqli_begin_transaction($conn);

    try {

        /*-- Check Booking Belongs to This Landlord */

        $check_stmt = mysqli_prepare(
            $conn,
            "SELECT
                b.booking_id,
                b.property_id,
                b.booking_status,
                p.property_status
             FROM bookings b
             INNER JOIN properties p
                ON b.property_id = p.property_id
             WHERE b.booking_id = ?
             AND p.landlord_id = ?
             FOR UPDATE"
        );

        mysqli_stmt_bind_param(
            $check_stmt,
            "ii",
            $booking_id,
            $landlord_id
        );

        mysqli_stmt_execute($check_stmt);

        $check_result =
            mysqli_stmt_get_result($check_stmt);

        $booking =
            mysqli_fetch_assoc($check_result);

        mysqli_stmt_close($check_stmt);


        if (!$booking) {
            throw new Exception(
                "Booking request not found."
            );
        }


        /*-- Only Pending Booking Can Be Approved */

        if (
            strtolower(
                $booking['booking_status']
            ) !== 'pending'
        ) {
            throw new Exception(
                "This booking has already been processed."
            );
        }


        /*-- Property Must Be Available */

        if (
            strtolower(
                $booking['property_status']
            ) !== 'available'
        ) {
            throw new Exception(
                "This property is no longer available."
            );
        }


        /*-- Confirm Booking */

        $update_booking = mysqli_prepare(
            $conn,
            "UPDATE bookings
             SET booking_status = 'Confirmed'
             WHERE booking_id = ?"
        );

        mysqli_stmt_bind_param(
            $update_booking,
            "i",
            $booking_id
        );

        mysqli_stmt_execute($update_booking);

        mysqli_stmt_close($update_booking);


        /*-- Mark Property Occupied */

        $update_property = mysqli_prepare(
            $conn,
            "UPDATE properties
             SET property_status = 'Occupied'
             WHERE property_id = ?"
        );

        mysqli_stmt_bind_param(
            $update_property,
            "i",
            $booking['property_id']
        );

        mysqli_stmt_execute($update_property);

        mysqli_stmt_close($update_property);


        mysqli_commit($conn);

        $message =
            "Booking request approved successfully.";

        $message_type = "success";

    } catch (Exception $e) {

        mysqli_rollback($conn);

        $message = $e->getMessage();

        $message_type = "error";
    }
}


/*-- Reject Booking */

if (isset($_POST['reject_booking'])) {

    $booking_id = (int) $_POST['booking_id'];

    $stmt = mysqli_prepare(
        $conn,
        "UPDATE bookings b
         INNER JOIN properties p
            ON b.property_id = p.property_id
         SET b.booking_status = 'Rejected'
         WHERE b.booking_id = ?
         AND p.landlord_id = ?
         AND b.booking_status = 'Pending'"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "ii",
        $booking_id,
        $landlord_id
    );

    mysqli_stmt_execute($stmt);

    if (mysqli_stmt_affected_rows($stmt) > 0) {

        $message =
            "Booking request rejected successfully.";

        $message_type = "success";

    } else {

        $message =
            "Unable to reject this booking.";

        $message_type = "error";
    }

    mysqli_stmt_close($stmt);
}


/*-- Get Landlord Booking Requests */

$query = "
    SELECT
        b.booking_id,
        b.property_id,
        b.tenant_id,
        b.booking_date,
        b.move_in_date,
        b.duration,
        b.occupants,
        b.message,
        b.booking_status,
        b.created_at,

        p.property_name,
        p.property_type,
        p.location,
        p.monthly_rent,
        p.property_status

    FROM bookings b

    INNER JOIN properties p
        ON b.property_id = p.property_id

    WHERE p.landlord_id = ?

    ORDER BY
        CASE
            WHEN b.booking_status = 'Pending'
            THEN 1
            WHEN b.booking_status = 'Confirmed'
            THEN 2
            WHEN b.booking_status = 'Rejected'
            THEN 3
            ELSE 4
        END,
        b.created_at DESC
";


$stmt = mysqli_prepare(
    $conn,
    $query
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $landlord_id
);

mysqli_stmt_execute($stmt);

$requests_result =
    mysqli_stmt_get_result($stmt);

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Rental Requests
    </title>

    <link
        rel="stylesheet"
        href="assets/css/rental_requests_style.css"
    >

</head>


<body>


<div class="page-container">


    <!-- Page Header -->

    <div class="page-header">

        <div>

            <h1>
                Rental Requests
            </h1>

            <p>
                View and manage tenant rental requests
            </p>

        </div>


        <a
            href="landlorddashboard.php"
            class="dashboard-btn"
        >
            ← Dashboard
        </a>

    </div>



    <!-- Message -->

    <?php if (!empty($message)): ?>

        <div
            class="message
            <?php echo $message_type; ?>"
        >

            <?php
            echo htmlspecialchars($message);
            ?>

        </div>

    <?php endif; ?>



    <!-- Booking Requests -->

    <div class="requests-card">


        <div class="card-header">

            <h2>
                Tenant Requests
            </h2>

        </div>


        <?php if (
            mysqli_num_rows($requests_result) > 0
        ): ?>


            <div class="table-wrapper">

                <table>

                    <thead>

                        <tr>

                            <th>
                                Property
                            </th>

                            <th>
                                Tenant ID
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


                    <?php while (
                        $request =
                        mysqli_fetch_assoc(
                            $requests_result
                        )
                    ): ?>


                        <tr>


                            <!-- Property -->

                            <td>

                                <div class="property-name">

                                    <strong>

                                        <?php

                                        echo htmlspecialchars(
                                            $request['property_name']
                                        );

                                        ?>

                                    </strong>

                                    <small>

                                        <?php

                                        echo htmlspecialchars(
                                            $request['property_type']
                                        );

                                        ?>
                                        -
                                        <?php

                                        echo htmlspecialchars(
                                            $request['location']
                                        );

                                        ?>

                                    </small>

                                </div>

                            </td>



                            <!-- Tenant -->

                            <td>

                                #<?php

                                echo htmlspecialchars(
                                    $request['tenant_id']
                                );

                                ?>

                            </td>



                            <!-- Booking Date -->

                            <td>

                                <?php

                                echo date(
                                    "d M Y",
                                    strtotime(
                                        $request['booking_date']
                                    )
                                );

                                ?>

                            </td>



                            <!-- Move-in Date -->

                            <td>

                                <?php

                                echo date(
                                    "d M Y",
                                    strtotime(
                                        $request['move_in_date']
                                    )
                                );

                                ?>

                            </td>



                            <!-- Duration -->

                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $request['duration']
                                );

                                ?>

                                month(s)

                            </td>



                            <!-- Occupants -->

                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $request['occupants']
                                );

                                ?>

                            </td>



                            <!-- Rent -->

                            <td>

                                Rs.

                                <?php

                                echo number_format(
                                    $request['monthly_rent'],
                                    2
                                );

                                ?>

                            </td>



                            <!-- Status -->

                            <td>

                                <span
                                    class="status
                                    <?php
                                    echo strtolower(
                                        $request[
                                            'booking_status'
                                        ]
                                    );
                                    ?>"
                                >

                                    <?php

                                    echo htmlspecialchars(
                                        $request[
                                            'booking_status'
                                        ]
                                    );

                                    ?>

                                </span>

                            </td>



                            <!-- Action -->

                            <td>

                                <?php if (
                                    strtolower(
                                        $request[
                                            'booking_status'
                                        ]
                                    ) === 'pending'
                                ): ?>


                                    <div class="action-buttons">


                                        <!-- Approve -->

                                        <form
                                            method="POST"
                                            onsubmit="
                                                return confirm(
                                                    'Are you sure you want to approve this booking?'
                                                );
                                            "
                                        >

                                            <input
                                                type="hidden"
                                                name="booking_id"
                                                value="<?php
                                                echo $request[
                                                    'booking_id'
                                                ];
                                                ?>"
                                            >

                                            <button
                                                type="submit"
                                                name="approve_booking"
                                                class="approve-btn"
                                            >
                                                Approve
                                            </button>

                                        </form>



                                        <!-- Reject -->

                                        <form
                                            method="POST"
                                            onsubmit="
                                                return confirm(
                                                    'Are you sure you want to reject this booking?'
                                                );
                                            "
                                        >

                                            <input
                                                type="hidden"
                                                name="booking_id"
                                                value="<?php
                                                echo $request[
                                                    'booking_id'
                                                ];
                                                ?>"
                                            >

                                            <button
                                                type="submit"
                                                name="reject_booking"
                                                class="reject-btn"
                                            >
                                                Reject
                                            </button>

                                        </form>


                                    </div>


                                <?php else: ?>

                                    <span class="processed">
                                        Processed
                                    </span>

                                <?php endif; ?>

                            </td>


                        </tr>


                    <?php endwhile; ?>


                    </tbody>

                </table>

            </div>


        <?php else: ?>


            <div class="no-requests">

                <div class="empty-icon">
                    📋
                </div>

                <h3>
                    No Rental Requests
                </h3>

                <p>
                    You don't have any tenant
                    rental requests yet.
                </p>

            </div>


        <?php endif; ?>


    </div>


</div>


</body>

</html>

<?php

mysqli_stmt_close($stmt);

?>