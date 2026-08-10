```php
<?php

// ===============================
// DATABASE CONNECTION
// ===============================

$conn = mysqli_connect("localhost", "root", "", "hrms");

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}


// ===============================
// REGISTRATION
// ===============================

if (isset($_POST['register'])) {

    // Get form values
    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);

    $password = $_POST['password'];
    $cpassword = $_POST['cpassword'];

    $gender = $_POST['gender'];
    $role = $_POST['role'];

    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);


    // ===============================
    // CHECK PASSWORD
    // ===============================

    if ($password !== $cpassword) {

        echo "<script>
                alert('Password and Confirm Password do not match');
              </script>";

    } else {


        // ===============================
        // CHECK EMAIL
        // ===============================

        $check = mysqli_prepare(
            $conn,
            "SELECT id FROM users WHERE email = ?"
        );

        mysqli_stmt_bind_param(
            $check,
            "s",
            $email
        );

        mysqli_stmt_execute($check);

        mysqli_stmt_store_result($check);


        if (mysqli_stmt_num_rows($check) > 0) {

            echo "<script>
                    alert('Email is already registered');
                  </script>";

        } else {


            // ===============================
            // HASH PASSWORD
            // ===============================

            $hashed_password = password_hash(
                $password,
                PASSWORD_DEFAULT
            );


            // ===============================
            // INSERT USER
            // ===============================

            $query = mysqli_prepare(
                $conn,
                "INSERT INTO users
                (firstname, lastname, password, gender, role, email, phone, address)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );

            mysqli_stmt_bind_param(
                $query,
                "ssssssss",
                $firstname,
                $lastname,
                $hashed_password,
                $gender,
                $role,
                $email,
                $phone,
                $address
            );


            // ===============================
            // CHECK INSERT
            // ===============================

            if (mysqli_stmt_execute($query)) {

                echo "<script>
                        alert('Registration Successful');
                        window.location.href = 'login.php';
                      </script>";

            } else {

                echo "<script>
                        alert('Registration Failed');
                      </script>";
            }


            mysqli_stmt_close($query);
        }

        mysqli_stmt_close($check);
    }
}

?>


<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Registration - House Rental Management System</title>


    <!-- Registration CSS -->

    <link rel="stylesheet"
          href="css/register_style.css">

</head>


<body>


<div class="container">


    <!-- ===============================
         TITLE
    ================================ -->

    <div class="title">
        Registration Form
    </div>


    <!-- ===============================
         REGISTRATION FORM
    ================================ -->

    <form action="" method="POST">


        <!-- FIRST NAME -->

        <div class="input_field">

            <label>First Name</label>

            <input
                type="text"
                name="firstname"
                class="input"
                required
            >

        </div>


        <!-- LAST NAME -->

        <div class="input_field">

            <label>Last Name</label>

            <input
                type="text"
                name="lastname"
                class="input"
                required
            >

        </div>


        <!-- PASSWORD -->

        <div class="input_field">

            <label>Password</label>

            <input
                type="password"
                name="password"
                class="input"
                required
            >

        </div>


        <!-- CONFIRM PASSWORD -->

        <div class="input_field">

            <label>Confirm Password</label>

            <input
                type="password"
                name="cpassword"
                class="input"
                required
            >

        </div>


        <!-- GENDER -->

        <div class="input_field">

            <label>Gender</label>

            <select
                name="gender"
                required
            >

                <option value="">
                    Select
                </option>

                <option value="Male">
                    Male
                </option>

                <option value="Female">
                    Female
                </option>

            </select>

        </div>


        <!-- ROLE -->

        <div class="input_field">

            <label>Role</label>

            <select
                name="role"
                required
            >

                <option value="">
                    Select
                </option>

                <option value="landlord">
                    Landlord
                </option>

                <option value="tenant">
                    Tenant
                </option>

            </select>

        </div>


        <!-- EMAIL -->

        <div class="input_field">

            <label>Email</label>

            <input
                type="email"
                name="email"
                class="input"
                required
            >

        </div>


        <!-- PHONE -->

        <div class="input_field">

            <label>Phone</label>

            <input
                type="text"
                name="phone"
                class="input"
                required
            >

        </div>


        <!-- ADDRESS -->

        <div class="input_field">

            <label>Address</label>

            <textarea
                name="address"
                class="input"
                required
            ></textarea>

        </div>


        <!-- TERMS -->

        <div class="input_field terms">

            <label class="check-label">

                <input
                    type="checkbox"
                    name="terms"
                    class="checkbox"
                    required
                >

                <span class="checkmarks"></span>

            </label>

            <p>
                I agree to the terms and conditions
            </p>

        </div>


        <!-- REGISTER BUTTON -->

        <div class="input_field">

            <input
                type="submit"
                name="register"
                value="Register"
                class="btn"
            >

        </div>


    </form>

</div>


</body>

</html>
```
