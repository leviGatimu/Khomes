<?php
// add_property_map.php
session_start();
require_once 'includes/db.php';
require_once 'includes/header.php';

// Security check
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'host') {
    echo "<script>window.location.href='login.php';</script>";
    exit;
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $price = $_POST['price'];
    $lat = $_POST['lat'];
    $lng = $_POST['lng'];
    $host_id = $_SESSION['user_id'];
    
    // Simple Insert for testing
    $stmt = $pdo->prepare("INSERT INTO properties (host_id, title, price, lat, lng, status) VALUES (?, ?, ?, ?, ?, 'active')");
    $stmt->execute([$host_id, $title, $price, $lat, $lng]);
    
    echo "<script>alert('Property Listed with Location!'); window.location.href='dashboard.php';</script>";
}
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<div class="container" style="max-width: 800px; margin-top: 40px; margin-bottom: 60px;">
    <h2>📍 Pin Your Property</h2>
    <p class="text-muted">Drag the map or click to set the exact location of your property.</p>

    <form method="POST" action="">
        <div style="margin-bottom: 20px;">
            <label>Property Title</label>
            <input type="text" name="title" required class="booking-input" placeholder="e.g. Modern Apartment in Rebero">
        </div>
        
        <div style="margin-bottom: 20px;">
            <label>Price (RWF)</label>
            <input type="number" name="price" required class="booking-input" placeholder="e.g. 500000">
        </div>

        <label>Set Location on Map (Click exact spot)</label>
        <div id="host-map" style="height: 400px; width: 100%; border-radius: 10px; border: 2px solid #ddd; z-index: 1;"></div>
        
        <input type="hidden" name="lat" id="lat_input" required>
        <input type="hidden" name="lng" id="lng_input" required>

        <p id="status-txt" style="color: var(--primary-orange); font-weight: bold; margin-top: 10px;">
            <i class="fas fa-map-marker-alt"></i> No location selected yet.
        </p>

        <button type="submit" class="btn-highlight" style="width: 100%; border: none; margin-top: 20px;">
            Submit Property
        </button>
    </form>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    // 1. Initialize Map (Center on Kigali)
    var map = L.map('host-map').setView([-1.9441, 30.0619], 13);
    
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap'
    }).addTo(map);

    var marker;
    var orangeIcon = L.icon({
        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-orange.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41]
    });

    // 2. Click to Pin Function
    map.on('click', function(e) {
        var lat = e.latlng.lat;
        var lng = e.latlng.lng;

        // Remove old marker if exists
        if (marker) {
            map.removeLayer(marker);
        }

        // Add new marker
        marker = L.marker([lat, lng], {icon: orangeIcon}).addTo(map);

        // Update Hidden Inputs
        document.getElementById('lat_input').value = lat;
        document.getElementById('lng_input').value = lng;
        
        // Update Status Text
        document.getElementById('status-txt').innerHTML = "✅ Location Set: " + lat.toFixed(5) + ", " + lng.toFixed(5);
    });
</script>

<?php include 'includes/footer.php'; ?>