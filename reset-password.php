<?php

ini_set('display_errors',1);
error_reporting(E_ALL);

require_once "includes/db.php";

/* Get email and token from URL */

$email = $_GET['email'] ?? '';
$token = $_GET['token'] ?? '';

if(empty($email) || empty($token)){
die("Invalid reset request.");
}

/* Get user reset token */

$stmt = $conn->prepare("SELECT reset_token, reset_expires FROM users WHERE email=?");
$stmt->bind_param("s",$email);
$stmt->execute();

$result = $stmt->get_result();

if($result->num_rows !== 1){
die("Invalid request.");
}

$user = $result->fetch_assoc();

/* Check expiry */

if(strtotime($user['reset_expires']) < time()){
die("Reset link expired.");
}

/* Verify token */

if(!password_verify($token,$user['reset_token'])){
die("Invalid reset token.");
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<title>Reset Password</title>

<meta name="viewport" content="width=device-width, initial-scale=1">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>

body{
height:100vh;
display:flex;
align-items:center;
justify-content:center;
background:linear-gradient(135deg,#4facfe,#00f2fe);
font-family:Arial;
}

.reset-card{

width:420px;
background:white;
padding:35px;
border-radius:12px;
box-shadow:0 20px 50px rgba(0,0,0,0.2);

}

.title{

text-align:center;
font-weight:700;
margin-bottom:10px;

}

.subtitle{

text-align:center;
font-size:14px;
color:#666;
margin-bottom:25px;

}

.form-control{

height:45px;
border-radius:8px;

}

.btn-reset{

height:45px;
border-radius:8px;
background:#007bff;
color:white;
font-weight:600;

}

.btn-reset:hover{

background:#0069d9;

}

</style>

</head>

<body>

<div class="reset-card">

<h4 class="title">
<i class="fa fa-lock"></i> Reset Password
</h4>

<p class="subtitle">
Enter your new password below
</p>

<form action="update-password.php" method="POST">

<input type="hidden" name="email" value="<?=htmlspecialchars($email)?>">
<input type="hidden" name="token" value="<?=htmlspecialchars($token)?>">

<div class="form-group">

<input
type="password"
name="password"
class="form-control"
placeholder="New Password"
required>

</div>

<div class="form-group">

<input
type="password"
name="confirm_password"
class="form-control"
placeholder="Confirm Password"
required>

</div>

<button type="submit" class="btn btn-reset btn-block">

Update Password

</button>

</form>

<div class="text-center mt-3">

<a href="login.php">
<i class="fa fa-arrow-left"></i> Back to Login
</a>

</div>

</div>

</body>
</html>