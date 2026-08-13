<?php

session_start();

// ===============================
// DATABASE CONNECTION
// ===============================

$conn = mysqli_connect("localhost", "root", "", "hrms");

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}


// ===============================
// LOGIN AUTHENTICATION
// ===============================

if (isset($_POST['login'])) {

    $email = trim($_POST['username']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Check role
    if (empty($role)) {

        echo "<script>
                alert('Please select your role');
              </script>";

    } else {

        // ===============================
        // FIND USER
        // ===============================

        $query = mysqli_prepare(
            $conn,
            "SELECT id, firstname, lastname, password, role
             FROM users
             WHERE email = ? AND role = ?"
        );

        mysqli_stmt_bind_param(
            $query,
            "ss",
            $email,
            $role
        );

        mysqli_stmt_execute($query);

        $result = mysqli_stmt_get_result($query);


        // ===============================
        // CHECK USER
        // ===============================

        if (mysqli_num_rows($result) == 1) {

            $user = mysqli_fetch_assoc($result);


            // ===============================
            // CHECK PASSWORD
            // ===============================

            if (password_verify($password, $user['password'])) {

                // Create session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['firstname'] = $user['firstname'];
                $_SESSION['lastname'] = $user['lastname'];
                $_SESSION['role'] = $user['role'];


                // ===============================
                // ROLE BASED REDIRECT
                // ===============================

                if ($user['role'] == 'landlord') {

                    header("Location: landlord.php");
                    exit();

                } elseif ($user['role'] == 'tenant') {

                    header("Location: tenant.php");
                    exit();
                }

            } else {

                echo "<script>
                        alert('Incorrect password');
                      </script>";
            }

        } else {

            echo "<script>
                    alert('Invalid email or role');
                  </script>";
        }

        mysqli_stmt_close($query);
    }
}

?>


<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Login - House Rental Management System</title>


    <!-- LOGIN CSS -->

    <link rel="stylesheet"
          href="Assets/css/login_style.css">


    <!-- FONT AWESOME -->

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

</head>


<body>


<!-- ===============================
     LOGIN CONTAINER
================================ -->

<div class="login-container">


    <!-- ===============================
         TITLE
    ================================ -->

    <h3>
        Login Here
    </h3>


    <!-- ===============================
         LOGIN FORM
    ================================ -->

    <form action="" method="POST">


        <!-- USERNAME -->

        <label for="username">
            Username
        </label>

        <input
            type="text"
            name="username"
            placeholder="Email"
            id="username"
            required
        >


        <!-- PASSWORD -->

        <label for="password">
            Password
        </label>

        <input
            type="password"
            name="password"
            placeholder="Password"
            id="password"
            required
        >


        <!-- ROLE -->

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


        <!-- LOGIN BUTTON -->

        <button
            type="submit"
            name="login"
        >
            Log In
        </button>


        <!-- ===============================
             SOCIAL LOGIN
        ================================ -->

        <div class="social">


            <!-- FACEBOOK -->

            <div class="fb">

                <i class="fab fa-facebook-f"></i>

                <span>
                    Facebook
                </span>

            </div>


            <!-- GOOGLE -->

            <div class="google">

                <i class="fab fa-google"></i>

                <span>
                    Google
                </span>

            </div>


        </div>


        <!-- ===============================
             REGISTER LINK
        ================================ -->

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