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
                        <a href="#" onclick="logoutUser(); return false;">🚪 Logout</a>
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
    
    if (dorks.length > 20) {
        if (!confirm(`You're about to process ${dorks.length} dork queries. This may take several minutes. Continue?`)) {
            return;
        }
    }
    
    // Show progress
    const progressMsg = document.createElement('div');
    progressMsg.style.cssText = 'position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:white; padding:2rem; border-radius:1rem; box-shadow:0 10px 40px rgba(0,0,0,0.2); z-index:9999; min-width:300px; text-align:center;';
    progressMsg.innerHTML = `
        <h3 style="margin:0 0 1rem 0;">⚡ Processing Bulk Dorks</h3>
        <div id="progress-info">Starting...</div>
        <div style="width:100%; height:8px; background:#e5e7eb; border-radius:4px; margin:1rem 0; overflow:hidden;">
            <div id="progress-bar" style="height:100%; background:linear-gradient(90deg,#000,#404040); width:0%; transition:width 0.3s;"></div>
        </div>
        <small id="progress-detail" style="color:#666;">Processed: 0/${dorks.length}</small>
    `;
    document.body.appendChild(progressMsg);
    
    // Process with progress callback
    window.enhancedDorker.processBulkDorks(dorks, (progress) => {
        const progressBar = document.getElementById('progress-bar');
        const progressInfo = document.getElementById('progress-info');
        const progressDetail = document.getElementById('progress-detail');
        
        if (progressBar) progressBar.style.width = progress.percentage + '%';
        if (progressInfo) {
            // Sanitize and truncate currentDork to prevent XSS
            const sanitizedDork = progress.currentDork.substring(0, 50).replace(/[<>'"]/g, '');
            progressInfo.textContent = `Processing: ${sanitizedDork}...`;
        }
        if (progressDetail) progressDetail.innerHTML = `
            Processed: ${progress.processed}/${progress.total} | 
            Found: ${progress.resultsFound} results
        `;
    }).then(bulkResults => {
        document.body.removeChild(progressMsg);
        
        currentResults = bulkResults.results;
        displayResults(bulkResults.results);
        switchTab('basic');
        
        // Show comprehensive summary
        alert(`✅ Bulk Processing Complete!\n\n` +
              `Total Dorks: ${bulkResults.totalProcessed}\n` +
              `Successful: ${bulkResults.summary.successful}\n` +
              `Failed: ${bulkResults.summary.failed}\n` +
              `Total Results: ${bulkResults.totalResults}\n` +
              `Avg Results/Dork: ${bulkResults.summary.avgResultsPerDork.toFixed(1)}\n\n` +
              `Results sorted by relevance score.`);
    }).catch(error => {
        document.body.removeChild(progressMsg);
        alert('❌ Bulk processing failed: ' + error.message);
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
                    <label>Quick Categories (150+ Available)</label>
                    <div class="category-grid">
                        <button class="category-btn" data-category="admin">🔐 Admin Panels</button>
                        <button class="category-btn" data-category="files">📁 Exposed Files</button>
                        <button class="category-btn" data-category="vulns">⚠️ Vulnerabilities</button>
                        <button class="category-btn" data-category="logins">🔑 Login Pages</button>
                        <button class="category-btn" data-category="docs">📄 Sensitive Docs</button>
                        <button class="category-btn" data-category="dirs">📂 Directories</button>
                        <button class="category-btn" data-category="iot">📹 IoT Devices</button>
                        <button class="category-btn" data-category="emails">📧 Email Lists</button>
                        <button class="category-btn" data-category="api-keys">🔑 API Keys</button>
                        <button class="category-btn" data-category="database-files">💾 Databases</button>
                        <button class="category-btn" data-category="git-repos">🔧 Git Repos</button>
                        <button class="category-btn" data-category="aws-s3">☁️ AWS S3</button>
                    </div>
                    <div style="margin-top: 1rem;">
                        <select id="categorySelect" class="form-input" onchange="loadCategoryDork(this.value)">
                            <option value="">-- Load More Categories --</option>
                            <optgroup label="Security & Vulnerabilities">
                                <option value="sql-injection">SQL Injection</option>
                                <option value="xss-vulns">XSS Vulnerabilities</option>
                                <option value="lfi">Local File Inclusion</option>
                                <option value="rfi">Remote File Inclusion</option>
                                <option value="open-redirects">Open Redirects</option>
                                <option value="xxe-injection">XXE Injection</option>
                                <option value="ssrf-vulns">SSRF Vulnerabilities</option>
                                <option value="command-injection">Command Injection</option>
                                <option value="path-traversal">Path Traversal</option>
                                <option value="csrf-vulnerable">CSRF Vulnerable</option>
                            </optgroup>
                            <optgroup label="Admin & Authentication">
                                <option value="admin-panels">Admin Panels</option>
                                <option value="login-pages">Login Pages</option>
                                <option value="wp-admin">WordPress Admin</option>
                                <option value="cpanel">cPanel</option>
                                <option value="phpmyadmin">phpMyAdmin</option>
                                <option value="joomla-admin">Joomla Admin</option>
                                <option value="drupal-admin">Drupal Admin</option>
                                <option value="magento-admin">Magento Admin</option>
                            </optgroup>
                            <optgroup label="Exposed Files">
                                <option value="config-files">Config Files</option>
                                <option value="database-files">Database Files</option>
                                <option value="log-files">Log Files</option>
                                <option value="backup-files">Backup Files</option>
                                <option value="env-files">Environment Files</option>
                                <option value="ssh-keys">SSH Keys</option>
                                <option value="credentials">Credentials</option>
                                <option value="git-config">Git Config</option>
                            </optgroup>
                            <optgroup label="Sensitive Documents">
                                <option value="financial-docs">Financial Documents</option>
                                <option value="legal-docs">Legal Documents</option>
                                <option value="medical-records">Medical Records</option>
                                <option value="email-lists">Email Lists</option>
                                <option value="password-lists">Password Lists</option>
                                <option value="invoices">Invoices</option>
                            </optgroup>
                            <optgroup label="IoT & Devices">
                                <option value="webcams">Webcams</option>
                                <option value="printers">Printers</option>
                                <option value="routers">Routers</option>
                                <option value="security-cameras">Security Cameras</option>
                                <option value="nas-devices">NAS Devices</option>
                            </optgroup>
                            <optgroup label="Cloud Services">
                                <option value="aws-s3">AWS S3 Buckets</option>
                                <option value="azure-storage">Azure Storage</option>
                                <option value="google-cloud">Google Cloud</option>
                                <option value="firebase-db">Firebase DB</option>
                                <option value="heroku-apps">Heroku Apps</option>
                            </optgroup>
                            <optgroup label="Development">
                                <option value="github-secrets">GitHub Secrets</option>
                                <option value="api-keys">API Keys</option>
                                <option value="git-repos">Git Repositories</option>
                                <option value="docker-files">Docker Files</option>
                                <option value="jenkins-servers">Jenkins Servers</option>
                                <option value="aws-keys">AWS Keys</option>
                            </optgroup>
                            <optgroup label="Advanced">
                                <option value="api-endpoints">API Endpoints</option>
                                <option value="swagger-docs">Swagger Docs</option>
                                <option value="graphql-endpoints">GraphQL</option>
                                <option value="directory-listings">Directory Listings</option>
                                <option value="vulnerable-apps">Vulnerable Apps</option>
                                <option value="web-shells">Web Shells</option>
                            </optgroup>
                        </select>
                    </div>
                </div>

                <button class="dork-btn" onclick="startDorking()">🚀 Start Dorking</button>

                <div class="operators-help">
                    <h3>📚 Advanced Google Dork Operators (100+):</h3>
                    <div class="operator-list">
                        <div><strong>Basic:</strong></div>
                        <div>• site: - Search specific domain</div>
                        <div>• intitle: - Search in page title</div>
                        <div>• inurl: - Search in URL</div>
                        <div>• filetype: - Search specific file type</div>
                        <div>• ext: - File extension (same as filetype)</div>
                        <div>• intext: - Search in page body</div>
                        <div>• allintext: - All words in body</div>
                        <div>• allintitle: - All words in title</div>
                        <div>• allinurl: - All words in URL</div>
                        <div>• cache: - View cached version</div>
                        <div>• link: - Pages linking to URL</div>
                        <div>• related: - Similar pages</div>
                        <div>• info: - Info about page</div>
                        <div><strong>Advanced:</strong></div>
                        <div>• | - OR operator (pipe)</div>
                        <div>• - - Exclude term (minus)</div>
                        <div>• " " - Exact phrase match</div>
                        <div>• * - Wildcard (any word)</div>
                        <div>• .. - Number range (e.g., 2020..2024)</div>
                        <div>• AROUND(X) - Words near each other</div>
                        <div>• stocks: - Stock market info</div>
                        <div>• define: - Definition of term</div>
                        <div>• weather: - Weather info</div>
                        <div>• movie: - Movie info</div>
                        <div><strong>Combinations:</strong></div>
                        <div>• Use () for grouping queries</div>
                        <div>• Chain multiple operators</div>
                        <div>• Combine with keywords</div>
                        <div>• Use negation effectively</div>
                    </div>
                    <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--gray-300);">
                        <strong>💡 Pro Tips:</strong>
                        <div style="font-size: 0.813rem; color: var(--gray-600); margin-top: 0.5rem; line-height: 1.6;">
                            • Combine operators for powerful searches<br>
                            • Use quotes for exact matches<br>
                            • Use minus (-) to exclude unwanted results<br>
                            • Try multiple operators together<br>
                            • Test different combinations<br>
                            • Save successful dorks for later
                        </div>
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

    <!-- Footer -->
    <footer style="text-align: center; padding: 2rem 1rem; background: var(--white); border-top: 2px solid var(--gray-200); margin-top: 3rem;">
        <div style="max-width: 1200px; margin: 0 auto;">
            <p style="margin: 0; font-size: 0.9rem; color: var(--gray-600);">
                <strong style="color: var(--black);">Powered by Legend House</strong> • Advanced Tools Platform
            </p>
        </div>
    </footer>

    <script>
        // Category presets - Enhanced with more dorks
        const categoryDorks = {
            admin: 'intitle:"admin" OR intitle:"administrator" OR intitle:"login" inurl:admin OR inurl:administrator',
            files: 'filetype:env OR filetype:sql OR filetype:log OR filetype:conf OR filetype:config OR filetype:bak',
            vulns: 'intext:"sql syntax near" OR intext:"mysql_fetch" OR intext:"error in your SQL" OR intext:"Warning: mysql"',
            logins: 'inurl:wp-admin OR inurl:administrator OR inurl:cpanel OR inurl:webmail OR inurl:login OR inurl:signin',
            docs: 'filetype:pdf OR filetype:doc OR filetype:xlsx OR filetype:docx intext:"confidential" OR intext:"password"',
            dirs: 'intitle:"index of" inurl:/admin/ OR inurl:/backup/ OR inurl:/config/ OR inurl:/data/',
            iot: 'inurl:view/view.shtml OR inurl:ViewerFrame?Mode= OR inurl:/admin/login.asp OR inurl:video.cgi',
            emails: 'intext:"@gmail.com" OR intext:"@yahoo.com" OR intext:"@hotmail.com" OR intext:"@outlook.com" filetype:csv OR filetype:txt',
            'api-keys': 'intext:"api_key" OR intext:"apikey" OR intext:"api key" OR intext:"secret_key" filetype:json OR filetype:txt OR filetype:js',
            'database-files': 'filetype:sql OR filetype:db OR filetype:mdb OR filetype:sqlite intext:INSERT OR intext:"CREATE TABLE"',
            'git-repos': 'inurl:.git/config OR inurl:.git/HEAD OR intext:"repositoryformatversion" OR intitle:"index of" .git',
            'aws-s3': 'site:s3.amazonaws.com OR inurl:".s3.amazonaws.com" OR inurl:s3.amazonaws.com'
        };

        // Function to load category from dropdown
        function loadCategoryDork(category) {
            if (!category) return;
            
            // Check if category exists in enhancedDorker categories
            if (window.enhancedDorker && window.enhancedDorker.categories && window.enhancedDorker.categories[category]) {
                document.getElementById('dorkQuery').value = window.enhancedDorker.categories[category];
            } else if (categoryDorks[category]) {
                document.getElementById('dorkQuery').value = categoryDorks[category];
            }
            
            // Reset select
            document.getElementById('categorySelect').value = '';
        }

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
                    <p>🔍 Advanced Google Dorking in progress...</p>
                    <p style="font-size:0.875rem; color:#666; margin-top:0.5rem;">Optimizing query and scraping multiple pages</p>
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
                    
                    // Show query optimization info if available
                    if (data.query_info) {
                        displayQueryInfo(data.query_info);
                    }
                    
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
        
        // Display query optimization information
        function displayQueryInfo(queryInfo) {
            const container = document.getElementById('resultsContainer');
            
            let infoHtml = '<div style="background:#f0f9ff; border-left:4px solid #0284c7; padding:1rem; margin-bottom:1rem; border-radius:0.5rem;">';
            infoHtml += '<h4 style="margin:0 0 0.5rem 0; font-size:0.875rem; font-weight:600; color:#0369a1;">📊 Query Analysis</h4>';
            
            if (queryInfo.optimized !== queryInfo.original) {
                infoHtml += `<p style="margin:0.25rem 0; font-size:0.813rem;"><strong>Original:</strong> <code style="background:#e0f2fe; padding:0.125rem 0.375rem; border-radius:0.25rem;">${queryInfo.original}</code></p>`;
                infoHtml += `<p style="margin:0.25rem 0; font-size:0.813rem;"><strong>Optimized:</strong> <code style="background:#dbeafe; padding:0.125rem 0.375rem; border-radius:0.25rem;">${queryInfo.optimized}</code></p>`;
            }
            
            if (queryInfo.suggestions && queryInfo.suggestions.operators_used && queryInfo.suggestions.operators_used.length > 0) {
                infoHtml += `<p style="margin:0.5rem 0 0.25rem 0; font-size:0.813rem;"><strong>Operators Used:</strong> ${queryInfo.suggestions.operators_used.map(op => `<span style="background:#bae6fd; padding:0.125rem 0.375rem; border-radius:0.25rem; margin-right:0.25rem;">${op}</span>`).join('')}</p>`;
            }
            
            if (queryInfo.suggestions && queryInfo.suggestions.complexity) {
                const complexityColors = {
                    'basic': '#86efac',
                    'intermediate': '#fbbf24',
                    'advanced': '#f87171'
                };
                const color = complexityColors[queryInfo.suggestions.complexity] || '#86efac';
                infoHtml += `<p style="margin:0.25rem 0; font-size:0.813rem;"><strong>Complexity:</strong> <span style="background:${color}; padding:0.125rem 0.375rem; border-radius:0.25rem; font-weight:600;">${queryInfo.suggestions.complexity.toUpperCase()}</span></p>`;
            }
            
            if (queryInfo.suggestions && queryInfo.suggestions.tips && queryInfo.suggestions.tips.length > 0) {
                infoHtml += '<div style="margin-top:0.5rem; padding-top:0.5rem; border-top:1px solid #bae6fd;">';
                infoHtml += '<p style="margin:0 0 0.25rem 0; font-size:0.813rem; font-weight:600;">💡 Tips:</p>';
                queryInfo.suggestions.tips.forEach(tip => {
                    infoHtml += `<p style="margin:0.125rem 0; font-size:0.75rem; color:#0369a1;">• ${tip}</p>`;
                });
                infoHtml += '</div>';
            }
            
            infoHtml += '</div>';
            
            container.innerHTML = infoHtml + container.innerHTML;
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

            // Sort by score if available
            const sortedResults = results.sort((a, b) => {
                const scoreA = a.score || window.enhancedDorker?.scoreResult(a) || 0;
                const scoreB = b.score || window.enhancedDorker?.scoreResult(b) || 0;
                return scoreB - scoreA;
            });

            container.innerHTML = sortedResults.map((result, index) => {
                const score = result.score || (window.enhancedDorker ? window.enhancedDorker.scoreResult(result) : 0);
                const scoreColor = score >= 70 ? '#dc2626' : score >= 40 ? '#f59e0b' : '#10b981';
                
                // Sanitize data for safe display
                const sanitizedTitle = (result.title || '').replace(/[<>]/g, '');
                const sanitizedUrl = (result.url || '').replace(/["'<>]/g, '');
                const sanitizedDesc = (result.description || '').replace(/[<>]/g, '');
                const sanitizedCached = result.cached ? (result.cached || '').replace(/["'<>]/g, '') : null;
                
                const scoreBadge = score > 0 ? `<span style="background:${scoreColor}; color:white; padding:0.25rem 0.5rem; border-radius:0.25rem; font-size:0.75rem; font-weight:600; margin-left:0.5rem;">Score: ${score}</span>` : '';
                
                // Display indicators if available
                let indicatorBadges = '';
                if (result.indicators && result.indicators.length > 0) {
                    const iconMap = {
                        'admin_access': '🔐',
                        'configuration': '⚙️',
                        'backup': '💾',
                        'api': '🔌',
                        'database': '🗄️',
                        'version_control': '📦',
                        'cloud_storage': '☁️'
                    };
                    indicatorBadges = '<div style="margin-top:0.5rem;">';
                    result.indicators.forEach(ind => {
                        const icon = iconMap[ind] || '🔖';
                        const label = ind.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                        indicatorBadges += `<span style="background:#eff6ff; color:#1e40af; padding:0.125rem 0.5rem; border-radius:0.25rem; font-size:0.75rem; margin-right:0.25rem;">${icon} ${label}</span>`;
                    });
                    indicatorBadges += '</div>';
                }
                
                // Show file type if it's a file
                let fileTypeBadge = '';
                if (result.is_file && result.file_type) {
                    fileTypeBadge = `<span style="background:#fef3c7; color:#92400e; padding:0.125rem 0.5rem; border-radius:0.25rem; font-size:0.75rem; margin-left:0.5rem;">📄 ${result.file_type.toUpperCase()}</span>`;
                }
                
                const resultHtml = `
                    <div class="result-item" style="position:relative; padding-left:1rem; border-left:4px solid ${scoreColor};">
                        <a href="${sanitizedUrl}" target="_blank" rel="noopener noreferrer" class="result-title">
                            ${index + 1}. ${sanitizedTitle}
                            ${scoreBadge}
                            ${fileTypeBadge}
                        </a>
                        <div class="result-url">${sanitizedUrl}</div>
                        <div class="result-description">${sanitizedDesc}</div>
                        ${indicatorBadges}
                        <div class="result-actions">
                            <button class="action-btn" data-url="${sanitizedUrl}">📋 Copy URL</button>
                            <button class="action-btn" data-open-url="${sanitizedUrl}">🔗 Open</button>
                            ${sanitizedCached ? `<button class="action-btn" data-open-url="${sanitizedCached}">💾 Cached</button>` : ''}
                            ${score > 0 ? `<button class="action-btn" style="background:#f3f4f6; font-weight:600;">🎯 Relevance: ${score}/100</button>` : ''}
                        </div>
                    </div>
                `;
                return resultHtml;
            }).join('');
            
            // Add event listeners for buttons (safer than inline onclick)
            container.querySelectorAll('button[data-url]').forEach(btn => {
                btn.addEventListener('click', function() {
                    copyToClipboard(this.dataset.url);
                });
            });
            
            container.querySelectorAll('button[data-open-url]').forEach(btn => {
                btn.addEventListener('click', function() {
                    window.open(this.dataset.openUrl, '_blank', 'noopener,noreferrer');
                });
            });
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
    
    <script>
    // Logout function
    async function logoutUser() {
        try {
            const formData = new FormData();
            formData.append('action', 'logout');
            await fetch('../auth.php', { method: 'POST', body: formData });
            window.location.href = '../login.php';
        } catch (error) {
            alert('Logout failed');
        }
    }
    </script>
    
    <!-- AI Chat Widget Integration -->
    <script src="../ai-chat-widget.js"></script>
    <script>
        // Set context to 'dorking' for dorker page
        document.body.dataset.aiContext = 'dorking';
    </script>
</body>
</html>
