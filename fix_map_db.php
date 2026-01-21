<?php
// fix_map_db.php
require_once 'includes/db.php';

echo "<h2>🗺️ Map Database Repair</h2>";

try {
    // Check if 'lat' column exists
    $check = $pdo->query("SHOW COLUMNS FROM properties LIKE 'lat'");
    
    if ($check->rowCount() == 0) {
        // Add both lat and lng columns
        $sql = "ALTER TABLE properties 
                ADD COLUMN lat DECIMAL(10, 8) NULL,
                ADD COLUMN lng DECIMAL(11, 8) NULL";
        
        $pdo->exec($sql);
        echo "<h3 style='color:green'>✅ Success! Added 'lat' and 'lng' columns.</h3>";
        echo "<p>Your database can now store map locations.</p>";
    } else {
        echo "<h3 style='color:blue'>ℹ️ Columns already exist. You are good to go.</h3>";
    }

    echo "<br><a href='map_search.php'><strong>➜ Try the Map Page Now</strong></a>";

} catch (PDOException $e) {
    echo "<div style='color:red'>SQL Error: " . $e->getMessage() . "</div>";
}
?>