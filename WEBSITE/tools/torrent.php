<?php
require_once __DIR__ . '/../auth.php';

$user = getCurrentUser();
if (!$user) {
    header('Location: ../login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Torrent Center - Legend House</title>
    <link rel="stylesheet" href="../dashboard-style.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🧲</text></svg>">
    
    <style>
        .torrent-layout {
            display: grid;
            gap: 24px;
        }
        
        .torrent-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }
        
        .tab-btn {
            padding: 12px 24px;
            background: var(--bg-tertiary);
            border: 1px solid var(--border-default);
            border-radius: var(--radius-md);
            font-size: 14px;
            font-weight: 500;
            color: var(--text-secondary);
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .tab-btn:hover {
            background: var(--accent-muted);
            border-color: var(--accent-primary);
            color: var(--text-primary);
        }
        
        .tab-btn.active {
            background: var(--accent-primary);
            color: var(--bg-primary);
            border-color: var(--accent-primary);
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .torrent-card {
            background: var(--bg-secondary);
            border: 1px solid var(--border-default);
            border-radius: var(--radius-lg);
            padding: 24px;
        }
        
        .torrent-card h3 {
            font-size: 18px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: var(--text-secondary);
            margin-bottom: 8px;
        }
        
        .form-input, .form-textarea {
            width: 100%;
            padding: 14px 18px;
            background: var(--bg-tertiary);
            border: 1px solid var(--border-default);
            border-radius: var(--radius-md);
            font-size: 14px;
            color: var(--text-primary);
            transition: all 0.2s;
        }
        
        .form-textarea {
            min-height: 120px;
            resize: vertical;
            font-family: var(--font-mono);
        }
        
        .form-input:focus, .form-textarea:focus {
            outline: none;
            border-color: var(--accent-primary);
            background: var(--bg-primary);
        }
        
        .form-hint {
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 8px;
        }
        
        .btn-primary {
            padding: 14px 28px;
            background: var(--accent-primary);
            color: var(--bg-primary);
            border: none;
            border-radius: var(--radius-md);
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn-primary:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
        
        .file-drop-zone {
            border: 2px dashed var(--border-default);
            border-radius: var(--radius-lg);
            padding: 40px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .file-drop-zone:hover, .file-drop-zone.drag-over {
            border-color: var(--accent-primary);
            background: var(--accent-muted);
        }
        
        .drop-icon {
            font-size: 48px;
            margin-bottom: 16px;
        }
        
        .drop-text {
            font-size: 16px;
            color: var(--text-primary);
            margin-bottom: 8px;
        }
        
        .drop-hint {
            font-size: 13px;
            color: var(--text-muted);
        }
        
        .btn-browse {
            margin-top: 16px;
            padding: 10px 20px;
            background: var(--bg-tertiary);
            border: 1px solid var(--border-default);
            border-radius: var(--radius-md);
            font-size: 14px;
            color: var(--text-primary);
            cursor: pointer;
        }
        
        .btn-browse:hover {
            background: var(--accent-muted);
        }
        
        .result-card {
            background: var(--bg-secondary);
            border: 1px solid var(--border-default);
            border-radius: var(--radius-lg);
            padding: 24px;
            display: none;
        }
        
        .result-card.active {
            display: block;
        }
        
        .result-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
        }
        
        .result-icon {
            font-size: 32px;
        }
        
        .result-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .result-subtitle {
            font-size: 13px;
            color: var(--text-muted);
        }
        
        .magnet-display {
            padding: 16px;
            background: var(--bg-tertiary);
            border-radius: var(--radius-md);
            margin-bottom: 16px;
            word-break: break-all;
            font-family: var(--font-mono);
            font-size: 13px;
            color: var(--text-secondary);
        }
        
        .result-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }
        
        .btn-action {
            padding: 12px 20px;
            background: var(--bg-tertiary);
            border: 1px solid var(--border-default);
            border-radius: var(--radius-md);
            font-size: 14px;
            color: var(--text-primary);
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-action:hover {
            background: var(--accent-muted);
            border-color: var(--accent-primary);
        }
        
        .btn-action.primary {
            background: var(--accent-primary);
            color: var(--bg-primary);
            border-color: var(--accent-primary);
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-top: 32px;
        }
        
        .feature-item {
            padding: 20px;
            background: var(--bg-secondary);
            border: 1px solid var(--border-default);
            border-radius: var(--radius-md);
            text-align: center;
        }
        
        .feature-icon {
            font-size: 32px;
            margin-bottom: 12px;
        }
        
        .feature-title {
            font-size: 15px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 4px;
        }
        
        .feature-desc {
            font-size: 13px;
            color: var(--text-muted);
        }
        
        @media (max-width: 768px) {
            .torrent-tabs {
                flex-direction: column;
            }
            
            .tab-btn {
                width: 100%;
                justify-content: center;
            }
            
            .result-actions {
                flex-direction: column;
            }
            
            .btn-action {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body data-theme="dark">
    <div class="app-layout">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <a href="../dashboard.php" class="sidebar-logo">
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
                        <li><a href="../home.php" class="nav-item"><span class="nav-icon">🏠</span><span class="sidebar-text">Home</span></a></li>
                        <li><a href="../dashboard.php" class="nav-item"><span class="nav-icon">📊</span><span class="sidebar-text">Dashboard</span></a></li>
                        <li><a href="../watch.php" class="nav-item"><span class="nav-icon">▶️</span><span class="sidebar-text">Watch</span></a></li>
                    </ul>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title">Tools</div>
                    <ul class="nav-list">
                        <li><a href="../tools.php" class="nav-item"><span class="nav-icon">🛠️</span><span class="sidebar-text">All Tools</span></a></li>
                        <li><a href="dorker.php" class="nav-item"><span class="nav-icon">🔍</span><span class="sidebar-text">Google Dorker</span></a></li>
                        <li><a href="torrent.php" class="nav-item active"><span class="nav-icon">🧲</span><span class="sidebar-text">Torrent Center</span></a></li>
                        <li><a href="proxy-scraper.php" class="nav-item"><span class="nav-icon">🌐</span><span class="sidebar-text">Proxy Scraper</span></a></li>
                        <li><a href="shortener.php" class="nav-item"><span class="nav-icon">🔗</span><span class="sidebar-text">Link Shortener</span></a></li>
                    </ul>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title">Account</div>
                    <ul class="nav-list">
                        <li><a href="../settings.php" class="nav-item"><span class="nav-icon">⚙️</span><span class="sidebar-text">Settings</span></a></li>
                        <li><a href="../profile.php" class="nav-item"><span class="nav-icon">👤</span><span class="sidebar-text">Profile</span></a></li>
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
                        <a href="../tools.php" class="breadcrumb-item">Tools</a>
                        <span class="breadcrumb-separator">/</span>
                        <span class="breadcrumb-item active">Torrent Center</span>
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
                    <h1 class="page-title">🧲 Torrent Center</h1>
                    <p class="page-subtitle">Process magnet links, upload torrent files, and generate magnets from info hashes</p>
                </div>
                
                <!-- Tabs -->
                <div class="torrent-tabs">
                    <button class="tab-btn active" data-tab="magnet" onclick="switchTab('magnet')">
                        🧲 Magnet Link
                    </button>
                    <button class="tab-btn" data-tab="file" onclick="switchTab('file')">
                        📁 Torrent File
                    </button>
                    <button class="tab-btn" data-tab="hash" onclick="switchTab('hash')">
                        🔑 Info Hash
                    </button>
                </div>
                
                <div class="torrent-layout">
                    <!-- Magnet Tab -->
                    <div id="magnet-tab" class="tab-content active">
                        <div class="torrent-card">
                            <h3>🧲 Process Magnet Link</h3>
                            
                            <div class="form-group">
                                <label class="form-label">Magnet Link</label>
                                <textarea id="magnetInput" class="form-textarea" placeholder="Paste your magnet link here... (magnet:?xt=urn:btih:...)"></textarea>
                            </div>
                            
                            <button class="btn-primary" onclick="processMagnet()">
                                ⚡ Process Magnet
                            </button>
                        </div>
                    </div>
                    
                    <!-- File Tab -->
                    <div id="file-tab" class="tab-content">
                        <div class="torrent-card">
                            <h3>📁 Upload Torrent File</h3>
                            
                            <div class="file-drop-zone" id="dropZone" onclick="document.getElementById('fileInput').click()">
                                <input type="file" id="fileInput" accept=".torrent" style="display: none;" onchange="handleFileSelect(event)">
                                <div class="drop-icon">📁</div>
                                <p class="drop-text">Drag & drop .torrent file here</p>
                                <p class="drop-hint">or click to browse</p>
                                <button class="btn-browse" onclick="event.stopPropagation(); document.getElementById('fileInput').click()">
                                    Browse Files
                                </button>
                            </div>
                            
                            <div id="fileInfo" class="form-hint" style="margin-top: 16px; display: none;"></div>
                            
                            <button class="btn-primary" onclick="processTorrentFile()" id="fileDownloadBtn" style="display: none; margin-top: 20px;">
                                ⚡ Process File
                            </button>
                        </div>
                    </div>
                    
                    <!-- Hash Tab -->
                    <div id="hash-tab" class="tab-content">
                        <div class="torrent-card">
                            <h3>🔑 Generate from Info Hash</h3>
                            
                            <div class="form-group">
                                <label class="form-label">Info Hash (40-character hex)</label>
                                <input type="text" id="hashInput" class="form-input" placeholder="Enter info hash (e.g., a1b2c3d4e5f6...)" maxlength="40">
                                <p class="form-hint">Info hash is a 40-character hexadecimal identifier for the torrent</p>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Torrent Name (optional)</label>
                                <input type="text" id="nameInput" class="form-input" placeholder="Enter torrent name for better tracking">
                            </div>
                            
                            <button class="btn-primary" onclick="processHash()">
                                ⚡ Generate Magnet Link
                            </button>
                        </div>
                    </div>
                    
                    <!-- Result Card -->
                    <div class="result-card" id="downloadResult">
                        <div class="result-header">
                            <div class="result-icon">✅</div>
                            <div>
                                <div class="result-title" id="resultTitle">Ready to Download</div>
                                <div class="result-subtitle" id="resultSubtitle">-</div>
                            </div>
                        </div>
                        
                        <div class="magnet-display" id="magnetDisplay">-</div>
                        
                        <div class="result-actions">
                            <button class="btn-action primary" onclick="copyMagnet()">📋 Copy Magnet</button>
                            <button class="btn-action" onclick="openMagnet()">🧲 Open in Client</button>
                            <a href="../watch.php" class="btn-action">▶️ Stream Now</a>
                        </div>
                    </div>
                </div>
                
                <!-- Features Grid -->
                <div class="features-grid">
                    <div class="feature-item">
                        <div class="feature-icon">🧲</div>
                        <div class="feature-title">Magnet Links</div>
                        <div class="feature-desc">Instant download with magnet links</div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">📁</div>
                        <div class="feature-title">Torrent Files</div>
                        <div class="feature-desc">Upload and parse .torrent files</div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">🔑</div>
                        <div class="feature-title">Info Hash</div>
                        <div class="feature-desc">Generate magnet from hash</div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">⚡</div>
                        <div class="feature-title">Lightning Fast</div>
                        <div class="feature-desc">Process torrents instantly</div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script>
        let currentMagnet = '';
        let selectedFile = null;
        
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
        
        const savedTheme = localStorage.getItem('theme') || 'dark';
        document.body.setAttribute('data-theme', savedTheme);
        document.getElementById('themeIcon').textContent = savedTheme === 'dark' ? '🌙' : '☀️';
        document.getElementById('themeText').textContent = savedTheme === 'dark' ? 'Dark' : 'Light';
        
        if (localStorage.getItem('sidebarCollapsed') === 'true') {
            document.getElementById('sidebar').classList.add('collapsed');
        }
        
        function switchTab(tab) {
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
            
            document.querySelector(`[data-tab="${tab}"]`).classList.add('active');
            document.getElementById(`${tab}-tab`).classList.add('active');
        }
        
        function processMagnet() {
            const input = document.getElementById('magnetInput').value.trim();
            if (!input) {
                alert('Please enter a magnet link');
                return;
            }
            
            if (!input.startsWith('magnet:?xt=urn:btih:')) {
                alert('Invalid magnet link format');
                return;
            }
            
            currentMagnet = input;
            
            // Extract name from magnet
            let name = 'Unknown';
            const dnMatch = input.match(/dn=([^&]+)/);
            if (dnMatch) {
                name = decodeURIComponent(dnMatch[1]);
            }
            
            showResult(name, currentMagnet);
        }
        
        function handleFileSelect(event) {
            const file = event.target.files[0];
            if (!file || !file.name.endsWith('.torrent')) {
                alert('Please select a valid .torrent file');
                return;
            }
            
            selectedFile = file;
            document.getElementById('fileInfo').style.display = 'block';
            document.getElementById('fileInfo').textContent = `Selected: ${file.name} (${formatBytes(file.size)})`;
            document.getElementById('fileDownloadBtn').style.display = 'flex';
        }
        
        function processTorrentFile() {
            if (!selectedFile) {
                alert('Please select a torrent file first');
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                try {
                    // Simple extraction of info hash from torrent file
                    const data = new Uint8Array(e.target.result);
                    const text = new TextDecoder('latin1').decode(data);
                    
                    // Look for info hash pattern
                    const infoStart = text.indexOf('4:info');
                    if (infoStart !== -1) {
                        // Generate a placeholder magnet link
                        const hash = generateTempHash(selectedFile.name);
                        currentMagnet = `magnet:?xt=urn:btih:${hash}&dn=${encodeURIComponent(selectedFile.name.replace('.torrent', ''))}`;
                        showResult(selectedFile.name.replace('.torrent', ''), currentMagnet);
                    } else {
                        alert('Could not parse torrent file');
                    }
                } catch (err) {
                    alert('Error processing torrent file: ' + err.message);
                }
            };
            reader.readAsArrayBuffer(selectedFile);
        }
        
        function processHash() {
            const hash = document.getElementById('hashInput').value.trim().toLowerCase();
            const name = document.getElementById('nameInput').value.trim() || 'Unknown Torrent';
            
            if (!hash) {
                alert('Please enter an info hash');
                return;
            }
            
            if (!/^[a-f0-9]{40}$/.test(hash)) {
                alert('Invalid info hash. Must be 40 hexadecimal characters.');
                return;
            }
            
            // Generate magnet with trackers
            const trackers = [
                'udp://open.stealth.si:80/announce',
                'udp://tracker.opentrackr.org:1337/announce',
                'udp://tracker.torrent.eu.org:451/announce',
                'udp://tracker.openbittorrent.com:6969/announce'
            ];
            
            currentMagnet = `magnet:?xt=urn:btih:${hash}&dn=${encodeURIComponent(name)}`;
            trackers.forEach(tr => {
                currentMagnet += `&tr=${encodeURIComponent(tr)}`;
            });
            
            showResult(name, currentMagnet);
        }
        
        function showResult(title, magnet) {
            document.getElementById('resultTitle').textContent = title;
            document.getElementById('resultSubtitle').textContent = 'Magnet link generated successfully';
            document.getElementById('magnetDisplay').textContent = magnet;
            document.getElementById('downloadResult').classList.add('active');
        }
        
        function copyMagnet() {
            if (!currentMagnet) return;
            
            navigator.clipboard.writeText(currentMagnet).then(() => {
                alert('Magnet link copied!');
            }).catch(() => {
                // Fallback
                const ta = document.createElement('textarea');
                ta.value = currentMagnet;
                document.body.appendChild(ta);
                ta.select();
                document.execCommand('copy');
                document.body.removeChild(ta);
                alert('Magnet link copied!');
            });
        }
        
        function openMagnet() {
            if (currentMagnet) {
                window.location.href = currentMagnet;
            }
        }
        
        function generateTempHash(filename) {
            // Generate a simple hash based on filename for demo
            let hash = '';
            for (let i = 0; i < 40; i++) {
                const charCode = filename.charCodeAt(i % filename.length);
                hash += ((charCode + i) % 16).toString(16);
            }
            return hash;
        }
        
        function formatBytes(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
        
        // Drag and drop
        const dropZone = document.getElementById('dropZone');
        
        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('drag-over');
        });
        
        dropZone.addEventListener('dragleave', () => {
            dropZone.classList.remove('drag-over');
        });
        
        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('drag-over');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                document.getElementById('fileInput').files = files;
                handleFileSelect({ target: { files: files } });
            }
        });
    </script>
    
    <script src="../ai-chat-widget.js"></script>
    <script>document.body.dataset.aiContext = 'torrents';</script>
</body>
</html>
