<?php
session_start();
require_once 'config.php';
require_once 'auth.php';

// Check if user is logged in
$user = getCurrentUser();
if (!$user) {
    header('Location: login.php');
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
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-1940810089559549"
     crossorigin="anonymous"></script>
</head>
<body>
    <div class="dashboard-container">
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
