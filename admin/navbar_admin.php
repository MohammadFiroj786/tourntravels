<style>

/* ===== SIDEBAR ===== */

.sidebar{
width:250px;
height:100vh;
background:linear-gradient(180deg,#1e1e2f,#2c2c54);
position:fixed;
left:0;
top:0;
color:white;
padding-top:20px;
z-index:1000;
box-shadow:4px 0 20px rgba(0,0,0,0.2);
}

.sidebar h4{
font-weight:600;
letter-spacing:1px;
}

.sidebar a{
display:block;
color:#cfd8dc;
padding:13px 25px;
text-decoration:none;
transition:0.3s;
font-size:15px;
border-left:3px solid transparent;
}

.sidebar a:hover{
background:#F96D00;
color:white;
padding-left:35px;
border-left:3px solid #fff;
}

/* ===== MAIN CONTENT ===== */

.main-content{
margin-left:250px;
padding:25px;
transition:0.3s;
}

/* ===== MOBILE ===== */

@media(max-width:991px){

.sidebar{
display:none;
}

.main-content{
margin-left:0;
padding:15px;
}

}

/* ===== MOBILE NAVBAR ===== */

.navbar-brand{
font-weight:600;
letter-spacing:1px;
}

.navbar-dark{
background:linear-gradient(135deg,#1e1e2f,#2c2c54);
}

.navbar-collapse{
background:#1e1e2f;
padding:15px;
border-radius:10px;
margin-top:10px;
}

.nav-link{
padding:10px;
border-bottom:1px solid rgba(255,255,255,0.1);
}

.nav-link:hover{
color:#F96D00 !important;
}

</style>


<!-- MOBILE NAVBAR -->

<nav class="navbar navbar-dark d-lg-none shadow-sm">
<div class="container-fluid">

<a class="navbar-brand">👑 Admin Panel</a>

<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mobileMenu">
<span class="navbar-toggler-icon"></span>
</button>

<div class="collapse navbar-collapse" id="mobileMenu">

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


<!-- DESKTOP SIDEBAR -->

<div class="sidebar d-none d-lg-block">

<h4 class="text-center mb-4">👑 Admin Panel</h4>

<a href="dashboard.php">📊 Dashboard</a>
<a href="manage_packages.php">📦 Manage Packages</a>
<a href="manage_bookings.php">📖 Manage Bookings</a>
<a href="manage_users.php">👤 Manage Users</a>
<a href="manage_payments.php">💳 Manage Payments</a>
<a href="reports.php">📈 Reports</a>
<a href="contact_messages.php">📩 Contact Messages</a>
<a href="../includes/logout.php" class="text-danger">🚪 Logout</a>

</div>