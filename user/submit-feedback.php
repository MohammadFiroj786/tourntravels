<?php
include("../includes/session_check.php");
include("../includes/db.php");

$user_id = $_SESSION['user_id'];

$booking_id = $_POST['booking_id'];
$rating = $_POST['rating'];
$emoji = $_POST['emoji'];
$message = $_POST['message'];

// prevent duplicate
$check = $conn->query("SELECT id FROM feedback WHERE booking_id=$booking_id");

if($check->num_rows == 0){
    $conn->query("INSERT INTO feedback (user_id, booking_id, rating, emoji, message)
    VALUES ('$user_id','$booking_id','$rating','$emoji','$message')");
}

header("Location: feedback.php");