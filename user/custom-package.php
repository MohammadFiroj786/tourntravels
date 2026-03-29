<?php
include("../includes/session_check.php");
include("../includes/db.php");

// Read service type from URL
$service_type = $_GET['type'] ?? 'full';
if (!in_array($service_type, ['full', 'stay', 'sightseeing'])) {
    $service_type = 'full';
}

// Fetch sightseeing places from database
$sightseeing_places = [];
$query = "SELECT destination, place_name, description FROM sightseeing_places ORDER BY destination, place_name";
$result = mysqli_query($conn, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $sightseeing_places[$row['destination']][$row['place_name']] = $row['description'];
    }
}

// Extract places for easier access in templates
$darjeeling_places = $sightseeing_places['Darjeeling'] ?? [];
$sikkim_places = $sightseeing_places['Sikkim'] ?? [];

// Get page title and description based on service type
$page_config = [
    'full' => [
        'title' => 'Full Trip Package - Complete Experience',
        'description' => 'Complete experience with pickup, accommodation, sightseeing and drop',
        'icon' => '🚗'
    ],
    'stay' => [
        'title' => 'Stay + Sightseeing Package',
        'description' => 'Accommodation with guided tours and activities',
        'icon' => '🏨'
    ],
    'sightseeing' => [
        'title' => 'Only Sightseeing Package',
        'description' => 'Day tours for exploring scenic beauty',
        'icon' => '🏔️'
    ]
];

$current_config = $page_config[$service_type];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($current_config['title']); ?> - Hidden Hills Collective</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }

        .main-content {
            margin-top: 80px;
            padding-bottom: 50px;
        }

        .custom-box {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            overflow: hidden;
            border: none;
        }

        .form-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .form-header .icon {
            font-size: 48px;
            margin-bottom: 15px;
        }

        .form-header h2 {
            margin: 0;
            font-weight: 600;
        }

        .form-header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
        }

        .form-body {
            padding: 40px;
        }

        .form-section {
            margin-bottom: 30px;
            padding: 25px;
            border: 1px solid #e9ecef;
            border-radius: 12px;
            background: #f8f9fa;
        }

        .section-title {
            font-weight: 600;
            color: #495057;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }

        .section-title i {
            margin-right: 10px;
            color: #667eea;
        }

        .destination-card {
            border: 2px solid #dee2e6;
            border-radius: 12px;
            padding: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            height: 100%;
            background: white;
        }

        .destination-card:hover {
            border-color: #667eea;
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        .destination-card.selected {
            border-color: #667eea;
            background: #f8f9ff;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .destination-card input {
            display: none;
        }

        .destination-card h6 {
            margin: 0 0 5px 0;
            font-weight: 600;
        }

        .destination-card p {
            margin: 0;
            color: #6c757d;
            font-size: 14px;
        }

        .sightseeing-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }

        .sightseeing-item {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            background: white;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .sightseeing-item:hover {
            border-color: #667eea;
            background: #f8f9ff;
        }

        .sightseeing-item.selected {
            border-color: #667eea;
            background: #f8f9ff;
            box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.1);
        }

        .sightseeing-item input {
            margin-right: 12px;
        }

        .pricing-notice {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            border: 1px solid #ffc107;
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
        }

        .pricing-notice h6 {
            color: #856404;
            margin-bottom: 10px;
        }

        .pricing-notice p {
            color: #856404;
            margin: 0;
            font-size: 14px;
        }

        .submit-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 50px;
            padding: 15px 30px;
            font-weight: 600;
            font-size: 16px;
            color: white;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 20px;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }

        .submit-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .loading-spinner {
            display: none;
            margin-left: 10px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 8px;
        }

        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #dee2e6;
            padding: 12px 15px;
            font-size: 14px;
        }

        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .back-btn {
            position: absolute;
            top: 20px;
            left: 20px;
            background: rgba(255,255,255,0.9);
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #667eea;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            background: white;
            transform: scale(1.1);
        }

        @media (max-width: 768px) {
            .form-body {
                padding: 20px;
            }

            .form-header {
                padding: 20px;
            }

            .sightseeing-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<?php include("navbar_user.php"); ?>

<div class="container main-content">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-8">
            <div class="custom-box">

                <a href="../index.php#custom-packages" class="back-btn">
                    <i class="fas fa-arrow-left"></i>
                </a>

                <div class="form-header">
                    <div class="icon"><?php echo $current_config['icon']; ?></div>
                    <h2><?php echo htmlspecialchars($current_config['title']); ?></h2>
                    <p><?php echo htmlspecialchars($current_config['description']); ?></p>
                </div>

                <div class="form-body">
                    <form id="customPackageForm" action="submit-custom-package.php" method="POST">

                        <input type="hidden" name="service_type" value="<?php echo htmlspecialchars($service_type); ?>">
                        <input type="hidden" name="estimated_price" id="estimated_price" value="0">

                        <!-- PICKUP LOCATION -->
                        <?php if($service_type === 'full'): ?>
                        <div class="form-section">
                            <div class="section-title">
                                <i class="fas fa-map-marker-alt"></i>
                                Pickup Location
                            </div>
                            <select name="pickup_location" class="form-select" required>
                                <option value="">Choose pickup location</option>
                                <option value="NJP">NJP Junction</option>
                                <option value="Siliguri">Siliguri</option>
                            </select>
                        </div>
                        <?php endif; ?>

                        <!-- DESTINATION -->
                        <div class="form-section">
                            <div class="section-title">
                                <i class="fas fa-map"></i>
                                Choose Destinations (Select One or More)
                            </div>

                            <div class="row g-3">
                                <?php foreach(array_keys($sightseeing_places) as $destination): ?>
                                <div class="col-md-6">
                                    <label class="destination-card w-100">
                                        <input type="checkbox" name="destination[]"
                                               value="<?php echo htmlspecialchars($destination); ?>">
                                        <h6><?php echo htmlspecialchars($destination); ?></h6>
                                        <p>Select sightseeing</p>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- SIGHTSEEING -->
                        <div class="form-section">
                            <div class="section-title">
                                <i class="fas fa-binoculars"></i>
                                Select Sightseeing Places
                            </div>

                            <?php foreach($sightseeing_places as $destination => $places): ?>
                            <div id="<?php echo strtolower($destination); ?>-places" style="display:none;">
                                <h6 class="mb-3"><?php echo htmlspecialchars($destination); ?> Sightseeing</h6>

                                <div class="sightseeing-grid">
                                    <?php foreach($places as $place_name => $description): ?>
                                    <label class="sightseeing-item">
                                        <input type="checkbox"
                                               name="sightseeing_places[]"
                                               value="<?php echo htmlspecialchars($place_name); ?>">
                                        <div>
                                            <strong><?php echo htmlspecialchars($place_name); ?></strong><br>
                                            <small><?php echo htmlspecialchars($description); ?></small>
                                        </div>
                                    </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- TRIP DETAILS -->
                        <div class="form-section">
                            <div class="section-title">
                                <i class="fas fa-calendar-alt"></i>
                                Trip Details
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Travel Date</label>
                                    <input type="date" name="travel_date" class="form-control" required>
                                </div>

                                <?php if($service_type !== 'sightseeing'): ?>
                                <div class="col-md-6">
                                    <label class="form-label">Number of Days</label>
                                    <input id="days" type="number" name="days" class="form-control" required min="1">
                                </div>
                                <?php endif; ?>

                                <div class="col-md-6">
                                    <label class="form-label">Travelers</label>
                                    <input id="travelers" type="number" name="travelers" class="form-control" required min="1">
                                </div>
                            </div>
                        </div>

                        <!-- HOTEL TYPE -->
                        <?php if($service_type !== 'sightseeing'): ?>
                        <div class="form-section">
                            <div class="section-title">
                                <i class="fas fa-hotel"></i>
                                Accommodation Type
                            </div>

                            <select name="hotel_type" id="hotel_type" class="form-select" required>
                                <option value="">Choose hotel type</option>
                                <option value="Homestay">Homestay</option>
                                <option value="Budget Hotel">Budget Hotel</option>
                                <option value="Deluxe Hotel">Deluxe Hotel</option>
                                <option value="Luxury Hotel">Luxury Hotel</option>
                            </select>
                        </div>
                        <?php endif; ?>

                        <div id="estimateBox" class="alert alert-info mt-3">
                            Estimated price will appear here
                        </div>

                        <button type="submit" class="btn submit-btn" id="submitBtn">
                            ✨ Get Final Quote
                        </button>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Handle multiple destination selection
document.querySelectorAll('.destination-card').forEach(card=>{
    card.addEventListener('click', function(){
        const checkbox = this.querySelector('input');
        checkbox.checked = !checkbox.checked;
        this.classList.toggle('selected', checkbox.checked);
        updateVisibleSightseeingPlaces();
    });
});

// Update visible sightseeing places based on selected destinations
function updateVisibleSightseeingPlaces() {
    const selectedDestinations = Array.from(
        document.querySelectorAll('input[name="destination[]"]:checked')
    ).map(input => input.value);

    // Hide all sightseeing sections
    document.querySelectorAll('[id$="-places"]').forEach(el => {
        el.style.display = 'none';
    });

    // Show sightseeing sections for selected destinations
    selectedDestinations.forEach(destination => {
        const target = document.getElementById(destination.toLowerCase() + '-places');
        if(target) target.style.display = 'block';
    });

    // Validation: require at least one destination
    const submitBtn = document.getElementById('submitBtn');
    if(submitBtn) {
        submitBtn.disabled = selectedDestinations.length === 0;
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', updateVisibleSightseeingPlaces);

document.querySelectorAll('.sightseeing-item').forEach(item=>{
    item.addEventListener('click', function(){
        const checkbox = this.querySelector('input');
        checkbox.checked = !checkbox.checked;
        this.classList.toggle('selected', checkbox.checked);
    });
});

function calculateEstimate(){
    const travelers = Number(document.getElementById('travelers')?.value || 0);
    const days = Number(document.getElementById('days')?.value || 1);

    if(travelers < 1) return;

    let estimate = travelers * 999 * days;

    document.getElementById('estimateBox').innerHTML =
        "<strong>Estimated Starting Price: ₹"+estimate.toLocaleString('en-IN')+"</strong>";

    document.getElementById('estimated_price').value = estimate;
}

document.querySelectorAll('#travelers,#days')
.forEach(el=>{
    if(el){
        el.addEventListener('input',calculateEstimate);
        el.addEventListener('change',calculateEstimate);
    }
});

// Form validation - ensure at least one destination is selected
document.getElementById('customPackageForm')?.addEventListener('submit', function(e) {
    const selectedDestinations = document.querySelectorAll('input[name="destination[]"]:checked');
    if (selectedDestinations.length === 0) {
        e.preventDefault();
        alert('❌ Please select at least one destination!');
        return false;
    }
});
</script>

</body>
</html>