<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Registration - House Rental Management System</title>

    <!-- Registration CSS -->
    <link rel="stylesheet"
          href="Assets/css/register_style.css">

</head>

<body>

<div class="container">

    <div class="title">
        Registration Form
    </div>

    <form action="" method="POST">

        <div class="input_field">

            <label>First Name</label>

            <input
                type="text"
                name="firstname"
                class="input"
                required
            >

        </div>


        <div class="input_field">

            <label>Last Name</label>

            <input
                type="text"
                name="lastname"
                class="input"
                required
            >

        </div>


        <div class="input_field">

            <label>Password</label>

            <input
                type="password"
                name="password"
                class="input"
                required
            >

        </div>


        <div class="input_field">

            <label>Confirm Password</label>

            <input
                type="password"
                name="cpassword"
                class="input"
                required
            >

        </div>


        <div class="input_field">

            <label>Gender</label>

            <select name="gender" required>

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


        <div class="input_field">

            <label>Role</label>

            <select name="role" required>

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


        <div class="input_field">

            <label>Email</label>

            <input
                type="email"
                name="email"
                class="input"
                required
            >

        </div>


        <div class="input_field">

            <label>Phone</label>

            <input
                type="text"
                name="phone"
                class="input"
                required
            >

        </div>


        <div class="input_field">

            <label>Address</label>

            <textarea
                name="address"
                class="input"
                required
            ></textarea>

        </div>


        <div class="input_field terms">

            <label class="check-label">

                <input
                    type="checkbox"
                    name="terms"
                    class="checkbox"
                    required
                >

            </label>

            <p>
                I agree to the terms and conditions
            </p>

        </div>


        <div class="input_field">

            <input
                type="submit"
                name="register"
                value="Register"
                class="btn"
            >

        </div>


        <div class="login-link">

            Already have an account?

            <a href="login.php">
                Login
            </a>

        </div>

    </form>

</div>

</body>

</html>