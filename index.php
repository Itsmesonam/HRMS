<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>House Rental Management System</title>

    <link rel="stylesheet"
          href="Assets/css/index_style.css?v=4">

</head>

<body>


<!-- =========================================
     NAVIGATION BAR
========================================= -->

<header class="navbar">

    <a href="index.php" class="logo">
        <img src="image/logo.jpg" alt="Homy Logo">
    </a>


    <nav class="nav-links">

        <a href="#home">Home</a>

        <a href="#about">About Us</a>

        <a href="#services">Services</a>

        <a href="#blog">Blog</a>

        <a href="#contact">Contact</a>

    </nav>


    <div class="nav-buttons">

        <a href="login.php"
           class="login-btn">
            Login
        </a>

        <a href="register.php"
           class="signup-btn">
            Sign Up
        </a>

    </div>

</header>



<!-- =========================================
     HERO SECTION
========================================= -->

<section class="hero"
         id="home">

    <div class="hero-overlay">


        <div class="hero-content">

            <span class="hero-small-text">
                RENT • LIVE • BELONG
            </span>


            <h1>
                Find Your Dream
                <br>
                Rental Home
            </h1>


            <p>
                Search houses, apartments and rooms
                across Nepal and find a place
                that feels like home.
            </p>


            <!-- SEARCH BOX -->

            <form class="search-box"
                  action="properties.php"
                  method="GET">


                <!-- Location -->

                <div class="search-field">

                    <label for="location">
                        Location
                    </label>

                    <input
                        type="text"
                        id="location"
                        name="location"
                        placeholder="Enter location">

                </div>



                <!-- Property Type -->

                <div class="search-field">

                    <label for="type">
                        Property Type
                    </label>

                    <select
                        id="type"
                        name="type">

                        <option value="">
                            Select Type
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

                </div>



                <!-- Rent -->

                <div class="search-field">

                    <label for="rent">
                        Monthly Rent
                    </label>

                    <select
                        id="rent"
                        name="rent">

                        <option value="">
                            Any Price
                        </option>

                        <option value="10000">
                            Under Rs. 10,000
                        </option>

                        <option value="20000">
                            Under Rs. 20,000
                        </option>

                        <option value="30000">
                            Under Rs. 30,000
                        </option>

                        <option value="50000">
                            Under Rs. 50,000
                        </option>

                        <option value="100000">
                            Under Rs. 100,000
                        </option>

                    </select>

                </div>



                <!-- Search Button -->

                <button
                    type="submit"
                    class="search-btn">

                    Search

                </button>

            </form>

        </div>

    </div>

</section>



<!-- =========================================
     BROWSE AVAILABLE PROPERTIES
========================================= -->

<section class="browse-properties" id="properties">

    <div class="section-heading">
        <span>EXPLORE</span>
        <h2>Browse Available Properties</h2>
        <p>Find a property that matches your lifestyle, location and budget.</p>
    </div>

    <div class="property-categories">

        <!-- House -->
        <div class="property-category">
            <img src="image/House.jpg" alt="Rental House">

            <div class="category-content">
                <span class="property-label">HOUSE</span>

                <h3>Rental Houses</h3>

                <p>
                    Find comfortable houses suitable for families
                    and long-term living.
                </p>

                <a href="properties.php?type=House" class="category-btn">
                    Explore Houses →
                </a>
            </div>
        </div>


        <!-- Apartment -->
        <div class="property-category">
            <img src="image/appartment.webp" alt="Apartment">

            <div class="category-content">
                <span class="property-label">APARTMENT</span>

                <h3>Modern Apartments</h3>

                <p>
                    Explore modern apartments in convenient
                    locations across Nepal.
                </p>

                <a href="properties.php?type=Apartment" class="category-btn">
                    Explore Apartments →
                </a>
            </div>
        </div>


        <!-- Room -->
        <div class="property-category">
            <img src="image/room.webp" alt="Rental Room">

            <div class="category-content">
                <span class="property-label">ROOM</span>

                <h3>Rental Rooms</h3>

                <p>
                    Find affordable rooms for students,
                    professionals and individuals.
                </p>

                <a href="properties.php?type=Room" class="category-btn">
                    Explore Rooms →
                </a>
            </div>
        </div>

    </div>

</section>



<!-- =========================================
     ABOUT US
========================================= -->

<section class="about-section"
         id="about">

    <div class="about-container">

    </div>

           
        <div class="about-content">

            <span class="section-label">
                ABOUT US
            </span>

            <h2>
                Making Rental Living
                Simple and Convenient
            </h2>

            <p>
                House Rental Management System
                is a platform designed to make
                the process of finding and managing
                rental properties easier.
            </p>

            <p>
                Tenants can search for available
                properties, submit rental requests
                and manage their bookings.
                Landlords can add properties,
                manage rental requests and
                monitor payments.
            </p>


            <a
                href="register.php"
                class="primary-btn">

                Get Started

            </a>

        </div>

    </div>

</section>



<!-- =========================================
     SERVICES
========================================= -->

<section class="services-section"
         id="services">

    <div class="section-heading">

        <span>
            OUR SERVICES
        </span>

        <h2>
            Everything You Need
            for Easy Renting
        </h2>

        <p>
            Simple tools for tenants and
            landlords to manage the rental
            process.
        </p>

    </div>


    <div class="service-cards">


        <div class="service-card">

            <div class="service-icon">
                🔍
            </div>

            <h3>
                Property Search
            </h3>

            <p>
                Search houses, apartments
                and rooms based on location
                and property type.
            </p>

        </div>



        <div class="service-card">

            <div class="service-icon">
                🏠
            </div>

            <h3>
                Rental Booking
            </h3>

            <p>
                Submit rental requests and
                manage your booking information
                easily.
            </p>

        </div>



        <div class="service-card">

            <div class="service-icon">
                💳
            </div>

            <h3>
                Payment Management
            </h3>

            <p>
                Manage rental payments and
                keep track of your payment
                history.
            </p>

        </div>



        <div class="service-card">

            <div class="service-icon">
                📋
            </div>

            <h3>
                Property Management
            </h3>

            <p>
                Landlords can add properties,
                manage listings and handle
                rental requests.
            </p>

        </div>

    </div>

</section>



<!-- =========================================
     BLOG
========================================= -->

<section class="blog-section"
         id="blog">

    <div class="section-heading">

        <span>
            OUR BLOG
        </span>

        <h2>
            Helpful Rental Tips
        </h2>

        <p>
            Useful information to help you
            make better rental decisions.
        </p>

    </div>


    <div class="blog-cards">


        <article class="blog-card">

            <img
                src="image/finding-home.png"
                alt="Finding a rental home">

            <div class="blog-content">

                <span>
                    RENTING GUIDE
                </span>

                <h3>
                    How to Find the Right
                    Rental Home
                </h3>

                <p>
                    Learn what to consider when
                    choosing a rental property.
                </p>

                <a href="#contact">
                    Read More →
                </a>

            </div>

        </article>



        <article class="blog-card">

            <img
                src="image/rental-book.png"
                alt="Rental booking">

            <div class="blog-content">

                <span>
                    RENTAL TIPS
                </span>

                <h3>
                    Things to Check Before
                    Renting a Property
                </h3>

                <p>
                    Important things tenants should
                    check before moving into a property.
                </p>

                <a href="#contact">
                    Read More →
                </a>

            </div>

        </article>



        <article class="blog-card">

            <img
                src="image/landlord-mgnt.png"
                alt="Landlord management">

            <div class="blog-content">

                <span>
                    LANDLORD GUIDE
                </span>

                <h3>
                    Tips for Managing
                    Rental Properties
                </h3>

                <p>
                    Helpful ideas for landlords to
                    manage their rental properties.
                </p>

                <a href="#contact">
                    Read More →
                </a>

            </div>

        </article>

    </div>

</section>



<!-- =========================================
     CONTACT
========================================= -->

<section class="contact-section"
         id="contact">

    <div class="contact-container">


        <div class="contact-content">

            <span class="section-label">
                CONTACT US
            </span>

            <h2>
                Have Questions?
                Let's Talk.
            </h2>

            <p>
                If you need help finding a rental
                property or managing your property,
                feel free to contact us.
            </p>

        </div>



        <div class="contact-info">

            <div class="contact-item">

                <div class="contact-icon">
                    ✉
                </div>

                <div>

                    <h4>
                        Email
                    </h4>

                    <p>
                        hrms@example.com
                    </p>

                </div>

            </div>



            <div class="contact-item">

                <div class="contact-icon">
                    ☎
                </div>

                <div>

                    <h4>
                        Phone
                    </h4>

                    <p>
                        +977 98XXXXXXXX
                    </p>

                </div>

            </div>



            <div class="contact-item">

                <div class="contact-icon">
                    📍
                </div>

                <div>

                    <h4>
                        Location
                    </h4>

                    <p>
                        Kathmandu, Nepal
                    </p>

                </div>

            </div>

        </div>

    </div>

</section>


        <p>
            © 2026 House Rental Management System.
            All Rights Reserved.
        </p>

    </div>

</footer>


</body>

</html>