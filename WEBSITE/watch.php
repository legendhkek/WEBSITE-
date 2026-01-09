<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Watch Now - Legend House</title>
    <meta name="description" content="Stream movies and TV shows directly in your browser with Legend House's WebTorrent streaming technology.">
    
    <!-- Google AdSense -->
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-1940810089559549"
         crossorigin="anonymous"></script>
    
    <link rel="stylesheet" href="style.css">
    
    
    
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🏠</text></svg>">
    <style>
        .watch-page {
            min-height: 100vh;
            padding-top: 100px;
        }
        
        .watch-hero {
            text-align: center;
            padding: 60px 20px;
        }
        
        .watch-hero h1 {
            font-size: 48px;
            font-weight: 800;
            margin-bottom: 16px;
        }
        
        .watch-hero p {
            font-size: 18px;
            color: var(--text-secondary);
            max-width: 600px;
            margin: 0 auto 40px;
        }
        
        .magnet-form {
            max-width: 700px;
            margin: 0 auto;
        }
        
        .magnet-input-group {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
        }
        
        .magnet-input {
            flex: 1;
            padding: 18px 24px;
            font-size: 15px;
            background: var(--bg-card);
            border: 1px solid var(--border-glass);
            border-radius: 12px;
            color: var(--text-primary);
            outline: none;
            transition: var(--transition);
        }
        
        .magnet-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.2);
        }
        
        .magnet-input::placeholder {
            color: var(--text-muted);
        }
        
        .watch-btn {
            padding: 18px 40px;
            font-size: 16px;
            font-weight: 600;
            background: linear-gradient(135deg, var(--stream), var(--stream-dark));
            border: none;
            border-radius: 12px;
            color: #000;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: var(--transition);
        }
        
        .watch-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(34, 211, 238, 0.4);
        }
        
        .or-divider {
            display: flex;
            align-items: center;
            gap: 20px;
            margin: 30px 0;
            color: var(--text-muted);
        }
        
        .or-divider::before,
        .or-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border-glass);
        }
        
        .search-link {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }
        
        .search-link:hover {
            text-decoration: underline;
        }
        
        /* Player Section */
        .player-section {
            display: none;
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .player-section.active {
            display: block;
        }
        
        .player-wrapper {
            background: #000;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
        }
        
        .player-video {
            width: 100%;
            aspect-ratio: 16/9;
            display: none;
        }
        
        .player-loading {
            width: 100%;
            aspect-ratio: 16/9;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: linear-gradient(180deg, #0a0a12, #0f0f1a);
        }
        
        .loading-spinner {
            width: 80px;
            height: 80px;
            border: 4px solid var(--border-glass);
            border-top-color: var(--stream);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 24px;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .player-loading h3 {
            font-size: 22px;
            margin-bottom: 10px;
        }
        
        .player-loading p {
            color: var(--text-secondary);
            margin-bottom: 24px;
        }
        
        .progress-bar-container {
            width: 80%;
            max-width: 400px;
            height: 8px;
            background: var(--bg-glass);
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 16px;
        }
        
        .progress-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--stream), var(--primary));
            width: 0%;
            transition: width 0.3s;
        }
        
        .stats-row {
            display: flex;
            gap: 24px;
            font-size: 14px;
            color: var(--text-muted);
        }
        
        .stats-row span {
            padding: 8px 16px;
            background: var(--bg-glass);
            border-radius: 8px;
        }
        
        .player-controls {
            padding: 20px;
            background: var(--bg-card);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }
        
        .file-info {
            flex: 1;
        }
        
        .file-info h4 {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 4px;
        }
        
        .file-info p {
            font-size: 13px;
            color: var(--text-muted);
        }
        
        .player-buttons {
            display: flex;
            gap: 12px;
        }
        
        .player-btn {
            padding: 12px 20px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: var(--transition);
        }
        
        .player-btn-outline {
            background: transparent;
            border: 1px solid var(--border-glass);
            color: var(--text-primary);
        }
        
        .player-btn-outline:hover {
            background: var(--bg-glass);
            border-color: var(--primary);
        }
        
        .player-btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--accent));
            border: none;
            color: #fff;
        }
        
        .player-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(245, 158, 11, 0.4);
        }
        
        .player-btn-stop {
            background: var(--danger);
            border: none;
            color: #fff;
        }
        
        .player-btn-stop:hover {
            background: #dc2626;
        }
        
        /* File list */
        .file-list-section {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
            display: none;
        }
        
        .file-list-section.active {
            display: block;
        }
        
        .file-list-header {
            margin-bottom: 20px;
        }
        
        .file-list-header h3 {
            font-size: 20px;
            margin-bottom: 8px;
        }
        
        .file-list-header p {
            color: var(--text-secondary);
        }
        
        .file-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .file-item {
            background: var(--bg-card);
            border: 1px solid var(--border-glass);
            border-radius: 12px;
            padding: 16px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: var(--transition);
        }
        
        .file-item:hover {
            border-color: var(--primary);
        }
        
        .file-item-info {
            display: flex;
            align-items: center;
            gap: 16px;
            flex: 1;
            min-width: 0;
        }
        
        .file-icon {
            font-size: 28px;
        }
        
        .file-details {
            flex: 1;
            min-width: 0;
        }
        
        .file-name {
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .file-size {
            font-size: 13px;
            color: var(--text-muted);
        }
        
        .file-play-btn {
            padding: 10px 20px;
            background: linear-gradient(135deg, var(--stream), var(--stream-dark));
            border: none;
            border-radius: 8px;
            color: #000;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: var(--transition);
        }
        
        .file-play-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(34, 211, 238, 0.4);
        }
        
        @media (max-width: 768px) {
            .watch-hero h1 {
                font-size: 32px;
            }
            
            .magnet-input-group {
                flex-direction: column;
            }
            
            .watch-btn {
                width: 100%;
                justify-content: center;
            }
            
            .player-controls {
                flex-direction: column;
                text-align: center;
            }
            
            .player-buttons {
                width: 100%;
                flex-direction: column;
            }
            
            .player-btn {
                justify-content: center;
            }
            
            .file-item {
                flex-direction: column;
                gap: 16px;
                align-items: stretch;
            }
            
            .file-play-btn {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <!-- Wallpaper -->
    <div class="wallpaper-container" id="wallpaperContainer"></div>
    <div class="wallpaper-overlay"></div>

    <!-- Header -->
    <header class="header">
        <div class="container">
            <a href="/" class="logo">
                <div class="logo-icon">
                    <svg width="42" height="42" viewBox="0 0 42 42">
                        <defs>
                            <linearGradient id="logoGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" style="stop-color:#f59e0b"/>
                                <stop offset="50%" style="stop-color:#ef4444"/>
                                <stop offset="100%" style="stop-color:#ec4899"/>
                            </linearGradient>
                        </defs>
                        <rect x="6" y="12" width="30" height="22" rx="3" fill="none" stroke="url(#logoGrad)" stroke-width="2.5"/>
                        <polygon points="21,6 28,12 14,12" fill="url(#logoGrad)"/>
                        <rect x="17" y="22" width="8" height="12" fill="url(#logoGrad)" opacity="0.7"/>
                    </svg>
                </div>
                <div class="logo-text">
                    <span class="logo-title"><span class="title-gradient">LEGEND</span> HOUSE</span>
                    <span class="logo-version">Stream • Download • Watch</span>
                </div>
            </a>
            <nav class="nav">
                <a href="/" class="nav-btn">
                    <span class="nav-btn-icon">🔍</span>
                    <span class="nav-btn-text">Search</span>
                </a>
            </nav>
        </div>
    </header>

    <!-- Watch Page -->
    <div class="watch-page">
        <section class="watch-hero" id="watchHero">
            <div class="container">
                <h1><span class="title-gradient">Stream</span> Instantly</h1>
                <p>Paste a magnet link below to stream movies and TV shows directly in your browser. No downloads needed.</p>
                
                <form class="magnet-form" id="magnetForm" onsubmit="return startStream()">
                    <div class="magnet-input-group">
                        <input type="text" class="magnet-input" id="magnetInput" 
                               placeholder="Paste magnet link here... magnet:?xt=urn:btih:..." 
                               autocomplete="off" spellcheck="false">
                        <button type="submit" class="watch-btn">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <polygon points="5 3 19 12 5 21 5 3"/>
                            </svg>
                            Stream Now
                        </button>
                    </div>
                </form>
                
                <div class="or-divider">or</div>
                
                <p>
                    <a href="/" class="search-link">🔍 Search for movies and TV shows</a> 
                    to find content with streaming options
                </p>
            </div>
        </section>
        
        <!-- Player Section -->
        <section class="player-section" id="playerSection">
            <div class="player-wrapper">
                <div class="player-loading" id="playerLoading">
                    <div class="loading-spinner"></div>
                    <h3 id="loadingTitle">Connecting to peers...</h3>
                    <p id="loadingStatus">Initializing WebTorrent</p>
                    <div class="progress-bar-container">
                        <div class="progress-bar-fill" id="progressBar"></div>
                    </div>
                    <div class="stats-row">
                        <span id="statPeers">Peers: 0</span>
                        <span id="statSpeed">Speed: 0 KB/s</span>
                        <span id="statProgress">Progress: 0%</span>
                    </div>
                </div>
                <video class="player-video" id="playerVideo" controls></video>
                <div class="player-controls">
                    <div class="file-info">
                        <h4 id="playingFileName">-</h4>
                        <p id="playingFileSize">-</p>
                    </div>
                    <div class="player-buttons">
                        <button class="player-btn player-btn-outline" onclick="toggleFullscreen()">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"/>
                            </svg>
                            Fullscreen
                        </button>
                        <button class="player-btn player-btn-outline" onclick="copyMagnetLink()">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                                <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                            </svg>
                            Copy Link
                        </button>
                        <button class="player-btn player-btn-stop" onclick="stopStream()">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                <rect x="6" y="6" width="12" height="12"/>
                            </svg>
                            Stop
                        </button>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- File List -->
        <section class="file-list-section" id="fileListSection">
            <div class="file-list-header">
                <h3>📁 Torrent Files</h3>
                <p>Select a file to stream</p>
            </div>
            <div class="file-list" id="fileList">
                <!-- Files populated by JS -->
            </div>
        </section>
    </div>

    <!-- Toast Container -->
    <div id="toastContainer" class="toast-container"></div>

    <!-- WebTorrent Script -->
    <script src="https://cdn.jsdelivr.net/npm/webtorrent@latest/webtorrent.min.js"></script>
    <script src="watch.js"></script>
    
    <!-- AI Chat Widget Integration -->
    <script src="ai-chat-widget.js"></script>
    <script>
        // Set context to 'general' for watch page
        document.body.dataset.aiContext = 'general';
    </script>
</body>
</html>
