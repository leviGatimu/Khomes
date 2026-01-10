<?php
session_start();
require_once 'includes/db.php';

if (isset($_GET['id']) && isset($_SESSION['user_id'])) {
    $prop_id = $_GET['id'];
    $user_id = $_SESSION['user_id'];

    // Delete the draft
    $stmt = $pdo->prepare("DELETE FROM properties WHERE property_id = ? AND host_id = ?");
    $stmt->execute([$prop_id, $user_id]);

    header("Location: add_property.php"); // Send them back to start over
}
?>