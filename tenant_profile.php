<?php

session_start();

require_once __DIR__ . "/config/database/db.php";

/*-- Tenant Access Check --*/

if (
    !isset($_SESSION['user_id']) ||
    strtolower($_SESSION['role']) !== 'tenant'
) {
    header("Location: login.php");
    exit();
}

$tenant_id = $_SESSION['user_id'];

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
            $tenant_id
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
                $tenant_id
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


/*-- Get Tenant Profile --*/

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
    $tenant_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$tenant = mysqli_fetch_assoc($result);

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

    <title>My Profile | HRMS</title>

    <link
        rel="stylesheet"
        href="/hrms/Assets/css/tenant_profile_style.css"
    >

</head>

<body>

<div class="profile-container">

    <!-- =========================================
         PAGE HEADER
    ========================================== -->

    <div class="page-header">

        <div>

            <h1>My Profile</h1>

            <p>
                View and manage your personal information.
            </p>

        </div>

        <a
            href="tenantdashboard.php"
            class="back-btn"
        >
            ← Dashboard
        </a>

    </div>


    <!-- =========================================
         PROFILE CARD
    ========================================== -->

    <div class="profile-card">

        <!-- PROFILE HEADER -->

        <div class="profile-header">

            <div class="profile-avatar">

                <?php
                echo strtoupper(
                    substr(
                        $tenant['firstname'],
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
                        $tenant['firstname']
                        . " "
                        . $tenant['lastname']
                    );
                    ?>

                </h2>

                <span class="role-badge">
                    Tenant
                </span>

            </div>

        </div>


        <!-- MESSAGES -->

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


        <!-- =========================================
             PROFILE FORM
        ========================================== -->

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
                            $tenant['firstname']
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
                            $tenant['lastname']
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
                                $tenant['gender'] === 'Male'
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
                                $tenant['gender'] === 'Female'
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
                                $tenant['gender'] === 'Other'
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
                        value="Tenant"
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
                            $tenant['email']
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
                            $tenant['phone']
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
                        $tenant['address']
                    );
                    ?></textarea>

                </div>

            </div>


            <!-- BUTTON -->

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