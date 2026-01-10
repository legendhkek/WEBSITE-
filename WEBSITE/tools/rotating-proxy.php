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
    <title>Rotating Proxy - Legend House</title>
    <link rel="stylesheet" href="../dashboard-style.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🔄</text></svg>">
    
    <style>
        /* Rotating Proxy Specific Styles */
        .proxy-hero {
            background: linear-gradient(135deg, var(--bg-secondary) 0%, var(--bg-tertiary) 100%);
            border: 1px solid var(--border-default);
            border-radius: var(--radius-xl);
            padding: 32px;
            margin-bottom: 24px;
            position: relative;
            overflow: hidden;
        }
        
        .proxy-hero::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, var(--accent-muted) 0%, transparent 70%);
            opacity: 0.5;
        }
        
        .proxy-hero h1 {
            font-size: 32px;
            font-weight: 800;
            color: var(--text-primary);
            margin-bottom: 8px;
            position: relative;
        }
        
        .proxy-hero p {
            color: var(--text-secondary);
            font-size: 16px;
            position: relative;
        }
        
        .stats-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }
        
        .stat-box {
            background: var(--bg-secondary);
            border: 1px solid var(--border-default);
            border-radius: var(--radius-lg);
            padding: 20px;
            text-align: center;
            transition: all 0.2s;
        }
        
        .stat-box:hover {
            border-color: var(--accent-primary);
            transform: translateY(-2px);
        }
        
        .stat-box-icon {
            font-size: 24px;
            margin-bottom: 8px;
        }
        
        .stat-box-value {
            font-size: 28px;
            font-weight: 800;
            color: var(--text-primary);
        }
        
        .stat-box-label {
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 4px;
        }
        
        .proxy-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }
        
        .panel {
            background: var(--bg-secondary);
            border: 1px solid var(--border-default);
            border-radius: var(--radius-lg);
            overflow: hidden;
        }
        
        .panel-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border-default);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .panel-title {
            font-size: 15px;
            font-weight: 600;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .panel-body {
            padding: 20px;
        }
        
        .form-group {
            margin-bottom: 16px;
        }
        
        .form-group:last-child {
            margin-bottom: 0;
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
            padding: 12px 16px;
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
        
        .strategy-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin-bottom: 16px;
        }
        
        .strategy-card {
            padding: 16px;
            background: var(--bg-tertiary);
            border: 2px solid var(--border-default);
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: all 0.2s;
            text-align: center;
        }
        
        .strategy-card:hover {
            border-color: var(--accent-primary);
        }
        
        .strategy-card.active {
            border-color: var(--accent-primary);
            background: var(--accent-muted);
        }
        
        .strategy-icon {
            font-size: 32px;
            margin-bottom: 8px;
        }
        
        .strategy-name {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 4px;
        }
        
        .strategy-desc {
            font-size: 11px;
            color: var(--text-muted);
        }
        
        .btn {
            width: 100%;
            padding: 14px 20px;
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
        
        .btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }
        
        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }
        
        .btn-secondary {
            background: var(--bg-tertiary);
            color: var(--text-primary);
            border: 1px solid var(--border-default);
        }
        
        .btn-secondary:hover {
            background: var(--accent-muted);
            border-color: var(--accent-primary);
        }
        
        /* API Endpoint */
        .api-endpoint {
            background: var(--bg-tertiary);
            border: 1px solid var(--border-default);
            border-radius: var(--radius-md);
            padding: 16px;
            margin-bottom: 16px;
        }
        
        .api-endpoint-label {
            font-size: 11px;
            color: var(--text-muted);
            text-transform: uppercase;
            margin-bottom: 8px;
        }
        
        .api-endpoint-url {
            font-family: var(--font-mono);
            font-size: 13px;
            color: var(--info);
            word-break: break-all;
            padding: 12px;
            background: var(--bg-primary);
            border-radius: var(--radius-sm);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
        }
        
        .copy-btn {
            padding: 6px 12px;
            background: var(--bg-secondary);
            border: 1px solid var(--border-default);
            border-radius: var(--radius-sm);
            font-size: 12px;
            color: var(--text-secondary);
            cursor: pointer;
            transition: all 0.2s;
            flex-shrink: 0;
        }
        
        .copy-btn:hover {
            background: var(--accent-muted);
            color: var(--text-primary);
        }
        
        /* Current Proxy Display */
        .current-proxy {
            text-align: center;
            padding: 24px;
        }
        
        .proxy-value {
            font-family: var(--font-mono);
            font-size: 20px;
            font-weight: 700;
            color: var(--success);
            padding: 16px;
            background: var(--bg-tertiary);
            border-radius: var(--radius-md);
            margin-bottom: 16px;
        }
        
        .proxy-meta {
            display: flex;
            justify-content: center;
            gap: 24px;
            margin-bottom: 16px;
        }
        
        .proxy-meta-item {
            font-size: 13px;
            color: var(--text-secondary);
        }
        
        .proxy-meta-item span {
            color: var(--text-primary);
            font-weight: 600;
        }
        
        /* Pool List */
        .pool-list {
            max-height: 300px;
            overflow-y: auto;
        }
        
        .pool-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px;
            background: var(--bg-tertiary);
            border-radius: var(--radius-sm);
            margin-bottom: 8px;
        }
        
        .pool-item:last-child {
            margin-bottom: 0;
        }
        
        .pool-proxy {
            font-family: var(--font-mono);
            font-size: 13px;
            color: var(--text-primary);
        }
        
        .pool-status {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }
        
        .pool-status.online {
            background: rgba(63, 185, 80, 0.1);
            color: var(--success);
        }
        
        .pool-status.offline {
            background: rgba(248, 81, 73, 0.1);
            color: var(--danger);
        }
        
        /* Responsive */
        @media (max-width: 1200px) {
            .proxy-layout {
                grid-template-columns: 1fr;
            }
            
            .stats-row {
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
                        <li><a href="dorker.php" class="nav-item"><span class="nav-icon">🔍</span><span class="sidebar-text">Google Dorker</span></a></li>
                        <li><a href="torrent.php" class="nav-item"><span class="nav-icon">🧲</span><span class="sidebar-text">Torrent Center</span></a></li>
                        <li><a href="proxy-scraper.php" class="nav-item"><span class="nav-icon">🌐</span><span class="sidebar-text">Proxy Scraper</span></a></li>
                        <li><a href="shortener.php" class="nav-item"><span class="nav-icon">🔗</span><span class="sidebar-text">Link Shortener</span></a></li>
                        <li><a href="rotating-proxy.php" class="nav-item active"><span class="nav-icon">🔄</span><span class="sidebar-text">Rotating Proxy</span></a></li>
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
                        <span class="breadcrumb-item active">Rotating Proxy</span>
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
                <!-- Hero -->
                <div class="proxy-hero">
                    <h1>🔄 Rotating Proxy</h1>
                    <p>Create your own rotating proxy pool with automatic rotation, multiple strategies, and API access</p>
                </div>
                
                <!-- Stats -->
                <div class="stats-row">
                    <div class="stat-box">
                        <div class="stat-box-icon">🌐</div>
                        <div class="stat-box-value" id="totalProxies">0</div>
                        <div class="stat-box-label">Total Proxies</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-box-icon">✅</div>
                        <div class="stat-box-value" id="onlineProxies">0</div>
                        <div class="stat-box-label">Online</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-box-icon">🔄</div>
                        <div class="stat-box-value" id="rotations">0</div>
                        <div class="stat-box-label">Rotations</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-box-icon">⚡</div>
                        <div class="stat-box-value" id="avgSpeed">0ms</div>
                        <div class="stat-box-label">Avg Speed</div>
                    </div>
                </div>
                
                <!-- Main Layout -->
                <div class="proxy-layout">
                    <!-- Configuration -->
                    <div class="panel">
                        <div class="panel-header">
                            <div class="panel-title">⚙️ Configuration</div>
                        </div>
                        <div class="panel-body">
                            <div class="form-group">
                                <label class="form-label">Rotation Strategy</label>
                                <div class="strategy-grid">
                                    <div class="strategy-card active" data-strategy="round-robin">
                                        <div class="strategy-icon">🔄</div>
                                        <div class="strategy-name">Round Robin</div>
                                        <div class="strategy-desc">Cycle through each proxy in order</div>
                                    </div>
                                    <div class="strategy-card" data-strategy="random">
                                        <div class="strategy-icon">🎲</div>
                                        <div class="strategy-name">Random</div>
                                        <div class="strategy-desc">Select random proxy each time</div>
                                    </div>
                                    <div class="strategy-card" data-strategy="least-used">
                                        <div class="strategy-icon">📊</div>
                                        <div class="strategy-name">Least Used</div>
                                        <div class="strategy-desc">Prefer less frequently used</div>
                                    </div>
                                    <div class="strategy-card" data-strategy="fastest">
                                        <div class="strategy-icon">⚡</div>
                                        <div class="strategy-name">Fastest</div>
                                        <div class="strategy-desc">Prioritize low latency proxies</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Rotation Interval (requests)</label>
                                <input type="number" class="form-input" id="rotationInterval" value="10" min="1" max="100">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Fallback Behavior</label>
                                <select class="form-select" id="fallbackBehavior">
                                    <option value="next">Use next available proxy</option>
                                    <option value="retry">Retry current proxy</option>
                                    <option value="direct">Connect directly</option>
                                </select>
                            </div>
                            
                            <div class="api-endpoint">
                                <div class="api-endpoint-label">API Endpoint</div>
                                <div class="api-endpoint-url">
                                    <span id="apiEndpoint">https://yoursite.com/api/proxy/rotate</span>
                                    <button class="copy-btn" onclick="copyEndpoint()">Copy</button>
                                </div>
                            </div>
                            
                            <button class="btn" onclick="saveConfig()">
                                💾 Save Configuration
                            </button>
                        </div>
                    </div>
                    
                    <!-- Current Proxy -->
                    <div class="panel">
                        <div class="panel-header">
                            <div class="panel-title">🎯 Current Proxy</div>
                        </div>
                        <div class="panel-body">
                            <div class="current-proxy">
                                <div class="proxy-value" id="currentProxy">192.168.1.100:8080</div>
                                <div class="proxy-meta">
                                    <div class="proxy-meta-item">Type: <span id="proxyType">HTTP</span></div>
                                    <div class="proxy-meta-item">Country: <span id="proxyCountry">US</span></div>
                                    <div class="proxy-meta-item">Latency: <span id="proxyLatency">45ms</span></div>
                                </div>
                                <div style="display: flex; gap: 12px;">
                                    <button class="btn" onclick="rotateProxy()">
                                        🔄 Rotate Now
                                    </button>
                                    <button class="btn btn-secondary" onclick="copyProxy()">
                                        📋 Copy
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Proxy Pool -->
                <div class="panel" style="margin-top: 24px;">
                    <div class="panel-header">
                        <div class="panel-title">🌐 Proxy Pool</div>
                        <button class="copy-btn" onclick="importProxies()">+ Import Proxies</button>
                    </div>
                    <div class="panel-body">
                        <div class="pool-list" id="poolList">
                            <div class="pool-item">
                                <span class="pool-proxy">192.168.1.100:8080</span>
                                <span class="pool-status online">Online</span>
                            </div>
                            <div class="pool-item">
                                <span class="pool-proxy">10.0.0.50:3128</span>
                                <span class="pool-status online">Online</span>
                            </div>
                            <div class="pool-item">
                                <span class="pool-proxy">172.16.0.25:8888</span>
                                <span class="pool-status offline">Offline</span>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script>
        let currentStrategy = 'round-robin';
        let proxyPool = [
            { ip: '192.168.1.100', port: 8080, type: 'HTTP', country: 'US', latency: 45, status: 'online' },
            { ip: '10.0.0.50', port: 3128, type: 'HTTP', country: 'DE', latency: 78, status: 'online' },
            { ip: '172.16.0.25', port: 8888, type: 'SOCKS5', country: 'UK', latency: 120, status: 'offline' }
        ];
        let currentIndex = 0;
        let rotationCount = 0;
        
        // Strategy selection
        document.querySelectorAll('.strategy-card').forEach(card => {
            card.addEventListener('click', function() {
                document.querySelectorAll('.strategy-card').forEach(c => c.classList.remove('active'));
                this.classList.add('active');
                currentStrategy = this.dataset.strategy;
            });
        });
        
        function updateStats() {
            const online = proxyPool.filter(p => p.status === 'online');
            document.getElementById('totalProxies').textContent = proxyPool.length;
            document.getElementById('onlineProxies').textContent = online.length;
            document.getElementById('rotations').textContent = rotationCount;
            
            const avgLatency = online.length > 0 
                ? Math.round(online.reduce((sum, p) => sum + p.latency, 0) / online.length)
                : 0;
            document.getElementById('avgSpeed').textContent = avgLatency + 'ms';
        }
        
        function updateCurrentProxy() {
            const online = proxyPool.filter(p => p.status === 'online');
            if (online.length === 0) {
                document.getElementById('currentProxy').textContent = 'No proxies available';
                return;
            }
            
            let proxy;
            switch (currentStrategy) {
                case 'random':
                    proxy = online[Math.floor(Math.random() * online.length)];
                    break;
                case 'least-used':
                case 'fastest':
                    proxy = online.sort((a, b) => a.latency - b.latency)[0];
                    break;
                default: // round-robin
                    currentIndex = currentIndex % online.length;
                    proxy = online[currentIndex];
            }
            
            document.getElementById('currentProxy').textContent = `${proxy.ip}:${proxy.port}`;
            document.getElementById('proxyType').textContent = proxy.type;
            document.getElementById('proxyCountry').textContent = proxy.country;
            document.getElementById('proxyLatency').textContent = proxy.latency + 'ms';
        }
        
        function renderPoolList() {
            const html = proxyPool.map(p => `
                <div class="pool-item">
                    <span class="pool-proxy">${p.ip}:${p.port}</span>
                    <span class="pool-status ${p.status}">${p.status.charAt(0).toUpperCase() + p.status.slice(1)}</span>
                </div>
            `).join('');
            document.getElementById('poolList').innerHTML = html;
        }
        
        function rotateProxy() {
            const online = proxyPool.filter(p => p.status === 'online');
            if (online.length === 0) return;
            
            currentIndex = (currentIndex + 1) % online.length;
            rotationCount++;
            updateCurrentProxy();
            updateStats();
            showToast('Proxy rotated!');
        }
        
        function copyProxy() {
            const proxy = document.getElementById('currentProxy').textContent;
            navigator.clipboard.writeText(proxy).then(() => {
                showToast('Proxy copied!');
            });
        }
        
        function copyEndpoint() {
            const endpoint = document.getElementById('apiEndpoint').textContent;
            navigator.clipboard.writeText(endpoint).then(() => {
                showToast('Endpoint copied!');
            });
        }
        
        function saveConfig() {
            const config = {
                strategy: currentStrategy,
                interval: document.getElementById('rotationInterval').value,
                fallback: document.getElementById('fallbackBehavior').value
            };
            localStorage.setItem('proxyConfig', JSON.stringify(config));
            showToast('Configuration saved!');
        }
        
        function importProxies() {
            const input = prompt('Enter proxies (one per line, format: IP:PORT)');
            if (!input) return;
            
            const lines = input.trim().split('\n');
            let added = 0;
            
            lines.forEach(line => {
                const match = line.trim().match(/^(\d+\.\d+\.\d+\.\d+):(\d+)$/);
                if (match) {
                    proxyPool.push({
                        ip: match[1],
                        port: parseInt(match[2]),
                        type: 'HTTP',
                        country: '??',
                        latency: 0,
                        status: 'online'
                    });
                    added++;
                }
            });
            
            if (added > 0) {
                renderPoolList();
                updateStats();
                updateCurrentProxy();
                showToast(`Added ${added} proxies!`);
            } else {
                showToast('No valid proxies found');
            }
        }
        
        function showToast(message) {
            const toast = document.createElement('div');
            toast.style.cssText = `
                position: fixed;
                bottom: 20px;
                right: 20px;
                background: var(--bg-secondary);
                border: 1px solid var(--border-default);
                color: var(--text-primary);
                padding: 12px 20px;
                border-radius: 8px;
                font-size: 14px;
                z-index: 10000;
            `;
            toast.textContent = message;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        }
        
        // Theme & sidebar
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('collapsed');
            localStorage.setItem('sidebarCollapsed', document.getElementById('sidebar').classList.contains('collapsed'));
        }
        
        function toggleTheme() {
            const theme = document.body.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            document.body.setAttribute('data-theme', theme);
            localStorage.setItem('theme', theme);
            document.getElementById('themeIcon').textContent = theme === 'dark' ? '🌙' : '☀️';
            document.getElementById('themeText').textContent = theme === 'dark' ? 'Dark' : 'Light';
        }
        
        // Restore settings
        const savedTheme = localStorage.getItem('theme') || 'dark';
        document.body.setAttribute('data-theme', savedTheme);
        document.getElementById('themeIcon').textContent = savedTheme === 'dark' ? '🌙' : '☀️';
        document.getElementById('themeText').textContent = savedTheme === 'dark' ? 'Dark' : 'Light';
        
        if (localStorage.getItem('sidebarCollapsed') === 'true') {
            document.getElementById('sidebar').classList.add('collapsed');
        }
        
        // Initialize
        updateStats();
        updateCurrentProxy();
        renderPoolList();
    </script>
    
    <script src="../ai-chat-widget.js"></script>
</body>
</html>
