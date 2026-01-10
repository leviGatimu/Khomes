<?php
// report_property.php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['property_id'])) {
    exit("Access Denied");
}

$user_id = $_SESSION['user_id'];
$property_id = $_POST['property_id'];
$reason = trim($_POST['reason']);

// 1. Save the report
$stmt = $pdo->prepare("INSERT INTO reports (property_id, user_id, reason) VALUES (?, ?, ?)");
$stmt->execute([$property_id, $user_id, $reason]);

// 2. Fetch Host ID and Property Title
$p_stmt = $pdo->prepare("SELECT host_id, title FROM properties WHERE property_id = ?");
$p_stmt->execute([$property_id]);
$prop = $p_stmt->fetch();

if ($prop) {
    $host_id = $prop['host_id'];
    $title = $prop['title'];

    // 3. Send Notification to Host
    $notif_msg = "⚠️ WARNING: Your listing '{$title}' has been flagged for: '{$reason}'. Please review it or it may be terminated.";
    $notif_stmt = $pdo->prepare("INSERT INTO notifications (user_id, message, is_read) VALUES (?, ?, 0)");
    $notif_stmt->execute([$host_id, $notif_msg]);

    // 4. Optional: Auto-hide if reported more than 3 times
    $count_stmt = $pdo->prepare("SELECT COUNT(*) FROM reports WHERE property_id = ?");
    $count_stmt->execute([$property_id]);
    if ($count_stmt->fetchColumn() >= 3) {
        $pdo->prepare("UPDATE properties SET status = 'flagged' WHERE property_id = ?")->execute([$property_id]);
    }
}

echo "success";