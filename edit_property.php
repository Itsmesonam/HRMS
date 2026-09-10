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


/* =========================================
   GET PROPERTY ID
========================================= */

$property_id = isset($_GET['id'])
    ? intval($_GET['id'])
    : 0;

if ($property_id <= 0) {
    header("Location: manage_property.php");
    exit();
}


$message = "";
$message_type = "";


/* =========================================
   UPDATE PROPERTY
========================================= */

if (isset($_POST['update_property'])) {

    $property_name = trim($_POST['property_name'] ?? '');
    $property_type = $_POST['property_type'] ?? '';
    $location = trim($_POST['location'] ?? '');
    $monthly_rent = $_POST['monthly_rent'] ?? '';
    $bedrooms = intval($_POST['bedrooms'] ?? 1);
    $bathrooms = intval($_POST['bathrooms'] ?? 1);
    $max_occupants = intval($_POST['max_occupants'] ?? 1);
    $description = trim($_POST['description'] ?? '');
    $property_status = $_POST['property_status'] ?? 'Available';


    /* =====================================
       VALIDATION
    ===================================== */

    if (
        empty($property_name) ||
        empty($property_type) ||
        empty($location) ||
        $monthly_rent === ''
    ) {

        $message = "Please fill in all required fields.";
        $message_type = "error";

    } elseif (!in_array(
        $property_type,
        ['House', 'Apartment', 'Room']
    )) {

        $message = "Invalid property type.";
        $message_type = "error";

    } elseif (!is_numeric($monthly_rent) || $monthly_rent <= 0) {

        $message = "Please enter a valid monthly rent.";
        $message_type = "error";

    } elseif ($bedrooms < 1) {

        $message = "Bedrooms must be at least 1.";
        $message_type = "error";

    } elseif ($bathrooms < 1) {

        $message = "Bathrooms must be at least 1.";
        $message_type = "error";

    } elseif ($max_occupants < 1) {

        $message = "Maximum occupants must be at least 1.";
        $message_type = "error";

    } elseif (!in_array(
        $property_status,
        [
            'Available',
            'Pending',
            'Occupied',
            'Expired',
            'Unavailable'
        ]
    )) {

        $message = "Invalid property status.";
        $message_type = "error";

    } else {


        /* =====================================
           CHECK PROPERTY OWNERSHIP
        ===================================== */

        $check_sql = "
            SELECT property_id
            FROM properties
            WHERE property_id = ?
              AND landlord_id = ?
            LIMIT 1
        ";

        $stmt = mysqli_prepare($conn, $check_sql);

        mysqli_stmt_bind_param(
            $stmt,
            "ii",
            $property_id,
            $landlord_id
        );

        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        $property_exists = mysqli_fetch_assoc($result);

        mysqli_stmt_close($stmt);


        if (!$property_exists) {

            $message = "Property not found.";
            $message_type = "error";

        } else {


            /* =====================================
               UPDATE DATABASE
            ===================================== */

            $update_sql = "
                UPDATE properties

                SET
                    property_name = ?,
                    property_type = ?,
                    location = ?,
                    description = ?,
                    monthly_rent = ?,
                    bedrooms = ?,
                    bathrooms = ?,
                    max_occupants = ?,
                    property_status = ?

                WHERE property_id = ?
                  AND landlord_id = ?
            ";

            $stmt = mysqli_prepare(
                $conn,
                $update_sql
            );

            mysqli_stmt_bind_param(
                $stmt,
                "ssssdiiisii",
                $property_name,
                $property_type,
                $location,
                $description,
                $monthly_rent,
                $bedrooms,
                $bathrooms,
                $max_occupants,
                $property_status,
                $property_id,
                $landlord_id
            );


            if (mysqli_stmt_execute($stmt)) {

                mysqli_stmt_close($stmt);

                header(
                    "Location: manage_property.php?updated=1"
                );

                exit();

            } else {

                $message =
                    "Failed to update property: " .
                    mysqli_error($conn);

                $message_type = "error";

                mysqli_stmt_close($stmt);
            }
        }
    }
}


/* =========================================
   GET PROPERTY DETAILS
========================================= */

$sql = "
    SELECT
        property_id,
        property_name,
        property_type,
        location,
        description,
        monthly_rent,
        bedrooms,
        bathrooms,
        max_occupants,
        property_status,
        image

    FROM properties

    WHERE property_id = ?
      AND landlord_id = ?

    LIMIT 1
";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $property_id,
    $landlord_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$property = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


if (!$property) {

    header("Location: manage_property.php");
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

    <title>Edit Property | HRMS</title>

    <link
        rel="stylesheet"
        href="assets/css/edit_property_style.css"
    >

</head>


<body>


<div class="page-container">


    <!-- =================================
         HEADER
    ================================== -->

    <header class="page-header">

        <div>

            <h1>Edit Property</h1>

            <p>
                Update your property information.
            </p>

        </div>


        <a
            href="manage_property.php"
            class="back-btn"
        >
            ← Manage Properties
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


        <div class="form-card">


            <!-- =================================
                 PROPERTY IMAGE
            ================================== -->

            <div class="property-preview">

                <?php if (!empty($property['image'])): ?>

                    <img
                        src="uploads/properties/<?php
                        echo htmlspecialchars(
                            $property['image']
                        );
                        ?>"
                        alt="Property Image"
                    >

                <?php else: ?>

                    <div class="image-placeholder">
                        🏠
                    </div>

                <?php endif; ?>


                <div>

                    <h2>
                        <?php
                        echo htmlspecialchars(
                            $property['property_name']
                        );
                        ?>
                    </h2>

                    <p>
                        Property ID:
                        <?php
                        echo $property['property_id'];
                        ?>
                    </p>

                </div>

            </div>


            <!-- =================================
                 FORM
            ================================== -->

            <form method="POST">


                <!-- PROPERTY NAME -->

                <div class="form-group">

                    <label>
                        Property Name *
                    </label>

                    <input
                        type="text"
                        name="property_name"
                        value="<?php
                        echo htmlspecialchars(
                            $property['property_name']
                        );
                        ?>"
                        required
                    >

                </div>


                <!-- PROPERTY TYPE -->

                <div class="form-row">


                    <div class="form-group">

                        <label>
                            Property Type *
                        </label>

                        <select
                            name="property_type"
                            required
                        >

                            <option
                                value="House"
                                <?php
                                echo $property['property_type']
                                    === 'House'
                                    ? 'selected'
                                    : '';
                                ?>
                            >
                                House
                            </option>

                            <option
                                value="Apartment"
                                <?php
                                echo $property['property_type']
                                    === 'Apartment'
                                    ? 'selected'
                                    : '';
                                ?>
                            >
                                Apartment
                            </option>

                            <option
                                value="Room"
                                <?php
                                echo $property['property_type']
                                    === 'Room'
                                    ? 'selected'
                                    : '';
                                ?>
                            >
                                Room
                            </option>

                        </select>

                    </div>


                    <!-- LOCATION -->

                    <div class="form-group">

                        <label>
                            Location *
                        </label>

                        <input
                            type="text"
                            name="location"
                            value="<?php
                            echo htmlspecialchars(
                                $property['location']
                            );
                            ?>"
                            required
                        >

                    </div>

                </div>


                <!-- RENT -->

                <div class="form-group">

                    <label>
                        Monthly Rent (Rs.) *
                    </label>

                    <input
                        type="number"
                        name="monthly_rent"
                        min="1"
                        step="0.01"
                        value="<?php
                        echo htmlspecialchars(
                            $property['monthly_rent']
                        );
                        ?>"
                        required
                    >

                </div>


                <!-- ROOMS -->

                <div class="form-row">


                    <div class="form-group">

                        <label>
                            Bedrooms
                        </label>

                        <input
                            type="number"
                            name="bedrooms"
                            min="1"
                            value="<?php
                            echo $property['bedrooms'];
                            ?>"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Bathrooms
                        </label>

                        <input
                            type="number"
                            name="bathrooms"
                            min="1"
                            value="<?php
                            echo $property['bathrooms'];
                            ?>"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Maximum Occupants
                        </label>

                        <input
                            type="number"
                            name="max_occupants"
                            min="1"
                            value="<?php
                            echo $property['max_occupants'];
                            ?>"
                            required
                        >

                    </div>

                </div>


                <!-- DESCRIPTION -->

                <div class="form-group">

                    <label>
                        Description
                    </label>

                    <textarea
                        name="description"
                        rows="6"
                        placeholder="Describe your property..."
                    ><?php
                    echo htmlspecialchars(
                        $property['description'] ?? ''
                    );
                    ?></textarea>

                </div>


                <!-- STATUS -->

                <div class="form-group">

                    <label>
                        Property Status
                    </label>

                    <select name="property_status">

                        <option
                            value="Available"
                            <?php
                            echo $property['property_status']
                                === 'Available'
                                ? 'selected'
                                : '';
                            ?>
                        >
                            Available
                        </option>

                        <option
                            value="Pending"
                            <?php
                            echo $property['property_status']
                                === 'Pending'
                                ? 'selected'
                                : '';
                            ?>
                        >
                            Pending
                        </option>

                        <option
                            value="Occupied"
                            <?php
                            echo $property['property_status']
                                === 'Occupied'
                                ? 'selected'
                                : '';
                            ?>
                        >
                            Occupied
                        </option>

                        <option
                            value="Expired"
                            <?php
                            echo $property['property_status']
                                === 'Expired'
                                ? 'selected'
                                : '';
                            ?>
                        >
                            Expired
                        </option>

                        <option
                            value="Unavailable"
                            <?php
                            echo $property['property_status']
                                === 'Unavailable'
                                ? 'selected'
                                : '';
                            ?>
                        >
                            Unavailable
                        </option>

                    </select>

                </div>


                <!-- BUTTONS -->

                <div class="form-actions">

                    <a
                        href="manage_property.php"
                        class="cancel-btn"
                    >
                        Cancel
                    </a>


                    <button
                        type="submit"
                        name="update_property"
                        class="update-btn"
                    >
                        Update Property
                    </button>

                </div>


            </form>


        </div>


    </main>


</div>


</body>

</html>