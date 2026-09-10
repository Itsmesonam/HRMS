<?php

/* =========================================
   ERROR REPORTING - TEMPORARY
   Remove or disable after everything works
========================================= */

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);


/* =========================================
   SESSION
========================================= */

session_start();


/* =========================================
   DATABASE CONNECTION
========================================= */

require_once __DIR__ . "/config/database/db.php";


/* =========================================
   TENANT LOGIN CHECK
========================================= */

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


/* =========================================
   TENANT ROLE CHECK
========================================= */

if (
    !isset($_SESSION['role']) ||
    strtolower($_SESSION['role']) !== 'tenant'
) {
    header("Location: index.php");
    exit();
}


/* =========================================
   AUTOMATIC PROPERTY EXPIRATION
   Properties older than 1 month become Expired
========================================= */

$expire_sql = "
    UPDATE properties
    SET property_status = 'Expired'
    WHERE property_status = 'Available'
    AND created_at <= DATE_SUB(NOW(), INTERVAL 1 MONTH)
";

mysqli_query($conn, $expire_sql);


/* =========================================
   SEARCH & FILTER
========================================= */

$search = "";

$type = "";


if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
}


if (isset($_GET['type'])) {
    $type = trim($_GET['type']);
}


/* =========================================
   BUILD PROPERTY QUERY
========================================= */

$sql = "
    SELECT
        p.property_id,
        p.landlord_id,
        p.property_name,
        p.property_type,
        p.location,
        p.description,
        p.monthly_rent,
        p.bedrooms,
        p.bathrooms,
        p.max_occupants,
        p.property_status,
        p.image,
        p.created_at
    FROM properties p
    WHERE p.property_status = 'Available'
    AND p.created_at > DATE_SUB(NOW(), INTERVAL 1 MONTH)
";


/* =========================================
   SEARCH
========================================= */

if ($search !== "") {

    $search_safe = mysqli_real_escape_string(
        $conn,
        $search
    );

    $sql .= "
        AND (
            p.property_name LIKE '%$search_safe%'
            OR p.location LIKE '%$search_safe%'
            OR p.description LIKE '%$search_safe%'
        )
    ";
}


/* =========================================
   PROPERTY TYPE FILTER
========================================= */

if (
    $type === "House" ||
    $type === "Apartment" ||
    $type === "Room"
) {

    $type_safe = mysqli_real_escape_string(
        $conn,
        $type
    );

    $sql .= "
        AND p.property_type = '$type_safe'
    ";
}


/* =========================================
   ORDER
========================================= */

$sql .= "
    ORDER BY p.created_at DESC
";


/* =========================================
   EXECUTE QUERY
========================================= */

$result = mysqli_query($conn, $sql);


/* =========================================
   CHECK QUERY ERROR
========================================= */

if (!$result) {

    die(
        "<h2>Database Query Error</h2>" .
        "<p>" . htmlspecialchars(mysqli_error($conn)) . "</p>"
    );
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

    <title>Browse Houses | HRMS</title>


    <!-- CSS -->

    <link
        rel="stylesheet"
        href="assets/css/properties_style.css"
    >


    <!-- Google Font -->

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >

</head>


<body>


<!-- =========================================
     HEADER
========================================= -->

<header class="top-header">

    <div class="header-container">

        <div class="logo">

            <span>HRMS</span>

        </div>


        <nav>

            <a href="tenantdashboard.php">
                Dashboard
            </a>

            <a href="properties.php" class="active">
                Browse Houses
            </a>

            <a href="tenant_bookings.php">
                My Bookings
            </a>

            <a href="tenant_payments.php">
                Payments
            </a>

            <a href="logout.php">
                Logout
            </a>

        </nav>

    </div>

</header>



<!-- =========================================
     MAIN CONTENT
========================================= -->

<main class="main-container">


    <!-- PAGE TITLE -->

    <div class="page-header">

        <div>

            <h1>
                Browse Houses
            </h1>

            <p>
                Find a house, apartment or room according to your needs.
            </p>

        </div>

    </div>



    <!-- =========================================
         SEARCH & FILTER
    ========================================= -->

    <section class="search-section">

        <form
            method="GET"
            action="properties.php"
            class="search-form"
        >

            <div class="search-box">

                <input
                    type="text"
                    name="search"
                    placeholder="Search by property name, location..."
                    value="<?php echo htmlspecialchars($search); ?>"
                >

            </div>


            <div class="filter-box">

                <select name="type">

                    <option value="">
                        All Property Types
                    </option>

                    <option
                        value="House"
                        <?php
                        if ($type === "House") {
                            echo "selected";
                        }
                        ?>
                    >
                        House
                    </option>

                    <option
                        value="Apartment"
                        <?php
                        if ($type === "Apartment") {
                            echo "selected";
                        }
                        ?>
                    >
                        Apartment
                    </option>

                    <option
                        value="Room"
                        <?php
                        if ($type === "Room") {
                            echo "selected";
                        }
                        ?>
                    >
                        Room
                    </option>

                </select>

            </div>


            <button
                type="submit"
                class="search-btn"
            >
                Search
            </button>


            <a
                href="properties.php"
                class="reset-btn"
            >
                Reset
            </a>

        </form>

    </section>



    <!-- =========================================
         PROPERTY LIST
    ========================================= -->

    <section class="property-section">


        <?php if (mysqli_num_rows($result) > 0): ?>


            <div class="property-grid">


                <?php while ($property = mysqli_fetch_assoc($result)): ?>


                    <div class="property-card">


                        <!-- PROPERTY IMAGE -->

                        <div class="property-image">


                            <?php

                            $image_path = "";

                            if (
                                !empty($property['image'])
                            ) {

                                $image_path =
                                    "uploads/properties/" .
                                    $property['image'];

                            }

                            ?>


                            <?php if (
                                !empty($property['image']) &&
                                file_exists(__DIR__ . "/" . $image_path)
                            ): ?>

                                <img
                                    src="<?php echo htmlspecialchars($image_path); ?>"
                                    alt="Property Image"
                                >

                            <?php else: ?>

                                <div class="no-image">

                                    <span>
                                        No Image
                                    </span>

                                </div>

                            <?php endif; ?>


                            <div class="available-badge">

                                Available

                            </div>


                        </div>



                        <!-- PROPERTY DETAILS -->

                        <div class="property-content">


                            <h2>

                                <?php
                                echo htmlspecialchars(
                                    $property['property_name']
                                );
                                ?>

                            </h2>


                            <p class="location">

                                📍

                                <?php
                                echo htmlspecialchars(
                                    $property['location']
                                );
                                ?>

                            </p>


                            <div class="property-type">

                                <?php
                                echo htmlspecialchars(
                                    $property['property_type']
                                );
                                ?>

                            </div>



                            <!-- RENT -->

                            <div class="rent">

                                <strong>
                                    Rs.
                                    <?php
                                    echo number_format(
                                        $property['monthly_rent'],
                                        2
                                    );
                                    ?>
                                </strong>

                                <span>
                                    / month
                                </span>

                            </div>



                            <!-- PROPERTY FEATURES -->

                            <div class="features">


                                <div>

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


                                <div>

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


                                <div>

                                    <strong>
                                        <?php
                                        echo htmlspecialchars(
                                            $property['max_occupants']
                                        );
                                        ?>
                                    </strong>

                                    <span>
                                        Occupants
                                    </span>

                                </div>


                            </div>



                            <!-- LANDLORD -->

                            <p class="landlord">

                                Landlord ID:
                                <strong>

                                    <?php
                                    echo htmlspecialchars(
                                        $property['landlord_id']
                                    );
                                    ?>

                                </strong>

                            </p>



                            <!-- CREATED DATE -->

                            <p class="created-date">

                                Listed on:

                                <?php

                                echo date(
                                    "d M Y",
                                    strtotime(
                                        $property['created_at']
                                    )
                                );

                                ?>

                            </p>



                            <!-- ACTION BUTTONS -->

                            <div class="property-actions">


                                <a
                                    href="property_details.php?id=<?php echo $property['property_id']; ?>"
                                    class="details-btn"
                                >

                                    View Details

                                </a>


                                <a
                                    href="booking.php?property_id=<?php echo $property['property_id']; ?>"
                                    class="book-btn"
                                >

                                    Book Now

                                </a>


                            </div>


                        </div>


                    </div>


                <?php endwhile; ?>


            </div>


        <?php else: ?>


            <!-- NO PROPERTY -->

            <div class="no-properties">

                <h2>
                    No Properties Available
                </h2>

                <p>
                    There are currently no available properties matching your search.
                </p>

                <a
                    href="properties.php"
                    class="reset-btn"
                >
                    View All Properties
                </a>

            </div>


        <?php endif; ?>


    </section>


</main>


</body>

</html>