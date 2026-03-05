<?php
session_start();
include("../includes/db.php");

if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php");
    exit();
}

if(!isset($_GET['id'])){
    header("Location: packages.php");
    exit();
}

$id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT * FROM packages WHERE id=?");
$stmt->bind_param("i",$id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows == 0){
    echo "<h3 class='text-center mt-5'>Package not found</h3>";
    exit();
}

$package = $result->fetch_assoc();

$images = array_filter(array_map('trim', explode(",", $package['image'])));
$total_images = count($images);

$final_price = ($package['discount'] > 0)
? $package['price'] - ($package['price'] * $package['discount'] / 100)
: $package['price'];
?>

<!DOCTYPE html>
<html>
<head>

<title><?= $package['title'] ?> | Tour Package</title>

<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
background:#f4f6f9;
font-family:Segoe UI;
}

.image-container{
position:relative;
}

.main-image{
width:100%;
height:420px;
object-fit:cover;
border-radius:12px;
}

.nav-btn{
position:absolute;
top:50%;
transform:translateY(-50%);
background:rgba(0,0,0,0.5);
color:white;
border:none;
font-size:28px;
padding:8px 14px;
border-radius:50%;
cursor:pointer;
}

.nav-btn:hover{
background:#000;
}

.prev{
left:10px;
}

.next{
right:10px;
}

.thumb{
width:80px;
height:60px;
object-fit:cover;
border-radius:8px;
cursor:pointer;
border:2px solid transparent;
}

.thumb:hover{
border:2px solid #0d6efd;
}

.price{
font-size:28px;
font-weight:bold;
color:#28a745;
}

.old-price{
text-decoration:line-through;
color:#888;
}

.btn-book{
border-radius:30px;
padding:10px 25px;
}

</style>

</head>

<body>

<div class="container py-5">

<div class="row g-4">

<!-- IMAGE SECTION -->

<div class="col-lg-7">

<div class="image-container">

<img id="mainImage"
src="../uploads/<?= $images ? $images[0] : 'default.jpg' ?>"
class="main-image shadow">

<?php if($total_images > 1){ ?>

<button class="nav-btn prev" onclick="prevImage()">❮</button>
<button class="nav-btn next" onclick="nextImage()">❯</button>

<?php } ?>

</div>

<?php if($total_images > 1){ ?>

<div class="d-flex gap-2 mt-3 flex-wrap">

<?php foreach($images as $index => $img){ ?>

<img src="../uploads/<?= $img ?>"
class="thumb"
onclick="setImage(<?= $index ?>)">

<?php } ?>

</div>

<?php } ?>

</div>

<!-- PACKAGE INFO -->

<div class="col-lg-5">

<h2><?= $package['title'] ?></h2>

<p class="text-muted"><?= $package['duration'] ?></p>

<div class="mb-3">

<span class="price">₹<?= number_format($final_price) ?></span>

<?php if($package['discount'] > 0){ ?>

<span class="badge bg-danger">
<?= $package['discount'] ?>% OFF
</span>

<br>

<span class="old-price">₹<?= number_format($package['price']) ?></span>

<?php } ?>

</div>

<p>
<strong>Seats Available:</strong> <?= $package['seats'] ?>
</p>

<div class="mt-3">

<a href="book.php?id=<?= $package['id'] ?>"
class="btn btn-primary btn-book me-2">
Book Now
</a>

<a href="packages.php"
class="btn btn-outline-secondary btn-book">
Back
</a>

</div>

<div class="mt-3">

<a href="https://wa.me/918158036?text=Hello I want details for <?= urlencode($package['title']) ?>"
class="btn btn-success w-100">
WhatsApp Inquiry
</a>

</div>

</div>

</div>

<hr class="my-5">

<h4>Description</h4>

<p><?= nl2br($package['description']) ?></p>

</div>

<script>

let images = <?php echo json_encode(array_values($images)); ?>;
let currentIndex = 0;

function showImage(){
document.getElementById("mainImage").src = "../uploads/" + images[currentIndex];
}

function nextImage(){

currentIndex++;

if(currentIndex >= images.length){
currentIndex = 0;
}

showImage();

}

function prevImage(){

currentIndex--;

if(currentIndex < 0){
currentIndex = images.length - 1;
}

showImage();

}

function setImage(index){

currentIndex = index;
showImage();

}

/* AUTO SLIDER */

if(images.length > 1){

setInterval(function(){
nextImage();
},3000);

}

</script>

</body>
</html>