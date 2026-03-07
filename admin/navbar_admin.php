<!-- ================= ADMIN NAVBAR + SIDEBAR STYLE ================= -->

<style>

/* ===== UNIQUE ADMIN LAYOUT ===== */

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
margin-bottom:25px;
letter-spacing:1px;
}

.adminLayoutLink{
display:block;
color:#cfd8dc;
padding:13px 25px;
text-decoration:none;
font-size:15px;
transition:0.3s;
border-left:3px solid transparent;
}

.adminLayoutLink:hover{
background:#F96D00;
color:white;
padding-left:35px;
border-left:3px solid white;
}

/* ===== MAIN CONTENT ===== */

.adminLayoutContent{
margin-left:250px;
padding:25px;
min-height:100vh;
transition:0.3s;
}

/* ===== MOBILE NAVBAR ===== */

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

/* ===== MOBILE VIEW ===== */

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

<a class="navbar-brand adminMobileBrand">👑 Admin Panel</a>

<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminMobileMenu">
<span class="navbar-toggler-icon"></span>
</button>

<div class="collapse navbar-collapse adminMobileMenu" id="adminMobileMenu">

<ul class="navbar-nav mt-3">

<li class="nav-item">
<a class="nav-link" href="dashboard.php">📊 Dashboard</a>
</li>

<li class="nav-item">
<a class="nav-link" href="manage_packages.php">📦 Packages</a>
</li>

<li class="nav-item">
<a class="nav-link" href="manage_bookings.php">📖 Bookings</a>
</li>

<li class="nav-item">
<a class="nav-link" href="manage_users.php">👤 Users</a>
</li>

<li class="nav-item">
<a class="nav-link" href="manage_payments.php">💳 Payments</a>
</li>

<li class="nav-item">
<a class="nav-link" href="reports.php">📈 Reports</a>
</li>

<li class="nav-item">
<a class="nav-link" href="contact_messages.php">📩 Contact Messages</a>
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

<h4 class="adminLayoutTitle">👑 Admin Panel</h4>

<a class="adminLayoutLink" href="dashboard.php">📊 Dashboard</a>
<a class="adminLayoutLink" href="manage_packages.php">📦 Manage Packages</a>
<a class="adminLayoutLink" href="manage_bookings.php">📖 Manage Bookings</a>
<a class="adminLayoutLink" href="manage_users.php">👤 Manage Users</a>
<a class="adminLayoutLink" href="manage_payments.php">💳 Manage Payments</a>
<a class="adminLayoutLink" href="reports.php">📈 Reports</a>
<a class="adminLayoutLink" href="contact_messages.php">📩 Contact Messages</a>
<a class="adminLayoutLink text-danger" href="../includes/logout.php">🚪 Logout</a>

</div>