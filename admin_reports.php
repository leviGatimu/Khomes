<?php
// admin_reports.php
session_start();
require_once 'includes/db.php';

// SECURITY: Only allow Admins
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$msg = "";

// 1. HANDLE ACTIONS (Fixed Undefined Key Warnings)
if (isset($_GET['action']) && isset($_GET['report_id'])) {
    $report_id = $_GET['report_id'];
    
    // Safely get IDs only if they exist in the URL
    $prop_id = $_GET['property_id'] ?? null;
    $host_id = $_GET['host_id'] ?? null;

    if ($_GET['action'] === 'dismiss') {
        $pdo->prepare("DELETE FROM reports WHERE report_id = ?")->execute([$report_id]);
        $msg = "Report dismissed.";
    } 
    elseif ($_GET['action'] === 'terminate' && $prop_id) {
        $pdo->prepare("DELETE FROM properties WHERE property_id = ?")->execute([$prop_id]);
        $warn = "🚫 TERMINATION NOTICE: Your listing has been permanently removed due to community reports.";
        $pdo->prepare("INSERT INTO notifications (user_id, message, is_read) VALUES (?, ?, 0)")->execute([$host_id, $warn]);
        $pdo->prepare("DELETE FROM reports WHERE property_id = ?")->execute([$prop_id]);
        $msg = "Listing terminated successfully.";
    }
    // --- NEW: BAN USER LOGIC ---
    elseif ($_GET['action'] === 'ban' && $host_id) {
        // Delete all properties by this host
        $pdo->prepare("DELETE FROM properties WHERE host_id = ?")->execute([$host_id]);
        // Delete the user themselves
        $pdo->prepare("DELETE FROM users WHERE user_id = ?")->execute([$host_id]);
        // Clean up remaining reports linked to this host
        $pdo->prepare("DELETE FROM reports WHERE property_id NOT IN (SELECT property_id FROM properties)")->execute();
        $msg = "User and all their listings have been permanently banned.";
    }
}

// 2. FETCH ALL REPORTS
$sql = "SELECT r.*, p.title, p.host_id, u.full_name as reporter 
        FROM reports r 
        JOIN properties p ON r.property_id = p.property_id 
        JOIN users u ON r.user_id = u.user_id 
        ORDER BY r.created_at DESC";
$reports = $pdo->query($sql)->fetchAll();

include 'includes/header.php';
?>

<div class="container" style="margin-top: 50px; margin-bottom: 100px;">
    <h2 style="color: #2c3e50; margin-bottom: 30px;">🚩 Property Reports Management</h2>

    <?php if($msg): ?>
        <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <?php echo $msg; ?>
        </div>
    <?php endif; ?>

    <table style="width: 100%; border-collapse: collapse; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
        <thead style="background: #2c3e50; color: white;">
            <tr>
                <th style="padding: 15px; text-align: left;">Property</th>
                <th style="padding: 15px; text-align: left;">Reporter</th>
                <th style="padding: 15px; text-align: left;">Reason</th>
                <th style="padding: 15px; text-align: left;">Date</th>
                <th style="padding: 15px; text-align: center;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($reports as $rep): ?>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 15px;">
                        <a href="property.php?id=<?php echo $rep['property_id']; ?>" target="_blank" style="font-weight: bold; color: #2980b9;">
                            <?php echo htmlspecialchars($rep['title']); ?>
                        </a>
                    </td>
                    <td style="padding: 15px; color: #555;"><?php echo htmlspecialchars($rep['reporter']); ?></td>
                    <td style="padding: 15px; color: #e74c3c; font-style: italic;">"<?php echo htmlspecialchars($rep['reason']); ?>"</td>
                    <td style="padding: 15px; font-size: 0.85rem; color: #999;"><?php echo date('M d, Y', strtotime($rep['created_at'])); ?></td>
                    <td style="padding: 15px; text-align: center;">
                        <div style="display: flex; gap: 5px; justify-content: center; flex-wrap: wrap;">
                            <a href="admin_reports.php?action=dismiss&report_id=<?php echo $rep['report_id']; ?>" 
                               style="background: #95a5a6; color: white; padding: 5px 8px; border-radius: 4px; text-decoration: none; font-size: 0.75rem;">
                               Dismiss
                            </a>
                            <a href="admin_reports.php?action=terminate&report_id=<?php echo $rep['report_id']; ?>&property_id=<?php echo $rep['property_id']; ?>&host_id=<?php echo $rep['host_id']; ?>" 
                               onclick="return confirm('Remove this listing?')"
                               style="background: #f39c12; color: white; padding: 5px 8px; border-radius: 4px; text-decoration: none; font-size: 0.75rem;">
                               Terminate
                            </a>
                            <a href="admin_reports.php?action=ban&report_id=<?php echo $rep['report_id']; ?>&host_id=<?php echo $rep['host_id']; ?>" 
                               onclick="return confirm('EXTREME ACTION: Ban this user and delete all their content?')"
                               style="background: #e74c3c; color: white; padding: 5px 8px; border-radius: 4px; text-decoration: none; font-size: 0.75rem;">
                               Ban User
                            </a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if(empty($reports)): ?>
                <tr><td colspan="5" style="padding: 40px; text-align: center; color: #999;">No active reports found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'includes/footer.php'; ?>