<?php
session_start();
include("../includes/db.php");

if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* ===== FUNCTION TO FIX ARRAY ERROR ===== */

function clean_input($data){
    if(is_array($data)){
        return implode(",", $data);
    }
    return $data;
}

/* ===== GET FORM DATA ===== */

$destination = clean_input($_POST['destination'] ?? '');
$style = clean_input($_POST['style'] ?? '');
$travel_date = clean_input($_POST['travel_date'] ?? '');
$days = clean_input($_POST['days'] ?? '');
$travelers = clean_input($_POST['travelers'] ?? '');
$hotel = clean_input($_POST['hotel'] ?? '');
$experience = clean_input($_POST['experience'] ?? '');

/* ===== VALIDATION ===== */

if(empty($destination) || empty($travel_date) || empty($days) || empty($travelers)){
    echo "Invalid Request";
    exit();
}

/* ===== INSERT DATA ===== */

$stmt = $conn->prepare("INSERT INTO custom_package_requests
(user_id,destination,style,travel_date,days,travelers,hotel,experience)
VALUES (?,?,?,?,?,?,?,?)");

$stmt->bind_param(
"isssiiss",
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
$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>

<title>Request Sent</title>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body>

<script>

Swal.fire({
    title: "Request Sent!",
    text: "Our travel expert will contact you soon to plan your trip.",
    icon: "success",
    confirmButtonText: "Go to Packages",
    confirmButtonColor: "#3085d6",
    background: "#ffffff",
    backdrop: `
        rgba(0,0,0,0.6)
    `
}).then((result) => {
    if (result.isConfirmed) {
        window.location.href = "packages.php";
    }
});

</script>

</body>
</html>