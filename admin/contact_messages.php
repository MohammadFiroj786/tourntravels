<?php
session_start();
include("../includes/db.php");

if(!isset($_SESSION['admin_id'])){
header("Location: ../login.php");
exit();
}

/* DELETE MESSAGE */

if(isset($_GET['delete'])){

$id = $_GET['delete'];

$conn->query("DELETE FROM contact_messages WHERE id='$id'");

header("Location: contact_messages.php");
exit();

}

/* SEND REPLY */

if(isset($_POST['send_reply'])){

$id = $_POST['id'];
$reply = $_POST['reply'];

/* GET USER DATA */

$data = $conn->query("SELECT * FROM contact_messages WHERE id='$id'");
$row = $data->fetch_assoc();

$email = $row['email'];
$name = $row['name'];
$subject = "Reply: ".$row['subject'];

/* EMAIL HEADERS */

$headers = "From: Tour N Travel <no-reply@tourntravel.com>\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

/* SEND EMAIL */

mail($email,$subject,$reply,$headers);

/* UPDATE DATABASE */

$conn->query("UPDATE contact_messages 
SET reply='$reply', status='Replied'
WHERE id='$id'");

header("Location: contact_messages.php");
exit();

}

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

/* MAIN CONTENT */

.main-content{
margin-left:250px;
padding:25px;
}

@media(max-width:991px){

.main-content{
margin-left:0;
}

}

/* CARD */

.card-box{
background:white;
padding:25px;
border-radius:10px;
box-shadow:0 0 10px rgba(0,0,0,0.1);
}

/* TABLE */

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

.message-box{
max-width:250px;
white-space:pre-line;
}

</style>

</head>

<body>

<!-- Sidebar -->
<?php include("navbar_admin.php"); ?>

<div class="main-content">

<h3 class="mb-4">📩 Contact Messages</h3>

<div class="card-box">

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

$data = $conn->query("SELECT * FROM contact_messages ORDER BY id DESC");

while($row = $data->fetch_assoc()){

?>

<tr>

<td><?php echo $row['id']; ?></td>

<td><?php echo $row['name']; ?></td>

<td><?php echo $row['email']; ?></td>

<td><?php echo $row['subject']; ?></td>

<td class="message-box">
<?php echo $row['message']; ?>
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
<?php echo $row['reply']; ?>
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