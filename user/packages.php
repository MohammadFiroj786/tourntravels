<?php
include("../includes/session_check.php");
include("../includes/db.php");

/* ================= FETCH TOP REVIEWS ================= */
$reviews = [];
$sql = "
SELECT f.rating, f.emoji, f.message, f.created_at, u.name
FROM feedback f
JOIN users u ON u.id = f.user_id
ORDER BY f.rating DESC, f.created_at DESC
LIMIT 6
";
$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    $reviews[] = $row;
}
$currentMonth = date('Y-m');
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
color:white;
font-family:'Segoe UI',sans-serif;
}

.glass{
background:rgba(255,255,255,0.1);
backdrop-filter:blur(10px);
border-radius:20px;
padding:25px;
}

.package-card{
border-radius:20px;
transition:.3s;
}

.package-card:hover{
transform:translateY(-10px);
}

.review-slider{
overflow:hidden;
position:relative;
}

.review-track{
display:flex;
gap:20px;
transition:transform .5s ease;
}

.review-premium{
min-width:320px;
}

.review-avatar{
width:50px;
height:50px;
border-radius:50%;
background:#6c63ff;
color:white;
display:flex;
align-items:center;
justify-content:center;
font-weight:bold;
}
</style>

</head>

<body>

<?php include("navbar_user.php"); ?>

<div class="main-content p-4">

<!-- HEADER -->
<div class="text-center mb-5">
<h2 class="fw-bold">✨ Create Your Perfect Trip</h2>
<p>Explore Darjeeling & Sikkim like never before 🌄</p>
<p class="opacity-75">Choose your package and start your journey today</p>
</div>

<!-- PACKAGE CARDS -->
<div class="glass">
<div class="row g-4 text-center">

<?php
$packages = [
["full","🚗","Full Trip Package","Pickup, stay, sightseeing & drop","🔥 Most Booked"],
["stay","🏨","Stay + Sightseeing","Comfortable hotels with tours",""],
["sightseeing","🏔️","Only Sightseeing","Day tours & popular attractions",""],
["offbeat","🏕️","Offbeat Experience","Village life, tea gardens, camping","🌲 New"]
];

foreach($packages as $p){
?>
<div class="col-md-3">
<a href="custom-package.php?type=<?= $p[0] ?>" class="text-decoration-none">
<div class="card package-card shadow h-100 text-dark">
<div class="card-body">

<?php if($p[4]): ?>
<span class="badge bg-danger position-absolute top-0 end-0 m-2"><?= $p[4] ?></span>
<?php endif; ?>

<div class="fs-1"><?= $p[1] ?></div>
<h5 class="mt-3"><?= $p[2] ?></h5>
<p><?= $p[3] ?></p>

<small class="text-muted">
✔ Flexible plans<br>
✔ Local experts<br>
✔ Best price guarantee
</small>

</div>
</div>
</a>
</div>
<?php } ?>

</div>
</div>

<!-- EMOTIONAL -->
<div class="text-center mt-4">
<p>🌄 Imagine watching sunrise at Tiger Hill with your loved ones...</p>
</div>

<!-- TRUST -->
<div class="row text-center mt-4">
<div class="col-md-3">
<div class="p-3 bg-light text-dark rounded shadow-sm">✔ 100+ Happy Travelers</div>
</div>
<div class="col-md-3">
<div class="p-3 bg-light text-dark rounded shadow-sm">✔ Verified Drivers</div>
</div>
<div class="col-md-3">
<div class="p-3 bg-light text-dark rounded shadow-sm">✔ No Hidden Charges</div>
</div>
<div class="col-md-3">
<div class="p-3 bg-light text-dark rounded shadow-sm">✔ 24/7 Support</div>
</div>
</div>

<!-- REVIEWS -->
<div class="mt-5">

<h3 class="text-center fw-bold">What Travelers Are Saying ❤️</h3>
<p class="text-center opacity-75 mb-4">
Real stories from people who explored with us
</p>

<div class="review-slider">

<div class="review-track" id="reviewTrack">

<?php foreach($reviews as $r):
$isNew = substr($r['created_at'],0,7)===$currentMonth;
$initial = strtoupper(substr($r['name'],0,1));
?>

<div class="card review-premium shadow border-0 text-dark">
<div class="card-body">

<div class="d-flex align-items-center mb-3">
<div class="review-avatar me-3"><?= $initial ?></div>
<div>
<div class="fw-bold"><?= htmlspecialchars($r['name']) ?></div>
<div class="text-warning">
<?= str_repeat("★",$r['rating']) ?> <?= $r['emoji'] ?>
</div>
</div>
</div>

<p><?= htmlspecialchars($r['message']) ?></p>

<small class="text-muted">
<?= date("F Y", strtotime($r['created_at'])) ?>
</small>

</div>
</div>

<?php endforeach; ?>

</div>

</div>
</div>

<!-- WHATSAPP -->
<div class="text-center mt-4">
<a href="https://wa.me/91XXXXXXXXXX?text=I%20want%20a%20trip%20quote"
class="btn btn-success px-4 py-2">
💬 Chat on WhatsApp
</a>

<p class="mt-2 opacity-75">
💡 No payment required now. Get quote first.
</p>
</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
let reviewIndex = 0;
const track = document.getElementById("reviewTrack");
const total = track.children.length;

/* Slide */
function slideReview(step){
    reviewIndex += step;

    if(reviewIndex >= total){
        reviewIndex = 0;
    }

    if(reviewIndex < 0){
        reviewIndex = total - 1;
    }

    track.style.transform = `translateX(-${reviewIndex * 340}px)`;
}

/* Auto Slide */
let autoSlide = setInterval(() => slideReview(1), 4000);

/* Pause on hover */
document.querySelector(".review-slider").addEventListener("mouseenter", () => {
    clearInterval(autoSlide);
});

document.querySelector(".review-slider").addEventListener("mouseleave", () => {
    autoSlide = setInterval(() => slideReview(1), 4000);
});
</script>

</body>
</html>