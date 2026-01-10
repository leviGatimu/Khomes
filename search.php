<?php
// search.php
session_start();
require_once 'includes/db.php';
require_once 'includes/header.php';

// --- BUILD SEARCH QUERY (Same logic as before) ---
$where_clauses = ["p.status = 'active'"];
$params = [];

if (isset($_GET['location']) && !empty($_GET['location'])) {
    $where_clauses[] = "(district LIKE ? OR sector LIKE ?)";
    $loc = "%" . $_GET['location'] . "%";
    $params[] = $loc;
    $params[] = $loc;
}
if (isset($_GET['category']) && !empty($_GET['category'])) {
    $where_clauses[] = "category = ?";
    $params[] = $_GET['category'];
}
if (isset($_GET['listing_type']) && !empty($_GET['listing_type'])) {
    $where_clauses[] = "listing_type = ?";
    $params[] = $_GET['listing_type'];
}

$where_sql = implode(' AND ', $where_clauses);

// FETCH DATA
$sql = "SELECT p.*, 
        (SELECT AVG(rating) FROM reviews WHERE property_id = p.property_id) as avg_rating,
        (SELECT COUNT(*) FROM reviews WHERE property_id = p.property_id) as review_count,
        u.full_name, u.profile_image, u.is_verified
        FROM properties p 
        JOIN users u ON p.host_id = u.user_id 
        WHERE $where_sql
        ORDER BY p.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$properties = $stmt->fetchAll();
?>

<style>
    /* 1. TOP BAR */
    .search-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 40px;
        margin-bottom: 30px;
    }
    
    .filter-trigger-btn {
        background: white;
        border: 1px solid #ddd;
        padding: 10px 20px;
        border-radius: 30px;
        cursor: pointer;
        font-weight: 600;
        color: #2c3e50;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: 0.2s;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }
    .filter-trigger-btn:hover { background: #f8f9fa; border-color: #27AE60; color: #27AE60; }

    /* 2. FILTER MODAL (Hidden by default) */
    .filter-modal-overlay {
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0,0,0,0.5); /* Dimmed background */
        z-index: 10000;
        display: none; /* Hidden */
        justify-content: center;
        align-items: center;
        backdrop-filter: blur(3px);
    }
    
    .filter-modal {
        background: white;
        width: 90%; max-width: 500px;
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        animation: popIn 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    @keyframes popIn {
        from { opacity: 0; transform: scale(0.9); }
        to { opacity: 1; transform: scale(1); }
    }

    /* Form Styles inside Modal */
    .filter-group { margin-bottom: 20px; }
    .filter-group label { display: block; font-weight: 600; color: #555; margin-bottom: 8px; font-size: 0.9rem; }
    .filter-input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-family: inherit; font-size: 1rem; }
    .filter-submit-btn { width: 100%; background: #2c3e50; color: white; padding: 15px; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; font-size: 1rem; margin-top: 10px; }
    .filter-submit-btn:hover { background: #34495e; }

    /* 3. LISTING GRID (Full Width) */
    .property-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); /* Responsive columns */
        gap: 30px;
    }

    /* Keep Card Styles from Index */
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    .property-card {
        background: white; border-radius: 12px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        border: 1px solid #f0f0f0; transition: transform 0.3s ease, box-shadow 0.3s ease;
        overflow: hidden; opacity: 0; animation: fadeInUp 0.6s ease-out forwards;
    }
    .property-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,0.1); }
    .img-wrapper { height: 220px; overflow: hidden; position: relative; }
    .img-wrapper img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s ease; }
    .property-card:hover .img-wrapper img { transform: scale(1.08); }
    .verified-badge { color: #e67e22; font-size: 0.8em; margin-left: 3px; vertical-align: middle; }
</style>

<div class="container">
    
    <div class="search-header">
        <h1 style="font-size: 1.8rem; color: #2c3e50; margin: 0;">
            Found <?php echo count($properties); ?> Properties
            <?php if(isset($_GET['location']) && !empty($_GET['location'])) echo " in <span style='color:#27AE60'>'" . htmlspecialchars($_GET['location']) . "'</span>"; ?>
        </h1>
        
        <button class="filter-trigger-btn" onclick="toggleFilterModal()">
            <i class="fas fa-sliders-h"></i> Filters
        </button>
    </div>

    <div class="property-grid">
        <?php if (count($properties) > 0): ?>
            <?php foreach ($properties as $index => $prop): 
                $host_img_path = 'uploads/users/' . $prop['profile_image'];
                if (!empty($prop['profile_image']) && file_exists($host_img_path)) {
                    $host_img = $host_img_path;
                } else {
                    $safe_name = urlencode($prop['full_name']);
                    $host_img = "https://ui-avatars.com/api/?name={$safe_name}&background=random&color=fff&size=128";
                }
                $anim_delay = ($index < 10) ? $index * 0.1 : 0; 
            ?>
                <div class="property-card" style="animation-delay: <?php echo $anim_delay; ?>s;">
                    <a href="property.php?id=<?php echo $prop['property_id']; ?>" style="display: block; position: relative; text-decoration: none;">
                        <div class="img-wrapper">
                            <img src="uploads/properties/<?php echo $prop['main_image']; ?>" alt="Property">
                        </div>
                        <span style="position: absolute; top: 10px; left: 10px; background: <?php echo ($prop['listing_type'] == 'sale') ? '#D35400' : '#27AE60'; ?>; color: white; padding: 4px 8px; font-size: 11px; border-radius: 4px; font-weight: 700; text-transform: uppercase;">
                            <?php echo ($prop['listing_type'] == 'sale') ? 'For Sale' : 'Rent'; ?>
                        </span>
                    </a>
                    
                    <div class="p-details" style="padding: 15px;">
                        <h3 style="margin: 0 0 5px; font-size: 1.1rem; color: #2c3e50;">
                            <a href="property.php?id=<?php echo $prop['property_id']; ?>" style="text-decoration: none; color: inherit;">
                                <?php echo htmlspecialchars($prop['title']); ?>
                            </a>
                        </h3>
                        
                        <div style="font-size: 0.9rem; color: #7f8c8d; margin-bottom: 15px; display: flex; align-items: center; gap: 8px;">
                            <span>📍 <?php echo htmlspecialchars($prop['district']); ?></span>
                            <?php if($prop['review_count'] > 0): ?>
                                <span style="color: #ddd;">|</span>
                                <span style="color: #f1c40f; font-weight: 600;">★ <?php echo number_format($prop['avg_rating'], 1); ?></span>
                            <?php endif; ?>
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
            <div style="grid-column: 1 / -1; text-align: center; padding: 60px; background: #f9f9f9; border-radius: 12px; color: #7f8c8d;">
                <i class="fas fa-search" style="font-size: 3rem; margin-bottom: 15px; opacity: 0.3;"></i>
                <p>No properties found.</p>
                <a href="search.php" style="color: #27AE60; font-weight: bold;">Show All Listings</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="filter-modal-overlay" id="filterModal">
    <div class="filter-modal">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            <h2 style="margin: 0; color: #2c3e50;">Filter Results</h2>
            <i class="fas fa-times" onclick="toggleFilterModal()" style="font-size: 1.5rem; cursor: pointer; color: #999;"></i>
        </div>
        
        <form action="search.php" method="GET">
            <div class="filter-group">
                <label>📍 Location</label>
                <input type="text" name="location" class="filter-input" placeholder="District or Sector..." value="<?php echo isset($_GET['location']) ? htmlspecialchars($_GET['location']) : ''; ?>">
            </div>

            <div class="filter-group">
                <label>🏠 Category</label>
                <select name="category" class="filter-input">
                    <option value="">All Categories</option>
                    <option value="house" <?php if(isset($_GET['category']) && $_GET['category'] == 'house') echo 'selected'; ?>>House</option>
                    <option value="apartment" <?php if(isset($_GET['category']) && $_GET['category'] == 'apartment') echo 'selected'; ?>>Apartment</option>
                    <option value="land" <?php if(isset($_GET['category']) && $_GET['category'] == 'land') echo 'selected'; ?>>Land</option>
                    <option value="commercial" <?php if(isset($_GET['category']) && $_GET['category'] == 'commercial') echo 'selected'; ?>>Commercial</option>
                </select>
            </div>

            <div class="filter-group">
                <label>🔑 Listing Type</label>
                <select name="listing_type" class="filter-input">
                    <option value="">Any</option>
                    <option value="short_term" <?php if(isset($_GET['listing_type']) && $_GET['listing_type'] == 'short_term') echo 'selected'; ?>>Short Term Rent</option>
                    <option value="long_term" <?php if(isset($_GET['listing_type']) && $_GET['listing_type'] == 'long_term') echo 'selected'; ?>>Long Term Rent</option>
                    <option value="sale" <?php if(isset($_GET['listing_type']) && $_GET['listing_type'] == 'sale') echo 'selected'; ?>>For Sale</option>
                </select>
            </div>

            <button type="submit" class="filter-submit-btn">Apply Filters</button>
        </form>
    </div>
</div>

<script>
    function toggleFilterModal() {
        const modal = document.getElementById('filterModal');
        if (modal.style.display === 'flex') {
            modal.style.display = 'none';
        } else {
            modal.style.display = 'flex';
        }
    }
    
    // Close modal if clicking outside the box
    document.getElementById('filterModal').addEventListener('click', function(e) {
        if (e.target === this) {
            this.style.display = 'none';
        }
    });
</script>

<?php include 'includes/footer.php'; ?>