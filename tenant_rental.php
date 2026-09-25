<?php

session_start();

require_once __DIR__ . "/config/database/db.php";

/* =========================================
   TENANT ACCESS CHECK
========================================= */

if (
    !isset($_SESSION['user_id']) ||
    strtolower($_SESSION['role']) !== 'tenant'
) {
    header("Location: login.php");
    exit();
}

$tenant_id = $_SESSION['user_id'];

/* =========================================
   GET ACTIVE RENTAL
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
        p.description,
        p.monthly_rent,
        p.bedrooms,
        p.bathrooms,
        p.image,

        u.id AS landlord_id,
        u.firstname AS landlord_firstname,
        u.lastname AS landlord_lastname,
        u.email AS landlord_email,
        u.phone AS landlord_phone

    FROM bookings b

    INNER JOIN properties p
        ON b.property_id = p.property_id

    INNER JOIN users u
        ON p.landlord_id = u.id

    WHERE b.tenant_id = ?
      AND b.booking_status = 'Confirmed'

    ORDER BY b.updated_at DESC

    LIMIT 1
";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $tenant_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$rental = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>My Rental | HRMS</title>

    <link rel="stylesheet"
          href="/hrms/Assets/css/tenant_rental_style.css">

</head>

<body>

<div class="rental-container">

    <!-- =========================================
         PAGE HEADER
    ========================================== -->

    <div class="page-header">

        <div>

            <h1>My Rental</h1>

            <p>
                View your current rental property and rental details.
            </p>

        </div>

        <a href="tenantdashboard.php"
           class="back-btn">
            ← Dashboard
        </a>

    </div>


    <?php if ($rental): ?>

        <!-- =========================================
             RENTAL CARD
        ========================================== -->

        <div class="rental-card">

            <!-- PROPERTY IMAGE -->

            <div class="rental-image">

                <?php if (!empty($rental['image'])): ?>

                    <img
                        src="uploads/properties/<?php echo htmlspecialchars($rental['image']); ?>"
                        alt="Property Image"
                    >

                <?php else: ?>

                    <img
                        src="Assets/images/house.jpg"
                        alt="Property Image"
                    >

                <?php endif; ?>

            </div>


            <!-- PROPERTY DETAILS -->

            <div class="rental-details">

                <span class="rental-status">
                    Active Rental
                </span>

                <h2>
                    <?php
                    echo htmlspecialchars(
                        $rental['property_name']
                    );
                    ?>
                </h2>

                <p class="location">

                    📍
                    <?php
                    echo htmlspecialchars(
                        $rental['location']
                    );
                    ?>

                </p>


                <!-- DETAILS GRID -->

                <div class="details-grid">

                    <div class="detail-box">

                        <span>Property Type</span>

                        <strong>
                            <?php
                            echo htmlspecialchars(
                                $rental['property_type']
                            );
                            ?>
                        </strong>

                    </div>


                    <div class="detail-box">

                        <span>Monthly Rent</span>

                        <strong>
                            Rs.
                            <?php
                            echo number_format(
                                $rental['monthly_rent']
                            );
                            ?>
                        </strong>

                    </div>


                    <div class="detail-box">

                        <span>Bedrooms</span>

                        <strong>
                            <?php
                            echo htmlspecialchars(
                                $rental['bedrooms']
                            );
                            ?>
                        </strong>

                    </div>


                    <div class="detail-box">

                        <span>Bathrooms</span>

                        <strong>
                            <?php
                            echo htmlspecialchars(
                                $rental['bathrooms']
                            );
                            ?>
                        </strong>

                    </div>


                    <div class="detail-box">

                        <span>Move-in Date</span>

                        <strong>
                            <?php
                            echo date(
                                "d M Y",
                                strtotime(
                                    $rental['move_in_date']
                                )
                            );
                            ?>
                        </strong>

                    </div>


                    <div class="detail-box">

                        <span>Duration</span>

                        <strong>
                            <?php
                            echo htmlspecialchars(
                                $rental['duration']
                            );
                            ?>
                            Months
                        </strong>

                    </div>


                    <div class="detail-box">

                        <span>Occupants</span>

                        <strong>
                            <?php
                            echo htmlspecialchars(
                                $rental['occupants']
                            );
                            ?>
                        </strong>

                    </div>


                    <div class="detail-box">

                        <span>Booking Date</span>

                        <strong>
                            <?php
                            echo date(
                                "d M Y",
                                strtotime(
                                    $rental['booking_date']
                                )
                            );
                            ?>
                        </strong>

                    </div>

                </div>


                <!-- LANDLORD -->

                <div class="landlord-section">

                    <h3>Landlord Information</h3>

                    <p>

                        <strong>Name:</strong>

                        <?php
                        echo htmlspecialchars(
                            $rental['landlord_firstname']
                            . " "
                            . $rental['landlord_lastname']
                        );
                        ?>

                    </p>


                    <p>

                        <strong>Email:</strong>

                        <?php
                        echo htmlspecialchars(
                            $rental['landlord_email']
                        );
                        ?>

                    </p>


                    <p>

                        <strong>Phone:</strong>

                        <?php
                        echo htmlspecialchars(
                            $rental['landlord_phone']
                        );
                        ?>

                    </p>

                </div>


                <!-- ACTIONS -->

                <div class="rental-actions">

                    <a
                        href="tenant_payments.php"
                        class="payment-btn"
                    >
                        View Payment History
                    </a>

                    <a
                        href="messages.php"
                        class="message-btn"
                    >
                        Contact Landlord
                    </a>

                </div>

            </div>

        </div>


        <!-- =========================================
             DESCRIPTION
        ========================================== -->

        <?php if (!empty($rental['description'])): ?>

            <div class="description-card">

                <h3>Property Description</h3>

                <p>
                    <?php
                    echo nl2br(
                        htmlspecialchars(
                            $rental['description']
                        )
                    );
                    ?>
                </p>

            </div>

        <?php endif; ?>


    <?php else: ?>

        <!-- =========================================
             NO RENTAL
        ========================================== -->

        <div class="no-rental">

            <div class="no-rental-icon">
                🏠
            </div>

            <h2>No Active Rental</h2>

            <p>
                You currently do not have a confirmed rental property.
            </p>

            <a
                href="properties.php"
                class="browse-btn"
            >
                Browse Properties
            </a>

        </div>

    <?php endif; ?>

</div>

</body>
</html>