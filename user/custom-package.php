<?php
session_start();
include("../includes/db.php");

if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php");
    exit();
}

// Read service type from URL
$service_type = $_GET['type'] ?? 'full';
if(!in_array($service_type, ['full', 'stay', 'sightseeing'])) {
    $service_type = 'full';
}

// Fetch sightseeing places from database
$sightseeing_places = [];
$stmt = $conn->prepare("SELECT * FROM sightseeing_places WHERE status = 'active' ORDER BY destination, place_name");
$stmt->execute();
$result = $stmt->get_result();
while($row = $result->fetch_assoc()){
    $destination = $row['destination'];
    if(!isset($sightseeing_places[$destination])){
        $sightseeing_places[$destination] = [];
    }
    $sightseeing_places[$destination][] = $row;
}
$stmt->close();

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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Destination selection handling
            const destinationRadios = document.querySelectorAll('input[name="destination"]');
            const sightseeingSections = document.querySelectorAll('[id$="-places"]');

            destinationRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    const selectedDestination = this.value.toLowerCase();

                    // Hide all sightseeing sections
                    sightseeingSections.forEach(section => {
                        section.style.display = 'none';
                    });

                    // Show selected destination's places
                    const targetSection = document.getElementById(selectedDestination + '-places');
                    if (targetSection) {
                        targetSection.style.display = 'block';
                    }
                });
            });

            // Dynamic sightseeing places loading (AJAX)
            function loadSightseeingPlaces(destination) {
                const sectionId = destination.toLowerCase() + '-places';
                const section = document.getElementById(sectionId);

                if (!section) return;

                // Show loading
                section.innerHTML = '<h6 class="mb-3">' + destination + ' Sightseeing</h6><p>Loading places...</p>';

                fetch('get_sightseeing_places.php?destination=' + encodeURIComponent(destination))
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            section.innerHTML = '<h6 class="mb-3">' + destination + ' Sightseeing</h6><p class="text-danger">Error loading places</p>';
                            return;
                        }

                        let html = '<h6 class="mb-3">' + destination + ' Sightseeing</h6>';
                        html += '<div class="sightseeing-grid">';

                        data.forEach(place => {
                            html += `
                                <label class="sightseeing-item">
                                    <input type="checkbox" name="sightseeing_places[]" value="${place.place_name}">
                                    <div>
                                        <strong>${place.place_name}</strong><br>
                                        <small>${place.description}</small>
                                        ${place.image ? `<br><img src="../assets/images/sightseeing/${place.image}" alt="${place.place_name}" style="width: 60px; height: 40px; object-fit: cover; margin-top: 5px; border-radius: 4px;">` : ''}
                                    </div>
                                </label>
                            `;
                        });

                        html += '</div>';
                        section.innerHTML = html;
                    })
                    .catch(error => {
                        section.innerHTML = '<h6 class="mb-3">' + destination + ' Sightseeing</h6><p class="text-danger">Error loading places</p>';
                        console.error('Error:', error);
                    });
            }

            // Load places when destination is selected
            destinationRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    loadSightseeingPlaces(this.value);
                });
            });

            // Price calculation functions
            function calculateEstimate() {
                const travelersInput = document.getElementById('travelers');
                const daysInput = document.getElementById('days');
                const hotelTypeSelect = document.querySelector('select[name="hotel_type"]');
                const sightseeingChecked = document.querySelectorAll('input[name="sightseeing_places[]"]:checked');

                let basePrice = 0;
                let travelers = travelersInput ? parseInt(travelersInput.value) || 0 : 0;
                let days = daysInput ? parseInt(daysInput.value) || 0 : 0;
                let hotelMultiplier = 1;

                // Hotel type pricing
                if (hotelTypeSelect) {
                    switch(hotelTypeSelect.value) {
                        case 'Budget': hotelMultiplier = 1; break;
                        case 'Standard': hotelMultiplier = 1.5; break;
                        case 'Deluxe': hotelMultiplier = 2; break;
                        case 'Luxury': hotelMultiplier = 3; break;
                    }
                }

                // Base pricing per destination
                const destination = document.querySelector('input[name="destination"]:checked');
                if (destination) {
                    switch(destination.value) {
                        case 'Darjeeling': basePrice = 2500; break;
                        case 'Sikkim': basePrice = 3500; break;
                        default: basePrice = 2000;
                    }
                }

                // Sightseeing places pricing
                const sightseeingPrice = sightseeingChecked.length * 500;

                // Calculate total
                let estimate = (basePrice * travelers * days * hotelMultiplier) + sightseeingPrice;

                const estimateInput = document.getElementById('estimated_price');
                if (estimateInput) {
                    estimateInput.value = estimate;
                }
            }

            [document.getElementById('travelers'), document.getElementById('days'), document.querySelector('select[name="hotel_type"]')].forEach(el => {
                if (el) {
                    el.addEventListener('change', calculateEstimate);
                    el.addEventListener('input', calculateEstimate);
                }
            });

            // Recalculate when sightseeing places change
            document.addEventListener('change', function(e) {
                if (e.target.name === 'sightseeing_places[]') {
                    calculateEstimate();
                }
            });

            // Initial estimation
            calculateEstimate();

            // Form validation and submission
            const form = document.getElementById('customPackageForm');
            const submitBtn = document.getElementById('submitBtn');
            const loadingSpinner = document.querySelector('.loading-spinner');

            form.addEventListener('submit', function(e) {
                const destination = document.querySelector('input[name="destination"]:checked');
                if (!destination) {
                    e.preventDefault();
                    alert('Please select a destination');
                    return;
                }

                const sightseeingChecked = document.querySelectorAll('input[name="sightseeing_places[]"]:checked');
                if (sightseeingChecked.length === 0) {
                    e.preventDefault();
                    alert('Please select at least one sightseeing place');
                    return;
                }

                if (submitBtn) {
                    submitBtn.disabled = true;
                    if (loadingSpinner) loadingSpinner.style.display = 'inline-block';
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                }
            });

            const dateInput = document.querySelector('input[name="travel_date"]');
            if (dateInput) {
                const today = new Date().toISOString().split('T')[0];
                dateInput.setAttribute('min', today);
            }
        });
    </script>
</body>
</html>
                            
                            <!-- TRIP DETAILS -->
                            <div class="form-section">
                                <div class="section-title">
                                    <i class="fas fa-calendar-alt"></i>
                                    Trip Details
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Travel Date</label>
                                        <input type="date" name="travel_date" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                                    </div>

                                    <?php if($service_type !== 'sightseeing'): ?>
                                    <div class="col-md-6">
                                        <label class="form-label">Number of Days</label>
                                        <input id="days" type="number" name="days" class="form-control" required min="1" max="30" placeholder="e.g., 3">
                                    </div>
                                    <?php endif; ?>

                                    <div class="col-md-6">
                                        <label class="form-label">Number of Travelers</label>
                                        <input id="travelers" type="number" name="travelers" class="form-control" required min="1" max="50" placeholder="e.g., 4">
                                    </div>
                                </div>
                            </div>

                            <!-- HOTEL TYPE (Not for sightseeing only) -->
                            <?php if($service_type !== 'sightseeing'): ?>
                            <div class="form-section">
                                <div class="section-title">
                                    <i class="fas fa-hotel"></i>
                                    Accommodation Type
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Select Hotel Category</label>
                                    <select id="hotel_type" name="hotel_type" class="form-select" required>
                                        <option value="">Choose hotel type</option>
                                        <option value="Homestay">Homestay (Not Confirmed)</option>
                                        <option value="Budget Hotel">Budget Hotel (Not Confirmed)</option>
                                        <option value="Deluxe Hotel">Deluxe Hotel (Not Confirmed)</option>
                                        <option value="Luxury Hotel">Luxury Hotel (Not Confirmed)</option>
                                    </select>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- SPECIAL NOTES -->
                            <div class="form-section">
                                <div class="section-title">
                                    <i class="fas fa-sticky-note"></i>
                                    Special Requests
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Tell us about your preferences (optional)</label>
                                    <textarea name="user_notes" class="form-control" rows="4" placeholder="Dietary requirements, accessibility needs, special interests, etc."></textarea>
                                </div>
                            </div>

                            <!-- PRICING NOTICE -->
                            <div class="pricing-notice">
                                <h6><i class="fas fa-info-circle"></i> Pricing Information</h6>
                                <p><strong>Starting from ₹999 per person</strong><br>
                                Final price depends on number of travelers, vehicle type, and season.<br>
                                Our team will provide a detailed quote after reviewing your requirements.</p>
                            </div>

                            <div id="estimateBox" class="alert alert-info mt-3">
                                Estimated price will appear here
                            </div>

                            <!-- SUBMIT BUTTON -->
                            <button type="submit" class="btn submit-btn" id="submitBtn">
                                <i class="fas fa-paper-plane"></i>
                                ✨ Get Final Quote
                                <div class="spinner-border spinner-border-sm loading-spinner" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Handle destination selection and sightseeing visibility
        const destinationRadios = document.querySelectorAll('input[name="destination"]');
        const darjeelingPlaces = document.getElementById('darjeeling-places');
        const sikkimPlaces = document.getElementById('sikkim-places');

        function updateSightseeingVisibility() {
            const selectedDestination = document.querySelector('input[name="destination"]:checked');
            if (!selectedDestination) {
                if (darjeelingPlaces) darjeelingPlaces.style.display = 'none';
                if (sikkimPlaces) sikkimPlaces.style.display = 'none';
                return;
            }

            const destination = selectedDestination.value;
            if (destination === 'Darjeeling') {
                if (darjeelingPlaces) darjeelingPlaces.style.display = 'block';
                if (sikkimPlaces) sikkimPlaces.style.display = 'none';
            } else if (destination === 'Sikkim') {
                if (darjeelingPlaces) darjeelingPlaces.style.display = 'none';
                if (sikkimPlaces) sikkimPlaces.style.display = 'block';
            }
        }

        destinationRadios.forEach(radio => {
            radio.addEventListener('change', updateSightseeingVisibility);
        });

        // Handle destination card selection
        const destinationCards = document.querySelectorAll('.destination-card');
        destinationCards.forEach(card => {
            card.addEventListener('click', function() {
                const radio = this.querySelector('input[type="radio"]');
                if (!radio) return;
                radio.checked = true;
                updateSightseeingVisibility();

                destinationCards.forEach(c => c.classList.remove('selected'));
                this.classList.add('selected');
            });
        });

        // Handle sightseeing item selection
        const sightseeingItems = document.querySelectorAll('.sightseeing-item');
        sightseeingItems.forEach(item => {
            item.addEventListener('click', function() {
                const checkbox = this.querySelector('input[type="checkbox"]');
                if (!checkbox) return;
                checkbox.checked = !checkbox.checked;
                this.classList.toggle('selected', checkbox.checked);
            });
        });

        // Estimate logic
        const serviceType = '<?php echo htmlspecialchars($service_type); ?>';
        const estimateBox = document.getElementById('estimateBox');
        const travelersInput = document.getElementById('travelers');
        const daysInput = document.getElementById('days');
        const hotelTypeSelect = document.getElementById('hotel_type');

        function calculateEstimate() {
            const travelers = Number(travelersInput?.value) || 0;
            const days = Number(daysInput?.value) || 0;
            const hotelType = hotelTypeSelect?.value || '';

            if (travelers < 1 || (serviceType !== 'sightseeing' && days < 1)) {
                estimateBox.innerHTML = 'Estimated price will appear here';
                return;
            }

            const sightseeingCost = travelers * 999;

            const stayRates = {
                'Homestay': 1200,
                'Budget Hotel': 1800,
                'Deluxe Hotel': 2500,
                'Luxury Hotel': 4000
            };

            let stayCost = 0;
            if (serviceType !== 'sightseeing') {
                if (hotelType && stayRates[hotelType] !== undefined) {
                    stayCost = days * stayRates[hotelType];
                } else {
                    // default to 0 if hotel type not selected
                    stayCost = 0;
                }
            }

            let carCost = 0;
            if (serviceType === 'full') {
                if (travelers <= 4) carCost = 2500;
                else if (travelers <= 7) carCost = 3500;
                else if (travelers <= 12) carCost = 5000;
                else carCost = 7000;
            }

            let estimate = 0;
            if (serviceType === 'full') {
                estimate = sightseeingCost + stayCost + carCost;
            } else if (serviceType === 'stay') {
                estimate = sightseeingCost + stayCost;
            } else if (serviceType === 'sightseeing') {
                estimate = sightseeingCost;
            }

            estimateBox.innerHTML = '<strong>Estimated Starting Price: ₹' + estimate.toLocaleString('en-IN') + '</strong><br>' +
                '<small>Homestay subject to availability<br>Final price may vary based on season and vehicle</small>';

            const estimateInput = document.getElementById('estimated_price');
            if (estimateInput) {
                estimateInput.value = estimate;
            }
        }

        [travelersInput, daysInput, hotelTypeSelect].forEach(el => {
            if (el) {
                el.addEventListener('change', calculateEstimate);
                el.addEventListener('input', calculateEstimate);
            }
        });

        // Initial estimation
        calculateEstimate();

        // Form validation and submission
        const form = document.getElementById('customPackageForm');
        const submitBtn = document.getElementById('submitBtn');
        const loadingSpinner = document.querySelector('.loading-spinner');

        form.addEventListener('submit', function(e) {
            submitBtn.disabled = true;
            loadingSpinner.style.display = 'inline-block';
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

            const destination = document.querySelector('input[name="destination"]:checked');
            if (!destination) {
                e.preventDefault();
                alert('Please select a destination');
                resetSubmitButton();
                return;
            }

            const sightseeingChecked = document.querySelectorAll('input[name="sightseeing_places[]"]:checked');
            if (sightseeingChecked.length === 0) {
                e.preventDefault();
                alert('Please select at least one sightseeing place');
                resetSubmitButton();
                return;
            }
        });

        function resetSubmitButton() {
            submitBtn.disabled = false;
            loadingSpinner.style.display = 'none';
            submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> ✨ Get Final Quote';
        }

        const dateInput = document.querySelector('input[name="travel_date"]');
        if (dateInput) {
            const today = new Date().toISOString().split('T')[0];
            dateInput.setAttribute('min', today);
        }
    </script>
</body>
</html>