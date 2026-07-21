*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    font-family:Arial, sans-serif;
    background:#f4f4f4;
}

/* Navigation */

nav{
    background:#222;
    color:white;
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:15px 30px;
}

.logo{
    font-size:20px;
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
    text-decoration:none;
    color:white;
}

nav ul li a:hover{
    color:orange;
}

/* Welcome */

.welcome{
    text-align:center;
    margin-top:30px;
}

/* Cards */

.container{
    width:90%;
    margin:30px auto;
    display:flex;
    flex-wrap:wrap;
    justify-content:center;
    gap:25px;
}

.card{
    background:white;
    width:250px;
    padding:15px;
    text-align:center;
    border-radius:10px;
    box-shadow:0 2px 10px rgba(0,0,0,0.2);
}

.card img{
    width:100%;
    height:150px;
    object-fit:cover;
    border-radius:5px;
}

.card h3{
    margin-top:10px;
}

.card p{
    margin:10px 0;
    font-size:14px;
}

.btn{
    display:inline-block;
    text-decoration:none;
    background:#007bff;
    color:white;
    padding:8px 15px;
    border-radius:5px;
}

.btn:hover{
    background:#0056b3;
}