<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth.php';

// Check if user is logged in
$user = getCurrentUser();
if (!$user) {
    header('Location: ../login.php');
    exit;
}

// Initialize database
$db = getDatabase();

// Create short_urls table if not exists
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

// Create url_clicks table if not exists
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
    <link rel="stylesheet" href="dashboard-style.css">
    <link rel="stylesheet" href="shortener-style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="shortener-enhanced-features.js"></script>
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-1940810089559549"
     crossorigin="anonymous"></script>
</head>
<body>
    <div class="dashboard-container">
<!-- Enhanced Link Shortener UI Elements -->
<style>
.pro-banner {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
    padding: 2rem;
    border-radius: 1rem;
    margin-bottom: 2rem;
    text-align: center;
    box-shadow: 0 10px 30px rgba(245, 87, 108, 0.3);
}

.pro-banner h2 {
    margin: 0;
    font-size: 2.5rem;
    font-weight: 900;
}

.pro-features {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin: 2rem 0;
}

.pro-feature {
    background: white;
    padding: 1.5rem;
    border-radius: 0.75rem;
    text-align: center;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    transition: transform 0.3s;
}

.pro-feature:hover {
    transform: translateY(-5px);
}

.pro-feature-icon {
    font-size: 2.5rem;
    margin-bottom: 0.5rem;
}

.pro-feature-title {
    font-weight: 700;
    margin-bottom: 0.25rem;
}

.pro-feature-desc {
    font-size: 0.875rem;
    color: #666;
}

.analytics-container {
    background: white;
    border-radius: 1rem;
    padding: 2rem;
    margin: 2rem 0;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.chart-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
    margin-top: 2rem;
}

.bulk-uploader {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 2rem;
    border-radius: 1rem;
    color: white;
    margin: 2rem 0;
}

.utm-builder {
    background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    padding: 2rem;
    border-radius: 1rem;
    margin: 2rem 0;
}

.ab-testing {
    background: linear-gradient(135deg, #30cfd0 0%, #330867 100%);
    padding: 2rem;
    border-radius: 1rem;
    color: white;
    margin: 2rem 0;
}
</style>

<!-- Pro Banner -->
<div class="pro-banner">
    <h2>🚀 LINK SHORTENER PRO</h2>
    <p style="font-size: 1.25rem; margin: 0.5rem 0 0;">Advanced URL Management with Analytics & Automation</p>
    <div class="feature-badges" style="display: flex; gap: 0.5rem; justify-content: center; margin-top: 1rem; flex-wrap: wrap;">
        <span class="badge" style="background: rgba(255,255,255,0.3); padding: 0.5rem 1rem; border-radius: 2rem;">📊 Advanced Analytics</span>
        <span class="badge" style="background: rgba(255,255,255,0.3); padding: 0.5rem 1rem; border-radius: 2rem;">🌍 Geographic Tracking</span>
        <span class="badge" style="background: rgba(255,255,255,0.3); padding: 0.5rem 1rem; border-radius: 2rem;">🔄 A/B Testing</span>
        <span class="badge" style="background: rgba(255,255,255,0.3); padding: 0.5rem 1rem; border-radius: 2rem;">📦 Bulk Operations</span>
        <span class="badge" style="background: rgba(255,255,255,0.3); padding: 0.5rem 1rem; border-radius: 2rem;">🔐 Advanced Security</span>
    </div>
</div>

<!-- Pro Features Grid -->
<div class="pro-features">
    <div class="pro-feature">
        <div class="pro-feature-icon">📊</div>
        <div class="pro-feature-title">Analytics Dashboard</div>
        <div class="pro-feature-desc">Real-time charts & insights</div>
    </div>
    <div class="pro-feature">
        <div class="pro-feature-icon">🌐</div>
        <div class="pro-feature-title">Geo Tracking</div>
        <div class="pro-feature-desc">Track clicks by location</div>
    </div>
    <div class="pro-feature">
        <div class="pro-feature-icon">📱</div>
        <div class="pro-feature-title">Device Analytics</div>
        <div class="pro-feature-desc">Desktop, mobile, tablet stats</div>
    </div>
    <div class="pro-feature">
        <div class="pro-feature-icon">🔄</div>
        <div class="pro-feature-title">Link Rotation</div>
        <div class="pro-feature-desc">Split traffic across URLs</div>
    </div>
    <div class="pro-feature">
        <div class="pro-feature-icon">🎯</div>
        <div class="pro-feature-title">UTM Builder</div>
        <div class="pro-feature-desc">Campaign tracking made easy</div>
    </div>
    <div class="pro-feature">
        <div class="pro-feature-icon">📦</div>
        <div class="pro-feature-title">Bulk Shortening</div>
        <div class="pro-feature-desc">Upload CSV files</div>
    </div>
</div>

<!-- Analytics Dashboard -->
<div class="analytics-container">
    <h2 style="margin-top: 0;">📊 Advanced Analytics Dashboard</h2>
    <div id="chartsContainer" class="chart-grid">
        <div><canvas id="clicksChart"></canvas></div>
        <div><canvas id="geoChart"></canvas></div>
        <div><canvas id="deviceChart"></canvas></div>
        <div><canvas id="browserChart"></canvas></div>
    </div>
</div>

<!-- Bulk URL Shortener -->
<div class="bulk-uploader">
    <h3 style="margin-top: 0;">📦 Bulk URL Shortener</h3>
    <p>Upload a CSV file or paste multiple URLs (one per line)</p>
    <textarea id="bulkUrls" class="form-input" rows="6" placeholder="https://example.com/page1
https://example.com/page2
https://example.com/page3" style="margin-top: 1rem;"></textarea>
    <div style="margin-top: 1rem; display: flex; gap: 1rem;">
        <input type="file" accept=".csv" id="csvFile" style="flex: 1; padding: 0.75rem; border-radius: 0.5rem; border: 2px solid white;">
        <button class="dork-btn" style="background: white; color: #667eea;" onclick="processBulkUrls()">⚡ Shorten All</button>
    </div>
    <div id="bulkResults" style="margin-top: 1rem; display: none;"></div>
</div>

<!-- UTM Builder -->
<div class="utm-builder">
    <h3 style="margin-top: 0;">🎯 UTM Parameter Builder</h3>
    <p style="color: #333;">Build trackable campaign URLs with UTM parameters</p>
    <div style="display: grid; gap: 1rem; margin-top: 1rem;">
        <input type="text" id="baseUrl" class="form-input" placeholder="Base URL (https://example.com)">
        <input type="text" id="utmSource" class="form-input" placeholder="Source (e.g., newsletter)">
        <input type="text" id="utmMedium" class="form-input" placeholder="Medium (e.g., email)">
        <input type="text" id="utmCampaign" class="form-input" placeholder="Campaign (e.g., summer_sale)">
        <input type="text" id="utmTerm" class="form-input" placeholder="Term (optional)">
        <input type="text" id="utmContent" class="form-input" placeholder="Content (optional)">
        <button class="dork-btn" style="background: #333; color: white;" onclick="buildUTM()">🔗 Build URL</button>
        <div id="utmResult" style="background: white; padding: 1rem; border-radius: 0.5rem; display: none;"></div>
    </div>
</div>

<!-- A/B Testing -->
<div class="ab-testing">
    <h3 style="margin-top: 0;">🔄 A/B Testing</h3>
    <p>Split test multiple destination URLs</p>
    <textarea id="abUrls" class="form-input" rows="4" placeholder="Enter variant URLs (one per line)
https://example.com/version-a
https://example.com/version-b" style="margin-top: 1rem;"></textarea>
    <button class="dork-btn" style="margin-top: 1rem; background: white; color: #330867;" onclick="createABTest()">🚀 Create A/B Test</button>
</div>

<script>
// Initialize Analytics Dashboard
window.addEventListener('DOMContentLoaded', () => {
    if (typeof window.linkShortenerPro !== 'undefined') {
        console.log('Initializing Pro Analytics Dashboard...');
        // Dashboard will be initialized when data is available
    }
});

function processBulkUrls() {
    const bulkText = document.getElementById('bulkUrls').value;
    const urls = bulkText.split('\n').filter(u => u.trim().startsWith('http'));
    
    if (urls.length === 0) {
        alert('Please enter at least one valid URL');
        return;
    }
    
    alert(`Processing ${urls.length} URLs...`);
    window.linkShortenerPro.bulkShortenUrls(urls).then(results => {
        document.getElementById('bulkResults').style.display = 'block';
        document.getElementById('bulkResults').innerHTML = `
            <h4>✅ Bulk Shortening Complete!</h4>
            <div style="background: white; padding: 1rem; border-radius: 0.5rem; color: #333; max-height: 300px; overflow-y: auto;">
                ${results.map(r => `
                    <div style="padding: 0.5rem; border-bottom: 1px solid #eee;">
                        <div><strong>Original:</strong> ${r.original}</div>
                        <div><strong>Short:</strong> ${r.short || r.error}</div>
                    </div>
                `).join('')}
            </div>
        `;
    });
}

function buildUTM() {
    const baseUrl = document.getElementById('baseUrl').value;
    const source = document.getElementById('utmSource').value;
    const medium = document.getElementById('utmMedium').value;
    const campaign = document.getElementById('utmCampaign').value;
    const term = document.getElementById('utmTerm').value;
    const content = document.getElementById('utmContent').value;
    
    if (!baseUrl || !source || !medium || !campaign) {
        alert('Please fill in required fields (URL, Source, Medium, Campaign)');
        return;
    }
    
    const builder = new window.linkShortenerPro.UTMBuilder();
    builder.setSource(source)
           .setMedium(medium)
           .setCampaign(campaign);
    
    if (term) builder.setTerm(term);
    if (content) builder.setContent(content);
    
    const finalUrl = builder.build(baseUrl);
    
    document.getElementById('utmResult').style.display = 'block';
    document.getElementById('utmResult').innerHTML = `
        <h4 style="margin-top: 0; color: #333;">✅ UTM URL Generated!</h4>
        <div style="word-break: break-all; color: #333; background: #f0f0f0; padding: 1rem; border-radius: 0.5rem;">
            ${finalUrl}
        </div>
        <button onclick="navigator.clipboard.writeText('${finalUrl}')" style="margin-top: 0.5rem; padding: 0.5rem 1rem; background: #fa709a; color: white; border: none; border-radius: 0.5rem; cursor: pointer;">📋 Copy URL</button>
    `;
}

function createABTest() {
    const abText = document.getElementById('abUrls').value;
    const variants = abText.split('\n').filter(u => u.trim().startsWith('http'));
    
    if (variants.length < 2) {
        alert('Please enter at least 2 variant URLs for A/B testing');
        return;
    }
    
    const testId = 'test_' + Date.now();
    const manager = new window.linkShortenerPro.ABTestManager();
    manager.createTest(testId, variants);
    
    alert(`A/B Test created with ${variants.length} variants!\\nTest ID: ${testId}`);
}
</script>
        <!-- Header with Navigation -->
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
                            <div class="dropdown-item">
                                <span class="item-icon">🧲</span>
                                <div class="item-content">
                                    <div class="item-title">Torrent</div>
                                    <div class="item-desc">Download tools</div>
                                </div>
                            </div>
                            <div class="dropdown-item">
                                <span class="item-icon">🎬</span>
                                <div class="item-content">
                                    <div class="item-title">Media</div>
                                    <div class="item-desc">Streaming & conversion</div>
                                </div>
                            </div>
                            <div class="dropdown-item">
                                <span class="item-icon">🤖</span>
                                <div class="item-content">
                                    <div class="item-title">AI</div>
                                    <div class="item-desc">Smart features</div>
                                </div>
                            </div>
                            <div class="dropdown-item active">
                                <span class="item-icon">⚙️</span>
                                <div class="item-content">
                                    <div class="item-title">Utility</div>
                                    <div class="item-desc">Helpful tools</div>
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
                <h1>🔗 Link Shortener</h1>
                <p>Create short, shareable links with analytics and QR codes</p>
            </div>

            <!-- Create Short Link Section -->
            <div class="shortener-card">
                <h2>Create Short Link</h2>
                <form id="createLinkForm" class="shortener-form">
                    <div class="form-group">
                        <label for="originalUrl">Long URL *</label>
                        <input type="url" id="originalUrl" name="original_url" placeholder="https://example.com/very/long/url" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="customAlias">Custom Alias (optional)</label>
                            <input type="text" id="customAlias" name="custom_alias" placeholder="my-link" pattern="[a-zA-Z0-9-_]{3,20}">
                            <small>3-20 characters (letters, numbers, - and _ only)</small>
                        </div>

                        <div class="form-group">
                            <label for="expiresAt">Expiration Date (optional)</label>
                            <input type="datetime-local" id="expiresAt" name="expires_at">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="password">Password (optional)</label>
                            <input type="password" id="password" name="password" placeholder="Protect with password">
                        </div>

                        <div class="form-group">
                            <label for="clickLimit">Click Limit (optional)</label>
                            <input type="number" id="clickLimit" name="click_limit" min="1" placeholder="e.g., 100">
                        </div>
                    </div>

                    <button type="submit" class="btn-primary">🚀 Create Short Link</button>
                </form>

                <!-- Result Display -->
                <div id="linkResult" class="link-result" style="display: none;">
                    <h3>✅ Link Created Successfully!</h3>
                    <div class="result-url">
                        <input type="text" id="shortUrl" readonly>
                        <button onclick="copyToClipboard()" class="btn-copy">📋 Copy</button>
                    </div>
                    <div class="result-qr">
                        <div id="qrcode"></div>
                        <button onclick="downloadQR()" class="btn-download">⬇️ Download QR</button>
                    </div>
                </div>
            </div>

            <!-- Your Links Section -->
            <div class="shortener-card">
                <h2>📊 Your Links</h2>
                <div class="table-container">
                    <table id="linksTable" class="links-table">
                        <thead>
                            <tr>
                                <th>Short URL</th>
                                <th>Original URL</th>
                                <th>Clicks</th>
                                <th>Created</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="linksTableBody">
                            <tr>
                                <td colspan="6" class="loading">Loading your links...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Analytics Section -->
            <div class="shortener-card">
                <h2>📈 Analytics Overview</h2>
                <div class="analytics-grid">
                    <div class="stat-card">
                        <div class="stat-value" id="totalLinks">0</div>
                        <div class="stat-label">Total Links</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value" id="totalClicks">0</div>
                        <div class="stat-label">Total Clicks</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value" id="avgClicks">0</div>
                        <div class="stat-label">Avg. Clicks/Link</div>
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="clicksChart"></canvas>
                </div>
            </div>
        </main>
    </div>

    <script src="shortener-script.js"></script>
    <script>
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadUserLinks();
            initializeAnalytics();
        });
    </script>
</body>
</html>
