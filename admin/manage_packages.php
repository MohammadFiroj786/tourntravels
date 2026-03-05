<?php
session_start();
include("../includes/db.php");

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin'){
    header("Location: ../login.php");
    exit();
}

/* ================= DELETE SINGLE IMAGE ================= */

if(isset($_GET['delete_img'])){

$id = intval($_GET['delete_img']);
$img = $_GET['img'];

$data = $conn->query("SELECT image FROM packages WHERE id=$id");
$row = $data->fetch_assoc();

$imgs = explode(",", $row['image']);
$imgs = array_diff($imgs, [$img]);

$new = implode(",", $imgs);

$conn->query("UPDATE packages SET image='$new' WHERE id=$id");

if(file_exists("../uploads/".$img)){
unlink("../uploads/".$img);
}

header("Location: manage_packages.php");
exit();
}


/* ================= ADD PACKAGE ================= */

if(isset($_POST['add_package'])){

$title = $_POST['title'];
$duration = $_POST['duration'];
$price = $_POST['price'];
$discount = $_POST['discount'];
$seats = $_POST['seats'];
$description = $_POST['description'];
$status = $_POST['status'];

$final_price = $price - ($price * $discount / 100);

$images = [];

if(!empty($_FILES['image']['name'][0])){

foreach($_FILES['image']['name'] as $key=>$img){

$name = time().'_'.$img;

move_uploaded_file(
$_FILES['image']['tmp_name'][$key],
"../uploads/".$name
);

$images[] = $name;
}

}

$image = implode(",", $images);

$stmt = $conn->prepare("INSERT INTO packages (title,duration,price,description,image,created_at,discount,final_price,seats,status) VALUES (?,?,?,?,?,NOW(),?,?,?,?)");

$stmt->bind_param("ssdssddis",
$title,
$duration,
$price,
$description,
$image,
$discount,
$final_price,
$seats,
$status
);

$stmt->execute();

}


/* ================= UPDATE PACKAGE ================= */

if(isset($_POST['update_package'])){

$id = $_POST['id'];

$title = $_POST['title'];
$duration = $_POST['duration'];
$price = $_POST['price'];
$discount = $_POST['discount'];
$seats = $_POST['seats'];
$description = $_POST['description'];
$status = $_POST['status'];

$final_price = $price - ($price * $discount / 100);

/* get old images */

$data = $conn->query("SELECT image FROM packages WHERE id=$id");
$row = $data->fetch_assoc();

$old_images = [];

if(!empty($row['image'])){
$old_images = explode(",", $row['image']);
}

/* upload new */

$new_images = [];

if(!empty($_FILES['image']['name'][0])){

foreach($_FILES['image']['name'] as $key=>$img){

$name = time().'_'.$img;

move_uploaded_file(
$_FILES['image']['tmp_name'][$key],
"../uploads/".$name
);

$new_images[] = $name;

}

}

$all = array_merge($old_images,$new_images);

$image = implode(",",$all);

$stmt = $conn->prepare("UPDATE packages SET title=?,duration=?,price=?,description=?,image=?,discount=?,final_price=?,seats=?,status=? WHERE id=?");

$stmt->bind_param("ssdssddisi",
$title,
$duration,
$price,
$description,
$image,
$discount,
$final_price,
$seats,
$status,
$id
);

$stmt->execute();

}


/* ================= DELETE PACKAGE ================= */

if(isset($_GET['delete'])){

$id = $_GET['delete'];

$conn->query("DELETE FROM packages WHERE id=$id");

}


/* ================= FETCH ================= */

$packages = $conn->query("SELECT * FROM packages ORDER BY id DESC");

?>

<!DOCTYPE html>
<html>
<head>

<title>Manage Packages</title>

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
background:#f4f7fc;
font-family:Poppins;
}

.header-gradient{
background:linear-gradient(135deg,#667eea,#764ba2);
color:white;
padding:25px;
border-radius:20px;
}

.card{
border-radius:20px;
box-shadow:0 10px 30px rgba(0,0,0,0.08);
}

.drop-zone{
border:2px dashed #0d6efd;
border-radius:15px;
padding:25px;
text-align:center;
cursor:pointer;
background:#f8fbff;
transition:.3s;
}

.drop-zone:hover{
background:#e9f2ff;
}

.drop-zone input{
display:none;
}

.preview img{
max-width:90px;
margin:5px;
border-radius:10px;
position:relative;
}

.remove-btn{
position:absolute;
top:-8px;
right:-8px;
background:red;
color:white;
border-radius:50%;
width:20px;
height:20px;
text-align:center;
font-size:12px;
cursor:pointer;
}

.table thead{
background:#1e1e2f;
color:white;
}

</style>

</head>

<body class="p-4">

<div class="header-gradient mb-4 d-flex justify-content-between align-items-center">
<h3>📦 Manage Packages</h3>
<button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#addModal">+ Add Package</button>
</div>

<div class="card p-4">

<table class="table table-hover align-middle">

<thead>

<tr>

<th>Image</th>
<th>Title</th>
<th>Price</th>
<th>Discount</th>
<th>Seats</th>
<th>Status</th>
<th>Action</th>

</tr>

</thead>

<tbody>

<?php while($row=$packages->fetch_assoc()){ ?>

<tr>

<td>

<?php

$imgs = explode(",", $row['image']);

foreach($imgs as $img){

if($img!=''){

echo '<img src="../uploads/'.$img.'" width="60" class="rounded me-1">';

}

}

?>

</td>

<td><?php echo $row['title']; ?></td>

<td>₹<?php echo $row['price']; ?></td>

<td><?php echo $row['discount']; ?>%</td>

<td><?php echo $row['seats']; ?></td>

<td><?php echo $row['status']; ?></td>

<td>

<button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#edit<?php echo $row['id']; ?>">Edit</button>

<a href="?delete=<?php echo $row['id']; ?>" onclick="return confirm('Delete package?')" class="btn btn-danger btn-sm">Delete</a>

</td>

</tr>

<!-- EDIT MODAL -->

<div class="modal fade" id="edit<?php echo $row['id']; ?>">

<div class="modal-dialog modal-lg">

<div class="modal-content p-4">

<form method="POST" enctype="multipart/form-data">

<input type="hidden" name="id" value="<?php echo $row['id']; ?>">

<h5>Edit Package</h5>

<input type="text" name="title" value="<?php echo $row['title']; ?>" class="form-control mb-2">

<input type="text" name="duration" value="<?php echo $row['duration']; ?>" class="form-control mb-2">

<input type="number" name="price" value="<?php echo $row['price']; ?>" class="form-control mb-2">

<input type="number" name="discount" value="<?php echo $row['discount']; ?>" class="form-control mb-2">

<input type="number" name="seats" value="<?php echo $row['seats']; ?>" class="form-control mb-2">

<textarea name="description" class="form-control mb-2"><?php echo $row['description']; ?></textarea>

<select name="status" class="form-select mb-2">

<option <?php if($row['status']=="Active") echo "selected"; ?>>Active</option>

<option <?php if($row['status']=="Inactive") echo "selected"; ?>>Inactive</option>

</select>

<!-- EXISTING IMAGES -->

<div class="mb-3">

<?php

foreach($imgs as $img){

if($img!=''){

?>

<div style="display:inline-block;position:relative;margin:5px">

<img src="../uploads/<?php echo $img; ?>" width="80">

<a href="?delete_img=<?php echo $row['id']; ?>&img=<?php echo $img; ?>" class="remove-btn">×</a>

</div>

<?php

}

}

?>

</div>

<div class="drop-zone">

Drag & Drop OR Paste Images

<input type="file" name="image[]" accept="image/*" multiple>

</div>

<div class="preview mt-2"></div>

<button type="submit" name="update_package" class="btn btn-primary mt-3">Update</button>

</form>

</div>

</div>

</div>

<?php } ?>

</tbody>

</table>

</div>


<!-- ADD MODAL -->

<div class="modal fade" id="addModal">

<div class="modal-dialog modal-lg">

<div class="modal-content p-4">

<form method="POST" enctype="multipart/form-data">

<h5>Add Package</h5>

<input type="text" name="title" class="form-control mb-2" placeholder="Title">

<input type="text" name="duration" class="form-control mb-2" placeholder="Duration">

<input type="number" name="price" class="form-control mb-2" placeholder="Price">

<input type="number" name="discount" class="form-control mb-2" placeholder="Discount">

<input type="number" name="seats" class="form-control mb-2" placeholder="Seats">

<textarea name="description" class="form-control mb-2"></textarea>

<select name="status" class="form-select mb-2">

<option value="Active">Active</option>
<option value="Inactive">Inactive</option>

</select>

<div class="drop-zone">

Drag & Drop OR Paste Images

<input type="file" name="image[]" accept="image/*" multiple>

</div>

<div class="preview mt-2"></div>

<button type="submit" name="add_package" class="btn btn-success mt-3">Add Package</button>

</form>

</div>

</div>

</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>

document.querySelectorAll('.drop-zone').forEach(zone => {

    const input = zone.querySelector("input");
    const preview = zone.nextElementSibling;

    let filesArray = [];

    /* CLICK TO OPEN FILE MANAGER */

    zone.addEventListener("click", () => {
        input.click();
    });

    /* DRAG OVER */

    zone.addEventListener("dragover", e => {
        e.preventDefault();
        zone.style.background="#e9f2ff";
    });

    zone.addEventListener("dragleave", () => {
        zone.style.background="";
    });

    /* DROP FILES */

    zone.addEventListener("drop", e => {

        e.preventDefault();
        zone.style.background="";

        const files = e.dataTransfer.files;

        addFiles(files);

    });

    /* FILE SELECT */

    input.addEventListener("change", () => {

        addFiles(input.files);

    });

    /* PASTE IMAGE */

    zone.addEventListener("paste", e => {

        const items = e.clipboardData.items;

        let pastedFiles = [];

        for(let item of items){

            if(item.type.indexOf("image") !== -1){

                pastedFiles.push(item.getAsFile());

            }

        }

        if(pastedFiles.length > 0){

            addFiles(pastedFiles);

        }

    });

    /* ADD FILE FUNCTION */

    function addFiles(files){

        for(let file of files){

            if(!file.type.startsWith("image")) continue;

            filesArray.push(file);

        }

        updateInput();
        previewImages();

    }

    /* UPDATE INPUT FILES */

    function updateInput(){

        const dt = new DataTransfer();

        filesArray.forEach(file => dt.items.add(file));

        input.files = dt.files;

    }

    /* SHOW IMAGE PREVIEW */

    function previewImages(){

        preview.innerHTML="";

        filesArray.forEach((file,index)=>{

            const wrapper = document.createElement("div");

            wrapper.style.display="inline-block";
            wrapper.style.position="relative";
            wrapper.style.margin="5px";

            const img = document.createElement("img");
            img.src = URL.createObjectURL(file);
            img.style.width="90px";
            img.style.borderRadius="10px";

            const remove = document.createElement("div");
            remove.innerHTML="×";

            remove.style.position="absolute";
            remove.style.top="-6px";
            remove.style.right="-6px";
            remove.style.background="red";
            remove.style.color="white";
            remove.style.width="20px";
            remove.style.height="20px";
            remove.style.borderRadius="50%";
            remove.style.textAlign="center";
            remove.style.fontSize="12px";
            remove.style.cursor="pointer";

            remove.onclick = () => {

                filesArray.splice(index,1);

                updateInput();
                previewImages();

            };

            wrapper.appendChild(img);
            wrapper.appendChild(remove);

            preview.appendChild(wrapper);

        });

    }

    /* MAKE PASTE WORK */

    zone.setAttribute("tabindex","0");

});

</script>

</body>
</html>