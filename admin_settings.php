<?php

session_start();

require_once __DIR__ . "/config/database/db.php";


/* =====================================================
   ADMIN LOGIN CHECK
===================================================== */

if (!isset($_SESSION['admin_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: adminlogin.php");
    exit();
}


$adminId = (int) $_SESSION['admin_id'];

$success = "";
$error = "";


/* =====================================================
   UPDATE PROFILE
===================================================== */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $firstname = trim($_POST['firstname'] ?? '');
    $lastname  = trim($_POST['lastname'] ?? '');
    $email     = trim($_POST['email'] ?? '');


    /* ---------------------------------------------
       VALIDATION
    --------------------------------------------- */

    if ($firstname === '' || $lastname === '' || $email === '') {

        $error = "Please fill in all fields.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    } else {


        /* ---------------------------------------------
           CHECK IF EMAIL IS USED BY ANOTHER USER
        --------------------------------------------- */

        $checkSql = "
            SELECT id
            FROM users
            WHERE email = ?
            AND id != ?
            LIMIT 1
        ";

        $checkStmt = mysqli_prepare($conn, $checkSql);


        if ($checkStmt) {

            mysqli_stmt_bind_param(
                $checkStmt,
                "si",
                $email,
                $adminId
            );

            mysqli_stmt_execute($checkStmt);

            $checkResult = mysqli_stmt_get_result($checkStmt);


            if (mysqli_num_rows($checkResult) > 0) {

                $error = "This email address is already being used.";

            }

            mysqli_stmt_close($checkStmt);

        } else {

            $error = "Unable to check the email address.";

        }


        /* ---------------------------------------------
           UPDATE USER
        --------------------------------------------- */

        if ($error === "") {

            $updateSql = "
                UPDATE users
                SET firstname = ?,
                    lastname = ?,
                    email = ?
                WHERE id = ?
                AND role = 'admin'
            ";

            $updateStmt = mysqli_prepare(
                $conn,
                $updateSql
            );


            if ($updateStmt) {

                mysqli_stmt_bind_param(
                    $updateStmt,
                    "sssi",
                    $firstname,
                    $lastname,
                    $email,
                    $adminId
                );


                if (mysqli_stmt_execute($updateStmt)) {

                    $success = "Profile updated successfully.";

                    /* Update session name */

                    $_SESSION['admin_name'] =
                        $firstname . " " . $lastname;

                } else {

                    $error = "Unable to update your profile.";

                }


                mysqli_stmt_close($updateStmt);

            } else {

                $error = "Unable to prepare the update query.";

            }

        }

    }

}


/* =====================================================
   GET CURRENT ADMIN DETAILS
===================================================== */

$sql = "
    SELECT id, firstname, lastname, email, role
    FROM users
    WHERE id = ?
    AND role = 'admin'
    LIMIT 1
";

$stmt = mysqli_prepare($conn, $sql);


if (!$stmt) {

    die("Unable to load administrator information.");

}


mysqli_stmt_bind_param(
    $stmt,
    "i",
    $adminId
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$admin = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


/* =====================================================
   IF ADMIN RECORD IS NOT FOUND
===================================================== */

if (!$admin) {

    die("Administrator account could not be found.");

}


$adminName =
    trim(
        $admin['firstname'] .
        " " .
        $admin['lastname']
    );

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Settings | HRMS Admin</title>


    <!-- Material Symbols -->

    <link
        rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined"
    >


    <!-- Settings CSS -->

    <link
        rel="stylesheet"
        href="Assets/css/admin_settings_style.css"
    >

</head>


<body>


<div class="container">


    <!-- =================================================
         SIDEBAR
    ================================================== -->

    <aside>


        <div class="top">

            <h2 class="logo-text">
                HRMS
            </h2>

        </div>


        <nav class="sidebar">


            <a href="admindashboard.php">

                <span class="material-symbols-outlined">
                    dashboard
                </span>

                <h3>
                    Dashboard
                </h3>

            </a>


            <a href="admin_houses.php">

                <span class="material-symbols-outlined">
                    home
                </span>

                <h3>
                    Houses
                </h3>

            </a>


            <a href="admin_landlords.php">

                <span class="material-symbols-outlined">
                    real_estate_agent
                </span>

                <h3>
                    Landlords
                </h3>

            </a>


            <a href="admin_tenants.php">

                <span class="material-symbols-outlined">
                    group
                </span>

                <h3>
                    Tenants
                </h3>

            </a>


            <a href="admin_bookings.php">

                <span class="material-symbols-outlined">
                    calendar_month
                </span>

                <h3>
                    Bookings
                </h3>

            </a>


            <a href="admin_payments.php">

                <span class="material-symbols-outlined">
                    payments
                </span>

                <h3>
                    Payments
                </h3>

            </a>


            <a href="admin_reports.php">

                <span class="material-symbols-outlined">
                    bar_chart
                </span>

                <h3>
                    Reports
                </h3>

            </a>


            <a href="admin_users.php">

                <span class="material-symbols-outlined">
                    manage_accounts
                </span>

                <h3>
                    Users
                </h3>

            </a>


            <a href="admin_messages.php">

                <span class="material-symbols-outlined">
                    chat
                </span>

                <h3>
                    Messages
                </h3>

            </a>


            <a
                href="admin_settings.php"
                class="active"
            >

                <span class="material-symbols-outlined">
                    settings
                </span>

                <h3>
                    Settings
                </h3>

            </a>


            <a
                href="logout.php"
                class="logout"
            >

                <span class="material-symbols-outlined">
                    logout
                </span>

                <h3>
                    Logout
                </h3>

            </a>


        </nav>

    </aside>



    <!-- =================================================
         MAIN
    ================================================== -->

    <main>


        <!-- HEADER -->

        <header class="page-header">


            <div>

                <h1>
                    Settings
                </h1>

                <p>
                    Manage your administrator account information.
                </p>

            </div>


            <div class="profile">

                <?= htmlspecialchars($adminName) ?>

                · Administrator

            </div>


        </header>



        <!-- =================================================
             SETTINGS CONTENT
        ================================================== -->

        <section class="settings-grid">


            <!-- =================================================
                 PROFILE CARD
            ================================================== -->

            <div class="settings-card">


                <div class="card-header">


                    <div class="header-icon">

                        <span class="material-symbols-outlined">
                            person
                        </span>

                    </div>


                    <div>

                        <h2>
                            Administrator Profile
                        </h2>

                        <p>
                            Update your personal account information.
                        </p>

                    </div>


                </div>



                <!-- SUCCESS MESSAGE -->

                <?php if ($success !== ''): ?>

                    <div class="success-message">

                        <span class="material-symbols-outlined">
                            check_circle
                        </span>

                        <?= htmlspecialchars($success) ?>

                    </div>

                <?php endif; ?>



                <!-- ERROR MESSAGE -->

                <?php if ($error !== ''): ?>

                    <div class="error-message">

                        <span class="material-symbols-outlined">
                            error
                        </span>

                        <?= htmlspecialchars($error) ?>

                    </div>

                <?php endif; ?>



                <!-- FORM -->

                <form
                    method="POST"
                    class="settings-form"
                >


                    <!-- FIRST NAME -->

                    <div class="form-group">

                        <label for="firstname">
                            First Name
                        </label>

                        <input
                            type="text"
                            id="firstname"
                            name="firstname"
                            value="<?= htmlspecialchars(
                                $admin['firstname']
                            ) ?>"
                            required
                        >

                    </div>



                    <!-- LAST NAME -->

                    <div class="form-group">

                        <label for="lastname">
                            Last Name
                        </label>

                        <input
                            type="text"
                            id="lastname"
                            name="lastname"
                            value="<?= htmlspecialchars(
                                $admin['lastname']
                            ) ?>"
                            required
                        >

                    </div>



                    <!-- EMAIL -->

                    <div class="form-group full">

                        <label for="email">
                            Email Address
                        </label>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="<?= htmlspecialchars(
                                $admin['email']
                            ) ?>"
                            required
                        >

                    </div>



                    <!-- ROLE -->

                    <div class="form-group full">

                        <label>
                            Account Role
                        </label>

                        <div class="role-display">

                            <span class="material-symbols-outlined">
                                admin_panel_settings
                            </span>

                            Administrator

                        </div>

                    </div>



                    <!-- BUTTON -->

                    <div class="form-actions">

                        <button type="submit">

                            <span class="material-symbols-outlined">
                                save
                            </span>

                            Save Changes

                        </button>

                    </div>


                </form>


            </div>



            <!-- =================================================
                 ACCOUNT INFORMATION
            ================================================== -->

            <div class="settings-card account-card">


                <div class="card-header">


                    <div class="header-icon">

                        <span class="material-symbols-outlined">
                            manage_accounts
                        </span>

                    </div>


                    <div>

                        <h2>
                            Account Information
                        </h2>

                        <p>
                            Basic information about your admin account.
                        </p>

                    </div>


                </div>



                <div class="account-info">


                    <div class="info-row">

                        <span class="info-label">
                            User ID
                        </span>

                        <strong>
                            #<?= (int)$admin['id'] ?>
                        </strong>

                    </div>


                    <div class="info-row">

                        <span class="info-label">
                            Account Type
                        </span>

                        <strong>
                            Administrator
                        </strong>

                    </div>


                    <div class="info-row">

                        <span class="info-label">
                            Email
                        </span>

                        <strong class="email-value">
                            <?= htmlspecialchars(
                                $admin['email']
                            ) ?>
                        </strong>

                    </div>


                    <div class="info-row">

                        <span class="info-label">
                            Access
                        </span>

                        <span class="access-badge">
                            Full Admin Access
                        </span>

                    </div>


                </div>


                <div class="security-note">


                    <span class="material-symbols-outlined">
                        security
                    </span>


                    <div>

                        <strong>
                            Administrator account
                        </strong>

                        <p>
                            This account has access to the HRMS
                            administration module.
                        </p>

                    </div>


                </div>


            </div>


        </section>



        <!-- =================================================
             SYSTEM INFORMATION
        ================================================== -->

        <section class="system-card">


            <div class="system-icon">

                <span class="material-symbols-outlined">
                    apartment
                </span>

            </div>


            <div>

                <h2>
                    HOMY House Rental Management System
                </h2>

                <p>
                    Administrator settings and account management.
                </p>

            </div>


        </section>


    </main>


</div>


</body>

</html>