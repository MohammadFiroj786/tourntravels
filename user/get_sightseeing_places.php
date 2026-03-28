<?php
include("../includes/db.php");

header('Content-Type: application/json');

if(isset($_GET['destination'])){
    $destination = mysqli_real_escape_string($conn, $_GET['destination']);

    $stmt = $conn->prepare("SELECT * FROM sightseeing_places WHERE destination = ? AND status = 'active' ORDER BY place_name");
    $stmt->bind_param("s", $destination);
    $stmt->execute();
    $result = $stmt->get_result();

    $places = [];
    while($row = $result->fetch_assoc()){
        $places[] = [
            'id' => $row['id'],
            'place_name' => $row['place_name'],
            'description' => $row['description'],
            'image' => $row['image']
        ];
    }

    echo json_encode($places);
    $stmt->close();
} else {
    echo json_encode(['error' => 'Destination not specified']);
}

$conn->close();
?>