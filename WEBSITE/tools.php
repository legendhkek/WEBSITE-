<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <?php
    // SEO Configuration for Tools Page
    $seo_title = 'Free Online Tools - Google Dorker, Proxy Scraper, Torrent Center | Legend House';
    $seo_description = 'Access 20+ free online tools at Legend House (LegendBL.tech). Google Dorker with 100+ operators, Proxy Scraper with 100+ sources, Rotating Proxy Maker, Link Shortener, WebTorrent Player, AI Chat Assistant, and more.';
    $seo_keywords = 'free online tools, google dorker, proxy scraper, rotating proxy, torrent tools, link shortener, ai chat, legendbl tools, download tools';
    $seo_url = 'https://legendbl.tech/tools.php';
    $seo_canonical = 'https://legendbl.tech/tools.php';
    include 'seo-head.php';
    ?>
    
    <title><?php echo htmlspecialchars($seo_title); ?></title>
    
    <!-- Google AdSense -->
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-1940810089559549"
         crossorigin="anonymous"></script>
    
    <link rel="stylesheet" href="dashboard-style.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🛠️</text></svg>">
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
                <div class="nav-section">
                    <div class="nav-section-title">Navigation</div>
                    <ul class="nav-list">
                        <li>
                            <a href="home.php" class="nav-item">
                                <span class="nav-icon">🏠</span>
                                <span class="sidebar-text">Home</span>
                            </a>
                        </li>
                        <li>
                            <a href="dashboard.php" class="nav-item">
                                <span class="nav-icon">📊</span>
                                <span class="sidebar-text">Dashboard</span>
                            </a>
                        </li>
                        <li>
                            <a href="watch.php" class="nav-item">
                                <span class="nav-icon">▶️</span>
                                <span class="sidebar-text">Watch</span>
                            </a>
                        </li>
                    </ul>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title">Tools</div>
                    <ul class="nav-list">
                        <li>
                            <a href="tools.php" class="nav-item active">
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
                
                <div class="nav-section">
                    <div class="nav-section-title">Account</div>
                    <ul class="nav-list">
                        <li>
                            <a href="settings.php" class="nav-item">
                                <span class="nav-icon">⚙️</span>
                                <span class="sidebar-text">Settings</span>
                            </a>
                        </li>
                        <li>
                            <a href="profile.php" class="nav-item">
                                <span class="nav-icon">👤</span>
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
                                strpos($picUrl, 'https://lh3.googleusercontent.com') === 0) {
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
                        <span class="breadcrumb-item active">Tools Hub</span>
                    </div>
                </div>
                
                <div class="search-container">
                    <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input type="text" class="search-input" placeholder="Search tools..." id="toolSearch">
                    <span class="search-shortcut">⌘K</span>
                </div>
                
                <div class="header-right">
                    <button class="theme-toggle" onclick="toggleTheme()">
                        <span id="themeIcon">🌙</span>
                        <span id="themeText">Dark</span>
                    </button>
                </div>
            </header>
            
            <main class="main-content">
                <!-- Hero Section -->
                <div class="dashboard-header-section" style="margin-bottom: 32px;">
                    <h1 class="page-title">🛠️ Tools Hub</h1>
                    <p class="page-subtitle">Access our comprehensive suite of advanced tools. All tools are free to use.</p>
                    
                    <div class="stats-grid" style="margin-top: 24px;">
                        <div class="stat-card">
                            <div class="stat-header">
                                <div class="stat-icon">🛠️</div>
                            </div>
                            <div class="stat-value">20+</div>
                            <div class="stat-label">Total Tools</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-header">
                                <div class="stat-icon">✅</div>
                            </div>
                            <div class="stat-value">15</div>
                            <div class="stat-label">Active Now</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-header">
                                <div class="stat-icon">🆓</div>
                            </div>
                            <div class="stat-value">100%</div>
                            <div class="stat-label">Free to Use</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-header">
                                <div class="stat-icon">⚡</div>
                            </div>
                            <div class="stat-value">24/7</div>
                            <div class="stat-label">Availability</div>
                        </div>
                    </div>
                </div>
                
                <!-- Search & Discovery Tools -->
                <div style="margin-bottom: 40px;">
                    <h2 style="font-size: 20px; font-weight: 600; color: var(--text-primary); margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                        <span>🔍</span> Search & Discovery
                    </h2>
                    <div class="tools-grid">
                        <a href="tools/dorker.php" class="tool-card">
                            <div class="tool-card-header">
                                <div class="tool-icon">🔍</div>
                                <div class="tool-info">
                                    <div class="tool-title">Google Dorker</div>
                                    <span class="tool-status active">
                                        <span style="width: 6px; height: 6px; background: currentColor; border-radius: 50%;"></span>
                                        Active
                                    </span>
                                </div>
                            </div>
                            <p class="tool-desc">Advanced Google dorking with 100+ operators. AI-powered query generation, bulk processing, and result scoring. No API required.</p>
                            <div class="tool-features">
                                <span class="tool-feature">100+ Operators</span>
                                <span class="tool-feature">AI-Powered</span>
                                <span class="tool-feature">Bulk Processing</span>
                            </div>
                        </a>
                        
                        <a href="home.php" class="tool-card">
                            <div class="tool-card-header">
                                <div class="tool-icon">🎬</div>
                                <div class="tool-info">
                                    <div class="tool-title">Torrent Search</div>
                                    <span class="tool-status active">
                                        <span style="width: 6px; height: 6px; background: currentColor; border-radius: 50%;"></span>
                                        Active
                                    </span>
                                </div>
                            </div>
                            <p class="tool-desc">Search multiple torrent sources simultaneously. Find movies, TV shows, games, software, and more with AI-powered suggestions.</p>
                            <div class="tool-features">
                                <span class="tool-feature">Multi-Source</span>
                                <span class="tool-feature">AI Suggestions</span>
                                <span class="tool-feature">Filters</span>
                            </div>
                        </a>
                    </div>
                </div>
                
                <!-- Torrent Tools -->
                <div style="margin-bottom: 40px;">
                    <h2 style="font-size: 20px; font-weight: 600; color: var(--text-primary); margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                        <span>🧲</span> Torrent Tools
                    </h2>
                    <div class="tools-grid">
                        <a href="tools/torrent.php" class="tool-card">
                            <div class="tool-card-header">
                                <div class="tool-icon">⬇️</div>
                                <div class="tool-info">
                                    <div class="tool-title">Download Center</div>
                                    <span class="tool-status active">
                                        <span style="width: 6px; height: 6px; background: currentColor; border-radius: 50%;"></span>
                                        Active
                                    </span>
                                </div>
                            </div>
                            <p class="tool-desc">Process magnet links, upload .torrent files, and generate magnets from info hashes. Complete torrent management solution.</p>
                            <div class="tool-features">
                                <span class="tool-feature">Magnet Links</span>
                                <span class="tool-feature">.torrent Files</span>
                                <span class="tool-feature">Hash Generator</span>
                            </div>
                        </a>
                        
                        <a href="watch.php" class="tool-card">
                            <div class="tool-card-header">
                                <div class="tool-icon">▶️</div>
                                <div class="tool-info">
                                    <div class="tool-title">WebTorrent Player</div>
                                    <span class="tool-status active">
                                        <span style="width: 6px; height: 6px; background: currentColor; border-radius: 50%;"></span>
                                        Active
                                    </span>
                                </div>
                            </div>
                            <p class="tool-desc">Stream torrents directly in your browser. No downloads required - instant playback with advanced player controls.</p>
                            <div class="tool-features">
                                <span class="tool-feature">Instant Stream</span>
                                <span class="tool-feature">No Download</span>
                                <span class="tool-feature">HD Quality</span>
                            </div>
                        </a>
                    </div>
                </div>
                
                <!-- Proxy Tools -->
                <div style="margin-bottom: 40px;">
                    <h2 style="font-size: 20px; font-weight: 600; color: var(--text-primary); margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                        <span>🌐</span> Proxy Tools
                    </h2>
                    <div class="tools-grid">
                        <a href="tools/proxy-scraper.php" class="tool-card">
                            <div class="tool-card-header">
                                <div class="tool-icon">🔍</div>
                                <div class="tool-info">
                                    <div class="tool-title">Proxy Scraper</div>
                                    <span class="tool-status active">
                                        <span style="width: 6px; height: 6px; background: currentColor; border-radius: 50%;"></span>
                                        Active
                                    </span>
                                </div>
                            </div>
                            <p class="tool-desc">Scrape proxies from 100+ sources with auto-validation. Check speed, anonymity, and country. Export as TXT, CSV, or JSON.</p>
                            <div class="tool-features">
                                <span class="tool-feature">100+ Sources</span>
                                <span class="tool-feature">Auto-Validate</span>
                                <span class="tool-feature">Export</span>
                            </div>
                        </a>
                        
                        <a href="tools/rotating-proxy.php" class="tool-card">
                            <div class="tool-card-header">
                                <div class="tool-icon">🔄</div>
                                <div class="tool-info">
                                    <div class="tool-title">Rotating Proxy Maker</div>
                                    <span class="tool-status active">
                                        <span style="width: 6px; height: 6px; background: currentColor; border-radius: 50%;"></span>
                                        Active
                                    </span>
                                </div>
                            </div>
                            <p class="tool-desc">Create rotating proxy pools from your proxy lists. Auto-rotation, health monitoring, and multiple rotation strategies.</p>
                            <div class="tool-features">
                                <span class="tool-feature">Auto-Rotate</span>
                                <span class="tool-feature">Health Check</span>
                                <span class="tool-feature">API Access</span>
                            </div>
                        </a>
                        
                        <a href="tools/residential-proxy-maker.php" class="tool-card">
                            <div class="tool-card-header">
                                <div class="tool-icon">🏠</div>
                                <div class="tool-info">
                                    <div class="tool-title">Residential Proxy Maker</div>
                                    <span class="tool-status active">
                                        <span style="width: 6px; height: 6px; background: currentColor; border-radius: 50%;"></span>
                                        Active
                                    </span>
                                </div>
                            </div>
                            <p class="tool-desc">Scrape & auto-check proxies from 200+ sources. Convert to residential rotating pools with real-time stats tracking.</p>
                            <div class="tool-features">
                                <span class="tool-feature">200+ Sources</span>
                                <span class="tool-feature">Auto-Check</span>
                                <span class="tool-feature">Real Stats</span>
                            </div>
                        </a>
                    </div>
                </div>
                
                <!-- Utility Tools -->
                <div style="margin-bottom: 40px;">
                    <h2 style="font-size: 20px; font-weight: 600; color: var(--text-primary); margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                        <span>⚙️</span> Utility Tools
                    </h2>
                    <div class="tools-grid">
                        <a href="tools/shortener.php" class="tool-card">
                            <div class="tool-card-header">
                                <div class="tool-icon">🔗</div>
                                <div class="tool-info">
                                    <div class="tool-title">Link Shortener</div>
                                    <span class="tool-status active">
                                        <span style="width: 6px; height: 6px; background: currentColor; border-radius: 50%;"></span>
                                        Active
                                    </span>
                                </div>
                            </div>
                            <p class="tool-desc">Create short, shareable links with advanced analytics. Track clicks, generate QR codes, set expiration, and password protect.</p>
                            <div class="tool-features">
                                <span class="tool-feature">Analytics</span>
                                <span class="tool-feature">QR Codes</span>
                                <span class="tool-feature">Password</span>
                            </div>
                        </a>
                    </div>
                </div>
                
                <!-- AI Tools -->
                <div style="margin-bottom: 40px;">
                    <h2 style="font-size: 20px; font-weight: 600; color: var(--text-primary); margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                        <span>🤖</span> AI Tools
                    </h2>
                    <div class="tools-grid">
                        <div class="tool-card" style="cursor: default;">
                            <div class="tool-card-header">
                                <div class="tool-icon">💬</div>
                                <div class="tool-info">
                                    <div class="tool-title">AI Chat Assistant</div>
                                    <span class="tool-status active">
                                        <span style="width: 6px; height: 6px; background: currentColor; border-radius: 50%;"></span>
                                        Active
                                    </span>
                                </div>
                            </div>
                            <p class="tool-desc">AI-powered chat assistant available across the platform. Get help with dorking, torrents, proxies, and more.</p>
                            <div class="tool-features">
                                <span class="tool-feature">Multi-Context</span>
                                <span class="tool-feature">24/7 Help</span>
                                <span class="tool-feature">Free</span>
                            </div>
                            <p style="font-size: 12px; color: var(--text-muted); margin-top: 12px;">💡 Click the chat button in the bottom right corner</p>
                        </div>
                        
                        <div class="tool-card" style="cursor: default;">
                            <div class="tool-card-header">
                                <div class="tool-icon">💡</div>
                                <div class="tool-info">
                                    <div class="tool-title">Smart Suggestions</div>
                                    <span class="tool-status active">
                                        <span style="width: 6px; height: 6px; background: currentColor; border-radius: 50%;"></span>
                                        Active
                                    </span>
                                </div>
                            </div>
                            <p class="tool-desc">AI-powered search suggestions as you type. Get intelligent recommendations based on context and trends.</p>
                            <div class="tool-features">
                                <span class="tool-feature">Real-Time</span>
                                <span class="tool-feature">Contextual</span>
                                <span class="tool-feature">Trending</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Coming Soon -->
                <div style="margin-bottom: 40px;">
                    <h2 style="font-size: 20px; font-weight: 600; color: var(--text-primary); margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                        <span>🚀</span> Coming Soon
                    </h2>
                    <div class="tools-grid">
                        <div class="tool-card" style="opacity: 0.6; cursor: not-allowed;">
                            <div class="tool-card-header">
                                <div class="tool-icon">💬</div>
                                <div class="tool-info">
                                    <div class="tool-title">Subtitle Generator</div>
                                    <span class="tool-status coming-soon">
                                        <span style="width: 6px; height: 6px; background: currentColor; border-radius: 50%;"></span>
                                        Coming Soon
                                    </span>
                                </div>
                            </div>
                            <p class="tool-desc">AI-powered subtitle generation with multi-language support and automatic timing sync.</p>
                        </div>
                        
                        <div class="tool-card" style="opacity: 0.6; cursor: not-allowed;">
                            <div class="tool-card-header">
                                <div class="tool-icon">🔄</div>
                                <div class="tool-info">
                                    <div class="tool-title">Video Converter</div>
                                    <span class="tool-status coming-soon">
                                        <span style="width: 6px; height: 6px; background: currentColor; border-radius: 50%;"></span>
                                        Coming Soon
                                    </span>
                                </div>
                            </div>
                            <p class="tool-desc">Convert videos between formats online. MP4, MKV, AVI, WebM support with quality preservation.</p>
                        </div>
                        
                        <div class="tool-card" style="opacity: 0.6; cursor: not-allowed;">
                            <div class="tool-card-header">
                                <div class="tool-icon">📊</div>
                                <div class="tool-info">
                                    <div class="tool-title">Bandwidth Monitor</div>
                                    <span class="tool-status coming-soon">
                                        <span style="width: 6px; height: 6px; background: currentColor; border-radius: 50%;"></span>
                                        Coming Soon
                                    </span>
                                </div>
                            </div>
                            <p class="tool-desc">Real-time network usage statistics. Monitor speeds, track data consumption, and optimize performance.</p>
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
        
        if (localStorage.getItem('sidebarCollapsed') === 'true') {
            document.getElementById('sidebar').classList.add('collapsed');
        }
        
        // User menu toggle
        function toggleUserMenu() {
            document.getElementById('userMenu').classList.toggle('show');
        }
        
        document.addEventListener('click', function(e) {
            const dropdown = document.getElementById('userDropdown');
            const menu = document.getElementById('userMenu');
            if (dropdown && !dropdown.contains(e.target)) {
                menu.classList.remove('show');
            }
        });
        
        // Theme toggle
        function toggleTheme() {
            const body = document.body;
            const newTheme = body.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            body.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            document.getElementById('themeIcon').textContent = newTheme === 'dark' ? '🌙' : '☀️';
            document.getElementById('themeText').textContent = newTheme === 'dark' ? 'Dark' : 'Light';
        }
        
        const savedTheme = localStorage.getItem('theme') || 'dark';
        document.body.setAttribute('data-theme', savedTheme);
        document.getElementById('themeIcon').textContent = savedTheme === 'dark' ? '🌙' : '☀️';
        document.getElementById('themeText').textContent = savedTheme === 'dark' ? 'Dark' : 'Light';
        
        // Logout
        async function logoutUser() {
            if (confirm('Are you sure you want to sign out?')) {
                const formData = new FormData();
                formData.append('action', 'logout');
                await fetch('auth.php', { method: 'POST', body: formData });
                window.location.href = 'login.php';
            }
        }
        
        // Search tools
        document.getElementById('toolSearch')?.addEventListener('input', function(e) {
            const query = e.target.value.toLowerCase();
            document.querySelectorAll('.tool-card').forEach(card => {
                const title = card.querySelector('.tool-title')?.textContent.toLowerCase() || '';
                const desc = card.querySelector('.tool-desc')?.textContent.toLowerCase() || '';
                card.style.display = (title.includes(query) || desc.includes(query)) ? '' : 'none';
            });
        });
        
        // Keyboard shortcut
        document.addEventListener('keydown', function(e) {
            if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
                e.preventDefault();
                document.getElementById('toolSearch')?.focus();
            }
        });
    </script>
    
    <!-- AI Chat Widget -->
    <script src="ai-chat-widget.js"></script>
</body>
</html>
