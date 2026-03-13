<?php
session_start();
include("../includes/db.php");

if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$destination = $_POST['destination'] ?? '';
$style = $_POST['style'] ?? '';
$travel_date = $_POST['travel_date'] ?? '';
$days = $_POST['days'] ?? '';
$travelers = $_POST['travelers'] ?? '';
$hotel = $_POST['hotel'] ?? '';
$experience = $_POST['experience'] ?? '';

if(empty($destination) || empty($style) || empty($travel_date) || empty($days) || empty($travelers)){

echo "Invalid request";
exit();

}

$stmt = $conn->prepare("INSERT INTO custom_package_requests 
(user_id,destination,style,travel_date,days,travelers,hotel,experience)
VALUES (?,?,?,?,?,?,?,?)");

$stmt->bind_param("isssiiss",
$user_id,
$destination,
$style,
$travel_date,
$days,
$travelers,
$hotel,
$experience
);

$stmt->execute();

?>

<!DOCTYPE html>
<html>
<head>

<title>Request Sent</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
background:#f4f6f9;
font-family:'Segoe UI',sans-serif;
}

.box{
margin-top:120px;
background:white;
padding:40px;
border-radius:12px;
box-shadow:0 10px 30px rgba(0,0,0,0.1);
text-align:center;
}

</style>

</head>

<body>

<div class="container">

<div class="row justify-content-center">

<div class="col-md-6">

<div class="box">

<h3 class="text-success mb-3">
✅ Request Sent Successfully
</h3>

<p>
Our travel expert will contact you soon to discuss your custom trip and finalize the itinerary.
</p>

<a href="packages.php" class="btn btn-primary mt-3">
Back to Packages
</a>

</div>

</div>

</div>

</div>

</body>
</html>