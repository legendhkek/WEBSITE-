<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rotating Proxy Maker - Legend House</title>
    
    <!-- Google AdSense -->
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-1940810089559549"
         crossorigin="anonymous"></script>
    
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="shortener-style.css">
    
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🔄</text></svg>">
    
    <style>
        .proxy-pool-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin: 2rem 0;
        }
        
        .pool-card {
            background: var(--white);
            border: 2px solid var(--gray-200);
            border-radius: 12px;
            padding: 1.5rem;
            transition: all 0.3s ease;
        }
        
        .pool-card:hover {
            border-color: var(--black);
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .pool-stat {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--gray-200);
        }
        
        .pool-stat:last-child {
            border-bottom: none;
        }
        
        .pool-stat-label {
            font-size: 0.875rem;
            color: var(--gray-600);
        }
        
        .pool-stat-value {
            font-weight: 600;
            font-size: 1.125rem;
            color: var(--black);
        }
        
        .strategy-selector {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin: 1.5rem 0;
        }
        
        .strategy-option {
            padding: 1rem;
            border: 2px solid var(--gray-200);
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
        }
        
        .strategy-option:hover,
        .strategy-option.active {
            border-color: var(--black);
            background: var(--gray-50);
        }
        
        .strategy-icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .strategy-name {
            font-weight: 600;
            color: var(--black);
            margin-bottom: 0.25rem;
        }
        
        .strategy-desc {
            font-size: 0.75rem;
            color: var(--gray-600);
        }
        
        .api-endpoint-box {
            background: var(--gray-50);
            border: 2px solid var(--gray-200);
            border-radius: 8px;
            padding: 1rem;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.875rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 1rem 0;
        }
        
        .code-snippet {
            background: var(--black);
            color: var(--white);
            padding: 1.5rem;
            border-radius: 8px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.875rem;
            overflow-x: auto;
            margin: 1rem 0;
        }
        
        .code-snippet pre {
            margin: 0;
        }
    </style>
</head>
<body>
    <?php
    session_start();
    require_once __DIR__ . '/../auth.php';
    
    // Check if user is logged in
    if (!isLoggedIn()) {
        header('Location: ../login.php');
        exit;
    }
    
    $user = getCurrentUser();
    ?>
    
    <!-- Header -->
    <header class="header">
        <div class="container">
            <a href="../" class="logo">
                <div class="logo-icon">🔄</div>
                <span>Rotating Proxy Maker</span>
            </a>
            <nav class="header-nav">
                <a href="../" class="nav-link">Home</a>
                <a href="../dashboard.php" class="nav-link">Dashboard</a>
                <a href="../tools.php" class="nav-link">Tools</a>
            </nav>
        </div>
    </header>
    
    <main class="main-content">
        <div class="container" style="max-width: 1200px;">
            <!-- Hero Section -->
            <div class="hero-section" style="text-align: center; padding: 3rem 0 2rem;">
                <h1 style="font-size: 3rem; font-weight: 900; margin-bottom: 1rem;">🔄 Rotating Proxy Maker</h1>
                <p style="font-size: 1.25rem; color: var(--gray-600); max-width: 700px; margin: 0 auto;">
                    Transform your proxy list into a professional rotating proxy service with API access
                </p>
            </div>
            
            <!-- Upload Section -->
            <div class="section-card">
                <h2 class="section-title">📁 Upload Proxy List</h2>
                <p style="color: var(--gray-600); margin-bottom: 1.5rem;">
                    Upload a file containing 200-10,000 working proxies (one per line)
                </p>
                
                <div class="form-group">
                    <label>Proxy List File</label>
                    <input type="file" id="proxyFile" accept=".txt,.csv" class="form-input">
                    <small style="color: var(--gray-500);">Supported formats: TXT, CSV (IP:PORT or IP PORT)</small>
                </div>
                
                <div class="form-group">
                    <label>Pool Name</label>
                    <input type="text" id="poolName" class="form-input" placeholder="My Proxy Pool" maxlength="50">
                </div>
                
                <button id="uploadBtn" class="btn btn-primary btn-large">
                    <span>🚀</span>
                    Create Proxy Pool
                </button>
                
                <div id="uploadProgress" style="display: none; margin-top: 1.5rem;">
                    <div class="progress-bar-container">
                        <div class="progress-bar" id="validationProgress"></div>
                    </div>
                    <p id="validationStatus" style="text-align: center; color: var(--gray-600); margin-top: 0.5rem;">
                        Validating proxies...
                    </p>
                </div>
            </div>
            
            <!-- Pool Configuration -->
            <div class="section-card">
                <h2 class="section-title">⚙️ Rotation Strategy</h2>
                <p style="color: var(--gray-600); margin-bottom: 1.5rem;">
                    Choose how proxies should rotate when API is called
                </p>
                
                <div class="strategy-selector">
                    <div class="strategy-option active" data-strategy="round-robin">
                        <div class="strategy-icon">🔄</div>
                        <div class="strategy-name">Round Robin</div>
                        <div class="strategy-desc">Sequential rotation</div>
                    </div>
                    <div class="strategy-option" data-strategy="random">
                        <div class="strategy-icon">🎲</div>
                        <div class="strategy-name">Random</div>
                        <div class="strategy-desc">Random selection</div>
                    </div>
                    <div class="strategy-option" data-strategy="least-used">
                        <div class="strategy-icon">📊</div>
                        <div class="strategy-name">Least Used</div>
                        <div class="strategy-desc">Balance load evenly</div>
                    </div>
                    <div class="strategy-option" data-strategy="fastest">
                        <div class="strategy-icon">⚡</div>
                        <div class="strategy-name">Fastest</div>
                        <div class="strategy-desc">Speed priority</div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Rotation Interval (seconds)</label>
                    <input type="number" id="rotationInterval" class="form-input" value="60" min="10" max="3600">
                    <small style="color: var(--gray-500);">Minimum time before reusing same proxy</small>
                </div>
                
                <div class="form-group">
                    <label>Max Requests Per Proxy</label>
                    <input type="number" id="maxRequests" class="form-input" value="100" min="1" max="10000">
                    <small style="color: var(--gray-500);">Rotate after this many requests</small>
                </div>
            </div>
            
            <!-- Current Pool Stats -->
            <div class="section-card" id="poolStats" style="display: none;">
                <h2 class="section-title">📊 Pool Statistics</h2>
                
                <div class="proxy-pool-container">
                    <div class="pool-card">
                        <h3 style="margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                            <span>🌐</span> Pool Status
                        </h3>
                        <div class="pool-stat">
                            <span class="pool-stat-label">Active Proxies</span>
                            <span class="pool-stat-value" id="activeProxies">0</span>
                        </div>
                        <div class="pool-stat">
                            <span class="pool-stat-label">Total Requests</span>
                            <span class="pool-stat-value" id="totalRequests">0</span>
                        </div>
                        <div class="pool-stat">
                            <span class="pool-stat-label">Success Rate</span>
                            <span class="pool-stat-value" id="successRate">0%</span>
                        </div>
                    </div>
                    
                    <div class="pool-card">
                        <h3 style="margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                            <span>⚙️</span> Configuration
                        </h3>
                        <div class="pool-stat">
                            <span class="pool-stat-label">Strategy</span>
                            <span class="pool-stat-value" id="currentStrategy">Round Robin</span>
                        </div>
                        <div class="pool-stat">
                            <span class="pool-stat-label">Interval</span>
                            <span class="pool-stat-value" id="currentInterval">60s</span>
                        </div>
                        <div class="pool-stat">
                            <span class="pool-stat-label">Max Requests</span>
                            <span class="pool-stat-value" id="currentMaxReq">100</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- API Access -->
            <div class="section-card" id="apiAccess" style="display: none;">
                <h2 class="section-title">🔌 API Access</h2>
                <p style="color: var(--gray-600); margin-bottom: 1.5rem;">
                    Use these endpoints to access your rotating proxy pool
                </p>
                
                <div>
                    <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 0.75rem;">Get Next Proxy</h3>
                    <div class="api-endpoint-box">
                        <code id="apiEndpoint">GET /tools/rotating-proxy-api.php?action=get&pool_id=123</code>
                        <button class="btn btn-sm" onclick="copyToClipboard('apiEndpoint')">Copy</button>
                    </div>
                </div>
                
                <div style="margin-top: 2rem;">
                    <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 0.75rem;">Python Integration</h3>
                    <div class="code-snippet">
<pre>import requests

# Get next proxy from pool
response = requests.get('https://yoursite.com/tools/rotating-proxy-api.php?action=get&pool_id=123')
proxy_data = response.json()

if proxy_data['success']:
    proxy = proxy_data['proxy']
    
    # Use proxy for your requests
    proxies = {
        'http': f'http://{proxy["ip"]}:{proxy["port"]}',
        'https': f'https://{proxy["ip"]}:{proxy["port"]}'
    }
    
    # Make request with proxy
    r = requests.get('https://target-site.com', proxies=proxies)
    print(r.text)</pre>
                    </div>
                </div>
                
                <div style="margin-top: 2rem;">
                    <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 0.75rem;">Node.js Integration</h3>
                    <div class="code-snippet">
<pre>const axios = require('axios');
const HttpsProxyAgent = require('https-proxy-agent');

// Get next proxy
const response = await axios.get('https://yoursite.com/tools/rotating-proxy-api.php?action=get&pool_id=123');
const proxy = response.data.proxy;

// Create proxy agent
const agent = new HttpsProxyAgent(`http://${proxy.ip}:${proxy.port}`);

// Make request with proxy
const result = await axios.get('https://target-site.com', { httpAgent: agent, httpsAgent: agent });
console.log(result.data);</pre>
                    </div>
                </div>
                
                <div style="margin-top: 2rem;">
                    <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 0.75rem;">cURL Example</h3>
                    <div class="code-snippet">
<pre># Get proxy
PROXY=$(curl -s 'https://yoursite.com/tools/rotating-proxy-api.php?action=get&pool_id=123' | jq -r '.proxy | "\(.ip):\(.port)"')

# Use proxy
curl -x "$PROXY" https://target-site.com</pre>
                    </div>
                </div>
            </div>
            
            <!-- Features -->
            <div class="section-card">
                <h2 class="section-title">✨ Features</h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-top: 1.5rem;">
                    <div>
                        <h4 style="font-weight: 600; margin-bottom: 0.5rem;">🔄 Auto-Rotation</h4>
                        <p style="color: var(--gray-600); font-size: 0.875rem;">Automatically rotates proxies based on your strategy</p>
                    </div>
                    <div>
                        <h4 style="font-weight: 600; margin-bottom: 0.5rem;">💪 Health Monitoring</h4>
                        <p style="color: var(--gray-600); font-size: 0.875rem;">Removes dead proxies automatically</p>
                    </div>
                    <div>
                        <h4 style="font-weight: 600; margin-bottom: 0.5rem;">📊 Load Balancing</h4>
                        <p style="color: var(--gray-600); font-size: 0.875rem;">Distributes requests evenly across pool</p>
                    </div>
                    <div>
                        <h4 style="font-weight: 600; margin-bottom: 0.5rem;">🔐 Secure API</h4>
                        <p style="color: var(--gray-600); font-size: 0.875rem;">Authentication required for all access</p>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <script>
    // Strategy selection
    document.querySelectorAll('.strategy-option').forEach(option => {
        option.addEventListener('click', function() {
            document.querySelectorAll('.strategy-option').forEach(o => o.classList.remove('active'));
            this.classList.add('active');
        });
    });
    
    // Copy to clipboard
    function copyToClipboard(elementId) {
        const text = document.getElementById(elementId).textContent;
        navigator.clipboard.writeText(text).then(() => {
            alert('Copied to clipboard!');
        });
    }
    
    // File upload and pool creation
    document.getElementById('uploadBtn').addEventListener('click', async function() {
        const fileInput = document.getElementById('proxyFile');
        const poolName = document.getElementById('poolName').value;
        
        if (!fileInput.files.length) {
            alert('Please select a proxy file');
            return;
        }
        
        if (!poolName) {
            alert('Please enter a pool name');
            return;
        }
        
        const file = fileInput.files[0];
        const formData = new FormData();
        formData.append('file', file);
        formData.append('poolName', poolName);
        formData.append('strategy', document.querySelector('.strategy-option.active').dataset.strategy);
        formData.append('interval', document.getElementById('rotationInterval').value);
        formData.append('maxRequests', document.getElementById('maxRequests').value);
        
        // Show progress
        document.getElementById('uploadProgress').style.display = 'block';
        this.disabled = true;
        
        try {
            const response = await fetch('rotating-proxy-api.php?action=create', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                // Update UI with pool info
                document.getElementById('poolStats').style.display = 'block';
                document.getElementById('apiAccess').style.display = 'block';
                document.getElementById('activeProxies').textContent = result.activeProxies;
                document.getElementById('apiEndpoint').textContent = result.apiEndpoint;
                
                alert('Proxy pool created successfully!');
            } else {
                alert('Error: ' + result.message);
            }
        } catch (error) {
            alert('Error creating pool: ' + error.message);
        } finally {
            document.getElementById('uploadProgress').style.display = 'none';
            this.disabled = false;
        }
    });
    </script>
</body>
</html>
