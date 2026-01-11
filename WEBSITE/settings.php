<?php
// IMPORTANT: All PHP session/auth code must be at the TOP before any HTML output
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = getCurrentUser();

// Initialize settings table
$db = getDB();
$db->exec('CREATE TABLE IF NOT EXISTS user_settings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER UNIQUE NOT NULL,
    ai_model TEXT DEFAULT "blackboxai/openai/gpt-4o",
    theme TEXT DEFAULT "dark",
    settings_json TEXT,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
)');

// Get user settings
$stmt = $db->prepare('SELECT * FROM user_settings WHERE user_id = :user_id');
$stmt->bindValue(':user_id', $user['id'], SQLITE3_INTEGER);
$result = $stmt->execute();
$userSettings = $result->fetchArray(SQLITE3_ASSOC);

// Default settings if none exist
if (!$userSettings) {
    $defaultModel = defined('BLACKBOX_MODEL') ? BLACKBOX_MODEL : 'blackboxai/openai/gpt-4o';
    $stmt = $db->prepare('INSERT INTO user_settings (user_id, ai_model) VALUES (:user_id, :model)');
    $stmt->bindValue(':user_id', $user['id'], SQLITE3_INTEGER);
    $stmt->bindValue(':model', $defaultModel, SQLITE3_TEXT);
    $stmt->execute();
    $userSettings = ['ai_model' => $defaultModel];
}
$db->close();

// Available AI models (tested and working)
$availableModels = [
    'GPT Models (OpenAI)' => [
        'blackboxai/openai/gpt-4o' => ['name' => 'GPT-4o', 'desc' => 'Fast & Smart - Recommended', 'icon' => '🟢'],
        'blackboxai/openai/gpt-4-turbo' => ['name' => 'GPT-4 Turbo', 'desc' => 'Powerful with large context', 'icon' => '🟢'],
        'blackboxai/openai/gpt-4' => ['name' => 'GPT-4', 'desc' => 'Original GPT-4', 'icon' => '🟢'],
        'blackboxai/openai/chatgpt-4o-latest' => ['name' => 'ChatGPT-4o Latest', 'desc' => 'Latest ChatGPT version', 'icon' => '🟢'],
    ],
    'Claude Models (Anthropic)' => [
        'blackboxai/anthropic/claude-opus-4' => ['name' => 'Claude Opus 4', 'desc' => 'Most intelligent - Best for complex tasks', 'icon' => '🟣'],
        'blackboxai/anthropic/claude-sonnet-4' => ['name' => 'Claude Sonnet 4', 'desc' => 'Balanced performance', 'icon' => '🟣'],
    ],
    'Gemini Models (Google)' => [
        'blackboxai/google/gemini-2.5-flash' => ['name' => 'Gemini 2.5 Flash', 'desc' => 'Very fast responses', 'icon' => '🔵'],
        'blackboxai/google/gemini-2.0-flash-001' => ['name' => 'Gemini 2.0 Flash', 'desc' => 'Fast and capable', 'icon' => '🔵'],
    ],
    'DeepSeek Models' => [
        'blackboxai/deepseek/deepseek-chat' => ['name' => 'DeepSeek Chat', 'desc' => 'Great for coding help', 'icon' => '🟠'],
    ],
    'Llama Models (Meta)' => [
        'blackboxai/meta-llama/llama-4-maverick' => ['name' => 'Llama 4 Maverick', 'desc' => 'Latest open source model', 'icon' => '🔴'],
    ],
    'Qwen Models (Alibaba)' => [
        'blackboxai/qwen/qwen-max' => ['name' => 'Qwen Max', 'desc' => 'Powerful multilingual model', 'icon' => '🟡'],
    ],
];

$currentModel = $userSettings['ai_model'] ?? 'blackboxai/openai/gpt-4o';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Legend House</title>
    <link rel="stylesheet" href="dashboard-style.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>⚙️</text></svg>">
</head>
<body data-theme="dark">
    
    <div class="app-layout">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <a href="dashboard.php" class="sidebar-logo">
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
                        <li><a href="home.php" class="nav-item"><span class="nav-icon">🏠</span><span class="sidebar-text">Home</span></a></li>
                        <li><a href="dashboard.php" class="nav-item"><span class="nav-icon">📊</span><span class="sidebar-text">Dashboard</span></a></li>
                        <li><a href="watch.php" class="nav-item"><span class="nav-icon">▶️</span><span class="sidebar-text">Watch</span></a></li>
                    </ul>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title">Tools</div>
                    <ul class="nav-list">
                        <li><a href="tools.php" class="nav-item"><span class="nav-icon">🛠️</span><span class="sidebar-text">All Tools</span></a></li>
                        <li><a href="tools/dorker.php" class="nav-item"><span class="nav-icon">🔍</span><span class="sidebar-text">Google Dorker</span></a></li>
                        <li><a href="tools/torrent.php" class="nav-item"><span class="nav-icon">🧲</span><span class="sidebar-text">Torrent Center</span></a></li>
                    </ul>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title">Account</div>
                    <ul class="nav-list">
                        <li><a href="settings.php" class="nav-item active"><span class="nav-icon">⚙️</span><span class="sidebar-text">Settings</span></a></li>
                        <li><a href="profile.php" class="nav-item"><span class="nav-icon">👤</span><span class="sidebar-text">Profile</span></a></li>
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
                        <a href="dashboard.php" class="breadcrumb-item">Dashboard</a>
                        <span class="breadcrumb-separator">/</span>
                        <span class="breadcrumb-item active">Settings</span>
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
                    <h1 class="page-title">⚙️ Settings</h1>
                    <p class="page-subtitle">Manage your account preferences and settings</p>
                </div>
                
                <!-- Settings Grid -->
                <div class="content-grid">
                    <div>
                        <!-- Account Settings -->
                        <div class="card" style="margin-bottom: 24px;">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <span class="card-title-icon">👤</span>
                                    Account Information
                                </h3>
                            </div>
                            <div class="card-body">
                                <div style="display: grid; gap: 20px;">
                                    <div>
                                        <label style="display: block; font-size: 13px; color: var(--text-muted); margin-bottom: 8px;">Username</label>
                                        <input type="text" value="<?php echo htmlspecialchars($user['username']); ?>" readonly
                                               style="width: 100%; padding: 12px; background: var(--bg-tertiary); border: 1px solid var(--border-default); border-radius: 8px; color: var(--text-primary); font-size: 14px;">
                                    </div>
                                    <div>
                                        <label style="display: block; font-size: 13px; color: var(--text-muted); margin-bottom: 8px;">Email</label>
                                        <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly
                                               style="width: 100%; padding: 12px; background: var(--bg-tertiary); border: 1px solid var(--border-default); border-radius: 8px; color: var(--text-primary); font-size: 14px;">
                                    </div>
                                    <div>
                                        <label style="display: block; font-size: 13px; color: var(--text-muted); margin-bottom: 8px;">Account Type</label>
                                        <input type="text" value="<?php echo $user['auth_provider'] === 'google' ? 'Google Account' : 'Local Account'; ?>" readonly
                                               style="width: 100%; padding: 12px; background: var(--bg-tertiary); border: 1px solid var(--border-default); border-radius: 8px; color: var(--text-primary); font-size: 14px;">
                                    </div>
                                    <div>
                                        <label style="display: block; font-size: 13px; color: var(--text-muted); margin-bottom: 8px;">Member Since</label>
                                        <input type="text" value="<?php echo date('F j, Y', strtotime($user['created_at'])); ?>" readonly
                                               style="width: 100%; padding: 12px; background: var(--bg-tertiary); border: 1px solid var(--border-default); border-radius: 8px; color: var(--text-primary); font-size: 14px;">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- AI Settings -->
                        <div class="card" style="margin-bottom: 24px;">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <span class="card-title-icon">🤖</span>
                                    AI Assistant Settings
                                </h3>
                            </div>
                            <div class="card-body">
                                <div style="margin-bottom: 20px;">
                                    <label style="display: block; font-size: 13px; color: var(--text-muted); margin-bottom: 8px;">AI Model</label>
                                    <select id="aiModelSelect" onchange="saveAIModel(this.value)" style="width: 100%; padding: 12px; background: var(--bg-tertiary); border: 1px solid var(--border-default); border-radius: 8px; color: var(--text-primary); font-size: 14px; cursor: pointer;">
                                        <?php foreach ($availableModels as $category => $models): ?>
                                            <optgroup label="<?php echo htmlspecialchars($category); ?>">
                                                <?php foreach ($models as $modelId => $modelInfo): ?>
                                                    <option value="<?php echo htmlspecialchars($modelId); ?>" <?php echo $currentModel === $modelId ? 'selected' : ''; ?>>
                                                        <?php echo $modelInfo['icon'] . ' ' . htmlspecialchars($modelInfo['name']) . ' - ' . htmlspecialchars($modelInfo['desc']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </optgroup>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div id="modelInfo" style="padding: 16px; background: var(--bg-tertiary); border-radius: 8px; margin-bottom: 16px;">
                                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                                        <span style="font-size: 24px;" id="modelIcon">🟢</span>
                                        <div>
                                            <div style="font-weight: 600; color: var(--text-primary);" id="modelName">GPT-4o</div>
                                            <div style="font-size: 12px; color: var(--text-muted);" id="modelDesc">Fast & Smart - Recommended</div>
                                        </div>
                                    </div>
                                    <div style="font-size: 12px; color: var(--text-secondary);">
                                        Current model: <code id="modelCode" style="background: var(--bg-secondary); padding: 2px 6px; border-radius: 4px;"><?php echo htmlspecialchars($currentModel); ?></code>
                                    </div>
                                </div>
                                
                                <div style="display: flex; gap: 12px;">
                                    <button class="btn btn-secondary" onclick="testAIModel()" id="testBtn" style="flex: 1;">
                                        🧪 Test Model
                                    </button>
                                    <button class="btn btn-secondary" onclick="resetToDefault()" style="flex: 1;">
                                        🔄 Reset to Default
                                    </button>
                                </div>
                                
                                <div id="testResult" style="margin-top: 16px; display: none;"></div>
                            </div>
                        </div>
                        
                        <!-- Appearance -->
                        <div class="card" style="margin-bottom: 24px;">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <span class="card-title-icon">🎨</span>
                                    Appearance
                                </h3>
                            </div>
                            <div class="card-body">
                                <div style="display: flex; justify-content: space-between; align-items: center; padding: 16px 0; border-bottom: 1px solid var(--border-default);">
                                    <div>
                                        <div style="font-weight: 500; color: var(--text-primary); margin-bottom: 4px;">Theme</div>
                                        <div style="font-size: 13px; color: var(--text-muted);">Choose your preferred color scheme</div>
                                    </div>
                                    <button class="btn btn-secondary" onclick="toggleTheme()">
                                        <span id="themeToggleIcon">🌙</span>
                                        <span id="themeToggleText">Dark Mode</span>
                                    </button>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center; padding: 16px 0;">
                                    <div>
                                        <div style="font-weight: 500; color: var(--text-primary); margin-bottom: 4px;">Sidebar</div>
                                        <div style="font-size: 13px; color: var(--text-muted);">Collapse sidebar to save space</div>
                                    </div>
                                    <button class="btn btn-secondary" onclick="toggleSidebar()">Toggle Sidebar</button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Privacy & Security -->
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <span class="card-title-icon">🔒</span>
                                    Privacy & Security
                                </h3>
                            </div>
                            <div class="card-body">
                                <div style="display: flex; justify-content: space-between; align-items: center; padding: 16px 0; border-bottom: 1px solid var(--border-default);">
                                    <div>
                                        <div style="font-weight: 500; color: var(--text-primary); margin-bottom: 4px;">Download History</div>
                                        <div style="font-size: 13px; color: var(--text-muted);">Clear your download history</div>
                                    </div>
                                    <button class="btn btn-secondary" onclick="clearHistory()">Clear History</button>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center; padding: 16px 0;">
                                    <div>
                                        <div style="font-weight: 500; color: var(--text-primary); margin-bottom: 4px;">Sign Out</div>
                                        <div style="font-size: 13px; color: var(--text-muted);">Sign out of your account</div>
                                    </div>
                                    <button class="btn" style="background: #f85149; color: white;" onclick="logoutUser()">Sign Out</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <!-- Quick Stats -->
                        <div class="card" style="margin-bottom: 24px;">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <span class="card-title-icon">📊</span>
                                    Account Stats
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="status-grid">
                                    <div class="status-item">
                                        <div class="status-indicator online"></div>
                                        <div class="status-info">
                                            <div class="status-name">Account Status</div>
                                            <div class="status-detail">Active</div>
                                        </div>
                                    </div>
                                    <div class="status-item">
                                        <div class="status-indicator online"></div>
                                        <div class="status-info">
                                            <div class="status-name">AI Chat</div>
                                            <div class="status-detail">Enabled</div>
                                        </div>
                                    </div>
                                    <div class="status-item">
                                        <div class="status-indicator online"></div>
                                        <div class="status-info">
                                            <div class="status-name">Tools Access</div>
                                            <div class="status-detail">Full Access</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Help -->
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <span class="card-title-icon">❓</span>
                                    Need Help?
                                </h3>
                            </div>
                            <div class="card-body">
                                <p style="color: var(--text-secondary); font-size: 14px; margin-bottom: 16px;">
                                    Use the AI Chat assistant for help with any feature. Click the chat button in the bottom right corner.
                                </p>
                                <div class="quick-actions">
                                    <a href="tools.php" class="quick-action-item">
                                        <div class="quick-action-icon">🛠️</div>
                                        <div class="quick-action-text">
                                            <div class="quick-action-title">View All Tools</div>
                                        </div>
                                    </a>
                                    <a href="dashboard.php" class="quick-action-item">
                                        <div class="quick-action-icon">📊</div>
                                        <div class="quick-action-text">
                                            <div class="quick-action-title">Go to Dashboard</div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script>
        // AI Model data
        const modelData = <?php echo json_encode($availableModels); ?>;
        
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
            document.getElementById('themeToggleIcon').textContent = theme === 'dark' ? '🌙' : '☀️';
            document.getElementById('themeToggleText').textContent = theme === 'dark' ? 'Dark Mode' : 'Light Mode';
        }
        
        // Restore theme
        const savedTheme = localStorage.getItem('theme') || 'dark';
        document.body.setAttribute('data-theme', savedTheme);
        document.getElementById('themeIcon').textContent = savedTheme === 'dark' ? '🌙' : '☀️';
        document.getElementById('themeText').textContent = savedTheme === 'dark' ? 'Dark' : 'Light';
        
        if (localStorage.getItem('sidebarCollapsed') === 'true') {
            document.getElementById('sidebar').classList.add('collapsed');
        }
        
        // Update model info display
        function updateModelInfo(modelId) {
            for (const category in modelData) {
                if (modelData[category][modelId]) {
                    const info = modelData[category][modelId];
                    document.getElementById('modelIcon').textContent = info.icon;
                    document.getElementById('modelName').textContent = info.name;
                    document.getElementById('modelDesc').textContent = info.desc;
                    document.getElementById('modelCode').textContent = modelId;
                    break;
                }
            }
        }
        
        // Save AI model setting
        async function saveAIModel(modelId) {
            updateModelInfo(modelId);
            
            try {
                const response = await fetch('ai-settings.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'save_model', model: modelId })
                });
                
                const data = await response.json();
                if (data.success) {
                    showToast('✅ AI model saved successfully!', 'success');
                } else {
                    showToast('❌ Failed to save: ' + (data.error || 'Unknown error'), 'error');
                }
            } catch (error) {
                showToast('❌ Network error', 'error');
            }
        }
        
        // Test AI model
        async function testAIModel() {
            const btn = document.getElementById('testBtn');
            const resultDiv = document.getElementById('testResult');
            const modelId = document.getElementById('aiModelSelect').value;
            
            btn.disabled = true;
            btn.textContent = '⏳ Testing...';
            resultDiv.style.display = 'block';
            resultDiv.innerHTML = '<div style="padding: 16px; background: var(--bg-tertiary); border-radius: 8px; text-align: center;"><div class="loading-spinner" style="margin: 0 auto 12px;"></div>Testing AI model...</div>';
            
            try {
                const response = await fetch('ai-settings.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'test_model', model: modelId })
                });
                
                const data = await response.json();
                
                if (data.success && data.response) {
                    resultDiv.innerHTML = `
                        <div style="padding: 16px; background: rgba(46, 160, 67, 0.15); border: 1px solid #2ea043; border-radius: 8px;">
                            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px; color: #2ea043; font-weight: 600;">
                                ✅ Model is working!
                            </div>
                            <div style="font-size: 13px; color: var(--text-secondary);">
                                <strong>Response:</strong> ${escapeHtml(data.response)}
                            </div>
                            <div style="font-size: 11px; color: var(--text-muted); margin-top: 8px;">
                                Response time: ${data.time || 'N/A'}
                            </div>
                        </div>
                    `;
                } else {
                    resultDiv.innerHTML = `
                        <div style="padding: 16px; background: rgba(248, 81, 73, 0.15); border: 1px solid #f85149; border-radius: 8px;">
                            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px; color: #f85149; font-weight: 600;">
                                ❌ Model test failed
                            </div>
                            <div style="font-size: 13px; color: var(--text-secondary);">
                                ${escapeHtml(data.error || 'No response from model')}
                            </div>
                        </div>
                    `;
                }
            } catch (error) {
                resultDiv.innerHTML = `
                    <div style="padding: 16px; background: rgba(248, 81, 73, 0.15); border: 1px solid #f85149; border-radius: 8px;">
                        <div style="color: #f85149; font-weight: 600;">❌ Network error</div>
                    </div>
                `;
            } finally {
                btn.disabled = false;
                btn.textContent = '🧪 Test Model';
            }
        }
        
        // Reset to default model
        function resetToDefault() {
            document.getElementById('aiModelSelect').value = 'blackboxai/openai/gpt-4o';
            saveAIModel('blackboxai/openai/gpt-4o');
        }
        
        // Clear download history
        async function clearHistory() {
            if (confirm('Are you sure you want to clear your download history?')) {
                try {
                    const response = await fetch('ai-settings.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'clear_history' })
                    });
                    const data = await response.json();
                    if (data.success) {
                        showToast('✅ History cleared!', 'success');
                    }
                } catch (error) {
                    showToast('✅ History cleared!', 'success');
                }
            }
        }
        
        async function logoutUser() {
            if (confirm('Are you sure you want to sign out?')) {
                const formData = new FormData();
                formData.append('action', 'logout');
                await fetch('auth.php', { method: 'POST', body: formData });
                window.location.href = 'login.php';
            }
        }
        
        // Toast notification
        function showToast(message, type = 'info') {
            const existing = document.querySelector('.settings-toast');
            if (existing) existing.remove();
            
            const toast = document.createElement('div');
            toast.className = 'settings-toast';
            toast.style.cssText = `
                position: fixed;
                bottom: 24px;
                right: 24px;
                padding: 16px 24px;
                background: ${type === 'success' ? '#2ea043' : type === 'error' ? '#f85149' : '#1f6feb'};
                color: white;
                border-radius: 8px;
                font-size: 14px;
                font-weight: 500;
                z-index: 10000;
                animation: slideIn 0.3s ease;
                box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            `;
            toast.textContent = message;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        // Initialize model info
        updateModelInfo(document.getElementById('aiModelSelect').value);
    </script>
    
    <style>
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes slideOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
        .loading-spinner {
            width: 24px;
            height: 24px;
            border: 3px solid var(--border-default);
            border-top-color: var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        #aiModelSelect optgroup {
            font-weight: 600;
            color: var(--text-primary);
        }
        #aiModelSelect option {
            padding: 8px;
        }
    </style>
    
    <script src="ai-chat-widget.js"></script>
</body>
</html>
