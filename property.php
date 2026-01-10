<?php
// property.php
session_start();
require_once 'includes/db.php';
require_once 'includes/header.php';

// 1. GET ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit;
}
$property_id = $_GET['id'];
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

// 2. FETCH PROPERTY DETAILS
$sql = "SELECT p.*, u.full_name, u.phone_number, u.email, u.profile_image, u.is_verified 
        FROM properties p 
        JOIN users u ON p.host_id = u.user_id 
        WHERE p.property_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$property_id]);
$prop = $stmt->fetch();

if (!$prop) {
    header("Location: index.php");
    exit;
}

// 3. CALCULATE RATINGS
$avg_stmt = $pdo->prepare("SELECT AVG(rating) as avg_score, COUNT(*) as total FROM reviews WHERE property_id = ?");
$avg_stmt->execute([$property_id]);
$stats = $avg_stmt->fetch();

$average_rating = $stats['avg_score'] ? number_format($stats['avg_score'], 1) : 0;
$total_reviews = $stats['total'];

// 4. FETCH REVIEWS LIST
$rev_stmt = $pdo->prepare("SELECT r.*, u.full_name, u.profile_image, u.is_verified, u.user_id FROM reviews r JOIN users u ON r.user_id = u.user_id WHERE r.property_id = ? ORDER BY r.created_at DESC");
$rev_stmt->execute([$property_id]);
$reviews = $rev_stmt->fetchAll();
?>

<style>
    /* PAGE LAYOUT */
    .gallery-container { margin-bottom: 40px; }
    .hero-frame { height: 500px; width: 100%; overflow: hidden; border-radius: 10px; position: relative; background: #000; }
    .hero-frame img { width: 100%; height: 100%; object-fit: cover; transition: opacity 0.3s ease; }
    
    .thumb-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 10px; margin-top: 10px; }
    .thumb-box { width: 100%; aspect-ratio: 16 / 9; cursor: pointer; border-radius: 5px; overflow: hidden; opacity: 0.7; transition: 0.2s; border: 2px solid transparent; background: #eee; }
    .thumb-box:hover, .thumb-box.active { opacity: 1; border-color: var(--primary-orange); }
    .thumb-box img { width: 100%; height: 100%; object-fit: cover; display: block; }

    .prop-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 40px; margin-top: 30px; margin-bottom: 60px; }
    .booking-card { background: white; padding: 25px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); position: sticky; top: 100px; border: 1px solid #eee; }

    .review-section { margin-top: 50px; background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
    .star-widget { display: flex; flex-direction: row-reverse; justify-content: flex-end; gap: 10px; }
    .star-widget input { display: none; }
    .star-widget label { font-size: 35px; color: #ddd; cursor: pointer; transition: 0.2s; }
    .star-widget label:hover, .star-widget label:hover ~ label, .star-widget input:checked ~ label { color: #ffb400; transform: scale(1.1); }
    
    .review-item { border-bottom: 1px solid #eee; padding: 25px 0; display: flex; gap: 20px; }
    .review-avatar { width: 60px; height: 60px; border-radius: 50%; object-fit: cover; border: 2px solid #f0f0f0; flex-shrink: 0; }
    
    .booking-input { width: 100%; padding: 12px; margin: 8px 0 15px; border: 1px solid #ddd; border-radius: 8px; font-family: inherit; }

    @media (max-width: 768px) {
        .hero-frame { height: 300px; }
        .prop-grid { grid-template-columns: 1fr; }
    }
</style>

<div class="container" style="margin-top: 30px;">
    
    <div class="gallery-container">
        <div class="hero-frame">
            <img id="mainDisplay" src="uploads/properties/<?php echo $prop['main_image']; ?>" alt="Main View">
        </div>
        <div class="thumb-grid">
            <div class="thumb-box active" onclick="swapImage(this, 'uploads/properties/<?php echo $prop['main_image']; ?>')">
                <img src="uploads/properties/<?php echo $prop['main_image']; ?>">
            </div>
            <?php 
                $extras = ['image_2', 'image_3', 'image_4', 'image_5'];
                foreach($extras as $img_col): 
                    if(!empty($prop[$img_col])):
            ?>
                <div class="thumb-box" onclick="swapImage(this, 'uploads/properties/<?php echo $prop[$img_col]; ?>')">
                    <img src="uploads/properties/<?php echo $prop[$img_col]; ?>">
                </div>
            <?php endif; endforeach; ?>
        </div>
    </div>

    <div class="prop-grid">
        <div>
            <div style="margin-bottom: 10px;">
                <span style="background: var(--dark-blue); color: white; padding: 5px 12px; font-size: 12px; border-radius: 4px; text-transform: uppercase;">
                    <?php echo htmlspecialchars($prop['category']); ?>
                </span>
                <span style="background: var(--primary-orange); color: white; padding: 5px 12px; font-size: 12px; border-radius: 4px; text-transform: uppercase; margin-left: 10px;">
                    <?php 
                        if ($prop['listing_type'] == 'short_term') echo 'Short Stay';
                        elseif ($prop['listing_type'] == 'long_term') echo 'Long Rent';
                        else echo 'For Sale';
                    ?>
                </span>
            </div>

            <h1 style="font-size: 2.2rem; color: var(--dark-blue); margin: 10px 0;">
                <?php echo htmlspecialchars($prop['title']); ?>
            </h1>
            
            <p style="font-size: 1.1rem; color: #666; margin-bottom: 25px;">
                <i class="fas fa-map-marker-alt" style="color: var(--primary-orange);"></i> 
                <?php echo htmlspecialchars($prop['district']) . ', ' . htmlspecialchars($prop['sector']); ?>
            </p>

            <div style="background: #fdf2e9; display: inline-flex; align-items: center; gap: 10px; padding: 10px 20px; border-radius: 8px; border: 1px solid #fae5d3;">
               <span style="font-weight: 600; color: #d35400;">📏 Size: <?php echo htmlspecialchars($prop['property_size']); ?></span>
            </div>

            <hr style="border: 0; border-top: 1px solid #eee; margin: 30px 0;">

            <h3 style="color: var(--dark-blue);">Property Description</h3>
            <p style="line-height: 1.8; color: #444; white-space: pre-line;">
                <?php echo htmlspecialchars($prop['description']); ?>
            </p>

            <hr style="border: 0; border-top: 1px solid #eee; margin: 30px 0;">

            <h3>Host Information</h3>
            <div style="display: flex; align-items: center; gap: 15px; margin-top: 15px; background: #f9f9f9; padding: 20px; border-radius: 12px;">
                <?php 
                $host_img = !empty($prop['profile_image']) ? 'uploads/users/'.$prop['profile_image'] : "https://ui-avatars.com/api/?name=".urlencode($prop['full_name'])."&background=F39C47&color=fff";
                ?>
                <a href="profile.php?id=<?php echo $prop['host_id']; ?>">
                    <img src="<?php echo $host_img; ?>" style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover; border: 2px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                </a>
                <div>
                    <h4 style="margin: 0; display: flex; align-items: center; gap: 5px;">
                        <?php echo htmlspecialchars($prop['full_name']); ?>
                        <?php if($prop['is_verified']): ?>
                            <i class="fas fa-check-circle" style="color: #2980b9;" title="Verified Host"></i>
                        <?php endif; ?>
                    </h4>
                    <p style="font-size: 0.85rem; color: #7f8c8d;">Member since <?php echo date('M Y', strtotime($prop['created_at'])); ?></p>
                </div>
            </div>

            <button onclick="openReportModal()" style="margin-top: 30px; background:none; border:none; color:#e74c3c; cursor:pointer; font-size:0.85rem; text-decoration:underline;">
                <i class="fas fa-flag"></i> Report this listing
            </button>
        </div>

        <aside>
            <div class="booking-card">
                <?php 
                $is_owner = ($user_id == $prop['host_id']);
                
                if ($is_owner && $prop['status'] == 'draft'): ?>
                    <div style="text-align: center;">
                        <h3 style="color: #e67e22; margin-top: 0;">⚠️ Draft Mode</h3>
                        <p style="font-size: 0.9rem; color: #666;">This listing is invisible to others.</p>
                        <a href="publish_property.php?id=<?php echo $prop['property_id']; ?>" class="btn-highlight" style="display:block; text-align:center; margin-top:15px;">🚀 Publish Now</a>
                    </div>
                <?php else: ?>
                    <div style="margin-bottom: 20px;">
                        <span style="font-size: 1.8rem; font-weight: 700; color: var(--dark-blue);">
                            <?php echo number_format($prop['price']); ?> RWF
                        </span>
                        <span style="color: #7f8c8d; font-size: 0.9rem;">
                            <?php echo ($prop['listing_type'] == 'sale') ? '' : '/ month'; ?>
                        </span>
                    </div>

                    <form action="process_booking.php" method="POST" style="border-top: 1px solid #eee; padding-top: 20px;">
                        <input type="hidden" name="property_id" value="<?php echo $property_id; ?>">
                        
                        <label style="font-size: 0.8rem; font-weight: 600; color: #555; text-transform: uppercase;">Check-in</label>
                        <input type="date" name="check_in" required class="booking-input">

                        <label style="font-size: 0.8rem; font-weight: 600; color: #555; text-transform: uppercase;">Check-out</label>
                        <input type="date" name="check_out" required class="booking-input">

                        <button type="submit" class="btn-highlight" style="width: 100%; border: none; cursor: pointer; padding: 15px; font-size: 1rem;">
                            <i class="fas fa-calendar-check"></i> Request Booking
                        </button>
                    </form>

                    <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee; text-align: center;">
                        <p style="font-size: 0.9rem; color: #666; margin-bottom: 15px;">Or contact the host directly:</p>
                        <a href="tel:<?php echo $prop['phone_number']; ?>" style="display: block; color: var(--dark-blue); font-weight: 700; font-size: 1.1rem; text-decoration: none; margin-bottom: 10px;">
                            <i class="fas fa-phone-alt" style="color: var(--primary-orange);"></i> <?php echo htmlspecialchars($prop['phone_number']); ?>
                        </a>
                        
                        <?php if($user_id && !$is_owner): ?>
                            <button onclick="startChatWith(<?php echo $prop['host_id']; ?>, '<?php echo htmlspecialchars($prop['full_name']); ?>')" 
                                    style="width: 100%; background: #f8f9fa; color: var(--dark-blue); padding: 10px; border: 1px solid #ddd; border-radius: 8px; font-weight: 600; cursor: pointer;">
                                <i class="fas fa-comment-dots"></i> Send Message
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </aside>
    </div>

    <?php if (in_array($prop['category'], ['house', 'apartment']) && $prop['listing_type'] != 'sale'): ?>
    <div class="review-section">
        <h2 style="margin-top: 0; color: var(--dark-blue);">Guest Reviews (<?php echo $total_reviews; ?>)</h2>
        
        <?php if ($user_id && !$is_owner): ?>
            <form action="submit_review.php" method="POST" style="background: #f9f9f9; padding: 25px; border-radius: 12px; margin-bottom: 40px; border: 1px solid #eee;">
                <input type="hidden" name="property_id" value="<?php echo $property_id; ?>">
                <p style="margin-top: 0; font-weight: 600;">Rate your experience:</p>
                <div class="star-widget">
                    <input type="radio" name="rating" id="rate-5" value="5" required><label for="rate-5">★</label>
                    <input type="radio" name="rating" id="rate-4" value="4"><label for="rate-4">★</label>
                    <input type="radio" name="rating" id="rate-3" value="3"><label for="rate-3">★</label>
                    <input type="radio" name="rating" id="rate-2" value="2"><label for="rate-2">★</label>
                    <input type="radio" name="rating" id="rate-1" value="1"><label for="rate-1">★</label>
                </div>
                <textarea name="comment" rows="3" placeholder="Share details of your stay..." class="booking-input" style="height: 100px;"></textarea>
                <button type="submit" class="btn-highlight" style="border: none; cursor: pointer;">Post Review</button>
            </form>
        <?php endif; ?>

        <div class="review-list">
            <?php foreach ($reviews as $rev): 
                $r_img = !empty($rev['profile_image']) ? 'uploads/users/'.$rev['profile_image'] : "https://ui-avatars.com/api/?name=".urlencode($rev['full_name'])."&background=random&color=fff";
            ?>
                <div class="review-item">
                    <img src="<?php echo $r_img; ?>" class="review-avatar">
                    <div style="flex-grow: 1;">
                        <div style="display: flex; justify-content: space-between;">
                            <h4 style="margin: 0; color: var(--dark-blue);"><?php echo htmlspecialchars($rev['full_name']); ?></h4>
                            <span style="color: #ffb400;"><?php echo str_repeat('★', $rev['rating']); ?></span>
                        </div>
                        <p style="color: #555; margin-top: 8px; font-size: 0.95rem;"><?php echo htmlspecialchars($rev['comment']); ?></p>
                        <small style="color: #999;"><?php echo date('M d, Y', strtotime($rev['created_at'])); ?></small>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<div id="reportModal" class="filter-modal-overlay" style="display:none; position: fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; justify-content: center; align-items:center;">
    <div style="background:white; padding:30px; border-radius:15px; width:400px; box-shadow:0 10px 25px rgba(0,0,0,0.2);">
        <h3 style="margin-top:0;">Report Listing</h3>
        <p style="font-size:0.9rem; color:#666;">Describe the issue with this property.</p>
        <textarea id="reportReason" class="booking-input" rows="4" placeholder="e.g. Scams, fake photos..."></textarea>
        <div style="display:flex; gap:10px;">
            <button onclick="submitReport()" class="btn-highlight" style="flex:1; border:none; background:#e74c3c; cursor:pointer;">Submit</button>
            <button onclick="closeReportModal()" class="btn-highlight" style="flex:1; border:none; background:#95a5a6; cursor:pointer;">Cancel</button>
        </div>
    </div>
</div>

<script>
    function swapImage(element, srcUrl) {
        document.getElementById('mainDisplay').src = srcUrl;
        document.querySelectorAll('.thumb-box').forEach(box => box.classList.remove('active'));
        element.classList.add('active');
    }

    function openReportModal() { document.getElementById('reportModal').style.display = 'flex'; }
    function closeReportModal() { document.getElementById('reportModal').style.display = 'none'; }

    function submitReport() {
        const reason = document.getElementById('reportReason').value;
        if(!reason) return alert("Please provide a reason.");
        fetch('report_property.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `property_id=<?php echo $property_id; ?>&reason=${encodeURIComponent(reason)}`
        })
        .then(() => {
            alert("Report submitted to Admins.");
            closeReportModal();
        });
    }
</script>

<?php include 'includes/footer.php'; ?>