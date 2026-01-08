<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Legend House</title>
    <link rel="stylesheet" href="dashboard-style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
</head>
<body>
    <?php
    session_start();
    require_once __DIR__ . '/auth.php';
    
    // Check if user is logged in
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
    
    $user = getCurrentUser();
    ?>
    
    <!-- Animated Background -->
    <div class="dashboard-bg">
        <div class="bg-gradient"></div>
        <div class="bg-pattern"></div>
    </div>
    
    <!-- Header -->
    <header class="dashboard-header">
        <div class="header-container">
            <a href="dashboard.php" class="dashboard-logo">
                <svg width="36" height="36" viewBox="0 0 42 42">
                    <rect x="6" y="12" width="30" height="22" rx="3" fill="none" stroke="currentColor" stroke-width="2.5"/>
                    <polygon points="21,6 28,12 14,12" fill="currentColor"/>
                    <rect x="17" y="22" width="8" height="12" fill="currentColor" opacity="0.7"/>
                </svg>
                <span class="logo-text">LEGEND HOUSE</span>
            </a>
            
            <nav class="header-nav">
                <a href="home.php" class="nav-link">
                    <span class="nav-icon">🏠</span>
                    Home
                </a>
                <a href="dashboard.php" class="nav-link">
                    <span class="nav-icon">📊</span>
                    Dashboard
                </a>
                <a href="watch.php" class="nav-link">
                    <span class="nav-icon">▶️</span>
                    Watch
                </a>
                <a href="tools.php" class="nav-link">
                    <span class="nav-icon">🛠️</span>
                    Tools
                </a>
                <div class="user-menu">
                    <button class="user-menu-trigger">
                        <?php 
                        $showProfilePic = false;
                        if ($user['profile_picture']) {
                            $picUrl = $user['profile_picture'];
                            if (filter_var($picUrl, FILTER_VALIDATE_URL) && 
                                (strpos($picUrl, 'https://lh3.googleusercontent.com') === 0 || 
                                 strpos($picUrl, 'https://googleusercontent.com') !== false)) {
                                $showProfilePic = true;
                            }
                        }
                        ?>
                        <?php if ($showProfilePic): ?>
                            <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile" class="user-avatar">
                        <?php else: ?>
                            <div class="user-avatar-placeholder">
                                <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                        <span class="user-name"><?php echo htmlspecialchars($user['username']); ?></span>
                        <svg class="dropdown-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </button>
                    <div class="dropdown-menu user-dropdown">
                        <div class="dropdown-header">
                            <div class="dropdown-header-title"><?php echo htmlspecialchars($user['username']); ?></div>
                            <div class="dropdown-header-subtitle"><?php echo htmlspecialchars($user['email']); ?></div>
                        </div>
                        <div class="dropdown-divider"></div>
                        <a href="dashboard.php" class="dropdown-item">
                            <span class="dropdown-icon">📊</span>
                            <div class="dropdown-item-content">
                                <div class="dropdown-item-title">Dashboard</div>
                            </div>
                        </a>
                        <a href="settings.php" class="dropdown-item active">
                            <span class="dropdown-icon">⚙️</span>
                            <div class="dropdown-item-content">
                                <div class="dropdown-item-title">Settings</div>
                            </div>
                        </a>
                        <div class="dropdown-divider"></div>
                        <button onclick="logoutUser()" class="dropdown-item logout-item">
                            <span class="dropdown-icon">🚪</span>
                            <div class="dropdown-item-content">
                                <div class="dropdown-item-title">Logout</div>
                            </div>
                        </button>
                    </div>
                </div>
            </nav>
        </div>
    </header>
    
    <!-- Main Content -->
    <main class="dashboard-main">
        <div class="dashboard-container">
            <section class="welcome-section">
                <div class="welcome-content">
                    <h1 class="welcome-title">
                        <span class="gradient-text">⚙️ Settings</span>
                    </h1>
                    <p class="welcome-subtitle">
                        Manage your account preferences and settings
                    </p>
                </div>
            </section>
            
            <!-- Settings Sections -->
            <div class="dashboard-grid" style="margin-top: 2rem;">
                <div class="dashboard-col-left" style="width: 100%;">
                    <!-- Account Settings -->
                    <section style="background: white; border-radius: 1rem; padding: 2rem; margin-bottom: 2rem; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                        <h2 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 1.5rem; color: #000;">
                            👤 Account Information
                        </h2>
                        <div style="display: grid; gap: 1rem;">
                            <div style="display: flex; justify-content: space-between; padding: 1rem; background: #f9fafb; border-radius: 0.5rem;">
                                <span style="font-weight: 600; color: #666;">Username:</span>
                                <span style="color: #000;"><?php echo htmlspecialchars($user['username']); ?></span>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding: 1rem; background: #f9fafb; border-radius: 0.5rem;">
                                <span style="font-weight: 600; color: #666;">Email:</span>
                                <span style="color: #000;"><?php echo htmlspecialchars($user['email']); ?></span>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding: 1rem; background: #f9fafb; border-radius: 0.5rem;">
                                <span style="font-weight: 600; color: #666;">Account Type:</span>
                                <span style="color: #000;"><?php echo $user['auth_provider'] === 'google' ? 'Google Account' : 'Local Account'; ?></span>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding: 1rem; background: #f9fafb; border-radius: 0.5rem;">
                                <span style="font-weight: 600; color: #666;">Member Since:</span>
                                <span style="color: #000;"><?php echo date('F j, Y', strtotime($user['created_at'])); ?></span>
                            </div>
                        </div>
                    </section>
                    
                    <!-- Preferences -->
                    <section style="background: white; border-radius: 1rem; padding: 2rem; margin-bottom: 2rem; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                        <h2 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 1.5rem; color: #000;">
                            🎨 Preferences
                        </h2>
                        <div style="display: grid; gap: 1rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; background: #f9fafb; border-radius: 0.5rem;">
                                <div>
                                    <div style="font-weight: 600; color: #000;">Download Notifications</div>
                                    <div style="font-size: 0.875rem; color: #666;">Get notified when downloads complete</div>
                                </div>
                                <label style="position: relative; display: inline-block; width: 50px; height: 24px;">
                                    <input type="checkbox" checked style="opacity: 0; width: 0; height: 0;">
                                    <span style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #000; border-radius: 24px; transition: 0.4s;"></span>
                                </label>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; background: #f9fafb; border-radius: 0.5rem;">
                                <div>
                                    <div style="font-weight: 600; color: #000;">Auto-Play Videos</div>
                                    <div style="font-size: 0.875rem; color: #666;">Automatically play videos when streaming</div>
                                </div>
                                <label style="position: relative; display: inline-block; width: 50px; height: 24px;">
                                    <input type="checkbox" checked style="opacity: 0; width: 0; height: 0;">
                                    <span style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #000; border-radius: 24px; transition: 0.4s;"></span>
                                </label>
                            </div>
                        </div>
                    </section>
                    
                    <!-- Danger Zone -->
                    <section style="background: white; border: 2px solid #fee2e2; border-radius: 1rem; padding: 2rem; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                        <h2 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 1.5rem; color: #dc2626;">
                            ⚠️ Danger Zone
                        </h2>
                        <div style="display: grid; gap: 1rem;">
                            <button style="padding: 1rem; background: white; border: 2px solid #dc2626; color: #dc2626; border-radius: 0.5rem; font-weight: 600; cursor: pointer; transition: all 0.3s;" onmouseover="this.style.background='#dc2626'; this.style.color='white';" onmouseout="this.style.background='white'; this.style.color='#dc2626';">
                                Clear Download History
                            </button>
                            <button onclick="if(confirm('Are you sure you want to logout?')) { window.location.href='auth.php?action=logout'; }" style="padding: 1rem; background: white; border: 2px solid #dc2626; color: #dc2626; border-radius: 0.5rem; font-weight: 600; cursor: pointer; transition: all 0.3s;" onmouseover="this.style.background='#dc2626'; this.style.color='white';" onmouseout="this.style.background='white'; this.style.color='#dc2626';">
                                Logout
                            </button>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </main>
    
    <script>
        // Dropdown functionality
        document.addEventListener('DOMContentLoaded', () => {
            const userMenuTrigger = document.querySelector('.user-menu-trigger');
            const userDropdown = userMenuTrigger?.nextElementSibling;
            
            userMenuTrigger?.addEventListener('click', (e) => {
                e.preventDefault();
                userDropdown?.classList.toggle('show');
            });
            
            document.addEventListener('click', (e) => {
                if (!e.target.closest('.user-menu')) {
                    document.querySelectorAll('.dropdown-menu').forEach(menu => {
                        menu.classList.remove('show');
                    });
                }
            });
        });
        
        function logoutUser() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = 'auth.php?action=logout';
            }
        }
    </script>
</body>
</html>
