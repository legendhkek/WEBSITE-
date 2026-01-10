<?php
require_once __DIR__ . '/../config.php';
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
    <title>Google Dorker - Legend House</title>
    <link rel="stylesheet" href="../dashboard-style.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🔍</text></svg>">
    
    <style>
        /* Dorker Specific Styles */
        .dorker-layout {
            display: grid;
            grid-template-columns: 350px 1fr;
            gap: 24px;
            height: calc(100vh - 180px);
        }
        
        .dorker-sidebar {
            display: flex;
            flex-direction: column;
            gap: 16px;
            overflow-y: auto;
        }
        
        .dorker-main {
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        
        .dork-builder {
            background: var(--bg-secondary);
            border: 1px solid var(--border-default);
            border-radius: var(--radius-lg);
            padding: 20px;
        }
        
        .dork-builder h3 {
            font-size: 16px;
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
        
        .form-input {
            width: 100%;
            padding: 10px 14px;
            background: var(--bg-tertiary);
            border: 1px solid var(--border-default);
            border-radius: var(--radius-md);
            font-size: 14px;
            color: var(--text-primary);
            transition: all 0.2s;
        }
        
        .form-input:focus {
            outline: none;
            border-color: var(--accent-primary);
            background: var(--bg-primary);
        }
        
        .form-input::placeholder {
            color: var(--text-muted);
        }
        
        textarea.form-input {
            min-height: 100px;
            resize: vertical;
            font-family: var(--font-mono);
        }
        
        .category-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
            margin-bottom: 16px;
        }
        
        .category-btn {
            padding: 10px 12px;
            background: var(--bg-tertiary);
            border: 1px solid var(--border-default);
            border-radius: var(--radius-md);
            font-size: 12px;
            font-weight: 500;
            color: var(--text-secondary);
            cursor: pointer;
            transition: all 0.2s;
            text-align: left;
        }
        
        .category-btn:hover {
            background: var(--accent-muted);
            border-color: var(--accent-primary);
            color: var(--text-primary);
        }
        
        .category-btn.active {
            background: var(--accent-primary);
            color: var(--bg-primary);
            border-color: var(--accent-primary);
        }
        
        .btn-primary {
            width: 100%;
            padding: 12px 20px;
            background: var(--accent-primary);
            color: var(--bg-primary);
            border: none;
            border-radius: var(--radius-md);
            font-size: 14px;
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
            transform: translateY(-1px);
        }
        
        .btn-primary:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }
        
        .results-panel {
            flex: 1;
            background: var(--bg-secondary);
            border: 1px solid var(--border-default);
            border-radius: var(--radius-lg);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        
        .results-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border-default);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .results-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .results-count {
            background: var(--accent-muted);
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .export-btns {
            display: flex;
            gap: 8px;
        }
        
        .export-btn {
            padding: 6px 12px;
            background: var(--bg-tertiary);
            border: 1px solid var(--border-default);
            border-radius: var(--radius-sm);
            font-size: 12px;
            color: var(--text-secondary);
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .export-btn:hover {
            background: var(--accent-muted);
            border-color: var(--accent-primary);
            color: var(--text-primary);
        }
        
        .results-body {
            flex: 1;
            overflow-y: auto;
            padding: 16px;
        }
        
        .result-item {
            padding: 16px;
            background: var(--bg-tertiary);
            border: 1px solid var(--border-default);
            border-radius: var(--radius-md);
            margin-bottom: 12px;
            transition: all 0.2s;
        }
        
        .result-item:hover {
            border-color: var(--accent-primary);
        }
        
        .result-item:last-child {
            margin-bottom: 0;
        }
        
        .result-title {
            font-size: 15px;
            font-weight: 600;
            color: var(--info);
            margin-bottom: 4px;
            display: block;
            text-decoration: none;
        }
        
        .result-title:hover {
            text-decoration: underline;
        }
        
        .result-url {
            font-size: 12px;
            color: var(--success);
            margin-bottom: 8px;
            word-break: break-all;
        }
        
        .result-desc {
            font-size: 13px;
            color: var(--text-secondary);
            line-height: 1.5;
            margin-bottom: 10px;
        }
        
        .result-actions {
            display: flex;
            gap: 8px;
        }
        
        .result-action {
            padding: 4px 10px;
            background: var(--bg-secondary);
            border: 1px solid var(--border-default);
            border-radius: var(--radius-sm);
            font-size: 11px;
            color: var(--text-secondary);
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .result-action:hover {
            background: var(--accent-muted);
            color: var(--text-primary);
        }
        
        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 48px;
            text-align: center;
            color: var(--text-muted);
        }
        
        .empty-state-icon {
            font-size: 48px;
            margin-bottom: 16px;
        }
        
        .empty-state-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 8px;
        }
        
        .loading-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 48px;
        }
        
        .spinner {
            width: 40px;
            height: 40px;
            border: 3px solid var(--border-default);
            border-top-color: var(--accent-primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 16px;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .operators-help {
            background: var(--bg-secondary);
            border: 1px solid var(--border-default);
            border-radius: var(--radius-lg);
            padding: 16px;
        }
        
        .operators-help h4 {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 12px;
        }
        
        .operator-item {
            padding: 6px 0;
            font-size: 12px;
            color: var(--text-secondary);
            border-bottom: 1px solid var(--border-muted);
        }
        
        .operator-item:last-child {
            border-bottom: none;
        }
        
        .operator-item code {
            background: var(--bg-tertiary);
            padding: 2px 6px;
            border-radius: 4px;
            font-family: var(--font-mono);
            color: var(--text-primary);
        }
        
        .stats-mini {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 24px;
        }
        
        .stat-mini {
            background: var(--bg-secondary);
            border: 1px solid var(--border-default);
            border-radius: var(--radius-md);
            padding: 16px;
            text-align: center;
        }
        
        .stat-mini-value {
            font-size: 24px;
            font-weight: 700;
            color: var(--text-primary);
        }
        
        .stat-mini-label {
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 4px;
        }
        
        @media (max-width: 1200px) {
            .dorker-layout {
                grid-template-columns: 1fr;
                height: auto;
            }
            
            .stats-mini {
                grid-template-columns: repeat(2, 1fr);
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
                        <li><a href="dorker.php" class="nav-item active"><span class="nav-icon">🔍</span><span class="sidebar-text">Google Dorker</span></a></li>
                        <li><a href="torrent.php" class="nav-item"><span class="nav-icon">🧲</span><span class="sidebar-text">Torrent Center</span></a></li>
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
                        <span class="breadcrumb-item active">Google Dorker</span>
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
                    <h1 class="page-title">🔍 Google Dorker</h1>
                    <p class="page-subtitle">Advanced Google search with 100+ dork operators - AI powered, no API required</p>
                </div>
                
                <!-- Stats -->
                <div class="stats-mini">
                    <div class="stat-mini">
                        <div class="stat-mini-value" id="totalDorks">0</div>
                        <div class="stat-mini-label">Dorks Run</div>
                    </div>
                    <div class="stat-mini">
                        <div class="stat-mini-value" id="totalResults">0</div>
                        <div class="stat-mini-label">Results Found</div>
                    </div>
                    <div class="stat-mini">
                        <div class="stat-mini-value" id="savedQueries">0</div>
                        <div class="stat-mini-label">Saved Queries</div>
                    </div>
                    <div class="stat-mini">
                        <div class="stat-mini-value" id="successRate">0%</div>
                        <div class="stat-mini-label">Success Rate</div>
                    </div>
                </div>
                
                <!-- Main Layout -->
                <div class="dorker-layout">
                    <!-- Left Panel -->
                    <div class="dorker-sidebar">
                        <div class="dork-builder">
                            <h3>🎯 Build Your Dork</h3>
                            
                            <div class="form-group">
                                <label class="form-label">Dork Query</label>
                                <textarea id="dorkQuery" class="form-input" placeholder='site:example.com intitle:"admin" filetype:php'></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Quick Categories</label>
                                <div class="category-grid">
                                    <button class="category-btn" data-category="admin">🔐 Admin Panels</button>
                                    <button class="category-btn" data-category="files">📁 Exposed Files</button>
                                    <button class="category-btn" data-category="vulns">⚠️ Vulnerabilities</button>
                                    <button class="category-btn" data-category="logins">🔑 Login Pages</button>
                                    <button class="category-btn" data-category="docs">📄 Sensitive Docs</button>
                                    <button class="category-btn" data-category="dirs">📂 Directories</button>
                                    <button class="category-btn" data-category="api-keys">🔑 API Keys</button>
                                    <button class="category-btn" data-category="database">💾 Databases</button>
                                </div>
                            </div>
                            
                            <button class="btn-primary" onclick="startDorking()" id="dorkBtn">
                                <span>🚀</span> Start Dorking
                            </button>
                        </div>
                        
                        <div class="operators-help">
                            <h4>📚 Common Operators</h4>
                            <div class="operator-item"><code>site:</code> Search specific domain</div>
                            <div class="operator-item"><code>intitle:</code> Search in page title</div>
                            <div class="operator-item"><code>inurl:</code> Search in URL</div>
                            <div class="operator-item"><code>filetype:</code> Specific file type</div>
                            <div class="operator-item"><code>intext:</code> Search page body</div>
                            <div class="operator-item"><code>"quotes"</code> Exact phrase match</div>
                            <div class="operator-item"><code>-</code> Exclude term</div>
                            <div class="operator-item"><code>|</code> OR operator</div>
                        </div>
                    </div>
                    
                    <!-- Results Panel -->
                    <div class="results-panel">
                        <div class="results-header">
                            <div class="results-title">
                                <span>📊</span> Results
                                <span class="results-count" id="resultsCount">0</span>
                            </div>
                            <div class="export-btns">
                                <button class="export-btn" onclick="exportResults('txt')">📄 TXT</button>
                                <button class="export-btn" onclick="exportResults('csv')">📊 CSV</button>
                                <button class="export-btn" onclick="exportResults('json')">📋 JSON</button>
                            </div>
                        </div>
                        
                        <div class="results-body" id="resultsContainer">
                            <div class="empty-state">
                                <div class="empty-state-icon">🔍</div>
                                <div class="empty-state-title">No results yet</div>
                                <p>Enter a dork query and click "Start Dorking" to begin</p>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script>
        // Category presets
        const categoryDorks = {
            admin: 'intitle:"admin" OR intitle:"administrator" OR intitle:"login" inurl:admin OR inurl:administrator',
            files: 'filetype:env OR filetype:sql OR filetype:log OR filetype:conf OR filetype:bak',
            vulns: 'intext:"sql syntax near" OR intext:"mysql_fetch" OR intext:"Warning: mysql"',
            logins: 'inurl:wp-admin OR inurl:administrator OR inurl:cpanel OR inurl:webmail OR inurl:login',
            docs: 'filetype:pdf OR filetype:doc OR filetype:xlsx intext:"confidential" OR intext:"password"',
            dirs: 'intitle:"index of" inurl:/admin/ OR inurl:/backup/ OR inurl:/config/',
            'api-keys': 'intext:"api_key" OR intext:"apikey" OR intext:"api key" filetype:json OR filetype:txt',
            database: 'filetype:sql OR filetype:db OR filetype:mdb intext:INSERT OR intext:"CREATE TABLE"'
        };
        
        let currentResults = [];
        
        // Category buttons
        document.querySelectorAll('.category-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const category = this.dataset.category;
                document.getElementById('dorkQuery').value = categoryDorks[category];
                document.querySelectorAll('.category-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
            });
        });
        
        // Start dorking
        async function startDorking() {
            const query = document.getElementById('dorkQuery').value.trim();
            if (!query) {
                alert('Please enter a dork query');
                return;
            }
            
            const btn = document.getElementById('dorkBtn');
            btn.disabled = true;
            btn.innerHTML = '<span>⏳</span> Searching...';
            
            document.getElementById('resultsContainer').innerHTML = `
                <div class="loading-state">
                    <div class="spinner"></div>
                    <p style="color: var(--text-secondary);">Searching with advanced dorking...</p>
                </div>
            `;
            
            try {
                const response = await fetch('dorker-api.php?action=dork', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ query })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    currentResults = data.results;
                    displayResults(data.results);
                    updateStats(data.stats);
                } else {
                    showError(data.error || 'Search failed');
                }
            } catch (error) {
                showError('Network error: ' + error.message);
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<span>🚀</span> Start Dorking';
            }
        }
        
        function displayResults(results) {
            document.getElementById('resultsCount').textContent = results.length;
            
            if (results.length === 0) {
                document.getElementById('resultsContainer').innerHTML = `
                    <div class="empty-state">
                        <div class="empty-state-icon">😕</div>
                        <div class="empty-state-title">No results found</div>
                        <p>Try a different dork query</p>
                    </div>
                `;
                return;
            }
            
            let html = '';
            results.forEach((result, i) => {
                const safeTitle = (result.title || 'No title').replace(/[<>]/g, '');
                const safeUrl = (result.url || '').replace(/["'<>]/g, '');
                const safeDesc = (result.description || '').replace(/[<>]/g, '');
                
                html += `
                    <div class="result-item">
                        <a href="${safeUrl}" target="_blank" rel="noopener" class="result-title">
                            ${i + 1}. ${safeTitle}
                        </a>
                        <div class="result-url">${safeUrl}</div>
                        <div class="result-desc">${safeDesc}</div>
                        <div class="result-actions">
                            <button class="result-action" onclick="copyUrl('${safeUrl}')">📋 Copy URL</button>
                            <button class="result-action" onclick="window.open('${safeUrl}', '_blank')">🔗 Open</button>
                        </div>
                    </div>
                `;
            });
            
            document.getElementById('resultsContainer').innerHTML = html;
        }
        
        function showError(message) {
            document.getElementById('resultsContainer').innerHTML = `
                <div class="empty-state">
                    <div class="empty-state-icon">❌</div>
                    <div class="empty-state-title">Error</div>
                    <p>${message}</p>
                </div>
            `;
        }
        
        function updateStats(stats) {
            if (stats) {
                document.getElementById('totalDorks').textContent = stats.total_dorks || 0;
                document.getElementById('totalResults').textContent = stats.total_results || 0;
                document.getElementById('savedQueries').textContent = stats.saved_queries || 0;
                document.getElementById('successRate').textContent = (stats.success_rate || 0) + '%';
            }
        }
        
        function copyUrl(url) {
            navigator.clipboard.writeText(url).then(() => alert('URL copied!'));
        }
        
        async function exportResults(format) {
            if (currentResults.length === 0) {
                alert('No results to export');
                return;
            }
            
            try {
                const response = await fetch(`dorker-api.php?action=export&format=${format}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ results: currentResults })
                });
                
                const blob = await response.blob();
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `dorker-results-${Date.now()}.${format}`;
                a.click();
                URL.revokeObjectURL(url);
            } catch (error) {
                alert('Export failed: ' + error.message);
            }
        }
        
        // Theme & sidebar
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
        
        // Restore theme
        const savedTheme = localStorage.getItem('theme') || 'dark';
        document.body.setAttribute('data-theme', savedTheme);
        document.getElementById('themeIcon').textContent = savedTheme === 'dark' ? '🌙' : '☀️';
        document.getElementById('themeText').textContent = savedTheme === 'dark' ? 'Dark' : 'Light';
        
        if (localStorage.getItem('sidebarCollapsed') === 'true') {
            document.getElementById('sidebar').classList.add('collapsed');
        }
        
        // Load stats
        fetch('dorker-api.php?action=stats')
            .then(r => r.json())
            .then(data => { if (data.success) updateStats(data.stats); })
            .catch(() => {});
    </script>
    
    <script src="../ai-chat-widget.js"></script>
    <script>document.body.dataset.aiContext = 'dorking';</script>
</body>
</html>
