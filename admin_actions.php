<?php
// admin_actions.php
session_start();
require_once 'includes/db.php';

// 1. SECURITY: Admin Access Only
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// ==========================================
// USER MANAGEMENT ACTIONS
// ==========================================

// --- ACTION 1: VERIFY USER ---
if (isset($_GET['verify_user'])) {
    $id = $_GET['verify_user'];
    $stmt = $pdo->prepare("UPDATE users SET is_verified = 1 WHERE user_id = ?");
    
    if ($stmt->execute([$id])) {
        $_SESSION['msg'] = "User verified successfully! ✅";
    }
    header("Location: admin_users.php");
    exit;
}

// --- ACTION 2: UNVERIFY USER (THE FIX) ---
if (isset($_GET['unverify_user'])) {
    $id = $_GET['unverify_user'];
    $stmt = $pdo->prepare("UPDATE users SET is_verified = 0 WHERE user_id = ?");
    
    if ($stmt->execute([$id])) {
        $_SESSION['msg'] = "Verification badge removed. ❌";
    }
    header("Location: admin_users.php");
    exit;
}

// --- ACTION 3: MAKE ADMIN ---
if (isset($_GET['make_admin'])) {
    $id = $_GET['make_admin'];
    $stmt = $pdo->prepare("UPDATE users SET user_role = 'admin' WHERE user_id = ?");
    
    if ($stmt->execute([$id])) {
        $_SESSION['msg'] = "User promoted to Admin! 🛡️";
    }
    header("Location: admin_users.php");
    exit;
}

// --- ACTION 4: DELETE USER ---
if (isset($_GET['delete_user'])) {
    $id = $_GET['delete_user'];
    
    // Prevent deleting yourself
    if ($id == $_SESSION['user_id']) {
        $_SESSION['msg'] = "You cannot delete your own account.";
        header("Location: admin_users.php");
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
    if ($stmt->execute([$id])) {
        $_SESSION['msg'] = "User deleted successfully.";
    }
    header("Location: admin_users.php");
    exit;
}

// --- ACTION 5: SEND NOTIFICATION (Modal) ---
if (isset($_POST['send_notification'])) {
    $user_id = $_POST['user_id'];
    $message = trim($_POST['message']);

    if (!empty($message)) {
        $stmt = $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
        $stmt->execute([$user_id, $message]);
        $_SESSION['msg'] = "Message sent successfully! 📩";
    }
    header("Location: admin_users.php");
    exit;
}


// ==========================================
// PROPERTY ACTIONS
// ==========================================

// --- ACTION 6: DELETE PROPERTY ---
if (isset($_GET['delete_property'])) {
    $prop_id = $_GET['delete_property'];

    // 1. Get Owner ID and Title (for notification)
    $stmt = $pdo->prepare("SELECT host_id, title FROM properties WHERE property_id = ?");
    $stmt->execute([$prop_id]);
    $prop = $stmt->fetch();

    if ($prop) {
        $owner_id = $prop['host_id'];
        $prop_title = $prop['title'];

        // 2. Delete the property
        $del = $pdo->prepare("DELETE FROM properties WHERE property_id = ?");
        $del->execute([$prop_id]);

        // 3. Send Notification to Owner
        $msg = "⚠️ Your listing '$prop_title' was removed by an administrator. Please check our guidelines.";
        $notify = $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
        $notify->execute([$owner_id, $msg]);

        $_SESSION['msg'] = "Property deleted and owner notified.";
    }
    
    header("Location: admin_properties.php");
    exit;
}

// If no valid action found, go back to dashboard
header("Location: admin_dashboard.php");
exit;
?>