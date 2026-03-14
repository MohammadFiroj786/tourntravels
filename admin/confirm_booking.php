<?php
session_start();
include("../includes/db.php");

/* ================= SECURITY CHECK ================= */
if(!isset($_SESSION['admin_id'])){
    header("Location: ../login.php");
    exit();
}

/* ================= VALIDATE REQUEST ================= */
if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    header("Location: custom-package-requests.php");
    exit();
}

/* ================= GET POST DATA ================= */
$request_id  = $_POST['request_id'] ?? '';
$user_id     = $_POST['user_id'] ?? '';
$travel_date = $_POST['travel_date'] ?? '';
$persons     = $_POST['persons'] ?? '';
$price       = $_POST['price'] ?? '';

/* ================= BASIC VALIDATION ================= */
if(empty($request_id) || empty($user_id) || empty($travel_date) || empty($persons) || empty($price)){
    header("Location: custom-package-requests.php?error=1");
    exit();
}

/* ================= CHECK ALREADY CONFIRMED ================= */
$check = $conn->prepare("
    SELECT status 
    FROM custom_package_requests 
    WHERE id=?
");
$check->bind_param("i", $request_id);
$check->execute();
$result = $check->get_result();
$row = $result->fetch_assoc();

if(!$row || $row['status'] === 'Accepted'){
    // Already confirmed → prevent duplicate booking
    header("Location: custom-package-requests.php");
    exit();
}

/* ================= UPDATE REQUEST ================= */
$update = $conn->prepare("
    UPDATE custom_package_requests
    SET price=?, status='Accepted'
    WHERE id=?
");
$update->bind_param("di", $price, $request_id);
$update->execute();

/* ================= INSERT BOOKING ================= */
$insert = $conn->prepare("
    INSERT INTO bookings
    (user_id, package_id, travel_date, persons, total_price, booking_status)
    VALUES (?, NULL, ?, ?, ?, 'Confirmed')
");
$insert->bind_param("isid", $user_id, $travel_date, $persons, $price);
$insert->execute();

/* ================= SUCCESS REDIRECT ================= */
header("Location: custom-package-requests.php?confirmed=1");
exit();
?>