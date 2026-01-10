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
    <title>Advanced Google Dorker Pro - Legend House</title>
    <link rel="stylesheet" href="../dashboard-style.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🔍</text></svg>">
    
    <style>
        /* Advanced Dorker Pro Styles */
        .dorker-hero {
            background: linear-gradient(135deg, var(--bg-secondary) 0%, var(--bg-tertiary) 100%);
            border: 1px solid var(--border-default);
            border-radius: var(--radius-xl);
            padding: 32px;
            margin-bottom: 24px;
            position: relative;
            overflow: hidden;
        }
        
        .dorker-hero::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, var(--accent-muted) 0%, transparent 70%);
            opacity: 0.5;
        }
        
        .dorker-hero h1 {
            font-size: 32px;
            font-weight: 800;
            color: var(--text-primary);
            margin-bottom: 8px;
            position: relative;
        }
        
        .dorker-hero p {
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
            grid-template-columns: repeat(5, 1fr);
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
        
        .dorker-layout {
            display: grid;
            grid-template-columns: 380px 1fr;
            gap: 24px;
            min-height: calc(100vh - 350px);
        }
        
        .dorker-sidebar {
            display: flex;
            flex-direction: column;
            gap: 16px;
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
            padding: 16px 20px;
        }
        
        .form-group {
            margin-bottom: 16px;
        }
        
        .form-group:last-child {
            margin-bottom: 0;
        }
        
        .form-label {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 13px;
            font-weight: 500;
            color: var(--text-secondary);
            margin-bottom: 8px;
        }
        
        .form-label-hint {
            font-size: 11px;
            color: var(--text-muted);
            font-weight: 400;
        }
        
        .form-input, .form-textarea, .form-select {
            width: 100%;
            padding: 12px 16px;
            background: var(--bg-tertiary);
            border: 1px solid var(--border-default);
            border-radius: var(--radius-md);
            font-size: 14px;
            color: var(--text-primary);
            transition: all 0.2s;
            font-family: inherit;
        }
        
        .form-textarea {
            min-height: 120px;
            resize: vertical;
            font-family: var(--font-mono);
            font-size: 13px;
            line-height: 1.6;
        }
        
        .form-input:focus, .form-textarea:focus, .form-select:focus {
            outline: none;
            border-color: var(--accent-primary);
            background: var(--bg-primary);
        }
        
        .form-input::placeholder, .form-textarea::placeholder {
            color: var(--text-muted);
        }
        
        /* Category Grid - Advanced */
        .category-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 12px;
            overflow-x: auto;
            padding-bottom: 4px;
        }
        
        .category-tab {
            padding: 8px 16px;
            background: var(--bg-tertiary);
            border: 1px solid var(--border-default);
            border-radius: var(--radius-sm);
            font-size: 12px;
            font-weight: 500;
            color: var(--text-secondary);
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.2s;
        }
        
        .category-tab:hover {
            background: var(--accent-muted);
            border-color: var(--accent-primary);
            color: var(--text-primary);
        }
        
        .category-tab.active {
            background: var(--accent-primary);
            color: var(--bg-primary);
            border-color: var(--accent-primary);
        }
        
        .category-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
        }
        
        .category-btn {
            padding: 12px;
            background: var(--bg-tertiary);
            border: 1px solid var(--border-default);
            border-radius: var(--radius-md);
            font-size: 12px;
            font-weight: 500;
            color: var(--text-secondary);
            cursor: pointer;
            transition: all 0.2s;
            text-align: left;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        
        .category-btn:hover {
            background: var(--accent-muted);
            border-color: var(--accent-primary);
            color: var(--text-primary);
        }
        
        .category-btn.active {
            background: var(--accent-primary);
            color: var(--bg-primary);
            border-color: var(--accent-primary);
        }
        
        .category-btn-title {
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .category-btn-count {
            font-size: 10px;
            opacity: 0.7;
        }
        
        /* Buttons */
        .btn-group {
            display: flex;
            gap: 8px;
        }
        
        .btn {
            padding: 12px 20px;
            border-radius: var(--radius-md);
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: none;
        }
        
        .btn-primary {
            background: var(--accent-primary);
            color: var(--bg-primary);
            flex: 1;
        }
        
        .btn-primary:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }
        
        .btn-primary:disabled {
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
        
        .btn-ai {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }
        
        .btn-ai:hover {
            opacity: 0.9;
        }
        
        /* Operators Help - Advanced */
        .operators-grid {
            display: grid;
            gap: 8px;
        }
        
        .operator-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            background: var(--bg-tertiary);
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .operator-item:hover {
            background: var(--accent-muted);
        }
        
        .operator-code {
            font-family: var(--font-mono);
            font-size: 12px;
            font-weight: 600;
            color: var(--info);
            min-width: 80px;
        }
        
        .operator-desc {
            font-size: 12px;
            color: var(--text-secondary);
            flex: 1;
        }
        
        /* Results Panel */
        .results-panel {
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        
        .results-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border-default);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
        }
        
        .results-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .results-count {
            background: var(--accent-muted);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .results-filters {
            display: flex;
            gap: 8px;
        }
        
        .filter-select {
            padding: 6px 12px;
            background: var(--bg-tertiary);
            border: 1px solid var(--border-default);
            border-radius: var(--radius-sm);
            font-size: 12px;
            color: var(--text-primary);
        }
        
        .results-actions {
            display: flex;
            gap: 8px;
        }
        
        .action-btn {
            padding: 8px 14px;
            background: var(--bg-tertiary);
            border: 1px solid var(--border-default);
            border-radius: var(--radius-sm);
            font-size: 12px;
            color: var(--text-secondary);
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .action-btn:hover {
            background: var(--accent-muted);
            border-color: var(--accent-primary);
            color: var(--text-primary);
        }
        
        .results-body {
            flex: 1;
            overflow-y: auto;
            padding: 16px;
        }
        
        /* Result Items - Enhanced */
        .result-item {
            padding: 16px;
            background: var(--bg-tertiary);
            border: 1px solid var(--border-default);
            border-radius: var(--radius-md);
            margin-bottom: 12px;
            transition: all 0.2s;
            position: relative;
        }
        
        .result-item:hover {
            border-color: var(--accent-primary);
            transform: translateX(4px);
        }
        
        .result-item:last-child {
            margin-bottom: 0;
        }
        
        .result-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 8px;
        }
        
        .result-number {
            background: var(--accent-muted);
            padding: 4px 10px;
            border-radius: var(--radius-sm);
            font-size: 11px;
            font-weight: 600;
            color: var(--text-muted);
        }
        
        .result-title {
            font-size: 15px;
            font-weight: 600;
            color: var(--info);
            text-decoration: none;
            display: block;
            margin-bottom: 4px;
            line-height: 1.4;
        }
        
        .result-title:hover {
            text-decoration: underline;
        }
        
        .result-url {
            font-size: 12px;
            color: var(--success);
            margin-bottom: 8px;
            word-break: break-all;
            font-family: var(--font-mono);
        }
        
        .result-desc {
            font-size: 13px;
            color: var(--text-secondary);
            line-height: 1.5;
            margin-bottom: 12px;
        }
        
        .result-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-bottom: 12px;
        }
        
        .result-tag {
            padding: 3px 8px;
            background: var(--bg-secondary);
            border-radius: var(--radius-sm);
            font-size: 10px;
            font-weight: 500;
            color: var(--text-muted);
        }
        
        .result-tag.admin { color: #f85149; background: rgba(248, 81, 73, 0.1); }
        .result-tag.config { color: #d29922; background: rgba(210, 153, 34, 0.1); }
        .result-tag.api { color: #58a6ff; background: rgba(88, 166, 255, 0.1); }
        .result-tag.database { color: #a371f7; background: rgba(163, 113, 247, 0.1); }
        .result-tag.file { color: #3fb950; background: rgba(63, 185, 80, 0.1); }
        
        .result-actions {
            display: flex;
            gap: 8px;
        }
        
        .result-action {
            padding: 6px 12px;
            background: var(--bg-secondary);
            border: 1px solid var(--border-default);
            border-radius: var(--radius-sm);
            font-size: 11px;
            color: var(--text-secondary);
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        
        .result-action:hover {
            background: var(--accent-muted);
            color: var(--text-primary);
        }
        
        /* Empty & Loading States */
        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 60px;
            text-align: center;
            color: var(--text-muted);
        }
        
        .empty-state-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }
        
        .empty-state-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 8px;
        }
        
        .empty-state-desc {
            font-size: 14px;
            max-width: 300px;
            line-height: 1.5;
        }
        
        .loading-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 60px;
        }
        
        .spinner {
            width: 48px;
            height: 48px;
            border: 3px solid var(--border-default);
            border-top-color: var(--accent-primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 20px;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .loading-text {
            font-size: 14px;
            color: var(--text-secondary);
        }
        
        /* Query History */
        .history-list {
            max-height: 200px;
            overflow-y: auto;
        }
        
        .history-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            background: var(--bg-tertiary);
            border-radius: var(--radius-sm);
            margin-bottom: 8px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .history-item:hover {
            background: var(--accent-muted);
        }
        
        .history-item:last-child {
            margin-bottom: 0;
        }
        
        .history-query {
            flex: 1;
            font-size: 12px;
            color: var(--text-primary);
            font-family: var(--font-mono);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .history-time {
            font-size: 10px;
            color: var(--text-muted);
        }
        
        /* Bulk Mode */
        .bulk-container {
            display: none;
        }
        
        .bulk-container.active {
            display: block;
        }
        
        .bulk-progress {
            margin-top: 16px;
            padding: 16px;
            background: var(--bg-tertiary);
            border-radius: var(--radius-md);
        }
        
        .bulk-progress-bar {
            height: 8px;
            background: var(--border-default);
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 8px;
        }
        
        .bulk-progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--info), var(--success));
            width: 0%;
            transition: width 0.3s;
        }
        
        .bulk-progress-text {
            font-size: 12px;
            color: var(--text-muted);
            text-align: center;
        }
        
        /* AI Generator */
        .ai-suggestions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 12px;
        }
        
        .ai-suggestion {
            padding: 8px 14px;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
            border: 1px solid rgba(102, 126, 234, 0.3);
            border-radius: var(--radius-md);
            font-size: 12px;
            color: var(--text-primary);
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .ai-suggestion:hover {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.2), rgba(118, 75, 162, 0.2));
            border-color: rgba(102, 126, 234, 0.5);
        }
        
        /* Responsive */
        @media (max-width: 1200px) {
            .dorker-layout {
                grid-template-columns: 1fr;
            }
            
            .stats-row {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .stats-row {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .category-grid {
                grid-template-columns: 1fr;
            }
            
            .results-header {
                flex-direction: column;
                align-items: stretch;
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
                        <li><a href="dorker.php" class="nav-item active"><span class="nav-icon">🔍</span><span class="sidebar-text">Google Dorker</span><span class="nav-badge success">PRO</span></a></li>
                        <li><a href="torrent.php" class="nav-item"><span class="nav-icon">🧲</span><span class="sidebar-text">Torrent Center</span></a></li>
                        <li><a href="proxy-scraper.php" class="nav-item"><span class="nav-icon">🌐</span><span class="sidebar-text">Proxy Scraper</span></a></li>
                        <li><a href="shortener.php" class="nav-item"><span class="nav-icon">🔗</span><span class="sidebar-text">Link Shortener</span></a></li>
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
                        <span class="breadcrumb-item active">Google Dorker Pro</span>
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
                <!-- Hero Section -->
                <div class="dorker-hero">
                    <h1>
                        🔍 Google Dorker
                        <span class="pro-badge">⚡ PRO</span>
                    </h1>
                    <p>Advanced search reconnaissance with 150+ operators, AI-powered queries, bulk processing & real-time analysis</p>
                </div>
                
                <!-- Stats Row -->
                <div class="stats-row">
                    <div class="stat-box">
                        <div class="stat-box-icon">🔍</div>
                        <div class="stat-box-value" id="totalDorks">0</div>
                        <div class="stat-box-label">Dorks Run</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-box-icon">🎯</div>
                        <div class="stat-box-value" id="totalResults">0</div>
                        <div class="stat-box-label">Results Found</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-box-icon">💾</div>
                        <div class="stat-box-value" id="savedQueries">0</div>
                        <div class="stat-box-label">Saved Queries</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-box-icon">📊</div>
                        <div class="stat-box-value" id="successRate">0%</div>
                        <div class="stat-box-label">Success Rate</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-box-icon">⚡</div>
                        <div class="stat-box-value">150+</div>
                        <div class="stat-box-label">Operators</div>
                    </div>
                </div>
                
                <!-- Main Layout -->
                <div class="dorker-layout">
                    <!-- Left Sidebar -->
                    <div class="dorker-sidebar">
                        <!-- Query Builder -->
                        <div class="panel">
                            <div class="panel-header">
                                <div class="panel-title">🎯 Query Builder</div>
                                <div class="category-tabs">
                                    <button class="category-tab active" data-mode="single">Single</button>
                                    <button class="category-tab" data-mode="bulk">Bulk</button>
                                </div>
                            </div>
                            <div class="panel-body">
                                <!-- Single Mode -->
                                <div id="singleMode">
                                    <div class="form-group">
                                        <label class="form-label">
                                            Dork Query
                                            <span class="form-label-hint">Use operators below</span>
                                        </label>
                                        <textarea id="dorkQuery" class="form-textarea" placeholder='site:example.com intitle:"admin" filetype:php

Examples:
• site:gov.* filetype:pdf "confidential"
• inurl:wp-admin intitle:login
• "index of" inurl:/backup/'></textarea>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="form-label">Target Domain (Optional)</label>
                                        <input type="text" id="targetDomain" class="form-input" placeholder="e.g., example.com, *.gov, *.edu">
                                    </div>
                                    
                                    <div class="btn-group">
                                        <button class="btn btn-primary" onclick="startDorking()" id="dorkBtn">
                                            🚀 Start Dorking
                                        </button>
                                        <button class="btn btn-ai" onclick="generateAI()" title="AI Generate">
                                            🤖
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Bulk Mode -->
                                <div id="bulkMode" class="bulk-container">
                                    <div class="form-group">
                                        <label class="form-label">
                                            Bulk Dork Queries
                                            <span class="form-label-hint">One per line</span>
                                        </label>
                                        <textarea id="bulkQueries" class="form-textarea" placeholder='site:example.com filetype:sql
inurl:admin intitle:login
"index of" inurl:/backup/
filetype:env DB_PASSWORD'></textarea>
                                    </div>
                                    
                                    <div class="btn-group">
                                        <button class="btn btn-primary" onclick="startBulkDorking()" id="bulkDorkBtn">
                                            🚀 Run Bulk (0 queries)
                                        </button>
                                    </div>
                                    
                                    <div class="bulk-progress" id="bulkProgress" style="display: none;">
                                        <div class="bulk-progress-bar">
                                            <div class="bulk-progress-fill" id="bulkProgressFill"></div>
                                        </div>
                                        <div class="bulk-progress-text" id="bulkProgressText">Processing...</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Categories -->
                        <div class="panel">
                            <div class="panel-header">
                                <div class="panel-title">📁 Quick Categories</div>
                            </div>
                            <div class="panel-body">
                                <div class="category-grid">
                                    <button class="category-btn" data-category="admin">
                                        <span class="category-btn-title">🔐 Admin Panels</span>
                                        <span class="category-btn-count">12 dorks</span>
                                    </button>
                                    <button class="category-btn" data-category="files">
                                        <span class="category-btn-title">📁 Sensitive Files</span>
                                        <span class="category-btn-count">15 dorks</span>
                                    </button>
                                    <button class="category-btn" data-category="database">
                                        <span class="category-btn-title">💾 Databases</span>
                                        <span class="category-btn-count">10 dorks</span>
                                    </button>
                                    <button class="category-btn" data-category="config">
                                        <span class="category-btn-title">⚙️ Config Files</span>
                                        <span class="category-btn-count">14 dorks</span>
                                    </button>
                                    <button class="category-btn" data-category="api">
                                        <span class="category-btn-title">🔌 API & Keys</span>
                                        <span class="category-btn-count">11 dorks</span>
                                    </button>
                                    <button class="category-btn" data-category="vulns">
                                        <span class="category-btn-title">⚠️ Vulnerabilities</span>
                                        <span class="category-btn-count">16 dorks</span>
                                    </button>
                                    <button class="category-btn" data-category="cloud">
                                        <span class="category-btn-title">☁️ Cloud Storage</span>
                                        <span class="category-btn-count">9 dorks</span>
                                    </button>
                                    <button class="category-btn" data-category="git">
                                        <span class="category-btn-title">🔧 Git & SVN</span>
                                        <span class="category-btn-count">8 dorks</span>
                                    </button>
                                </div>
                                
                                <div class="ai-suggestions" id="aiSuggestions" style="display: none;">
                                    <!-- AI suggestions will appear here -->
                                </div>
                            </div>
                        </div>
                        
                        <!-- Operators Reference -->
                        <div class="panel">
                            <div class="panel-header">
                                <div class="panel-title">📚 Operators</div>
                            </div>
                            <div class="panel-body">
                                <div class="operators-grid">
                                    <div class="operator-item" onclick="insertOperator('site:')">
                                        <span class="operator-code">site:</span>
                                        <span class="operator-desc">Specific domain/site</span>
                                    </div>
                                    <div class="operator-item" onclick="insertOperator('intitle:')">
                                        <span class="operator-code">intitle:</span>
                                        <span class="operator-desc">Search in page title</span>
                                    </div>
                                    <div class="operator-item" onclick="insertOperator('inurl:')">
                                        <span class="operator-code">inurl:</span>
                                        <span class="operator-desc">Search in URL</span>
                                    </div>
                                    <div class="operator-item" onclick="insertOperator('filetype:')">
                                        <span class="operator-code">filetype:</span>
                                        <span class="operator-desc">File extension filter</span>
                                    </div>
                                    <div class="operator-item" onclick="insertOperator('intext:')">
                                        <span class="operator-code">intext:</span>
                                        <span class="operator-desc">Search in page body</span>
                                    </div>
                                    <div class="operator-item" onclick="insertOperator('ext:')">
                                        <span class="operator-code">ext:</span>
                                        <span class="operator-desc">File extension (alt)</span>
                                    </div>
                                    <div class="operator-item" onclick="insertOperator('cache:')">
                                        <span class="operator-code">cache:</span>
                                        <span class="operator-desc">Cached version</span>
                                    </div>
                                    <div class="operator-item" onclick="insertOperator('related:')">
                                        <span class="operator-code">related:</span>
                                        <span class="operator-desc">Similar sites</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Results Panel -->
                    <div class="panel results-panel">
                        <div class="results-header">
                            <div class="results-title">
                                📊 Results
                                <span class="results-count" id="resultsCount">0</span>
                            </div>
                            <div class="results-filters">
                                <select class="filter-select" id="filterType" onchange="filterResults()">
                                    <option value="all">All Types</option>
                                    <option value="admin">Admin Pages</option>
                                    <option value="config">Config Files</option>
                                    <option value="api">API/Keys</option>
                                    <option value="database">Databases</option>
                                    <option value="file">Files</option>
                                </select>
                            </div>
                            <div class="results-actions">
                                <button class="action-btn" onclick="exportResults('txt')">📄 TXT</button>
                                <button class="action-btn" onclick="exportResults('csv')">📊 CSV</button>
                                <button class="action-btn" onclick="exportResults('json')">📋 JSON</button>
                                <button class="action-btn" onclick="copyAllUrls()">📋 Copy All</button>
                            </div>
                        </div>
                        
                        <div class="results-body" id="resultsContainer">
                            <div class="empty-state">
                                <div class="empty-state-icon">🔍</div>
                                <div class="empty-state-title">Ready to Dork</div>
                                <div class="empty-state-desc">Enter a dork query or select a category to discover hidden content across the web</div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script>
        // Advanced Dorker Pro v2.0
        
        // Enhanced Category Dorks
        const categoryDorks = {
            admin: `intitle:"admin" OR intitle:"administrator" OR intitle:"login" OR intitle:"dashboard"
inurl:admin OR inurl:administrator OR inurl:cpanel OR inurl:phpmyadmin
inurl:wp-admin OR inurl:wp-login OR inurl:administrator/index
"Welcome to phpMyAdmin" OR "phpMyAdmin" intitle:phpMyAdmin
inurl:"/admin/login" OR inurl:"/admin/index" OR inurl:"/admin/dashboard"
intitle:"Webmin" OR intitle:"cPanel" OR intitle:"Plesk"`,
            
            files: `filetype:env OR filetype:sql OR filetype:log OR filetype:bak OR filetype:old
filetype:conf OR filetype:cfg OR filetype:ini "password" OR "passwd" OR "pwd"
"index of" /backup/ OR /backups/ OR /dump/ OR /sql/
filetype:txt "username" "password" OR "credentials"
filetype:xls OR filetype:xlsx "password" OR "passwords" OR "credentials"
ext:log "password" OR "error" OR "warning"`,
            
            database: `filetype:sql "INSERT INTO" OR "CREATE TABLE" OR "DROP TABLE"
filetype:mdb OR filetype:accdb OR filetype:db
"phpMyAdmin" inurl:db OR inurl:database
filetype:sql intext:password OR intext:passwd
"MySQL dump" OR "PostgreSQL database dump"
inurl:db_backup OR inurl:database_backup filetype:sql`,
            
            config: `filetype:env "DB_PASSWORD" OR "DATABASE_URL" OR "SECRET_KEY"
filetype:yml OR filetype:yaml "password:" OR "secret:"
filetype:json "api_key" OR "apikey" OR "secret"
filetype:xml "password" OR "credentials"
filetype:conf "ServerRoot" OR "DocumentRoot"
"config.php" OR "settings.php" OR "database.php" intext:password`,
            
            api: `filetype:json "api_key" OR "apiKey" OR "api_secret"
intext:"PRIVATE KEY" filetype:pem OR filetype:key
"Authorization: Bearer" OR "X-API-Key"
filetype:env "API_KEY" OR "SECRET_KEY" OR "ACCESS_TOKEN"
inurl:api "swagger" OR "graphql" OR "rest"
"api.github.com" OR "api.stripe.com" OR "api.twilio.com"`,
            
            vulns: `intext:"sql syntax near" OR intext:"mysql_fetch" OR intext:"Warning: mysql"
intext:"Warning: pg_" OR intext:"pg_query" OR intext:"pg_connect"
"PHP Parse error" OR "PHP Warning" OR "PHP Fatal error"
"Index of /" intitle:index.of
inurl:".git" OR inurl:".svn" OR inurl:".env"
"Directory listing for" OR "Parent Directory"`,
            
            cloud: `site:s3.amazonaws.com OR site:blob.core.windows.net
site:storage.googleapis.com OR site:drive.google.com
"bucket" site:s3.amazonaws.com
inurl:sharepoint.com OR inurl:onedrive.live.com
site:digitaloceanspaces.com OR site:backblazeb2.com
"Azure Blob" OR "Amazon S3" filetype:xml`,
            
            git: `inurl:.git/config OR inurl:.gitignore
"Index of /.git" OR "Index of /.svn"
filetype:git OR filetype:gitignore "password"
inurl:gitlab OR inurl:bitbucket "private"
".git/HEAD" OR ".git/logs"
intext:"Repository" inurl:git filetype:conf`
        };
        
        let currentResults = [];
        let allResults = [];
        let currentMode = 'single';
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            loadStats();
            setupEventListeners();
        });
        
        function setupEventListeners() {
            // Mode tabs
            document.querySelectorAll('.category-tab').forEach(tab => {
                tab.addEventListener('click', function() {
                    document.querySelectorAll('.category-tab').forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                    currentMode = this.dataset.mode;
                    
                    document.getElementById('singleMode').style.display = currentMode === 'single' ? 'block' : 'none';
                    document.getElementById('bulkMode').classList.toggle('active', currentMode === 'bulk');
                });
            });
            
            // Category buttons
            document.querySelectorAll('.category-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const category = this.dataset.category;
                    const dorks = categoryDorks[category];
                    
                    if (currentMode === 'single') {
                        document.getElementById('dorkQuery').value = dorks.split('\n')[0];
                    } else {
                        document.getElementById('bulkQueries').value = dorks;
                        updateBulkCount();
                    }
                    
                    document.querySelectorAll('.category-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                });
            });
            
            // Bulk query count
            document.getElementById('bulkQueries')?.addEventListener('input', updateBulkCount);
        }
        
        function updateBulkCount() {
            const queries = document.getElementById('bulkQueries').value.trim().split('\n').filter(q => q.trim());
            document.getElementById('bulkDorkBtn').textContent = `🚀 Run Bulk (${queries.length} queries)`;
        }
        
        function insertOperator(operator) {
            const textarea = document.getElementById('dorkQuery');
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const text = textarea.value;
            
            textarea.value = text.substring(0, start) + operator + text.substring(end);
            textarea.focus();
            textarea.selectionStart = textarea.selectionEnd = start + operator.length;
        }
        
        // Start dorking
        async function startDorking() {
            let query = document.getElementById('dorkQuery').value.trim();
            const targetDomain = document.getElementById('targetDomain').value.trim();
            
            if (!query) {
                alert('Please enter a dork query');
                return;
            }
            
            // Add target domain if specified
            if (targetDomain && !query.includes('site:')) {
                query = `site:${targetDomain} ${query}`;
            }
            
            const btn = document.getElementById('dorkBtn');
            btn.disabled = true;
            btn.innerHTML = '⏳ Searching...';
            
            showLoading('Executing advanced dorking query...');
            
            try {
                const response = await fetch('dorker-api.php?action=dork', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ query })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    currentResults = data.results;
                    allResults = data.results;
                    displayResults(data.results);
                    updateStats(data.stats);
                    
                    // Show AI suggestions based on results
                    if (data.query_info?.suggestions) {
                        showAISuggestions(data.query_info.suggestions);
                    }
                } else {
                    showError(data.error || 'Search failed. Try a different query.');
                }
            } catch (error) {
                showError('Network error: ' + error.message);
            } finally {
                btn.disabled = false;
                btn.innerHTML = '🚀 Start Dorking';
            }
        }
        
        // Bulk dorking
        async function startBulkDorking() {
            const queries = document.getElementById('bulkQueries').value.trim().split('\n').filter(q => q.trim());
            
            if (queries.length === 0) {
                alert('Please enter at least one dork query');
                return;
            }
            
            const btn = document.getElementById('bulkDorkBtn');
            btn.disabled = true;
            
            const progressDiv = document.getElementById('bulkProgress');
            const progressFill = document.getElementById('bulkProgressFill');
            const progressText = document.getElementById('bulkProgressText');
            
            progressDiv.style.display = 'block';
            allResults = [];
            
            for (let i = 0; i < queries.length; i++) {
                const query = queries[i].trim();
                if (!query) continue;
                
                const percent = ((i + 1) / queries.length) * 100;
                progressFill.style.width = percent + '%';
                progressText.textContent = `Processing ${i + 1}/${queries.length}: ${query.substring(0, 50)}...`;
                
                try {
                    const response = await fetch('dorker-api.php?action=dork', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ query })
                    });
                    
                    const data = await response.json();
                    
                    if (data.success && data.results) {
                        allResults = allResults.concat(data.results);
                    }
                } catch (error) {
                    console.error('Query failed:', query, error);
                }
                
                // Small delay between queries
                await new Promise(r => setTimeout(r, 500));
            }
            
            progressText.textContent = `Completed! Found ${allResults.length} total results`;
            
            currentResults = allResults;
            displayResults(allResults);
            loadStats();
            
            setTimeout(() => {
                progressDiv.style.display = 'none';
                btn.disabled = false;
            }, 2000);
        }
        
        // AI Query Generator
        async function generateAI() {
            const topic = prompt('Enter a topic or target to generate AI dork suggestions:\n\nExamples:\n• "banking login pages"\n• "exposed databases"\n• "API keys leaked"\n• "government documents"');
            
            if (!topic) return;
            
            const suggestions = generateLocalSuggestions(topic);
            
            document.getElementById('aiSuggestions').style.display = 'flex';
            document.getElementById('aiSuggestions').innerHTML = suggestions.map(s => 
                `<button class="ai-suggestion" onclick="useAISuggestion('${escapeHtml(s)}')">${escapeHtml(s)}</button>`
            ).join('');
        }
        
        function generateLocalSuggestions(topic) {
            const topicLower = topic.toLowerCase();
            const suggestions = [];
            
            if (topicLower.includes('login') || topicLower.includes('admin')) {
                suggestions.push(
                    `intitle:"login" site:*.${topicLower.replace(/[^a-z]/g, '')}.*`,
                    `inurl:admin intitle:"dashboard" "${topic}"`,
                    `"Welcome" intitle:"${topic}" inurl:login`
                );
            } else if (topicLower.includes('database') || topicLower.includes('sql')) {
                suggestions.push(
                    `filetype:sql "${topic}" "INSERT INTO"`,
                    `"${topic}" filetype:db OR filetype:mdb`,
                    `inurl:phpMyAdmin "${topic}"`
                );
            } else if (topicLower.includes('api') || topicLower.includes('key')) {
                suggestions.push(
                    `"${topic}" filetype:json "api_key"`,
                    `intext:"${topic}" "API_SECRET"`,
                    `site:github.com "${topic}" "secret" filetype:env`
                );
            } else if (topicLower.includes('document') || topicLower.includes('pdf')) {
                suggestions.push(
                    `"${topic}" filetype:pdf "confidential"`,
                    `site:*.gov "${topic}" filetype:pdf`,
                    `intitle:"${topic}" filetype:doc OR filetype:xlsx`
                );
            } else {
                suggestions.push(
                    `"${topic}" site:*.edu OR site:*.gov`,
                    `intitle:"${topic}" "index of"`,
                    `"${topic}" filetype:pdf OR filetype:doc`,
                    `inurl:"${topic}" intitle:admin`,
                    `"${topic}" intext:password OR intext:username`
                );
            }
            
            return suggestions.slice(0, 5);
        }
        
        function useAISuggestion(query) {
            document.getElementById('dorkQuery').value = query;
            document.getElementById('aiSuggestions').style.display = 'none';
        }
        
        function showAISuggestions(suggestions) {
            if (!suggestions?.tips?.length) return;
            
            // Convert tips to actionable suggestions
            const container = document.getElementById('aiSuggestions');
            container.style.display = 'flex';
            container.innerHTML = `<div style="width: 100%; font-size: 11px; color: var(--text-muted); margin-bottom: 8px;">💡 ${suggestions.tips.join(' • ')}</div>`;
        }
        
        function displayResults(results) {
            document.getElementById('resultsCount').textContent = results.length;
            
            if (results.length === 0) {
                document.getElementById('resultsContainer').innerHTML = `
                    <div class="empty-state">
                        <div class="empty-state-icon">😕</div>
                        <div class="empty-state-title">No results found</div>
                        <div class="empty-state-desc">Try a different dork query or adjust your search parameters</div>
                    </div>
                `;
                return;
            }
            
            let html = '';
            results.forEach((result, i) => {
                const safeTitle = escapeHtml(result.title || 'No title');
                const safeUrl = escapeHtml(result.url || '');
                const safeDesc = escapeHtml(result.description || '');
                
                // Generate tags based on indicators
                let tags = '';
                if (result.indicators?.length) {
                    tags = result.indicators.map(ind => {
                        const tagClass = ind.includes('admin') ? 'admin' : 
                                        ind.includes('config') ? 'config' :
                                        ind.includes('api') ? 'api' :
                                        ind.includes('database') ? 'database' : 'file';
                        return `<span class="result-tag ${tagClass}">${ind}</span>`;
                    }).join('');
                }
                
                // Detect file type tag
                if (result.is_file && result.file_type) {
                    tags += `<span class="result-tag file">.${result.file_type}</span>`;
                }
                
                html += `
                    <div class="result-item" data-type="${getResultType(result)}">
                        <div class="result-header">
                            <span class="result-number">#${i + 1}</span>
                        </div>
                        <a href="${safeUrl}" target="_blank" rel="noopener noreferrer" class="result-title">
                            ${safeTitle}
                        </a>
                        <div class="result-url">${safeUrl}</div>
                        ${safeDesc ? `<div class="result-desc">${safeDesc}</div>` : ''}
                        ${tags ? `<div class="result-tags">${tags}</div>` : ''}
                        <div class="result-actions">
                            <button class="result-action" onclick="copyUrl('${safeUrl}')">📋 Copy</button>
                            <button class="result-action" onclick="window.open('${safeUrl}', '_blank')">🔗 Open</button>
                            ${result.cached ? `<button class="result-action" onclick="window.open('${escapeHtml(result.cached)}', '_blank')">📦 Cache</button>` : ''}
                        </div>
                    </div>
                `;
            });
            
            document.getElementById('resultsContainer').innerHTML = html;
        }
        
        function getResultType(result) {
            const url = (result.url || '').toLowerCase();
            const title = (result.title || '').toLowerCase();
            
            if (result.indicators?.some(i => i.includes('admin'))) return 'admin';
            if (result.indicators?.some(i => i.includes('config'))) return 'config';
            if (result.indicators?.some(i => i.includes('api'))) return 'api';
            if (result.indicators?.some(i => i.includes('database'))) return 'database';
            if (result.is_file) return 'file';
            return 'all';
        }
        
        function filterResults() {
            const filter = document.getElementById('filterType').value;
            
            if (filter === 'all') {
                displayResults(allResults);
            } else {
                const filtered = allResults.filter(r => getResultType(r) === filter);
                displayResults(filtered);
            }
        }
        
        function showLoading(message) {
            document.getElementById('resultsContainer').innerHTML = `
                <div class="loading-state">
                    <div class="spinner"></div>
                    <div class="loading-text">${message || 'Processing...'}</div>
                </div>
            `;
        }
        
        function showError(message) {
            document.getElementById('resultsContainer').innerHTML = `
                <div class="empty-state">
                    <div class="empty-state-icon">❌</div>
                    <div class="empty-state-title">Error</div>
                    <div class="empty-state-desc">${escapeHtml(message)}</div>
                </div>
            `;
        }
        
        function updateStats(stats) {
            if (stats) {
                animateValue('totalDorks', stats.total_dorks || 0);
                animateValue('totalResults', stats.total_results || 0);
                animateValue('savedQueries', stats.saved_queries || 0);
                document.getElementById('successRate').textContent = (stats.success_rate || 0) + '%';
            }
        }
        
        function animateValue(id, target) {
            const el = document.getElementById(id);
            const current = parseInt(el.textContent) || 0;
            if (current === target) return;
            
            const step = (target - current) / 20;
            let val = current;
            const timer = setInterval(() => {
                val += step;
                if ((step > 0 && val >= target) || (step < 0 && val <= target)) {
                    el.textContent = target;
                    clearInterval(timer);
                } else {
                    el.textContent = Math.round(val);
                }
            }, 30);
        }
        
        async function loadStats() {
            try {
                const response = await fetch('dorker-api.php?action=stats');
                const data = await response.json();
                if (data.success) {
                    updateStats(data.stats);
                }
            } catch (e) {
                console.error('Failed to load stats:', e);
            }
        }
        
        function copyUrl(url) {
            navigator.clipboard.writeText(url).then(() => {
                showToast('URL copied to clipboard!');
            }).catch(() => {
                // Fallback
                const ta = document.createElement('textarea');
                ta.value = url;
                document.body.appendChild(ta);
                ta.select();
                document.execCommand('copy');
                document.body.removeChild(ta);
                showToast('URL copied!');
            });
        }
        
        function copyAllUrls() {
            if (currentResults.length === 0) {
                alert('No results to copy');
                return;
            }
            
            const urls = currentResults.map(r => r.url).join('\n');
            navigator.clipboard.writeText(urls).then(() => {
                showToast(`${currentResults.length} URLs copied!`);
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
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ results: currentResults })
                });
                
                const blob = await response.blob();
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `dorker-results-${Date.now()}.${format}`;
                a.click();
                URL.revokeObjectURL(url);
                
                showToast(`Exported ${currentResults.length} results as ${format.toUpperCase()}`);
            } catch (error) {
                alert('Export failed: ' + error.message);
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
                animation: slideIn 0.3s ease;
            `;
            toast.textContent = message;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
        
        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
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
    <script>document.body.dataset.aiContext = 'dorking';</script>
</body>
</html>
