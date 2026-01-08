<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tools Hub - Legend House</title>
    
    <!-- Google AdSense -->
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-1940810089559549"
         crossorigin="anonymous"></script>
    
    <link rel="stylesheet" href="dashboard-style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🛠️</text></svg>">
    
    <style>
        /* Tools Hub Specific Styles */
        .tools-hero {
            padding: 6rem 2rem 4rem 2rem;
            text-align: center;
            background: linear-gradient(135deg, var(--gray-50) 0%, var(--white) 100%);
            border-bottom: 2px solid var(--gray-200);
        }
        
        .tools-hero-title {
            font-size: 3.5rem;
            font-weight: 900;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, var(--black) 0%, var(--gray-600) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .tools-hero-subtitle {
            font-size: 1.25rem;
            color: var(--gray-600);
            max-width: 600px;
            margin: 0 auto 2rem auto;
        }
        
        .tools-stats {
            display: flex;
            justify-content: center;
            gap: 3rem;
            margin-top: 3rem;
        }
        
        .tools-stat {
            text-align: center;
        }
        
        .tools-stat-value {
            font-size: 3rem;
            font-weight: 900;
            color: var(--black);
        }
        
        .progress-bar-container {
            width: 100%;
            height: 8px;
            background: var(--gray-200);
            border-radius: 4px;
            overflow: hidden;
            margin: 1rem 0;
        }
        
        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, var(--black) 0%, var(--gray-600) 100%);
            transition: width 0.3s ease;
            width: 0;
        }
        
        .tools-stat-label {
            font-size: 0.875rem;
            color: var(--gray-500);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-top: 0.5rem;
        }
        
        .tools-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 4rem 2rem;
        }
        
        .tools-section {
            margin-bottom: 4rem;
        }
        
        .tools-section-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--gray-200);
        }
        
        .tools-section-icon {
            font-size: 2.5rem;
        }
        
        .tools-section-title {
            font-size: 2rem;
            font-weight: 800;
            color: var(--black);
        }
        
        .tools-section-desc {
            font-size: 1rem;
            color: var(--gray-600);
            margin-left: auto;
        }
        
        .tools-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 1.5rem;
        }
        
        .tool-card {
            background: var(--white);
            border: 2px solid var(--gray-200);
            border-radius: 1rem;
            padding: 2rem;
            transition: all 0.3s ease;
            text-decoration: none;
            color: var(--black);
            display: block;
            position: relative;
            overflow: hidden;
        }
        
        .tool-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--black), var(--gray-600));
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }
        
        .tool-card:hover {
            transform: translateY(-4px);
            border-color: var(--black);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        
        .tool-card:hover::before {
            transform: scaleX(1);
        }
        
        .tool-card-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .tool-card-icon {
            font-size: 2.5rem;
        }
        
        .tool-card-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--black);
        }
        
        .tool-card-desc {
            font-size: 0.9375rem;
            color: var(--gray-600);
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }
        
        .tool-card-features {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }
        
        .tool-feature-tag {
            padding: 0.375rem 0.75rem;
            background: var(--gray-100);
            border-radius: 0.375rem;
            font-size: 0.8125rem;
            color: var(--gray-700);
            font-weight: 500;
        }
        
        .tool-card-status {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.375rem 0.75rem;
            background: var(--gray-100);
            border-radius: 0.375rem;
            font-size: 0.8125rem;
            font-weight: 600;
        }
        
        .tool-card-status.active {
            background: rgba(34, 197, 94, 0.1);
            color: rgb(22, 163, 74);
        }
        
        .tool-card-status.coming-soon {
            background: rgba(245, 158, 11, 0.1);
            color: rgb(217, 119, 6);
        }
        
        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: currentColor;
        }
        
        @media (max-width: 768px) {
            .tools-hero-title {
                font-size: 2.5rem;
            }
            
            .tools-stats {
                flex-direction: column;
                gap: 2rem;
            }
            
            .tools-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php
    session_start();
    require_once __DIR__ . '/auth.php';
    $user = getCurrentUser();
    // Allow access without login, but show login button if not logged in
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
                <a href="tools.php" class="nav-link" style="border: 2px solid var(--black); font-weight: 600;">
                    <span class="nav-icon">🛠️</span>
                    Tools
                </a>
                
                <?php if ($user): ?>
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
                        <a href="dashboard.php" class="dropdown-item">
                            <span class="dropdown-icon">📊</span>
                            <div class="dropdown-item-content">
                                <div class="dropdown-item-title">Dashboard</div>
                            </div>
                        </a>
                        <button onclick="logoutUser()" class="dropdown-item logout-item">
                            <span class="dropdown-icon">🚪</span>
                            <div class="dropdown-item-content">
                                <div class="dropdown-item-title">Logout</div>
                            </div>
                        </button>
                    </div>
                </div>
                <?php else: ?>
                <a href="login.php" class="nav-link" style="border: 1px solid var(--gray-200);">
                    <span class="nav-icon">🔐</span>
                    Login
                </a>
                <?php endif; ?>
            </nav>
        </div>
    </header>
    
    <!-- Hero Section -->
    <section class="tools-hero">
        <h1 class="tools-hero-title">🛠️ Tools Hub</h1>
        <p class="tools-hero-subtitle">
            Access our comprehensive suite of advanced tools for torrents, media, AI, and utilities. 
            Everything you need in one powerful platform.
        </p>
        
        <div class="tools-stats">
            <div class="tools-stat">
                <div class="tools-stat-value">20+</div>
                <div class="tools-stat-label">Total Tools</div>
            </div>
            <div class="tools-stat">
                <div class="tools-stat-value">12</div>
                <div class="tools-stat-label">Active Now</div>
            </div>
            <div class="tools-stat">
                <div class="tools-stat-value">100%</div>
                <div class="tools-stat-label">Free to Use</div>
            </div>
        </div>
    </section>
    
    <!-- Tools Content -->
    <div class="tools-content">
        <!-- Torrent Tools -->
        <section class="tools-section">
            <div class="tools-section-header">
                <span class="tools-section-icon">🧲</span>
                <div>
                    <h2 class="tools-section-title">Torrent Tools</h2>
                </div>
                <span class="tools-section-desc">4 tools available</span>
            </div>
            
            <div class="tools-grid">
                <a href="tools/torrent.php" class="tool-card">
                    <div class="tool-card-header">
                        <span class="tool-card-icon">⬇️</span>
                        <h3 class="tool-card-title">Download Center</h3>
                    </div>
                    <p class="tool-card-desc">
                        All-in-one torrent management hub. Process magnet links, upload .torrent files, 
                        and generate magnet URIs from info hashes. Complete torrent solution.
                    </p>
                    <div class="tool-card-features">
                        <span class="tool-feature-tag">Magnet Links</span>
                        <span class="tool-feature-tag">.torrent Files</span>
                        <span class="tool-feature-tag">Info Hash</span>
                    </div>
                    <span class="tool-card-status active">
                        <span class="status-dot"></span>
                        Active
                    </span>
                </a>
                
                <a href="tools/torrent.php#magnet" class="tool-card">
                    <div class="tool-card-header">
                        <span class="tool-card-icon">🧲</span>
                        <h3 class="tool-card-title">Magnet Link Processor</h3>
                    </div>
                    <p class="tool-card-desc">
                        Instantly process and validate magnet URIs. Extract info hash, tracker lists, 
                        and metadata. Copy to clipboard or open directly in torrent client.
                    </p>
                    <div class="tool-card-features">
                        <span class="tool-feature-tag">Fast Processing</span>
                        <span class="tool-feature-tag">Validation</span>
                        <span class="tool-feature-tag">One-Click Copy</span>
                    </div>
                    <span class="tool-card-status active">
                        <span class="status-dot"></span>
                        Active
                    </span>
                </a>
                
                <a href="tools/torrent.php#file" class="tool-card">
                    <div class="tool-card-header">
                        <span class="tool-card-icon">📁</span>
                        <h3 class="tool-card-title">Torrent File Parser</h3>
                    </div>
                    <p class="tool-card-desc">
                        Upload or drag & drop .torrent files for instant parsing. Extract all metadata, 
                        file lists, and generate corresponding magnet links.
                    </p>
                    <div class="tool-card-features">
                        <span class="tool-feature-tag">Drag & Drop</span>
                        <span class="tool-feature-tag">Metadata</span>
                        <span class="tool-feature-tag">Magnet Gen</span>
                    </div>
                    <span class="tool-card-status active">
                        <span class="status-dot"></span>
                        Active
                    </span>
                </a>
                
                <a href="tools/torrent.php#hash" class="tool-card">
                    <div class="tool-card-header">
                        <span class="tool-card-icon">🔑</span>
                        <h3 class="tool-card-title">Info Hash to Magnet</h3>
                    </div>
                    <p class="tool-card-desc">
                        Convert 40-character info hashes into fully functional magnet links. 
                        Add custom trackers and configure display name.
                    </p>
                    <div class="tool-card-features">
                        <span class="tool-feature-tag">Hash Validation</span>
                        <span class="tool-feature-tag">Custom Trackers</span>
                        <span class="tool-feature-tag">Instant Gen</span>
                    </div>
                    <span class="tool-card-status active">
                        <span class="status-dot"></span>
                        Active
                    </span>
                </a>
            </div>
        </section>
        
        <!-- Media Tools -->
        <section class="tools-section">
            <div class="tools-section-header">
                <span class="tools-section-icon">🎬</span>
                <div>
                    <h2 class="tools-section-title">Media Tools</h2>
                </div>
                <span class="tools-section-desc">3 tools (1 active)</span>
            </div>
            
            <div class="tools-grid">
                <a href="watch.php" class="tool-card">
                    <div class="tool-card-header">
                        <span class="tool-card-icon">▶️</span>
                        <h3 class="tool-card-title">WebTorrent Player</h3>
                    </div>
                    <p class="tool-card-desc">
                        Stream torrents directly in your browser using WebTorrent technology. 
                        No downloads required - instant playback with advanced player controls.
                    </p>
                    <div class="tool-card-features">
                        <span class="tool-feature-tag">Instant Streaming</span>
                        <span class="tool-feature-tag">No Downloads</span>
                        <span class="tool-feature-tag">HD Quality</span>
                    </div>
                    <span class="tool-card-status active">
                        <span class="status-dot"></span>
                        Active
                    </span>
                </a>
                
                <div class="tool-card" style="opacity: 0.7; cursor: not-allowed;">
                    <div class="tool-card-header">
                        <span class="tool-card-icon">💬</span>
                        <h3 class="tool-card-title">Subtitle Generator</h3>
                    </div>
                    <p class="tool-card-desc">
                        Automatically generate and sync subtitles for your videos. Support for multiple 
                        languages with AI-powered transcription and timing adjustment.
                    </p>
                    <div class="tool-card-features">
                        <span class="tool-feature-tag">Auto-Generate</span>
                        <span class="tool-feature-tag">Multi-Language</span>
                        <span class="tool-feature-tag">Sync Tool</span>
                    </div>
                    <span class="tool-card-status coming-soon">
                        <span class="status-dot"></span>
                        Coming Soon
                    </span>
                </div>
                
                <div class="tool-card" style="opacity: 0.7; cursor: not-allowed;">
                    <div class="tool-card-header">
                        <span class="tool-card-icon">🔄</span>
                        <h3 class="tool-card-title">Video Converter</h3>
                    </div>
                    <p class="tool-card-desc">
                        Convert videos between formats online. Support for MP4, MKV, AVI, WebM, and more. 
                        Fast processing with quality preservation.
                    </p>
                    <div class="tool-card-features">
                        <span class="tool-feature-tag">Multiple Formats</span>
                        <span class="tool-feature-tag">Quality Options</span>
                        <span class="tool-feature-tag">Fast</span>
                    </div>
                    <span class="tool-card-status coming-soon">
                        <span class="status-dot"></span>
                        Coming Soon
                    </span>
                </div>
            </div>
        </section>
        
        <!-- AI Tools -->
        <section class="tools-section">
            <div class="tools-section-header">
                <span class="tools-section-icon">🤖</span>
                <div>
                    <h2 class="tools-section-title">AI Tools</h2>
                </div>
                <span class="tools-section-desc">3 tools available</span>
            </div>
            
            <div class="tools-grid">
                <a href="/#voice-search" class="tool-card">
                    <div class="tool-card-header">
                        <span class="tool-card-icon">🎤</span>
                        <h3 class="tool-card-title">Voice Search</h3>
                    </div>
                    <p class="tool-card-desc">
                        Search hands-free using your voice. Powered by Web Speech API with real-time 
                        transcription and instant search execution.
                    </p>
                    <div class="tool-card-features">
                        <span class="tool-feature-tag">Hands-Free</span>
                        <span class="tool-feature-tag">Real-Time</span>
                        <span class="tool-feature-tag">Accurate</span>
                    </div>
                    <span class="tool-card-status active">
                        <span class="status-dot"></span>
                        Active
                    </span>
                </a>
                
                <a href="/#ai-suggestions" class="tool-card">
                    <div class="tool-card-header">
                        <span class="tool-card-icon">💡</span>
                        <h3 class="tool-card-title">Smart Suggestions</h3>
                    </div>
                    <p class="tool-card-desc">
                        AI-powered search improvements as you type. Get 5 intelligent suggestions 
                        based on context, trends, and semantic understanding.
                    </p>
                    <div class="tool-card-features">
                        <span class="tool-feature-tag">AI-Powered</span>
                        <span class="tool-feature-tag">Real-Time</span>
                        <span class="tool-feature-tag">Contextual</span>
                    </div>
                    <span class="tool-card-status active">
                        <span class="status-dot"></span>
                        Active
                    </span>
                </a>
                
                <a href="/#content-analysis" class="tool-card">
                    <div class="tool-card-header">
                        <span class="tool-card-icon">🔍</span>
                        <h3 class="tool-card-title">Content Analysis</h3>
                    </div>
                    <p class="tool-card-desc">
                        Extract detailed metadata from torrent names using AI. Identifies genre, 
                        quality, release year, and content type automatically.
                    </p>
                    <div class="tool-card-features">
                        <span class="tool-feature-tag">AI Analysis</span>
                        <span class="tool-feature-tag">Metadata</span>
                        <span class="tool-feature-tag">Instant</span>
                    </div>
                    <span class="tool-card-status active">
                        <span class="status-dot"></span>
                        Active
                    </span>
                </a>
            </div>
        </section>
        
        <!-- Utility Tools -->
        <section class="tools-section">
            <div class="tools-section-header">
                <span class="tools-section-icon">⚙️</span>
                <div>
                    <h2 class="tools-section-title">Utility Tools</h2>
                </div>
                <span class="tools-section-desc">6 tools (4 active)</span>
            </div>
            
            <div class="tools-grid">
                <a href="tools/shortener.php" class="tool-card">
                    <div class="tool-card-header">
                        <span class="tool-card-icon">🔗</span>
                        <h3 class="tool-card-title">Link Shortener</h3>
                    </div>
                    <p class="tool-card-desc">
                        Create short, shareable links with advanced analytics. Track clicks, generate QR codes, 
                        set expiration dates, and protect links with passwords.
                    </p>
                    <div class="tool-card-features">
                        <span class="tool-feature-tag">Analytics</span>
                        <span class="tool-feature-tag">QR Codes</span>
                        <span class="tool-feature-tag">Password Protected</span>
                    </div>
                    <span class="tool-card-status active">
                        <span class="status-dot"></span>
                        Active
                    </span>
                </a>
                
                <a href="tools/proxy-scraper.php" class="tool-card">
                    <div class="tool-card-header">
                        <span class="tool-card-icon">🔍</span>
                        <h3 class="tool-card-title">Proxy Scraper</h3>
                    </div>
                    <p class="tool-card-desc">
                        Scrape and validate proxies from 10+ sources. Auto-checks working status, 
                        speed, anonymity level, and country. Export as TXT, CSV, or JSON.
                    </p>
                    <div class="tool-card-features">
                        <span class="tool-feature-tag">Multi-Source</span>
                        <span class="tool-feature-tag">Auto-Validate</span>
                        <span class="tool-feature-tag">Export</span>
                    </div>
                    <span class="tool-card-status active">
                        <span class="status-dot"></span>
                        Active
                    </span>
                </a>
                
                <a href="tools/rotating-proxy.php" class="tool-card">
                    <div class="tool-card-header">
                        <span class="tool-card-icon">🔄</span>
                        <h3 class="tool-card-title">Rotating Proxy Maker</h3>
                    </div>
                    <p class="tool-card-desc">
                        Upload 200+ proxies and create a residential rotating proxy pool. 
                        Auto-rotation, health monitoring, API access, and multiple rotation strategies.
                    </p>
                    <div class="tool-card-features">
                        <span class="tool-feature-tag">Rotating Pool</span>
                        <span class="tool-feature-tag">API Access</span>
                        <span class="tool-feature-tag">Health Monitor</span>
                    </div>
                    <span class="tool-card-status active">
                        <span class="status-dot"></span>
                        Active
                    </span>
                </a>
                
                <a href="tools/dorker.php" class="tool-card">
                    <div class="tool-card-header">
                        <span class="tool-card-icon">🔍</span>
                        <h3 class="tool-card-title">Google Dorker</h3>
                    </div>
                    <p class="tool-card-desc">
                        Advanced Google dorking with 50+ operators. Reverse-engineered scraping, 
                        no API required, with 12 pre-built categories and export options.
                    </p>
                    <div class="tool-card-features">
                        <span class="tool-feature-tag">50+ Operators</span>
                        <span class="tool-feature-tag">No API</span>
                        <span class="tool-feature-tag">Fast Results</span>
                    </div>
                    <span class="tool-card-status active">
                        <span class="status-dot"></span>
                        Active
                    </span>
                </a>
            
                <div class="tool-card" style="opacity: 0.7; cursor: not-allowed;">
                    <div class="tool-card-header">
                        <span class="tool-card-icon">📥</span>
                        <h3 class="tool-card-title">Download Manager</h3>
                    </div>
                    <p class="tool-card-desc">
                        Track and manage all your downloads in one place. View history, resume 
                        interrupted downloads, and organize by category.
                    </p>
                    <div class="tool-card-features">
                        <span class="tool-feature-tag">History</span>
                        <span class="tool-feature-tag">Resume</span>
                        <span class="tool-feature-tag">Organize</span>
                    </div>
                    <span class="tool-card-status coming-soon">
                        <span class="status-dot"></span>
                        Coming Soon
                    </span>
                </div>
                
                <div class="tool-card" style="opacity: 0.7; cursor: not-allowed;">
                    <div class="tool-card-header">
                        <span class="tool-card-icon">📊</span>
                        <h3 class="tool-card-title">Bandwidth Monitor</h3>
                    </div>
                    <p class="tool-card-desc">
                        Real-time network usage statistics. Monitor upload/download speeds, 
                        track data consumption, and optimize performance.
                    </p>
                    <div class="tool-card-features">
                        <span class="tool-feature-tag">Real-Time</span>
                        <span class="tool-feature-tag">Statistics</span>
                        <span class="tool-feature-tag">Graphs</span>
                    </div>
                    <span class="tool-card-status coming-soon">
                        <span class="status-dot"></span>
                        Coming Soon
                    </span>
                </div>
            </div>
        </section>
    </div>
    
    <!-- Scripts -->
    <script>
        // Dropdown toggle
        document.querySelectorAll('.dropdown-trigger, .user-menu-trigger').forEach(trigger => {
            trigger.addEventListener('click', function(e) {
                e.stopPropagation();
                const menu = this.nextElementSibling;
                const isOpen = menu.classList.contains('show');
                
                // Close all dropdowns
                document.querySelectorAll('.dropdown-menu').forEach(m => m.classList.remove('show'));
                
                // Toggle current dropdown
                if (!isOpen) {
                    menu.classList.add('show');
                }
            });
        });
        
        // Close dropdowns when clicking outside
        document.addEventListener('click', function() {
            document.querySelectorAll('.dropdown-menu').forEach(menu => {
                menu.classList.remove('show');
            });
        });
        
        // Logout function
        function logoutUser() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = 'auth.php?action=logout';
            }
        }
    </script>
</body>
</html>
