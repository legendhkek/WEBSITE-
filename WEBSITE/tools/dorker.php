<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth.php';

// Check if user is logged in
$user = getCurrentUser();
if (!$user) {
    header('Location: ../login.php');
    exit;
}

// Get database connection
$db = getDatabase();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Google Dorker - Advanced Search Tool</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="../dashboard-style.css">
    <style>
        .dorker-container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 1.5rem;
        }

        .dorker-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .dorker-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #000 0%, #333 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .dorker-header p {
            color: var(--gray-600);
            font-size: 1.125rem;
        }

        .dorker-grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .dork-input-section {
            background: white;
            border: 2px solid var(--gray-200);
            border-radius: 1rem;
            padding: 2rem;
        }

        .dork-input-section h2 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: var(--gray-700);
        }

        .form-input {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid var(--gray-300);
            border-radius: 0.5rem;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--gray-900);
        }

        .category-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .category-btn {
            padding: 0.75rem 1rem;
            border: 2px solid var(--gray-300);
            border-radius: 0.5rem;
            background: white;
            cursor: pointer;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.3s;
        }

        .category-btn:hover {
            border-color: var(--gray-900);
            background: var(--gray-50);
        }

        .category-btn.active {
            border-color: var(--gray-900);
            background: var(--gray-900);
            color: white;
        }

        .dork-btn {
            width: 100%;
            padding: 1rem;
            background: var(--gray-900);
            color: white;
            border: none;
            border-radius: 0.5rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .dork-btn:hover {
            background: var(--gray-700);
            transform: translateY(-2px);
        }

        .results-section {
            background: white;
            border: 2px solid var(--gray-200);
            border-radius: 1rem;
            padding: 2rem;
        }

        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .results-header h2 {
            font-size: 1.5rem;
            font-weight: 600;
        }

        .export-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .export-btn {
            padding: 0.5rem 1rem;
            border: 2px solid var(--gray-300);
            border-radius: 0.5rem;
            background: white;
            cursor: pointer;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.3s;
        }

        .export-btn:hover {
            border-color: var(--gray-900);
            background: var(--gray-50);
        }

        .loading-state {
            text-align: center;
            padding: 3rem;
            color: var(--gray-500);
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid var(--gray-200);
            border-top-color: var(--gray-900);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .result-item {
            border-bottom: 1px solid var(--gray-200);
            padding: 1.5rem 0;
        }

        .result-item:last-child {
            border-bottom: none;
        }

        .result-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: #1a0dab;
            margin-bottom: 0.5rem;
            cursor: pointer;
            text-decoration: none;
        }

        .result-title:hover {
            text-decoration: underline;
        }

        .result-url {
            font-size: 0.875rem;
            color: #006621;
            margin-bottom: 0.5rem;
        }

        .result-description {
            font-size: 0.938rem;
            color: var(--gray-700);
            line-height: 1.6;
        }

        .result-actions {
            margin-top: 0.75rem;
            display: flex;
            gap: 0.75rem;
        }

        .action-btn {
            padding: 0.375rem 0.75rem;
            border: 1px solid var(--gray-300);
            border-radius: 0.25rem;
            background: white;
            cursor: pointer;
            font-size: 0.813rem;
            transition: all 0.3s;
        }

        .action-btn:hover {
            background: var(--gray-50);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border: 2px solid var(--gray-200);
            border-radius: 1rem;
            padding: 1.5rem;
            text-align: center;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 0.875rem;
            color: var(--gray-600);
        }

        .operators-help {
            background: var(--gray-50);
            border: 2px solid var(--gray-200);
            border-radius: 0.5rem;
            padding: 1rem;
            margin-top: 1.5rem;
        }

        .operators-help h3 {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
        }

        .operator-list {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.5rem;
            font-size: 0.813rem;
            color: var(--gray-700);
        }

        @media (max-width: 1024px) {
            .dorker-grid {
                grid-template-columns: 1fr;
            }
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
    <!-- Google AdSense -->
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-1940810089559549"
     crossorigin="anonymous"></script>
    <script src="dorker-enhanced-features.js"></script>
</head>
<body>
    <!-- Navigation (same as dashboard) -->
    <nav class="dashboard-nav">
        <div class="nav-container">
            <div class="nav-logo">
                <a href="../index.php">🏠 Legend House</a>
            </div>
            <div class="nav-links">
                <a href="../index.php">Home</a>
                <a href="../watch.php">Watch</a>
                <div class="nav-dropdown">
                    <button class="nav-dropdown-btn">🛠️ Tools</button>
                    <div class="nav-dropdown-menu">
                        <!-- Tools categories will be here -->
                    </div>
                </div>
                <div class="nav-dropdown">
                    <button class="nav-dropdown-btn">👤 <?php echo htmlspecialchars($user['username']); ?></button>
                    <div class="nav-dropdown-menu">
                        <a href="../dashboard.php">📊 Dashboard</a>
                        <a href="../auth.php?action=logout">🚪 Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="dorker-container">
        <div class="dorker-header">
<!-- Enhanced Dorker UI Elements -->
<style>
.godlike-banner {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 1.5rem;
    border-radius: 1rem;
    margin-bottom: 2rem;
    text-align: center;
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
}

.godlike-banner h2 {
    margin: 0;
    font-size: 2rem;
    font-weight: 800;
}

.godlike-banner p {
    margin: 0.5rem 0 0;
    opacity: 0.9;
}

.feature-badges {
    display: flex;
    gap: 0.5rem;
    justify-content: center;
    flex-wrap: wrap;
    margin-top: 1rem;
}

.badge {
    background: rgba(255,255,255,0.2);
    padding: 0.375rem 0.75rem;
    border-radius: 2rem;
    font-size: 0.813rem;
    font-weight: 600;
}

.advanced-tabs {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
    border-bottom: 2px solid var(--gray-200);
}

.tab-btn {
    padding: 0.75rem 1.5rem;
    border: none;
    background: none;
    cursor: pointer;
    font-weight: 600;
    color: var(--gray-600);
    border-bottom: 3px solid transparent;
    transition: all 0.3s;
}

.tab-btn:hover {
    color: var(--gray-900);
}

.tab-btn.active {
    color: var(--gray-900);
    border-bottom-color: var(--gray-900);
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

.ai-generator {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    padding: 2rem;
    border-radius: 1rem;
    color: white;
    margin-bottom: 2rem;
}

.bulk-processor {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    padding: 2rem;
    border-radius: 1rem;
    color: white;
}
</style>

<!-- Godlike Banner -->
<div class="godlike-banner">
    <h2>⚡ GODLIKE GOOGLE DORKER</h2>
    <p>Ultimate Advanced Search Tool with 100+ Categories & AI-Powered Features</p>
    <div class="feature-badges">
        <span class="badge">🤖 AI-Powered</span>
        <span class="badge">📊 100+ Categories</span>
        <span class="badge">🔥 Bulk Processing</span>
        <span class="badge">📈 Result Scoring</span>
        <span class="badge">🎯 Domain Reputation</span>
        <span class="badge">💾 Advanced Exports</span>
    </div>
</div>

<!-- Advanced Tabs -->
<div class="advanced-tabs">
    <button class="tab-btn active" onclick="switchTab('basic')">🎯 Basic Dorking</button>
    <button class="tab-btn" onclick="switchTab('ai')">🤖 AI Generator</button>
    <button class="tab-btn" onclick="switchTab('bulk')">🔥 Bulk Processor</button>
    <button class="tab-btn" onclick="switchTab('advanced')">⚙️ Advanced</button>
</div>

<!-- AI Generator Tab -->
<div id="ai-tab" class="tab-content">
    <div class="ai-generator">
        <h3 style="margin-top:0;">🤖 AI-Powered Dork Generator</h3>
        <p>Describe what you're looking for, and AI will generate advanced dork queries</p>
        <textarea id="aiDescription" class="form-input" rows="3" placeholder="Example: Find admin panels on WordPress sites" style="margin-top: 1rem;"></textarea>
        <button class="dork-btn" style="margin-top: 1rem; background: white; color: #f5576c;" onclick="generateAIDork()">🚀 Generate AI Dork</button>
    </div>
</div>

<!-- Bulk Processor Tab -->
<div id="bulk-tab" class="tab-content">
    <div class="bulk-processor">
        <h3 style="margin-top:0;">🔥 Bulk Dork Processor</h3>
        <p>Process multiple dork queries simultaneously</p>
        <textarea id="bulkDorks" class="form-input" rows="6" placeholder="Enter one dork per line..." style="margin-top: 1rem;"></textarea>
        <button class="dork-btn" style="margin-top: 1rem; background: white; color: #00f2fe;" onclick="processBulk()">⚡ Process All</button>
    </div>
</div>

<script>
function switchTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    
    // Show selected tab
    if (tabName === 'basic') {
        document.querySelector('.dorker-grid').style.display = 'grid';
    } else {
        document.querySelector('.dorker-grid').style.display = 'none';
        document.getElementById(`${tabName}-tab`)?.classList.add('active');
    }
    
    event.target.classList.add('active');
}

function generateAIDork() {
    const description = document.getElementById('aiDescription').value;
    if (!description) {
        alert('Please describe what you want to find');
        return;
    }
    
    const dork = window.enhancedDorker.generateAIDork(description);
    document.getElementById('dorkQuery').value = dork;
    switchTab('basic');
    alert('AI-generated dork added! Click "Start Dorking" to search.');
}

function processBulk() {
    const bulkText = document.getElementById('bulkDorks').value;
    const dorks = bulkText.split('\n').filter(d => d.trim());
    
    if (dorks.length === 0) {
        alert('Please enter at least one dork query');
        return;
    }
    
    alert(`Processing ${dorks.length} dork queries... This may take a moment.`);
    window.enhancedDorker.processBulkDorks(dorks).then(results => {
        currentResults = results;
        displayResults(results);
        switchTab('basic');
        alert(`Bulk processing complete! Found ${results.length} total results.`);
    });
}
</script>
            <h1>🔍 Google Dorker</h1>
            <p>Advanced Google search with 50+ dork operators - No API required!</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value" id="totalDorks">0</div>
                <div class="stat-label">Dorks Run</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="totalResults">0</div>
                <div class="stat-label">Results Found</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="savedQueries">0</div>
                <div class="stat-label">Saved Queries</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="successRate">0%</div>
                <div class="stat-label">Success Rate</div>
            </div>
        </div>

        <div class="dorker-grid">
            <div class="dork-input-section">
                <h2>Build Your Dork</h2>
                
                <div class="form-group">
                    <label for="dorkQuery">Dork Query</label>
                    <textarea id="dorkQuery" class="form-input" rows="4" placeholder='site:example.com intitle:"admin" filetype:php'></textarea>
                </div>

                <div class="form-group">
                    <label>Quick Categories</label>
                    <div class="category-grid">
                        <button class="category-btn" data-category="admin">Admin Panels</button>
                        <button class="category-btn" data-category="files">Exposed Files</button>
                        <button class="category-btn" data-category="vulns">Vulnerabilities</button>
                        <button class="category-btn" data-category="logins">Login Pages</button>
                        <button class="category-btn" data-category="docs">Sensitive Docs</button>
                        <button class="category-btn" data-category="dirs">Directories</button>
                        <button class="category-btn" data-category="iot">IoT Devices</button>
                        <button class="category-btn" data-category="emails">Email Harvest</button>
                    </div>
                </div>

                <button class="dork-btn" onclick="startDorking()">🚀 Start Dorking</button>

                <div class="operators-help">
                    <h3>Common Operators:</h3>
                    <div class="operator-list">
                        <div>• site: - Specific domain</div>
                        <div>• intitle: - Page title</div>
                        <div>• inurl: - In URL</div>
                        <div>• filetype: - File type</div>
                        <div>• intext: - In body</div>
                        <div>• cache: - Cached page</div>
                        <div>• link: - Links to page</div>
                        <div>• related: - Similar pages</div>
                    </div>
                </div>
            </div>

            <div class="results-section">
                <div class="results-header">
                    <h2>Results</h2>
                    <div class="export-buttons">
                        <button class="export-btn" onclick="exportResults('txt')">📄 TXT</button>
                        <button class="export-btn" onclick="exportResults('csv')">📊 CSV</button>
                        <button class="export-btn" onclick="exportResults('json')">📋 JSON</button>
                    </div>
                </div>

                <div id="resultsContainer">
                    <div class="loading-state">
                        <p>Enter a dork query and click "Start Dorking" to begin...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Category presets
        const categoryDorks = {
            admin: 'intitle:"admin" OR intitle:"administrator" OR intitle:"login" inurl:admin',
            files: 'filetype:env OR filetype:sql OR filetype:log OR filetype:conf',
            vulns: 'intext:"sql syntax near" OR intext:"mysql_fetch" OR intext:"error in your SQL"',
            logins: 'inurl:wp-admin OR inurl:administrator OR inurl:cpanel OR inurl:webmail',
            docs: 'filetype:pdf OR filetype:doc OR filetype:xlsx confidential OR password',
            dirs: 'intitle:"index of" inurl:/admin/ OR inurl:/backup/ OR inurl:/config/',
            iot: 'inurl:view/view.shtml OR inurl:ViewerFrame?Mode= OR inurl:/admin/login.asp',
            emails: 'intext:"@gmail.com" OR intext:"@yahoo.com" OR intext:"@hotmail.com"'
        };

        // Category button handlers
        document.querySelectorAll('.category-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const category = this.dataset.category;
                document.getElementById('dorkQuery').value = categoryDorks[category];
                
                // Update active state
                document.querySelectorAll('.category-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
            });
        });

        let currentResults = [];

        async function startDorking() {
            const query = document.getElementById('dorkQuery').value.trim();
            if (!query) {
                alert('Please enter a dork query');
                return;
            }

            const resultsContainer = document.getElementById('resultsContainer');
            resultsContainer.innerHTML = `
                <div class="loading-state">
                    <div class="spinner"></div>
                    <p>Dorking in progress... Please wait.</p>
                </div>
            `;

            try {
                const response = await fetch('dorker-api.php?action=dork', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ query: query })
                });

                const data = await response.json();

                if (data.success) {
                    currentResults = data.results;
                    displayResults(data.results);
                    updateStats(data.stats);
                } else {
                    resultsContainer.innerHTML = `
                        <div class="loading-state">
                            <p style="color: red;">❌ Error: ${data.error}</p>
                        </div>
                    `;
                }
            } catch (error) {
                resultsContainer.innerHTML = `
                    <div class="loading-state">
                        <p style="color: red;">❌ Network error: ${error.message}</p>
                    </div>
                `;
            }
        }

        function displayResults(results) {
            const container = document.getElementById('resultsContainer');
            
            if (results.length === 0) {
                container.innerHTML = `
                    <div class="loading-state">
                        <p>No results found. Try a different dork query.</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = results.map((result, index) => `
                <div class="result-item">
                    <a href="${result.url}" target="_blank" class="result-title">
                        ${result.title}
                    </a>
                    <div class="result-url">${result.url}</div>
                    <div class="result-description">${result.description}</div>
                    <div class="result-actions">
                        <button class="action-btn" onclick="copyToClipboard('${result.url}')">📋 Copy URL</button>
                        <button class="action-btn" onclick="window.open('${result.url}', '_blank')">🔗 Open</button>
                        ${result.cached ? `<button class="action-btn" onclick="window.open('${result.cached}', '_blank')">💾 Cached</button>` : ''}
                    </div>
                </div>
            `).join('');
        }

        function updateStats(stats) {
            if (stats) {
                document.getElementById('totalDorks').textContent = stats.total_dorks || 0;
                document.getElementById('totalResults').textContent = stats.total_results || 0;
                document.getElementById('savedQueries').textContent = stats.saved_queries || 0;
                document.getElementById('successRate').textContent = (stats.success_rate || 0) + '%';
            }
        }

        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                alert('URL copied to clipboard!');
            });
        }

        async function exportResults(format) {
            if (currentResults.length === 0) {
                alert('No results to export');
                return;
            }

            try {
                const response = await fetch(`dorker-api.php?action=export&format=${format}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ results: currentResults })
                });

                const blob = await response.blob();
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `dorker-results-${Date.now()}.${format}`;
                a.click();
                window.URL.revokeObjectURL(url);
            } catch (error) {
                alert('Export failed: ' + error.message);
            }
        }

        // Load stats on page load
        window.addEventListener('DOMContentLoaded', async () => {
            try {
                const response = await fetch('dorker-api.php?action=stats');
                const data = await response.json();
                if (data.success) {
                    updateStats(data.stats);
                }
            } catch (error) {
                console.error('Failed to load stats:', error);
            }
        });
    </script>
</body>
</html>
