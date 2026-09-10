<?php

session_start();

require_once __DIR__ . "/config/database/db.php";

/*-- Check tenant login */
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

/*-- Check tenant role */
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'tenant') {
    header("Location: index.php");
    exit();
}

$tenant_id = $_SESSION['user_id'];


/*-- Get tenant name */
$user_name = '';

$user_query = mysqli_query(
    $conn,
    "SELECT * FROM users WHERE id = $tenant_id LIMIT 1"
);

if ($user_query && mysqli_num_rows($user_query) > 0) {

    $user = mysqli_fetch_assoc($user_query);

    /*
       Change these according to your actual users table
       if necessary.
    */

    if (isset($user['first_name'])) {
        $user_name = $user['first_name'];

        if (isset($user['last_name'])) {
            $user_name .= " " . $user['last_name'];
        }
    }
}


/*-- Get available properties */
$properties_query = "
    SELECT
        property_id,
        property_name,
        property_type,
        location,
        monthly_rent,
        max_occupants
    FROM properties
    WHERE property_status = 'Available'
    AND created_at > DATE_SUB(NOW(), INTERVAL 1 MONTH)
    ORDER BY property_id DESC
";

$properties_result = mysqli_query(
    $conn,
    $properties_query
);


/*-- Submit booking */
if (isset($_POST['submit_booking'])) {

    $property_id = intval($_POST['property_id']);
    $booking_date = $_POST['booking_date'];
    $move_in_date = $_POST['move_in_date'];
    $duration = intval($_POST['duration']);
    $occupants = intval($_POST['occupants']);
    $message = trim($_POST['message']);

    /*-- Check property */
    $property_query = mysqli_query(
        $conn,
        "SELECT *
         FROM properties
         WHERE property_id = $property_id
         AND property_status = 'Available'
         AND created_at > DATE_SUB(NOW(), INTERVAL 1 MONTH)
         LIMIT 1"
    );

    if (!$property_query || mysqli_num_rows($property_query) === 0) {

        $error = "Selected property is no longer available.";

    } else {

        $property = mysqli_fetch_assoc($property_query);

        /*-- Check occupants */
        if ($occupants > $property['max_occupants']) {

            $error = "Number of occupants exceeds the maximum allowed.";

        } elseif ($move_in_date < $booking_date) {

            $error = "Move-in date cannot be before booking date.";

        } else {

            /*-- Check existing booking */
            $check_booking = mysqli_query(
                $conn,
                "SELECT booking_id
                 FROM bookings
                 WHERE property_id = $property_id
                 AND tenant_id = $tenant_id
                 AND booking_status IN ('Pending', 'Confirmed')
                 LIMIT 1"
            );

            if ($check_booking && mysqli_num_rows($check_booking) > 0) {

                $error = "You already have a booking request for this property.";

            } else {

                /*-- Insert booking */
                $stmt = mysqli_prepare(
                    $conn,
                    "INSERT INTO bookings
                    (
                        property_id,
                        tenant_id,
                        booking_date,
                        move_in_date,
                        duration,
                        occupants,
                        message,
                        booking_status
                    )
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')"
                );

                mysqli_stmt_bind_param(
                    $stmt,
                    "iisssis",
                    $property_id,
                    $tenant_id,
                    $booking_date,
                    $move_in_date,
                    $duration,
                    $occupants,
                    $message
                );

                if (mysqli_stmt_execute($stmt)) {

                    header("Location: tenant_bookings.php?success=1");
                    exit();

                } else {

                    $error = "Booking request failed. Please try again.";
                }

                mysqli_stmt_close($stmt);
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Submit Rental Request</title>

    <link rel="stylesheet"
          href="assets/css/booking_style.css">

</head>

<body>

    <div class="booking-container">

        <h1>Submit Rental Request</h1>

        <p class="welcome">
            Welcome,
            <?php echo htmlspecialchars($user_name); ?>
        </p>

        <?php if (isset($error)): ?>

            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>

        <?php endif; ?>

        <form method="POST">

            <div class="form-group">

                <label for="property_id">
                    Select Property
                </label>

                <select
                    name="property_id"
                    id="property_id"
                    required
                >

                    <option value="">
                        Select an available property
                    </option>

                    <?php while ($property = mysqli_fetch_assoc($properties_result)): ?>

                        <option value="<?php echo $property['property_id']; ?>">

                            <?php echo htmlspecialchars($property['property_name']); ?>
                            -
                            <?php echo htmlspecialchars($property['property_type']); ?>
                            -
                            <?php echo htmlspecialchars($property['location']); ?>
                            -
                            Rs.
                            <?php echo number_format($property['monthly_rent'], 2); ?>

                        </option>

                    <?php endwhile; ?>

                </select>

            </div>


            <div class="form-group">

                <label for="booking_date">
                    Booking Date
                </label>

                <input
                    type="date"
                    name="booking_date"
                    id="booking_date"
                    value="<?php echo date('Y-m-d'); ?>"
                    readonly
                    required
                >

            </div>


            <div class="form-group">

                <label for="move_in_date">
                    Move-in Date
                </label>

                <input
                    type="date"
                    name="move_in_date"
                    id="move_in_date"
                    min="<?php echo date('Y-m-d'); ?>"
                    required
                >

            </div>


            <div class="form-group">

                <label for="duration">
                    Rental Duration
                </label>

                <div class="duration-row">

                    <input
                        type="number"
                        name="duration"
                        id="duration"
                        min="1"
                        value="1"
                        required
                    >

                    <span>Month(s)</span>

                </div>

            </div>


            <div class="form-group">

                <label for="occupants">
                    Number of Occupants
                </label>

                <input
                    type="number"
                    name="occupants"
                    id="occupants"
                    min="1"
                    value="1"
                    required
                >

            </div>


            <div class="form-group">

                <label for="message">
                    Message
                </label>

                <textarea
                    name="message"
                    id="message"
                    placeholder="Enter any additional information for the landlord..."
                ></textarea>

            </div>


            <button
                type="submit"
                name="submit_booking"
                class="submit-btn"
            >
                Submit Rental Request
            </button>

        </form>


        <a
            href="tenantdashboard.php"
            class="back-btn"
        >
            ← Back to Dashboard
        </a>

    </div>

</body>

</html>