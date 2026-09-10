<?php

session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . "/config/database/db.php";


/* =========================================
   LANDLORD LOGIN CHECK
========================================= */

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (
    !isset($_SESSION['role']) ||
    strtolower($_SESSION['role']) !== 'landlord'
) {
    header("Location: login.php");
    exit();
}

$landlord_id = $_SESSION['user_id'];

$message = "";
$message_type = "";


/* =========================================
   CONFIRM BOOKING
========================================= */

if (isset($_POST['confirm_booking'])) {

    $booking_id = intval($_POST['booking_id']);

    mysqli_begin_transaction($conn);

    try {

        /* Get booking belonging to this landlord */

        $sql = "
            SELECT
                b.booking_id,
                b.property_id,
                b.booking_status,
                p.property_status

            FROM bookings b

            INNER JOIN properties p
                ON b.property_id = p.property_id

            WHERE b.booking_id = ?
              AND p.landlord_id = ?

            FOR UPDATE
        ";

        $stmt = mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "ii",
            $booking_id,
            $landlord_id
        );

        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        $booking = mysqli_fetch_assoc($result);

        mysqli_stmt_close($stmt);


        if (!$booking) {
            throw new Exception("Booking request not found.");
        }


        /* Only Pending bookings can be confirmed */

        if ($booking['booking_status'] !== 'Pending') {
            throw new Exception(
                "This booking has already been processed."
            );
        }


        /* Property must still be available */

        if ($booking['property_status'] !== 'Available') {
            throw new Exception(
                "This property is no longer available."
            );
        }


        /* Confirm booking */

        $sql = "
            UPDATE bookings

            SET booking_status = 'Confirmed'

            WHERE booking_id = ?
              AND booking_status = 'Pending'
        ";

        $stmt = mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $booking_id
        );

        mysqli_stmt_execute($stmt);

        mysqli_stmt_close($stmt);


        /* Make property occupied */

        $sql = "
            UPDATE properties

            SET property_status = 'Occupied'

            WHERE property_id = ?
              AND property_status = 'Available'
        ";

        $stmt = mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $booking['property_id']
        );

        mysqli_stmt_execute($stmt);

        mysqli_stmt_close($stmt);


        mysqli_commit($conn);

        header(
            "Location: rental_requests.php?success=confirmed"
        );

        exit();

    } catch (Exception $e) {

        mysqli_rollback($conn);

        $message = $e->getMessage();
        $message_type = "error";
    }
}


/* =========================================
   REJECT BOOKING
========================================= */

if (isset($_POST['reject_booking'])) {

    $booking_id = intval($_POST['booking_id']);


    $sql = "
        UPDATE bookings b

        INNER JOIN properties p
            ON b.property_id = p.property_id

        SET b.booking_status = 'Rejected'

        WHERE b.booking_id = ?
          AND p.landlord_id = ?
          AND b.booking_status = 'Pending'
    ";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "ii",
        $booking_id,
        $landlord_id
    );

    if (mysqli_stmt_execute($stmt)) {

        if (mysqli_stmt_affected_rows($stmt) > 0) {

            header(
                "Location: rental_requests.php?success=rejected"
            );

            exit();

        } else {

            $message =
                "Booking request could not be rejected.";

            $message_type = "error";
        }

    } else {

        $message =
            "Database error: " .
            mysqli_error($conn);

        $message_type = "error";
    }

    mysqli_stmt_close($stmt);
}


/* =========================================
   SUCCESS MESSAGE
========================================= */

if (isset($_GET['success'])) {

    if ($_GET['success'] === 'confirmed') {

        $message = "Booking confirmed successfully.";
        $message_type = "success";

    }

    elseif ($_GET['success'] === 'rejected') {

        $message = "Booking request rejected.";
        $message_type = "success";

    }
}


/* =========================================
   FILTER
========================================= */

$filter = $_GET['status'] ?? 'All';


/* =========================================
   GET RENTAL REQUESTS
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
        b.created_at,

        p.property_id,
        p.property_name,
        p.property_type,
        p.location,
        p.monthly_rent,

        u.first_name,
        u.last_name,
        u.email,
        u.phone

    FROM bookings b

    INNER JOIN properties p
        ON b.property_id = p.property_id

    INNER JOIN users u
        ON b.tenant_id = u.id

    WHERE p.landlord_id = ?
";


/* Add status filter */

if (
    in_array(
        $filter,
        ['Pending', 'Confirmed', 'Rejected']
    )
) {

    $sql .= "
        AND b.booking_status = ?
    ";

}

$sql .= "
    ORDER BY b.created_at DESC
";


$stmt = mysqli_prepare($conn, $sql);


if (
    in_array(
        $filter,
        ['Pending', 'Confirmed', 'Rejected']
    )
) {

    mysqli_stmt_bind_param(
        $stmt,
        "is",
        $landlord_id,
        $filter
    );

} else {

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $landlord_id
    );

}


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

    <title>Rental Requests | HRMS</title>

    <link
        rel="stylesheet"
        href="assets/css/rental_requests_style.css"
    >

</head>


<body>


<div class="page-container">


    <!-- =================================
         HEADER
    ================================== -->

    <header class="page-header">

        <div>

            <h1>Rental Requests</h1>

            <p>
                Review rental requests from tenants.
            </p>

        </div>


        <a
            href="landlorddashboard.php"
            class="back-btn"
        >
            ← Dashboard
        </a>

    </header>


    <main>


        <!-- =================================
             MESSAGE
        ================================== -->

        <?php if (!empty($message)): ?>

            <div class="message <?php echo $message_type; ?>">

                <?php
                echo htmlspecialchars($message);
                ?>

            </div>

        <?php endif; ?>


        <!-- =================================
             FILTER
        ================================== -->

        <div class="filter-bar">

            <form method="GET">

                <select
                    name="status"
                    onchange="this.form.submit()"
                >

                    <option
                        value="All"
                        <?php
                        echo $filter === 'All'
                            ? 'selected'
                            : '';
                        ?>
                    >
                        All Requests
                    </option>


                    <option
                        value="Pending"
                        <?php
                        echo $filter === 'Pending'
                            ? 'selected'
                            : '';
                        ?>
                    >
                        Pending
                    </option>


                    <option
                        value="Confirmed"
                        <?php
                        echo $filter === 'Confirmed'
                            ? 'selected'
                            : '';
                        ?>
                    >
                        Confirmed
                    </option>


                    <option
                        value="Rejected"
                        <?php
                        echo $filter === 'Rejected'
                            ? 'selected'
                            : '';
                        ?>
                    >
                        Rejected
                    </option>

                </select>

            </form>

        </div>


        <!-- =================================
             REQUEST CARD
        ================================== -->

        <div class="requests-card">


            <?php if (mysqli_num_rows($result) > 0): ?>


                <div class="table-container">

                    <table>

                        <thead>

                            <tr>

                                <th>Tenant</th>

                                <th>Property</th>

                                <th>Move-in Date</th>

                                <th>Duration</th>

                                <th>Occupants</th>

                                <th>Rent</th>

                                <th>Status</th>

                                <th>Action</th>

                            </tr>

                        </thead>


                        <tbody>


                        <?php while ($row = mysqli_fetch_assoc($result)): ?>


                            <tr>


                                <!-- TENANT -->

                                <td>

                                    <div class="tenant-info">

                                        <strong>

                                            <?php
                                            echo htmlspecialchars(
                                                $row['first_name']
                                                . " "
                                                . $row['last_name']
                                            );
                                            ?>

                                        </strong>

                                        <small>

                                            <?php
                                            echo htmlspecialchars(
                                                $row['email']
                                            );
                                            ?>

                                        </small>

                                        <?php if (!empty($row['phone'])): ?>

                                            <small>

                                                <?php
                                                echo htmlspecialchars(
                                                    $row['phone']
                                                );
                                                ?>

                                            </small>

                                        <?php endif; ?>

                                    </div>

                                </td>


                                <!-- PROPERTY -->

                                <td>

                                    <div class="property-info">

                                        <strong>

                                            <?php
                                            echo htmlspecialchars(
                                                $row['property_name']
                                            );
                                            ?>

                                        </strong>

                                        <small>

                                            <?php
                                            echo htmlspecialchars(
                                                $row['property_type']
                                            );
                                            ?>

                                        </small>

                                        <small>

                                            <?php
                                            echo htmlspecialchars(
                                                $row['location']
                                            );
                                            ?>

                                        </small>

                                    </div>

                                </td>


                                <!-- MOVE IN -->

                                <td>

                                    <?php
                                    echo htmlspecialchars(
                                        $row['move_in_date']
                                    );
                                    ?>

                                </td>


                                <!-- DURATION -->

                                <td>

                                    <?php
                                    echo htmlspecialchars(
                                        $row['duration']
                                    );
                                    ?>

                                    month(s)

                                </td>


                                <!-- OCCUPANTS -->

                                <td>

                                    <?php
                                    echo htmlspecialchars(
                                        $row['occupants']
                                    );
                                    ?>

                                </td>


                                <!-- RENT -->

                                <td>

                                    Rs.

                                    <?php
                                    echo number_format(
                                        $row['monthly_rent'],
                                        2
                                    );
                                    ?>

                                </td>


                                <!-- STATUS -->

                                <td>

                                    <span
                                        class="status <?php
                                        echo strtolower(
                                            $row['booking_status']
                                        );
                                        ?>"
                                    >

                                        <?php
                                        echo htmlspecialchars(
                                            $row['booking_status']
                                        );
                                        ?>

                                    </span>

                                </td>


                                <!-- ACTION -->

                                <td>


                                    <?php if (
                                        $row['booking_status']
                                        === 'Pending'
                                    ): ?>


                                        <div class="action-buttons">


                                            <!-- CONFIRM -->

                                            <form
                                                method="POST"
                                                onsubmit="
                                                    return confirm(
                                                        'Confirm this rental request?'
                                                    );
                                                "
                                            >

                                                <input
                                                    type="hidden"
                                                    name="booking_id"
                                                    value="<?php
                                                    echo $row['booking_id'];
                                                    ?>"
                                                >

                                                <button
                                                    type="submit"
                                                    name="confirm_booking"
                                                    class="confirm-btn"
                                                >
                                                    Confirm
                                                </button>

                                            </form>


                                            <!-- REJECT -->

                                            <form
                                                method="POST"
                                                onsubmit="
                                                    return confirm(
                                                        'Reject this rental request?'
                                                    );
                                                "
                                            >

                                                <input
                                                    type="hidden"
                                                    name="booking_id"
                                                    value="<?php
                                                    echo $row['booking_id'];
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


                <div class="empty-state">

                    <div class="icon">
                        📋
                    </div>

                    <h2>
                        No Rental Requests
                    </h2>

                    <p>
                        Tenant rental requests will appear here.
                    </p>

                </div>


            <?php endif; ?>


        </div>


    </main>


</div>


</body>

</html>


<?php

mysqli_stmt_close($stmt);

?>