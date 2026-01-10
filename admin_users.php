<?php
// admin_users.php
session_start();
require_once 'includes/db.php';

// SECURITY: Only allow those with has_admin_access to be here
if (!isset($_SESSION['has_admin_access']) || $_SESSION['has_admin_access'] != 1) {
    header("Location: index.php");
    exit;
}

$msg = "";

// 1. HANDLE ROLE UPDATE (Guest/Host/Admin)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_role'])) {
    $target_user_id = $_POST['user_id'];
    $new_role = $_POST['role'];
    $allowed_roles = ['guest', 'host', 'admin'];
    
    if (in_array($new_role, $allowed_roles)) {
        $stmt = $pdo->prepare("UPDATE users SET user_role = ? WHERE user_id = ?");
        $stmt->execute([$new_role, $target_user_id]);
        
        $notif_msg = "🎊 Your account role has been updated to: " . ucfirst($new_role);
        $pdo->prepare("INSERT INTO notifications (user_id, message, is_read) VALUES (?, ?, 0)")->execute([$target_user_id, $notif_msg]);
        $msg = "Role updated successfully.";
    }
}

// 2. HANDLE ADMIN ACCESS TOGGLE (New Logic)
if (isset($_GET['toggle_admin'])) {
    $uid = $_GET['toggle_admin'];
    $current = $_GET['current'];
    $new_val = ($current == 1) ? 0 : 1;
    
    $pdo->prepare("UPDATE users SET has_admin_access = ? WHERE user_id = ?")->execute([$new_val, $uid]);
    
    $status_text = ($new_val == 1) ? "granted" : "revoked";
    $msg = "Admin access $status_text successfully.";
}

// 3. FETCH ALL USERS (Included has_admin_access)
$users = $pdo->query("SELECT user_id, full_name, email, user_role, has_admin_access, created_at FROM users ORDER BY created_at DESC")->fetchAll();

include 'includes/header.php';
?>

<div class="container" style="margin-top: 50px; margin-bottom: 100px;">
    <h2 style="color: #2c3e50; margin-bottom: 30px;">👥 User & Permission Management</h2>

    <?php if($msg): ?>
        <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <?php echo $msg; ?>
        </div>
    <?php endif; ?>

    <table style="width: 100%; border-collapse: collapse; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
        <thead style="background: #2c3e50; color: white;">
            <tr>
                <th style="padding: 15px; text-align: left;">Full Name</th>
                <th style="padding: 15px; text-align: left;">Account Role</th>
                <th style="padding: 15px; text-align: center;">Admin Panel Access</th>
                <th style="padding: 15px; text-align: center;">Update Role</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 15px;">
                        <strong><?php echo htmlspecialchars($u['full_name']); ?></strong><br>
                        <small style="color: #666;"><?php echo htmlspecialchars($u['email']); ?></small>
                    </td>
                    <td style="padding: 15px;">
                        <span class="role-badge role-<?php echo $u['user_role']; ?>">
                            <?php echo ucfirst($u['user_role']); ?>
                        </span>
                    </td>
                    <td style="padding: 15px; text-align: center;">
                        <?php if($u['has_admin_access']): ?>
                            <div style="color: #27AE60; font-weight: bold; margin-bottom: 5px;">
                                <i class="fas fa-user-shield"></i> Authorized
                            </div>
                            <a href="admin_users.php?toggle_admin=<?php echo $u['user_id']; ?>&current=1" 
                               style="font-size: 0.75rem; color: #e74c3c; text-decoration: none; border: 1px solid #e74c3c; padding: 2px 8px; border-radius: 4px;">
                               Revoke Access
                            </a>
                        <?php else: ?>
                            <div style="color: #95a5a6; margin-bottom: 5px;">No Access</div>
                            <a href="admin_users.php?toggle_admin=<?php echo $u['user_id']; ?>&current=0" 
                               style="font-size: 0.75rem; color: #27AE60; text-decoration: none; border: 1px solid #27AE60; padding: 2px 8px; border-radius: 4px;">
                               Grant Access
                            </a>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 15px; text-align: center;">
                        <form method="POST" style="display: flex; gap: 5px; justify-content: center;">
                            <input type="hidden" name="user_id" value="<?php echo $u['user_id']; ?>">
                            <select name="role" style="padding: 5px; border-radius: 4px; border: 1px solid #ddd; font-size: 0.85rem;">
                                <option value="guest" <?php echo ($u['user_role'] == 'guest') ? 'selected' : ''; ?>>Guest</option>
                                <option value="host" <?php echo ($u['user_role'] == 'host') ? 'selected' : ''; ?>>Host</option>
                                <option value="admin" <?php echo ($u['user_role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                            </select>
                            <button type="submit" name="update_role" style="background: #2c3e50; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; font-size: 0.8rem;">
                                Set
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<style>
    .role-badge { padding: 4px 8px; border-radius: 4px; color: white; font-size: 0.7rem; font-weight: bold; text-transform: uppercase; }
    .role-admin { background: #2c3e50; }
    .role-host { background: #27AE60; }
    .role-guest { background: #95a5a6; }
</style>

<?php include 'includes/footer.php'; ?>