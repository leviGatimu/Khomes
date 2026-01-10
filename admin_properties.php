<?php
// admin_properties.php
session_start();
require_once 'includes/db.php';

// 1. SECURITY: Admins Only
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// 2. FETCH ALL PROPERTIES
// We join with 'users' to see WHO owns the property
$sql = "SELECT p.*, u.full_name, u.email 
        FROM properties p 
        JOIN users u ON p.host_id = u.user_id 
        ORDER BY p.created_at DESC";
$stmt = $pdo->query($sql);
$properties = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Listings | Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        /* Reusing your standard Admin CSS */
        * { box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: #f4f6f9; margin: 0; display: grid; grid-template-columns: 250px 1fr; min-height: 100vh; }
        
        /* Sidebar */
        .sidebar { background: #2c3e50; color: white; display: flex; flex-direction: column; padding-top: 20px; position: sticky; top: 0; height: 100vh; }
        .sidebar h2 { text-align: center; margin-bottom: 30px; }
        .sidebar a { color: #ecf0f1; padding: 15px 25px; text-decoration: none; display: block; }
        .sidebar a:hover, .sidebar a.active { background: #34495e; color: #f1c40f; border-left: 4px solid #f1c40f; }
        
        /* Content */
        .content { padding: 40px; }
        .card { background: white; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); overflow: hidden; }
        
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; color: #555; font-weight: 600; }
        tr:hover { background: #f1f1f1; }
        
        .status-active { background: #d4edda; color: #155724; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; font-weight: bold; }
        .status-draft { background: #e2e3e5; color: #383d41; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; font-weight: bold; }
        
        .btn-action { border: none; background: none; cursor: pointer; font-size: 1.1rem; margin-right: 10px; transition: 0.2s; }
        .btn-del:hover { color: #e74c3c; transform: scale(1.2); }
        .btn-edit:hover { color: #3498db; transform: scale(1.2); }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>Khomes Admin</h2>
        <a href="admin_dashboard.php"><i class="fas fa-chart-line"></i> Dashboard</a>
        <a href="admin_users.php"><i class="fas fa-users"></i> Users</a>
        <a href="admin_properties.php" class="active"><i class="fas fa-home"></i> Listings</a>
        <a href="logout.php" style="margin-top: auto; color: #e74c3c;"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <div class="content">
        <h1 style="color: #2c3e50; margin-bottom: 25px;">Manage Properties</h1>
        
        <?php if(isset($_SESSION['msg'])): ?>
            <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                <?php echo $_SESSION['msg']; unset($_SESSION['msg']); ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>Property</th>
                        <th>Host</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($properties as $p): ?>
                    <tr>
                        <td style="display: flex; gap: 10px; align-items: center;">
                            <img src="uploads/properties/<?php echo $p['main_image']; ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                            <div>
                                <div style="font-weight: bold;"><?php echo htmlspecialchars($p['title']); ?></div>
                                <div style="font-size: 0.8rem; color: #777;"><?php echo htmlspecialchars($p['district']); ?></div>
                            </div>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($p['full_name']); ?><br>
                            <small style="color:#999;"><?php echo $p['email']; ?></small>
                        </td>
                        <td><?php echo number_format($p['price']); ?> RWF</td>
                        <td>
                            <span class="<?php echo ($p['status'] == 'active') ? 'status-active' : 'status-draft'; ?>">
                                <?php echo ucfirst($p['status']); ?>
                            </span>
                        </td>
                        <td>
                            <a href="admin_actions.php?delete_property=<?php echo $p['property_id']; ?>" 
                               class="btn-action btn-del" 
                               title="Delete Listing"
                               onclick="return confirm('Delete this property? The owner will be notified.');">
                                <i class="fas fa-trash-alt"></i>
                            </a>
                            
                            <a href="property.php?id=<?php echo $p['property_id']; ?>" target="_blank" class="btn-action btn-edit" title="View/Fix">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>