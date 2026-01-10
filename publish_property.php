<?php
// publish_property.php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$prop_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// 1. Verify Ownership (Security)
$stmt = $pdo->prepare("SELECT host_id FROM properties WHERE property_id = ?");
$stmt->execute([$prop_id]);
$prop = $stmt->fetch();

if ($prop && $prop['host_id'] == $user_id) {
    // 2. Publish it!
    $update = $pdo->prepare("UPDATE properties SET status = 'active' WHERE property_id = ?");
    $update->execute([$prop_id]);
    
    // 3. Go to Dashboard or the Live Page
    $_SESSION['msg'] = "Success! Your property is now live.";
    header("Location: dashboard.php");
    exit;
} else {
    die("Unauthorized access.");
}
?>