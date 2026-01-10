<?php
// add_property.php
session_start();
require_once 'includes/db.php';

// 1. SECURITY
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$error = '';
$success = '';

// 2. HANDLE FORM SUBMISSION
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Inputs
    $title = trim($_POST['title']);
    $category = $_POST['category'];
    $listing_type = $_POST['listing_type'];
    $price = $_POST['price'];
    $size = trim($_POST['property_size']);
    $district = $_POST['district'];
    $sector = trim($_POST['sector']);
    $description = trim($_POST['description']);
    $host_id = $_SESSION['user_id'];

    // 3. IMAGE VALIDATION & UPLOAD
    // We expect 5 images: 'main_image' and 'gallery_1' to 'gallery_4'
    $upload_dir = 'uploads/properties/';
    $saved_images = [];
    $image_fields = ['main_image', 'gallery_1', 'gallery_2', 'gallery_3', 'gallery_4'];
    $upload_errors = false;

    // Check if ALL 5 are present
    foreach ($image_fields as $field) {
        if (!isset($_FILES[$field]) || $_FILES[$field]['error'] !== 0) {
            $upload_errors = true;
            $error = "You must upload all 5 photos to list a property.";
            break;
        }
    }

    if (!$upload_errors) {
        // Create directory if not exists
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Loop through and upload each
        foreach ($image_fields as $field) {
            $file_ext = pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION);
            // Unique name: time + fieldname + random
            $unique_name = time() . "_{$field}_" . rand(1000, 9999) . '.' . $file_ext;
            
            if (move_uploaded_file($_FILES[$field]['tmp_name'], $upload_dir . $unique_name)) {
                $saved_images[] = $unique_name;
            } else {
                $error = "Failed to upload one of the images.";
                break;
            }
        }
    }

    // 4. INSERT INTO DB
    if (empty($error) && count($saved_images) === 5) {
        
        // Get Lat/Lng from POST (if empty, set to NULL)
        $lat = !empty($_POST['latitude']) ? $_POST['latitude'] : NULL;
        $lng = !empty($_POST['longitude']) ? $_POST['longitude'] : NULL;

        $sql = "INSERT INTO properties (host_id, title, category, listing_type, price, property_size, district, sector, description, main_image, image_2, image_3, image_4, image_5, province, status, latitude, longitude) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Kigali City', 'draft', ?, ?)";

        // --- THE FIX IS HERE ---
        // We prepare the statement correctly before executing
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([
            $host_id, $title, $category, $listing_type, $price, $size, $district, $sector, $description,
            $saved_images[0], $saved_images[1], $saved_images[2], $saved_images[3], $saved_images[4], 
            $lat, $lng
        ])) {
            // Get the ID of the new draft
            $new_id = $pdo->lastInsertId();
            
            // Redirect to the property page in PREVIEW MODE
            header("Location: property.php?id=$new_id&preview=true");
            exit;
        } else {
            $error = "Database error. Could not list property.";
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<style>
    .form-section-title { font-size: 1.2rem; color: #2c3e50; border-bottom: 2px solid #eee; padding-bottom: 10px; margin: 30px 0 20px; font-weight: 600; }
    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    
    /* Image Upload Grid */
    .gallery-grid { display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 15px; grid-template-rows: 200px 200px; }
    
    .upload-box { 
        background: #f8f9fa; border: 2px dashed #cbd5e0; border-radius: 8px; 
        display: flex; flex-direction: column; align-items: center; justify-content: center; 
        cursor: pointer; position: relative; overflow: hidden; transition: 0.3s;
    }
    .upload-box:hover { border-color: #27AE60; background: #f0fff4; }
    
    .upload-box input { position: absolute; width: 100%; height: 100%; opacity: 0; cursor: pointer; }
    .upload-box img { width: 100%; height: 100%; object-fit: cover; position: absolute; display: none; }
    .upload-label { color: #7f8c8d; font-size: 0.9rem; text-align: center; padding: 10px; pointer-events: none; }
    
    /* Layout specific */
    .main-upload { grid-column: 1 / 2; grid-row: 1 / 3; } /* Big box on left */
    
    @media (max-width: 768px) {
        .grid-2 { grid-template-columns: 1fr; }
        .gallery-grid { grid-template-columns: 1fr 1fr; grid-template-rows: auto; }
        .main-upload { grid-column: span 2; height: 250px; }
    }
</style>

<div class="container" style="max-width: 900px; margin-top: 40px; margin-bottom: 60px;">
    
    <div style="background: white; padding: 40px; border-radius: 12px; box-shadow: 0 5px 20px rgba(0,0,0,0.05);">
        <h2 style="color: #2c3e50; margin-bottom: 10px;">List Your Property</h2>
        <p style="color: #7f8c8d; margin-bottom: 30px;">Fill in the details below. High-quality photos are required.</p>

        <?php if($success): ?>
            <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                <?php echo $success; ?> <a href="index.php" style="font-weight: bold;">View Listing</a>
            </div>
        <?php endif; ?>
        
        <?php if($error): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                ⚠️ <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            
            <div class="form-section-title" style="margin-top: 0;">1. Basic Information</div>
            
            <div style="margin-bottom: 20px;">
                <label style="font-weight: 600; display: block; margin-bottom: 8px;">Property Title</label>
                <input type="text" name="title" placeholder="e.g. Luxury 3-Bedroom Apartment in Gacuriro" required 
                       style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px;">
            </div>

            <div class="grid-2">
                <div>
                    <label style="font-weight: 600; display: block; margin-bottom: 8px;">Category</label>
                    <select name="category" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px;">
                        <option value="house">Residential House</option>
                        <option value="apartment">Apartment</option>
                        <option value="land">Land / Plot</option>
                        <option value="commercial">Commercial</option>
                    </select>
                </div>
                <div>
                    <label style="font-weight: 600; display: block; margin-bottom: 8px;">Listing Type</label>
                    <select name="listing_type" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px;">
                        <option value="short_term">Short Term Rent</option>
                        <option value="long_term">Long Term Rent</option>
                        <option value="sale">For Sale</option>
                    </select>
                </div>
            </div>

            <div class="form-section-title">2. Details & Location</div>
            
            <div class="grid-2" style="margin-bottom: 20px;">
                <div>
                    <label style="font-weight: 600; display: block; margin-bottom: 8px;">Price (RWF)</label>
                    <input type="number" name="price" placeholder="e.g. 500000" required 
                           style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px;">
                </div>
                <div>
                    <label style="font-weight: 600; display: block; margin-bottom: 8px;">Size / Dimensions</label>
                    <input type="text" name="property_size" placeholder="e.g. 300sqm" 
                           style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px;">
                </div>
            </div>

            <div class="grid-2" style="margin-bottom: 20px;">
                <div>
                    <label style="font-weight: 600; display: block; margin-bottom: 8px;">District</label>
                    <select name="district" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px;">
                        <option value="Gasabo">Gasabo</option>
                        <option value="Kicukiro">Kicukiro</option>
                        <option value="Nyarugenge">Nyarugenge</option>
                    </select>
                </div>
                <div>
                    <label style="font-weight: 600; display: block; margin-bottom: 8px;">Sector</label>
                    <input type="text" name="sector" placeholder="e.g. Kimironko" required 
                           style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px;">
                </div>
            </div>

            <div class="grid-2" style="margin-bottom: 20px;">
                <div>
                    <label style="font-weight: 600; display: block; margin-bottom: 8px;">Latitude (e.g. -1.9441)</label>
                    <input type="text" name="latitude" placeholder="From Google Maps">
                </div>
                <div>
                    <label style="font-weight: 600; display: block; margin-bottom: 8px;">Longitude (e.g. 30.0619)</label>
                    <input type="text" name="longitude" placeholder="From Google Maps">
                </div>
            </div>
            <small style="display:block; margin-bottom: 20px; color: #666;">
                Tip: On Google Maps, right-click a spot to copy these numbers.
            </small>

            <div style="margin-bottom: 20px;">
                <label style="font-weight: 600; display: block; margin-bottom: 8px;">Description</label>
                <textarea name="description" rows="5" placeholder="Tell us about the property..." required 
                          style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px;"></textarea>
            </div>

            <div class="form-section-title">3. Photo Gallery (All 5 Required)</div>
            
            <div class="gallery-grid">
                <div class="upload-box main-upload">
                    <input type="file" name="main_image" accept="image/*" required onchange="previewImage(this)">
                    <img src="" alt="Preview">
                    <div class="upload-label">
                        <i class="fas fa-camera" style="font-size: 2rem; margin-bottom: 10px; color: #ccc;"></i><br>
                        <strong>Main Photo</strong><br>Click to Upload
                    </div>
                </div>

                <div class="upload-box">
                    <input type="file" name="gallery_1" accept="image/*" required onchange="previewImage(this)">
                    <img src="" alt="Preview">
                    <div class="upload-label">Image 2</div>
                </div>
                <div class="upload-box">
                    <input type="file" name="gallery_2" accept="image/*" required onchange="previewImage(this)">
                    <img src="" alt="Preview">
                    <div class="upload-label">Image 3</div>
                </div>
                <div class="upload-box">
                    <input type="file" name="gallery_3" accept="image/*" required onchange="previewImage(this)">
                    <img src="" alt="Preview">
                    <div class="upload-label">Image 4</div>
                </div>
                <div class="upload-box">
                    <input type="file" name="gallery_4" accept="image/*" required onchange="previewImage(this)">
                    <img src="" alt="Preview">
                    <div class="upload-label">Image 5</div>
                </div>
            </div>

            <button type="submit" style="margin-top: 30px; width: 100%; background: #27AE60; color: white; padding: 15px; border: none; border-radius: 6px; font-weight: bold; font-size: 1.1rem; cursor: pointer;">
                Publish Listing
            </button>
        </form>
    </div>
</div>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            // Find the <img> tag inside the same box and set its src
            var img = input.parentElement.querySelector('img');
            img.src = e.target.result;
            img.style.display = 'block'; // Show the image
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php include 'includes/footer.php'; ?>