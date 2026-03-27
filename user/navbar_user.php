<?php
if(!isset($_SESSION)){
    session_start();
}

// ACTIVE PAGE DETECTION
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- FONT AWESOME -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<!-- GOOGLE FONT -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

<style>

/* GLOBAL */
body{
    font-family:'Poppins',sans-serif;
}

/* SIDEBAR */
.sidebar{
    height:100vh;
    position:fixed;
    left:0;
    top:0;
    width:250px;
    background:linear-gradient(180deg,#1e1e2f,#2c2c54);
    color:white;
    padding-top:20px;
    z-index:1000;
    box-shadow:4px 0 25px rgba(0,0,0,0.3);
}

/* LOGO */
.sidebar h4{
    font-weight:700;
    text-align:center;
    margin-bottom:25px;
}

/* LINKS */
.sidebar a{
    display:flex;
    align-items:center;
    gap:12px;
    color:#cfd8dc;
    text-decoration:none;
    padding:13px 20px;
    margin:5px 10px;
    border-radius:10px;
    font-size:15px;
    transition:0.3s;
}

/* HOVER EFFECT */
.sidebar a:hover{
    background:linear-gradient(135deg,#667eea,#764ba2);
    color:white;
    transform:translateX(5px);
}

/* ACTIVE LINK */
.sidebar a.active{
    background:linear-gradient(135deg,#43cea2,#185a9d);
    color:white;
    font-weight:600;
    box-shadow:0 5px 15px rgba(0,0,0,0.2);
}

/* ICON */
.sidebar i{
    width:20px;
    text-align:center;
}

/* MAIN CONTENT */
@media(min-width:768px){
.main-content{
    margin-left:250px;
    padding:25px;
}
}

/* MOBILE NAVBAR */
.mobile-navbar{
    display:none;
    background:linear-gradient(135deg,#1e1e2f,#2c2c54);
    backdrop-filter: blur(10px);
}

/* MOBILE MENU */
.mobile-menu{
    background:#1e1e2f;
    border-radius:15px;
    padding:10px;
}

/* MOBILE LINKS */
.mobile-menu .nav-link{
    padding:12px;
    border-radius:10px;
    transition:0.3s;
}

.mobile-menu .nav-link:hover{
    background:#667eea;
    color:white !important;
}

/* MOBILE VIEW */
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

<!-- ================= MOBILE NAVBAR ================= -->

<nav class="navbar navbar-dark mobile-navbar fixed-top shadow">

<div class="container-fluid">

<a class="navbar-brand fw-bold" href="../index.php">
<i class="fa-solid fa-plane-departure"></i> Hidden Hills Collective
</a>

<button class="navbar-toggler" type="button"
data-bs-toggle="collapse"
data-bs-target="#mobileMenu">
<span class="navbar-toggler-icon"></span>
</button>

<div class="collapse navbar-collapse mobile-menu mt-3" id="mobileMenu">

<ul class="navbar-nav">

<li class="nav-item">
<a class="nav-link <?= ($current_page=='user-dashboard.php')?'active':'' ?>" href="user-dashboard.php">
<i class="fa fa-home"></i> Dashboard
</a>
</li>

<li class="nav-item">
<a class="nav-link <?= ($current_page=='my-bookings.php')?'active':'' ?>" href="my-bookings.php">
<i class="fa fa-calendar"></i> My Bookings
</a>
</li>

<li class="nav-item">
<a class="nav-link <?= ($current_page=='packages.php')?'active':'' ?>" href="packages.php">
<i class="fa fa-box"></i> Packages
</a>
</li>

<li class="nav-item">
<a class="nav-link <?= ($current_page=='profile.php')?'active':'' ?>" href="profile.php">
<i class="fa fa-user"></i> Profile
</a>
</li>

<li class="nav-item">
<a class="nav-link text-danger" href="../includes/logout.php">
<i class="fa fa-sign-out-alt"></i> Logout
</a>
</li>

</ul>

</div>

</div>

</nav>

<!-- ================= DESKTOP SIDEBAR ================= -->

<div class="sidebar">

<h4>
<i class="fa-solid fa-plane-departure me-3"></i>Hidden Hills Collective
</h4>

<a href="user-dashboard.php" class="<?= ($current_page=='user-dashboard.php')?'active':'' ?>">
<i class="fa-solid fa-house"></i> Dashboard
</a>

<a href="my-bookings.php" class="<?= ($current_page=='my-bookings.php')?'active':'' ?>">
<i class="fa-solid fa-calendar"></i> My Bookings
</a>

<a href="packages.php" class="<?= ($current_page=='packages.php')?'active':'' ?>">
<i class="fa-solid fa-box"></i> Packages
</a>

<a href="profile.php" class="<?= ($current_page=='profile.php')?'active':'' ?>">
<i class="fa-solid fa-user"></i> Profile
</a>

<a href="../includes/logout.php" class="text-danger">
<i class="fa-solid fa-right-from-bracket"></i> Logout
</a>

</div>