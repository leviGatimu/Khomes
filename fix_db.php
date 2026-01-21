<?php
// fix_all.php - MASTER REPAIR TOOL
// 1. Safe Include
$db_file = __DIR__ . '/includes/db.php';
if (!file_exists($db_file)) {
    die("❌ Error: Could not find 'includes/db.php'. Please create it first.");
}
require_once $db_file;

echo "<h2>🛠️ Master Database Repair Tool</h2>";

try {
    // ======================================================
    // 1. FIX USERS TABLE
    // ======================================================
    echo "<h3>1. Checking Users...</h3>";
    
    // Add 'full_name'
    $check_name = $pdo->query("SHOW COLUMNS FROM users LIKE 'full_name'");
    if ($check_name->rowCount() == 0) {
        $pdo->exec("ALTER TABLE users ADD COLUMN full_name VARCHAR(100) AFTER email");
        echo "✅ Added 'full_name' column.<br>";
        // Fill it
        $pdo->exec("UPDATE users SET full_name = SUBSTRING_INDEX(email, '@', 1) WHERE full_name IS NULL OR full_name = ''");
        echo "✅ Populated 'full_name'.<br>";
    }

    // Add 'profile_image'
    $check_img = $pdo->query("SHOW COLUMNS FROM users LIKE 'profile_image'");
    if ($check_img->rowCount() == 0) {
        $pdo->exec("ALTER TABLE users ADD COLUMN profile_image VARCHAR(255) DEFAULT 'default_user.png'");
        echo "✅ Added 'profile_image' column.<br>";
    }

    // ======================================================
    // 2. FIX PROPERTIES TABLE
    // ======================================================
    echo "<h3>2. Checking Properties...</h3>";
    
    // Rename 'id' -> 'property_id'
    $check_pid = $pdo->query("SHOW COLUMNS FROM properties LIKE 'property_id'");
    if ($check_pid->rowCount() == 0) {
        $check_id = $pdo->query("SHOW COLUMNS FROM properties LIKE 'id'");
        if ($check_id->rowCount() > 0) {
            $pdo->exec("ALTER TABLE properties CHANGE id property_id INT(11) AUTO_INCREMENT");
            echo "✅ Renamed 'id' to 'property_id'.<br>";
        }
    }

    // ======================================================
    // 3. FIX REVIEWS TABLE (The error you just got)
    // ======================================================
    echo "<h3>3. Checking Reviews...</h3>";
    
    // Rename 'id' -> 'review_id'
    $check_rid = $pdo->query("SHOW COLUMNS FROM reviews LIKE 'review_id'");
    if ($check_rid->rowCount() == 0) {
        $check_id = $pdo->query("SHOW COLUMNS FROM reviews LIKE 'id'");
        if ($check_id->rowCount() > 0) {
            $pdo->exec("ALTER TABLE reviews CHANGE id review_id INT(11) AUTO_INCREMENT");
            echo "✅ Renamed 'id' to 'review_id'.<br>";
        }
    }
    
    // Fix foreign keys in reviews just in case
    $check_rpid = $pdo->query("SHOW COLUMNS FROM reviews LIKE 'property_id'");
    if ($check_rpid->rowCount() == 0) {
         echo "⚠️ Warning: 'reviews' table missing 'property_id'.<br>";
    }

    echo "<br><h1 style='color:green'>✅ REPAIRS COMPLETE.</h1>";
    echo "<a href='index.php' style='font-size:20px; font-weight:bold;'>Go to Home Page ➜</a>";

} catch (PDOException $e) {
    echo "<div style='color:red; border:1px solid red; padding:10px;'>SQL Error: " . $e->getMessage() . "</div>";
}
?>