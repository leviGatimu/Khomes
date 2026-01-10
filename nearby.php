<?php
// nearby.php
require_once 'includes/db.php';
require_once 'includes/header.php';

$results = [];
$error = "";

// IF we received coordinates from the JavaScript below
if (isset($_GET['lat']) && isset($_GET['lng'])) {
    $user_lat = $_GET['lat'];
    $user_lng = $_GET['lng'];

    // THE HAVERSINE FORMULA (SQL Magic to find distance)
    // 6371 is Earth's radius in KM. 
    // It calculates distance and calls it 'distance_km'
    $sql = "SELECT p.*, 
            ( 6371 * acos( cos( radians(?) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( latitude ) ) ) ) AS distance_km
            FROM properties p
            WHERE p.status = 'active' AND p.latitude IS NOT NULL
            HAVING distance_km < 10 
            ORDER BY distance_km ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_lat, $user_lng, $user_lat]);
    $results = $stmt->fetchAll();
}
?>

<div class="container" style="margin-top: 50px; margin-bottom: 50px; min-height: 60vh;">
    
    <div style="text-align: center; margin-bottom: 40px;">
        <h2 style="color: #2c3e50;">Properties Near You</h2>
        <p style="color: #7f8c8d;">We are looking for listings within 10km of your location.</p>
        
        <button onclick="getLocation()" id="geoBtn" style="background: #2c3e50; color: white; padding: 12px 25px; border: none; border-radius: 30px; font-weight: bold; cursor: pointer; margin-top: 10px;">
            <i class="fas fa-location-arrow"></i> Find Places Around Me
        </button>
        <p id="status" style="margin-top: 10px; font-size: 0.9rem; color: #e67e22;"></p>
    </div>

    <?php if (isset($_GET['lat'])): ?>
        <?php if (count($results) > 0): ?>
            <div class="property-grid">
                <?php foreach ($results as $prop): ?>
                    <div class="property-card">
                        <div style="position: relative;">
                            <img src="uploads/properties/<?php echo $prop['main_image']; ?>" alt="Prop">
                            <span style="position: absolute; bottom: 10px; right: 10px; background: rgba(0,0,0,0.7); color: white; padding: 5px 10px; font-size: 12px; border-radius: 4px; font-weight: bold;">
                                <?php echo number_format($prop['distance_km'], 1); ?> km away
                            </span>
                        </div>
                        <div class="p-details">
                            <h3><?php echo htmlspecialchars($prop['title']); ?></h3>
                            <p class="price"><?php echo number_format($prop['price']); ?> RWF</p>
                            <a href="property.php?id=<?php echo $prop['property_id']; ?>" class="btn-view">View</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div style="text-align: center; color: #999; margin-top: 50px;">
                <h3>No properties found nearby.</h3>
                <p>Try searching manually in <a href="search.php">Find a Home</a>.</p>
            </div>
        <?php endif; ?>
    <?php endif; ?>

</div>

<script>
    function getLocation() {
        var status = document.getElementById("status");
        var btn = document.getElementById("geoBtn");

        if (!navigator.geolocation) {
            status.innerHTML = "Geolocation is not supported by your browser";
            return;
        }

        status.innerHTML = "Locating...";
        btn.disabled = true;
        btn.style.opacity = "0.7";

        navigator.geolocation.getCurrentPosition(success, error);
    }

    function success(position) {
        const lat = position.coords.latitude;
        const lng = position.coords.longitude;
        // Reload page with coordinates in URL
        window.location.href = "nearby.php?lat=" + lat + "&lng=" + lng;
    }

    function error() {
        document.getElementById("status").innerHTML = "Unable to retrieve your location. Please allow location access.";
        document.getElementById("geoBtn").disabled = false;
    }
</script>

<?php include 'includes/footer.php'; ?>