<?php
// admin_dashboard.php
session_start();
require_once 'includes/db.php';

// SECURITY: Only allow Admins
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// FETCH TOTALS FOR THE CARDS
$user_count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$prop_count = $pdo->query("SELECT COUNT(*) FROM properties")->fetchColumn();
$report_count = $pdo->query("SELECT COUNT(*) FROM reports")->fetchColumn();

include 'includes/header.php';
?>

<style>
    .admin-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 25px;
        margin-top: 40px;
    }
    .admin-card {
        background: white;
        padding: 30px;
        border-radius: 15px;
        text-align: center;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        text-decoration: none;
        color: #2c3e50;
        transition: 0.3s ease;
        border: 1px solid #eee;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 15px;
    }
    .admin-card:hover {
        transform: translateY(-10px);
        border-color: #27AE60;
        box-shadow: 0 15px 30px rgba(0,0,0,0.1);
    }
    .admin-card i {
        font-size: 3rem;
        color: #27AE60;
    }
    .admin-card h3 { margin: 0; font-size: 1.4rem; }
    .admin-card p { margin: 0; color: #7f8c8d; font-size: 0.9rem; }
    .stat-number {
        background: #f8f9fa;
        padding: 5px 15px;
        border-radius: 20px;
        font-weight: bold;
        color: #2c3e50;
    }
</style>

<div class="container" style="margin-top: 50px; margin-bottom: 100px;">
    <div style="border-bottom: 2px solid #f1f1f1; padding-bottom: 20px;">
        <h1 style="margin: 0; color: #2c3e50;">Admin Control Center</h1>
        <p style="color: #7f8c8d;">Manage users, listings, and platform safety.</p>
    </div>

    <div class="admin-grid">
        <a href="admin_users.php" class="admin-card">
            <i class="fas fa-users"></i>
            <h3>Manage Users</h3>
            <p>Promote guests to hosts or manage roles.</p>
            <span class="stat-number"><?php echo $user_count; ?> Total Users</span>
        </a>

        <a href="admin_reports.php" class="admin-card">
            <i class="fas fa-flag" style="color: #e74c3c;"></i>
            <h3>Safety Reports</h3>
            <p>Review flagged properties and ban bad actors.</p>
            <span class="stat-number" style="color: #e74c3c;"><?php echo $report_count; ?> Active Reports</span>
        </a>

        <a href="search.php" class="admin-card">
            <i class="fas fa-home" style="color: #2980b9;"></i>
            <h3>View All Listings</h3>
            <p>Monitor all active properties on the site.</p>
            <span class="stat-number" style="color: #2980b9;"><?php echo $prop_count; ?> Live Listings</span>
        </a>
    </div>

    <div style="margin-top: 60px; background: #2c3e50; padding: 40px; border-radius: 15px; color: white;">
        <h3>System Quick Actions</h3>
        <div style="display: flex; gap: 15px; margin-top: 20px;">
            <a href="add_property.php" class="btn-highlight" style="text-decoration: none;">Add Official Listing</a>
            <a href="generate_reviews.php" style="color: white; text-decoration: underline; font-size: 0.9rem; opacity: 0.8;">Run Review Generator</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>