<?php
// admin_add_user.php
session_start();
require_once 'includes/db.php';

// 1. SECURITY: Only Admins allowed!
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $pass = $_POST['password'];
    $role = $_POST['role']; // Admin selects this!

    // Basic Validation
    if (empty($name) || empty($email) || empty($phone) || empty($pass)) {
        $error = "All fields are required.";
    } else {
        // Check if Email exists
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $error = "User with this email already exists.";
        } else {
            // Create the User
            $hashed = password_hash($pass, PASSWORD_DEFAULT);
            // Default to verified since Admin is creating it
            $sql = "INSERT INTO users (full_name, email, phone_number, password_hash, user_role, is_verified) VALUES (?, ?, ?, ?, ?, 1)";
            $stmt = $pdo->prepare($sql);
            
            if ($stmt->execute([$name, $email, $phone, $hashed, $role])) {
                $_SESSION['msg'] = "User account created successfully!";
                header("Location: admin_users.php");
                exit;
            } else {
                $error = "Database error. Could not create user.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add User | Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        /* Reusing your Admin CSS for consistency */
        body { font-family: 'Poppins', sans-serif; background: #f4f6f9; padding: 50px; display: flex; justify-content: center; }
        .form-card { background: white; padding: 40px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); width: 100%; max-width: 500px; }
        h2 { text-align: center; color: #2c3e50; margin-bottom: 25px; border-bottom: 1px solid #eee; padding-bottom: 15px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: 600; color: #555; }
        input, select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        .btn-submit { width: 100%; background: #27AE60; color: white; padding: 12px; border: none; border-radius: 5px; cursor: pointer; font-size: 1rem; font-weight: bold; margin-top: 10px; }
        .btn-submit:hover { background: #219150; }
        .btn-cancel { display: block; text-align: center; margin-top: 15px; color: #777; text-decoration: none; }
    </style>
</head>
<body>

    <div class="form-card">
        <h2>Create New User</h2>

        <?php if($error): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="full_name" required>
            </div>

            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" required>
            </div>

            <div class="form-group">
                <label>Phone Number</label>
                <input type="text" name="phone" required>
            </div>

            <div class="form-group">
                <label>Assign Role</label>
                <select name="role">
                    <option value="guest">Guest (Regular User)</option>
                    <option value="host">Host (Can list properties)</option>
                    <option value="admin" style="color: red; font-weight: bold;">Administrator</option>
                </select>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required placeholder="Create a temporary password">
            </div>

            <button type="submit" class="btn-submit">Create Account</button>
            <a href="admin_users.php" class="btn-cancel">Cancel</a>
        </form>
    </div>

</body>
</html>