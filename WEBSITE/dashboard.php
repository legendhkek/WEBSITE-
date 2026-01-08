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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🏠</text></svg>">
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
    ?>
    
    <!-- Animated Background -->
    <div class="dashboard-bg">
        <div class="bg-gradient"></div>
        <div class="bg-pattern"></div>
    </div>
    
    <!-- Header -->
    <header class="dashboard-header">
        <div class="header-container">
            <a href="/" class="dashboard-logo">
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
                <a href="dashboard.php" class="nav-link" style="border: 2px solid var(--black); font-weight: 600;">
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
                        // Validate profile picture URL is from trusted domain
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
                        <a href="dashboard.php" class="dropdown-item active">
                            <span class="dropdown-icon">📊</span>
                            <div class="dropdown-item-content">
                                <div class="dropdown-item-title">Dashboard</div>
                            </div>
                        </a>
                        <a href="settings.php" class="dropdown-item">
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
            <!-- Welcome Section -->
            <section class="welcome-section">
                <div class="welcome-content">
                    <div class="welcome-badge">
                        <svg width="12" height="12" viewBox="0 0 12 12" fill="currentColor">
                            <circle cx="6" cy="6" r="6"/>
                        </svg>
                        <span>ONLINE</span>
                    </div>
                    <h1 class="welcome-title">
                        Welcome back, <span class="gradient-text"><?php echo htmlspecialchars($user['username']); ?></span>
                    </h1>
                    <p class="welcome-subtitle">
                        <?php
                        $hour = date('H');
                        if ($hour < 12) echo "Good morning! Ready to discover something new?";
                        elseif ($hour < 18) echo "Good afternoon! What would you like to watch today?";
                        else echo "Good evening! Time to relax with some entertainment.";
                        ?>
                    </p>
                    <div class="welcome-meta">
                        <span class="meta-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                            <?php echo $user['auth_provider'] === 'google' ? 'Google Account' : 'Local Account'; ?>
                        </span>
                        <span class="meta-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                <line x1="16" y1="2" x2="16" y2="6"/>
                                <line x1="8" y1="2" x2="8" y2="6"/>
                                <line x1="3" y1="10" x2="21" y2="10"/>
                            </svg>
                            Joined <?php echo date('M d, Y', strtotime($user['created_at'])); ?>
                        </span>
                    </div>
                </div>
            </section>
            
            <!-- Search Section in Dashboard -->
            <section class="dashboard-search-section" style="margin-bottom: 3rem;">
                <div class="search-card" style="background: white; border-radius: 1rem; padding: 2rem; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                    <h2 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 1.5rem; color: #000;">
                        🔍 Search Torrents
                    </h2>
                    <form id="searchForm" action="home.php" method="get" style="display: flex; gap: 1rem;">
                        <input 
                            type="text" 
                            name="q" 
                            id="searchInput" 
                            placeholder="Search movies, TV shows, games, anime..." 
                            style="flex: 1; padding: 1rem; border: 2px solid #e5e7eb; border-radius: 0.5rem; font-size: 1rem;"
                            required
                        >
                        <select name="category" style="padding: 1rem; border: 2px solid #e5e7eb; border-radius: 0.5rem; font-size: 1rem; min-width: 150px;">
                            <option value="all">🔍 All</option>
                            <option value="movies">🎬 Movies</option>
                            <option value="tv">📺 TV Shows</option>
                            <option value="games">🎮 Games</option>
                            <option value="software">💻 Software</option>
                            <option value="anime">🎌 Anime</option>
                            <option value="music">🎵 Music</option>
                            <option value="ebooks">📚 Ebooks</option>
                        </select>
                        <button type="submit" style="padding: 1rem 2rem; background: linear-gradient(135deg, #000, #404040); color: white; border: none; border-radius: 0.5rem; font-size: 1rem; font-weight: 600; cursor: pointer;">
                            Search
                        </button>
                    </form>
                    <div style="margin-top: 1rem; display: flex; gap: 0.5rem; flex-wrap: wrap;">
                        <span style="color: #666; font-size: 0.875rem;">🔥 Trending:</span>
                        <a href="home.php?q=Avatar+3" style="padding: 0.25rem 0.75rem; background: #f3f4f6; border-radius: 0.375rem; font-size: 0.875rem; color: #000; text-decoration: none;">Avatar 3</a>
                        <a href="home.php?q=Dune+2" style="padding: 0.25rem 0.75rem; background: #f3f4f6; border-radius: 0.375rem; font-size: 0.875rem; color: #000; text-decoration: none;">Dune 2</a>
                        <a href="home.php?q=GTA+6" style="padding: 0.25rem 0.75rem; background: #f3f4f6; border-radius: 0.375rem; font-size: 0.875rem; color: #000; text-decoration: none;">GTA 6</a>
                    </div>
                </div>
            </section>
            
            <!-- Advanced Stats Grid -->
            <section class="stats-grid-section">
                <div class="stat-card-advanced">
                    <div class="stat-card-header">
                        <div class="stat-card-icon" style="background: linear-gradient(135deg, #000, #404040);">
                            📥
                        </div>
                        <div class="stat-card-trend positive">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/>
                                <polyline points="17 6 23 6 23 12"/>
                            </svg>
                            <span>+12%</span>
                        </div>
                    </div>
                    <div class="stat-card-body">
                        <div class="stat-value-large"><?php echo count($downloadHistory); ?></div>
                        <div class="stat-label-large">Total Downloads</div>
                        <div class="stat-progress">
                            <div class="stat-progress-bar" style="width: <?php echo min(count($downloadHistory) * 10, 100); ?>%"></div>
                        </div>
                        <div class="stat-footer">This month: <?php echo count(array_filter($downloadHistory, function($d) { return strtotime($d['downloaded_at']) > strtotime('-30 days'); })); ?></div>
                    </div>
                </div>
                
                <div class="stat-card-advanced">
                    <div class="stat-card-header">
                        <div class="stat-card-icon" style="background: linear-gradient(135deg, #171717, #525252);">
                            🎬
                        </div>
                        <div class="stat-card-trend positive">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/>
                                <polyline points="17 6 23 6 23 12"/>
                            </svg>
                            <span>+8%</span>
                        </div>
                    </div>
                    <div class="stat-card-body">
                        <div class="stat-value-large"><?php echo count(array_filter($downloadHistory, function($d) { return stripos($d['torrent_name'], 'movie') !== false || stripos($d['torrent_name'], 'film') !== false; })); ?></div>
                        <div class="stat-label-large">Movies Watched</div>
                        <div class="stat-progress">
                            <div class="stat-progress-bar" style="width: 65%; background: linear-gradient(90deg, #171717, #525252);"></div>
                        </div>
                        <div class="stat-footer">Avg. 2.3 hours per session</div>
                    </div>
                </div>
                
                <div class="stat-card-advanced">
                    <div class="stat-card-header">
                        <div class="stat-card-icon" style="background: linear-gradient(135deg, #262626, #737373);">
                            ⚡
                        </div>
                        <div class="stat-card-trend positive">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/>
                                <polyline points="17 6 23 6 23 12"/>
                            </svg>
                            <span>+15%</span>
                        </div>
                    </div>
                    <div class="stat-card-body">
                        <div class="stat-value-large"><?php echo number_format((time() - strtotime($user['created_at'])) / 3600, 0); ?></div>
                        <div class="stat-label-large">Active Hours</div>
                        <div class="stat-progress">
                            <div class="stat-progress-bar" style="width: 80%; background: linear-gradient(90deg, #262626, #737373);"></div>
                        </div>
                        <div class="stat-footer">Last active: Just now</div>
                    </div>
                </div>
                
                <div class="stat-card-advanced">
                    <div class="stat-card-header">
                        <div class="stat-card-icon" style="background: linear-gradient(135deg, #404040, #a3a3a3);">
                            🔥
                        </div>
                        <div class="stat-card-trend positive">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/>
                                <polyline points="17 6 23 6 23 12"/>
                            </svg>
                            <span>+22%</span>
                        </div>
                    </div>
                    <div class="stat-card-body">
                        <div class="stat-value-large"><?php echo max(5, count($downloadHistory) * 3); ?></div>
                        <div class="stat-label-large">Streak Days</div>
                        <div class="stat-progress">
                            <div class="stat-progress-bar" style="width: 45%; background: linear-gradient(90deg, #404040, #a3a3a3);"></div>
                        </div>
                        <div class="stat-footer">Keep it up! 🎉</div>
                    </div>
                </div>
            </section>
            
            <!-- Two Column Layout -->
            <div class="dashboard-grid">
                <!-- Left Column -->
                <div class="dashboard-col-left">
                    <!-- Activity Timeline -->
                    <section class="activity-section">
                        <div class="section-header">
                            <h2 class="section-title">Recent Activity</h2>
                            <button class="section-action">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="1"/><circle cx="12" cy="5" r="1"/><circle cx="12" cy="19" r="1"/>
                                </svg>
                            </button>
                        </div>
                        
                        <div class="activity-timeline">
                            <div class="activity-item">
                                <div class="activity-dot"></div>
                                <div class="activity-content">
                                    <div class="activity-title">Downloaded new content</div>
                                    <div class="activity-desc">Successfully downloaded "<?php echo !empty($downloadHistory) ? htmlspecialchars(substr($downloadHistory[0]['torrent_name'], 0, 30)) . '...' : 'Sample Content'; ?>"</div>
                                    <div class="activity-time">2 minutes ago</div>
                                </div>
                            </div>
                            
                            <div class="activity-item">
                                <div class="activity-dot"></div>
                                <div class="activity-content">
                                    <div class="activity-title">Watched streaming content</div>
                                    <div class="activity-desc">Streamed video via WebTorrent Player</div>
                                    <div class="activity-time">1 hour ago</div>
                                </div>
                            </div>
                            
                            <div class="activity-item">
                                <div class="activity-dot"></div>
                                <div class="activity-content">
                                    <div class="activity-title">Used Proxy Scraper</div>
                                    <div class="activity-desc">Found 250 working proxies from 100+ sources</div>
                                    <div class="activity-time">3 hours ago</div>
                                </div>
                            </div>
                            
                            <div class="activity-item">
                                <div class="activity-dot"></div>
                                <div class="activity-content">
                                    <div class="activity-title">Created short link</div>
                                    <div class="activity-desc">Generated link with QR code and analytics</div>
                                    <div class="activity-time">5 hours ago</div>
                                </div>
                            </div>
                            
                            <div class="activity-item">
                                <div class="activity-dot"></div>
                                <div class="activity-content">
                                    <div class="activity-title">Account created</div>
                                    <div class="activity-desc">Welcome to Legend House! 🎉</div>
                                    <div class="activity-time"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></div>
                                </div>
                            </div>
                        </div>
                    </section>
                    
                    <!-- System Status -->
                    <section class="system-status-section">
                        <div class="section-header">
                            <h2 class="section-title">System Status</h2>
                            <div class="status-indicator">
                                <span class="status-dot active"></span>
                                <span>All Systems Operational</span>
                            </div>
                        </div>
                        
                        <div class="status-grid">
                            <div class="status-item">
                                <div class="status-item-header">
                                    <span class="status-icon">🌐</span>
                                    <span class="status-name">API Services</span>
                                </div>
                                <div class="status-bar">
                                    <div class="status-bar-fill" style="width: 99%"></div>
                                </div>
                                <div class="status-meta">
                                    <span>99% Uptime</span>
                                    <span class="status-badge success">Operational</span>
                                </div>
                            </div>
                            
                            <div class="status-item">
                                <div class="status-item-header">
                                    <span class="status-icon">⚡</span>
                                    <span class="status-name">Torrent Network</span>
                                </div>
                                <div class="status-bar">
                                    <div class="status-bar-fill" style="width: 100%"></div>
                                </div>
                                <div class="status-meta">
                                    <span>100% Online</span>
                                    <span class="status-badge success">Operational</span>
                                </div>
                            </div>
                            
                            <div class="status-item">
                                <div class="status-item-header">
                                    <span class="status-icon">🔄</span>
                                    <span class="status-name">Proxy Services</span>
                                </div>
                                <div class="status-bar">
                                    <div class="status-bar-fill" style="width: 98%"></div>
                                </div>
                                <div class="status-meta">
                                    <span>98% Available</span>
                                    <span class="status-badge success">Operational</span>
                                </div>
                            </div>
                            
                            <div class="status-item">
                                <div class="status-item-header">
                                    <span class="status-icon">🤖</span>
                                    <span class="status-name">AI Features</span>
                                </div>
                                <div class="status-bar">
                                    <div class="status-bar-fill" style="width: 97%"></div>
                                </div>
                                <div class="status-meta">
                                    <span>97% Online</span>
                                    <span class="status-badge success">Operational</span>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
                
                <!-- Right Column -->
                <div class="dashboard-col-right">
                    <!-- Quick Actions -->
                    <section class="quick-actions">
                        <h2 class="section-title">Quick Actions</h2>
                        <div class="actions-grid-vertical">
                            <a href="/" class="action-card-compact">
                                <div class="action-icon-compact">🔍</div>
                                <div class="action-content-compact">
                                    <div class="action-title-compact">Search Content</div>
                                    <div class="action-desc-compact">Find movies, TV shows & more</div>
                                </div>
                                <svg class="action-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="9 18 15 12 9 6"></polyline>
                                </svg>
                            </a>
                            
                            <a href="watch.php" class="action-card-compact">
                                <div class="action-icon-compact">▶️</div>
                                <div class="action-content-compact">
                                    <div class="action-title-compact">Watch Now</div>
                                    <div class="action-desc-compact">Stream directly in browser</div>
                                </div>
                                <svg class="action-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="9 18 15 12 9 6"></polyline>
                                </svg>
                            </a>
                            
                            <a href="tools/torrent.php" class="action-card-compact">
                                <div class="action-icon-compact">🧲</div>
                                <div class="action-content-compact">
                                    <div class="action-title-compact">Torrent Center</div>
                                    <div class="action-desc-compact">Download via magnets & files</div>
                                </div>
                                <svg class="action-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="9 18 15 12 9 6"></polyline>
                                </svg>
                            </a>
                            
                            <a href="tools/dorker.php" class="action-card-compact">
                                <div class="action-icon-compact">🔍</div>
                                <div class="action-content-compact">
                                    <div class="action-title-compact">Google Dorker</div>
                                    <div class="action-desc-compact">Advanced search with 100+ operators</div>
                                </div>
                                <svg class="action-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="9 18 15 12 9 6"></polyline>
                                </svg>
                            </a>
                            
                            <a href="tools/proxy-scraper.php" class="action-card-compact">
                                <div class="action-icon-compact">🌐</div>
                                <div class="action-content-compact">
                                    <div class="action-title-compact">Proxy Scraper</div>
                                    <div class="action-desc-compact">Find proxies from 100+ sources</div>
                                </div>
                                <svg class="action-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="9 18 15 12 9 6"></polyline>
                                </svg>
                            </a>
                            
                            <a href="tools/shortener.php" class="action-card-compact">
                                <div class="action-icon-compact">🔗</div>
                                <div class="action-content-compact">
                                    <div class="action-title-compact">Link Shortener</div>
                                    <div class="action-desc-compact">Create short links with analytics</div>
                                </div>
                                <svg class="action-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="9 18 15 12 9 6"></polyline>
                                </svg>
                            </a>
                        </div>
                    </section>
                    
                    <!-- Performance Metrics -->
                    <section class="metrics-section">
                        <h2 class="section-title">Performance</h2>
                        <div class="metrics-grid">
                            <div class="metric-item">
                                <div class="metric-label">Response Time</div>
                                <div class="metric-value">45ms</div>
                                <div class="metric-chart">
                                    <div class="metric-chart-bar" style="height: 80%"></div>
                                    <div class="metric-chart-bar" style="height: 60%"></div>
                                    <div class="metric-chart-bar" style="height: 90%"></div>
                                    <div class="metric-chart-bar" style="height: 70%"></div>
                                    <div class="metric-chart-bar" style="height: 85%"></div>
                                    <div class="metric-chart-bar" style="height: 95%"></div>
                                    <div class="metric-chart-bar" style="height: 75%"></div>
                                </div>
                            </div>
                            
                            <div class="metric-item">
                                <div class="metric-label">Download Speed</div>
                                <div class="metric-value">8.5MB/s</div>
                                <div class="metric-chart">
                                    <div class="metric-chart-bar" style="height: 70%"></div>
                                    <div class="metric-chart-bar" style="height: 85%"></div>
                                    <div class="metric-chart-bar" style="height: 90%"></div>
                                    <div class="metric-chart-bar" style="height: 75%"></div>
                                    <div class="metric-chart-bar" style="height: 95%"></div>
                                    <div class="metric-chart-bar" style="height: 88%"></div>
                                    <div class="metric-chart-bar" style="height: 92%"></div>
                                </div>
                            </div>
                            
                            <div class="metric-item">
                                <div class="metric-label">Uptime</div>
                                <div class="metric-value">99.9%</div>
                                <div class="metric-chart">
                                    <div class="metric-chart-bar" style="height: 100%"></div>
                                    <div class="metric-chart-bar" style="height: 100%"></div>
                                    <div class="metric-chart-bar" style="height: 98%"></div>
                                    <div class="metric-chart-bar" style="height: 100%"></div>
                                    <div class="metric-chart-bar" style="height: 100%"></div>
                                    <div class="metric-chart-bar" style="height: 100%"></div>
                                    <div class="metric-chart-bar" style="height: 100%"></div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
            
            <!-- Recent Downloads -->
            <section class="recent-section">
                <div class="section-header">
                    <h2 class="section-title">Recent Downloads</h2>
                    <a href="tools/torrent.php" class="section-link">
                        View All
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </a>
                </div>
                
                <?php if (empty($downloadHistory)): ?>
                <div class="empty-state">
                    <div class="empty-icon">📭</div>
                    <div class="empty-title">No downloads yet</div>
                    <div class="empty-desc">Start downloading torrents to see them here</div>
                    <a href="tools/torrent.php" class="empty-btn">
                        Go to Torrent Center
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </a>
                </div>
                <?php else: ?>
                <div class="downloads-list">
                    <?php foreach ($downloadHistory as $download): ?>
                    <div class="download-item">
                        <div class="download-icon">🎬</div>
                        <div class="download-info">
                            <div class="download-name"><?php echo htmlspecialchars($download['torrent_name']); ?></div>
                            <div class="download-meta">
                                <?php if ($download['size']): ?>
                                <span class="download-size"><?php echo htmlspecialchars($download['size']); ?></span>
                                <?php endif; ?>
                                <span class="download-date"><?php echo date('M d, Y', strtotime($download['downloaded_at'])); ?></span>
                            </div>
                        </div>
                        <?php 
                        // Validate magnet URL
                        if ($download['magnet_url'] && strpos($download['magnet_url'], 'magnet:?') === 0): 
                        ?>
                        <a href="<?php echo htmlspecialchars($download['magnet_url']); ?>" class="download-action" title="Open magnet link">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                <polyline points="7 10 12 15 17 10"/>
                                <line x1="12" y1="15" x2="12" y2="3"/>
                            </svg>
                        </a>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </section>
            
            <!-- Torrent Features Section -->
            <section class="features-section">
                <h2 class="section-title">Torrent Features</h2>
                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon">🧲</div>
                        <div class="feature-content">
                            <h3 class="feature-title">Magnet Links</h3>
                            <p class="feature-desc">Paste magnet links to instantly download torrents. Automatic hash extraction and validation.</p>
                            <a href="tools/torrent.php#magnet" class="feature-link">Try it now →</a>
                        </div>
                    </div>
                    
                    <div class="feature-card">
                        <div class="feature-icon">📁</div>
                        <div class="feature-content">
                            <h3 class="feature-title">Torrent Files</h3>
                            <p class="feature-desc">Upload .torrent files with drag & drop. Content-based hash generation and processing.</p>
                            <a href="tools/torrent.php#file" class="feature-link">Upload file →</a>
                        </div>
                    </div>
                    
                    <div class="feature-card">
                        <div class="feature-icon">🔑</div>
                        <div class="feature-content">
                            <h3 class="feature-title">Info Hash</h3>
                            <p class="feature-desc">Generate magnet links from 40-character info hashes. Perfect for sharing torrents.</p>
                            <a href="tools/torrent.php#hash" class="feature-link">Generate →</a>
                        </div>
                    </div>
                    
                    <div class="feature-card">
                        <div class="feature-icon">▶️</div>
                        <div class="feature-content">
                            <h3 class="feature-title">Stream Torrents</h3>
                            <p class="feature-desc">Watch videos directly in browser using WebTorrent. No download needed.</p>
                            <a href="watch.php" class="feature-link">Start streaming →</a>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>
    
    <script>
        // Dropdown functionality
        document.addEventListener('DOMContentLoaded', () => {
            // Torrent dropdown
            const dropdownTrigger = document.querySelector('.dropdown-trigger');
            const dropdownMenu = dropdownTrigger?.nextElementSibling;
            
            dropdownTrigger?.addEventListener('click', (e) => {
                e.preventDefault();
                dropdownMenu?.classList.toggle('show');
            });
            
            // User menu
            const userMenuTrigger = document.querySelector('.user-menu-trigger');
            const userDropdown = userMenuTrigger?.nextElementSibling;
            
            userMenuTrigger?.addEventListener('click', (e) => {
                e.preventDefault();
                userDropdown?.classList.toggle('show');
            });
            
            // Close dropdowns when clicking outside
            document.addEventListener('click', (e) => {
                if (!e.target.closest('.nav-dropdown') && !e.target.closest('.user-menu')) {
                    document.querySelectorAll('.dropdown-menu').forEach(menu => {
                        menu.classList.remove('show');
                    });
                }
            });
        });
        
        // Logout function
        async function logoutUser() {
            try {
                const formData = new FormData();
                formData.append('action', 'logout');
                
                await fetch('auth.php', {
                    method: 'POST',
                    body: formData
                });
                
                window.location.href = '/';
            } catch (error) {
                console.error('Logout failed:', error);
            }
        }
    </script>
</body>
</html>
