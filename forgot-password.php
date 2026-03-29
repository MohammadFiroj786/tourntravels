<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Forgot Password</title>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>

body{
height:100vh;
display:flex;
align-items:center;
justify-content:center;
background:linear-gradient(135deg,#667eea,#764ba2);
font-family:Arial;
}

.card-box{
width:400px;
background:white;
padding:35px;
border-radius:10px;
box-shadow:0 15px 40px rgba(0,0,0,0.2);
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
background:#667eea;
color:white;
font-weight:600;
}

.btn-reset:hover{
background:#5a67d8;
}

.back-link{
text-align:center;
margin-top:15px;
}

</style>

</head>

<body>

<div class="card-box">

<h4 class="title">
<i class="fa fa-key"></i> Forgot Password
</h4>

<p class="subtitle">
Enter your registered email to receive a password reset link.
</p>


<?php if(isset($_GET['success'])){ ?>

<div class="alert alert-success text-center">
Reset link sent successfully. Please check your email.
</div>

<?php } ?>


<?php if(isset($_GET['notfound'])){ ?>

<div class="alert alert-danger text-center">
This email is not registered in our system.
</div>

<?php } ?>


<form action="send-reset-link.php" method="POST">

<div class="form-group">

<div class="input-group">

<div class="input-group-prepend">

<span class="input-group-text">
<i class="fa fa-envelope"></i>
</span>

</div>

<input
type="email"
name="email"
class="form-control"
placeholder="Enter your email"
required>

</div>

</div>

<button type="submit" class="btn btn-reset btn-block">
Send Reset Link
</button>

</form>

<div class="back-link">

<a href="login.php">
<i class="fa fa-arrow-left"></i> Back to Login
</a>

</div>

</div>

</body>
</html>