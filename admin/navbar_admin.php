<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- ================= ADMIN STYLE ================= -->

<style>

/* SIDEBAR */

.adminLayoutSidebar{
width:250px;
height:100vh;
background:linear-gradient(180deg,#1e1e2f,#2c2c54);
position:fixed;
top:0;
left:0;
color:white;
padding-top:20px;
z-index:1000;
overflow-y:auto;
box-shadow:4px 0 20px rgba(0,0,0,0.2);
}

.adminLayoutTitle{
text-align:center;
font-weight:600;
margin-bottom:20px;
letter-spacing:1px;
}

/* LINKS */

.adminLayoutLink{
display:block;
color:#cfd8dc;
padding:13px 25px;
text-decoration:none;
font-size:15px;
transition:0.3s;
border-left:3px solid transparent;
}

/* HOVER EFFECT */

.adminLayoutLink:hover{
background:linear-gradient(90deg,#F96D00,#ff9a3c);
color:white;
padding-left:35px;
border-left:3px solid white;
}

/* ACTIVE PAGE */

.adminLayoutLink.active{
background:#F96D00;
color:white;
border-left:3px solid white;
}

/* DIVIDER */

.adminLayoutSidebar hr{
border-color: rgba(255,255,255,0.1);
margin:15px 0;
}

/* MAIN CONTENT */

.adminLayoutContent{
margin-left:250px;
padding:25px;
min-height:100vh;
transition:0.3s;
}

/* MOBILE NAVBAR */

.adminMobileNavbar{
background:linear-gradient(135deg,#1e1e2f,#2c2c54);
}

.adminMobileBrand{
font-weight:600;
letter-spacing:1px;
}

.adminMobileMenu{
background:#1e1e2f;
padding:15px;
border-radius:10px;
margin-top:10px;
}

.adminMobileMenu .nav-link{
padding:10px;
border-bottom:1px solid rgba(255,255,255,0.1);
}

.adminMobileMenu .nav-link:hover{
color:#F96D00 !important;
}

/* MOBILE */

@media(max-width:991px){

.adminLayoutSidebar{
display:none;
}

.adminLayoutContent{
margin-left:0;
padding:15px;
}

}

</style>

<!-- ================= MOBILE NAVBAR ================= -->

<nav class="navbar navbar-dark adminMobileNavbar d-lg-none shadow-sm">

<div class="container-fluid">

<a class="navbar-brand adminMobileBrand">✈️ Hidden Hills Admin</a>

<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminMobileMenu">
<span class="navbar-toggler-icon"></span>
</button>

<div class="collapse navbar-collapse adminMobileMenu" id="adminMobileMenu">

<ul class="navbar-nav mt-3">

<li class="nav-item">
<a class="nav-link <?= ($current_page=='dashboard.php')?'active':'' ?>" href="dashboard.php">📊 Dashboard</a>
</li>

<li class="nav-item">
<a class="nav-link <?= ($current_page=='custom-package-requests.php')?'active':'' ?>" href="custom-package-requests.php">🌍 Tour Requests</a>
</li>

<li class="nav-item">
<a class="nav-link <?= ($current_page=='manage_bookings.php')?'active':'' ?>" href="manage_bookings.php">📖 Bookings</a>
</li>

<li class="nav-item">
<a class="nav-link <?= ($current_page=='manage_users.php')?'active':'' ?>" href="manage_users.php">👤 Users</a>
</li>

<li class="nav-item">
<a class="nav-link <?= ($current_page=='manage_payments.php')?'active':'' ?>" href="manage_payments.php">💳 Payments</a>
</li>

<li class="nav-item">
<a class="nav-link <?= ($current_page=='reports.php')?'active':'' ?>" href="reports.php">📈 Reports</a>
</li>

<li class="nav-item">
<a class="nav-link <?= ($current_page=='contact_messages.php')?'active':'' ?>" href="contact_messages.php">📩 Messages</a>
</li>

<li class="nav-item">
<a class="nav-link text-danger" href="../includes/logout.php">🚪 Logout</a>
</li>

</ul>

</div>

</div>

</nav>

<!-- ================= DESKTOP SIDEBAR ================= -->

<div class="adminLayoutSidebar d-none d-lg-block">

<h4 class="adminLayoutTitle">
✈️ Hidden Hills Admin
</h4>

<hr>

<a class="adminLayoutLink <?= ($current_page=='dashboard.php')?'active':'' ?>" href="dashboard.php">
📊 Dashboard
</a>

<a class="adminLayoutLink <?= ($current_page=='custom-package-requests.php')?'active':'' ?>" href="custom-package-requests.php">
🌍 Tour Requests
</a>

<a class="adminLayoutLink <?= ($current_page=='manage_bookings.php')?'active':'' ?>" href="manage_bookings.php">
📖 Bookings
</a>

<a class="adminLayoutLink <?= ($current_page=='manage_users.php')?'active':'' ?>" href="manage_users.php">
👤 Users
</a>

<a class="adminLayoutLink <?= ($current_page=='manage_payments.php')?'active':'' ?>" href="manage_payments.php">
💳 Payments
</a>

<a class="adminLayoutLink <?= ($current_page=='reports.php')?'active':'' ?>" href="reports.php">
📈 Reports
</a>

<a class="adminLayoutLink <?= ($current_page=='contact_messages.php')?'active':'' ?>" href="contact_messages.php">
📩 Messages
</a>

<hr>

<a class="adminLayoutLink text-danger" href="../includes/logout.php">
🚪 Logout
</a>

</div>