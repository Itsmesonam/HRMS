<?php

session_start();

/* =========================================
   DATABASE CONNECTION
========================================= */

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


/* =========================================
   LOGIN AUTHENTICATION
========================================= */

if (isset($_POST['login'])) {

    $email = trim($_POST['username']);
    $password = $_POST['password'];
    $role = $_POST['role'];


    /* =====================================
       CHECK ROLE
    ===================================== */

    if (empty($role)) {

        echo "<script>
                alert('Please select your role');
              </script>";

    } else {


        /* =================================
           CHECK USER FROM DATABASE
        ================================= */

        $query = mysqli_prepare(
            $conn,

            "SELECT id,
                    firstname,
                    lastname,
                    password,
                    role
             FROM users
             WHERE email = ?
             AND role = ?
             LIMIT 1"
        );


        if (!$query) {

            die(
                "Login query failed: "
                . mysqli_error($conn)
            );

        }


        /* Bind email and role */

        mysqli_stmt_bind_param(
            $query,
            "ss",
            $email,
            $role
        );


        /* Execute query */

        mysqli_stmt_execute($query);


        /* Get result */

        $result = mysqli_stmt_get_result(
            $query
        );


        /* =================================
           CHECK USER
        ================================= */

        if (mysqli_num_rows($result) === 1) {

            $user = mysqli_fetch_assoc($result);


            /* =================================
               CHECK PASSWORD
            ================================= */

            if (
                password_verify(
                    $password,
                    $user['password']
                )
            ) {


                /* =================================
                   REGENERATE SESSION ID
                ================================= */

                session_regenerate_id(true);


                /* =================================
                   CREATE USER SESSION
                ================================= */

                $_SESSION['user_id'] =
                    $user['id'];

                $_SESSION['firstname'] =
                    $user['firstname'];

                $_SESSION['lastname'] =
                    $user['lastname'];

                $_SESSION['role'] =
                    $user['role'];


                /* =================================
                   ROLE BASED REDIRECT
                ================================= */

                if (
                    $user['role'] === 'landlord'
                ) {

                    header(
                        "Location: landlorddashboard.php"
                    );

                    exit();


                } elseif (
                    $user['role'] === 'tenant'
                ) {

                    header(
                        "Location: tenantdashboard.php"
                    );

                    exit();

                }


            } else {

                echo "<script>
                        alert('Incorrect password');
                      </script>";

            }


        } else {

            echo "<script>
                    alert('Invalid email or selected role');
                  </script>";

        }


        /* Close statement */

        mysqli_stmt_close($query);

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

    <title>
        Login - House Rental Management System
    </title>


    <!-- =====================================
         LOGIN CSS
    ====================================== -->

    <link
        rel="stylesheet"
        href="Assets/css/login_style.css"
    >
</head>

<body>


<!-- =========================================
     LOGIN CONTAINER
========================================= -->

<div class="login-container">


    <!-- =====================================
         TITLE
    ====================================== -->

    <h3>
        Login Here
    </h3>


    <!-- =====================================
         LOGIN FORM
    ====================================== -->

    <form
        action=""
        method="POST"
    >


        <!-- =================================
             EMAIL
        ================================== -->

        <label for="username">
            Email
        </label>


        <input
            type="email"
            name="username"
            id="username"
            placeholder="Enter your email"
            required
        >



        <!-- =================================
             PASSWORD
        ================================== -->

        <label for="password">
            Password
        </label>


        <input
            type="password"
            name="password"
            id="password"
            placeholder="Enter your password"
            required
        >



        <!-- =================================
             ROLE
        ================================== -->

        <label for="role">
            Login As
        </label>


        <select
            name="role"
            id="role"
            required
        >

            <option value="">
                Select your role
            </option>


            <option value="landlord">
                Landlord
            </option>


            <option value="tenant">
                Tenant
            </option>

        </select>



        <!-- =================================
             LOGIN BUTTON
        ================================== -->

        <button
            type="submit"
            name="login"
        >
            Log In
        </button>

        <!-- =================================
             REGISTER LINK
        ================================== -->

        <div class="register-link">

            Don't have an account?

            <a href="register.php">
                Sign Up
            </a>

        </div>


    </form>


</div>


</body>

</html>