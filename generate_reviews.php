<?php
// generate_reviews.php
session_start();
require_once 'includes/db.php';

// 1. Fetch all properties that aren't drafts
$stmt = $pdo->query("SELECT property_id FROM properties WHERE status = 'active'");
$properties = $stmt->fetchAll(PDO::FETCH_COLUMN);

// 2. Fetch some user IDs to act as reviewers (excluding the hosts we created)
$user_stmt = $pdo->query("SELECT user_id FROM users LIMIT 10");
$reviewer_ids = $user_stmt->fetchAll(PDO::FETCH_COLUMN);

$comments = [
    "Amazing place! The view was incredible and the host was very helpful.",
    "Very clean and modern. I highly recommend this for anyone visiting Kigali.",
    "Great location, close to everything. The WiFi was fast and reliable.",
    "The house was even better than the photos. Very secure neighborhood.",
    "Beautifully furnished and very comfortable beds. Will stay again!",
    "Excellent value for money. The host was very responsive to our needs.",
    "A perfect stay! Everything was handled professionally.",
    "Sparkling clean and very peaceful. 5 stars!"
];

$count = 0;
foreach ($properties as $pid) {
    // Add 2-3 random reviews per property
    $num_reviews = rand(2, 3);
    for ($i = 0; $i < $num_reviews; $i++) {
        $user_id = $reviewer_ids[array_rand($reviewer_ids)];
        $rating = rand(4, 5); // Keep ratings high for the "Top Rated" section
        $comment = $comments[array_rand($comments)];
        
        $ins = $pdo->prepare("INSERT INTO reviews (property_id, user_id, rating, comment, created_at) VALUES (?, ?, ?, ?, NOW())");
        $ins->execute([$pid, $user_id, $rating, $comment]);
        $count++;
    }
}

echo "Successfully generated $count reviews for " . count($properties) . " properties!";
echo "<br><a href='index.php'>Go to Home Page</a>";