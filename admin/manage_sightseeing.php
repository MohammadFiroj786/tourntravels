<?php
include("../includes/session_check.php");
include("../includes/db.php");

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$message = "";

/* ================= ADD SIGHTSEEING PLACE ================= */
if (isset($_POST['add_place'])) {
    $destination = mysqli_real_escape_string($conn, $_POST['destination']);
    $place_name = mysqli_real_escape_string($conn, $_POST['place_name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $status = $_POST['status'];
    $is_offbeat = isset($_POST['is_offbeat']) ? 1 : 0;

    $image_name = "";
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (in_array($_FILES['image']['type'], $allowed_types)) {
            $image_name = time() . '_' . basename($_FILES['image']['name']);
            $target_path = "../assets/images/sightseeing/" . $image_name;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                // Image uploaded successfully
            } else {
                $message = "<div class='alert alert-danger'>Failed to upload image.</div>";
                $image_name = "";
            }
        } else {
            $message = "<div class='alert alert-danger'>Invalid image type. Only JPG, PNG, GIF, WEBP allowed.</div>";
        }
    }

    if (empty($message)) {
        $stmt = $conn->prepare("INSERT INTO sightseeing_places (destination, place_name, description, image, status, is_offbeat) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssi", $destination, $place_name, $description, $image_name, $status, $is_offbeat);
        if ($stmt->execute()) {
            $message = "<div class='alert alert-success'>Sightseeing place added successfully!</div>";
        } else {
            $message = "<div class='alert alert-danger'>Error adding place: " . $stmt->error . "</div>";
        }
        $stmt->close();
    }
}

/* ================= DELETE PLACE ================= */
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    // Get image filename to delete
    $stmt = $conn->prepare("SELECT image FROM sightseeing_places WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $place = $result->fetch_assoc();
    $stmt->close();

    if ($place && !empty($place['image'])) {
        $image_path = "../assets/images/sightseeing/" . $place['image'];
        if(file_exists($image_path)){
            unlink($image_path);
        }
    }

    $stmt = $conn->prepare("DELETE FROM sightseeing_places WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $message = "<div class='alert alert-success'>Place deleted successfully!</div>";
    } else {
        $message = "<div class='alert alert-danger'>Error deleting place.</div>";
    }
    $stmt->close();
}

/* ================= TOGGLE STATUS ================= */
if(isset($_GET['toggle'])){
    $id = intval($_GET['toggle']);
    $stmt = $conn->prepare("UPDATE sightseeing_places SET status = CASE WHEN status = 'active' THEN 'inactive' ELSE 'active' END WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: manage_sightseeing.php");
    exit();
}

// Get all places
$places = $conn->query("SELECT * FROM sightseeing_places ORDER BY destination, place_name");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Sightseeing Places - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<?php include("navbar_admin.php"); ?>

<div class="adminLayoutContent">
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <h2 class="text-center mb-4">🏔️ Manage Sightseeing Places</h2>

                <?php if($message): ?>
                <div class="row justify-content-center mb-4">
                    <div class="col-md-8">
                        <?php echo $message; ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="row">
                    <!-- Add New Place Form -->
                    <div class="col-lg-4 mb-4">
                        <div class="card shadow">
                            <div class="card-header bg-primary text-white">
                                <h5 class="card-title mb-0">➕ Add New Sightseeing Place</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" enctype="multipart/form-data">
                                    <div class="row">
                                        <div class="col-12 mb-3">
                                            <label class="form-label fw-bold">Destination</label>
                                            <select name="destination" class="form-select" required>
                                                <option value="">Select Destination</option>
                                                <option value="Darjeeling">Darjeeling</option>
                                                <option value="Sikkim">Sikkim</option>
                                                <option value="Kalimpong">Kalimpong</option>
                                                <option value="Mirik">Mirik</option>
                                            </select>
                                        </div>

                                        <div class="col-12 mb-3">
                                            <label class="form-label fw-bold">Place Name</label>
                                            <input type="text" name="place_name" class="form-control" required>
                                        </div>

                                        <div class="col-12 mb-3">
                                            <label class="form-label fw-bold">Description</label>
                                            <textarea name="description" class="form-control" rows="3"></textarea>
                                        </div>

                                        <div class="col-12 mb-3">
                                            <label class="form-label fw-bold">Image</label>
                                            <input type="file" name="image" class="form-control" accept="image/*">
                                            <small class="text-muted">Optional. JPG, PNG, GIF, WEBP only.</small>
                                        </div>

                                        <div class="col-12 mb-3">
                                            <label class="form-label fw-bold">Status</label>
                                            <select name="status" class="form-select">
                                                <option value="active">Active</option>
                                                <option value="inactive">Inactive</option>
                                            </select>
                                        </div>

                                        <div class="col-12 mb-3">
                                            <label class="form-label fw-bold">Offbeat Experience</label>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="is_offbeat" value="1" id="is_offbeat_add">
                                                <label class="form-check-label" for="is_offbeat_add">Mark as Offbeat Experience</label>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <button type="submit" name="add_place" class="btn btn-success w-100">
                                                <i class="fas fa-plus"></i> Add Place
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Places List -->
                    <div class="col-lg-8">
                        <div class="card shadow">
                            <div class="card-header bg-dark text-white">
                                <h5 class="card-title mb-0">📋 All Sightseeing Places</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped table-hover">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>Destination</th>
                                                <th>Place Name</th>
                                                <th>Image</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while($place = $places->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($place['destination']); ?></td>
                                                <td><?php echo htmlspecialchars($place['place_name']); ?></td>
                                                <td>
                                                    <?php if($place['image']): ?>
                                                        <img src="../assets/images/sightseeing/<?php echo htmlspecialchars($place['image']); ?>"
                                                             alt="Image" class="img-thumbnail" style="width: 60px; height: 60px; object-fit: cover;">
                                                    <?php else: ?>
                                                        <span class="text-muted">No image</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge <?php echo $place['status'] == 'active' ? 'bg-success' : 'bg-danger'; ?>">
                                                        <?php echo ucfirst($place['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="?toggle=<?php echo $place['id']; ?>" class="btn btn-sm btn-warning" title="Toggle Status">
                                                            <i class="fas fa-toggle-on"></i>
                                                        </a>
                                                        <a href="edit_sightseeing.php?id=<?php echo $place['id']; ?>" class="btn btn-sm btn-primary" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="?delete=<?php echo $place['id']; ?>" class="btn btn-sm btn-danger"
                                                           onclick="return confirm('Are you sure you want to delete this place?')" title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>