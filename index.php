<?php
// index.php
require_once 'includes/db.php';

// FETCH DATA: TOP 3 RATED PROPERTIES
$sql = "SELECT p.*, 
        (SELECT AVG(rating) FROM reviews WHERE property_id = p.property_id) as avg_rating,
        (SELECT COUNT(*) FROM reviews WHERE property_id = p.property_id) as review_count,
        u.full_name, u.profile_image, u.is_verified
        FROM properties p 
        JOIN users u ON p.host_id = u.user_id 
        WHERE p.status = 'active' 
        -- ORDER BY RATING (Highest First), then Newest
        ORDER BY avg_rating DESC, p.created_at DESC 
        LIMIT 3";

$stmt = $pdo->query($sql);
$properties = $stmt->fetchAll();
?>

<?php include 'includes/header.php'; ?>

<style>
    /* HERO SECTION */
    .hero-section {
        background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('assets/images/rwanda-banner.jpg');
        background-size: cover;
        background-position: center;
        color: white;
        padding: 100px 20px;
        text-align: center;
    }

    /* CARD STYLES */
    .property-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        border: 1px solid #f0f0f0;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        overflow: hidden;
    }
    .property-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.12);
    }

    .img-wrapper {
        overflow: hidden;
        height: 220px;
        position: relative;
    }
    .img-wrapper img {
        width: 100%; height: 100%; object-fit: cover;
        transition: transform 0.5s ease;
    }
    .property-card:hover .img-wrapper img { transform: scale(1.05); }

    .verified-badge { color: #e67e22; font-size: 0.8em; margin-left: 3px; vertical-align: middle; }
    
    /* Category Grid */
    .category-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; }
    .cat-card { background: white; padding: 15px; border-radius: 8px; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.05); text-decoration: none; color: #555; border: 1px solid #eee; font-weight: 500; display: block; transition: 0.2s; }
    .cat-card:hover { border-color: #27AE60; color: #27AE60; transform: translateY(-3px); }
</style>

<div class="hero-section">
    <h1 style="font-size: 3rem; margin-bottom: 10px;">Find Your Place in Rwanda</h1>
    <p style="font-size: 1.2rem; opacity: 0.9;">Apartments, Houses, and Land for Rent & Sale.</p>
    
    <form action="search.php" method="GET" style="margin-top: 30px;">
        <input type="text" name="location" placeholder="Search by District (e.g. Gasabo)" style="padding: 15px; width: 60%; max-width: 400px; border-radius: 30px 0 0 30px; border: none; outline: none;">
        <button type="submit" style="padding: 15px 30px; background-color: #27AE60; color: white; border: none; cursor: pointer; border-radius: 0 30px 30px 0; font-weight: bold;">Search</button>
    </form>
</div>

<div class="container" style="margin-top: 50px; margin-bottom: 50px;">
    <h3 style="margin-bottom: 20px; color: #7f8c8d; font-weight: 400;">Browse by Category</h3>
    <div class="category-grid">
        <a href="search.php?category=house" class="cat-card"><span>🏠 Houses</span></a>
        <a href="search.php?category=apartment" class="cat-card"><span>🏢 Apartments</span></a>
        <a href="search.php?category=land" class="cat-card"><span>🌱 Land</span></a>
        <a href="search.php?category=commercial" class="cat-card"><span>🏪 Commercial</span></a>
    </div>
</div>

<div class="container" style="margin-bottom: 60px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
        <h2 style="margin: 0; color: #2c3e50; font-weight: 600;">⭐ Top Rated Homes</h2>
        <a href="search.php" style="color: #27AE60; text-decoration: none; font-weight: 600;">See All ➜</a>
    </div>
    
    <div class="property-grid">
        <?php if (count($properties) > 0): ?>
            <?php foreach ($properties as $prop): 
                // SMART HOST IMAGE LOGIC
                $user_img_path = 'uploads/users/' . $prop['profile_image'];
                if (!empty($prop['profile_image']) && file_exists($user_img_path)) {
                    $host_img = $user_img_path;
                } else {
                    $safe_name = urlencode($prop['full_name']);
                    $host_img = "https://ui-avatars.com/api/?name={$safe_name}&background=random&color=fff&size=128";
                }
            ?>
                <div class="property-card">
                    <a href="property.php?id=<?php echo $prop['property_id']; ?>" style="display: block; position: relative; text-decoration: none;">
                        <div class="img-wrapper">
                            <img src="uploads/properties/<?php echo $prop['main_image']; ?>" alt="Property">
                        </div>
                        <span style="position: absolute; top: 10px; left: 10px; background: <?php echo ($prop['listing_type'] == 'sale') ? '#D35400' : '#27AE60'; ?>; color: white; padding: 4px 8px; font-size: 11px; border-radius: 4px; font-weight: 700; text-transform: uppercase;">
                            <?php echo ($prop['listing_type'] == 'sale') ? 'For Sale' : 'Rent'; ?>
                        </span>
                        <?php if($prop['avg_rating'] > 0): ?>
                        <span style="position: absolute; bottom: 10px; right: 10px; background: white; color: #f1c40f; padding: 4px 8px; font-size: 12px; border-radius: 15px; font-weight: 700; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">
                            ★ <?php echo number_format($prop['avg_rating'], 1); ?>
                        </span>
                        <?php endif; ?>
                    </a>
                    
                    <div class="p-details" style="padding: 15px;">
                        <h3 style="margin: 0 0 5px; font-size: 1.1rem; color: #2c3e50;">
                            <a href="property.php?id=<?php echo $prop['property_id']; ?>" style="text-decoration: none; color: inherit;">
                                <?php echo htmlspecialchars($prop['title']); ?>
                            </a>
                        </h3>
                        
                        <div style="font-size: 0.9rem; color: #7f8c8d; margin-bottom: 15px;">
                            <span>📍 <?php echo htmlspecialchars($prop['district']); ?></span>
                        </div>

                        <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #f9f9f9; padding-top: 12px;">
                            <p style="font-weight: 700; color: #2c3e50; margin: 0; font-size: 1.1rem;">
                                <?php echo number_format($prop['price']); ?><small> RWF</small>
                            </p>
                            <div style="display: flex; align-items: center; font-size: 0.85rem; color: #7f8c8d;">
                                <img src="<?php echo $host_img; ?>" style="width: 24px; height: 24px; border-radius: 50%; object-fit: cover; margin-right: 6px;">
                                <span style="max-width: 80px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars($prop['full_name']); ?></span>
                                <?php if($prop['is_verified']): ?>
                                    <i class="fas fa-check-circle verified-badge"></i>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align: center; color: #999; width: 100%; padding: 40px;">No top rated properties yet.</p>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>