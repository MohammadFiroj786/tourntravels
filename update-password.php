<?php

ini_set('display_errors',1);
error_reporting(E_ALL);

require_once "includes/db.php";

/* Only POST request allowed */

if($_SERVER["REQUEST_METHOD"] !== "POST"){
header("Location: login.php");
exit();
}

/* Get form data */

$email = $_POST['email'] ?? '';
$token = $_POST['token'] ?? '';
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

/* Basic validation */

if(empty($email) || empty($token) || empty($password) || empty($confirm_password)){
die("Invalid request.");
}

/* Password match */

if($password !== $confirm_password){
die("Passwords do not match.");
}

/* Password strength */

if(strlen($password) < 8){
die("Password must be at least 8 characters.");
}

/* Fetch reset token */

$stmt = $conn->prepare("SELECT reset_token, reset_expires FROM users WHERE email=?");
$stmt->bind_param("s",$email);
$stmt->execute();

$result = $stmt->get_result();

if($result->num_rows !== 1){
die("Invalid request.");
}

$user = $result->fetch_assoc();

/* Expiry check */

if(strtotime($user['reset_expires']) < time()){
die("Reset link expired.");
}

/* Verify token */

if(!password_verify($token,$user['reset_token'])){
die("Invalid reset token.");
}

/* Hash new password */

$new_password = password_hash($password, PASSWORD_DEFAULT);

/* Update password */

$update = $conn->prepare("UPDATE users 
SET password=?, reset_token=NULL, reset_expires=NULL 
WHERE email=?");

$update->bind_param("ss",$new_password,$email);

if($update->execute()){

header("Location: login.php?reset=success");
exit();

}else{

die("Something went wrong.");

}

?>