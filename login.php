<?php
session_start();
include("includes/db.php");

$error = "";

// Check if form is submitted
if (isset($_POST['email']) && isset($_POST['password'])) {

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Prepared statement
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {

        $user = $result->fetch_assoc();

        // Verify hashed password
        if (password_verify($password, $user['password'])) {

            // Regenerate session for security
            session_regenerate_id(true);

            // COMMON SESSION (for both user & admin)
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name']    = $user['name'];
            $_SESSION['role']    = $user['role'];

            // ADMIN LOGIN
            if ($user['role'] === 'admin') {

                // ✅ REQUIRED FOR ADMIN PAGES
                $_SESSION['admin_id'] = $user['id'];

                header("Location: admin/dashboard.php");
                exit();
            }

            // USER LOGIN
            if ($user['role'] === 'user') {
                header("Location: user/user-dashboard.php");
                exit();
            }

        } else {
            $error = "Wrong Password!";
        }

    } else {
        $error = "User Not Found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>Tour N Travels - Login</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	
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

	<style>
	body {
		background: url('images/bg_5.jpg') no-repeat center center fixed;
		background-size: cover;
	}

	.card {
		background-color: rgba(255,255,255,0.9);
		border-radius: 20px;
	}

	.password-toggle{
		position:absolute;
		right:15px;
		bottom:12px;
		cursor:pointer;
		color:#F96D00;
	}
	</style>
</head>
<body>
<?php include 'navbar.php'; ?>

<main class="pt-5 mt-4">
<div class="min-vh-100 d-flex align-items-center justify-content-center">
  <div class="card shadow-lg p-4 p-md-5 w-100 mx-3" style="max-width: 480px;">
    <div class="card-body text-center">

      <!-- Logo / Title -->
      <h1 class="mb-3" style="font-family: 'Arizonia', cursive; color:#F96D00; font-size: 2.5rem;">Tour N Travels</h1>

      <!-- Login pitch -->
      <p class="mb-4 text-dark fw-semibold fs-6 fs-md-5">
        Welcome back! 🌄 <br>
        Sign in to continue planning your dream trips with Tour N Travels.
      </p>

      <!-- Display error if exists -->
      <?php if($error != ""){ ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
      <?php } ?>

      <!-- Login Form -->
      <form method="POST">
        <div class="mb-3">
          <input type="email" class="form-control form-control-lg rounded-pill border-0 shadow-sm" name="email" placeholder="Email Address" required>
        </div>
        <div class="mb-4 position-relative">
          <input type="password" class="form-control form-control-lg rounded-pill border-0 shadow-sm" id="password" name="password" placeholder="Password" required>
          <span class="password-toggle" onclick="togglePassword('password')"><i class="fa fa-eye"></i></span>
        </div>
        <div class="text-end mb-3">
          <a href="forgot-password.php" class="text-decoration-none text-warning fw-semibold">
            Forgot Password?
          </a>
        </div>
        
        <div class="d-grid mb-3">
          <button type="submit" class="btn btn-warning btn-lg rounded-pill fw-bold shadow-sm">Login</button>
        </div>

        <hr class="mb-3">

        <div class="text-center">
          <p class="mb-0 text-dark fw-semibold">
            Don't have an account? 
            <a href="register.php" class="text-warning fw-bold text-decoration-none">Sign Up</a>
          </p>
        </div>
      </form>

      <!-- Optional travel quote -->
      <p class="mt-4 fst-italic text-secondary small">
        "Travel is the only thing you buy that makes you richer." ✈️
      </p>

    </div>
  </div>
</div>
</main>
<?php include 'footer.php'; ?>
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
<script src="js/jquery.timepicker.min.js"></script>
<script src="js/scrollax.min.js"></script>
<script src="js/main.js"></script>
<!-- Password toggle -->
<script>
  function togglePassword(id) {
    const input = document.getElementById(id);
    const icon = input.nextElementSibling.querySelector('i');
    if (input.type === 'password') {
      input.type = 'text';
      icon.classList.remove('fa-eye');
      icon.classList.add('fa-eye-slash');
    } else {
      input.type = 'password';
      icon.classList.remove('fa-eye-slash');
      icon.classList.add('fa-eye');
    }
  }
</script>

</body>
</html>