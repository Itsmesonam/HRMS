<?php

session_start();

require_once __DIR__ . "/config/database/db.php";

/*-- Landlord Access Check --*/

if (
    !isset($_SESSION['user_id']) ||
    strtolower($_SESSION['role']) !== 'landlord'
) {
    header("Location: login.php");
    exit();
}

$landlord_id = $_SESSION['user_id'];

$message = "";
$error = "";


/*-- Update Profile --*/

if (isset($_POST['update_profile'])) {

    $firstname = trim($_POST['firstname']);
    $lastname  = trim($_POST['lastname']);
    $gender    = trim($_POST['gender']);
    $email     = trim($_POST['email']);
    $phone     = trim($_POST['phone']);
    $address   = trim($_POST['address']);

    if (
        empty($firstname) ||
        empty($lastname) ||
        empty($email) ||
        empty($phone) ||
        empty($address)
    ) {

        $error = "Please fill in all required fields.";

    } else {

        /*-- Check Email --*/

        $check_sql = "
            SELECT id
            FROM users
            WHERE email = ?
              AND id != ?
            LIMIT 1
        ";

        $check_stmt = mysqli_prepare(
            $conn,
            $check_sql
        );

        mysqli_stmt_bind_param(
            $check_stmt,
            "si",
            $email,
            $landlord_id
        );

        mysqli_stmt_execute($check_stmt);

        $check_result = mysqli_stmt_get_result(
            $check_stmt
        );

        if (mysqli_num_rows($check_result) > 0) {

            $error = "This email address is already in use.";

        } else {

            /*-- Update User --*/

            $update_sql = "
                UPDATE users
                SET
                    firstname = ?,
                    lastname = ?,
                    gender = ?,
                    email = ?,
                    phone = ?,
                    address = ?
                WHERE id = ?
            ";

            $update_stmt = mysqli_prepare(
                $conn,
                $update_sql
            );

            mysqli_stmt_bind_param(
                $update_stmt,
                "ssssssi",
                $firstname,
                $lastname,
                $gender,
                $email,
                $phone,
                $address,
                $landlord_id
            );

            if (mysqli_stmt_execute($update_stmt)) {

                $message = "Profile updated successfully.";

            } else {

                $error = "Unable to update profile.";

            }

            mysqli_stmt_close($update_stmt);
        }

        mysqli_stmt_close($check_stmt);
    }
}


/*-- Get Landlord Profile --*/

$sql = "
    SELECT
        id,
        firstname,
        lastname,
        gender,
        role,
        email,
        phone,
        address
    FROM users
    WHERE id = ?
    LIMIT 1
";

$stmt = mysqli_prepare(
    $conn,
    $sql
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $landlord_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$landlord = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Landlord Profile | HRMS</title>

    <link
        rel="stylesheet"
        href="/hrms/Assets/css/landlord_profile_style.css"
    >

</head>

<body>

<div class="profile-container">

    <!-- Page Header -->

    <div class="page-header">

        <div>

            <h1>My Profile</h1>

            <p>
                View and manage your personal information.
            </p>

        </div>

        <a
            href="landlorddashboard.php"
            class="back-btn"
        >
            ← Dashboard
        </a>

    </div>


    <!-- Profile Card -->

    <div class="profile-card">

        <!-- Profile Header -->

        <div class="profile-header">

            <div class="profile-avatar">

                <?php
                echo strtoupper(
                    substr(
                        $landlord['firstname'],
                        0,
                        1
                    )
                );
                ?>

            </div>

            <div>

                <h2>

                    <?php
                    echo htmlspecialchars(
                        $landlord['firstname']
                        . " "
                        . $landlord['lastname']
                    );
                    ?>

                </h2>

                <span class="role-badge">
                    Landlord
                </span>

            </div>

        </div>


        <!-- Success Message -->

        <?php if (!empty($message)): ?>

            <div class="success-message">

                <?php
                echo htmlspecialchars($message);
                ?>

            </div>

        <?php endif; ?>


        <!-- Error Message -->

        <?php if (!empty($error)): ?>

            <div class="error-message">

                <?php
                echo htmlspecialchars($error);
                ?>

            </div>

        <?php endif; ?>


        <!-- Profile Form -->

        <form method="POST">

            <div class="form-grid">

                <!-- First Name -->

                <div class="form-group">

                    <label>
                        First Name
                    </label>

                    <input
                        type="text"
                        name="firstname"
                        value="<?php
                        echo htmlspecialchars(
                            $landlord['firstname']
                        );
                        ?>"
                        required
                    >

                </div>


                <!-- Last Name -->

                <div class="form-group">

                    <label>
                        Last Name
                    </label>

                    <input
                        type="text"
                        name="lastname"
                        value="<?php
                        echo htmlspecialchars(
                            $landlord['lastname']
                        );
                        ?>"
                        required
                    >

                </div>


                <!-- Gender -->

                <div class="form-group">

                    <label>
                        Gender
                    </label>

                    <select name="gender">

                        <option value="">
                            Select Gender
                        </option>

                        <option
                            value="Male"
                            <?php
                            echo (
                                $landlord['gender'] === 'Male'
                            )
                                ? 'selected'
                                : '';
                            ?>
                        >
                            Male
                        </option>

                        <option
                            value="Female"
                            <?php
                            echo (
                                $landlord['gender'] === 'Female'
                            )
                                ? 'selected'
                                : '';
                            ?>
                        >
                            Female
                        </option>

                        <option
                            value="Other"
                            <?php
                            echo (
                                $landlord['gender'] === 'Other'
                            )
                                ? 'selected'
                                : '';
                            ?>
                        >
                            Other
                        </option>

                    </select>

                </div>


                <!-- Role -->

                <div class="form-group">

                    <label>
                        Role
                    </label>

                    <input
                        type="text"
                        value="Landlord"
                        disabled
                    >

                </div>


                <!-- Email -->

                <div class="form-group">

                    <label>
                        Email
                    </label>

                    <input
                        type="email"
                        name="email"
                        value="<?php
                        echo htmlspecialchars(
                            $landlord['email']
                        );
                        ?>"
                        required
                    >

                </div>


                <!-- Phone -->

                <div class="form-group">

                    <label>
                        Phone
                    </label>

                    <input
                        type="text"
                        name="phone"
                        value="<?php
                        echo htmlspecialchars(
                            $landlord['phone']
                        );
                        ?>"
                        required
                    >

                </div>


                <!-- Address -->

                <div class="form-group full-width">

                    <label>
                        Address
                    </label>

                    <textarea
                        name="address"
                        rows="4"
                        required
                    ><?php
                    echo htmlspecialchars(
                        $landlord['address']
                    );
                    ?></textarea>

                </div>

            </div>


            <!-- Update Button -->

            <div class="form-actions">

                <button
                    type="submit"
                    name="update_profile"
                    class="update-btn"
                >
                    Update Profile
                </button>

            </div>

        </form>

    </div>

</div>

</body>

</html>