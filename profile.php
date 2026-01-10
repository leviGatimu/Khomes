<?php
// profile.php
session_start();
require_once 'includes/db.php';
require_once 'includes/header.php';

// 1. GET USER ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<div class='container' style='margin-top:50px;'>User not found.</div>";
    include 'includes/footer.php';
    exit;
}
$user_id = (int)$_GET['id'];

// 2. FETCH USER DETAILS (Added is_verified)
$stmt = $pdo->prepare("SELECT full_name, profile_image, bio, created_at, user_role, is_verified FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$u = $stmt->fetch();

if (!$u) {
    echo "<div class='container' style='margin-top:50px;'>User not found.</div>";
    include 'includes/footer.php';
    exit;
}

// 3. IMAGE LOGIC (Smart Fallback)
$img_path = 'uploads/users/' . $u['profile_image'];
if (!empty($u['profile_image']) && file_exists($img_path)) {
    $img = $img_path;
} else {
    $safe_name = urlencode($u['full_name']);
    $img = "https://ui-avatars.com/api/?name={$safe_name}&background=random&color=fff&size=128";
}
?>

<style>
    .verified-badge-large { color: #e67e22; font-size: 0.6em; vertical-align: middle; margin-left: 10px; }
</style>

<div class="container" style="max-width: 800px; margin-top: 60px; margin-bottom: 80px;">
    
    <div style="background: white; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); overflow: hidden; display: flex; flex-direction: column; align-items: center; text-align: center; padding: 40px;">
        
        <div style="width: 150px; height: 150px; border-radius: 50%; overflow: hidden; border: 5px solid #f4f6f9; margin-bottom: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
            <img src="<?php echo $img; ?>" style="width: 100%; height: 100%; object-fit: cover;">
        </div>

        <h1 style="margin: 0; color: #2c3e50; font-size: 2.5rem; display: flex; align-items: center; justify-content: center;">
            <?php echo htmlspecialchars($u['full_name']); ?>
            
            <?php if($u['is_verified']): ?>
                <i class="fas fa-check-circle verified-badge-large" title="Verified Account"></i>
            <?php endif; ?>
        </h1>
        
        <div style="margin-top: 10px;">
            <?php if($u['user_role'] === 'host'): ?>
                <span style="background: #27AE60; color: white; padding: 5px 15px; border-radius: 20px; font-size: 0.8rem; font-weight: bold; text-transform: uppercase;">Host</span>
            <?php elseif($u['user_role'] === 'admin'): ?>
                <span style="background: #34495e; color: white; padding: 5px 15px; border-radius: 20px; font-size: 0.8rem; font-weight: bold; text-transform: uppercase;">Admin</span>
            <?php endif; ?>
        </div>

        <p style="color: #95a5a6; font-size: 0.9rem; margin-top: 5px;">
            Joined <?php echo date('F Y', strtotime($u['created_at'])); ?>
        </p>

        <hr style="width: 50px; border: 2px solid #eee; margin: 25px auto;">

        <div style="max-width: 600px; color: #555; line-height: 1.8; font-size: 1.1rem;">
            <?php if(!empty($u['bio'])): ?>
                <?php echo nl2br(htmlspecialchars($u['bio'])); ?>
            <?php else: ?>
                <p style="font-style: italic; color: #aaa;">This user hasn't written a bio yet.</p>
            <?php endif; ?>
        </div>
        <h1 style="..."><?php echo htmlspecialchars($u['full_name']); ?> ...</h1>

<?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] != $user_id): ?>
    <button onclick="startChatWith(<?php echo $user_id; ?>, '<?php echo htmlspecialchars($u['full_name']); ?>')" 
            style="margin-top: 15px; background: #2980b9; color: white; border: none; padding: 10px 25px; border-radius: 20px; font-weight: bold; cursor: pointer; transition: 0.2s;">
        <i class="fas fa-paper-plane"></i> Send Message
    </button>
<?php endif; ?>

    </div>

</div>

<?php include 'includes/footer.php'; ?>