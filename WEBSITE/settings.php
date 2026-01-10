<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Legend House</title>
    <link rel="stylesheet" href="dashboard-style.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>⚙️</text></svg>">
</head>
<body data-theme="dark">
    <?php
    require_once __DIR__ . '/auth.php';
    
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
    
    $user = getCurrentUser();
    ?>
    
    <div class="app-layout">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <a href="dashboard.php" class="sidebar-logo">
                    <div class="logo-icon">🏠</div>
                    <span class="sidebar-text">Legend House</span>
                </a>
                <button class="sidebar-toggle" onclick="toggleSidebar()">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="3" y1="12" x2="21" y2="12"></line>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <line x1="3" y1="18" x2="21" y2="18"></line>
                    </svg>
                </button>
            </div>
            
            <nav class="sidebar-nav">
                <div class="nav-section">
                    <div class="nav-section-title">Navigation</div>
                    <ul class="nav-list">
                        <li><a href="home.php" class="nav-item"><span class="nav-icon">🏠</span><span class="sidebar-text">Home</span></a></li>
                        <li><a href="dashboard.php" class="nav-item"><span class="nav-icon">📊</span><span class="sidebar-text">Dashboard</span></a></li>
                        <li><a href="watch.php" class="nav-item"><span class="nav-icon">▶️</span><span class="sidebar-text">Watch</span></a></li>
                    </ul>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title">Tools</div>
                    <ul class="nav-list">
                        <li><a href="tools.php" class="nav-item"><span class="nav-icon">🛠️</span><span class="sidebar-text">All Tools</span></a></li>
                        <li><a href="tools/dorker.php" class="nav-item"><span class="nav-icon">🔍</span><span class="sidebar-text">Google Dorker</span></a></li>
                        <li><a href="tools/torrent.php" class="nav-item"><span class="nav-icon">🧲</span><span class="sidebar-text">Torrent Center</span></a></li>
                    </ul>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title">Account</div>
                    <ul class="nav-list">
                        <li><a href="settings.php" class="nav-item active"><span class="nav-icon">⚙️</span><span class="sidebar-text">Settings</span></a></li>
                        <li><a href="profile.php" class="nav-item"><span class="nav-icon">👤</span><span class="sidebar-text">Profile</span></a></li>
                    </ul>
                </div>
            </nav>
            
            <div class="sidebar-footer">
                <div class="user-profile">
                    <div class="user-avatar-placeholder"><?php echo strtoupper(substr($user['username'], 0, 1)); ?></div>
                    <div class="user-info">
                        <div class="user-name"><?php echo htmlspecialchars($user['username']); ?></div>
                        <div class="user-email"><?php echo htmlspecialchars($user['email']); ?></div>
                    </div>
                </div>
            </div>
        </aside>
        
        <!-- Main -->
        <div class="main-wrapper">
            <header class="top-header">
                <div class="header-left">
                    <button class="header-btn" onclick="toggleSidebar()">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="3" y1="12" x2="21" y2="12"></line>
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                            <line x1="3" y1="18" x2="21" y2="18"></line>
                        </svg>
                    </button>
                    <div class="breadcrumb">
                        <a href="dashboard.php" class="breadcrumb-item">Dashboard</a>
                        <span class="breadcrumb-separator">/</span>
                        <span class="breadcrumb-item active">Settings</span>
                    </div>
                </div>
                
                <div class="header-right">
                    <button class="theme-toggle" onclick="toggleTheme()">
                        <span id="themeIcon">🌙</span>
                        <span id="themeText">Dark</span>
                    </button>
                </div>
            </header>
            
            <main class="main-content">
                <div class="dashboard-header-section">
                    <h1 class="page-title">⚙️ Settings</h1>
                    <p class="page-subtitle">Manage your account preferences and settings</p>
                </div>
                
                <!-- Settings Grid -->
                <div class="content-grid">
                    <div>
                        <!-- Account Settings -->
                        <div class="card" style="margin-bottom: 24px;">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <span class="card-title-icon">👤</span>
                                    Account Information
                                </h3>
                            </div>
                            <div class="card-body">
                                <div style="display: grid; gap: 20px;">
                                    <div>
                                        <label style="display: block; font-size: 13px; color: var(--text-muted); margin-bottom: 8px;">Username</label>
                                        <input type="text" value="<?php echo htmlspecialchars($user['username']); ?>" readonly
                                               style="width: 100%; padding: 12px; background: var(--bg-tertiary); border: 1px solid var(--border-default); border-radius: 8px; color: var(--text-primary); font-size: 14px;">
                                    </div>
                                    <div>
                                        <label style="display: block; font-size: 13px; color: var(--text-muted); margin-bottom: 8px;">Email</label>
                                        <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly
                                               style="width: 100%; padding: 12px; background: var(--bg-tertiary); border: 1px solid var(--border-default); border-radius: 8px; color: var(--text-primary); font-size: 14px;">
                                    </div>
                                    <div>
                                        <label style="display: block; font-size: 13px; color: var(--text-muted); margin-bottom: 8px;">Account Type</label>
                                        <input type="text" value="<?php echo $user['auth_provider'] === 'google' ? 'Google Account' : 'Local Account'; ?>" readonly
                                               style="width: 100%; padding: 12px; background: var(--bg-tertiary); border: 1px solid var(--border-default); border-radius: 8px; color: var(--text-primary); font-size: 14px;">
                                    </div>
                                    <div>
                                        <label style="display: block; font-size: 13px; color: var(--text-muted); margin-bottom: 8px;">Member Since</label>
                                        <input type="text" value="<?php echo date('F j, Y', strtotime($user['created_at'])); ?>" readonly
                                               style="width: 100%; padding: 12px; background: var(--bg-tertiary); border: 1px solid var(--border-default); border-radius: 8px; color: var(--text-primary); font-size: 14px;">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Appearance -->
                        <div class="card" style="margin-bottom: 24px;">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <span class="card-title-icon">🎨</span>
                                    Appearance
                                </h3>
                            </div>
                            <div class="card-body">
                                <div style="display: flex; justify-content: space-between; align-items: center; padding: 16px 0; border-bottom: 1px solid var(--border-default);">
                                    <div>
                                        <div style="font-weight: 500; color: var(--text-primary); margin-bottom: 4px;">Theme</div>
                                        <div style="font-size: 13px; color: var(--text-muted);">Choose your preferred color scheme</div>
                                    </div>
                                    <button class="btn btn-secondary" onclick="toggleTheme()">
                                        <span id="themeToggleIcon">🌙</span>
                                        <span id="themeToggleText">Dark Mode</span>
                                    </button>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center; padding: 16px 0;">
                                    <div>
                                        <div style="font-weight: 500; color: var(--text-primary); margin-bottom: 4px;">Sidebar</div>
                                        <div style="font-size: 13px; color: var(--text-muted);">Collapse sidebar to save space</div>
                                    </div>
                                    <button class="btn btn-secondary" onclick="toggleSidebar()">Toggle Sidebar</button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Privacy & Security -->
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <span class="card-title-icon">🔒</span>
                                    Privacy & Security
                                </h3>
                            </div>
                            <div class="card-body">
                                <div style="display: flex; justify-content: space-between; align-items: center; padding: 16px 0; border-bottom: 1px solid var(--border-default);">
                                    <div>
                                        <div style="font-weight: 500; color: var(--text-primary); margin-bottom: 4px;">Download History</div>
                                        <div style="font-size: 13px; color: var(--text-muted);">Clear your download history</div>
                                    </div>
                                    <button class="btn btn-secondary" onclick="clearHistory()">Clear History</button>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center; padding: 16px 0;">
                                    <div>
                                        <div style="font-weight: 500; color: var(--text-primary); margin-bottom: 4px;">Sign Out</div>
                                        <div style="font-size: 13px; color: var(--text-muted);">Sign out of your account</div>
                                    </div>
                                    <button class="btn" style="background: #f85149; color: white;" onclick="logoutUser()">Sign Out</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <!-- Quick Stats -->
                        <div class="card" style="margin-bottom: 24px;">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <span class="card-title-icon">📊</span>
                                    Account Stats
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="status-grid">
                                    <div class="status-item">
                                        <div class="status-indicator online"></div>
                                        <div class="status-info">
                                            <div class="status-name">Account Status</div>
                                            <div class="status-detail">Active</div>
                                        </div>
                                    </div>
                                    <div class="status-item">
                                        <div class="status-indicator online"></div>
                                        <div class="status-info">
                                            <div class="status-name">AI Chat</div>
                                            <div class="status-detail">Enabled</div>
                                        </div>
                                    </div>
                                    <div class="status-item">
                                        <div class="status-indicator online"></div>
                                        <div class="status-info">
                                            <div class="status-name">Tools Access</div>
                                            <div class="status-detail">Full Access</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Help -->
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <span class="card-title-icon">❓</span>
                                    Need Help?
                                </h3>
                            </div>
                            <div class="card-body">
                                <p style="color: var(--text-secondary); font-size: 14px; margin-bottom: 16px;">
                                    Use the AI Chat assistant for help with any feature. Click the chat button in the bottom right corner.
                                </p>
                                <div class="quick-actions">
                                    <a href="tools.php" class="quick-action-item">
                                        <div class="quick-action-icon">🛠️</div>
                                        <div class="quick-action-text">
                                            <div class="quick-action-title">View All Tools</div>
                                        </div>
                                    </a>
                                    <a href="dashboard.php" class="quick-action-item">
                                        <div class="quick-action-icon">📊</div>
                                        <div class="quick-action-text">
                                            <div class="quick-action-title">Go to Dashboard</div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('collapsed');
        }
        
        function toggleTheme() {
            const theme = document.body.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            document.body.setAttribute('data-theme', theme);
            localStorage.setItem('theme', theme);
            document.getElementById('themeIcon').textContent = theme === 'dark' ? '🌙' : '☀️';
            document.getElementById('themeText').textContent = theme === 'dark' ? 'Dark' : 'Light';
            document.getElementById('themeToggleIcon').textContent = theme === 'dark' ? '🌙' : '☀️';
            document.getElementById('themeToggleText').textContent = theme === 'dark' ? 'Dark Mode' : 'Light Mode';
        }
        
        // Restore theme
        const savedTheme = localStorage.getItem('theme') || 'dark';
        document.body.setAttribute('data-theme', savedTheme);
        document.getElementById('themeIcon').textContent = savedTheme === 'dark' ? '🌙' : '☀️';
        document.getElementById('themeText').textContent = savedTheme === 'dark' ? 'Dark' : 'Light';
        
        if (localStorage.getItem('sidebarCollapsed') === 'true') {
            document.getElementById('sidebar').classList.add('collapsed');
        }
        
        function clearHistory() {
            if (confirm('Are you sure you want to clear your download history?')) {
                alert('History cleared!');
            }
        }
        
        async function logoutUser() {
            if (confirm('Are you sure you want to sign out?')) {
                const formData = new FormData();
                formData.append('action', 'logout');
                await fetch('auth.php', { method: 'POST', body: formData });
                window.location.href = 'login.php';
            }
        }
    </script>
    
    <script src="ai-chat-widget.js"></script>
</body>
</html>
