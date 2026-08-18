<?php

session_start();

/* DATABASE CONNECTION */

$conn = mysqli_connect("localhost", "root", "", "hrms");

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}


/* ADMIN LOGIN */

if (isset($_POST['admin_login'])) {

    $email = trim($_POST['email']);
    $password = $_POST['password'];


    /* FIND ADMIN */

    $query = mysqli_prepare(
        $conn,
        "SELECT id, firstname, lastname, password
         FROM users
         WHERE email = ? AND role = 'admin'"
    );

    mysqli_stmt_bind_param(
        $query,
        "s",
        $email
    );

    mysqli_stmt_execute($query);

    $result = mysqli_stmt_get_result($query);


    /* CHECK ADMIN */

    if (mysqli_num_rows($result) == 1) {

        $admin = mysqli_fetch_assoc($result);


        /* CHECK PASSWORD */

        if (password_verify($password, $admin['password'])) {

            $_SESSION['admin_id'] = $admin['id'];

            $_SESSION['admin_name'] =
                $admin['firstname'] . " " . $admin['lastname'];


            /* GO TO ADMIN PANEL */

            header("Location: admin_page.php");

            exit();

        } else {

            $error = "Incorrect password.";

        }

    } else {

        $error = "Admin account not found.";

    }

    mysqli_stmt_close($query);
}

?>


<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Admin Login - HRMS</title>

    <link rel="stylesheet"
          href="Assets/css/admin_login_style.css">

</head>


<body>


<div class="admin-login-container">


    <h2>
        Admin Login
    </h2>


    <p class="subtitle">
        House Rental Management System
    </p>


    <?php

    if (isset($error)) {

        echo "<p class='error'>$error</p>";

    }

    ?>


    <form method="POST"
          action="">


        <label>
            Admin Email
        </label>

        <input
            type="email"
            name="email"
            placeholder="Enter admin email"
            required
        >


        <label>
            Password
        </label>

        <input
            type="password"
            name="password"
            placeholder="Enter password"
            required
        >


        <button
            type="submit"
            name="admin_login"
        >
            Login
        </button>


    </form>


    <a href="index.php"
       class="back-home">
        ← Back to Home
    </a>


</div>


</body>

</html>