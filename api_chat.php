<?php
// api_chat.php
session_start();
require_once 'includes/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

$my_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

// --- 1. SEND MESSAGE & NOTIFICATION ---
if ($action === 'send_msg') {
    $receiver_id = (int)$_POST['receiver_id'];
    $text = trim($_POST['message']);
    
    if (!empty($text)) {
        // A. Insert Message
        $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
        $stmt->execute([$my_id, $receiver_id, $text]);

        // B. Insert Notification for Receiver
        $sender_name = $_SESSION['full_name'] ?? 'A user'; // Fallback if session var missing
        
        // Fetch real name if needed
        if($sender_name == 'A user') {
            $n_stmt = $pdo->prepare("SELECT full_name FROM users WHERE user_id = ?");
            $n_stmt->execute([$my_id]);
            $sender_name = $n_stmt->fetchColumn();
        }

        $notif_msg = "💬 New message from " . $sender_name;
        $notif_stmt = $pdo->prepare("INSERT INTO notifications (user_id, message, is_read) VALUES (?, ?, 0)");
        $notif_stmt->execute([$receiver_id, $notif_msg]);

        echo json_encode(['status' => 'success']);
    }
}

// --- 2. FETCH CONVERSATION ---
if ($action === 'get_msgs') {
    $partner_id = (int)$_POST['partner_id'];
    
    // Mark these messages as read
    $read_stmt = $pdo->prepare("UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ?");
    $read_stmt->execute([$partner_id, $my_id]);

    $sql = "SELECT m.*, 
            CASE WHEN m.sender_id = ? THEN 'me' ELSE 'them' END as type
            FROM messages m 
            WHERE (m.sender_id = ? AND m.receiver_id = ?) 
            OR (m.sender_id = ? AND m.receiver_id = ?) 
            ORDER BY m.created_at ASC";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$my_id, $my_id, $partner_id, $partner_id, $my_id]);
    $msgs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['status' => 'success', 'data' => $msgs]);
}

// --- 3. FETCH CONTACT LIST ---
if ($action === 'get_list') {
    $sql = "SELECT u.user_id, u.full_name, u.profile_image, u.user_role,
            (SELECT message FROM messages WHERE (sender_id = u.user_id AND receiver_id = ?) OR (sender_id = ? AND receiver_id = u.user_id) ORDER BY created_at DESC LIMIT 1) as last_msg,
            (SELECT COUNT(*) FROM messages WHERE sender_id = u.user_id AND receiver_id = ? AND is_read = 0) as unread
            FROM users u
            WHERE u.user_id IN (
                SELECT sender_id FROM messages WHERE receiver_id = ?
                UNION
                SELECT receiver_id FROM messages WHERE sender_id = ?
            )";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$my_id, $my_id, $my_id, $my_id, $my_id]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach($users as &$u) {
        if(empty($u['profile_image']) || !file_exists('uploads/users/'.$u['profile_image'])){
            $u['profile_image'] = "https://ui-avatars.com/api/?name=".urlencode($u['full_name'])."&background=random&color=fff";
        } else {
            $u['profile_image'] = 'uploads/users/'.$u['profile_image'];
        }
    }
    
    echo json_encode(['status' => 'success', 'data' => $users]);
}

// --- 4. NEW: CHECK TOTAL UNREAD MESSAGES (For Bubble Badge) ---
if ($action === 'check_unread') {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND is_read = 0");
    $stmt->execute([$my_id]);
    $count = $stmt->fetchColumn();
    echo json_encode(['status' => 'success', 'count' => $count]);
}

// --- 5. AI CHAT ---
if ($action === 'ai_chat') {
    $q = strtolower(trim($_POST['message']));
    $reply = "I'm not sure about that. Try contacting support.";
    if (strpos($q, 'hi') !== false) $reply = "Hello! 👋 How can I help?";
    elseif (strpos($q, 'list') !== false) $reply = "Go to Dashboard > List Property.";
    elseif (strpos($q, 'price') !== false) $reply = "It is free to list!";
    
    echo json_encode(['status' => 'success', 'reply' => $reply]);
}
?>