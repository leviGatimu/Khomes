<?php
// submit_review.php
session_start();
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $prop_id = $_POST['property_id'];
    $user_id = $_SESSION['user_id'];
    $rating = $_POST['rating'];
    $comment = trim($_POST['comment']);

    // Check if user already reviewed this property? (Optional, but good practice)
    $check = $pdo->prepare("SELECT review_id FROM reviews WHERE property_id = ? AND user_id = ?");
    $check->execute([$prop_id, $user_id]);
    
    if ($check->rowCount() > 0) {
        // Update existing review
        $stmt = $pdo->prepare("UPDATE reviews SET rating = ?, comment = ?, created_at = CURRENT_TIMESTAMP WHERE property_id = ? AND user_id = ?");
        $stmt->execute([$rating, $comment, $prop_id, $user_id]);
    } else {
        // Insert new review
        $stmt = $pdo->prepare("INSERT INTO reviews (property_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
        $stmt->execute([$prop_id, $user_id, $rating, $comment]);
    }

    header("Location: property.php?id=$prop_id");
    exit;
} else {
    header("Location: login.php");
    exit;
}