<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Legend House</title>
    
    <!-- Google AdSense -->
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-1940810089559549"
         crossorigin="anonymous"></script>
    
    <link rel="stylesheet" href="dashboard-style.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🏠</text></svg>">
</head>
<body data-theme="dark">
    <?php
    require_once __DIR__ . '/auth.php';
    
    // Check if user is logged in
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
    
    $user = getCurrentUser();
    if (!$user) {
        header('Location: login.php');
        exit;
    }
    
    // Get user's download history
    $db = getDB();
    $stmt = $db->prepare('SELECT * FROM download_history WHERE user_id = :user_id ORDER BY downloaded_at DESC LIMIT 10');
    $stmt->bindValue(':user_id', $user['id'], SQLITE3_INTEGER);
    $result = $stmt->execute();
    $downloadHistory = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $downloadHistory[] = $row;
    }
    $db->close();
    
    // Calculate stats
    $totalDownloads = count($downloadHistory);
    $thisMonth = count(array_filter($downloadHistory, function($d) { 
        return strtotime($d['downloaded_at']) > strtotime('-30 days'); 
    }));
    $activeHours = number_format((time() - strtotime($user['created_at'])) / 3600, 0);
    ?>
    
    <div class="app-layout">
        <!-- Left Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <a href="dashboard.php" class="sidebar-logo">
                    <div class="logo-icon">🏠</div>
                    <span class="sidebar-text">Legend House</span>
                </a>
                <button class="sidebar-toggle" onclick="toggleSidebar()" title="Toggle Sidebar">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="3" y1="12" x2="21" y2="12"></line>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <line x1="3" y1="18" x2="21" y2="18"></line>
                    </svg>
                </button>
            </div>
            
            <nav class="sidebar-nav">
                <!-- Main Navigation -->
                <div class="nav-section">
                    <div class="nav-section-title">Navigation</div>
                    <ul class="nav-list">
                        <li>
                            <a href="home.php" class="nav-item">
                                <span class="nav-icon">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                        <polyline points="9 22 9 12 15 12 15 22"></polyline>
                                    </svg>
                                </span>
                                <span class="sidebar-text">Home</span>
                            </a>
                        </li>
                        <li>
                            <a href="dashboard.php" class="nav-item active">
                                <span class="nav-icon">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="3" width="7" height="7"></rect>
                                        <rect x="14" y="3" width="7" height="7"></rect>
                                        <rect x="14" y="14" width="7" height="7"></rect>
                                        <rect x="3" y="14" width="7" height="7"></rect>
                                    </svg>
                                </span>
                                <span class="sidebar-text">Dashboard</span>
                            </a>
                        </li>
                        <li>
                            <a href="watch.php" class="nav-item">
                                <span class="nav-icon">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polygon points="5 3 19 12 5 21 5 3"></polygon>
                                    </svg>
                                </span>
                                <span class="sidebar-text">Watch</span>
                            </a>
                        </li>
                    </ul>
                </div>
                
                <!-- Tools Section -->
                <div class="nav-section">
                    <div class="nav-section-title">Tools</div>
                    <ul class="nav-list">
                        <li>
                            <a href="tools.php" class="nav-item">
                                <span class="nav-icon">🛠️</span>
                                <span class="sidebar-text">All Tools</span>
                                <span class="nav-badge success">20+</span>
                            </a>
                        </li>
                        <li>
                            <a href="tools/dorker.php" class="nav-item">
                                <span class="nav-icon">🔍</span>
                                <span class="sidebar-text">Google Dorker</span>
                            </a>
                        </li>
                        <li>
                            <a href="tools/torrent.php" class="nav-item">
                                <span class="nav-icon">🧲</span>
                                <span class="sidebar-text">Torrent Center</span>
                            </a>
                        </li>
                        <li>
                            <a href="tools/proxy-scraper.php" class="nav-item">
                                <span class="nav-icon">🌐</span>
                                <span class="sidebar-text">Proxy Scraper</span>
                            </a>
                        </li>
                        <li>
                            <a href="tools/shortener.php" class="nav-item">
                                <span class="nav-icon">🔗</span>
                                <span class="sidebar-text">Link Shortener</span>
                            </a>
                        </li>
                        <li>
                            <a href="tools/rotating-proxy.php" class="nav-item">
                                <span class="nav-icon">🔄</span>
                                <span class="sidebar-text">Rotating Proxy</span>
                            </a>
                        </li>
                    </ul>
                </div>
                
                <!-- Account Section -->
                <div class="nav-section">
                    <div class="nav-section-title">Account</div>
                    <ul class="nav-list">
                        <li>
                            <a href="settings.php" class="nav-item">
                                <span class="nav-icon">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="3"></circle>
                                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                                    </svg>
                                </span>
                                <span class="sidebar-text">Settings</span>
                            </a>
                        </li>
                        <li>
                            <a href="profile.php" class="nav-item">
                                <span class="nav-icon">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="12" cy="7" r="4"></circle>
                                    </svg>
                                </span>
                                <span class="sidebar-text">Profile</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
            
            <div class="sidebar-footer">
                <div class="user-profile dropdown" id="userDropdown">
                    <div class="user-profile" onclick="toggleUserMenu()">
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
                        <div class="user-info">
                            <div class="user-name"><?php echo htmlspecialchars($user['username']); ?></div>
                            <div class="user-email"><?php echo htmlspecialchars($user['email']); ?></div>
                        </div>
                    </div>
                    <div class="dropdown-menu" id="userMenu">
                        <div class="dropdown-header">
                            <div class="dropdown-header-title"><?php echo htmlspecialchars($user['username']); ?></div>
                            <div class="dropdown-header-subtitle"><?php echo $user['auth_provider'] === 'google' ? 'Google Account' : 'Local Account'; ?></div>
                        </div>
                        <div class="dropdown-divider"></div>
                        <a href="profile.php" class="dropdown-item">
                            <span class="dropdown-item-icon">👤</span>
                            Your Profile
                        </a>
                        <a href="settings.php" class="dropdown-item">
                            <span class="dropdown-item-icon">⚙️</span>
                            Settings
                        </a>
                        <div class="dropdown-divider"></div>
                        <button onclick="logoutUser()" class="dropdown-item danger">
                            <span class="dropdown-item-icon">🚪</span>
                            Sign Out
                        </button>
                    </div>
                </div>
            </div>
        </aside>
        
        <!-- Main Content -->
        <div class="main-wrapper">
            <!-- Top Header -->
            <header class="top-header">
                <div class="header-left">
                    <button class="header-btn mobile-menu" onclick="toggleSidebar()">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="3" y1="12" x2="21" y2="12"></line>
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                            <line x1="3" y1="18" x2="21" y2="18"></line>
                        </svg>
                    </button>
                    <div class="breadcrumb">
                        <a href="dashboard.php" class="breadcrumb-item">Dashboard</a>
                        <span class="breadcrumb-separator">/</span>
                        <span class="breadcrumb-item active">Overview</span>
                    </div>
                </div>
                
                <div class="search-container">
                    <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input type="text" class="search-input" placeholder="Search torrents, tools, settings..." id="globalSearch">
                    <span class="search-shortcut">⌘K</span>
                </div>
                
                <div class="header-right">
                    <button class="header-btn" title="Notifications">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                        </svg>
                        <span class="notification-dot"></span>
                    </button>
                    <button class="theme-toggle" onclick="toggleTheme()">
                        <span id="themeIcon">🌙</span>
                        <span id="themeText">Dark</span>
                    </button>
                </div>
            </header>
            
            <!-- Main Content Area -->
            <main class="main-content">
                <!-- Welcome Section -->
                <div class="dashboard-header-section">
                    <h1 class="page-title">
                        <?php
                        $hour = date('H');
                        if ($hour < 12) echo "Good morning";
                        elseif ($hour < 18) echo "Good afternoon";
                        else echo "Good evening";
                        ?>, <?php echo htmlspecialchars($user['username']); ?> 👋
                    </h1>
                    <p class="page-subtitle">Welcome back! Here's what's happening with your account.</p>
                </div>
                
                <!-- Stats Grid -->
                <div class="stats-grid">
                    <div class="stat-card fade-in" style="animation-delay: 0.1s">
                        <div class="stat-header">
                            <div class="stat-icon">📥</div>
                            <div class="stat-trend positive">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                                </svg>
                                +12%
                            </div>
                        </div>
                        <div class="stat-value"><?php echo $totalDownloads; ?></div>
                        <div class="stat-label">Total Downloads</div>
                        <div class="stat-progress">
                            <div class="stat-progress-bar" style="width: <?php echo min($totalDownloads * 10, 100); ?>%"></div>
                        </div>
                    </div>
                    
                    <div class="stat-card fade-in" style="animation-delay: 0.2s">
                        <div class="stat-header">
                            <div class="stat-icon">📅</div>
                            <div class="stat-trend positive">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                                </svg>
                                +8%
                            </div>
                        </div>
                        <div class="stat-value"><?php echo $thisMonth; ?></div>
                        <div class="stat-label">This Month</div>
                        <div class="stat-progress">
                            <div class="stat-progress-bar" style="width: <?php echo min($thisMonth * 10, 100); ?>%"></div>
                        </div>
                    </div>
                    
                    <div class="stat-card fade-in" style="animation-delay: 0.3s">
                        <div class="stat-header">
                            <div class="stat-icon">⚡</div>
                            <div class="stat-trend positive">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                                </svg>
                                +15%
                            </div>
                        </div>
                        <div class="stat-value"><?php echo $activeHours; ?></div>
                        <div class="stat-label">Active Hours</div>
                        <div class="stat-progress">
                            <div class="stat-progress-bar" style="width: 80%"></div>
                        </div>
                    </div>
                    
                    <div class="stat-card fade-in" style="animation-delay: 0.4s">
                        <div class="stat-header">
                            <div class="stat-icon">🔥</div>
                            <div class="stat-trend positive">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                                </svg>
                                +22%
                            </div>
                        </div>
                        <div class="stat-value"><?php echo max(5, $totalDownloads * 3); ?></div>
                        <div class="stat-label">Streak Days</div>
                        <div class="stat-progress">
                            <div class="stat-progress-bar" style="width: 45%"></div>
                        </div>
                    </div>
                </div>
                
                <!-- Content Grid -->
                <div class="content-grid">
                    <!-- Left Column -->
                    <div>
                        <!-- Recent Activity -->
                        <div class="card fade-in" style="animation-delay: 0.5s; margin-bottom: 24px;">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <span class="card-title-icon">📊</span>
                                    Recent Activity
                                </h3>
                                <div class="card-actions">
                                    <button class="card-action-btn">View All</button>
                                </div>
                            </div>
                            <div class="card-body">
                                <ul class="activity-list">
                                    <?php if (!empty($downloadHistory)): ?>
                                        <?php foreach (array_slice($downloadHistory, 0, 5) as $download): ?>
                                        <li class="activity-item">
                                            <div class="activity-icon">📥</div>
                                            <div class="activity-content">
                                                <div class="activity-title">Downloaded content</div>
                                                <div class="activity-desc"><?php echo htmlspecialchars(substr($download['torrent_name'], 0, 50)); ?>...</div>
                                            </div>
                                            <div class="activity-time"><?php echo date('M d', strtotime($download['downloaded_at'])); ?></div>
                                        </li>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <li class="activity-item">
                                            <div class="activity-icon">👋</div>
                                            <div class="activity-content">
                                                <div class="activity-title">Account created</div>
                                                <div class="activity-desc">Welcome to Legend House!</div>
                                            </div>
                                            <div class="activity-time"><?php echo date('M d', strtotime($user['created_at'])); ?></div>
                                        </li>
                                        <li class="activity-item">
                                            <div class="activity-icon">🎯</div>
                                            <div class="activity-content">
                                                <div class="activity-title">Getting started</div>
                                                <div class="activity-desc">Try searching for your favorite content</div>
                                            </div>
                                            <div class="activity-time">Now</div>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                        
                        <!-- System Status -->
                        <div class="card fade-in" style="animation-delay: 0.6s;">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <span class="card-title-icon">🟢</span>
                                    System Status
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="status-grid">
                                    <div class="status-item">
                                        <div class="status-indicator online"></div>
                                        <div class="status-info">
                                            <div class="status-name">API Services</div>
                                            <div class="status-detail">99.9% uptime</div>
                                        </div>
                                    </div>
                                    <div class="status-item">
                                        <div class="status-indicator online"></div>
                                        <div class="status-info">
                                            <div class="status-name">Torrent Network</div>
                                            <div class="status-detail">All trackers online</div>
                                        </div>
                                    </div>
                                    <div class="status-item">
                                        <div class="status-indicator online"></div>
                                        <div class="status-info">
                                            <div class="status-name">Proxy Services</div>
                                            <div class="status-detail">500+ proxies available</div>
                                        </div>
                                    </div>
                                    <div class="status-item">
                                        <div class="status-indicator online"></div>
                                        <div class="status-info">
                                            <div class="status-name">AI Assistant</div>
                                            <div class="status-detail">Powered by Blackbox</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Right Column -->
                    <div>
                        <!-- Quick Actions -->
                        <div class="card fade-in" style="animation-delay: 0.7s; margin-bottom: 24px;">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <span class="card-title-icon">⚡</span>
                                    Quick Actions
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="quick-actions">
                                    <a href="home.php" class="quick-action-item">
                                        <div class="quick-action-icon">🔍</div>
                                        <div class="quick-action-text">
                                            <div class="quick-action-title">Search Content</div>
                                            <div class="quick-action-desc">Find movies, TV shows & more</div>
                                        </div>
                                        <svg class="quick-action-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="9 18 15 12 9 6"></polyline>
                                        </svg>
                                    </a>
                                    <a href="tools/dorker.php" class="quick-action-item">
                                        <div class="quick-action-icon">🔍</div>
                                        <div class="quick-action-text">
                                            <div class="quick-action-title">Google Dorker</div>
                                            <div class="quick-action-desc">100+ dork operators</div>
                                        </div>
                                        <svg class="quick-action-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="9 18 15 12 9 6"></polyline>
                                        </svg>
                                    </a>
                                    <a href="tools/torrent.php" class="quick-action-item">
                                        <div class="quick-action-icon">🧲</div>
                                        <div class="quick-action-text">
                                            <div class="quick-action-title">Torrent Center</div>
                                            <div class="quick-action-desc">Magnet links & files</div>
                                        </div>
                                        <svg class="quick-action-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="9 18 15 12 9 6"></polyline>
                                        </svg>
                                    </a>
                                    <a href="tools/proxy-scraper.php" class="quick-action-item">
                                        <div class="quick-action-icon">🌐</div>
                                        <div class="quick-action-text">
                                            <div class="quick-action-title">Proxy Scraper</div>
                                            <div class="quick-action-desc">100+ proxy sources</div>
                                        </div>
                                        <svg class="quick-action-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="9 18 15 12 9 6"></polyline>
                                        </svg>
                                    </a>
                                    <a href="watch.php" class="quick-action-item">
                                        <div class="quick-action-icon">▶️</div>
                                        <div class="quick-action-text">
                                            <div class="quick-action-title">Watch Now</div>
                                            <div class="quick-action-desc">Stream in browser</div>
                                        </div>
                                        <svg class="quick-action-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="9 18 15 12 9 6"></polyline>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Pro Tips -->
                        <div class="card fade-in" style="animation-delay: 0.8s;">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <span class="card-title-icon">💡</span>
                                    Pro Tips
                                </h3>
                            </div>
                            <div class="card-body">
                                <div style="color: var(--text-secondary); font-size: 13px; line-height: 1.6;">
                                    <p style="margin-bottom: 12px;"><strong style="color: var(--text-primary);">⌘K</strong> - Quick search anywhere</p>
                                    <p style="margin-bottom: 12px;"><strong style="color: var(--text-primary);">🤖 AI Chat</strong> - Click the chat button for help</p>
                                    <p style="margin-bottom: 12px;"><strong style="color: var(--text-primary);">🔍 Dorker</strong> - Use site: to filter domains</p>
                                    <p><strong style="color: var(--text-primary);">🧲 Torrents</strong> - Paste magnet links directly</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script>
        // Sidebar toggle
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('collapsed');
            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
        }
        
        // Restore sidebar state
        if (localStorage.getItem('sidebarCollapsed') === 'true') {
            document.getElementById('sidebar').classList.add('collapsed');
        }
        
        // User menu toggle
        function toggleUserMenu() {
            const menu = document.getElementById('userMenu');
            menu.classList.toggle('show');
        }
        
        // Close user menu when clicking outside
        document.addEventListener('click', function(e) {
            const dropdown = document.getElementById('userDropdown');
            const menu = document.getElementById('userMenu');
            if (!dropdown.contains(e.target)) {
                menu.classList.remove('show');
            }
        });
        
        // Theme toggle
        function toggleTheme() {
            const body = document.body;
            const currentTheme = body.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            body.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            
            document.getElementById('themeIcon').textContent = newTheme === 'dark' ? '🌙' : '☀️';
            document.getElementById('themeText').textContent = newTheme === 'dark' ? 'Dark' : 'Light';
        }
        
        // Restore theme
        const savedTheme = localStorage.getItem('theme') || 'dark';
        document.body.setAttribute('data-theme', savedTheme);
        document.getElementById('themeIcon').textContent = savedTheme === 'dark' ? '🌙' : '☀️';
        document.getElementById('themeText').textContent = savedTheme === 'dark' ? 'Dark' : 'Light';
        
        // Logout function
        async function logoutUser() {
            if (!confirm('Are you sure you want to sign out?')) {
                return;
            }
            
            try {
                const formData = new FormData();
                formData.append('action', 'logout');
                
                await fetch('auth.php', {
                    method: 'POST',
                    body: formData
                });
                
                window.location.href = 'login.php';
            } catch (error) {
                console.error('Logout failed:', error);
                window.location.href = 'auth.php?action=logout';
            }
        }
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Cmd/Ctrl + K for search
            if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
                e.preventDefault();
                document.getElementById('globalSearch').focus();
            }
            
            // Escape to close menus
            if (e.key === 'Escape') {
                document.getElementById('userMenu').classList.remove('show');
            }
        });
        
        // Global search functionality
        document.getElementById('globalSearch').addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && this.value.trim()) {
                window.location.href = 'home.php?q=' + encodeURIComponent(this.value.trim());
            }
        });
        
        // Animate stats on load
        document.addEventListener('DOMContentLoaded', function() {
            const statValues = document.querySelectorAll('.stat-value');
            statValues.forEach(el => {
                const target = parseInt(el.textContent) || 0;
                if (target === 0) return;
                
                let current = 0;
                const increment = target / 30;
                const timer = setInterval(() => {
                    current += increment;
                    if (current >= target) {
                        el.textContent = target;
                        clearInterval(timer);
                    } else {
                        el.textContent = Math.floor(current);
                    }
                }, 30);
            });
        });
    </script>
    
    <!-- AI Chat Widget -->
    <script src="ai-chat-widget.js"></script>
    <script>
        document.body.dataset.aiContext = 'general';
    </script>
</body>
</html>
