<?php
include("../includes/session_check.php");
include("../includes/db.php");

/* ================= SERVICE TYPE ================= */
$service_type = $_GET['type'] ?? 'full';
if (!in_array($service_type, ['full','stay','sightseeing'])) {
    $service_type = 'full';
}

/* ================= FETCH ACTIVE SIGHTSEEING ================= */
$sightseeing_places = [];
$sql = "SELECT destination, place_name, description, image
        FROM sightseeing_places
        WHERE status='active'
        ORDER BY destination, place_name";
$res = mysqli_query($conn, $sql);
while($row = mysqli_fetch_assoc($res)){
    $sightseeing_places[$row['destination']][] = $row;
}

/* ================= PAGE CONFIG ================= */
$page_config = [
    'full' => [
        'title'=>'Complete Tour Package',
        'desc'=>'Pickup, stay, sightseeing & drop included',
        'icon'=>'🚗'
    ],
    'stay' => [
        'title'=>'Stay + Sightseeing',
        'desc'=>'Comfortable hotels with guided sightseeing',
        'icon'=>'🏨'
    ],
    'sightseeing' => [
        'title'=>'Only Sightseeing',
        'desc'=>'Day tour with pickup & drop',
        'icon'=>'🏔️'
    ]
];
$config = $page_config[$service_type];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?php echo $config['title']; ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<style>
:root{
    --navbar-height:72px;
    --sidebar-width:260px;
}

body{
    padding-top:var(--navbar-height);
    background:linear-gradient(135deg,#eef2ff,#f8fafc);
    font-family:'Segoe UI',sans-serif;
}

/* PAGE LAYOUT */
.page-wrapper{
    margin-left:var(--sidebar-width);
    padding:40px 30px;
    transition:.3s;
}
body.sidebar-collapsed .page-wrapper{
    margin-left:0;
}

/* PREMIUM CARD */
.premium-card{
    background:rgba(255,255,255,.88);
    backdrop-filter:blur(14px);
    border-radius:26px;
    box-shadow:0 30px 80px rgba(0,0,0,.15);
    overflow:hidden;
}

/* HEADER */
.premium-header{
    background:linear-gradient(135deg,#667eea,#764ba2);
    color:#fff;
    text-align:center;
    padding:50px;
}
.premium-header h2{font-weight:700}
.premium-header p{opacity:.9}

/* BODY */
.premium-body{padding:50px}

/* SECTION */
.section{margin-bottom:45px}
.section-title{
    font-size:20px;
    font-weight:700;
    margin-bottom:25px;
    display:flex;
    gap:12px;
    align-items:center;
}
.section-title i{color:#667eea}

/* DESTINATION */
.destination-card{
    background:#fff;
    border-radius:18px;
    padding:25px;
    text-align:center;
    border:2px solid #e5e7eb;
    cursor:pointer;
    transition:.35s;
}
.destination-card:hover{
    transform:translateY(-6px);
    box-shadow:0 18px 40px rgba(102,126,234,.25);
}
.destination-card.selected{
    border-color:#667eea;
    background:linear-gradient(135deg,#eef2ff,#fff);
}
.destination-card input{display:none}

/* SIGHTSEEING */
.sightseeing-grid{
    display:grid;
    grid-template-columns:repeat(auto-fill,minmax(240px,1fr));
    gap:22px;
}
.place-card{
    background:#fff;
    border-radius:20px;
    overflow:hidden;
    cursor:pointer;
    border:2px solid transparent;
    transition:.35s;
}
.place-card:hover{
    transform:translateY(-6px) scale(1.02);
}
.place-card.selected{
    border-color:#667eea;
    box-shadow:0 0 0 4px rgba(102,126,234,.25);
}
.place-card img{
    width:100%;
    height:170px;
    object-fit:cover;
}
.place-card .content{padding:16px}
.place-card input{display:none}

/* ESTIMATE */
#estimateBox{
    background:linear-gradient(135deg,#fff7ed,#ffedd5);
    border-radius:18px;
    border:1px solid #fed7aa;
    font-size:18px;
    font-weight:600;
    padding:22px;
    text-align:center;
}

/* SUBMIT */
.submit-btn{
    background:linear-gradient(135deg,#667eea,#764ba2);
    color:#fff;
    border:none;
    border-radius:50px;
    padding:18px;
    font-size:18px;
    width:100%;
}
</style>
</head>

<body>

<?php include("navbar_user.php"); ?>

<div class="page-wrapper">
<div class="container-xl">

<div class="premium-card">

<div class="premium-header">
<div style="font-size:52px"><?php echo $config['icon']; ?></div>
<h2><?php echo $config['title']; ?></h2>
<p><?php echo $config['desc']; ?></p>
</div>

<div class="premium-body">
<form method="POST" action="submit-custom-package.php">

<input type="hidden" name="service_type" value="<?php echo $service_type; ?>">
<input type="hidden" name="estimated_price" id="estimated_price">

<!-- PICKUP LOCATION (ALL TYPES) -->
<div class="section">
<div class="section-title"><i class="fas fa-map-marker-alt"></i>Pickup Location</div>
<select name="pickup_location" class="form-select" required>
<option value="">Choose pickup</option>
<option>NJP Junction</option>
<option>Siliguri</option>
<option>Bagdogra Airport</option>
</select>
</div>

<!-- DESTINATION -->
<div class="section">
<div class="section-title"><i class="fas fa-map"></i>Choose Destination</div>
<div class="row g-3">
<?php foreach($sightseeing_places as $destination=>$p): ?>
<div class="col-md-6">
<label class="destination-card w-100">
<input type="checkbox" name="destination[]" value="<?php echo $destination; ?>">
<h6><?php echo $destination; ?></h6>
<p>Select sightseeing places</p>
</label>
</div>
<?php endforeach; ?>
</div>
</div>

<!-- SIGHTSEEING -->
<div class="section">
<div class="section-title"><i class="fas fa-binoculars"></i>Select Sightseeing</div>

<?php foreach($sightseeing_places as $destination=>$places): ?>
<div id="<?php echo strtolower($destination); ?>-places" style="display:none">
<h6 class="mb-3"><?php echo $destination; ?> Attractions</h6>
<div class="sightseeing-grid">
<?php foreach($places as $pl): ?>
<label class="place-card">
<input type="checkbox" name="sightseeing_places[]" value="<?php echo $pl['place_name']; ?>">
<img src="../assets/images/sightseeing/<?php echo $pl['image'] ?: 'no-image.jpg'; ?>">
<div class="content">
<strong><?php echo $pl['place_name']; ?></strong>
<p class="small text-muted"><?php echo $pl['description']; ?></p>
</div>
</label>
<?php endforeach; ?>
</div>
</div>
<?php endforeach; ?>
</div>
<!-- HOTEL TYPE -->
<?php if($service_type != 'sightseeing'): ?>
<div class="section">
<div class="section-title"><i class="fas fa-hotel"></i>Select Hotel Type</div>

<select name="hotel_type" class="form-select" required>
<option value="">Choose Hotel</option>
<option value="budget">Budget </option>
<option value="standard">Standard </option>
<option value="deluxe">Deluxe </option>
<option value="luxury">Luxury </option>
</select>

</div>
<?php endif; ?>


<!-- CAR TYPE -->
<?php if($service_type != 'stay'): ?>
<div class="section">
<div class="section-title"><i class="fas fa-car"></i>Select Car Type</div>

<select name="car_type" class="form-select" required>
<option value="">Choose Car</option>
<option value="hatchback">Small car</option>
<option value="sedan">Sumo gold</option>
<option value="suv">SUV </option>
<option value="innova">Innova </option>
</select>

</div>
<?php endif; ?>

<!-- DETAILS -->
<div class="section">
<div class="section-title"><i class="fas fa-calendar"></i>Trip Details</div>
<div class="row g-3">
<div class="col-md-4">
<input type="date" name="travel_date" class="form-control" required>
</div>

<?php if($service_type!='sightseeing'): ?>
<div class="col-md-4">
<input type="number" id="days" name="days" class="form-control" placeholder="Days" required>
</div>
<?php endif; ?>

<div class="col-md-4">
<input type="number" id="travelers" name="travelers" class="form-control" placeholder="Travelers" required>
</div>
</div>
</div>

<div id="estimateBox">Estimated price will appear here</div>
<br>
<button class="submit-btn">✨ Get Final Quote</button>

</form>
</div>
</div>

</div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {

    /* ================= SIDEBAR TOGGLE ================= */
    const toggleBtn = document.querySelector('.sidebar-toggle');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            document.body.classList.toggle('sidebar-collapsed');
        });
    }

    /* ================= DESTINATION SELECT ================= */
    const destinationCards = document.querySelectorAll('.destination-card');

    destinationCards.forEach(card => {
        card.addEventListener('click', function () {
            const checkbox = this.querySelector('input');
            checkbox.checked = !checkbox.checked;
            this.classList.toggle('selected', checkbox.checked);
            updatePlaces();
        });
    });

    function updatePlaces() {
        let scrolled = false;

        // Hide all sections
        document.querySelectorAll('[id$="-places"]').forEach(div => {
            div.style.display = 'none';
        });

        // Show selected destination places
        document.querySelectorAll('input[name="destination[]"]:checked').forEach(input => {
            const id = input.value.toLowerCase().replace(/\s+/g, '-') + '-places';
            const el = document.getElementById(id);

            if (el) {
                el.style.display = 'block';

                if (!scrolled) {
                    el.scrollIntoView({ behavior: 'smooth' });
                    scrolled = true;
                }
            }
        });
    }

    /* ================= SIGHTSEEING CARD ================= */
    document.querySelectorAll('.place-card').forEach(card => {
        card.addEventListener('click', function () {
            const checkbox = this.querySelector('input');
            checkbox.checked = !checkbox.checked;
            this.classList.toggle('selected', checkbox.checked);
        });
    });

    /* ================= PRICE CALCULATION ================= */

    const travelersInput = document.getElementById('travelers');
    const daysInput = document.getElementById('days');
    const hotelInput = document.querySelector('[name="hotel_type"]');
    const carInput = document.querySelector('[name="car_type"]');

    const estimateBox = document.getElementById('estimateBox');
    const estimatedPriceInput = document.getElementById('estimated_price');

    function calc() {

        const travelers = parseInt(travelersInput?.value) || 0;
        const days = parseInt(daysInput?.value) || 1;

        const hotel = hotelInput?.value || '';
        const car = carInput?.value || '';

        let hotelPrice = 0;
        let carPrice = 0;

        /* HOTEL PRICE */
        switch (hotel) {
            case 'budget': hotelPrice = 1500; break;
            case 'standard': hotelPrice = 2500; break;
            case 'deluxe': hotelPrice = 4000; break;
            case 'luxury': hotelPrice = 7000; break;
        }

        /* CAR PRICE */
        switch (car) {
            case 'hatchback': carPrice = 2000; break;
            case 'sedan': carPrice = 3000; break;
            case 'suv': carPrice = 4500; break;
            case 'innova': carPrice = 5500; break;
        }

        /* BASE COST */
        let total = 0;

        if (travelers > 0) {
            total += travelers * 500; // per person cost
        }

        if (days > 0) {
            total += (hotelPrice * days);
            total += (carPrice * days);
        }

        /* UPDATE UI */
        if (total > 0) {
            estimateBox.innerHTML = `<strong>Estimated Price: ₹${total.toLocaleString('en-IN')}</strong>`;
            if (estimatedPriceInput) {
                estimatedPriceInput.value = total;
            }
        } else {
            estimateBox.innerHTML = "Estimated price will appear here";
        }
    }

    /* ================= EVENT LISTENERS ================= */
    [travelersInput, daysInput, hotelInput, carInput].forEach(el => {
        if (el) {
            el.addEventListener('input', calc);
            el.addEventListener('change', calc);
        }
    });

});
</script>

</body>
</html>