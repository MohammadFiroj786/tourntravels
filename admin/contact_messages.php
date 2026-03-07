<?php
session_start();
include("../includes/db.php");
require_once("../env.php");

require_once("../includes/PHPMailer/src/PHPMailer.php");
require_once("../includes/PHPMailer/src/SMTP.php");
require_once("../includes/PHPMailer/src/Exception.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if(!isset($_SESSION['admin_id'])){
header("Location: ../login.php");
exit();
}

/* DELETE MESSAGE */

if(isset($_GET['delete'])){
$id = intval($_GET['delete']);
$conn->query("DELETE FROM contact_messages WHERE id='$id'");
header("Location: contact_messages.php");
exit();
}

/* SEND REPLY */

if(isset($_POST['send_reply'])){

$id = intval($_POST['id']);
$reply = trim($_POST['reply']);

$data = $conn->query("SELECT * FROM contact_messages WHERE id='$id'");
$row = $data->fetch_assoc();

$email = $row['email'];
$name = $row['name'];
$subject = "Tour N Travels Support [Ticket #$id] - ".$row['subject'];

$mail = new PHPMailer(true);

try{

$mail->isSMTP();
$mail->Host       = MAIL_HOST;
$mail->SMTPAuth   = true;
$mail->Username   = MAIL_USERNAME;
$mail->Password   = MAIL_PASSWORD;
$mail->SMTPSecure = 'tls';
$mail->Port       = MAIL_PORT;

$mail->setFrom(MAIL_USERNAME,"Tour N Travels");
$mail->addReplyTo(MAIL_USERNAME,"Tour N Travels Support");

$mail->addAddress($email,$name);

$mail->isHTML(true);

$mail->Subject = $subject;

$mail->Body = "

<div style='font-family:Arial,sans-serif;background:#f4f6f9;padding:20px;'>

<div style='max-width:600px;margin:auto;background:#ffffff;border-radius:6px;padding:25px;'>

<h2 style='color:#0d6efd;'>Tour N Travels Support</h2>

<p>Hello <b>$name</b>,</p>

<p>Thank you for contacting <b>Tour N Travels</b>.  
Our support team has reviewed your enquiry and here is our response:</p>

<div style='background:#f8f9fa;padding:15px;border-left:4px solid #0d6efd;margin:15px 0;'>
$reply
</div>

<p>If you need further assistance regarding tours or travel services, please feel free to contact us again through our website.</p>

<p>
Best regards,<br>
<b>Tour N Travels Support Team</b>
</p>

<hr>

<p style='font-size:12px;color:#777;'>

Please note: This email address is not monitored for replies.<br>
If you need further assistance, please contact us through the contact form on our website.<br><br>

Website: <a href='".APP_URL."'>".APP_URL."</a>

</p>

</div>

</div>

";

$mail->AltBody = "Hello $name,

$reply

Best regards,
Tour N Travels Support Team

Please do not reply to this email. Visit ".APP_URL;

$mail->send();

/* UPDATE STATUS */

$stmt = $conn->prepare("UPDATE contact_messages SET reply=?, status='Replied', is_read=1 WHERE id=?");
$stmt->bind_param("si",$reply,$id);
$stmt->execute();

header("Location: contact_messages.php");
exit();

}catch(Exception $e){

echo "Mailer Error: ".$mail->ErrorInfo;

}

}

/* UNREAD COUNT */

$unread = $conn->query("SELECT COUNT(*) as total FROM contact_messages WHERE is_read=0")->fetch_assoc()['total'];

?>

<!DOCTYPE html>
<html>
<head>

<title>Contact Messages | Admin</title>

<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
background:#f4f6f9;
}

.main-content{
margin-left:250px;
padding:25px;
}

@media(max-width:991px){
.main-content{
margin-left:0;
}
}

.card-box{
background:white;
padding:25px;
border-radius:10px;
box-shadow:0 0 10px rgba(0,0,0,0.1);
}

.table th{
background:#0d6efd;
color:white;
}

.status-pending{
color:#dc3545;
font-weight:600;
}

.status-replied{
color:#198754;
font-weight:600;
}

.unread-row{
background:#fff3cd;
}

.message-box{
max-width:250px;
white-space:pre-line;
}

</style>

</head>

<body>

<?php include("navbar_admin.php"); ?>

<div class="main-content">

<h3 class="mb-4">
📩 Contact Messages

<?php if($unread>0){ ?>
<span class="badge bg-danger"><?php echo $unread; ?> New</span>
<?php } ?>

</h3>

<div class="card-box">

<!-- SEARCH BAR -->

<form method="GET" class="mb-3">

<div class="input-group">

<input type="text" 
name="search" 
class="form-control"
placeholder="Search name, email or subject..."
value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>">

<button class="btn btn-primary">Search</button>

<a href="contact_messages.php" class="btn btn-secondary">Reset</a>

</div>

</form>

<div class="table-responsive">

<table class="table table-bordered table-striped align-middle">

<thead>

<tr>

<th>ID</th>
<th>Name</th>
<th>Email</th>
<th>Subject</th>
<th>Message</th>
<th>Status</th>
<th width="250">Reply</th>
<th width="120">Action</th>

</tr>

</thead>

<tbody>

<?php

if(isset($_GET['search']) && $_GET['search']!=""){

$search = $conn->real_escape_string($_GET['search']);

$sql = "SELECT * FROM contact_messages 
WHERE name LIKE '%$search%' 
OR email LIKE '%$search%' 
OR subject LIKE '%$search%' 
ORDER BY id DESC";

}else{

$sql = "SELECT * FROM contact_messages ORDER BY id DESC";

}

$data = $conn->query($sql);

while($row = $data->fetch_assoc()){

if($row['is_read']==0){
$conn->query("UPDATE contact_messages SET is_read=1 WHERE id=".$row['id']);
}

?>

<tr class="<?php echo ($row['is_read']==0) ? 'unread-row' : ''; ?>">

<td><?php echo $row['id']; ?></td>

<td>

<?php echo htmlspecialchars($row['name']); ?>

<?php if($row['is_read']==0){ ?>
<span class="badge bg-danger">New</span>
<?php } ?>

</td>

<td><?php echo htmlspecialchars($row['email']); ?></td>

<td><?php echo htmlspecialchars($row['subject']); ?></td>

<td class="message-box">
<?php echo htmlspecialchars($row['message']); ?>
</td>

<td>

<?php

if($row['status']=="Pending"){
echo "<span class='status-pending'>Pending</span>";
}else{
echo "<span class='status-replied'>Replied</span>";
}

?>

</td>

<td>

<?php if($row['status']=="Pending"){ ?>

<form method="POST">

<input type="hidden" name="id" value="<?php echo $row['id']; ?>">

<textarea name="reply" class="form-control mb-2" rows="2" placeholder="Write reply..." required></textarea>

<button class="btn btn-success btn-sm" name="send_reply">
Send Reply
</button>

</form>

<?php } else { ?>

<div class="alert alert-success p-2 mb-0">
<?php echo htmlspecialchars($row['reply']); ?>
</div>

<?php } ?>

</td>

<td>

<a href="contact_messages.php?delete=<?php echo $row['id']; ?>"
class="btn btn-danger btn-sm"
onclick="return confirm('Delete this message?')">

Delete

</a>

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>