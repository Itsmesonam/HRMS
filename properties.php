<?php
session_start();

/* =========================================
   DATABASE CONNECTION
========================================= */
require_once __DIR__ . "/config/database/db.php";


/* =========================================
   LANDLORD ACCESS ONLY
========================================= */
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'landlord') {
    header("Location: index.php");
    exit();
}

$landlord_id = $_SESSION['user_id'];

$message = "";
$error = "";


/* =========================================
   ADD PROPERTY
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

    /* -----------------------------------------
       BASIC VALIDATION
    ----------------------------------------- */

    if (
        empty($property_name) ||
        empty($property_type) ||
        empty($location) ||
        empty($monthly_rent)
    ) {
        $error = "Please fill in all required fields.";
    } else {

        /* -----------------------------------------
           IMAGE UPLOAD
        ----------------------------------------- */

        $image_name = NULL;

        if (isset($_FILES['property_image']) &&
            $_FILES['property_image']['error'] === UPLOAD_ERR_OK) {

            $upload_dir = __DIR__ . "/uploads/properties/";

            /* Create folder if it doesn't exist */
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $original_name = $_FILES['property_image']['name'];
            $tmp_name = $_FILES['property_image']['tmp_name'];

            $extension = strtolower(
                pathinfo($original_name, PATHINFO_EXTENSION)
            );

            $allowed_extensions = [
                'jpg',
                'jpeg',
                'png',
                'webp'
            ];

            if (!in_array($extension, $allowed_extensions)) {

                $error = "Only JPG, JPEG, PNG and WEBP images are allowed.";

            } else {

                /* Generate unique filename */
                $image_name =
                    time() . "_" .
                    uniqid() . "." .
                    $extension;

                $image_path = $upload_dir . $image_name;

                if (!move_uploaded_file($tmp_name, $image_path)) {
                    $error = "Failed to upload image.";
                }
            }
        }


        /* -----------------------------------------
           INSERT PROPERTY
        ----------------------------------------- */

        if (empty($error)) {

            $sql = "INSERT INTO properties
                    (
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
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Available', ?)";

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

                    $message = "Property added successfully!";

                    /* Clear form values */
                    $property_name = "";
                    $location = "";
                    $description = "";
                    $monthly_rent = "";
                    $bedrooms = 1;
                    $bathrooms = 1;
                    $max_occupants = 1;

                } else {

                    $error = "Failed to add property: "
                           . mysqli_error($conn);
                }

                mysqli_stmt_close($stmt);

            } else {

                $error = "Database error: "
                       . mysqli_error($conn);
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

    <title>Add Property - HRMS</title>

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f4f7fb;
            color: #333;
        }

        /* =====================================
           TOP BAR
        ===================================== */

        .topbar {
            height: 65px;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            border-bottom: 1px solid #e5e7eb;
        }

        .logo {
            font-size: 22px;
            font-weight: bold;
            color: #2563eb;
        }

        .user-info {
            font-size: 14px;
            color: #555;
        }

        /* =====================================
           MAIN CONTAINER
        ===================================== */

        .container {
            width: 90%;
            max-width: 1000px;
            margin: 35px auto;
        }

        .page-title {
            margin-bottom: 25px;
        }

        .page-title h1 {
            font-size: 28px;
            margin-bottom: 7px;
        }

        .page-title p {
            color: #777;
        }

        /* =====================================
           ALERTS
        ===================================== */

        .success {
            background: #dcfce7;
            color: #166534;
            padding: 14px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .error {
            background: #fee2e2;
            color: #991b1b;
            padding: 14px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        /* =====================================
           FORM CARD
        ===================================== */

        .card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
        }

        .form-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 25px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .full {
            grid-column: 1 / 3;
        }

        label {
            font-weight: bold;
            margin-bottom: 8px;
            font-size: 14px;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #d1d5db;
            border-radius: 7px;
            font-size: 14px;
            outline: none;
        }

        input:focus,
        select:focus,
        textarea:focus {
            border-color: #2563eb;
        }

        textarea {
            resize: vertical;
            min-height: 120px;
        }

        .required {
            color: red;
        }

        /* =====================================
           BUTTON
        ===================================== */

        .button-area {
            margin-top: 25px;
            display: flex;
            gap: 12px;
        }

        .btn {
            border: none;
            padding: 13px 25px;
            border-radius: 7px;
            cursor: pointer;
            font-size: 15px;
            font-weight: bold;
        }

        .btn-primary {
            background: #2563eb;
            color: white;
        }

        .btn-primary:hover {
            background: #1d4ed8;
        }

        .btn-secondary {
            background: #e5e7eb;
            color: #333;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }

        .btn-secondary:hover {
            background: #d1d5db;
        }

        /* =====================================
           RESPONSIVE
        ===================================== */

        @media (max-width: 700px) {

            .form-grid {
                grid-template-columns: 1fr;
            }

            .full {
                grid-column: 1;
            }

            .container {
                width: 95%;
            }

            .card {
                padding: 20px;
            }

        }

    </style>

</head>

<body>


<!-- =========================================
     TOP BAR
========================================= -->

<div class="topbar">

    <div class="logo">
        HRMS
    </div>

    <div class="user-info">
        Landlord Panel
    </div>

</div>


<!-- =========================================
     MAIN
========================================= -->

<div class="container">

    <div class="page-title">

        <h1>Add Property</h1>

        <p>
            Add your house, apartment or room for tenants.
        </p>

    </div>


    <?php if (!empty($message)): ?>

        <div class="success">
            <?php echo htmlspecialchars($message); ?>
        </div>

    <?php endif; ?>


    <?php if (!empty($error)): ?>

        <div class="error">
            <?php echo htmlspecialchars($error); ?>
        </div>

    <?php endif; ?>


    <!-- =====================================
         FORM
    ====================================== -->

    <div class="card">

        <div class="form-title">
            Property Information
        </div>


        <form method="POST"
              enctype="multipart/form-data">


            <div class="form-grid">


                <!-- PROPERTY NAME -->

                <div class="form-group">

                    <label>
                        Property Name
                        <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        name="property_name"
                        placeholder="Example: Sunrise Apartment"
                        value="<?php echo htmlspecialchars($property_name ?? ''); ?>"
                        required
                    >

                </div>


                <!-- PROPERTY TYPE -->

                <div class="form-group">

                    <label>
                        Property Type
                        <span class="required">*</span>
                    </label>

                    <select name="property_type" required>

                        <option value="">
                            Select property type
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

                <div class="form-group full">

                    <label>
                        Location
                        <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        name="location"
                        placeholder="Example: Dillibazar, Kathmandu"
                        value="<?php echo htmlspecialchars($location ?? ''); ?>"
                        required
                    >

                </div>


                <!-- MONTHLY RENT -->

                <div class="form-group">

                    <label>
                        Monthly Rent (NPR)
                        <span class="required">*</span>
                    </label>

                    <input
                        type="number"
                        name="monthly_rent"
                        min="0"
                        step="0.01"
                        placeholder="Example: 25000"
                        value="<?php echo htmlspecialchars($monthly_rent ?? ''); ?>"
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
                        value="<?php echo htmlspecialchars($max_occupants ?? 1); ?>"
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
                        value="<?php echo htmlspecialchars($bedrooms ?? 1); ?>"
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
                        value="<?php echo htmlspecialchars($bathrooms ?? 1); ?>"
                    >

                </div>


                <!-- DESCRIPTION -->

                <div class="form-group full">

                    <label>
                        Description
                    </label>

                    <textarea
                        name="description"
                        placeholder="Describe your property..."
                    ><?php echo htmlspecialchars($description ?? ''); ?></textarea>

                </div>


                <!-- IMAGE -->

                <div class="form-group full">

                    <label>
                        Property Image
                    </label>

                    <input
                        type="file"
                        name="property_image"
                        accept=".jpg,.jpeg,.png,.webp"
                    >

                </div>


            </div>


            <!-- BUTTONS -->

            <div class="button-area">

                <button
                    type="submit"
                    name="add_property"
                    class="btn btn-primary"
                >
                    Add Property
                </button>

                <a
                    href="landlord.php"
                    class="btn btn-secondary"
                >
                    Cancel
                </a>

            </div>


        </form>

    </div>

</div>

</body>

</html>