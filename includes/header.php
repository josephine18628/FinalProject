<?php
/**
 * Common Header Component
 * CS3 Quiz Platform
 */
?>
<header class="main-header">
    <nav class="navbar">
        <div class="navbar-container">
            <div class="navbar-brand">
                <a href="dashboard.php">CS3 Quiz Platform</a>
            </div>
            
            <?php if (isLoggedIn()): ?>
                <div class="navbar-menu">
                    <a href="dashboard.php" class="nav-link">Dashboard</a>
                    <div class="navbar-user">
                        <span class="user-name"><?php echo htmlspecialchars(getCurrentUsername()); ?></span>
                        <a href="api/logout.php" class="btn btn-sm btn-outline">Logout</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </nav>
</header>

