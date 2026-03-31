<?php
include("../includes/session_check.php");
include("../includes/db.php");

$user_id = $_SESSION['user_id'];

if (!isset($_GET['booking_id'])) {
    header("Location: my-bookings.php");
    exit();
}
$booking_id = intval($_GET['booking_id']);

/* Check if feedback already exists */
$check = $conn->query("SELECT id FROM feedback WHERE booking_id=$booking_id");
$locked = $check->num_rows > 0;

$msg = "";
$success = false;

if (isset($_POST['send']) && !$locked) {

    $rating = intval($_POST['rating']);
    $emoji  = $conn->real_escape_string($_POST['emoji']);
    $text   = $conn->real_escape_string($_POST['message']);

    if ($rating < 1 || empty($emoji)) {
        $msg = "<div class='alert alert-danger'>❌ Please complete all steps</div>";
    } else {
        $conn->query("
            INSERT INTO feedback (user_id, booking_id, rating, emoji, message)
            VALUES ($user_id, $booking_id, $rating, '$emoji', '$text')
        ");
        $locked = true;
        $success = true;
        $msg = "<div class='alert alert-success text-center'>
                🎉 Thank you! Your journey is recorded.
                </div>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Journey Feedback</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body{
    background: linear-gradient(120deg,#0f2027,#203a43,#2c5364);
    min-height:100vh;
    padding-bottom:80px;
    font-family: system-ui;
    color:#fff;
}
.timeline{
    max-width:420px;
    margin:40px auto;
}
.card-step{
    background: rgba(255,255,255,0.08);
    backdrop-filter: blur(18px);
    border-radius:22px;
    padding:26px;
    margin-bottom:18px;
}
.hidden{display:none;}
.emotions span{
    display:inline-block;
    margin:6px;
    padding:10px 18px;
    border-radius:30px;
    background: rgba(255,255,255,.12);
    cursor:pointer;
    transition:.25s;
}
.emotions span.active{
    background:#ffd166;
    color:#000;
}
.stars span{
    font-size:30px;
    color:#555;
    cursor:pointer;
}
.stars span.active{color:#ffd166;}
textarea{
    width:100%;
    background:transparent;
    border:none;
    border-bottom:2px solid rgba(255,255,255,.3);
    color:#fff;
}
textarea:focus{
    outline:none;
    border-color:#ffd166;
}
button.submit-btn{
    background:#ffd166;
    border:none;
    border-radius:30px;
    padding:12px;
    font-weight:600;
}

/* Sticky Back Button */
.sticky-back{
    position:fixed;
    bottom:0;
    left:0;
    right:0;
    background:rgba(15,32,39,.95);
    padding:12px;
    text-align:center;
    z-index:999;
}
.sticky-back a{
    display:inline-block;
    background:rgba(255,255,255,.15);
    padding:10px 24px;
    border-radius:30px;
    color:#fff;
    text-decoration:none;
    font-weight:500;
}
</style>
</head>

<body>

<div class="timeline">

<h4 class="text-center mb-4">🛤️ Your Journey in 30 Seconds</h4>

<?= $msg ?>

<?php if(!$locked): ?>
<form method="POST">

<!-- STEP 1 -->
<div class="card-step" id="step1">
<p>1️⃣ How did it feel when the trip ended?</p>
<div class="emotions">
<span data="😍">Magical</span>
<span data="😊">Happy</span>
<span data="😐">Okay</span>
<span data="😕">Disappointing</span>
<span data="😡">Frustrating</span>
</div>
</div>

<!-- STEP 2 -->
<div class="card-step hidden" id="step2">
<p>2️⃣ Overall rating</p>
<div class="stars">
<?php for($i=1;$i<=5;$i++): ?>
<span data="<?= $i ?>">★</span>
<?php endfor; ?>
</div>
</div>

<!-- STEP 3 -->
<div class="card-step hidden" id="step3">
<p>3️⃣ One line you’ll remember</p>
<textarea name="message" rows="2"
placeholder="“The view from the hotel was unreal…”"></textarea>
</div>

<input type="hidden" name="emoji" id="emoji">
<input type="hidden" name="rating" id="rating">

<button class="submit-btn w-100 mt-3" name="send">
Finish Journey
</button>

</form>
<?php endif; ?>

</div>

<!-- Sticky Back Button -->
<div class="sticky-back">
    <a href="my-bookings.php">← Back to My Bookings</a>
</div>

<!-- 🎉 CONFETTI -->
<?php if($success): ?>
<script>
const confettiCount = 120;
for(let i=0;i<confettiCount;i++){
    let c = document.createElement("div");
    c.style.position="fixed";
    c.style.width="8px";
    c.style.height="8px";
    c.style.background=`hsl(${Math.random()*360},80%,60%)`;
    c.style.top="-10px";
    c.style.left=Math.random()*100+"%";
    c.style.opacity=Math.random();
    c.style.zIndex=1000;
    document.body.appendChild(c);

    let fall = c.animate([
        { transform:`translateY(0)` },
        { transform:`translateY(${window.innerHeight+20}px)` }
    ],{
        duration:2000+Math.random()*1000,
        easing:"ease-out"
    });

    fall.onfinish=()=>c.remove();
}
</script>
<?php endif; ?>

<script>
const emo=document.querySelectorAll('.emotions span');
const stars=document.querySelectorAll('.stars span');
const step2=document.getElementById('step2');
const step3=document.getElementById('step3');
const emoji=document.getElementById('emoji');
const rating=document.getElementById('rating');

emo.forEach(e=>{
    e.onclick=()=>{
        emo.forEach(x=>x.classList.remove('active'));
        e.classList.add('active');
        emoji.value=e.dataset.emoji || e.getAttribute("data");
        step2.classList.remove('hidden');
    }
});

stars.forEach((s,i)=>{
    s.onclick=()=>{
        rating.value=i+1;
        stars.forEach(x=>x.classList.remove('active'));
        for(let j=0;j<=i;j++)stars[j].classList.add('active');
        step3.classList.remove('hidden');
    }
});
</script>

</body>
</html>