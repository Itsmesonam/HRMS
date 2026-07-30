<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>House Rental Management System</title>

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:Arial, sans-serif;
}

body{
    background:#f4f6f9;
}

/* Navbar */
nav{
    background:#1E3A8A;
    color:white;
    padding:15px 30px;
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.logo{
    font-size:24px;
    font-weight:bold;
}

nav ul{
    list-style:none;
    display:flex;
}

nav ul li{
    margin-left:20px;
}

nav ul li a{
    color:white;
    text-decoration:none;
    font-weight:bold;
}

nav ul li a:hover{
    color:#FFD700;
}

/* Welcome Section */
.welcome{
    text-align:center;
    padding:50px;
    background:white;
}

.welcome h2{
    color:#1E3A8A;
    font-size:35px;
}

/* Cards */
.container{
    width:90%;
    margin:40px auto;
    display:flex;
    flex-wrap:wrap;
    justify-content:center;
    gap:20px;
}

.card{
    width:300px;
    background:white;
    border-radius:10px;
    overflow:hidden;
    box-shadow:0 4px 10px rgba(0,0,0,0.1);
    text-align:center;
    transition:0.3s;
}

.card:hover{
    transform:translateY(-5px);
}

.card img{
    width:100%;
    height:200px;
    object-fit:cover;
}

.card h3{
    margin:15px 0;
    color:#1E3A8A;
}

.card p{
    padding:0 15px;
    color:#555;
}

.btn{
    display:inline-block;
    margin:15px;
    padding:10px 20px;
    background:#1E3A8A;
    color:white;
    text-decoration:none;
    border-radius:5px;
}

.btn:hover{
    background:#14296b;
}

/* Responsive */
@media(max-width:768px){
    nav{
        flex-direction:column;
    }

    nav ul{
        flex-wrap:wrap;
        justify-content:center;
        margin-top:10px;
    }

    nav ul li{
        margin:5px 10px;
    }
}
</style>

</head>
<body>

<nav>
    <div class="logo">House Rental Management System</div>

    <ul>
        <li><a href="#">Home</a></li>
        <li><a href="#">Houses</a></li>
        <li><a href="#">Landlords</a></li>
        <li><a href="#">Tenants</a></li>
        <li><a href="#">Booking</a></li>
        <li><a href="#">Admin</a></li>
    </ul>
</nav>

<div class="welcome">
    <h2>Welcome User</h2>
    <p>Find and manage rental properties easily.</p>
</div>

<div class="container">

    <div class="card">
        <img src="images/house.jpg" alt="House">
        <h3>Houses</h3>
        <p>View and manage available rental houses.</p>
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
        <h3>Admin</h3>
        <p>Control users, houses and system reports.</p>
        <a href="#" class="btn">See Details</a>
    </div>

</div>

</body>
</html>