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
$db->exec("CREATE TABLE IF NOT EXISTS scraped_proxies (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    ip TEXT NOT NULL,
    port INTEGER NOT NULL,
    protocol TEXT NOT NULL,
    country TEXT,
    anonymity TEXT,
    speed INTEGER,
    tested_at INTEGER NOT NULL,
    is_working BOOLEAN DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id)
)");

$db->exec("CREATE TABLE IF NOT EXISTS proxy_sources (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    name TEXT NOT NULL,
    url TEXT NOT NULL,
    is_active BOOLEAN DEFAULT 1,
    created_at INTEGER NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
)");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proxy Scraper - Legend House</title>
    <link rel="stylesheet" href="../dashboard-style.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🌐</text></svg>">
    
    <style>
        .proxy-layout {
            display: grid;
            gap: 24px;
        }
        
        .proxy-controls {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 24px;
        }
        
        .control-group {
            background: var(--bg-secondary);
            border: 1px solid var(--border-default);
            border-radius: var(--radius-lg);
            padding: 20px;
        }
        
        .control-group h3 {
            font-size: 15px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .form-group {
            margin-bottom: 16px;
        }
        
        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: var(--text-secondary);
            margin-bottom: 8px;
        }
        
        .form-input, .form-select {
            width: 100%;
            padding: 10px 14px;
            background: var(--bg-tertiary);
            border: 1px solid var(--border-default);
            border-radius: var(--radius-md);
            font-size: 14px;
            color: var(--text-primary);
            transition: all 0.2s;
        }
        
        .form-input:focus, .form-select:focus {
            outline: none;
            border-color: var(--accent-primary);
            background: var(--bg-primary);
        }
        
        .btn-start {
            width: 100%;
            padding: 14px 24px;
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
        
        .btn-start:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
        
        .btn-start:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .progress-section {
            background: var(--bg-secondary);
            border: 1px solid var(--border-default);
            border-radius: var(--radius-lg);
            padding: 24px;
            display: none;
        }
        
        .progress-section.active {
            display: block;
        }
        
        .progress-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }
        
        .progress-stat {
            text-align: center;
            padding: 16px;
            background: var(--bg-tertiary);
            border-radius: var(--radius-md);
        }
        
        .progress-stat-value {
            font-size: 28px;
            font-weight: 700;
            color: var(--text-primary);
        }
        
        .progress-stat-label {
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 4px;
        }
        
        .progress-bar-wrap {
            height: 8px;
            background: var(--bg-tertiary);
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 12px;
        }
        
        .progress-bar {
            height: 100%;
            background: var(--accent-primary);
            width: 0%;
            transition: width 0.3s;
        }
        
        .progress-text {
            font-size: 14px;
            color: var(--text-secondary);
            text-align: center;
        }
        
        .results-section {
            background: var(--bg-secondary);
            border: 1px solid var(--border-default);
            border-radius: var(--radius-lg);
            overflow: hidden;
        }
        
        .results-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border-default);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
        }
        
        .results-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .results-actions {
            display: flex;
            gap: 8px;
        }
        
        .btn-export {
            padding: 8px 14px;
            background: var(--bg-tertiary);
            border: 1px solid var(--border-default);
            border-radius: var(--radius-sm);
            font-size: 13px;
            color: var(--text-secondary);
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-export:hover {
            background: var(--accent-muted);
            border-color: var(--accent-primary);
            color: var(--text-primary);
        }
        
        .results-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .results-table th,
        .results-table td {
            padding: 12px 16px;
            text-align: left;
            border-bottom: 1px solid var(--border-muted);
            font-size: 13px;
        }
        
        .results-table th {
            background: var(--bg-tertiary);
            font-weight: 600;
            color: var(--text-secondary);
        }
        
        .results-table td {
            color: var(--text-primary);
        }
        
        .results-table tr:hover td {
            background: var(--bg-tertiary);
        }
        
        .status-working {
            color: var(--success);
            font-weight: 500;
        }
        
        .status-dead {
            color: var(--danger);
        }
        
        .btn-copy-proxy {
            padding: 4px 10px;
            background: var(--bg-primary);
            border: 1px solid var(--border-default);
            border-radius: var(--radius-sm);
            font-size: 11px;
            color: var(--text-secondary);
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-copy-proxy:hover {
            background: var(--accent-muted);
            color: var(--text-primary);
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
            .progress-stats {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .results-table {
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
                        <li><a href="proxy-scraper.php" class="nav-item active"><span class="nav-icon">🌐</span><span class="sidebar-text">Proxy Scraper</span></a></li>
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
                        <span class="breadcrumb-item active">Proxy Scraper</span>
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
                    <h1 class="page-title">🌐 Proxy Scraper</h1>
                    <p class="page-subtitle">Scrape and validate proxies from 100+ sources with auto-checking</p>
                </div>
                
                <!-- Controls -->
                <div class="proxy-controls">
                    <div class="control-group">
                        <h3>📡 Sources & Filters</h3>
                        
                        <div class="form-group">
                            <label class="form-label">Proxy Sources</label>
                            <select id="sourceSelect" class="form-select">
                                <option value="all">🌐 All Sources (10+)</option>
                                <option value="free-proxy-list">Free Proxy List</option>
                                <option value="proxyscrape">ProxyScrape</option>
                                <option value="geonode">GeoNode</option>
                                <option value="pubproxy">PubProxy</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Protocol</label>
                            <select id="protocolFilter" class="form-select">
                                <option value="all">All Protocols</option>
                                <option value="http">HTTP Only</option>
                                <option value="https">HTTPS Only</option>
                                <option value="socks4">SOCKS4 Only</option>
                                <option value="socks5">SOCKS5 Only</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Country Filter (optional)</label>
                            <input type="text" id="countryFilter" class="form-input" placeholder="e.g., US, UK, FR">
                        </div>
                    </div>
                    
                    <div class="control-group">
                        <h3>⚙️ Settings</h3>
                        
                        <div class="form-group">
                            <label class="form-label">Max Timeout (seconds)</label>
                            <input type="number" id="maxTimeout" class="form-input" value="5" min="1" max="30">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Custom Source URL (optional)</label>
                            <input type="url" id="customSource" class="form-input" placeholder="https://example.com/proxy-list.txt">
                        </div>
                        
                        <button onclick="startScraping()" class="btn-start" id="scrapeBtn">
                            🚀 Start Scraping
                        </button>
                    </div>
                </div>
                
                <!-- Progress Section -->
                <div class="progress-section" id="progressSection">
                    <div class="progress-stats">
                        <div class="progress-stat">
                            <div class="progress-stat-value" id="totalScraped">0</div>
                            <div class="progress-stat-label">Total Scraped</div>
                        </div>
                        <div class="progress-stat">
                            <div class="progress-stat-value" id="totalValidated">0</div>
                            <div class="progress-stat-label">Validated</div>
                        </div>
                        <div class="progress-stat">
                            <div class="progress-stat-value" id="workingProxies">0</div>
                            <div class="progress-stat-label">Working</div>
                        </div>
                        <div class="progress-stat">
                            <div class="progress-stat-value" id="successRate">0%</div>
                            <div class="progress-stat-label">Success Rate</div>
                        </div>
                    </div>
                    
                    <div class="progress-bar-wrap">
                        <div class="progress-bar" id="progressBar"></div>
                    </div>
                    <p class="progress-text" id="progressText">Initializing scraper...</p>
                </div>
                
                <!-- Results Section -->
                <div class="results-section">
                    <div class="results-header">
                        <div class="results-title">✅ Working Proxies</div>
                        <div class="results-actions">
                            <button class="btn-export" onclick="exportProxies('txt')">📄 TXT</button>
                            <button class="btn-export" onclick="exportProxies('csv')">📊 CSV</button>
                            <button class="btn-export" onclick="exportProxies('json')">📋 JSON</button>
                            <button class="btn-export" onclick="clearResults()">🗑️ Clear</button>
                        </div>
                    </div>
                    
                    <div style="overflow-x: auto;">
                        <table class="results-table">
                            <thead>
                                <tr>
                                    <th>IP Address</th>
                                    <th>Port</th>
                                    <th>Protocol</th>
                                    <th>Country</th>
                                    <th>Anonymity</th>
                                    <th>Speed</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="proxiesTableBody">
                                <tr>
                                    <td colspan="8" class="empty-state">
                                        <div class="empty-state-icon">🔍</div>
                                        <div>No proxies yet. Start scraping to see results!</div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="proxy-scraper-script.js"></script>
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
        
        const savedTheme = localStorage.getItem('theme') || 'dark';
        document.body.setAttribute('data-theme', savedTheme);
        document.getElementById('themeIcon').textContent = savedTheme === 'dark' ? '🌙' : '☀️';
        document.getElementById('themeText').textContent = savedTheme === 'dark' ? 'Dark' : 'Light';
        
        if (localStorage.getItem('sidebarCollapsed') === 'true') {
            document.getElementById('sidebar').classList.add('collapsed');
        }
    </script>
    
    <script src="../ai-chat-widget.js"></script>
    <script>document.body.dataset.aiContext = 'general';</script>
</body>
</html>
