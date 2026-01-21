<?php
// map_search.php
session_start();
require_once 'includes/db.php';
require_once 'includes/header.php';

// 1. FETCH PROPERTIES WITH COORDINATES
// We only want properties that have a location set (lat IS NOT NULL)
$sql = "SELECT property_id, title, price, lat, lng, main_image FROM properties WHERE status='active' AND lat IS NOT NULL";
$stmt = $pdo->query($sql);
$properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Convert PHP array to JSON for JavaScript
$json_properties = json_encode($properties);
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<style>
    #map-container { position: relative; width: 100%; height: 85vh; z-index: 1; }
    #khome-map { width: 100%; height: 100%; }
    .info-card img { width: 100%; height: 120px; object-fit: cover; border-radius: 8px; }
    .info-card h4 { margin: 5px 0; font-size: 1rem; color: var(--dark-blue); }
    .btn-view { display: block; background: var(--primary-orange); color: white; text-align: center; padding: 5px; border-radius: 5px; text-decoration: none; margin-top: 5px; }
</style>

<div id="map-container">
    <div id="khome-map"></div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    // 1. Initialize Map
    var map = L.map('khome-map').setView([-1.9441, 30.0619], 13); // Default Kigali
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);

    // 2. Load Properties from Database
    var dbProperties = <?php echo $json_properties; ?>;
    var orangeIcon = L.icon({
        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-orange.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34]
    });

    dbProperties.forEach(prop => {
        var marker = L.marker([prop.lat, prop.lng], {icon: orangeIcon}).addTo(map);
        
        var imgPath = prop.main_image ? 'uploads/properties/' + prop.main_image : 'https://via.placeholder.com/200';
        
        var content = `
            <div class="info-card">
                <img src="${imgPath}">
                <h4>${prop.title}</h4>
                <strong>${parseInt(prop.price).toLocaleString()} RWF</strong>
                <a href="property.php?id=${prop.property_id}" class="btn-view">View House</a>
            </div>
        `;
        marker.bindPopup(content);
    });

    // 3. AUTO-LOCATE USER ON LOAD
    // This runs immediately when page opens
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition((position) => {
            var lat = position.coords.latitude;
            var lng = position.coords.longitude;
            
            // Blue Dot for User
            var blueIcon = L.icon({
                iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41]
            });

            var userMarker = L.marker([lat, lng], {icon: blueIcon}).addTo(map);
            userMarker.bindPopup("<b>📍 You are here</b>").openPopup();
            
            // Move map to user
            map.flyTo([lat, lng], 14);

        }, () => {
            console.log("User denied location or error.");
        });
    }
</script>

<?php include 'includes/footer.php'; ?>