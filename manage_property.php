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
   DELETE PROPERTY
========================================= */

if (isset($_POST['delete_property'])) {

    $property_id = intval($_POST['property_id']);


    /* Check that property belongs to landlord */

    $check_sql = "
        SELECT property_id, property_status
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

    $property = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);


    if (!$property) {

        $message = "Property not found.";
        $message_type = "error";

    } elseif ($property['property_status'] === 'Occupied') {

        /*
         * Do not delete an occupied property.
         */
        $message =
            "Occupied properties cannot be deleted.";

        $message_type = "error";

    } else {

        $delete_sql = "
            DELETE FROM properties
            WHERE property_id = ?
              AND landlord_id = ?
        ";

        $stmt = mysqli_prepare($conn, $delete_sql);

        mysqli_stmt_bind_param(
            $stmt,
            "ii",
            $property_id,
            $landlord_id
        );

        if (mysqli_stmt_execute($stmt)) {

            $message = "Property deleted successfully.";
            $message_type = "success";

        } else {

            $message =
                "Unable to delete property: " .
                mysqli_error($conn);

            $message_type = "error";
        }

        mysqli_stmt_close($stmt);
    }
}


/* =========================================
   FILTER
========================================= */

$status_filter = $_GET['status'] ?? 'All';

$allowed_statuses = [
    'Available',
    'Pending',
    'Occupied',
    'Expired',
    'Unavailable'
];


/* =========================================
   SEARCH
========================================= */

$search = trim($_GET['search'] ?? '');


/* =========================================
   GET PROPERTIES
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
        image,
        created_at,
        updated_at

    FROM properties

    WHERE landlord_id = ?
";


/* Status filter */

if (
    $status_filter !== 'All' &&
    in_array($status_filter, $allowed_statuses)
) {

    $sql .= "
        AND property_status = ?
    ";
}


/* Search */

if (!empty($search)) {

    $sql .= "
        AND (
            property_name LIKE ?
            OR location LIKE ?
            OR property_type LIKE ?
        )
    ";
}


$sql .= "
    ORDER BY created_at DESC
";


/* =========================================
   PREPARE QUERY
========================================= */

$stmt = mysqli_prepare($conn, $sql);


/* Bind parameters */

if (
    $status_filter !== 'All' &&
    in_array($status_filter, $allowed_statuses)
) {

    if (!empty($search)) {

        $search_value = "%" . $search . "%";

        mysqli_stmt_bind_param(
            $stmt,
            "issss",
            $landlord_id,
            $status_filter,
            $search_value,
            $search_value,
            $search_value
        );

    } else {

        mysqli_stmt_bind_param(
            $stmt,
            "is",
            $landlord_id,
            $status_filter
        );
    }

} else {

    if (!empty($search)) {

        $search_value = "%" . $search . "%";

        mysqli_stmt_bind_param(
            $stmt,
            "isss",
            $landlord_id,
            $search_value,
            $search_value,
            $search_value
        );

    } else {

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $landlord_id
        );
    }
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

    <title>Manage Properties | HRMS</title>

    <link
        rel="stylesheet"
        href="assets/css/manage_property_style.css"
    >

</head>


<body>


<div class="page-container">


    <!-- =================================
         HEADER
    ================================== -->

    <header class="page-header">

        <div>

            <h1>Manage Properties</h1>

            <p>
                View and manage your rental properties.
            </p>

        </div>


        <div class="header-actions">

            <a
                href="add_property.php"
                class="add-btn"
            >
                + Add Property
            </a>


            <a
                href="landlorddashboard.php"
                class="back-btn"
            >
                ← Dashboard
            </a>

        </div>

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
             FILTER / SEARCH
        ================================== -->

        <div class="filter-bar">

            <form method="GET">


                <input
                    type="text"
                    name="search"
                    placeholder="Search property..."
                    value="<?php echo htmlspecialchars($search); ?>"
                >


                <select name="status">

                    <option
                        value="All"
                        <?php
                        echo $status_filter === 'All'
                            ? 'selected'
                            : '';
                        ?>
                    >
                        All Properties
                    </option>


                    <option
                        value="Available"
                        <?php
                        echo $status_filter === 'Available'
                            ? 'selected'
                            : '';
                        ?>
                    >
                        Available
                    </option>


                    <option
                        value="Pending"
                        <?php
                        echo $status_filter === 'Pending'
                            ? 'selected'
                            : '';
                        ?>
                    >
                        Pending
                    </option>


                    <option
                        value="Occupied"
                        <?php
                        echo $status_filter === 'Occupied'
                            ? 'selected'
                            : '';
                        ?>
                    >
                        Occupied
                    </option>


                    <option
                        value="Expired"
                        <?php
                        echo $status_filter === 'Expired'
                            ? 'selected'
                            : '';
                        ?>
                    >
                        Expired
                    </option>


                    <option
                        value="Unavailable"
                        <?php
                        echo $status_filter === 'Unavailable'
                            ? 'selected'
                            : '';
                        ?>
                    >
                        Unavailable
                    </option>

                </select>


                <button
                    type="submit"
                    class="search-btn"
                >
                    Search
                </button>


            </form>

        </div>


        <!-- =================================
             PROPERTY TABLE
        ================================== -->

        <div class="properties-card">


            <?php if (mysqli_num_rows($result) > 0): ?>


                <div class="table-container">

                    <table>

                        <thead>

                            <tr>

                                <th>Property</th>

                                <th>Type</th>

                                <th>Location</th>

                                <th>Rent</th>

                                <th>Rooms</th>

                                <th>Occupants</th>

                                <th>Status</th>

                                <th>Action</th>

                            </tr>

                        </thead>


                        <tbody>


                        <?php while ($row = mysqli_fetch_assoc($result)): ?>


                            <tr>


                                <!-- PROPERTY -->

                                <td>

                                    <div class="property-info">

                                        <?php if (
                                            !empty($row['image'])
                                        ): ?>

                                            <img
                                                src="uploads/properties/<?php
                                                echo htmlspecialchars(
                                                    $row['image']
                                                );
                                                ?>"
                                                alt="Property"
                                            >

                                        <?php else: ?>

                                            <div class="property-placeholder">
                                                🏠
                                            </div>

                                        <?php endif; ?>


                                        <div>

                                            <strong>

                                                <?php
                                                echo htmlspecialchars(
                                                    $row['property_name']
                                                );
                                                ?>

                                            </strong>

                                            <small>

                                                ID:
                                                <?php
                                                echo $row['property_id'];
                                                ?>

                                            </small>

                                        </div>

                                    </div>

                                </td>


                                <!-- TYPE -->

                                <td>

                                    <?php
                                    echo htmlspecialchars(
                                        $row['property_type']
                                    );
                                    ?>

                                </td>


                                <!-- LOCATION -->

                                <td>

                                    <?php
                                    echo htmlspecialchars(
                                        $row['location']
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


                                <!-- ROOMS -->

                                <td>

                                    <?php
                                    echo $row['bedrooms'];
                                    ?>
                                    Bed /
                                    <?php
                                    echo $row['bathrooms'];
                                    ?>
                                    Bath

                                </td>


                                <!-- OCCUPANTS -->

                                <td>

                                    <?php
                                    echo $row['max_occupants'];
                                    ?>

                                </td>


                                <!-- STATUS -->

                                <td>

                                    <span
                                        class="status <?php
                                        echo strtolower(
                                            $row['property_status']
                                        );
                                        ?>"
                                    >

                                        <?php
                                        echo htmlspecialchars(
                                            $row['property_status']
                                        );
                                        ?>

                                    </span>

                                </td>


                                <!-- ACTION -->

                                <td>

                                    <div class="action-buttons">


                                        <!-- EDIT -->

                                        <a
                                            href="edit_property.php?id=<?php
                                            echo $row['property_id'];
                                            ?>"
                                            class="edit-btn"
                                        >
                                            Edit
                                        </a>


                                        <?php if (
                                            $row['property_status']
                                            !== 'Occupied'
                                        ): ?>


                                            <!-- DELETE -->

                                            <form
                                                method="POST"
                                                onsubmit="
                                                    return confirm(
                                                        'Are you sure you want to delete this property?'
                                                    );
                                                "
                                            >

                                                <input
                                                    type="hidden"
                                                    name="property_id"
                                                    value="<?php
                                                    echo $row['property_id'];
                                                    ?>"
                                                >

                                                <button
                                                    type="submit"
                                                    name="delete_property"
                                                    class="delete-btn"
                                                >
                                                    Delete
                                                </button>

                                            </form>


                                        <?php endif; ?>


                                    </div>

                                </td>


                            </tr>


                        <?php endwhile; ?>


                        </tbody>

                    </table>

                </div>


            <?php else: ?>


                <!-- EMPTY -->

                <div class="empty-state">

                    <div class="icon">
                        🏠
                    </div>

                    <h2>
                        No Properties Found
                    </h2>

                    <p>
                        You have not added any properties yet.
                    </p>


                    <a
                        href="add_property.php"
                        class="empty-add-btn"
                    >
                        + Add Your First Property
                    </a>

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