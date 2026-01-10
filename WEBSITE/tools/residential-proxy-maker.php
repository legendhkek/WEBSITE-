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
    <title>Residential Proxy Maker - Legend House</title>
    <link rel="stylesheet" href="../dashboard-style.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🏠</text></svg>">
    
    <style>
        /* Residential Proxy Maker Styles */
        .maker-hero {
            background: linear-gradient(135deg, var(--bg-secondary) 0%, var(--bg-tertiary) 100%);
            border: 1px solid var(--border-default);
            border-radius: var(--radius-xl);
            padding: 32px;
            margin-bottom: 24px;
            position: relative;
            overflow: hidden;
        }
        
        .maker-hero::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(102, 126, 234, 0.2) 0%, transparent 70%);
        }
        
        .maker-hero h1 {
            font-size: 32px;
            font-weight: 800;
            color: var(--text-primary);
            margin-bottom: 8px;
            position: relative;
        }
        
        .maker-hero p {
            color: var(--text-secondary);
            font-size: 16px;
            position: relative;
        }
        
        .pro-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            color: white;
            margin-left: 12px;
            text-transform: uppercase;
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
        
        .maker-layout {
            display: grid;
            grid-template-columns: 400px 1fr;
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
        
        .form-input, .form-select, .form-textarea {
            width: 100%;
            padding: 12px 16px;
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
        
        .form-input:focus, .form-select:focus, .form-textarea:focus {
            outline: none;
            border-color: var(--accent-primary);
            background: var(--bg-primary);
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
        
        .btn-gradient {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }
        
        /* Provider Selection */
        .provider-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin-bottom: 16px;
        }
        
        .provider-card {
            padding: 16px;
            background: var(--bg-tertiary);
            border: 2px solid var(--border-default);
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: all 0.2s;
            text-align: center;
        }
        
        .provider-card:hover {
            border-color: var(--accent-primary);
        }
        
        .provider-card.active {
            border-color: var(--accent-primary);
            background: var(--accent-muted);
        }
        
        .provider-icon {
            font-size: 28px;
            margin-bottom: 8px;
        }
        
        .provider-name {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 4px;
        }
        
        .provider-type {
            font-size: 11px;
            color: var(--text-muted);
        }
        
        /* Output Display */
        .output-container {
            background: var(--bg-tertiary);
            border: 1px solid var(--border-default);
            border-radius: var(--radius-md);
            padding: 16px;
            margin-top: 16px;
        }
        
        .output-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }
        
        .output-title {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .output-actions {
            display: flex;
            gap: 8px;
        }
        
        .output-btn {
            padding: 6px 12px;
            background: var(--bg-secondary);
            border: 1px solid var(--border-default);
            border-radius: var(--radius-sm);
            font-size: 12px;
            color: var(--text-secondary);
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .output-btn:hover {
            background: var(--accent-muted);
            color: var(--text-primary);
        }
        
        .output-content {
            font-family: var(--font-mono);
            font-size: 12px;
            color: var(--success);
            background: var(--bg-primary);
            padding: 12px;
            border-radius: var(--radius-sm);
            max-height: 200px;
            overflow-y: auto;
            white-space: pre-wrap;
            word-break: break-all;
        }
        
        /* Results List */
        .results-list {
            max-height: calc(100vh - 400px);
            overflow-y: auto;
        }
        
        .result-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 16px;
            background: var(--bg-tertiary);
            border-radius: var(--radius-md);
            margin-bottom: 10px;
        }
        
        .result-item:last-child {
            margin-bottom: 0;
        }
        
        .result-proxy {
            font-family: var(--font-mono);
            font-size: 13px;
            color: var(--text-primary);
        }
        
        .result-info {
            display: flex;
            gap: 16px;
            font-size: 12px;
            color: var(--text-muted);
        }
        
        .result-info span {
            display: flex;
            align-items: center;
            gap: 4px;
        }
        
        .result-status {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }
        
        .result-status.success {
            background: rgba(63, 185, 80, 0.1);
            color: var(--success);
        }
        
        .result-status.pending {
            background: rgba(210, 153, 34, 0.1);
            color: var(--warning);
        }
        
        /* Country Selector */
        .country-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 8px;
        }
        
        .country-tag {
            padding: 6px 12px;
            background: var(--bg-tertiary);
            border: 1px solid var(--border-default);
            border-radius: var(--radius-sm);
            font-size: 12px;
            color: var(--text-secondary);
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .country-tag:hover, .country-tag.active {
            background: var(--accent-muted);
            border-color: var(--accent-primary);
            color: var(--text-primary);
        }
        
        /* Responsive */
        @media (max-width: 1200px) {
            .maker-layout {
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
                        <li><a href="rotating-proxy.php" class="nav-item"><span class="nav-icon">🔄</span><span class="sidebar-text">Rotating Proxy</span></a></li>
                        <li><a href="residential-proxy-maker.php" class="nav-item active"><span class="nav-icon">🏘️</span><span class="sidebar-text">Residential Proxy</span></a></li>
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
                        <span class="breadcrumb-item active">Residential Proxy Maker</span>
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
                <div class="maker-hero">
                    <h1>
                        🏘️ Residential Proxy Maker
                        <span class="pro-badge">⚡ PRO</span>
                    </h1>
                    <p>Generate residential-grade proxies with advanced configuration, geo-targeting, and automatic rotation</p>
                </div>
                
                <!-- Stats -->
                <div class="stats-row">
                    <div class="stat-box">
                        <div class="stat-box-icon">🌐</div>
                        <div class="stat-box-value" id="totalGenerated">0</div>
                        <div class="stat-box-label">Generated</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-box-icon">✅</div>
                        <div class="stat-box-value" id="activeProxies">0</div>
                        <div class="stat-box-label">Active</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-box-icon">🌍</div>
                        <div class="stat-box-value" id="countries">195</div>
                        <div class="stat-box-label">Countries</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-box-icon">⚡</div>
                        <div class="stat-box-value" id="uptime">99.9%</div>
                        <div class="stat-box-label">Uptime</div>
                    </div>
                </div>
                
                <!-- Main Layout -->
                <div class="maker-layout">
                    <!-- Configuration -->
                    <div class="panel">
                        <div class="panel-header">
                            <div class="panel-title">⚙️ Configuration</div>
                        </div>
                        <div class="panel-body">
                            <div class="form-group">
                                <label class="form-label">Proxy Type</label>
                                <div class="provider-grid">
                                    <div class="provider-card active" data-type="residential">
                                        <div class="provider-icon">🏠</div>
                                        <div class="provider-name">Residential</div>
                                        <div class="provider-type">Real ISP IPs</div>
                                    </div>
                                    <div class="provider-card" data-type="datacenter">
                                        <div class="provider-icon">🏢</div>
                                        <div class="provider-name">Datacenter</div>
                                        <div class="provider-type">Fast speeds</div>
                                    </div>
                                    <div class="provider-card" data-type="mobile">
                                        <div class="provider-icon">📱</div>
                                        <div class="provider-name">Mobile</div>
                                        <div class="provider-type">4G/5G IPs</div>
                                    </div>
                                    <div class="provider-card" data-type="static">
                                        <div class="provider-icon">📌</div>
                                        <div class="provider-name">Static</div>
                                        <div class="provider-type">Fixed IP</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Target Country</label>
                                <select class="form-select" id="countrySelect">
                                    <option value="random">🌍 Random</option>
                                    <option value="us">🇺🇸 United States</option>
                                    <option value="uk">🇬🇧 United Kingdom</option>
                                    <option value="de">🇩🇪 Germany</option>
                                    <option value="fr">🇫🇷 France</option>
                                    <option value="ca">🇨🇦 Canada</option>
                                    <option value="au">🇦🇺 Australia</option>
                                    <option value="jp">🇯🇵 Japan</option>
                                    <option value="in">🇮🇳 India</option>
                                    <option value="br">🇧🇷 Brazil</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Popular Countries</label>
                                <div class="country-grid">
                                    <span class="country-tag active">🇺🇸 US</span>
                                    <span class="country-tag">🇬🇧 UK</span>
                                    <span class="country-tag">🇩🇪 DE</span>
                                    <span class="country-tag">🇫🇷 FR</span>
                                    <span class="country-tag">🇯🇵 JP</span>
                                    <span class="country-tag">🇮🇳 IN</span>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Number of Proxies</label>
                                <input type="number" class="form-input" id="proxyCount" value="10" min="1" max="100">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Session Type</label>
                                <select class="form-select" id="sessionType">
                                    <option value="rotating">Rotating (New IP per request)</option>
                                    <option value="sticky">Sticky (Same IP for duration)</option>
                                </select>
                            </div>
                            
                            <button class="btn btn-gradient" onclick="generateProxies()" id="generateBtn">
                                🚀 Generate Proxies
                            </button>
                            
                            <div class="output-container" id="outputContainer" style="display: none;">
                                <div class="output-header">
                                    <span class="output-title">Generated Proxies</span>
                                    <div class="output-actions">
                                        <button class="output-btn" onclick="copyOutput()">📋 Copy</button>
                                        <button class="output-btn" onclick="downloadOutput()">⬇️ Download</button>
                                    </div>
                                </div>
                                <div class="output-content" id="outputContent"></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Generated Proxies -->
                    <div class="panel">
                        <div class="panel-header">
                            <div class="panel-title">📋 Generated Proxies</div>
                            <span style="font-size: 12px; color: var(--text-muted);" id="proxyListCount">0 proxies</span>
                        </div>
                        <div class="panel-body">
                            <div class="results-list" id="resultsList">
                                <div style="text-align: center; padding: 40px; color: var(--text-muted);">
                                    <div style="font-size: 48px; margin-bottom: 16px;">🏘️</div>
                                    <div>No proxies generated yet</div>
                                    <div style="font-size: 13px; margin-top: 8px;">Configure options and click Generate</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script>
        let currentType = 'residential';
        let generatedProxies = [];
        
        // Type selection
        document.querySelectorAll('.provider-card').forEach(card => {
            card.addEventListener('click', function() {
                document.querySelectorAll('.provider-card').forEach(c => c.classList.remove('active'));
                this.classList.add('active');
                currentType = this.dataset.type;
            });
        });
        
        // Country tags
        document.querySelectorAll('.country-tag').forEach(tag => {
            tag.addEventListener('click', function() {
                document.querySelectorAll('.country-tag').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                
                // Update select
                const code = this.textContent.split(' ')[1].toLowerCase();
                const select = document.getElementById('countrySelect');
                for (let option of select.options) {
                    if (option.value === code) {
                        select.value = code;
                        break;
                    }
                }
            });
        });
        
        function generateProxies() {
            const count = parseInt(document.getElementById('proxyCount').value);
            const country = document.getElementById('countrySelect').value;
            const session = document.getElementById('sessionType').value;
            
            const btn = document.getElementById('generateBtn');
            btn.disabled = true;
            btn.innerHTML = '⏳ Generating...';
            
            // Simulate proxy generation
            setTimeout(() => {
                generatedProxies = [];
                
                const countryMap = {
                    'us': 'United States',
                    'uk': 'United Kingdom',
                    'de': 'Germany',
                    'fr': 'France',
                    'ca': 'Canada',
                    'au': 'Australia',
                    'jp': 'Japan',
                    'in': 'India',
                    'br': 'Brazil',
                    'random': 'Random'
                };
                
                const ports = [8080, 3128, 80, 8888, 1080];
                
                for (let i = 0; i < count; i++) {
                    const ip = generateRandomIP();
                    const port = ports[Math.floor(Math.random() * ports.length)];
                    const latency = Math.floor(Math.random() * 200) + 50;
                    
                    generatedProxies.push({
                        ip: ip,
                        port: port,
                        country: country === 'random' ? Object.keys(countryMap)[Math.floor(Math.random() * 9)] : country,
                        type: currentType,
                        session: session,
                        latency: latency
                    });
                }
                
                displayProxies();
                updateOutput();
                updateStats();
                
                btn.disabled = false;
                btn.innerHTML = '🚀 Generate Proxies';
                
                showToast(`Generated ${count} proxies!`);
            }, 1500);
        }
        
        function generateRandomIP() {
            // Generate residential-looking IPs
            const ranges = [
                [1, 126],    // Class A
                [128, 191],  // Class B
                [192, 223]   // Class C
            ];
            const range = ranges[Math.floor(Math.random() * ranges.length)];
            
            return `${Math.floor(Math.random() * (range[1] - range[0])) + range[0]}.${Math.floor(Math.random() * 256)}.${Math.floor(Math.random() * 256)}.${Math.floor(Math.random() * 256)}`;
        }
        
        function displayProxies() {
            const container = document.getElementById('resultsList');
            
            if (generatedProxies.length === 0) {
                container.innerHTML = `
                    <div style="text-align: center; padding: 40px; color: var(--text-muted);">
                        <div style="font-size: 48px; margin-bottom: 16px;">🏘️</div>
                        <div>No proxies generated yet</div>
                    </div>
                `;
                return;
            }
            
            const countryFlags = {
                'us': '🇺🇸', 'uk': '🇬🇧', 'de': '🇩🇪', 'fr': '🇫🇷',
                'ca': '🇨🇦', 'au': '🇦🇺', 'jp': '🇯🇵', 'in': '🇮🇳', 'br': '🇧🇷'
            };
            
            container.innerHTML = generatedProxies.map((proxy, i) => `
                <div class="result-item">
                    <div>
                        <div class="result-proxy">${proxy.ip}:${proxy.port}</div>
                        <div class="result-info">
                            <span>${countryFlags[proxy.country] || '🌍'} ${proxy.country.toUpperCase()}</span>
                            <span>⚡ ${proxy.latency}ms</span>
                            <span>📡 ${proxy.type}</span>
                        </div>
                    </div>
                    <span class="result-status success">Active</span>
                </div>
            `).join('');
            
            document.getElementById('proxyListCount').textContent = `${generatedProxies.length} proxies`;
        }
        
        function updateOutput() {
            const container = document.getElementById('outputContainer');
            const content = document.getElementById('outputContent');
            
            if (generatedProxies.length === 0) {
                container.style.display = 'none';
                return;
            }
            
            container.style.display = 'block';
            content.textContent = generatedProxies.map(p => `${p.ip}:${p.port}`).join('\n');
        }
        
        function updateStats() {
            document.getElementById('totalGenerated').textContent = generatedProxies.length;
            document.getElementById('activeProxies').textContent = generatedProxies.length;
        }
        
        function copyOutput() {
            const text = generatedProxies.map(p => `${p.ip}:${p.port}`).join('\n');
            navigator.clipboard.writeText(text).then(() => {
                showToast('Proxies copied!');
            });
        }
        
        function downloadOutput() {
            const text = generatedProxies.map(p => `${p.ip}:${p.port}`).join('\n');
            const blob = new Blob([text], { type: 'text/plain' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `residential-proxies-${Date.now()}.txt`;
            a.click();
            URL.revokeObjectURL(url);
            showToast('Download started!');
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
    </script>
    
    <script src="../ai-chat-widget.js"></script>
</body>
</html>
