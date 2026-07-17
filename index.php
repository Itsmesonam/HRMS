
Those should **not** be in an HTML/PHP file.

Use this corrected version:

```html
<!DOCTYPE html>
<html>
<head>
    <title>House Rental Management System</title>
    <style>
        body{
            font-family: Arial, sans-serif;
            margin:0;
            padding:0;
            background:#f4f4f4;
        }

        header{
            background:#2c3e50;
            color:white;
            text-align:center;
            padding:20px;
        }

        nav{
            background:#34495e;
            padding:10px;
            text-align:center;
        }

        nav a{
            color:white;
            text-decoration:none;
            margin:15px;
        }

        .container{
            width:80%;
            margin:auto;
            padding:20px;
        }

        .section{
            background:white;
            margin:20px 0;
            padding:20px;
            border-radius:8px;
        }

        footer{
            background:#2c3e50;
            color:white;
            text-align:center;
            padding:15px;
        }
    </style>
</head>
<body>

<header>
    <h1>House Rental Management System</h1>
    <p>Find, Rent and Manage Properties Easily</p>
</header>

<nav>
    <a href="index.php">Home</a>
    <a href="properties.php">Properties</a>
    <a href="tenant.php">Tenant</a>
    <a href="landlord.php">Landlord</a>
    <a href="login.php">Login</a>
    <a href="register.php">Register</a>
</nav>

<div class="container">

    <div class="section">
        <h2>About the System</h2>
        <p>
            This House Rental Management System helps tenants find rental
            properties and allows landlords to manage their houses online.
        </p>
    </div>

    <div class="section">
        <h2>Tenant Services</h2>
        <ul>
            <li>Register as Tenant</li>
            <li>Search Available Houses</li>
            <li>View Property Details</li>
            <li>Send Rental Requests</li>
        </ul>
    </div>

    <div class="section">
        <h2>Landlord Services</h2>
        <ul>
            <li>Register as Landlord</li>
            <li>Add New Property</li>
            <li>Edit Property Details</li>
            <li>Manage Rental Requests</li>
        </ul>
    </div>

    <div class="section">
        <h2>Featured Properties</h2>

        <h3>Property 1</h3>
        <p>Location: Bhaktapur</p>
        <p>Rent: NPR 15,000/month</p>

        <h3>Property 2</h3>
        <p>Location: Kathmandu</p>
        <p>Rent: NPR 20,000/month</p>
    </div>

</div>

<footer>
    <p>&copy; 2026 House Rental Management System</p>
</footer>

</body>
</html>