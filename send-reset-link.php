<?php

session_start();

require_once "includes/db.php";
require_once "env.php";

/* PHPMailer */
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require "includes/PHPMailer/src/PHPMailer.php";
require "includes/PHPMailer/src/SMTP.php";
require "includes/PHPMailer/src/Exception.php";


/* allow only POST */

if($_SERVER["REQUEST_METHOD"] !== "POST"){
header("Location: forgot-password.php");
exit();
}


$email = trim($_POST['email'] ?? '');


/* validate email */

if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
header("Location: forgot-password.php?notfound=1");
exit();
}


/* check if user exists */

$stmt = $conn->prepare("SELECT id,email FROM users WHERE email=?");
$stmt->bind_param("s",$email);
$stmt->execute();
$result = $stmt->get_result();


/* email not found */

if($result->num_rows !== 1){

header("Location: forgot-password.php?notfound=1");
exit();

}


/* generate token */

$token = bin2hex(random_bytes(32));
$hashed_token = password_hash($token, PASSWORD_DEFAULT);


/* expiry time (10 minutes) */

$expire = date("Y-m-d H:i:s", strtotime("+10 minutes"));


/* update database */

$update = $conn->prepare("UPDATE users SET reset_token=?, reset_expires=? WHERE email=?");
$update->bind_param("sss",$hashed_token,$expire,$email);
$update->execute();


/* create reset link */

$link = APP_URL."/reset-password.php?email=".$email."&token=".$token;


/* send email */

$mail = new PHPMailer(true);

try{

$mail->isSMTP();
$mail->Host = MAIL_HOST;
$mail->SMTPAuth = true;
$mail->Username = MAIL_USERNAME;
$mail->Password = MAIL_PASSWORD;
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = MAIL_PORT;

$mail->setFrom(MAIL_USERNAME, "Tour & Travels");

$mail->addAddress($email);

$mail->isHTML(true);

$mail->Subject = "Password Reset Request";

$mail->Body = "

<h2>Password Reset</h2>

<p>Hello,</p>

<p>We received a request to reset your password.</p>

<p>Click the button below to reset your password:</p>

<a href='$link'
style='
display:inline-block;
padding:12px 20px;
background:#667eea;
color:white;
text-decoration:none;
border-radius:6px;
'>
Reset Password
</a>

<p style='margin-top:20px;color:#666'>
This link will expire in 10 minutes.
If you did not request this, please ignore this email.
</p>

";

$mail->send();

}catch(Exception $e){

error_log($mail->ErrorInfo);

}


/* success message */

header("Location: forgot-password.php?success=1");
exit();

?>