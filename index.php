<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>House Rental Management System</title>

    <link rel="stylesheet" href="css/index_style.css">
</head>
<body>

    <!-- Navigation Bar -->
    <nav>
        <div class="logo">HRMS</div>

        <ul>
            <li><a href="#">Home</a></li>
            <li><a href="#">Properties</a></li>
            <li><a href="#">About</a></li>
            <li><a href="#">Contact</a></li>
            <li><a href="login.php">Login</a></li>
        </ul>
    </nav>

   <section class="hero">
    <div class="overlay">
        <h1>Find Your Dream Rental Home</h1>
        <p>Search houses, apartments and rooms across Nepal</p>

        <form class="search-box">
            <input type="text" placeholder="Location">
            <select>
                <option>Property Type</option>
                <option>House</option>
                <option>Apartment</option>
                <option>Room</option>
            </select>
            <button type="submit">Search</button>
        </form>
    </div>
</section>

    <!-- Welcome Section -->
    <section class="welcome">
        <h2>Featured Services</h2>
    </section>

    <!-- Cards Section -->
    <div class="container">

        <div class="card">
            <img src="images/house.jpg" alt="House">
            <h3>Properties</h3>
            <p>View and manage available rental properties.</p>
            <a href="#" class="btn">See Details</a>
        </div>

        <div class="card">
            <img src="images/landlord.jpg" alt="Landlord">
            <h3>Landlords</h3>
            <p>Manage landlord information and properties.</p>
            <a href="#" class="btn">See Details</a>
        </div>

        <div class="card">
            <img src="images/tenant.jpg" alt="Tenant">
            <h3>Tenants</h3>
            <p>Manage tenant details and rental records.</p>
            <a href="#" class="btn">See Details</a>
        </div>

        <div class="card">
            <img src="images/booking.jpg" alt="Booking">
            <h3>Booking</h3>
            <p>Handle house booking and reservations.</p>
            <a href="#" class="btn">See Details</a>
        </div>

        <div class="card">
            <img src="images/admin.jpg" alt="Admin">
            <h3>Admin Panel</h3>
            <p>Control users, houses and system reports.</p>
            <a href="#" class="btn">See Details</a>
        </div>

    </div>

</body>
</html>