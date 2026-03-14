<?php
include("includes/db.php");

$msg="";

if(isset($_POST['send'])){

$name=$_POST['name'];
$email=$_POST['email'];
$subject=$_POST['subject'];
$message=$_POST['message'];

$sql="INSERT INTO contact_messages(name,email,subject,message)
VALUES('$name','$email','$subject','$message')";

if($conn->query($sql)){
$msg="Message Sent Successfully!";
}

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<title>Contact Hidden Hills Collective | Plan Your Darjeeling Trip</title>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="Get in touch with Hidden Hills Collective to plan your perfect trip to Darjeeling, Mirik, and Kalimpong. Contact us for bookings, inquiries, and travel advice.">
<meta name="keywords" content="Darjeeling travel, Darjeeling homestays, Mirik tourism, Kalimpong travel, hill tourism West Bengal">
<meta property="og:title" content="Contact Hidden Hills Collective | Plan Your Darjeeling Trip">
<meta property="og:description" content="Get in touch with Hidden Hills Collective to plan your perfect trip to Darjeeling, Mirik, and Kalimpong. Contact us for bookings, inquiries, and travel advice.">
<meta property="og:type" content="website">
<meta property="og:url" content="https://hiddenhills.com/contact">
<meta property="og:image" content="images/bg_1.jpg">
<link rel="icon" href="images/favicon.png">

<link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700,800,900" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Arizonia&display=swap" rel="stylesheet">

<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">

<link rel="stylesheet" href="css/animate.css">
<link rel="stylesheet" href="css/owl.carousel.min.css">
<link rel="stylesheet" href="css/owl.theme.default.min.css">
<link rel="stylesheet" href="css/magnific-popup.css">
<link rel="stylesheet" href="css/bootstrap-datepicker.css">
<link rel="stylesheet" href="css/jquery.timepicker.css">
<link rel="stylesheet" href="css/flaticon.css">
<link rel="stylesheet" href="css/style.css">

</head>

<body>

<?php include 'navbar.php'; ?>

<section class="hero-wrap hero-wrap-2 js-fullheight" style="background-image:url('images/bg_1.jpg');">
<div class="overlay"></div>

<div class="container">
<div class="row no-gutters slider-text js-fullheight align-items-end justify-content-center">

<div class="col-md-9 ftco-animate pb-5 text-center">

<p class="breadcrumbs">
<span class="mr-2">
<a href="index.php">Home <i class="fa fa-chevron-right"></i></a>
</span>

<span>Contact Us</span>
</p>

<h1 class="mb-0 bread">Contact Us</h1>

</div>
</div>
</div>
</section>


<section class="ftco-section ftco-no-pb contact-section mb-4">

<div class="container">

<div class="row d-flex contact-info">

<div class="col-md-3 d-flex">

<div class="align-self-stretch box p-4 text-center">

<div class="icon d-flex align-items-center justify-content-center">
<span class="fa fa-map-marker"></span>
</div>

<h3 class="mb-2">Address</h3>

<p>Batasia, Darjeeling, West Bengal</p>

</div>
</div>


<div class="col-md-3 d-flex">

<div class="align-self-stretch box p-4 text-center">

<div class="icon d-flex align-items-center justify-content-center">
<span class="fa fa-phone"></span>
</div>

<h3 class="mb-2">Contact Number</h3>

<p><a href="tel:+918116064514">+91 8116064514</a></p>

</div>
</div>


<div class="col-md-3 d-flex">

<div class="align-self-stretch box p-4 text-center">

<div class="icon d-flex align-items-center justify-content-center">
<span class="fa fa-paper-plane"></span>
</div>

<h3 class="mb-2">Email Address</h3>

<p><a href="mailto:vinayakmoktan4@gmail.com">vinayakmoktan4@gmail.com</a></p>

</div>
</div>


<div class="col-md-3 d-flex">

<div class="align-self-stretch box p-4 text-center">

<div class="icon d-flex align-items-center justify-content-center">
<span class="fa fa-globe"></span>
</div>

<h3 class="mb-2">Website</h3>

<p><a href="index.php">Tour N Travel</a></p>

</div>
</div>

</div>
</div>
</section>


<section class="ftco-section contact-section ftco-no-pt">

<div class="container">

<div class="row block-9">

<div class="col-md-6 order-md-last d-flex">

<form method="POST" class="bg-light p-5 contact-form">

<?php if($msg!=""){ ?>

<div class="alert alert-success">
<?php echo $msg; ?>
</div>

<?php } ?>

<div class="form-group">
<input type="text" name="name" class="form-control" placeholder="Your Name" required>
</div>

<div class="form-group">
<input type="email" name="email" class="form-control" placeholder="Your Email" required>
</div>

<div class="form-group">
<input type="text" name="subject" class="form-control" placeholder="Subject">
</div>

<div class="form-group">
<textarea name="message" cols="30" rows="7" class="form-control" placeholder="Message"></textarea>
</div>

<div class="form-group">
<input type="submit" name="send" value="Send Message" class="btn btn-primary py-3 px-5">
</div>

</form>

</div>


<div class="col-md-6 d-flex">

<iframe
src="https://maps.google.com/maps?q=darjeeling%20west%20bengal&t=&z=13&ie=UTF8&iwloc=&output=embed"
width="100%"
height="100%"
style="border:0;">
</iframe>

</div>

</div>
</div>

</section>



<?php include 'footer.php'; ?>


<!-- WhatsApp Floating Button -->

<a href="https://wa.me/918116064514"
style="position:fixed;bottom:20px;right:20px;background:#25D366;color:white;padding:12px 16px;border-radius:50px;text-decoration:none;font-size:18px;z-index:999;">

<i class="fa fa-whatsapp"></i> Book Now

</a>



<!-- Loader -->

<div id="ftco-loader" class="show fullscreen">
<svg class="circular" width="48px" height="48px">
<circle class="path-bg" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke="#eeeeee"/>
<circle class="path" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke="#F96D00"/>
</svg>
</div>


<script src="js/jquery.min.js"></script>
<script src="js/jquery-migrate-3.0.1.min.js"></script>
<script src="js/popper.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/jquery.easing.1.3.js"></script>
<script src="js/jquery.waypoints.min.js"></script>
<script src="js/jquery.stellar.min.js"></script>
<script src="js/owl.carousel.min.js"></script>
<script src="js/jquery.magnific-popup.min.js"></script>
<script src="js/jquery.animateNumber.min.js"></script>
<script src="js/bootstrap-datepicker.js"></script>
<script src="js/scrollax.min.js"></script>
<script src="js/main.js"></script>

</body>
</html>