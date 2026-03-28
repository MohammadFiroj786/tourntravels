<?php
session_start();
include("../includes/db.php");

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin'){
    header("Location: ../login.php");
    exit();
}

$message = "";

if(!isset($_GET['id'])){
    header("Location: manage_sightseeing.php");
    exit();
}

$id = intval($_GET['id']);

// Get place data
$stmt = $conn->prepare("SELECT * FROM sightseeing_places WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$place = $result->fetch_assoc();
$stmt->close();

if(!$place){
    header("Location: manage_sightseeing.php");
    exit();
}

/* ================= UPDATE PLACE ================= */
if(isset($_POST['update_place'])){
    $destination = mysqli_real_escape_string($conn, $_POST['destination']);
    $place_name = mysqli_real_escape_string($conn, $_POST['place_name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $status = $_POST['status'];

    $image_name = $place['image']; // Keep existing image by default

    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0){
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if(in_array($_FILES['image']['type'], $allowed_types)){
            // Delete old image if exists
            if(!empty($place['image'])){
                $old_image_path = "../assets/images/sightseeing/" . $place['image'];
                if(file_exists($old_image_path)){
                    unlink($old_image_path);
                }
            }

            $image_name = time() . '_' . basename($_FILES['image']['name']);
            $target_path = "../assets/images/sightseeing/" . $image_name;

            if(move_uploaded_file($_FILES['image']['tmp_name'], $target_path)){
                // Image uploaded successfully
            } else {
                $message = "<div class='alert alert-danger'>Failed to upload image.</div>";
                $image_name = $place['image']; // Keep old image
            }
        } else {
            $message = "<div class='alert alert-danger'>Invalid image type. Only JPG, PNG, GIF, WEBP allowed.</div>";
        }
    }

    if(empty($message)){
        $stmt = $conn->prepare("UPDATE sightseeing_places SET destination = ?, place_name = ?, description = ?, image = ?, status = ? WHERE id = ?");
        $stmt->bind_param("sssssi", $destination, $place_name, $description, $image_name, $status, $id);
        if($stmt->execute()){
            $message = "<div class='alert alert-success'>Sightseeing place updated successfully!</div>";
            // Refresh place data
            $stmt = $conn->prepare("SELECT * FROM sightseeing_places WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $place = $result->fetch_assoc();
            $stmt->close();
        } else {
            $message = "<div class='alert alert-danger'>Error updating place: " . $stmt->error . "</div>";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Sightseeing Place - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<?php include("navbar_admin.php"); ?>

<div class="adminLayoutContent">
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <h2 class="text-center mb-4">✏️ Edit Sightseeing Place</h2>

                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">Edit Place Details</h5>
                    </div>
                    <div class="card-body">
                        <?php echo $message; ?>

                        <form method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Destination</label>
                                    <select name="destination" class="form-select" required>
                                        <option value="Darjeeling" <?php echo ($place['destination'] == 'Darjeeling') ? 'selected' : ''; ?>>Darjeeling</option>
                                        <option value="Sikkim" <?php echo ($place['destination'] == 'Sikkim') ? 'selected' : ''; ?>>Sikkim</option>
                                        <option value="Kalimpong" <?php echo ($place['destination'] == 'Kalimpong') ? 'selected' : ''; ?>>Kalimpong</option>
                                        <option value="Mirik" <?php echo ($place['destination'] == 'Mirik') ? 'selected' : ''; ?>>Mirik</option>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="active" <?php echo ($place['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                                        <option value="inactive" <?php echo ($place['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                    </select>
                                </div>

                                <div class="col-12 mb-3">
                                    <label class="form-label fw-bold">Place Name</label>
                                    <input type="text" name="place_name" class="form-control" value="<?php echo htmlspecialchars($place['place_name']); ?>" required>
                                </div>

                                <div class="col-12 mb-3">
                                    <label class="form-label fw-bold">Description</label>
                                    <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($place['description']); ?></textarea>
                                </div>

                                <div class="col-12 mb-3">
                                    <label class="form-label fw-bold">Current Image</label>
                                    <?php if($place['image']): ?>
                                        <div class="mb-2">
                                            <img src="../assets/images/sightseeing/<?php echo htmlspecialchars($place['image']); ?>"
                                                 alt="Current Image" class="img-thumbnail" style="width: 120px; height: 120px; object-fit: cover;">
                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted">No image uploaded</p>
                                    <?php endif; ?>
                                </div>

                                <div class="col-12 mb-3">
                                    <label class="form-label fw-bold">Change Image (Optional)</label>
                                    <input type="file" name="image" class="form-control" accept="image/*">
                                    <small class="text-muted">Leave empty to keep current image. JPG, PNG, GIF, WEBP only.</small>
                                </div>

                                <div class="col-12">
                                    <div class="d-flex gap-2 justify-content-end">
                                        <a href="manage_sightseeing.php" class="btn btn-secondary">
                                            <i class="fas fa-arrow-left"></i> Back to List
                                        </a>
                                        <button type="submit" name="update_place" class="btn btn-success">
                                            <i class="fas fa-save"></i> Update Place
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include("../footer.php"); ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>