<?php
session_start();
include("../includes/db.php");

if(!isset($_SESSION['user_id'])){
header("Location: ../login.php");
exit();
}
?>

<!DOCTYPE html>
<html>
<head>

<title>Custom Tour Package</title>

<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
background:#f4f6f9;
font-family:'Segoe UI',sans-serif;
}

/* MAIN CONTENT */

.main-content{
margin-top:90px;
padding-bottom:40px;
}

/* CUSTOM BOX */

.custom-box{
background:white;
padding:35px;
border-radius:14px;
box-shadow:0 10px 35px rgba(0,0,0,0.08);
}

/* STEP TITLE */

.step-title{
font-weight:600;
margin-bottom:15px;
}

/* DESTINATION CARD */

.destination-card{
border:2px solid #eee;
border-radius:12px;
padding:18px;
cursor:pointer;
transition:0.3s;
text-align:center;
height:100%;
}

.destination-card:hover{
border-color:#0d6efd;
transform:translateY(-5px);
}

/* HIDE CHECKBOX */

.destination-card input{
display:none;
}

/* SELECTED EFFECT */

.destination-card.selected{
border-color:#0d6efd;
background:#eef5ff;
}

/* BUTTON */

.submit-btn{
border-radius:30px;
padding:12px;
font-weight:600;
font-size:16px;
}

/* MOBILE */

@media(max-width:768px){

.custom-box{
padding:20px;
}

.destination-card{
padding:14px;
}

h2{
font-size:24px;
}

}

</style>

</head>

<body>

<?php include("navbar_user.php"); ?>

<div class="container main-content">

<h2 class="text-center mb-4 fw-bold">
🌍 Design Your Dream Trip
</h2>

<div class="row justify-content-center">

<div class="col-lg-10 col-xl-9 col-md-11">

<div class="custom-box">

<form action="submit-custom-package.php" method="POST">

<!-- DESTINATION -->

<div class="mb-4">

<h5 class="step-title">
1️⃣ Choose Destination (Select Multiple)
</h5>

<div class="row g-3">

<div class="col-lg-4 col-md-4 col-sm-6">

<label class="destination-card w-100">

<input type="checkbox" name="destination[]" value="Darjeeling">

<h6>Darjeeling</h6>
<p class="small text-muted">Queen of Hills</p>

</label>

</div>

<div class="col-md-4 col-6">

<label class="destination-card w-100">

<input type="checkbox" name="destination[]" value="Sikkim">

<h6>Sikkim</h6>
<p class="small text-muted">Land of Mountains</p>

</label>

</div>

<div class="col-md-4 col-6">

<label class="destination-card w-100">

<input type="checkbox" name="destination[]" value="Kalimpong">

<h6>Kalimpong</h6>
<p class="small text-muted">Peaceful Hills</p>

</label>

</div>

</div>

</div>

<!-- TRAVEL STYLE -->

<div class="mb-4">

<h5 class="step-title">
2️⃣ Travel Style
</h5>

<select name="style" class="form-control" required>

<option value="">Select Style</option>
<option>Adventure</option>
<option>Honeymoon</option>
<option>Family</option>
<option>Backpacking</option>
<option>Relaxation</option>

</select>

</div>

<!-- TRIP DETAILS -->

<div class="row mb-4 g-3">

<div class="col-md-4">

<label>Travel Date</label>

<input type="date" name="travel_date" class="form-control" required>

</div>

<div class="col-md-4">

<label>Days</label>

<input type="number" name="days" class="form-control" required>

</div>

<div class="col-md-4">

<label>Travelers</label>

<input type="number" name="travelers" class="form-control" required>

</div>

</div>

<!-- HOTEL -->

<div class="mb-4">

<label class="step-title">Hotel Type</label>

<select name="hotel" class="form-control">

<option>Budget</option>
<option>Standard</option>
<option>Deluxe</option>
<option>Luxury</option>

</select>

</div>

<!-- EXPERIENCE -->

<div class="mb-4">

<label class="step-title">
3️⃣ Tell Us Your Experience
</label>

<textarea
name="experience"
class="form-control"
rows="5"
placeholder="Example: Snow places, sunrise view, tea gardens, romantic dinner.">
</textarea>

</div>

<!-- SUBMIT -->

<button class="btn btn-primary w-100 submit-btn">

Send Custom Package Request

</button>

</form>

</div>

</div>

</div>

</div>

<script>

const cards = document.querySelectorAll(".destination-card");

cards.forEach(card => {

    const checkbox = card.querySelector("input");

    card.addEventListener("click", function(){

        checkbox.checked = !checkbox.checked;

        if(checkbox.checked){
            card.classList.add("selected");
        }else{
            card.classList.remove("selected");
        }

    });

});

</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>