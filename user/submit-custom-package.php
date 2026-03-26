<?php
session_start();
include("../includes/db.php");

if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* ===== INPUT SANITIZATION FUNCTION ===== */
function sanitize_input($data) {
    if (is_array($data)) {
        return array_map('sanitize_input', $data);
    }
    return trim(htmlspecialchars(strip_tags($data)));
}

/* ===== VALIDATION FUNCTION ===== */
function validate_required($value, $field_name) {
    if (empty($value)) {
        return "$field_name is required";
    }
    return null;
}

/* ===== GET AND VALIDATE FORM DATA ===== */
$errors = [];
$service_type = sanitize_input($_POST['service_type'] ?? '');
$pickup_location = sanitize_input($_POST['pickup_location'] ?? '');
$destination = sanitize_input($_POST['destination'] ?? '');
$sightseeing_places = isset($_POST['sightseeing_places']) ? $_POST['sightseeing_places'] : [];
$travel_date = sanitize_input($_POST['travel_date'] ?? '');
$days = isset($_POST['days']) ? (int)$_POST['days'] : 1;
$travelers = isset($_POST['travelers']) ? (int)$_POST['travelers'] : 1;
$hotel_type = sanitize_input($_POST['hotel_type'] ?? '');
$user_notes = sanitize_input($_POST['user_notes'] ?? '');

// Validate required fields
if ($error = validate_required($service_type, 'Service type')) $errors[] = $error;
if ($error = validate_required($destination, 'Destination')) $errors[] = $error;
if ($error = validate_required($travel_date, 'Travel date')) $errors[] = $error;
if ($error = validate_required($travelers, 'Number of travelers')) $errors[] = $error;

// Validate service type specific fields
if ($service_type === 'full' && empty($pickup_location)) {
    $errors[] = 'Pickup location is required for full trip package';
}

if ($service_type !== 'sightseeing' && empty($hotel_type)) {
    $errors[] = 'Hotel type is required for this package type';
}

if (empty($sightseeing_places)) {
    $errors[] = 'At least one sightseeing place must be selected';
}

// Validate travelers count
if ($travelers < 1 || $travelers > 50) {
    $errors[] = 'Number of travelers must be between 1 and 50';
}

// Validate days for non-sightseeing packages
if ($service_type !== 'sightseeing' && ($days < 1 || $days > 30)) {
    $errors[] = 'Number of days must be between 1 and 30';
}

// Validate travel date
$today = date('Y-m-d');
if ($travel_date < $today) {
    $errors[] = 'Travel date cannot be in the past';
}

if (!empty($errors)) {
    // Return to form with errors
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Validation Error</title>
        <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css' rel='stylesheet'>
    </head>
    <body>
        <div class='container mt-5'>
            <div class='row justify-content-center'>
                <div class='col-md-6'>
                    <div class='alert alert-danger'>
                        <h5><i class='fas fa-exclamation-triangle'></i> Please fix the following errors:</h5>
                        <ul class='mb-0'>";
    foreach ($errors as $error) {
        echo "<li>" . htmlspecialchars($error) . "</li>";
    }
    echo "          </ul>
                    </div>
                    <a href='javascript:history.back()' class='btn btn-primary'>Go Back</a>
                </div>
            </div>
        </div>
    </body>
    </html>";
    exit();
}

/* ===== CAR CAPACITY LOGIC ===== */
$car_type = '';
if ($travelers <= 4) {
    $car_type = 'Small Car';
} elseif ($travelers <= 7) {
    $car_type = 'SUV';
} elseif ($travelers <= 12) {
    $car_type = 'Tempo Traveller';
} else {
    $vehicles_needed = ceil($travelers / 12);
    $car_type = 'Multiple Vehicles (' . $vehicles_needed . ' vehicles)';
}

/* ===== PREPARE DATA FOR STORAGE ===== */
// Convert sightseeing places to JSON
$sightseeing_json = json_encode(array_map('sanitize_input', $sightseeing_places));

// Set default values for optional fields
if ($service_type === 'sightseeing') {
    $days = 1; // Day trip
    $hotel_type = ''; // No hotel for sightseeing only
}

/* ===== INSERT DATA INTO DATABASE ===== */
$stmt = $conn->prepare("INSERT INTO custom_package_requests
    (user_id, service_type, pickup_location, destinations, sightseeing_places, travel_date, days, travelers, hotel_type, user_notes, car_type, status, created_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', NOW())");

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param(
    "isssssiisss",
    $user_id,
    $service_type,
    $pickup_location,
    $destination,
    $sightseeing_json,
    $travel_date,
    $days,
    $travelers,
    $hotel_type,
    $user_notes,
    $car_type
);

if ($stmt->execute()) {
    $request_id = $conn->insert_id;
    $stmt->close();

    // Success - redirect to thank you page
    header("Location: thank-you.php?request_id=" . $request_id);
    exit();
} else {
    die("Error saving request: " . $stmt->error);
}
?>