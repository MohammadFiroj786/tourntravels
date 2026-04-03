<?php
include("../includes/session_check.php");
include("../includes/db.php");

/* ================= SERVICE TYPE ================= */
$service_type = $_GET['type'] ?? 'full';
if (!in_array($service_type, ['full','stay','sightseeing','offbeat'])) {
    $service_type = 'full';
}

/* ================= FETCH ACTIVE SIGHTSEEING ================= */
$sightseeing_places = [];
$sql = "SELECT destination, place_name, description, image, is_offbeat
        FROM sightseeing_places
        WHERE status='active'";

if ($service_type === 'offbeat') {
    $sql .= " AND is_offbeat = 1";
}

$sql .= " ORDER BY destination, is_offbeat ASC, place_name ASC";
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
        'desc'=>'Comfortable homestays with guided sightseeing',
        'icon'=>'🏨'
    ],
    'sightseeing' => [
        'title'=>'Only Sightseeing',
        'desc'=>'Day tour with pickup & drop',
        'icon'=>'🏔️'
    ],
    'offbeat' => [
        'title'=>'Offbeat Experiences',
        'desc'=>'Unique adventures and authentic stays',
        'icon'=>'🏕️'
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

    <?php if($service_type == 'offbeat'): ?>
    <!-- OFFBEAT FORM -->
    <!-- PICKUP LOCATION -->
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
    <input type="radio" name="destination" value="<?php echo $destination; ?>" required>
    <h6><?php echo $destination; ?></h6>
    <p>Select offbeat destination</p>
    </label>
    </div>
    <?php endforeach; ?>
    </div>
    </div>

    <!-- SIGHTSEEING (OFFBEAT ONLY) -->
    <div class="section">
    <div class="section-title"><i class="fas fa-tree"></i>Select Offbeat Experiences</div>

    <?php foreach($sightseeing_places as $destination=>$places): ?>
    <div id="<?php echo strtolower($destination); ?>-places" style="display:none">
    <h6 class="mb-3"><?php echo $destination; ?> Offbeat Places</h6>
    <div class="sightseeing-grid">
    <?php foreach($places as $pl): ?>
        <?php if($pl['is_offbeat'] == 1): ?>
            <label class="place-card">
            <input type="checkbox" name="sightseeing_places[]" value="<?php echo htmlspecialchars($pl['place_name']); ?>">
            <img src="../assets/images/sightseeing/<?php echo htmlspecialchars($pl['image'] ?: 'no-image.jpg'); ?>">
            <div class="content">
            <strong><?php echo htmlspecialchars($pl['place_name']); ?></strong>
            <p class="small text-muted"><?php echo htmlspecialchars($pl['description']); ?></p>
            </div>
            </label>
        <?php endif; ?>
    <?php endforeach; ?>
    </div>
    </div>
    <?php endforeach; ?>
    </div>

    <!-- STAY TYPE -->
    <div class="section">
    <div class="section-title"><i class="fas fa-home"></i>Stay Type</div>
    <select name="hotel_type" class="form-select" required>
    <option value="">Choose Stay</option>
    <option value="Tent">Tent</option>
    <option value="Homestay">Homestay</option>
    <option value="Cottage">Cottage</option>
    </select>
    </div>

    <!-- CAR TYPE -->
    <div class="section">
    <div class="section-title"><i class="fas fa-car"></i>Select Car Type</div>
    <select name="car_type" class="form-select" required>
    <option value="">Choose Car</option>
    <option value="small_car">Small car</option>
    <option value="sumo_gold">Sumo gold</option>
    <option value="suv">SUV</option>
    <option value="innova">Innova</option>
    </select>
    </div>

    <!-- DETAILS -->
    <div class="section">
    <div class="section-title"><i class="fas fa-calendar"></i>Trip Details</div>
    <div class="row g-3">
    <div class="col-md-4">
    <input type="date" name="travel_date" class="form-control" required>
    </div>
    <div class="col-md-4">
    <input type="number" id="days" name="days" class="form-control" placeholder="Nights" required>
    </div>
    <div class="col-md-4">
    <input type="number" id="travelers" name="travelers" class="form-control" placeholder="Travelers" required>
    </div>
    </div>
    </div>

    <?php else: ?>
    <!-- REGULAR FORM -->
    <!-- PICKUP LOCATION (ONLY FOR FULL) -->
    <?php if($service_type == 'full'): ?>
    <div class="section">
    <div class="section-title"><i class="fas fa-map-marker-alt"></i>Pickup Location</div>
    <select name="pickup_location" class="form-select" required>
    <option value="">Choose pickup</option>
    <option>NJP Junction</option>
    <option>Siliguri</option>
    <option>Bagdogra Airport</option>
    </select>
    </div>
    <?php endif; ?>

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
    <div class="section-title"><i class="fas fa-binoculars"></i>Select Sightseeing Places</div>

    <?php foreach($sightseeing_places as $destination=>$places): ?>
    <div id="<?php echo strtolower($destination); ?>-places" style="display:none">
    <h6 class="mb-3"><?php echo $destination; ?> Places</h6>
    <div class="sightseeing-grid">
    <?php
    $offbeat_started = false;
    foreach($places as $pl):
        if ($pl['is_offbeat'] == 1 && !$offbeat_started) {
            echo '<div class="col-12 mb-3"><hr class="my-3"><h6 class="text-center text-primary fw-bold">Offbeat Experiences</h6><hr class="my-3"></div>';
            $offbeat_started = true;
        }
    ?>
    <label class="place-card">
    <input type="checkbox" name="sightseeing_places[]" value="<?php echo htmlspecialchars($pl['place_name']); ?>">
    <img src="../assets/images/sightseeing/<?php echo htmlspecialchars($pl['image'] ?: 'no-image.jpg'); ?>">
    <div class="content">
    <strong><?php echo htmlspecialchars($pl['place_name']); ?></strong>
    <p class="small text-muted"><?php echo htmlspecialchars($pl['description']); ?></p>
    </div>
    </label>
    <?php endforeach; ?>
    </div>
    </div>
    <?php endforeach; ?>
    </div>

    <!-- HOMESTAY TYPE -->
    <?php if($service_type != 'sightseeing'): ?>
    <div class="section">
    <div class="section-title"><i class="fas fa-hotel"></i>Select Homestay Type</div>

    <select name="hotel_type" class="form-select" required>
    <option value="">Choose Homestay</option>
    <option value="3star">3 Star</option>
    <option value="4star">4 Star</option>
    <option value="5star">5 Star</option>
    </select>

    </div>
    <?php endif; ?>


    <!-- CAR TYPE -->
    <?php if($service_type != 'stay'): ?>
    <div class="section">
    <div class="section-title"><i class="fas fa-car"></i>Select Car Type</div>

    <select name="car_type" class="form-select" required>
    <option value="">Choose Car</option>
    <option value="Small_car">Small car</option>
    <option value="Sumo_gold">Sumo gold</option>
    <option value="SUV">SUV </option>
    <option value="Innova">Innova </option>
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

    <?php endif; ?>

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
                const input = this.querySelector('input');

                if (input.type === 'radio') {
                    input.checked = true;
                } else {
                    input.checked = !input.checked;
                }

                destinationCards.forEach(c => c.classList.remove('selected'));
                if (input.checked) {
                    this.classList.add('selected');
                }

                updatePlaces();
            });
        });

        function updatePlaces() {
            let scrolled = false;

            // Hide all sections
            document.querySelectorAll('[id$="-places"]').forEach(div => {
                div.style.display = 'none';
            });

            // Show selected destination places (supports radio + checkboxes)
            document.querySelectorAll('input[name="destination[]"]:checked, input[name="destination"]:checked').forEach(input => {
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
                case '3star': hotelPrice = 2500; break;
                case '4star': hotelPrice = 4000; break;
                case '5star': hotelPrice = 6500; break;
            }

            /* CAR PRICE */
            switch (car) {
            case 'small_car': carPrice = 2000; break;
            case 'sumo_gold': carPrice = 3000; break;
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