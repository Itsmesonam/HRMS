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


/*-- Change Password --*/

if (isset($_POST['change_password'])) {

    $current_password = $_POST['current_password'];
    $new_password     = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];


    if (
        empty($current_password) ||
        empty($new_password) ||
        empty($confirm_password)
    ) {

        $error = "Please fill in all password fields.";

    }

    elseif (strlen($new_password) < 6) {

        $error = "New password must be at least 6 characters.";

    }

    elseif ($new_password !== $confirm_password) {

        $error = "New passwords do not match.";

    }

    else {

        /*-- Get Current Password --*/

        $sql = "
            SELECT password
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

        $user = mysqli_fetch_assoc($result);

        mysqli_stmt_close($stmt);


        /*-- Verify Current Password --*/

        if (
            !$user ||
            !password_verify(
                $current_password,
                $user['password']
            )
        ) {

            $error = "Current password is incorrect.";

        } else {

            /*-- Hash New Password --*/

            $hashed_password = password_hash(
                $new_password,
                PASSWORD_DEFAULT
            );


            /*-- Update Password --*/

            $update_sql = "
                UPDATE users
                SET password = ?
                WHERE id = ?
            ";

            $update_stmt = mysqli_prepare(
                $conn,
                $update_sql
            );

            mysqli_stmt_bind_param(
                $update_stmt,
                "si",
                $hashed_password,
                $landlord_id
            );

            if (mysqli_stmt_execute($update_stmt)) {

                $message = "Password changed successfully.";

            } else {

                $error = "Unable to change password.";

            }

            mysqli_stmt_close($update_stmt);
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

    <title>Landlord Settings | HRMS</title>

    <link
        rel="stylesheet"
        href="/hrms/Assets/css/landlord_settings_style.css"
    >

</head>

<body>

<div class="settings-container">

    <!-- Page Header -->

    <div class="page-header">

        <div>

            <h1>Settings</h1>

            <p>
                Manage your account security and settings.
            </p>

        </div>

        <a
            href="landlorddashboard.php"
            class="back-btn"
        >
            ← Dashboard
        </a>

    </div>


    <!-- Account Security -->

    <div class="settings-card">

        <div class="settings-title">

            <h2>Account Security</h2>

            <p>
                Change your account password.
            </p>

        </div>


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


        <form method="POST">

            <div class="form-group">

                <label>
                    Current Password
                </label>

                <input
                    type="password"
                    name="current_password"
                    placeholder="Enter current password"
                    required
                >

            </div>


            <div class="form-group">

                <label>
                    New Password
                </label>

                <input
                    type="password"
                    name="new_password"
                    placeholder="Enter new password"
                    required
                >

                <small>
                    Password must contain at least 6 characters.
                </small>

            </div>


            <div class="form-group">

                <label>
                    Confirm New Password
                </label>

                <input
                    type="password"
                    name="confirm_password"
                    placeholder="Confirm new password"
                    required
                >

            </div>


            <button
                type="submit"
                name="change_password"
                class="change-btn"
            >
                Change Password
            </button>

        </form>

    </div>


    <!-- Account Information -->

    <div class="settings-card account-card">

        <div class="settings-title">

            <h2>Account Information</h2>

            <p>
                Basic information about your HRMS account.
            </p>

        </div>


        <div class="account-row">

            <span>Account Type</span>

            <strong>Landlord</strong>

        </div>


        <div class="account-row">

            <span>Security</span>

            <strong>Password Protected</strong>

        </div>


        <div class="account-row">

            <span>Profile</span>

            <a href="landlord_profile.php">
                Manage Profile
            </a>

        </div>

    </div>

</div>

</body>

</html>