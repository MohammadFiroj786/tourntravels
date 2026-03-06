<?php
if(!isset($_SESSION)){
    session_start();
}
?>

<!-- FONT AWESOME -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>

/* SIDEBAR */

.sidebar{
height:100vh;
position:fixed;
left:0;
top:0;
width:230px;
background:linear-gradient(135deg,#ff7e5f,#feb47b);
color:white;
padding-top:20px;
z-index:1000;
}

.sidebar a{
color:white;
text-decoration:none;
display:block;
padding:12px 20px;
border-radius:8px;
margin:5px 10px;
font-weight:500;
}

.sidebar a:hover{
background:rgba(255,255,255,0.2);
}

.sidebar h4{
font-weight:700;
}

/* MOBILE NAVBAR */

.mobile-navbar{
display:none;
background:linear-gradient(135deg,#ff7e5f,#feb47b);
z-index:1100;
}

/* DESKTOP LAYOUT */

@media(min-width:768px){

.main-content{
margin-left:230px;
padding:20px;
}

}

/* MOBILE LAYOUT */

@media(max-width:768px){

.sidebar{
display:none;
}

.mobile-navbar{
display:block;
}

.main-content{
margin-left:0;
margin-top:80px;
padding:15px;
}

}

</style>

<!-- MOBILE NAVBAR -->

<nav class="navbar navbar-dark mobile-navbar fixed-top px-3">

<div class="container-fluid">

<a class="navbar-brand" href="user-dashboard.php">
<i class="fa fa-plane"></i> Tour N Travels
</a>

<button class="navbar-toggler ms-auto" type="button"
data-bs-toggle="collapse"
data-bs-target="#mobileMenu">

<span class="navbar-toggler-icon"></span>

</button>

<div class="collapse navbar-collapse" id="mobileMenu">

<ul class="navbar-nav mt-3">

<li class="nav-item">
<a class="nav-link" href="user-dashboard.php">
<i class="fa fa-home"></i> Dashboard
</a>
</li>

<li class="nav-item">
<a class="nav-link" href="my-bookings.php">
<i class="fa fa-calendar"></i> My Bookings
</a>
</li>

<li class="nav-item">
<a class="nav-link" href="packages.php">
<i class="fa fa-box"></i> Packages
</a>
</li>

<li class="nav-item">
<a class="nav-link" href="profile.php">
<i class="fa fa-user"></i> Profile
</a>
</li>

<li class="nav-item">
<a class="nav-link" href="../includes/logout.php">
<i class="fa fa-sign-out-alt"></i> Logout
</a>
</li>

</ul>

</div>

</div>

</nav>


<!-- DESKTOP SIDEBAR -->

<div class="sidebar">

<h4 class="text-center mb-4">
<i class="fa-solid fa-plane"></i> Tour N Travels
</h4>

<a href="user-dashboard.php"><i class="fa-solid fa-house"></i> Dashboard</a>
<a href="my-bookings.php"><i class="fa-solid fa-calendar"></i> My Bookings</a>
<a href="packages.php"><i class="fa-solid fa-box"></i> Packages</a>
<a href="profile.php"><i class="fa-solid fa-user"></i> Profile</a>
<a href="../includes/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>

</div>