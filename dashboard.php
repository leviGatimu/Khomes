<?php
// dashboard.php
session_start();
require_once 'includes/db.php'; 
require_once 'includes/header.php';

// Security Check
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] !== 'host' && $_SESSION['user_role'] !== 'admin')) {
    echo "<script>window.location.href='login.php';</script>";
    exit;
}

$user_id = $_SESSION['user_id'];

// 1. FETCH NOTIFICATIONS
$notif_stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$notif_stmt->execute([$user_id]);
$notifications = $notif_stmt->fetchAll();

// 2. FETCH BOOKING REQUESTS (New System)
$book_sql = "SELECT b.*, p.title, u.full_name as guest_name 
             FROM bookings b
             JOIN properties p ON b.property_id = p.property_id
             JOIN users u ON b.user_id = u.user_id
             WHERE p.host_id = ? AND b.status = 'pending'
             ORDER BY b.created_at DESC";
$book_stmt = $pdo->prepare($book_sql);
$book_stmt->execute([$user_id]);
$booking_requests = $book_stmt->fetchAll();

// 3. FETCH MY PROPERTIES
$sql = "SELECT * FROM properties WHERE host_id = ? ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$my_properties = $stmt->fetchAll();
?>

<div class="container" style="margin-top: 40px; margin-bottom: 60px;">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h1 style="color: var(--dark-blue); margin: 0;">Host Dashboard</h1>
        <a href="add_property.php" class="btn-highlight" style="text-decoration: none;">
            <i class="fas fa-plus"></i> List New Property
        </a>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 40px;">
        <div style="background: white; padding: 25px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border-top: 5px solid var(--primary-orange);">
            <h3 style="margin-top: 0; color: var(--dark-blue);"><i class="fas fa-bell"></i> Recent Alerts</h3>
            <?php if(count($notifications) > 0): ?>
                <ul style="list-style: none; padding: 0; margin: 0;">
                    <?php foreach($notifications as $n): ?>
                        <li style="padding: 12px 0; border-bottom: 1px solid #eee; font-size: 0.9rem;">
                            <span style="color: #444;"><?php echo htmlspecialchars($n['message']); ?></span>
                            <br><small style="color: #999;"><?php echo date('M d, H:i', strtotime($n['created_at'])); ?></small>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p style="color: #999; font-size: 0.9rem;">No new notifications.</p>
            <?php endif; ?>
        </div>

        <div style="background: white; padding: 25px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border-top: 5px solid #2980b9;">
            <h3 style="margin-top: 0; color: var(--dark-blue);"><i class="fas fa-calendar-alt"></i> Pending Bookings</h3>
            <?php if(count($booking_requests) > 0): ?>
                <?php foreach($booking_requests as $req): ?>
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 10px; margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <strong style="color: var(--dark-blue);"><?php echo htmlspecialchars($req['guest_name']); ?></strong>
                            <p style="margin: 3px 0; font-size: 0.8rem; color: #666;">Property: <?php echo htmlspecialchars($req['title']); ?></p>
                            <small style="color: var(--primary-orange); font-weight: 600;"><?php echo $req['check_in']; ?> to <?php echo $req['check_out']; ?></small>
                        </div>
                        <div style="display: flex; gap: 10px;">
                            <a href="update_booking.php?id=<?php echo $req['booking_id']; ?>&status=confirmed" style="background: #27AE60; color: white; padding: 5px 10px; border-radius: 5px; text-decoration: none; font-size: 0.75rem; font-weight: bold;">Approve</a>
                            <a href="update_booking.php?id=<?php echo $req['booking_id']; ?>&status=cancelled" style="background: #e74c3c; color: white; padding: 5px 10px; border-radius: 5px; text-decoration: none; font-size: 0.75rem; font-weight: bold;">Decline</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color: #999; font-size: 0.9rem;">No pending requests at the moment.</p>
            <?php endif; ?>
        </div>
    </div>

    <div style="background: white; padding: 30px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
        <h2 style="color: var(--dark-blue); margin-bottom: 25px; border-bottom: 2px solid #f1f1f1; padding-bottom: 15px;">My Listings</h2>

        <?php if (count($my_properties) > 0): ?>
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8f9fa; text-align: left; color: #7f8c8d; font-size: 0.85rem; text-transform: uppercase;">
                        <th style="padding: 15px; border-bottom: 2px solid #eee;">Property</th>
                        <th style="padding: 15px; border-bottom: 2px solid #eee;">Price</th>
                        <th style="padding: 15px; border-bottom: 2px solid #eee;">Status</th>
                        <th style="padding: 15px; border-bottom: 2px solid #eee;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($my_properties as $prop): ?>
                        <tr>
                            <td style="padding: 15px; border-bottom: 1px solid #eee; display: flex; align-items: center; gap: 15px;">
                                <img src="uploads/properties/<?php echo $prop['main_image']; ?>" style="width: 70px; height: 50px; object-fit: cover; border-radius: 8px;">
                                <span style="font-weight: 600; color: var(--dark-blue);"><?php echo htmlspecialchars($prop['title']); ?></span>
                            </td>
                            <td style="padding: 15px; border-bottom: 1px solid #eee; font-weight: bold; color: #27AE60;">
                                <?php echo number_format($prop['price']); ?> RWF
                            </td>
                            <td style="padding: 15px; border-bottom: 1px solid #eee;">
                                <span style="padding: 5px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: bold; 
                                    background: <?php echo ($prop['status'] == 'active') ? '#d4edda' : '#eee'; ?>; 
                                    color: <?php echo ($prop['status'] == 'active') ? '#155724' : '#777'; ?>;">
                                    <?php echo ucfirst($prop['status']); ?>
                                </span>
                            </td>
                            <td style="padding: 15px; border-bottom: 1px solid #eee;">
                                <a href="property.php?id=<?php echo $prop['property_id']; ?>" style="color: var(--primary-orange); text-decoration: none; font-weight: bold;"><i class="fas fa-eye"></i> View</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div style="text-align: center; padding: 40px; color: #7f8c8d;">
                <p>You haven't listed any properties yet.</p>
                <a href="add_property.php" style="color: var(--primary-orange); font-weight: bold; text-decoration: none;">Create your first listing</a>
            </div>
        <?php endif; ?>
    </div>

</div>

<?php include 'includes/footer.php'; ?>