<?php
session_start();
include("../includes/db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$request_id = isset($_GET['request_id']) ? (int)$_GET['request_id'] : 0;

// Get request details
$stmt = $conn->prepare("SELECT * FROM custom_package_requests WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $request_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$request = $result->fetch_assoc();
$stmt->close();

if (!$request) {
    header("Location: user-dashboard.php");
    exit();
}

// Decode sightseeing places
$sightseeing_places = json_decode($request['sightseeing_places'], true);
if (!is_array($sightseeing_places)) {
    $sightseeing_places = [];
}

// fallback for destination field naming
$destinations = $request['destinations'] ?? $request['destination'] ?? 'N/A';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank You - Hidden Hills Collective</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
    <style>
        .thank-you-container {
            min-height: 80vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .thank-you-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            padding: 3rem;
            text-align: center;
            max-width: 600px;
            width: 100%;
        }
        .success-icon {
            font-size: 4rem;
            color: #28a745;
            margin-bottom: 1rem;
        }
        .request-details {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
            margin: 2rem 0;
            text-align: left;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #e9ecef;
        }
        .detail-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        .service-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        .service-badge.full { background: #e3f2fd; color: #1976d2; }
        .service-badge.stay { background: #f3e5f5; color: #7b1fa2; }
        .service-badge.sightseeing { background: #e8f5e8; color: #388e3c; }
    </style>
</head>
<body>
    <?php include("navbar_user.php"); ?>

    <div class="thank-you-container">
        <div class="thank-you-card">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h1 class="h2 mb-3">Thank You for Your Request!</h1>
            <p class="text-muted mb-4">
                Your custom tour package request has been submitted successfully.
                Our travel expert will contact you within 24 hours to confirm your package and provide final pricing.
            </p>

            <div class="request-details">
                <h5 class="mb-3"><i class="fas fa-clipboard-list"></i> Request Details</h5>

                <div class="detail-row">
                    <strong>Request ID:</strong>
                    <span>#<?php echo htmlspecialchars($request['id']); ?></span>
                </div>

                <div class="detail-row">
                    <strong>Service Type:</strong>
                    <span class="service-badge <?php echo htmlspecialchars($request['service_type']); ?>">
                        <?php
                        $service_names = [
                            'full' => 'Full Trip Package',
                            'stay' => 'Stay + Sightseeing',
                            'sightseeing' => 'Sightseeing Only'
                        ];
                        echo htmlspecialchars($service_names[$request['service_type']] ?? $request['service_type']);
                        ?>
                    </span>
                </div>

                <?php if ($request['service_type'] === 'full' && !empty($request['pickup_location'])): ?>
                <div class="detail-row">
                    <strong>Pickup Location:</strong>
                    <span><?php echo htmlspecialchars($request['pickup_location']); ?></span>
                </div>
                <?php endif; ?>

                <div class="detail-row">
                    <strong>Destination:</strong>
                    <span><?php echo htmlspecialchars($destinations); ?></span>
                </div>

                <div class="detail-row">
                    <strong>Sightseeing Places:</strong>
                    <span><?php echo htmlspecialchars(implode(', ', $sightseeing_places)); ?></span>
                </div>

                <div class="detail-row">
                    <strong>Travel Date:</strong>
                    <span><?php echo htmlspecialchars(date('d M Y', strtotime($request['travel_date']))); ?></span>
                </div>

                <?php if ($request['service_type'] !== 'sightseeing'): ?>
                <div class="detail-row">
                    <strong>Duration:</strong>
                    <span><?php echo htmlspecialchars($request['days']); ?> Days</span>
                </div>
                <?php endif; ?>

                <div class="detail-row">
                    <strong>Travelers:</strong>
                    <span><?php echo htmlspecialchars($request['travelers']); ?> Person<?php echo $request['travelers'] > 1 ? 's' : ''; ?></span>
                </div>

                <div class="detail-row">
                    <strong>Vehicle Type:</strong>
                    <span><?php echo htmlspecialchars($request['car_type']); ?></span>
                </div>

                <?php if (!empty($request['hotel_type'])): ?>
                <div class="detail-row">
                    <strong>Hotel Type:</strong>
                    <span><?php echo htmlspecialchars($request['hotel_type']); ?></span>
                </div>
                <?php endif; ?>

                <div class="detail-row">
                    <strong>Status:</strong>
                    <span class="badge bg-warning"><?php echo htmlspecialchars($request['status']); ?></span>
                </div>

                <?php if (!empty($request['user_notes'])): ?>
                <div class="detail-row">
                    <strong>Your Notes:</strong>
                    <span><?php echo htmlspecialchars($request['user_notes']); ?></span>
                </div>
                <?php endif; ?>

                <div class="detail-row">
                    <strong>Submitted On:</strong>
                    <span><?php echo htmlspecialchars(date('d M Y, H:i', strtotime($request['created_at']))); ?></span>
                </div>
            </div>

            <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                <a href="my-bookings.php" class="btn btn-primary">
                    <i class="fas fa-list"></i> View My Bookings
                </a>
                <a href="user-dashboard.php" class="btn btn-outline-primary">
                    <i class="fas fa-home"></i> Back to Dashboard
                </a>
            </div>

            <div class="mt-4 text-center">
                <small class="text-muted">
                    <i class="fas fa-clock"></i> Expected response time: Within 24 hours
                </small>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>