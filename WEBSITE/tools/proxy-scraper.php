<?php
require_once __DIR__ . '/../auth.php';

// Check if user is logged in
$user = getCurrentUser();
if (!$user) {
    header('Location: ../login.php');
    exit;
}

// Initialize database
$db = getDatabase();

// Create scraped_proxies table if not exists
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

// Create proxy_sources table
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
    <link rel="stylesheet" href="shortener-style.css">
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-1940810089559549"
     crossorigin="anonymous"></script>
</head>
<body>
    <div class="dashboard-container">
        <!-- Header -->
        <header class="dashboard-header">
            <div class="header-content">
                <div class="logo">
                    <a href="dashboard.php">🎬 Legend House</a>
                </div>
                
                <nav class="main-nav">
                    <a href="../index.php">Home</a>
                    <a href="watch.php">Watch</a>
                    <div class="dropdown">
                        <button class="dropbtn">🛠️ Tools ▼</button>
                        <div class="dropdown-content tools-menu">
                            <a href="tools.php" class="dropdown-header">🛠️ All Tools</a>
                            <div class="dropdown-item active">
                                <span class="item-icon">⚙️</span>
                                <div class="item-content">
                                    <div class="item-title">Utility</div>
                                    <div class="item-desc">Proxy tools</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </nav>

                <div class="user-menu">
                    <div class="dropdown">
                        <button class="dropbtn user-btn">
                            <?php if (!empty($user['profile_picture'])): ?>
                                <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile" class="user-avatar">
                            <?php else: ?>
                                <span class="user-avatar-placeholder">👤</span>
                            <?php endif; ?>
                            <span><?php echo htmlspecialchars($user['username']); ?></span>
                        </button>
                        <div class="dropdown-content">
                            <a href="dashboard.php">📊 Dashboard</a>
                            <a href="logout.php">🚪 Logout</a>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="dashboard-main shortener-main">
            <div class="page-header">
                <h1>🔍 Advanced Proxy Scraper</h1>
                <p>Scrape, validate, and export working proxies from multiple sources</p>
            </div>

            <!-- Control Panel -->
            <div class="shortener-card">
                <h2>⚙️ Scraping Control</h2>
                <div class="form-row">
                    <div class="form-group">
                        <label>Proxy Sources</label>
                        <select id="sourceSelect" multiple style="height: 150px;">
                            <option value="all" selected>🌐 All Sources (10+)</option>
                            <option value="free-proxy-list">Free Proxy List</option>
                            <option value="proxyscrape">ProxyScrape</option>
                            <option value="proxy-list">Proxy List Download</option>
                            <option value="spys">Spys.one</option>
                            <option value="geonode">GeoNode</option>
                            <option value="pubproxy">PubProxy</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Protocol Filter</label>
                        <select id="protocolFilter">
                            <option value="all">All Protocols</option>
                            <option value="http">HTTP Only</option>
                            <option value="https">HTTPS Only</option>
                            <option value="socks4">SOCKS4 Only</option>
                            <option value="socks5">SOCKS5 Only</option>
                        </select>
                        
                        <label style="margin-top: 1rem;">Country Filter</label>
                        <input type="text" id="countryFilter" placeholder="e.g., US, UK, FR (optional)">
                        
                        <label style="margin-top: 1rem;">Max Timeout (seconds)</label>
                        <input type="number" id="maxTimeout" value="5" min="1" max="30">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Custom Proxy Source URL (optional)</label>
                    <input type="url" id="customSource" placeholder="https://example.com/proxy-list.txt">
                    <small>URL must return plain text list of proxies in format: IP:PORT</small>
                </div>
                
                <button onclick="startScraping()" class="btn-primary" id="scrapeBtn">
                    🚀 Start Scraping & Validation
                </button>
            </div>

            <!-- Progress Section -->
            <div class="shortener-card" id="progressSection" style="display: none;">
                <h2>📊 Scraping Progress</h2>
                <div class="progress-stats">
                    <div class="stat-card">
                        <div class="stat-value" id="totalScraped">0</div>
                        <div class="stat-label">Total Scraped</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value" id="totalValidated">0</div>
                        <div class="stat-label">Validated</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value" id="workingProxies">0</div>
                        <div class="stat-label">Working</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value" id="successRate">0%</div>
                        <div class="stat-label">Success Rate</div>
                    </div>
                </div>
                
                <div class="progress-bar-container">
                    <div class="progress-bar" id="progressBar"></div>
                </div>
                <p id="progressText" class="loading">Initializing scraper...</p>
            </div>

            <!-- Results Table -->
            <div class="shortener-card">
                <h2>✅ Working Proxies</h2>
                <div class="form-row" style="margin-bottom: 1rem;">
                    <input type="text" id="searchProxy" placeholder="🔍 Search by IP, country..." class="form-control">
                    <select id="sortBy" class="form-control">
                        <option value="speed">Sort by Speed</option>
                        <option value="country">Sort by Country</option>
                        <option value="protocol">Sort by Protocol</option>
                        <option value="tested">Sort by Test Time</option>
                    </select>
                </div>
                
                <div class="action-buttons" style="margin-bottom: 1rem; gap: 1rem;">
                    <button onclick="exportProxies('txt')" class="btn-action">📄 Export TXT</button>
                    <button onclick="exportProxies('csv')" class="btn-action">📊 Export CSV</button>
                    <button onclick="exportProxies('json')" class="btn-action">📋 Export JSON</button>
                    <button onclick="clearResults()" class="btn-action">🗑️ Clear All</button>
                </div>
                
                <div class="table-container">
                    <table class="links-table">
                        <thead>
                            <tr>
                                <th>IP Address</th>
                                <th>Port</th>
                                <th>Protocol</th>
                                <th>Country</th>
                                <th>Anonymity</th>
                                <th>Speed (ms)</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="proxiesTableBody">
                            <tr>
                                <td colspan="8" class="loading">No proxies yet. Start scraping to see results!</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Footer -->
    <footer style="text-align: center; padding: 2rem 1rem; background: var(--white); border-top: 2px solid var(--gray-200); margin-top: 3rem;">
        <div style="max-width: 1200px; margin: 0 auto;">
            <p style="margin: 0; font-size: 0.9rem; color: var(--gray-600);">
                <strong style="color: var(--black);">Powered by Legend House</strong> • Advanced Tools Platform
            </p>
        </div>
    </footer>

    <script src="proxy-scraper-script.js"></script>
    
    <!-- AI Chat Widget Integration -->
    <script src="../ai-chat-widget.js"></script>
    <script>
        // Set context to 'general' for proxy scraper page
        document.body.dataset.aiContext = 'general';
    </script>
</body>
</html>
