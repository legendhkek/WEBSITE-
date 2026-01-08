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
                <a href="/" class="nav-link">
                    <span class="nav-icon">🏠</span>
                    Home
                </a>
                <a href="watch.php" class="nav-link">
                    <span class="nav-icon">▶️</span>
                    Watch
                </a>
                <div class="nav-dropdown">
                    <button class="nav-link dropdown-trigger">
                        <span class="nav-icon">🛠️</span>
                        Tools
                        <svg class="dropdown-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </button>
                    <div class="dropdown-menu tools-menu">
                        <!-- Torrent Tools Section -->
                        <div class="dropdown-section-header">
                            <span class="section-icon">🧲</span>
                            <span class="section-title">Torrent Tools</span>
                        </div>
                        <a href="tools/torrent.php" class="dropdown-item">
                            <span class="dropdown-icon">⬇️</span>
                            <div class="dropdown-item-content">
                                <div class="dropdown-item-title">Download Center</div>
                                <div class="dropdown-item-desc">All-in-one torrent management hub</div>
                            </div>
                        </a>
                        <a href="tools/torrent.php#magnet" class="dropdown-item">
                            <span class="dropdown-icon">🧲</span>
                            <div class="dropdown-item-content">
                                <div class="dropdown-item-title">Magnet Links</div>
                                <div class="dropdown-item-desc">Paste and process magnet URIs instantly</div>
                            </div>
                        </a>
                        <a href="tools/torrent.php#file" class="dropdown-item">
                            <span class="dropdown-icon">📁</span>
                            <div class="dropdown-item-content">
                                <div class="dropdown-item-title">Upload Torrent File</div>
                                <div class="dropdown-item-desc">Drag & drop .torrent files for parsing</div>
                            </div>
                        </a>
                        <a href="tools/torrent.php#hash" class="dropdown-item">
                            <span class="dropdown-icon">🔑</span>
                            <div class="dropdown-item-content">
                                <div class="dropdown-item-title">Info Hash</div>
                                <div class="dropdown-item-desc">Generate magnet link from hash</div>
                            </div>
                        </a>
                        
                        <div class="dropdown-divider"></div>
                        
                        <!-- Media Tools Section -->
                        <div class="dropdown-section-header">
                            <span class="section-icon">🎬</span>
                            <span class="section-title">Media Tools</span>
                        </div>
                        <a href="watch.php" class="dropdown-item">
                            <span class="dropdown-icon">▶️</span>
                            <div class="dropdown-item-content">
                                <div class="dropdown-item-title">Stream Player</div>
                                <div class="dropdown-item-desc">WebTorrent-powered instant streaming</div>
                            </div>
                        </a>
                        <a href="#subtitle-generator" class="dropdown-item">
                            <span class="dropdown-icon">💬</span>
                            <div class="dropdown-item-content">
                                <div class="dropdown-item-title">Subtitle Generator</div>
                                <div class="dropdown-item-desc">Auto-generate and sync subtitles</div>
                            </div>
                        </a>
                        <a href="#video-converter" class="dropdown-item">
                            <span class="dropdown-icon">🔄</span>
                            <div class="dropdown-item-content">
                                <div class="dropdown-item-title">Video Converter</div>
                                <div class="dropdown-item-desc">Convert between formats online</div>
                            </div>
                        </a>
                        
                        <div class="dropdown-divider"></div>
                        
                        <!-- AI Tools Section -->
                        <div class="dropdown-section-header">
                            <span class="section-icon">🤖</span>
                            <span class="section-title">AI Tools</span>
                        </div>
                        <a href="/#voice-search" class="dropdown-item">
                            <span class="dropdown-icon">🎤</span>
                            <div class="dropdown-item-content">
                                <div class="dropdown-item-title">Voice Search</div>
                                <div class="dropdown-item-desc">Hands-free content discovery</div>
                            </div>
                        </a>
                        <a href="/#ai-suggestions" class="dropdown-item">
                            <span class="dropdown-icon">💡</span>
                            <div class="dropdown-item-content">
                                <div class="dropdown-item-title">Smart Suggestions</div>
                                <div class="dropdown-item-desc">AI-powered search improvements</div>
                            </div>
                        </a>
                        <a href="/#content-analysis" class="dropdown-item">
                            <span class="dropdown-icon">🔍</span>
                            <div class="dropdown-item-content">
                                <div class="dropdown-item-title">Content Analysis</div>
                                <div class="dropdown-item-desc">Extract metadata with AI</div>
                            </div>
                        </a>
                        
                        <div class="dropdown-divider"></div>
                        
                        <!-- Utility Tools Section -->
                        <div class="dropdown-section-header">
                            <span class="section-icon">⚙️</span>
                            <span class="section-title">Utility Tools</span>
                        </div>
                        <a href="#download-manager" class="dropdown-item">
                            <span class="dropdown-icon">📥</span>
                            <div class="dropdown-item-content">
                                <div class="dropdown-item-title">Download Manager</div>
                                <div class="dropdown-item-desc">Track and manage all downloads</div>
                            </div>
                        </a>
                        <a href="#bandwidth-monitor" class="dropdown-item">
                            <span class="dropdown-icon">📊</span>
                            <div class="dropdown-item-content">
                                <div class="dropdown-item-title">Bandwidth Monitor</div>
                                <div class="dropdown-item-desc">Real-time network usage stats</div>
                            </div>
                        </a>
                        <a href="tools/shortener.php" class="dropdown-item">
                            <span class="dropdown-icon">🔗</span>
                            <div class="dropdown-item-content">
                                <div class="dropdown-item-title">Link Shortener</div>
                                <div class="dropdown-item-desc">Create shareable short links with analytics</div>
                            </div>
                        </a>
                        <a href="tools/proxy-scraper.php" class="dropdown-item">
                            <span class="dropdown-icon">🔍</span>
                            <div class="dropdown-item-content">
                                <div class="dropdown-item-title">Proxy Scraper</div>
                                <div class="dropdown-item-desc">Find and validate working proxies</div>
                            </div>
                        </a>
                        <a href="tools/rotating-proxy.php" class="dropdown-item">
                            <span class="dropdown-icon">🔄</span>
                            <div class="dropdown-item-content">
                                <div class="dropdown-item-title">Rotating Proxy Maker</div>
                                <div class="dropdown-item-desc">Create residential rotating proxy pools</div>
                            </div>
                        </a>
                    </div>
                </div>
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
                        <a href="#settings" class="dropdown-item">
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
                </div>
                <div class="welcome-stats">
                    <div class="stat-card">
                        <div class="stat-icon">📥</div>
                        <div class="stat-content">
                            <div class="stat-value"><?php echo count($downloadHistory); ?></div>
                            <div class="stat-label">Downloads</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">🎬</div>
                        <div class="stat-content">
                            <div class="stat-value"><?php echo $user['auth_provider'] === 'google' ? 'Google' : 'Local'; ?></div>
                            <div class="stat-label">Account Type</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">⚡</div>
                        <div class="stat-content">
                            <div class="stat-value"><?php echo date('M d', strtotime($user['created_at'])); ?></div>
                            <div class="stat-label">Member Since</div>
                        </div>
                    </div>
                </div>
            </section>
            
            <!-- Quick Actions -->
            <section class="quick-actions">
                <h2 class="section-title">Quick Actions</h2>
                <div class="actions-grid">
                    <a href="/" class="action-card">
                        <div class="action-icon" style="background: linear-gradient(135deg, #f59e0b, #ef4444);">
                            🔍
                        </div>
                        <div class="action-content">
                            <div class="action-title">Search Content</div>
                            <div class="action-desc">Find movies, TV shows, games & more</div>
                        </div>
                        <svg class="action-arrow" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </a>
                    
                    <a href="watch.php" class="action-card">
                        <div class="action-icon" style="background: linear-gradient(135deg, #10b981, #06b6d4);">
                            ▶️
                        </div>
                        <div class="action-content">
                            <div class="action-title">Watch Now</div>
                            <div class="action-desc">Stream movies & shows directly</div>
                        </div>
                        <svg class="action-arrow" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </a>
                    
                    <a href="tools/torrent.php" class="action-card">
                        <div class="action-icon" style="background: linear-gradient(135deg, #8b5cf6, #ec4899);">
                            🧲
                        </div>
                        <div class="action-content">
                            <div class="action-title">Torrent Center</div>
                            <div class="action-desc">Download via magnet links & files</div>
                        </div>
                        <svg class="action-arrow" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </a>
                    
                    <a href="#trending" class="action-card">
                        <div class="action-icon" style="background: linear-gradient(135deg, #ef4444, #ec4899);">
                            🔥
                        </div>
                        <div class="action-content">
                            <div class="action-title">Trending</div>
                            <div class="action-desc">Discover what's hot right now</div>
                        </div>
                        <svg class="action-arrow" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </a>
                </div>
            </section>
            
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
