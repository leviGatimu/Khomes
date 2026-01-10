</main>

<style>
    /* --- MEGA FOOTER STYLES --- */
    .footer-section {
        background-color: #1a252f; /* Darker Navy */
        color: #ecf0f1;
        padding: 60px 0 20px;
        margin-top: auto;
        font-size: 0.95rem;
    }
    
    .footer-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 40px;
        margin-bottom: 40px;
    }

    .footer-col h3 {
        color: white;
        font-size: 1.2rem;
        margin-bottom: 20px;
        font-weight: 600;
        border-left: 3px solid #27AE60; /* Green Accent */
        padding-left: 10px;
    }

    .footer-col p {
        color: #bdc3c7;
        line-height: 1.6;
        margin-bottom: 15px;
    }

    .footer-links {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .footer-links li {
        margin-bottom: 12px;
    }

    .footer-links a {
        color: #bdc3c7;
        text-decoration: none;
        transition: 0.3s;
        display: inline-flex;
        align-items: center;
    }

    .footer-links a:hover {
        color: #F39C47; /* Orange Hover */
        transform: translateX(5px);
    }
    
    .footer-links i { margin-right: 8px; font-size: 0.8em; }

    .social-icons {
        display: flex;
        gap: 15px;
        margin-top: 20px;
    }

    .social-btn {
        width: 35px; height: 35px;
        background: rgba(255,255,255,0.1);
        display: flex; justify-content: center; align-items: center;
        border-radius: 50%;
        color: white;
        text-decoration: none;
        transition: 0.3s;
    }
    .social-btn:hover { background: #27AE60; transform: translateY(-3px); }

    .footer-bottom {
        border-top: 1px solid rgba(255,255,255,0.1);
        padding-top: 20px;
        text-align: center;
        color: #7f8c8d;
        font-size: 0.85rem;
    }
</style>

<footer class="footer-section">
    <div class="container">
        <div class="footer-grid">
            
            <div class="footer-col">
                <h2 style="color: white; font-weight: bold; margin-bottom: 15px;">Khomes.rw</h2>
                <p>The easiest way to find, list, and rent properties in Rwanda. Connecting hosts and guests seamlessly.</p>
                <div class="social-icons">
                    <a href="#" class="social-btn"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-btn"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="social-btn"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-btn"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>

            <div class="footer-col">
                <h3>Discover</h3>
                <ul class="footer-links">
                    <li><a href="index.php"><i class="fas fa-chevron-right"></i> Home</a></li>
                    <li><a href="search.php"><i class="fas fa-chevron-right"></i> Browse Listings</a></li>
                    <li><a href="search.php?listing_type=short_term"><i class="fas fa-chevron-right"></i> Short Term Stays</a></li>
                    <li><a href="search.php?listing_type=sale"><i class="fas fa-chevron-right"></i> Buy Property</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h3>Account</h3>
                <ul class="footer-links">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if ($_SESSION['user_role'] === 'host'): ?>
                            <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Host Dashboard</a></li>
                            <li><a href="add_property.php"><i class="fas fa-plus-circle"></i> List Property</a></li>
                        <?php else: ?>
                            <li><a href="manage_account.php"><i class="fas fa-user-circle"></i> My Profile</a></li>
                        <?php endif; ?>
                        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    <?php else: ?>
                        <li><a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                        <li><a href="register.php"><i class="fas fa-user-plus"></i> Register</a></li>
                    <?php endif; ?>
                </ul>
            </div>

            <div class="footer-col">
                <h3>Contact Us</h3>
                <ul class="footer-links">
                    <li><a href="#"><i class="fas fa-map-marker-alt"></i> Kigali City, Rwanda</a></li>
                    <li><a href="#"><i class="fas fa-phone-alt"></i> +250 788 000 000</a></li>
                    <li><a href="#"><i class="fas fa-envelope"></i> support@khomes.rw</a></li>
                </ul>
            </div>

        </div>

        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> Khomes.rw. All rights reserved. | Made in kigali/Rwanda</p>
        </div>
    </div>
</footer>

<script>
(function() {
    const loader = document.getElementById('khome-loader-overlay');
    if (!loader) return;

    // 1. Get the navigation type
    const perfEntries = performance.getEntriesByType("navigation");
    const navType = perfEntries.length > 0 ? perfEntries[0].type : "";

    // 2. Logic: ONLY show if it's a "navigate" (clicking a link or typing URL)
    // This will skip it on 'reload' (F5) and 'back_forward'
    if (navType === 'navigate') {
        loader.style.display = 'flex'; // Show it only now

        // 3. Random delay between 1s and 2s (shorter is better for UX)
        const randomDelay = Math.floor(Math.random() * (2000 - 1000 + 1) + 1000);

        setTimeout(function() {
            loader.classList.add('loader-hidden');
            setTimeout(() => loader.remove(), 500); // Clean up DOM
        }, randomDelay);
    } else {
        // If it's a refresh or back button, keep it hidden and remove it
        loader.remove();
    }
})();
</script>
<?php if(isset($_SESSION['user_id'])) include 'includes/chat_widget.php'; ?>

</body>
</html>