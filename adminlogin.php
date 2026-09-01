<?php

session_start();

/* --DATABASE CONNECTION-- */

$conn = mysqli_connect(
    "localhost",
    "root",
    "",
    "hrms"
);

if (!$conn) {
    die(
        "Database connection failed: "
        . mysqli_connect_error()
    );
}


/* --ADMIN LOGIN-- */

if (isset($_POST['admin_login'])) {

    $email = trim($_POST['email']);
    $password = $_POST['password'];


    /* Find admin */

    $query = mysqli_prepare(
    $conn,

    "SELECT id,
            firstname,
            lastname,
            password
     FROM users
     WHERE email = ?
       AND role = 'admin'
     LIMIT 1"
);


    if (!$query) {

        die(
            "Login query failed: "
            . mysqli_error($conn)
        );

    }


    mysqli_stmt_bind_param(
        $query,
        "s",
        $email
    );


    mysqli_stmt_execute($query);


    $result = mysqli_stmt_get_result(
        $query
    );


    /* Check if admin exists */

    if (mysqli_num_rows($result) == 1) {

        $admin = mysqli_fetch_assoc($result);


        /* Check password */

        if (
            password_verify(
                $password,
                $admin['password']
            )
        ) {


            /* Regenerate session ID */

            session_regenerate_id(true);


            /* Create admin session */

            $_SESSION['admin_id'] =
                $admin['id'];

            $_SESSION['admin_name'] =
                $admin['firstname']
                . " "
                . $admin['lastname'];


            /* Go to admin dashboard */

            header(
                "Location: admindashboard.php"
            );

            exit();


        } else {

            $error =
                "Incorrect password.";

        }


    } else {

        $error =
            "Admin account not found.";

    }


    mysqli_stmt_close($query);
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
        Admin Login - HRMS
    </title>


    <!-- ADMIN LOGIN CSS -->

    <link rel="stylesheet"   href="Assets/css/adminlogin_style.css"
    >

</head>


<body>


<!-- admin login container -->

<div class="admin-login-container">


    <h2>
        Admin Login
    </h2>


    <p class="subtitle">
        House Rental Management System
    </p>


    <!-- ERROR MESSAGE -->

    <?php

    if (isset($error)) {

        echo "<p class='error'>"
             . htmlspecialchars($error)
             . "</p>";

    }

    ?>


    <!-- --ADMIN LOGIN FORM-- -->

    <form
        method="POST"
        action=""
    >


        <!-- ADMIN EMAIL -->

        <label for="email">
            Admin Email
        </label>


        <input
            type="email"
            name="email"
            id="email"
            placeholder="Enter admin email"
            required
        >


        <!-- PASSWORD -->

        <label for="password">
            Password
        </label>


        <input
            type="password"
            name="password"
            id="password"
            placeholder="Enter password"
            required
        >


        <!-- LOGIN BUTTON -->

        <button
            type="submit"
            name="admin_login"
        >
            Login
        </button>


    </form>


    <!-- BACK TO HOME -->

    <a
        href="index.php"
        class="back-home"
    >
        ← Back to Home
    </a>


</div>


</body>

</html>