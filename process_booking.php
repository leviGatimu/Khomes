<?php
// process_booking.php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pid = $_POST['property_id'];
    $uid = $_SESSION['user_id'];
    $in = $_POST['check_in'];
    $out = $_POST['check_out'];
    $msg = $_POST['message'];

    // 1. Save Booking
    $stmt = $pdo->prepare("INSERT INTO bookings (property_id, user_id, check_in, check_out, message) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$pid, $uid, $in, $out, $msg]);

    // 2. Notify Host
    $host_stmt = $pdo->prepare("SELECT host_id, title FROM properties WHERE property_id = ?");
    $host_stmt->execute([$pid]);
    $property = $host_stmt->fetch();

    if ($property) {
        $notif_msg = "📅 NEW BOOKING REQUEST: Someone wants to stay at '{$property['title']}' from $in to $out.";
        $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)")->execute([$property['host_id'], $notif_msg]);
    }

    header("Location: manage_account.php?booking=success");
}