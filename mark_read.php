<?php
// mark_read.php
session_start();
require_once 'includes/db.php';

// Only logged-in users can do this
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    
    // Update database: Mark all unread messages as read (1)
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
    $stmt->execute([$user_id]);
}
?>