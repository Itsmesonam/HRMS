<?php

session_start();

require_once __DIR__ . "/config/database/db.php";


/*-- Show PHP Errors While Testing */

error_reporting(E_ALL);
ini_set('display_errors', 1);


/*-- Login Check */

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}


$user_id = (int) $_SESSION['user_id'];
$role = strtolower(trim($_SESSION['role']));


/*-- Only Landlord and Tenant */

if ($role !== 'landlord' && $role !== 'tenant') {
    header("Location: index.php");
    exit();
}


/*-- Contact List */

$contacts = [];


if ($role === 'landlord') {

    /*-- Landlord sees tenants who requested landlord's properties */

    $sql = "
        SELECT
            b.booking_id,
            b.booking_status,
            p.property_name,
            p.location,
            u.firstname,
            u.lastname,
            u.email,
            u.phone
        FROM bookings b
        INNER JOIN properties p
            ON b.property_id = p.property_id
        INNER JOIN users u
            ON b.tenant_id = u.id
        WHERE p.landlord_id = $user_id
          AND b.booking_status IN ('Pending', 'Confirmed')
        ORDER BY b.created_at DESC
    ";

} else {

    /*-- Tenant sees landlords of requested properties */

    $sql = "
        SELECT
            b.booking_id,
            b.booking_status,
            p.property_name,
            p.location,
            u.firstname,
            u.lastname,
            u.email,
            u.phone
        FROM bookings b
        INNER JOIN properties p
            ON b.property_id = p.property_id
        INNER JOIN users u
            ON p.landlord_id = u.id
        WHERE b.tenant_id = $user_id
          AND b.booking_status IN ('Pending', 'Confirmed')
        ORDER BY b.created_at DESC
    ";
}


$result = mysqli_query($conn, $sql);


if (!$result) {

    die(
        "Database Error: "
        . htmlspecialchars(mysqli_error($conn))
    );

}


while ($row = mysqli_fetch_assoc($result)) {

    $contacts[] = $row;

}


/*-- Page Information */

if ($role === 'landlord') {

    $page_title = "Contact Tenants";

    $page_description =
        "Contact tenants through WhatsApp.";

} else {

    $page_title = "Contact Landlords";

    $page_description =
        "Contact property owners through WhatsApp.";

}


/*-- WhatsApp Number Function */

function whatsappNumber($phone)
{

    $phone = preg_replace('/[^0-9]/', '', $phone);


    /*-- Nepal number */

    if (
        strlen($phone) === 10 &&
        substr($phone, 0, 1) === '9'
    ) {

        $phone = '977' . $phone;

    }


    return $phone;

}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        Messages - HRMS
    </title>


  <link rel="stylesheet" href="/hrms/Assets/css/message_style.css">


</head>


<body>


<!-- Sidebar -->

<div class="sidebar">

    <div class="logo">
        HRMS
    </div>


    <?php if ($role === 'landlord'): ?>

        <a href="landlorddashboard.php">
            Dashboard
        </a>

        <a href="manage_property.php">
            My Houses
        </a>

        <a href="add_property.php">
            Add Property
        </a>

        <a href="rental_requests.php">
            Rental Requests
        </a>

        <a href="landlord_payments.php">
            Payments
        </a>

        <a href="messages.php" class="active">
            Messages
        </a>

        <a href="landlord_profile.php">
            Profile
        </a>

    <?php else: ?>

        <a href="tenantdashboard.php">
            Dashboard
        </a>

        <a href="properties.php">
            Search Property
        </a>

        <a href="tenant_bookings.php">
            My Bookings
        </a>

        <a href="payment.php">
            Make Payment
        </a>

        <a href="tenant_payments.php">
            Payment History
        </a>

        <a href="messages.php" class="active">
            Messages
        </a>

        <a href="tenant_profile.php">
            Profile
        </a>

    <?php endif; ?>


    <a href="logout.php" class="logout">
        Logout
    </a>

</div>


<!-- Main Content -->

<div class="main-content">


    <!-- Header -->

    <div class="page-header">

        <h1>
            <?php echo htmlspecialchars($page_title); ?>
        </h1>

        <p>
            <?php echo htmlspecialchars($page_description); ?>
        </p>

    </div>


    <!-- Information -->

    <div class="info-box">

        <div class="info-icon">
            💬
        </div>

        <div>

            <h3>
                Communicate through WhatsApp
            </h3>

            <p>
                Contact the landlord or tenant
                directly using WhatsApp.
            </p>

        </div>

    </div>


    <!-- Contacts -->

    <div class="contacts-container">


        <?php if (empty($contacts)): ?>

            <div class="empty-box">

                <div class="empty-icon">
                    💬
                </div>

                <h2>
                    No contacts available
                </h2>

                <p>

                    <?php if ($role === 'landlord'): ?>

                        Tenants who submit a rental
                        request will appear here.

                    <?php else: ?>

                        Landlords related to your
                        rental requests will appear here.

                    <?php endif; ?>

                </p>

            </div>


        <?php else: ?>


            <?php foreach ($contacts as $contact): ?>

                <?php

                $name =
                    $contact['firstname']
                    . " "
                    . $contact['lastname'];


                $phone =
                    whatsappNumber(
                        $contact['phone']
                    );


                $message =
                    "Hello "
                    . $name
                    . ", I am contacting you regarding "
                    . $contact['property_name']
                    . " (Booking #"
                    . $contact['booking_id']
                    . ").";


                $whatsapp_url =
                    "https://wa.me/"
                    . $phone
                    . "?text="
                    . urlencode($message);

                ?>


                <div class="contact-card">


                    <!-- Person -->

                    <div class="person-section">

                        <div class="avatar">

                            <?php
                            echo strtoupper(
                                substr(
                                    $contact['firstname'],
                                    0,
                                    1
                                )
                            );
                            ?>

                        </div>


                        <div class="person-info">

                            <h2>

                                <?php
                                echo htmlspecialchars(
                                    $name
                                );
                                ?>

                            </h2>

                            <p>

                                <?php
                                echo htmlspecialchars(
                                    $contact['email']
                                );
                                ?>

                            </p>

                            <p>

                                <?php
                                echo htmlspecialchars(
                                    $contact['phone']
                                );
                                ?>

                            </p>

                        </div>

                    </div>


                    <!-- Property -->

                    <div class="property-section">

                        <span class="property-label">
                            Property
                        </span>

                        <div class="property-name">

                            <?php
                            echo htmlspecialchars(
                                $contact['property_name']
                            );
                            ?>

                        </div>

                        <div class="location">

                            <?php
                            echo htmlspecialchars(
                                $contact['location']
                            );
                            ?>

                        </div>

                    </div>


                    <!-- Booking -->

                    <div class="booking-section">

                        <span>
                            Booking #
                            <?php
                            echo (int)
                                $contact['booking_id'];
                            ?>
                        </span>


                        <span class="status
                        <?php
                        echo strtolower(
                            $contact['booking_status']
                        );
                        ?>">

                            <?php
                            echo htmlspecialchars(
                                $contact['booking_status']
                            );
                            ?>

                        </span>

                    </div>


                    <!-- WhatsApp Button -->

                    <a
                        href="<?php
                        echo htmlspecialchars(
                            $whatsapp_url
                        );
                        ?>"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="whatsapp-btn"
                    >

                        <span>
                            💬
                        </span>

                        Chat on WhatsApp

                    </a>


                </div>


            <?php endforeach; ?>


        <?php endif; ?>


    </div>

</div>


</body>

</html>