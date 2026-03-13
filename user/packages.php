<?php
session_start();
include("../includes/db.php");

if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php");
    exit();
}

$search = "";
$min_price = "";
$max_price = "";

$sql = "SELECT * FROM packages WHERE status='active'";

if(isset($_GET['search']) && $_GET['search'] != ""){
    $search = $_GET['search'];
    $sql .= " AND title LIKE '%$search%'";
}

if(isset($_GET['min_price']) && $_GET['min_price'] != ""){
    $min_price = $_GET['min_price'];
    $sql .= " AND final_price >= '$min_price'";
}

if(isset($_GET['max_price']) && $_GET['max_price'] != ""){
    $max_price = $_GET['max_price'];
    $sql .= " AND final_price <= '$max_price'";
}

$sql .= " ORDER BY id DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>

<title>Tour Packages</title>

<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
background:#f4f6f9;
font-family:'Segoe UI',sans-serif;
}

/* ===== NAVBAR FIX ===== */

nav{
position:fixed;
top:0;
left:0;
width:100%;
z-index:999;
}

/* Prevent bootstrap from changing logo style */

.navbar-brand{
font-size:20px !important;
font-weight:600 !important;
display:flex;
align-items:center;
gap:6px;
}

/* MAIN CONTENT */

.main-content{
margin-top:90px;
}

/* PACKAGE CARD */

.package-card{
border-radius:16px;
overflow:hidden;
transition:0.3s;
height:100%;
display:flex;
flex-direction:column;
}

.package-card:hover{
transform:translateY(-8px);
box-shadow:0 15px 40px rgba(0,0,0,0.2);
}

.package-img{
height:220px;
object-fit:cover;
}

.card-body{
flex:1;
display:flex;
flex-direction:column;
}

.card-title{
font-weight:600;
}

/* DISCOUNT */

.discount-badge{
position:absolute;
top:10px;
left:10px;
background:#ff4d4d;
color:white;
padding:6px 12px;
font-size:13px;
border-radius:6px;
font-weight:600;
}

/* PRICE */

.price{
font-size:22px;
font-weight:700;
color:#28a745;
}

.old-price{
text-decoration:line-through;
color:#999;
font-size:14px;
margin-right:6px;
}

/* SEATS */

.seats-warning{
color:#ff4d4d;
font-weight:600;
font-size:14px;
}

/* BUTTONS */

.buttons{
margin-top:auto;
}

.btn{
border-radius:8px;
}

/* WHATSAPP */

.whatsapp-btn{
background:#25D366;
color:white;
}

.whatsapp-btn:hover{
background:#1ebd5a;
}

/* MOBILE */

@media(max-width:768px){

.package-img{
height:180px;
}

.main-content{
margin-top:100px;
}

}

</style>

</head>

<body>

<!-- NAVBAR -->
<?php include("navbar_user.php"); ?>

<div class="main-content">

<div class="container">

<h2 class="text-center mb-5 fw-bold">
🌍 Explore Tour Packages
</h2>

<form method="GET" class="row mb-4 g-2">

<div class="col-md-4">
<input type="text" name="search" class="form-control"
placeholder="Search destination..."
value="<?php echo $search; ?>">
</div>

<div class="col-md-3">
<input type="number" name="min_price" class="form-control"
placeholder="Min Price"
value="<?php echo $min_price; ?>">
</div>

<div class="col-md-3">
<input type="number" name="max_price" class="form-control"
placeholder="Max Price"
value="<?php echo $max_price; ?>">
</div>

<div class="col-md-2">
<button class="btn btn-primary w-100">Filter</button>
</div>

</form>

<div class="row">

<div class="col-md-4 mb-4">

<div class="card package-card shadow-lg border-0" 
style="background:linear-gradient(135deg,#ff9a9e,#fad0c4); color:white;">

<div class="card-body text-center d-flex flex-column justify-content-center">

<h4 class="fw-bold mb-3">
✈️ Plan Your Own Trip
</h4>

<p class="mb-4">
Can't find the perfect package?  
Create your own trip to <b>Darjeeling, Sikkim, or Kalimpong</b>.
Our travel expert will plan everything for you.
</p>

<a href="custom-package.php"
class="btn btn-light fw-bold">
Create Custom Package
</a>

</div>

</div>

</div>

<?php while($row = $result->fetch_assoc()) {

$images = explode(",", $row['image']);
$totalImages = count($images);
$firstImage = $images[0];

?>

<div class="col-md-4 mb-4">

<div class="card package-card shadow-sm">

<div class="position-relative">

<?php if($row['discount'] > 0){ ?>
<div class="discount-badge">
🔥 <?php echo $row['discount']; ?>% OFF
</div>
<?php } ?>

<?php if($totalImages > 1){ ?>

<div id="carousel<?php echo $row['id']; ?>" class="carousel slide" data-bs-ride="carousel">

<div class="carousel-inner">

<?php
$i=0;
foreach($images as $img){
?>

<div class="carousel-item <?php if($i==0) echo 'active'; ?>">
<img src="../uploads/<?php echo $img; ?>" class="d-block w-100 package-img">
</div>

<?php
$i++;
}
?>

</div>

<button class="carousel-control-prev" type="button"
data-bs-target="#carousel<?php echo $row['id']; ?>" data-bs-slide="prev">
<span class="carousel-control-prev-icon"></span>
</button>

<button class="carousel-control-next" type="button"
data-bs-target="#carousel<?php echo $row['id']; ?>" data-bs-slide="next">
<span class="carousel-control-next-icon"></span>
</button>

</div>

<?php } else { ?>

<img src="../uploads/<?php echo $firstImage; ?>" class="w-100 package-img">

<?php } ?>

</div>

<div class="card-body">

<h5 class="card-title"><?php echo $row['title']; ?></h5>

<p class="text-muted">
<?php echo substr($row['description'],0,100); ?>...
</p>

<p class="mb-2">
⏱ Duration: <?php echo $row['duration']; ?>
</p>

<?php if($row['seats'] <= 5 && $row['seats'] > 0){ ?>
<p class="seats-warning">
⚠ Only <?php echo $row['seats']; ?> Seats Left
</p>
<?php } ?>

<?php if($row['seats'] == 0){ ?>
<p class="text-danger fw-bold">❌ Sold Out</p>
<?php } ?>

<p class="price">

<?php if($row['discount'] > 0){ ?>
<span class="old-price">₹<?php echo $row['price']; ?></span>
₹<?php echo $row['final_price']; ?>
<?php } else { ?>
₹<?php echo $row['price']; ?>
<?php } ?>

</p>

<div class="buttons">

<a href="package-details.php?id=<?php echo $row['id']; ?>"
class="btn btn-outline-primary btn-sm w-100 mb-2">
View Details
</a>

<?php if($row['seats'] > 0){ ?>

<a href="book.php?id=<?php echo $row['id']; ?>"
class="btn btn-primary btn-sm w-100 mb-2">
Book Now
</a>

<?php } else { ?>

<button class="btn btn-secondary btn-sm w-100 mb-2" disabled>
Sold Out
</button>

<?php } ?>

<a href="https://wa.me/918158036510?text=Hello I want details about <?php echo urlencode($row['title']); ?>"
class="btn whatsapp-btn btn-sm w-100">
📱 WhatsApp Inquiry
</a>

</div>

</div>

</div>

</div>

<?php } ?>

</div>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>