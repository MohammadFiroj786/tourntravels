<?php
include("../includes/session_check.php");
include("../includes/db.php");

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

/* ===== GET FORM DATA ===== */
$errors = [];

$service_type = sanitize_input($_POST['service_type'] ?? '');
$pickup_location = sanitize_input($_POST['pickup_location'] ?? '');

// Handle destination input (can be array for regular, single value for offbeat)
$destinations_input = $_POST['destination'] ?? [];
if (is_array($destinations_input)) {
    $destinations_array = array_map('sanitize_input', $destinations_input);
} else {
    $destinations_array = [sanitize_input($destinations_input)];
}
$destinations = implode(', ', array_filter($destinations_array));

$sightseeing_places = $_POST['sightseeing_places'] ?? [];

$travel_date = sanitize_input($_POST['travel_date'] ?? '');
$days = isset($_POST['days']) ? (int)$_POST['days'] : 1;
$travelers = isset($_POST['travelers']) ? (int)$_POST['travelers'] : 1;

$hotel_type = sanitize_input($_POST['hotel_type'] ?? '');
$user_notes = sanitize_input($_POST['user_notes'] ?? '');

$estimated_price = isset($_POST['estimated_price']) ? (int)$_POST['estimated_price'] : 0;


/* ===== VALIDATION ===== */
if ($error = validate_required($service_type, 'Service type')) $errors[] = $error;
if (empty($destinations)) {
    $errors[] = 'At least one destination must be selected';
}
if ($error = validate_required($travel_date, 'Travel date')) $errors[] = $error;
if ($error = validate_required($travelers, 'Number of travelers')) $errors[] = $error;

if ($service_type === 'full' && empty($pickup_location)) {
    $errors[] = 'Pickup location is required for full trip package';
}

if ($service_type !== 'sightseeing' && empty($hotel_type)) {
    $errors[] = 'Hotel type is required for this package type';
}

if (empty($sightseeing_places)) {
    $errors[] = 'At least one sightseeing place must be selected';
}

if ($travelers < 1 || $travelers > 50) {
    $errors[] = 'Number of travelers must be between 1 and 50';
}

if ($service_type !== 'sightseeing' && ($days < 1 || $days > 30)) {
    $errors[] = 'Number of days must be between 1 and 30';
}

$today = date('Y-m-d');
if ($travel_date < $today) {
    $errors[] = 'Travel date cannot be in the past';
}

if (!empty($errors)) {
    echo "<h3>Errors:</h3><ul>";
    foreach ($errors as $error) {
        echo "<li>$error</li>";
    }
    echo "</ul><a href='javascript:history.back()'>Go Back</a>";
    exit();
}


$car_type = sanitize_input($_POST['car_type'] ?? '');

/* ===== CAR CAPACITY LOGIC (FALLBACK IF NOT PROVIDED) ===== */
if (empty($car_type)) {
    if ($travelers <= 4) {
        $car_type = 'Small Car';
    } elseif ($travelers <= 7) {
        $car_type = 'SUV';
    } elseif ($travelers <= 12) {
        $car_type = 'Tempo Traveller';
    } else {
        $vehicles_needed = ceil($travelers / 12);
        $car_type = "Multiple Vehicles ($vehicles_needed)";
    }
}


/* ===== PREPARE DATA ===== */
$sightseeing_json = json_encode(array_map('sanitize_input', $sightseeing_places));

if ($service_type === 'sightseeing') {
    $days = 1;
    $hotel_type = '';
}

/* ===== DESTINATION COLUMN HANDLING ===== */
$destination_column = 'destinations';
$has_destinations = $conn->query("SHOW COLUMNS FROM custom_package_requests LIKE 'destinations'");
if (!$has_destinations || $has_destinations->num_rows === 0) {
    $destination_column = 'destination';
}

/* ===== INSERT (WITH COLUMN FALLBACK) ===== */
$stmt = $conn->prepare("INSERT INTO custom_package_requests
    (user_id, service_type, pickup_location, $destination_column, sightseeing_places, hotel_type, car_type, travel_date, days, travelers, price, status)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')");

$stmt->bind_param(
    "isssssssiis",
    $user_id,
    $service_type,
    $pickup_location,
    $destinations,
    $sightseeing_json,
    $hotel_type,
    $car_type,
    $travel_date,
    $days,
    $travelers,
    $estimated_price
);

if ($stmt->execute()) {
    $request_id = $conn->insert_id;
    header("Location: thank-you.php?request_id=" . $request_id);
    exit();
} else {
    die("Error saving request: " . $stmt->error);
}
?>