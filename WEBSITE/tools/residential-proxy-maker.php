<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth.php';

if (!isLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Residential Proxy Maker - Legend House</title>
    <link rel="stylesheet" href="../dashboard-style.css">
    <style>
        .residential-maker-container {
            max-width: 1400px;
            margin: 20px auto;
            padding: 20px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            border-radius: 10px;
            color: white;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .stat-card h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            opacity: 0.9;
        }
        
        .stat-card .value {
            font-size: 32px;
            font-weight: bold;
            margin: 0;
        }
        
        .stat-card.global {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        
        .action-section {
            background: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .action-section h2 {
            margin-top: 0;
            color: #333;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(102, 126, 234, 0.4);
        }
        
        .btn-success {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
        }
        
        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(56, 239, 125, 0.4);
        }
        
        .progress-container {
            display: none;
            margin-top: 20px;
        }
        
        .progress-bar {
            width: 100%;
            height: 30px;
            background: #f0f0f0;
            border-radius: 15px;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            width: 0%;
            transition: width 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
        
        .result-box {
            margin-top: 20px;
            padding: 15px;
            border-radius: 5px;
            display: none;
        }
        
        .result-box.success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .result-box.error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        
        .proxy-list {
            max-height: 400px;
            overflow-y: auto;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            font-family: monospace;
            font-size: 12px;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="residential-maker-container">
        <h1>🏠 Residential Proxy Maker</h1>
        <p>Convert regular proxies to residential rotating proxies with real-time checking and stats</p>
        
        <!-- Stats Section -->
        <div class="stats-grid" id="statsGrid">
            <div class="stat-card">
                <h3>Checked Proxies</h3>
                <p class="value" id="statChecked">0</p>
            </div>
            <div class="stat-card">
                <h3>Working Proxies</h3>
                <p class="value" id="statWorking">0</p>
            </div>
            <div class="stat-card">
                <h3>Residential Proxies</h3>
                <p class="value" id="statResidential">0</p>
            </div>
            <div class="stat-card">
                <h3>Success Rate</h3>
                <p class="value" id="statSuccessRate">0%</p>
            </div>
            <div class="stat-card global">
                <h3>Global Total Proxies</h3>
                <p class="value" id="statGlobalTotal">0</p>
            </div>
            <div class="stat-card global">
                <h3>Global Residential</h3>
                <p class="value" id="statGlobalResidential">0</p>
            </div>
        </div>
        
        <!-- Scrape and Check Section -->
        <div class="action-section">
            <h2>Step 1: Scrape & Auto-Check Proxies</h2>
            <p>Scrape proxies from 200+ sources and automatically check which ones are working</p>
            
            <div class="form-group">
                <label>Max Proxies per Source:</label>
                <input type="number" id="maxPerSource" value="30" min="1" max="200">
            </div>
            
            <button class="btn btn-primary" onclick="scrapeAndCheck()">
                🔍 Scrape & Check Proxies
            </button>
            
            <div class="progress-container" id="scrapeProgress">
                <div class="progress-bar">
                    <div class="progress-fill" id="scrapeProgressFill">0%</div>
                </div>
                <p id="scrapeStatus" style="margin-top: 10px;"></p>
            </div>
            
            <div class="result-box" id="scrapeResult"></div>
            
            <button class="btn btn-success" onclick="downloadWorkingProxies()" style="margin-top: 15px; display: none;" id="downloadWorkingBtn">
                📥 Download Working Proxies (TXT)
            </button>
        </div>
        
        <!-- Convert to Residential Section -->
        <div class="action-section">
            <h2>Step 2: Convert to Residential Rotating Proxies</h2>
            <p>Convert minimum 200 working proxies into a residential rotating proxy pool</p>
            
            <div class="form-group">
                <label>Pool Name:</label>
                <input type="text" id="poolName" value="Residential Pool <?php echo date('Y-m-d'); ?>" placeholder="Enter pool name">
            </div>
            
            <div class="form-group">
                <label>Minimum Proxies Required:</label>
                <input type="number" id="minProxies" value="200" min="100" max="1000">
            </div>
            
            <div class="form-group">
                <label>Source:</label>
                <select id="conversionSource">
                    <option value="database">From Checked Proxies (Database)</option>
                    <option value="upload">Upload TXT File</option>
                </select>
            </div>
            
            <div class="form-group" id="uploadGroup" style="display: none;">
                <label>Upload Proxy List (TXT format: IP:PORT):</label>
                <input type="file" id="proxyFile" accept=".txt">
            </div>
            
            <button class="btn btn-success" onclick="convertToResidential()">
                🔄 Convert to Residential Pool
            </button>
            
            <div class="result-box" id="convertResult"></div>
            
            <button class="btn btn-success" onclick="downloadResidentialProxies()" style="margin-top: 15px; display: none;" id="downloadResidentialBtn">
                📥 Download Residential Proxies (TXT)
            </button>
        </div>
    </div>
    
    <!-- AI Chat Widget -->
    <script src="../ai-chat-widget.js"></script>
    <script>
        new LegendAIChat({
            context: 'residential_proxy_maker',
            position: 'bottom-right'
        });
    </script>
    
    <script>
        // Load stats on page load
        loadStats();
        
        // Source change handler
        document.getElementById('conversionSource').addEventListener('change', function() {
            document.getElementById('uploadGroup').style.display = 
                this.value === 'upload' ? 'block' : 'none';
        });
        
        function loadStats() {
            fetch('residential-proxy-maker-api.php?action=get_stats')
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('statChecked').textContent = data.user_stats.total_proxies_checked;
                        document.getElementById('statWorking').textContent = data.user_stats.working_proxies;
                        document.getElementById('statResidential').textContent = data.user_stats.residential_proxies;
                        document.getElementById('statSuccessRate').textContent = data.user_stats.success_rate + '%';
                        document.getElementById('statGlobalTotal').textContent = data.global_stats.total_proxies_checked;
                        document.getElementById('statGlobalResidential').textContent = data.global_stats.total_residential;
                    }
                });
        }
        
        function scrapeAndCheck() {
            const maxPerSource = document.getElementById('maxPerSource').value;
            const progressContainer = document.getElementById('scrapeProgress');
            const progressFill = document.getElementById('scrapeProgressFill');
            const status = document.getElementById('scrapeStatus');
            const resultBox = document.getElementById('scrapeResult');
            
            progressContainer.style.display = 'block';
            progressFill.style.width = '10%';
            progressFill.textContent = '10%';
            status.textContent = 'Scraping from 200+ sources...';
            resultBox.style.display = 'none';
            
            const formData = new FormData();
            formData.append('sources[]', 'all');
            formData.append('max_per_source', maxPerSource);
            formData.append('auto_check', '1');
            
            fetch('residential-proxy-maker-api.php?action=scrape_and_check', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                progressFill.style.width = '100%';
                progressFill.textContent = '100%';
                
                if (data.success) {
                    status.textContent = `Completed! Scraped: ${data.total_scraped}, Checked: ${data.total_checked}, Working: ${data.total_working}`;
                    resultBox.className = 'result-box success';
                    resultBox.style.display = 'block';
                    resultBox.innerHTML = `
                        <strong>✅ Success!</strong><br>
                        Total Scraped: ${data.total_scraped}<br>
                        Total Checked: ${data.total_checked}<br>
                        Working Proxies: ${data.total_working}<br>
                        Success Rate: ${data.check_rate}%
                    `;
                    document.getElementById('downloadWorkingBtn').style.display = 'inline-block';
                    loadStats();
                } else {
                    resultBox.className = 'result-box error';
                    resultBox.style.display = 'block';
                    resultBox.innerHTML = `<strong>❌ Error:</strong> ${data.error}`;
                }
            })
            .catch(err => {
                resultBox.className = 'result-box error';
                resultBox.style.display = 'block';
                resultBox.innerHTML = `<strong>❌ Error:</strong> ${err.message}`;
            });
        }
        
        function convertToResidential() {
            const poolName = document.getElementById('poolName').value;
            const minProxies = document.getElementById('minProxies').value;
            const source = document.getElementById('conversionSource').value;
            const resultBox = document.getElementById('convertResult');
            
            const formData = new FormData();
            formData.append('pool_name', poolName);
            formData.append('min_proxies', minProxies);
            formData.append('source', source);
            
            if (source === 'upload') {
                const fileInput = document.getElementById('proxyFile');
                if (!fileInput.files[0]) {
                    alert('Please select a file to upload');
                    return;
                }
                formData.append('proxy_file', fileInput.files[0]);
            }
            
            resultBox.style.display = 'none';
            
            fetch('residential-proxy-maker-api.php?action=convert_to_residential', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    resultBox.className = 'result-box success';
                    resultBox.style.display = 'block';
                    resultBox.innerHTML = `
                        <strong>✅ Conversion Successful!</strong><br>
                        Pool Name: ${data.pool_name}<br>
                        Total Converted: ${data.total_converted}<br>
                        Pool Size: ${data.pool_size}<br>
                        Rotation: ${data.rotation_enabled ? 'Enabled' : 'Disabled'}<br><br>
                        Your residential rotating proxy pool is ready to use!
                    `;
                    document.getElementById('downloadResidentialBtn').style.display = 'inline-block';
                    loadStats();
                } else {
                    resultBox.className = 'result-box error';
                    resultBox.style.display = 'block';
                    resultBox.innerHTML = `<strong>❌ Error:</strong> ${data.error}`;
                }
            })
            .catch(err => {
                resultBox.className = 'result-box error';
                resultBox.style.display = 'block';
                resultBox.innerHTML = `<strong>❌ Error:</strong> ${err.message}`;
            });
        }
        
        function downloadWorkingProxies() {
            window.location.href = 'residential-proxy-maker-api.php?action=download_txt&type=working';
        }
        
        function downloadResidentialProxies() {
            window.location.href = 'residential-proxy-maker-api.php?action=download_txt&type=residential';
        }
        
        // Refresh stats every 30 seconds
        setInterval(loadStats, 30000);
    </script>
    
    <!-- Powered by Legend House Footer -->
    <div style="text-align: center; margin: 40px 0 20px 0; padding: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 10px;">
        <p style="color: white; font-size: 18px; font-weight: bold; margin: 0;">⚡ Powered by LEGEND HOUSE</p>
        <p style="color: rgba(255,255,255,0.8); font-size: 14px; margin: 5px 0 0 0;">Real Residential Proxy Maker - No Mock Data</p>
    </div>
</body>
</html>
