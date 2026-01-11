<?php
// IMPORTANT: All PHP session/auth code must be at the TOP before any HTML output
require_once __DIR__ . '/auth.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = getCurrentUser();

// Get download stats
$db = getDB();
$stmt = $db->prepare('SELECT COUNT(*) as total FROM download_history WHERE user_id = :user_id');
$stmt->bindValue(':user_id', $user['id'], SQLITE3_INTEGER);
$result = $stmt->execute();
$stats = $result->fetchArray(SQLITE3_ASSOC);
$totalDownloads = $stats['total'] ?? 0;
$db->close();

$daysActive = floor((time() - strtotime($user['created_at'])) / 86400);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Legend House</title>
    <link rel="stylesheet" href="dashboard-style.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>👤</text></svg>">
</head>
<body data-theme="dark">
    
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
                        <li><a href="settings.php" class="nav-item"><span class="nav-icon">⚙️</span><span class="sidebar-text">Settings</span></a></li>
                        <li><a href="profile.php" class="nav-item active"><span class="nav-icon">👤</span><span class="sidebar-text">Profile</span></a></li>
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
                        <span class="breadcrumb-item active">Profile</span>
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
                <!-- Profile Header -->
                <div style="text-align: center; padding: 48px 24px; background: var(--bg-secondary); border: 1px solid var(--border-default); border-radius: 16px; margin-bottom: 32px;">
                    <?php 
                    $showProfilePic = false;
                    if ($user['profile_picture']) {
                        $picUrl = $user['profile_picture'];
                        if (filter_var($picUrl, FILTER_VALIDATE_URL) && 
                            strpos($picUrl, 'https://lh3.googleusercontent.com') === 0) {
                            $showProfilePic = true;
                        }
                    }
                    ?>
                    <?php if ($showProfilePic): ?>
                        <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile" 
                             style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 4px solid var(--border-default); margin-bottom: 20px;">
                    <?php else: ?>
                        <div style="width: 120px; height: 120px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; font-size: 48px; font-weight: 700; color: white; margin: 0 auto 20px;">
                            <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                        </div>
                    <?php endif; ?>
                    
                    <h1 style="font-size: 28px; font-weight: 700; color: var(--text-primary); margin-bottom: 8px;">
                        <?php echo htmlspecialchars($user['username']); ?>
                    </h1>
                    <p style="font-size: 16px; color: var(--text-secondary); margin-bottom: 16px;">
                        <?php echo htmlspecialchars($user['email']); ?>
                    </p>
                    <div style="display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px; background: var(--bg-tertiary); border-radius: 20px; font-size: 13px; color: var(--text-muted);">
                        <?php if ($user['auth_provider'] === 'google'): ?>
                            <span style="color: #4285f4;">●</span> Signed in with Google
                        <?php else: ?>
                            <span style="color: var(--success);">●</span> Local Account
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Stats Grid -->
                <div class="stats-grid" style="margin-bottom: 32px;">
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon">📥</div>
                        </div>
                        <div class="stat-value"><?php echo $totalDownloads; ?></div>
                        <div class="stat-label">Total Downloads</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon">📅</div>
                        </div>
                        <div class="stat-value"><?php echo $daysActive; ?></div>
                        <div class="stat-label">Days Active</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon">🛠️</div>
                        </div>
                        <div class="stat-value">20+</div>
                        <div class="stat-label">Tools Available</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon">🤖</div>
                        </div>
                        <div class="stat-value">∞</div>
                        <div class="stat-label">AI Chats</div>
                    </div>
                </div>
                
                <!-- Content Grid -->
                <div class="content-grid">
                    <div>
                        <!-- Account Details -->
                        <div class="card" style="margin-bottom: 24px;">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <span class="card-title-icon">📋</span>
                                    Account Details
                                </h3>
                            </div>
                            <div class="card-body">
                                <div style="display: grid; gap: 16px;">
                                    <div style="display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid var(--border-default);">
                                        <span style="color: var(--text-muted);">Username</span>
                                        <span style="color: var(--text-primary); font-weight: 500;"><?php echo htmlspecialchars($user['username']); ?></span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid var(--border-default);">
                                        <span style="color: var(--text-muted);">Email</span>
                                        <span style="color: var(--text-primary); font-weight: 500;"><?php echo htmlspecialchars($user['email']); ?></span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid var(--border-default);">
                                        <span style="color: var(--text-muted);">Account Type</span>
                                        <span style="color: var(--text-primary); font-weight: 500;">
                                            <?php echo $user['auth_provider'] === 'google' ? '🔵 Google' : '🟢 Local'; ?>
                                        </span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid var(--border-default);">
                                        <span style="color: var(--text-muted);">Created</span>
                                        <span style="color: var(--text-primary); font-weight: 500;"><?php echo date('F j, Y', strtotime($user['created_at'])); ?></span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; padding: 12px 0;">
                                        <span style="color: var(--text-muted);">Last Login</span>
                                        <span style="color: var(--text-primary); font-weight: 500;">
                                            <?php echo $user['last_login'] ? date('M j, Y g:i A', strtotime($user['last_login'])) : 'Just now'; ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Achievements -->
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <span class="card-title-icon">🏆</span>
                                    Achievements
                                </h3>
                            </div>
                            <div class="card-body">
                                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
                                    <div style="text-align: center; padding: 20px; background: var(--bg-tertiary); border-radius: 12px;">
                                        <div style="font-size: 32px; margin-bottom: 8px;">🎉</div>
                                        <div style="font-size: 13px; color: var(--text-primary); font-weight: 500;">Newcomer</div>
                                        <div style="font-size: 11px; color: var(--text-muted);">Joined Legend House</div>
                                    </div>
                                    <?php if ($totalDownloads >= 1): ?>
                                    <div style="text-align: center; padding: 20px; background: var(--bg-tertiary); border-radius: 12px;">
                                        <div style="font-size: 32px; margin-bottom: 8px;">📥</div>
                                        <div style="font-size: 13px; color: var(--text-primary); font-weight: 500;">First Download</div>
                                        <div style="font-size: 11px; color: var(--text-muted);">Downloaded content</div>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($daysActive >= 7): ?>
                                    <div style="text-align: center; padding: 20px; background: var(--bg-tertiary); border-radius: 12px;">
                                        <div style="font-size: 32px; margin-bottom: 8px;">⭐</div>
                                        <div style="font-size: 13px; color: var(--text-primary); font-weight: 500;">Week Warrior</div>
                                        <div style="font-size: 11px; color: var(--text-muted);">7 days active</div>
                                    </div>
                                    <?php endif; ?>
                                    <div style="text-align: center; padding: 20px; background: var(--bg-tertiary); border-radius: 12px; opacity: 0.5;">
                                        <div style="font-size: 32px; margin-bottom: 8px;">🔒</div>
                                        <div style="font-size: 13px; color: var(--text-primary); font-weight: 500;">Power User</div>
                                        <div style="font-size: 11px; color: var(--text-muted);">100+ downloads</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <!-- Quick Actions -->
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <span class="card-title-icon">⚡</span>
                                    Quick Actions
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="quick-actions">
                                    <a href="dashboard.php" class="quick-action-item">
                                        <div class="quick-action-icon">📊</div>
                                        <div class="quick-action-text">
                                            <div class="quick-action-title">Dashboard</div>
                                            <div class="quick-action-desc">View your stats</div>
                                        </div>
                                    </a>
                                    <a href="settings.php" class="quick-action-item">
                                        <div class="quick-action-icon">⚙️</div>
                                        <div class="quick-action-text">
                                            <div class="quick-action-title">Settings</div>
                                            <div class="quick-action-desc">Manage preferences</div>
                                        </div>
                                    </a>
                                    <a href="tools.php" class="quick-action-item">
                                        <div class="quick-action-icon">🛠️</div>
                                        <div class="quick-action-text">
                                            <div class="quick-action-title">Tools</div>
                                            <div class="quick-action-desc">Access all tools</div>
                                        </div>
                                    </a>
                                    <a href="home.php" class="quick-action-item">
                                        <div class="quick-action-icon">🔍</div>
                                        <div class="quick-action-text">
                                            <div class="quick-action-title">Search</div>
                                            <div class="quick-action-desc">Find content</div>
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
        }
        
        // Restore theme
        const savedTheme = localStorage.getItem('theme') || 'dark';
        document.body.setAttribute('data-theme', savedTheme);
        document.getElementById('themeIcon').textContent = savedTheme === 'dark' ? '🌙' : '☀️';
        document.getElementById('themeText').textContent = savedTheme === 'dark' ? 'Dark' : 'Light';
        
        if (localStorage.getItem('sidebarCollapsed') === 'true') {
            document.getElementById('sidebar').classList.add('collapsed');
        }
    </script>
    
    <script src="ai-chat-widget.js"></script>
</body>
</html>
