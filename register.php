<?php
// register.php
session_start();
require_once 'includes/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $pass = $_POST['password'];
    $confirm_pass = $_POST['confirm_password'];
    
    // HARDCODED ROLE: Everyone starts as a guest
    $role = 'guest'; 

    // Validation
    if (empty($name) || empty($email) || empty($phone) || empty($pass)) {
        $error = "All fields are required.";
    } elseif ($pass !== $confirm_pass) {
        $error = "Passwords do not match.";
    } else {
        // Check if Email exists
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $error = "Email is already registered. Please login.";
        } else {
            // Create the User (Not Verified by default)
            $hashed = password_hash($pass, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (full_name, email, phone_number, password_hash, user_role, is_verified) VALUES (?, ?, ?, ?, ?, 0)";
            $stmt = $pdo->prepare($sql);
            
            if ($stmt->execute([$name, $email, $phone, $hashed, $role])) {
                $_SESSION['msg'] = "Account created! Please login.";
                header("Location: login.php");
                exit;
            } else {
                $error = "Something went wrong. Try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Account | Khomes</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f4f6f9; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .register-card { background: white; padding: 40px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); width: 100%; max-width: 400px; text-align: center; }
        input { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        .btn-register { width: 100%; background: #2c3e50; color: white; padding: 12px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; font-size: 1rem; }
        .btn-register:hover { background: #34495e; }
        a { color: #27AE60; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>

    <div class="register-card">
        <h2 style="color: #2c3e50; margin-bottom: 5px;">Join Khomes</h2>
        <p style="color: #7f8c8d; margin-bottom: 20px;">Create an account to book your stay.</p>

        <?php if($error): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 15px; font-size: 0.9rem;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <input type="text" name="full_name" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email Address" required>
            <input type="text" name="phone" placeholder="Phone Number" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>

            <input type="hidden" name="user_role" value="guest">

            <button type="submit" class="btn-register">Create Account</button>
        </form>

        <p style="margin-top: 20px; font-size: 0.9rem;">
            Already have an account? <a href="login.php">Login</a>
        </p>
    </div>

</body>
</html>