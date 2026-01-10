<?php
// manage_account.php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$msg = "";
$error = "";

// 1. HANDLE FORM
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // A. UPDATE INFO (Bio & Name)
    if (isset($_POST['update_info'])) {
        $bio = trim($_POST['bio']);
        // You could also allow name updates here if you wanted
        $stmt = $pdo->prepare("UPDATE users SET bio = ? WHERE user_id = ?");
        $stmt->execute([$bio, $user_id]);
        $msg = "Profile updated successfully!";
    }

    // B. PROFILE IMAGE LOGIC
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $ext = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $new_name = "user_" . $user_id . "_" . time() . "." . $ext;
            $dest = "uploads/users/" . $new_name;
            
            // Create folder if not exists
            if (!is_dir('uploads/users')) {
                mkdir('uploads/users', 0777, true);
            }

            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $dest)) {
                // Update DB
                $pdo->prepare("UPDATE users SET profile_image = ? WHERE user_id = ?")->execute([$new_name, $user_id]);
                $_SESSION['profile_image'] = $new_name; 
                $msg = "Profile image updated!";
            }
        }
    }

    // C. REMOVE IMAGE
    if (isset($_POST['delete_image'])) {
        $stmt = $pdo->prepare("SELECT profile_image FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $current = $stmt->fetchColumn();
        
        if ($current && file_exists("uploads/users/" . $current)) {
            unlink("uploads/users/" . $current);
        }
        
        $pdo->prepare("UPDATE users SET profile_image = NULL WHERE user_id = ?")->execute([$user_id]);
        unset($_SESSION['profile_image']);
        $msg = "Photo removed.";
    }
}

// 2. FETCH DATA & SMART IMAGE LOGIC
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$u = $stmt->fetch();

// --- SMART CHECK START ---
$file_path = "uploads/users/" . $u['profile_image'];

if (!empty($u['profile_image']) && file_exists($file_path)) {
    // If DB has name AND file exists on server, use it
    $image_path = $file_path;
} else {
    // Otherwise, generate Initials Avatar (e.g., "LV" for Levi)
    $safe_name = urlencode($u['full_name']);
    $image_path = "https://ui-avatars.com/api/?name={$safe_name}&background=random&color=fff&size=128";
}
// --- SMART CHECK END ---
?>

<?php include 'includes/header.php'; ?>

<div class="container" style="max-width: 800px; margin-top: 50px; margin-bottom: 50px;">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="color: #2c3e50; margin: 0;">Edit Profile</h2>
        <a href="profile.php?id=<?php echo $user_id; ?>" target="_blank" style="background: #3498db; color: white; padding: 10px 20px; text-decoration: none; border-radius: 30px; font-weight: bold;">
            <i class="fas fa-eye"></i> View Public Profile
        </a>
    </div>

    <div style="background: white; padding: 40px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
        
        <?php if($msg): ?><div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 20px;"><?php echo $msg; ?></div><?php endif; ?>

        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 40px;">
            
            <div style="text-align: center;">
                <div style="width: 120px; height: 120px; margin: 0 auto 15px; border-radius: 50%; overflow: hidden; border: 3px solid #eee;">
                    <img src="<?php echo $image_path; ?>" style="width: 100%; height: 100%; object-fit: cover;">
                </div>
                
                <form method="POST" enctype="multipart/form-data">
                    <label style="cursor: pointer; background: #eef2f7; padding: 8px 15px; border-radius: 20px; font-size: 0.85rem; color: #2c3e50; font-weight: bold; display: inline-block; margin-bottom: 10px;">
                        📷 Change
                        <input type="file" name="profile_image" style="display: none;" onchange="this.form.submit()">
                    </label>
                    
                    <?php if (!empty($u['profile_image'])): ?>
                        <br>
                        <button type="submit" name="delete_image" value="1" onclick="return confirm('Remove photo?')" style="background: none; border: none; color: #e74c3c; cursor: pointer; text-decoration: underline; font-size: 0.85rem;">Remove Photo</button>
                    <?php endif; ?>
                </form>
            </div>

            <form method="POST">
                <div style="margin-bottom: 20px;">
                    <label style="font-weight: bold; display: block; margin-bottom: 5px;">Full Name</label>
                    <input type="text" value="<?php echo htmlspecialchars($u['full_name']); ?>" disabled style="width: 100%; padding: 10px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 5px; cursor: not-allowed;">
                    <small style="color: #999;">Contact support to change your name.</small>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="font-weight: bold; display: block; margin-bottom: 5px;">Bio / About Me</label>
                    <textarea name="bio" rows="5" placeholder="Tell people about yourself..." style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; font-family: inherit;"><?php echo htmlspecialchars($u['bio']); ?></textarea>
                </div>

                <button type="submit" name="update_info" value="1" style="background: #2c3e50; color: white; border: none; padding: 12px 30px; border-radius: 5px; font-weight: bold; cursor: pointer;">
                    Save Profile
                </button>
            </form>
            
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>