<?php
include("../includes/session_check.php");
?>

<!DOCTYPE html>
<html>
<head>

<title>Choose Package Type</title>

<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
background:linear-gradient(135deg,#667eea,#764ba2);
font-family:'Segoe UI',sans-serif;
color:white;
}

/* Container */
.main-content{
min-height:100vh;
display:flex;
align-items:center;
}

/* Cards */
.package-card{
border-radius:20px;
transition:0.4s;
cursor:pointer;
background:white;
color:#333;
position:relative;
overflow:hidden;
}

/* Hover Effect */
.package-card:hover{
transform:translateY(-12px) scale(1.02);
box-shadow:0 25px 50px rgba(0,0,0,0.4);
}

/* Glow Border */
.package-card::before{
content:'';
position:absolute;
top:0;
left:-100%;
width:100%;
height:100%;
background:linear-gradient(120deg,transparent,rgba(255,255,255,0.5),transparent);
transition:0.5s;
}

.package-card:hover::before{
left:100%;
}

/* Icons */
.icon{
font-size:55px;
}

/* Badge */
.popular-badge{
position:absolute;
top:10px;
right:10px;
background:#ff4757;
color:white;
padding:5px 10px;
border-radius:20px;
font-size:12px;
}

/* Trust Section */
.trust-box{
background:rgba(255,255,255,0.1);
border-radius:15px;
padding:15px;
margin-top:30px;
}

/* Review */
.review-box{
background:white;
color:#333;
border-radius:12px;
padding:15px;
margin-top:10px;
}

/* WhatsApp Button */
.whatsapp-btn{
background:#25D366;
color:white;
font-weight:bold;
border-radius:10px;
padding:12px;
display:inline-block;
text-decoration:none;
margin-top:20px;
}

.whatsapp-btn:hover{
background:#1ebe5d;
}

</style>

</head>

<body>

<?php include("navbar_user.php"); ?>

<div class="main-content">
<div class="container">

<!-- HEADER -->
<div class="text-center mb-5">
<h2 class="fw-bold">✨ Create Your Perfect Trip</h2>
<p style="font-size:18px;">
Explore Darjeeling & Sikkim like never before 🌄
</p>
<p style="opacity:0.8;">
Choose your package and start your journey today
</p>
</div>

<!-- PACKAGE CARDS -->
<div class="row text-center">

<!-- FULL TRIP -->
<div class="col-md-4 mb-4">
<a href="custom-package.php?type=full" class="text-decoration-none">
<div class="card package-card p-4">

<div class="popular-badge">🔥 Most Booked</div>

<div class="icon">🚗</div>
<h4 class="mt-3">Full Trip Package</h4>
<p>Pickup + Stay + Sightseeing + Drop</p>

<small class="text-success">Best for stress-free travel</small>

</div>
</a>
</div>

<!-- STAY -->
<div class="col-md-4 mb-4">
<a href="custom-package.php?type=stay" class="text-decoration-none">
<div class="card package-card p-4">

<div class="icon">🏨</div>
<h4 class="mt-3">Stay + Sightseeing</h4>
<p>Hotel + Tours + Activities</p>

<small class="text-primary">Perfect for flexible trips</small>

</div>
</a>
</div>

<!-- SIGHTSEEING -->
<div class="col-md-4 mb-4">
<a href="custom-package.php?type=sightseeing" class="text-decoration-none">
<div class="card package-card p-4">

<div class="icon">🏔️</div>
<h4 class="mt-3">Only Sightseeing</h4>
<p>Day tours + popular spots</p>

<small class="text-warning">Budget friendly option</small>

</div>
</a>
</div>

</div>

<!-- EMOTIONAL LINE -->
<div class="text-center mt-4">
<p style="font-size:18px;">
🌄 Imagine watching sunrise at Tiger Hill with your loved ones...
</p>
</div>

<!-- TRUST BADGES -->
<div class="row text-center trust-box">
<div class="col-md-3">✔ 100+ Happy Travelers</div>
<div class="col-md-3">✔ Verified Drivers</div>
<div class="col-md-3">✔ No Hidden Charges</div>
<div class="col-md-3">✔ 24/7 Support</div>
</div>

<!-- REVIEWS -->
<div class="mt-4 text-center">
<h5>⭐ What Our Travelers Say</h5>

<div class="review-box">
⭐⭐⭐⭐⭐ "Amazing trip! Everything was perfectly managed." – Rahul
</div>

<div class="review-box">
⭐⭐⭐⭐⭐ "Best experience in Sikkim. Highly recommended!" – Priya
</div>

</div>

<!-- WHATSAPP -->
<div class="text-center">
<a href="https://wa.me/91XXXXXXXXXX?text=I%20want%20a%20trip%20quote"
class="whatsapp-btn">
💬 Chat on WhatsApp
</a>

<p class="mt-2" style="opacity:0.8;">
💡 No payment required now. Get quote first.
</p>
</div>

</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>