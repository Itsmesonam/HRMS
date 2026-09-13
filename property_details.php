<?php

session_start();

require_once __DIR__ . "/config/database/db.php";


/*-- Tenant Login Check */

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


/*-- Tenant Role Check */

if (
    !isset($_SESSION['role']) ||
    strtolower($_SESSION['role']) !== 'tenant'
) {
    header("Location: index.php");
    exit();
}


/*-- Check Property ID */

if (
    !isset($_GET['id']) ||
    !is_numeric($_GET['id'])
) {
    header("Location: properties.php");
    exit();
}

$property_id = (int) $_GET['id'];


/*-- Automatically Expire Properties After 1 Month */

$expiry_query = "
    UPDATE properties
    SET property_status = 'Expired'
    WHERE property_status = 'Available'
    AND created_at <= DATE_SUB(NOW(), INTERVAL 1 MONTH)
";

mysqli_query($conn, $expiry_query);


/*-- Get Property Details */

$stmt = mysqli_prepare(
    $conn,
    "SELECT
        property_id,
        landlord_id,
        property_name,
        property_type,
        location,
        description,
        monthly_rent,
        bedrooms,
        bathrooms,
        max_occupants,
        property_status,
        image,
        created_at
     FROM properties
     WHERE property_id = ?"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $property_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$property = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


/*-- Property Not Found */

if (!$property) {
    header("Location: properties.php");
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

    <title>
        <?php
        echo htmlspecialchars(
            $property['property_name']
        );
        ?>
        - Property Details
    </title>

    <link
        rel="stylesheet"
        href="assets/css/property_details_style.css"
    >

</head>


<body>


<div class="details-container">


    <!-- Back to Properties -->

    <a
        href="properties.php"
        class="back-btn"
    >
        ← Back to Properties
    </a>



    <!-- Property Details Card -->

    <div class="details-card">


        <!-- Property Image -->

        <div class="property-image">

            <?php if (!empty($property['image'])): ?>

                <?php

                $image_path =
                    "uploads/properties/" .
                    $property['image'];

                ?>

                <img
                    src="<?php
                    echo htmlspecialchars($image_path);
                    ?>"
                    alt="<?php
                    echo htmlspecialchars(
                        $property['property_name']
                    );
                    ?>"
                >

            <?php else: ?>

                <div class="no-image">
                    No Image Available
                </div>

            <?php endif; ?>

        </div>



        <!-- Property Information -->

        <div class="property-info">


            <!-- Title and Status -->

            <div class="title-row">


                <div>

                    <h1>

                        <?php

                        echo htmlspecialchars(
                            $property['property_name']
                        );

                        ?>

                    </h1>


                    <p class="location">

                        📍

                        <?php

                        echo htmlspecialchars(
                            $property['location']
                        );

                        ?>

                    </p>

                </div>



                <!-- Property Status -->

                <span
                    class="status
                    <?php
                    echo strtolower(
                        $property['property_status']
                    );
                    ?>"
                >

                    <?php

                    echo htmlspecialchars(
                        $property['property_status']
                    );

                    ?>

                </span>


            </div>



            <!-- Monthly Rent -->

            <div class="rent">

                Rs.

                <?php

                echo number_format(
                    $property['monthly_rent'],
                    2
                );

                ?>

                <span>
                    / month
                </span>

            </div>



            <!-- Property Features -->

            <div class="features">


                <!-- Property Type -->

                <div class="feature">

                    <strong>

                        <?php

                        echo htmlspecialchars(
                            $property['property_type']
                        );

                        ?>

                    </strong>

                    <span>
                        Property Type
                    </span>

                </div>



                <!-- Bedrooms -->

                <div class="feature">

                    <strong>

                        <?php

                        echo htmlspecialchars(
                            $property['bedrooms']
                        );

                        ?>

                    </strong>

                    <span>
                        Bedrooms
                    </span>

                </div>



                <!-- Bathrooms -->

                <div class="feature">

                    <strong>

                        <?php

                        echo htmlspecialchars(
                            $property['bathrooms']
                        );

                        ?>

                    </strong>

                    <span>
                        Bathrooms
                    </span>

                </div>



                <!-- Maximum Occupants -->

                <div class="feature">

                    <strong>

                        <?php

                        echo htmlspecialchars(
                            $property['max_occupants']
                        );

                        ?>

                    </strong>

                    <span>
                        Max Occupants
                    </span>

                </div>


            </div>



            <!-- Description -->

            <div class="section">


                <h2>
                    Description
                </h2>


                <p>

                    <?php

                    if (
                        !empty(
                            $property['description']
                        )
                    ) {

                        echo nl2br(
                            htmlspecialchars(
                                $property['description']
                            )
                        );

                    } else {

                        echo
                            "No description available.";

                    }

                    ?>

                </p>


            </div>



            <!-- Property Information -->

            <div class="section">


                <h2>
                    Property Information
                </h2>


                <div class="info-grid">


                    <!-- Type -->

                    <div>

                        <span>
                            Property Type
                        </span>

                        <strong>

                            <?php

                            echo htmlspecialchars(
                                $property['property_type']
                            );

                            ?>

                        </strong>

                    </div>



                    <!-- Location -->

                    <div>

                        <span>
                            Location
                        </span>

                        <strong>

                            <?php

                            echo htmlspecialchars(
                                $property['location']
                            );

                            ?>

                        </strong>

                    </div>



                    <!-- Bedrooms -->

                    <div>

                        <span>
                            Bedrooms
                        </span>

                        <strong>

                            <?php

                            echo htmlspecialchars(
                                $property['bedrooms']
                            );

                            ?>

                        </strong>

                    </div>



                    <!-- Bathrooms -->

                    <div>

                        <span>
                            Bathrooms
                        </span>

                        <strong>

                            <?php

                            echo htmlspecialchars(
                                $property['bathrooms']
                            );

                            ?>

                        </strong>

                    </div>



                    <!-- Occupants -->

                    <div>

                        <span>
                            Maximum Occupants
                        </span>

                        <strong>

                            <?php

                            echo htmlspecialchars(
                                $property['max_occupants']
                            );

                            ?>

                        </strong>

                    </div>



                    <!-- Monthly Rent -->

                    <div>

                        <span>
                            Monthly Rent
                        </span>

                        <strong>

                            Rs.

                            <?php

                            echo number_format(
                                $property['monthly_rent'],
                                2
                            );

                            ?>

                        </strong>

                    </div>


                </div>


            </div>



            <!-- Action Buttons -->

            <div class="action-buttons">


                <?php if (
                    strtolower(
                        $property['property_status']
                    ) === 'available'
                ): ?>


                    <a
                        href="booking.php?property_id=<?php
                        echo $property['property_id'];
                        ?>"
                        class="book-btn"
                    >
                        Book This Property
                    </a>


                <?php else: ?>


                    <button
                        type="button"
                        class="unavailable-btn"
                        disabled
                    >
                        Property Not Available
                    </button>


                <?php endif; ?>



                <a
                    href="properties.php"
                    class="back-properties"
                >
                    Browse Other Properties
                </a>


            </div>


        </div>


    </div>


</div>


</body>

</html>