<?php
require_once __DIR__ . '/../auth.php';

$user = getCurrentUser();
if (!$user) {
    header('Location: ../login.php');
    exit;
}

// Initialize database
$db = getDatabase();

// Create tables
$db->exec("CREATE TABLE IF NOT EXISTS short_urls (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    original_url TEXT NOT NULL,
    short_code TEXT UNIQUE NOT NULL,
    password TEXT,
    expires_at INTEGER,
    click_limit INTEGER,
    clicks INTEGER DEFAULT 0,
    created_at INTEGER NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
)");

$db->exec("CREATE TABLE IF NOT EXISTS url_clicks (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    short_url_id INTEGER NOT NULL,
    ip_address TEXT,
    user_agent TEXT,
    referrer TEXT,
    clicked_at INTEGER NOT NULL,
    FOREIGN KEY (short_url_id) REFERENCES short_urls(id)
)");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Link Shortener - Legend House</title>
    <link rel="stylesheet" href="../dashboard-style.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🔗</text></svg>">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    
    <style>
        .shortener-layout {
            display: grid;
            gap: 24px;
        }
        
        .shortener-form-card {
            background: var(--bg-secondary);
            border: 1px solid var(--border-default);
            border-radius: var(--radius-lg);
            padding: 24px;
        }
        
        .shortener-form-card h3 {
            font-size: 18px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .url-input-group {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
        }
        
        .url-input {
            flex: 1;
            padding: 14px 18px;
            background: var(--bg-tertiary);
            border: 1px solid var(--border-default);
            border-radius: var(--radius-md);
            font-size: 15px;
            color: var(--text-primary);
            transition: all 0.2s;
        }
        
        .url-input:focus {
            outline: none;
            border-color: var(--accent-primary);
            background: var(--bg-primary);
        }
        
        .btn-shorten {
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
            gap: 8px;
        }
        
        .btn-shorten:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
        
        .advanced-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            padding: 20px;
            background: var(--bg-tertiary);
            border-radius: var(--radius-md);
            margin-bottom: 20px;
        }
        
        .option-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .option-label {
            font-size: 13px;
            font-weight: 500;
            color: var(--text-secondary);
        }
        
        .option-input {
            padding: 10px 14px;
            background: var(--bg-secondary);
            border: 1px solid var(--border-default);
            border-radius: var(--radius-sm);
            font-size: 14px;
            color: var(--text-primary);
        }
        
        .option-input:focus {
            outline: none;
            border-color: var(--accent-primary);
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
        
        .short-url-display {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px;
            background: var(--bg-tertiary);
            border-radius: var(--radius-md);
            margin-bottom: 16px;
        }
        
        .short-url-text {
            flex: 1;
            font-size: 16px;
            font-weight: 600;
            color: var(--info);
            word-break: break-all;
        }
        
        .btn-copy {
            padding: 10px 16px;
            background: var(--accent-primary);
            color: var(--bg-primary);
            border: none;
            border-radius: var(--radius-sm);
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-copy:hover {
            opacity: 0.9;
        }
        
        .qr-section {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 20px;
            background: var(--bg-tertiary);
            border-radius: var(--radius-md);
        }
        
        #qrcode {
            background: white;
            padding: 10px;
            border-radius: 8px;
        }
        
        .qr-info {
            flex: 1;
        }
        
        .qr-info h4 {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 8px;
        }
        
        .qr-info p {
            font-size: 13px;
            color: var(--text-muted);
        }
        
        .links-section {
            background: var(--bg-secondary);
            border: 1px solid var(--border-default);
            border-radius: var(--radius-lg);
            overflow: hidden;
        }
        
        .links-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border-default);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .links-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .links-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .links-table th,
        .links-table td {
            padding: 12px 16px;
            text-align: left;
            border-bottom: 1px solid var(--border-muted);
            font-size: 13px;
        }
        
        .links-table th {
            background: var(--bg-tertiary);
            font-weight: 600;
            color: var(--text-secondary);
        }
        
        .links-table td {
            color: var(--text-primary);
        }
        
        .links-table tr:hover td {
            background: var(--bg-tertiary);
        }
        
        .link-url {
            color: var(--info);
            text-decoration: none;
            font-weight: 500;
        }
        
        .link-url:hover {
            text-decoration: underline;
        }
        
        .btn-small {
            padding: 4px 10px;
            background: var(--bg-primary);
            border: 1px solid var(--border-default);
            border-radius: var(--radius-sm);
            font-size: 11px;
            color: var(--text-secondary);
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-small:hover {
            background: var(--accent-muted);
            color: var(--text-primary);
        }
        
        .btn-delete {
            color: var(--danger);
        }
        
        .empty-state {
            padding: 60px 20px;
            text-align: center;
            color: var(--text-muted);
        }
        
        .empty-state-icon {
            font-size: 48px;
            margin-bottom: 16px;
        }
        
        @media (max-width: 768px) {
            .url-input-group {
                flex-direction: column;
            }
            
            .btn-shorten {
                width: 100%;
                justify-content: center;
            }
            
            .qr-section {
                flex-direction: column;
                text-align: center;
            }
            
            .links-table {
                display: block;
                overflow-x: auto;
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
                        <li><a href="torrent.php" class="nav-item"><span class="nav-icon">🧲</span><span class="sidebar-text">Torrent Center</span></a></li>
                        <li><a href="proxy-scraper.php" class="nav-item"><span class="nav-icon">🌐</span><span class="sidebar-text">Proxy Scraper</span></a></li>
                        <li><a href="shortener.php" class="nav-item active"><span class="nav-icon">🔗</span><span class="sidebar-text">Link Shortener</span></a></li>
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
                        <span class="breadcrumb-item active">Link Shortener</span>
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
                    <h1 class="page-title">🔗 Link Shortener</h1>
                    <p class="page-subtitle">Create short, shareable links with analytics, QR codes, and password protection</p>
                </div>
                
                <div class="shortener-layout">
                    <!-- Create Link Form -->
                    <div class="shortener-form-card">
                        <h3>✨ Create Short Link</h3>
                        
                        <div class="url-input-group">
                            <input type="url" id="originalUrl" class="url-input" placeholder="Enter your long URL here...">
                            <button onclick="createShortUrl()" class="btn-shorten">
                                🚀 Shorten
                            </button>
                        </div>
                        
                        <div class="advanced-options">
                            <div class="option-group">
                                <label class="option-label">Custom Code (optional)</label>
                                <input type="text" id="customCode" class="option-input" placeholder="e.g., my-link">
                            </div>
                            <div class="option-group">
                                <label class="option-label">Password (optional)</label>
                                <input type="password" id="password" class="option-input" placeholder="Protect with password">
                            </div>
                            <div class="option-group">
                                <label class="option-label">Expires In</label>
                                <select id="expiresIn" class="option-input">
                                    <option value="">Never</option>
                                    <option value="1">1 Hour</option>
                                    <option value="24">24 Hours</option>
                                    <option value="168">7 Days</option>
                                    <option value="720">30 Days</option>
                                </select>
                            </div>
                            <div class="option-group">
                                <label class="option-label">Click Limit</label>
                                <input type="number" id="clickLimit" class="option-input" placeholder="Unlimited" min="1">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Result Card -->
                    <div class="result-card" id="resultCard">
                        <h3 style="font-size: 18px; font-weight: 600; color: var(--text-primary); margin-bottom: 20px;">✅ Link Created!</h3>
                        
                        <div class="short-url-display">
                            <span class="short-url-text" id="shortUrlText">-</span>
                            <button class="btn-copy" onclick="copyShortUrl()">📋 Copy</button>
                        </div>
                        
                        <div class="qr-section">
                            <div id="qrcode"></div>
                            <div class="qr-info">
                                <h4>📱 QR Code</h4>
                                <p>Scan to open the short link directly on mobile devices</p>
                                <button class="btn-small" onclick="downloadQR()" style="margin-top: 10px;">⬇️ Download QR</button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Links List -->
                    <div class="links-section">
                        <div class="links-header">
                            <div class="links-title">📊 Your Links</div>
                        </div>
                        
                        <div style="overflow-x: auto;">
                            <table class="links-table">
                                <thead>
                                    <tr>
                                        <th>Short URL</th>
                                        <th>Original URL</th>
                                        <th>Clicks</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="linksTableBody">
                                    <tr>
                                        <td colspan="5" class="empty-state">
                                            <div class="empty-state-icon">🔗</div>
                                            <div>No links yet. Create your first short link above!</div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="shortener-script.js"></script>
    <script>
        let currentShortUrl = '';
        let qrCode = null;
        
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
        
        async function createShortUrl() {
            const url = document.getElementById('originalUrl').value.trim();
            if (!url) {
                alert('Please enter a URL');
                return;
            }
            
            try {
                const response = await fetch('shortener-api.php?action=create', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        url: url,
                        custom_code: document.getElementById('customCode').value,
                        password: document.getElementById('password').value,
                        expires_in: document.getElementById('expiresIn').value,
                        click_limit: document.getElementById('clickLimit').value
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    currentShortUrl = window.location.origin + '/s/' + data.short_code;
                    document.getElementById('shortUrlText').textContent = currentShortUrl;
                    document.getElementById('resultCard').classList.add('active');
                    
                    // Generate QR Code
                    const qrContainer = document.getElementById('qrcode');
                    qrContainer.innerHTML = '';
                    qrCode = new QRCode(qrContainer, {
                        text: currentShortUrl,
                        width: 128,
                        height: 128
                    });
                    
                    loadLinks();
                } else {
                    alert(data.error || 'Failed to create short URL');
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        }
        
        function copyShortUrl() {
            navigator.clipboard.writeText(currentShortUrl).then(() => {
                alert('URL copied!');
            });
        }
        
        function downloadQR() {
            const canvas = document.querySelector('#qrcode canvas');
            if (canvas) {
                const link = document.createElement('a');
                link.download = 'qr-code.png';
                link.href = canvas.toDataURL();
                link.click();
            }
        }
        
        async function loadLinks() {
            try {
                const response = await fetch('shortener-api.php?action=list');
                const data = await response.json();
                
                if (data.success && data.links.length > 0) {
                    const tbody = document.getElementById('linksTableBody');
                    tbody.innerHTML = data.links.map(link => `
                        <tr>
                            <td><a href="${window.location.origin}/s/${link.short_code}" target="_blank" class="link-url">${link.short_code}</a></td>
                            <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis;">${escapeHtml(link.original_url)}</td>
                            <td>${link.clicks}</td>
                            <td>${new Date(link.created_at * 1000).toLocaleDateString()}</td>
                            <td>
                                <button class="btn-small" onclick="copyLink('${link.short_code}')">📋</button>
                                <button class="btn-small btn-delete" onclick="deleteLink(${link.id})">🗑️</button>
                            </td>
                        </tr>
                    `).join('');
                }
            } catch (error) {
                console.error('Error loading links:', error);
            }
        }
        
        function copyLink(code) {
            navigator.clipboard.writeText(window.location.origin + '/s/' + code).then(() => {
                alert('URL copied!');
            });
        }
        
        async function deleteLink(id) {
            if (!confirm('Delete this link?')) return;
            
            try {
                await fetch('shortener-api.php?action=delete', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id })
                });
                loadLinks();
            } catch (error) {
                alert('Error: ' + error.message);
            }
        }
        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        // Load links on page load
        loadLinks();
    </script>
    
    <script src="../ai-chat-widget.js"></script>
    <script>document.body.dataset.aiContext = 'general';</script>
</body>
</html>
