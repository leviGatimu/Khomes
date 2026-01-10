<?php
// /includes/header.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/db.php'; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Khomes | Rwanda's Best Rentals</title>
    
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <style>
        body { margin: 0; padding: 0; }
        
        /* NOTIFICATION STYLES */
        .notif-wrapper { position: relative; margin-right: 15px; }
        
        .notif-btn {
            color: #2c3e50; font-size: 1.2rem; cursor: pointer; position: relative;
            transition: 0.3s;
        }
        .notif-btn:hover { color: #27AE60; }
        
        .notif-badge {
            position: absolute; top: -8px; right: -8px;
            background: #e74c3c; color: white;
            font-size: 0.7rem; font-weight: bold;
            padding: 2px 6px; border-radius: 50%;
            border: 2px solid white;
            transition: opacity 0.3s;
        }

        /* Dropdown Styles */
        .notif-dropdown {
            display: none; 
            position: absolute; top: 40px; right: -10px;
            width: 300px; background: white;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            border-radius: 8px; overflow: hidden; z-index: 1000;
            border: 1px solid #eee;
        }
        .notif-dropdown.active { display: block; animation: fadeIn 0.3s; }
        
        .notif-header { background: #f8f9fa; padding: 10px 15px; font-weight: bold; font-size: 0.9rem; border-bottom: 1px solid #eee; color: #555; }
        
        .notif-list { max-height: 300px; overflow-y: auto; padding: 0; margin: 0; list-style: none; }
        
        .notif-item {
            padding: 12px 15px; border-bottom: 1px solid #f1f1f1;
            font-size: 0.85rem; color: #333; transition: 0.2s;
            cursor: pointer; display: block; text-decoration: none;
        }
        .notif-item:hover { background: #f9fffb; }
        
        /* Unread style: Light Blue background */
        .notif-item.unread { 
            background: #e3f2fd; 
            border-left: 4px solid #2980b9; 
            color: #000;
            font-weight: 600;
        }

        /* Loader Styles */
        #khome-loader-overlay { 
        position: fixed; 
        top: 0; 
        left: 0; 
        width: 100%; 
        height: 100%; 
        background-color: #F39C47; 
        z-index: 2147483647; 
        display: none; /* <--- CHANGE THIS TO NONE */
        justify-content: center; 
        align-items: center; 
        transition: opacity 0.5s ease-out, visibility 0.5s; 
        }
        #khome-loader-overlay.loader-hidden { opacity: 0; visibility: hidden; }
        .loader-content { text-align: center; }
        .bubbly-k { font-size: 140px; color: white; font-family: 'Segoe UI', sans-serif; font-weight: 900; line-height: 1; animation: slickPulse 2s infinite ease-in-out; }
        .brand-text { color: #1D1D35; font-size: 36px; font-weight: 600; font-family: 'Poppins', sans-serif; margin-top: -10px; animation: fadeIn 1.5s ease-in-out; }
        @keyframes slickPulse { 0% { transform: scale(1); text-shadow: 0 0 0 rgba(255,255,255,0); } 50% { transform: scale(1.08); text-shadow: 0 0 20px rgba(255,255,255,0.4); } 100% { transform: scale(1); text-shadow: 0 0 0 rgba(255,255,255,0); } }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    </style>
</head>
<body>

<div id="khome-loader-overlay">
    <div class="loader-content">
        <div class="bubbly-k">K</div>
        <div class="brand-text">Khome</div>
    </div>
</div>

<nav class="navbar">
    <div class="container nav-container">
        <a href="index.php" class="logo">Khomes.rw</a>
        
        <ul class="nav-links">
            <li><a href="index.php">Home</a></li>
            <li><a href="search.php">Find a Home</a></li>
            
            <?php 
            if (isset($_SESSION['user_id'])): 
                $uid = $_SESSION['user_id'];

                // FETCH UNREAD COUNT & LIST
                $count_stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
                $count_stmt->execute([$uid]);
                $unread_count = $count_stmt->fetchColumn();

                $notif_list_stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
                $notif_list_stmt->execute([$uid]);
                $my_notifs = $notif_list_stmt->fetchAll();

                // AVATAR CHECK
                $u_img = 'assets/images/default_user.png'; 
                if (isset($_SESSION['profile_image']) && !empty($_SESSION['profile_image'])) {
                    $check_path = 'uploads/users/' . $_SESSION['profile_image'];
                    if (file_exists($check_path)) {
                        $u_img = $check_path;
                    }
                }
            ?>
                
                <li class="notif-wrapper">
                    <div class="notif-btn" onclick="toggleNotifications()">
                        <i class="fas fa-bell"></i>
                        <?php if($unread_count > 0): ?>
                            <span class="notif-badge" id="notifBadge"><?php echo $unread_count; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="notif-dropdown" id="notifDropdown">
                        <div class="notif-header">Recent Notifications</div>
                        <ul class="notif-list" id="notifList">
                            <?php if(count($my_notifs) > 0): ?>
                                <?php foreach($my_notifs as $note): ?>
                                    <li class="notif-item <?php echo ($note['is_read'] == 0) ? 'unread' : ''; ?>">
                                        <?php echo htmlspecialchars($note['message']); ?>
                                        <span class="notif-date">
                                            <?php echo date('M d, H:i', strtotime($note['created_at'])); ?>
                                        </span>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li class="notif-empty">No notifications yet.</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </li>
                             
                <?php if ($_SESSION['user_role'] === 'admin'): ?>
                <li><i class="fas fa-chart-line"></i><a href="admin_dashboard.php">Dashboard</a></li>
                <?php elseif ($_SESSION['user_role'] === 'host'): ?>
                <li><a href="dashboard.php">Dashboard</a></li>
                <?php else: ?>
                <li><a href="manage_account.php">Manage Account</a></li>
                <?php endif; ?>

               <?php if ($_SESSION['user_role'] === 'host'): ?>
                <?php elseif ($_SESSION['user_role'] === 'guest'): ?>
                    <li><a href="manage_account.php"><i class="fas fa-user-circle"></i> My Account</a></li>
                <?php endif; ?>

                <?php if (isset($_SESSION['has_admin_access']) && $_SESSION['has_admin_access'] == 1): ?>
                    <li><a href="admin_dashboard.php" style="color: var(--primary-orange); font-weight: bold;"><i class="fas fa-shield-alt"></i> Admin Panel</a></li>
                <?php endif; ?>
                
                <li style="margin-left: 10px;">
                    <a href="manage_account.php" style="padding: 0; display: flex; align-items: center;">
                        <img src="<?php echo $u_img; ?>" 
                             style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #ddd;" 
                             alt="Profile">
                    </a>
                </li>

                <li><a href="logout.php" class="btn-logout" style="color: #c0392b;">Logout</a></li>

            <?php else: ?>
                <li><a href="register.php" style="font-weight: bold; color: #2c3e50;">Sign Up</a></li>
                <li><a href="login.php" class="btn-signup">Login</a></li>

            <?php endif; ?>
        </ul>
    </div>
</nav>

<script>
    // Toggle Notification Dropdown
    function toggleNotifications() {
        var dropdown = document.getElementById("notifDropdown");
        var badge = document.getElementById("notifBadge");
        
        dropdown.classList.toggle("active");

        if (dropdown.classList.contains("active")) {
            if(badge) badge.style.display = 'none';
            var unreadItems = document.querySelectorAll('.notif-item.unread');
            unreadItems.forEach(function(item) {
                item.classList.remove('unread');
                item.style.background = 'white';
                item.style.borderLeft = 'none';
            });
            fetch('mark_read.php');
        }

        document.addEventListener('click', function(event) {
            var isClickInside = dropdown.contains(event.target) || event.target.closest('.notif-btn');
            if (!isClickInside && dropdown.classList.contains('active')) {
                dropdown.classList.remove('active');
            }
        }, { once: true });
    }

    // Function to start chat (Used by Property and Profile pages)
    function startChatWith(userId, userName) {
        if (typeof toggleChatWindow === "function") {
            const win = document.getElementById('chat-window');
            win.style.display = 'flex';
            
            // Switch to DM Tab (Using logic from chat_widget.php)
            document.querySelectorAll('.chat-tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.chat-body').forEach(b => b.classList.remove('active'));
            document.getElementById('tab-dm').classList.add('active');
            
            // Call the open function from chat_widget.php
            openDm(userId, userName);
        } else {
            // If user isn't logged in, redirect to login
            window.location.href = 'login.php';
        }
    }
</script>

<main>