<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Torrent Download - Legend House</title>
    
    <!-- Google AdSense -->
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-1940810089559549"
         crossorigin="anonymous"></script>
    
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="torrent-style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🏠</text></svg>">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <a href="../index.php" class="logo">
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
                    <span class="logo-version">Torrent Download Center</span>
                </div>
            </a>
            <nav class="nav">
                <a href="../index.php" class="nav-btn">
                    <span class="nav-btn-icon">🏠</span>
                    <span class="nav-btn-text">Home</span>
                </a>
                <div id="userNav"></div>
            </nav>
        </div>
    </header>

    <!-- Torrent Upload Section -->
    <section class="torrent-section">
        <div class="container">
            <div class="torrent-hero">
                <h1 class="torrent-title">
                    <span class="title-gradient">Advanced Torrent</span> Download Center
                </h1>
                <p class="torrent-subtitle">Upload torrent file, paste magnet link, or enter info hash</p>
            </div>

            <!-- Torrent Input Card -->
            <div class="torrent-card">
                <div class="torrent-tabs">
                    <button class="tab-btn active" data-tab="magnet" onclick="switchTab('magnet')">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"/>
                            <path d="M8 12l4-4 4 4M12 16V8"/>
                        </svg>
                        Magnet Link
                    </button>
                    <button class="tab-btn" data-tab="file" onclick="switchTab('file')">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                        </svg>
                        Torrent File
                    </button>
                    <button class="tab-btn" data-tab="hash" onclick="switchTab('hash')">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="4" y1="9" x2="20" y2="9"/>
                            <line x1="4" y1="15" x2="20" y2="15"/>
                            <line x1="10" y1="3" x2="8" y2="21"/>
                            <line x1="16" y1="3" x2="14" y2="21"/>
                        </svg>
                        Info Hash
                    </button>
                </div>

                <!-- Magnet Tab -->
                <div id="magnet-tab" class="tab-content active">
                    <div class="input-group">
                        <label class="input-label">Magnet Link</label>
                        <textarea id="magnetInput" class="torrent-textarea" placeholder="Paste magnet link here... (magnet:?xt=urn:btih:...)" rows="4"></textarea>
                    </div>
                    <button class="btn-download-torrent" onclick="processMagnet()">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="7 10 12 15 17 10"/>
                            <line x1="12" y1="15" x2="12" y2="3"/>
                        </svg>
                        Download Torrent
                    </button>
                </div>

                <!-- File Tab -->
                <div id="file-tab" class="tab-content">
                    <div class="input-group">
                        <label class="input-label">Upload Torrent File</label>
                        <div class="file-drop-zone" id="dropZone">
                            <input type="file" id="fileInput" accept=".torrent" style="display: none;" onchange="handleFileSelect(event)">
                            <div class="drop-icon">📁</div>
                            <p class="drop-text">Drag & drop .torrent file here</p>
                            <p class="drop-hint">or click to browse</p>
                            <button class="btn-browse" onclick="document.getElementById('fileInput').click()">
                                Browse Files
                            </button>
                        </div>
                        <div id="fileInfo" class="file-info" style="display: none;"></div>
                    </div>
                    <button class="btn-download-torrent" onclick="processTorrentFile()" id="fileDownloadBtn" style="display: none;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="7 10 12 15 17 10"/>
                            <line x1="12" y1="15" x2="12" y2="3"/>
                        </svg>
                        Download Torrent
                    </button>
                </div>

                <!-- Hash Tab -->
                <div id="hash-tab" class="tab-content">
                    <div class="input-group">
                        <label class="input-label">Info Hash (40-character hex)</label>
                        <input type="text" id="hashInput" class="torrent-input" placeholder="Enter info hash (e.g., a1b2c3d4e5f6...)" maxlength="40" pattern="[a-fA-F0-9]{40}">
                        <small class="input-hint">Info hash is a 40-character hexadecimal identifier</small>
                    </div>
                    <div class="input-group">
                        <label class="input-label">Torrent Name (Optional)</label>
                        <input type="text" id="nameInput" class="torrent-input" placeholder="Enter torrent name for better tracking">
                    </div>
                    <button class="btn-download-torrent" onclick="processHash()">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="7 10 12 15 17 10"/>
                            <line x1="12" y1="15" x2="12" y2="3"/>
                        </svg>
                        Generate Magnet Link
                    </button>
                </div>
            </div>

            <!-- Download Result -->
            <div id="downloadResult" class="download-result" style="display: none;"></div>

            <!-- Features Grid -->
            <div class="features-grid-torrent">
                <div class="feature-card-torrent">
                    <div class="feature-icon-torrent">🧲</div>
                    <h3>Magnet Links</h3>
                    <p>Instant download with magnet links</p>
                </div>
                <div class="feature-card-torrent">
                    <div class="feature-icon-torrent">📁</div>
                    <h3>Torrent Files</h3>
                    <p>Upload and parse .torrent files</p>
                </div>
                <div class="feature-card-torrent">
                    <div class="feature-icon-torrent">🔑</div>
                    <h3>Info Hash</h3>
                    <p>Generate magnet from hash</p>
                </div>
                <div class="feature-card-torrent">
                    <div class="feature-icon-torrent">⚡</div>
                    <h3>Lightning Fast</h3>
                    <p>Process torrents instantly</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-left">
                    <span class="footer-brand">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="8" width="18" height="13" rx="2"/>
                            <polygon points="12,3 19,8 5,8"/>
                        </svg>
                        LEGEND HOUSE
                    </span>
                    <span class="footer-divider"></span>
                    <span class="footer-stats">Advanced Torrent Center</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Toast Container -->
    <div id="toastContainer" class="toast-container"></div>

    <script src="https://cdn.jsdelivr.net/npm/webtorrent@latest/webtorrent.min.js"></script>
    <script src="torrent-script.js"></script>
    <script src="script.js"></script>
</body>
</html>
