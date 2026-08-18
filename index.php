<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>House Rental Management System</title>

    <!-- Index CSS -->
    <link rel="stylesheet" href="Assets/css/index_style.css">

</head>


<body>


<!-- navigation -->

<nav>

    <div class="logo">
        HRMS
    </div>

    <ul>

        <li>
            <a href="index.php">Home</a>
        </li>

        <li>
            <a href="properties.php">Properties</a>
        </li>

        <li>
            <a href="landlord.php">Landlords</a>
        </li>

        <li>
            <a href="tenant.php">Tenants</a>
        </li>

        <li>
            <a href="booking.php">Booking</a>
        </li>

        <li>
            <a href="login.php">Login</a>
        </li>

        <li>
            <a href="register.php">Register</a>
        </li>

    </ul>

</nav>



<!-- HERO section -->

<section class="hero">

    <div class="overlay">

        <h1>
            Find Your Dream Rental Home
        </h1>

        <p>
            Search houses, apartments and rooms across Nepal
        </p>


        <!-- SEARCH BOX -->

        <form
            class="search-box"
            action="properties.php"
            method="GET"
        >

            <input
                type="text"
                name="location"
                placeholder="Location"
            >


            <select name="type">

                <option value="">
                    Property Type
                </option>

                <option value="House">
                    House
                </option>

                <option value="Apartment">
                    Apartment
                </option>

                <option value="Room">
                    Room
                </option>

            </select>


            <button type="submit">
                Search
            </button>

        </form>

    </div>

</section>



<!-- WELCOME -->

<section class="welcome">

    <h2>
        Featured Services
    </h2>

</section>



<!-- cards -->

<div class="container">


    <!-- PROPERTIES -->

    <div class="card">

        <img
            src="Assets/images/house.jpg"
            alt="House"
        >

        <h3>
            Properties
        </h3>

        <p>
            View and manage available rental properties.
        </p>

        <a
            href="properties.php"
            class="btn"
        >
            See Details
        </a>

    </div>



    <!-- LANDLORDS -->

    <div class="card">

        <img
            src="Assets/images/landlord.jpg"
            alt="Landlord"
        >

        <h3>
            Landlords
        </h3>

        <p>
            Manage landlord information and properties.
        </p>

        <a
            href="landlord.php"
            class="btn"
        >
            See Details
        </a>

    </div>



    <!-- TENANTS -->

    <div class="card">

        <img
            src="Assets/images/tenant.jpg"
            alt="Tenant"
        >

        <h3>
            Tenants
        </h3>

        <p>
            Manage tenant details and rental records.
        </p>

        <a
            href="tenant.php"
            class="btn"
        >
            See Details
        </a>

    </div>



    <!-- BOOKING -->

    <div class="card">

        <img
            src="Assets/images/booking.jpg"
            alt="Booking"
        >

        <h3>
            Booking
        </h3>

        <p>
            Handle house booking and reservations.
        </p>

        <a
            href="booking.php"
            class="btn"
        >
            See Details
        </a>

    </div>



    <!-- ADMIN -->

    <div class="card">

        <img
            src="Assets/images/admin.jpg"
            alt="Admin"
        >

        <h3>
            Admin Panel
        </h3>

        <p>
            Control users, houses, and system reports.
        </p>

        <a
            href="admin_page.php"
            class="btn"
        >
            See Details
        </a>

    </div>

</div>



<!-- FOOTER -->

<footer>

    <p>
        &copy; 2026 House Rental Management System.
        All Rights Reserved.
    </p>

</footer>


</body>

</html>