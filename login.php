<?php
// login.php

// 1. Start Session & Connect to DB
// We start session here because we might need to set variables if login succeeds
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'includes/db.php';

$error = '';

// 2. Handle Login Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        // Check if user exists
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // 3. Verify Password
        // password_verify() checks the plain text password against the hash in the DB
        if ($user && password_verify($password, $user['password_hash'])) {
            
            // ... inside the if(password_verify...) block ...

// SUCCESS! Store user info
$_SESSION['user_id'] = $user['user_id'];
$_SESSION['user_name'] = $user['full_name'];
$_SESSION['user_role'] = $user['user_role']; 

// TRAFFIC COP LOGIC: Send them to the right room based on their role
if ($user['user_role'] === 'admin') {
    header("Location: admin_dashboard.php"); // Create this next!
} elseif ($user['user_role'] === 'host') {
    header("Location: dashboard.php");       // The standard host dashboard
} else {
    // If a normal user somehow logs in, just send them home
    header("Location: index.php");
}
exit;
            
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="container" style="max-width: 450px; margin-top: 80px; margin-bottom: 80px;">
    <div style="background: white; padding: 40px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
        <h2 style="text-align: center; color: #D35400; margin-bottom: 30px;">Welcome Back</h2>
        
        <?php if($error): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 12px; border-radius: 5px; margin-bottom: 20px; text-align: center;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">Email Address</label>
                <input type="email" name="email" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; font-size: 16px;">
            </div>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">Password</label>
                <input type="password" name="password" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; font-size: 16px;">
            </div>
            
            <button type="submit" style="width: 100%; background: #2c3e50; color: white; padding: 14px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: bold; transition: 0.3s;">
                Login
            </button>
        </form>
        
        <div style="text-align: center; margin-top: 20px;">
            <p style="font-size: 14px; color: #666;">
                Don't have an account? <a href="register.php" style="color: #D35400; font-weight: bold;">Sign Up</a>
            </p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>