<?php
session_start();

// DB connection
$conn = mysqli_connect("localhost", "root", "", "hrms");

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

if (isset($_POST['login'])) {

    $email    = trim($_POST['username']);
    $password = $_POST['password'];
    $role     = $_POST['role'];

    if (empty($role)) {
        echo "<script>alert('Please select your role');</script>";
    } else {

        $query = mysqli_prepare(
            $conn,
            "SELECT id, firstname, lastname, password, role
             FROM users
             WHERE email = ? AND role = ?"
        );

        mysqli_stmt_bind_param($query, "ss", $email, $role);
        mysqli_stmt_execute($query);
        $result = mysqli_stmt_get_result($query);

        if (mysqli_num_rows($result) == 1) {

            $user = mysqli_fetch_assoc($result);

            if (password_verify($password, $user['password'])) {

                $_SESSION['user_id']   = $user['id'];
                $_SESSION['firstname'] = $user['firstname'];
                $_SESSION['lastname']  = $user['lastname'];
                $_SESSION['role']      = $user['role'];

                if ($user['role'] == 'landlord') {
                    header("Location: landlord.php");
                    exit();
                } elseif ($user['role'] == 'tenant') {
                    header("Location: tenant.php");
                    exit();
                }

            } else {
                echo "<script>alert('Incorrect password');</script>";
            }

        } else {
            echo "<script>alert('Invalid email or role');</script>";
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

    <!-- LOGIN CSS (external file) -->
    <link rel="stylesheet" href="Assets/css/login_style.css">

    <!-- FONT AWESOME (for social icons) -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>

<div class="login-container">
    <h3>Login Here</h3>

    <form action="" method="POST">

        <!-- USERNAME -->
        <label for="username">Username</label>
        <input
            type="text"
            name="username"
            id="username"
            placeholder="Email"
            required
        >

        <!-- PASSWORD -->
        <label for="password">Password</label>
        <input
            type="password"
            name="password"
            id="password"
            placeholder="Password"
            required
        >

        <!-- ROLE -->
        <label for="role">Login As</label>
        <select
            name="role"
            id="role"
            required
        >
            <option value="">Select your role</opt