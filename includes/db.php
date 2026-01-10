<?php
// /includes/db.php

// Database Credentials
$host = 'localhost';
$db_name = 'rwanda_rental_db'; // This must match the database name we created
$username = 'root';            // Default XAMPP/WAMP username
$password = '';                // Default XAMPP/WAMP password (leave empty)

try {
    // 1. Create the connection
    $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $username, $password);

    // 2. Set error options (Helpful for debugging)
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Connection is successful if code reaches here!
    
} catch(PDOException $e) {
    // 3. If connection fails, stop everything and show the error
    die("Connection failed: " . $e->getMessage());
}

?>