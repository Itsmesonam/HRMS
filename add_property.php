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
$error = "";


/* =========================================
   FORM SUBMISSION
========================================= */

if (isset($_POST['add_property'])) {

    $property_name = trim($_POST['property_name']);
    $property_type = $_POST['property_type'];
    $location = trim($_POST['location']);
    $description = trim($_POST['description']);

    $monthly_rent = $_POST['monthly_rent'];
    $bedrooms = $_POST['bedrooms'];
    $bathrooms = $_POST['bathrooms'];
    $max_occupants = $_POST['max_occupants'];


    /* =====================================
       BASIC VALIDATION
    ===================================== */

    if (
        empty($property_name) ||
        empty($property_type) ||
        empty($location) ||
        empty($monthly_rent)
    ) {

        $error = "Please fill in all required fields.";

    } else {


        /* =================================
           IMAGE UPLOAD
        ================================= */

        $image_name = null;

        if (
            isset($_FILES['property_image']) &&
            $_FILES['property_image']['error'] !== UPLOAD_ERR_NO_FILE
        ) {

            if (
                $_FILES['property_image']['error'] !== UPLOAD_ERR_OK
            ) {

                $error = "There was a problem uploading the image.";

            } else {

                $upload_dir =
                    __DIR__ . "/uploads/properties/";

                /* Create upload folder */

                if (!is_dir($upload_dir)) {

                    mkdir(
                        $upload_dir,
                        0777,
                        true
                    );
                }


                $original_name =
                    $_FILES['property_image']['name'];

                $tmp_name =
                    $_FILES['property_image']['tmp_name'];


                $extension = strtolower(
                    pathinfo(
                        $original_name,
                        PATHINFO_EXTENSION
                    )
                );


                $allowed_extensions = [
                    "jpg",
                    "jpeg",
                    "png",
                    "webp"
                ];


                if (
                    !in_array(
                        $extension,
                        $allowed_extensions
                    )
                ) {

                    $error =
                        "Only JPG, JPEG, PNG and WEBP images are allowed.";

                } else {

                    $image_name =
                        time() . "_" .
                        uniqid() . "." .
                        $extension;


                    $image_path =
                        $upload_dir . $image_name;


                    if (
                        !move_uploaded_file(
                            $tmp_name,
                            $image_path
                        )
                    ) {

                        $error =
                            "Failed to save the image.";
                    }
                }
            }
        }


        /* =================================
           INSERT INTO DATABASE
        ================================= */

        if (empty($error)) {

            $sql = "
                INSERT INTO properties (
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
                    image
                )
                VALUES (
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    'Available',
                    ?
                )
            ";


            $stmt = mysqli_prepare($conn, $sql);


            if ($stmt) {

                mysqli_stmt_bind_param(
                    $stmt,
                    "issssdiiis",
                    $landlord_id,
                    $property_name,
                    $property_type,
                    $location,
                    $description,
                    $monthly_rent,
                    $bedrooms,
                    $bathrooms,
                    $max_occupants,
                    $image_name
                );


                if (mysqli_stmt_execute($stmt)) {

                    $message =
                        "Property added successfully!";

                    /* Clear form */

                    $property_name = "";
                    $location = "";
                    $description = "";
                    $monthly_rent = "";

                } else {

                    $error =
                        "Failed to add property: " .
                        mysqli_stmt_error($stmt);
                }


                mysqli_stmt_close($stmt);

            } else {

                $error =
                    "Database query failed: " .
                    mysqli_error($conn);
            }
        }
    }
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

    <title>Add Property | HRMS</title>


    <link
        rel="stylesheet"
        href="assets/css/add_property_style.css"
    >

</head>


<body>


<div class="page-container">


    <!-- HEADER -->

    <header class="page-header">

        <div>

            <h1>Add Property</h1>

            <p>
                Add your property for tenants to rent.
            </p>

        </div>


        <a
            href="landlord.php"
            class="back-btn"
        >
            ← Back
        </a>

    </header>



    <!-- MAIN -->

    <main>


        <?php if (!empty($message)): ?>

            <div class="success-message">

                <?php
                echo htmlspecialchars($message);
                ?>

            </div>

        <?php endif; ?>


        <?php if (!empty($error)): ?>

            <div class="error-message">

                <?php
                echo htmlspecialchars($error);
                ?>

            </div>

        <?php endif; ?>



        <!-- FORM CARD -->

        <div class="form-card">


            <div class="form-header">

                <h2>Property Information</h2>

                <p>
                    Enter the details of your house,
                    apartment or room.
                </p>

            </div>



            <form
                method="POST"
                enctype="multipart/form-data"
            >


                <div class="form-grid">


                    <!-- PROPERTY NAME -->

                    <div class="form-group">

                        <label>
                            Property Name *
                        </label>

                        <input
                            type="text"
                            name="property_name"
                            placeholder="Example: Sunrise Apartment"
                            value="<?php
                            echo htmlspecialchars(
                                $property_name ?? ''
                            );
                            ?>"
                            required
                        >

                    </div>



                    <!-- PROPERTY TYPE -->

                    <div class="form-group">

                        <label>
                            Property Type *
                        </label>

                        <select
                            name="property_type"
                            required
                        >

                            <option value="">
                                Select Property Type
                            </option>

                            <option value="House">
                                House
                            </option>

                            <option value="Apartment">
                                Apartment
                            </option>

                            <option value="Room">
                                Room
                            </option>

                        </select>

                    </div>



                    <!-- LOCATION -->

                    <div class="form-group full-width">

                        <label>
                            Location *
                        </label>

                        <input
                            type="text"
                            name="location"
                            placeholder="Example: Dillibazar, Kathmandu"
                            value="<?php
                            echo htmlspecialchars(
                                $location ?? ''
                            );
                            ?>"
                            required
                        >

                    </div>



                    <!-- MONTHLY RENT -->

                    <div class="form-group">

                        <label>
                            Monthly Rent (Rs.) *
                        </label>

                        <input
                            type="number"
                            name="monthly_rent"
                            placeholder="Example: 25000"
                            min="0"
                            step="0.01"
                            value="<?php
                            echo htmlspecialchars(
                                $monthly_rent ?? ''
                            );
                            ?>"
                            required
                        >

                    </div>



                    <!-- MAX OCCUPANTS -->

                    <div class="form-group">

                        <label>
                            Maximum Occupants
                        </label>

                        <input
                            type="number"
                            name="max_occupants"
                            min="1"
                            value="1"
                            required
                        >

                    </div>



                    <!-- BEDROOMS -->

                    <div class="form-group">

                        <label>
                            Bedrooms
                        </label>

                        <input
                            type="number"
                            name="bedrooms"
                            min="0"
                            value="1"
                        >

                    </div>



                    <!-- BATHROOMS -->

                    <div class="form-group">

                        <label>
                            Bathrooms
                        </label>

                        <input
                            type="number"
                            name="bathrooms"
                            min="0"
                            value="1"
                        >

                    </div>



                    <!-- DESCRIPTION -->

                    <div class="form-group full-width">

                        <label>
                            Description
                        </label>

                        <textarea
                            name="description"
                            placeholder="Describe your property..."
                        ><?php
                        echo htmlspecialchars(
                            $description ?? ''
                        );
                        ?></textarea>

                    </div>



                    <!-- IMAGE -->

                    <div class="form-group full-width">

                        <label>
                            Property Image
                        </label>

                        <input
                            type="file"
                            name="property_image"
                            accept=".jpg,.jpeg,.png,.webp"
                        >

                        <small>
                            Allowed: JPG, JPEG, PNG, WEBP
                        </small>

                    </div>


                </div>



                <!-- BUTTONS -->

                <div class="button-group">

                    <button
                        type="submit"
                        name="add_property"
                        class="add-btn"
                    >
                        Add Property
                    </button>


                    <a
                        href="landlord.php"
                        class="cancel-btn"
                    >
                        Cancel
                    </a>

                </div>


            </form>


        </div>


    </main>


</div>


</body>

</html>