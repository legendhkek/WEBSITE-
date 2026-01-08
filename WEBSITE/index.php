<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Legend House - Stream & Download Movies, TV Shows, Games</title>
    <meta name="description" content="Legend House - The ultimate destination to stream and download movies, TV shows, games, software, and more. Watch instantly or download via magnet links.">
    
    <!-- Google AdSense -->
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-1940810089559549"
         crossorigin="anonymous"></script>
    
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🏠</text></svg>">
</head>
<body>
    <!-- Wallpaper Rotation System -->
    <div class="wallpaper-container" id="wallpaperContainer"></div>
    <div class="wallpaper-overlay"></div>

    <!-- Page Loader -->
    <div class="page-loader" id="pageLoader">
        <div class="loader-icon">
            <div class="loader-ring"></div>
            <div class="loader-ring"></div>
            <div class="loader-ring"></div>
        </div>
        <div class="loader-text">LEGEND HOUSE</div>
        <div class="loader-tagline">Stream & Download Anything</div>
    </div>

    <!-- Keyboard Shortcuts Modal -->
    <div id="shortcutsModal" class="shortcuts-modal">
        <div class="shortcuts-content">
            <h3>⌨️ Keyboard Shortcuts</h3>
            <p>Master Legend House with these shortcuts</p>
            <div class="shortcuts-grid">
                <div class="shortcut"><span>Focus search</span> <kbd>/</kbd></div>
                <div class="shortcut"><span>Close modal</span> <kbd>Esc</kbd></div>
                <div class="shortcut"><span>Show shortcuts</span> <kbd>?</kbd></div>
                <div class="shortcut"><span>Previous page</span> <kbd>←</kbd></div>
                <div class="shortcut"><span>Next page</span> <kbd>→</kbd></div>
                <div class="shortcut"><span>Category 1-8</span> <kbd>1-8</kbd></div>
            </div>
        </div>
    </div>

    <!-- Download Modal -->
    <div id="downloadModal" class="modal">
        <div class="modal-content" id="downloadModalContent">
            <!-- Populated by JavaScript -->
        </div>
    </div>

    <!-- Stream Modal -->
    <div id="streamModal" class="modal stream-modal">
        <div class="stream-modal-content">
            <div class="stream-header">
                <h3 id="streamTitle">Loading...</h3>
                <button class="modal-close" onclick="closeStreamModal()">×</button>
            </div>
            <div class="stream-player-container">
                <div id="streamLoading" class="stream-loading">
                    <div class="stream-loading-content">
                        <div class="stream-spinner"></div>
                        <h4>Connecting to peers...</h4>
                        <p id="streamStatus">Initializing WebTorrent</p>
                        <div class="stream-progress">
                            <div class="stream-progress-bar" id="streamProgressBar"></div>
                        </div>
                        <div class="stream-stats">
                            <span id="streamPeers">Peers: 0</span>
                            <span id="streamSpeed">Speed: 0 KB/s</span>
                            <span id="streamProgress">Progress: 0%</span>
                        </div>
                    </div>
                </div>
                <video id="streamPlayer" class="stream-player" controls autoplay></video>
            </div>
            <div class="stream-info">
                <div class="stream-info-row">
                    <span class="stream-info-label">Quality:</span>
                    <span id="streamQuality" class="stream-info-value">-</span>
                </div>
                <div class="stream-info-row">
                    <span class="stream-info-label">Size:</span>
                    <span id="streamSize" class="stream-info-value">-</span>
                </div>
                <div class="stream-info-row">
                    <span class="stream-info-label">Source:</span>
                    <span id="streamSource" class="stream-info-value">-</span>
                </div>
            </div>
            <div class="stream-controls">
                <button class="btn btn-outline" onclick="toggleFullscreen()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"/>
                    </svg>
                    Fullscreen
                </button>
                <button class="btn btn-outline" onclick="copyStreamLink()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                    </svg>
                    Copy Link
                </button>
                <a id="streamDownloadBtn" href="#" class="btn btn-primary" download>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <polyline points="7 10 12 15 17 10"/>
                        <line x1="12" y1="15" x2="12" y2="3"/>
                    </svg>
                    Download
                </a>
            </div>
        </div>
    </div>

    <!-- Header -->
    <header class="header">
        <div class="container">
            <a href="/" class="logo">
                <div class="logo-icon">
                    <svg width="42" height="42" viewBox="0 0 42 42">
                        <defs>
                            <linearGradient id="logoGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" style="stop-color:#f59e0b"/>
                                <stop offset="50%" style="stop-color:#ef4444"/>
                                <stop offset="100%" style="stop-color:#ec4899"/>
                            </linearGradient>
                        </defs>
                        <rect x="6" y="12" width="30" height="22" rx="3" fill="none" stroke="url(#logoGrad)" stroke-width="2.5"/>
                        <polygon points="21,6 28,12 14,12" fill="url(#logoGrad)"/>
                        <rect x="17" y="22" width="8" height="12" fill="url(#logoGrad)" opacity="0.7"/>
                    </svg>
                </div>
                <div class="logo-text">
                    <span class="logo-title"><span class="title-gradient">LEGEND</span> HOUSE</span>
                    <span class="logo-version">Stream • Download • Watch</span>
                </div>
            </a>
            <nav class="nav">
                <a href="watch.php" class="nav-btn nav-btn-featured">
                    <span class="nav-btn-icon">▶️</span>
                    <span class="nav-btn-text">Watch Now</span>
                </a>
                <a href="tools/torrent.php" class="nav-btn" title="Tools - Torrent Center, Media Tools, AI Tools, Utilities">
                    <span class="nav-btn-icon">🛠️</span>
                    <span class="nav-btn-text">Tools</span>
                </a>
                <button class="nav-btn" onclick="toggleShortcutsModal()" title="Keyboard Shortcuts (?)">
                    <span class="nav-btn-icon">⌨️</span>
                </button>
                <button class="nav-btn" onclick="exportResults()" title="Export Results">
                    <span class="nav-btn-icon">📥</span>
                </button>
                <div id="userNav"></div>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-badge">
                <span class="hero-badge-dot"></span>
                <span class="hero-badge-text">🎬 Stream Movies • 📺 Watch TV Shows • 🎮 Download Games</span>
            </div>
            <h1 class="hero-title">
                <span class="title-gradient">Legend</span> House
            </h1>
            <p class="hero-subtitle">Stream movies directly in browser or download via magnet links. 10+ sources, instant access.</p>
            
            <!-- Search Form -->
            <form id="searchForm" class="search-container">
                <div class="search-wrapper">
                    <div class="search-input-wrapper">
                        <input type="text" id="searchInput" class="search-input" 
                               placeholder="Search movies, TV shows, games, anime..." 
                               autocomplete="off" spellcheck="false">
                    </div>
                    <button type="submit" id="searchBtn" class="search-btn" title="Search">
                        <svg class="search-icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="m21 21-4.35-4.35"></path>
                        </svg>
                        <div class="search-btn-loader"></div>
                    </button>
                </div>
            </form>
            
            <!-- Category Buttons -->
            <div class="category-buttons">
                <button class="category-btn active" data-category="all">🔍 All</button>
                <button class="category-btn" data-category="movies">🎬 Movies</button>
                <button class="category-btn" data-category="tv">📺 TV Shows</button>
                <button class="category-btn" data-category="games">🎮 Games</button>
                <button class="category-btn" data-category="software">💻 Software</button>
                <button class="category-btn" data-category="anime">🎌 Anime</button>
                <button class="category-btn" data-category="music">🎵 Music</button>
                <button class="category-btn" data-category="ebooks">📚 Ebooks</button>
            </div>
            
            <!-- Quick Search Tags -->
            <div class="quick-tags">
                <span class="quick-tag-label">🔥 Trending:</span>
                <button class="quick-tag" onclick="quickSearch('Avatar 3')">Avatar 3</button>
                <button class="quick-tag" onclick="quickSearch('Dune 2')">Dune 2</button>
                <button class="quick-tag" onclick="quickSearch('GTA 6')">GTA 6</button>
                <button class="quick-tag" onclick="quickSearch('The Witcher')">The Witcher</button>
                <button class="quick-tag" onclick="quickSearch('Jujutsu Kaisen')">Jujutsu Kaisen</button>
                <button class="quick-tag" onclick="quickSearch('Cyberpunk')">Cyberpunk</button>
            </div>
            
            <!-- Recent Searches -->
            <div id="recentSearches" class="recent-searches"></div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
        <div class="container">
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">▶️</div>
                    <h3>Stream Instantly</h3>
                    <p>Watch movies and shows directly in your browser using WebTorrent technology</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">⚡</div>
                    <h3>Lightning Fast</h3>
                    <p>Parallel search across 10+ sources with intelligent caching</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🧲</div>
                    <h3>Multiple Downloads</h3>
                    <p>Magnet links, torrent files, and direct downloads available</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🔒</div>
                    <h3>No Sign Up</h3>
                    <p>No registration required. Just search, stream, and download</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Results Section -->
    <section id="resultsSection" class="results-section" style="display: none;">
        <div class="container">
            <!-- Filter Bar -->
            <div id="filterBar" class="filter-bar">
                <div class="filter-left">
                    <div class="filter-group">
                        <label>Quality</label>
                        <select id="filterQuality" class="filter-select">
                            <option value="all">All Quality</option>
                            <option value="4K">4K / UHD</option>
                            <option value="1080p">1080p</option>
                            <option value="720p">720p</option>
                            <option value="BluRay">BluRay</option>
                            <option value="WEB-DL">WEB-DL</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Type</label>
                        <select id="filterType" class="filter-select">
                            <option value="all">All Types</option>
                            <option value="Movie">Movies</option>
                            <option value="TV Show">TV Shows</option>
                            <option value="Game">Games</option>
                            <option value="Software">Software</option>
                            <option value="Anime">Anime</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Sort</label>
                        <select id="sortBy" class="filter-select">
                            <option value="seeds">🌱 Most Seeds</option>
                            <option value="size">📦 Largest Size</option>
                            <option value="name">🔤 Name A-Z</option>
                        </select>
                    </div>
                </div>
                <div class="filter-right">
                    <button class="filter-btn btn-clear" onclick="clearFilters()">
                        Clear Filters
                    </button>
                    <button class="filter-btn btn-export" onclick="exportResults()">
                        📤 Export
                    </button>
                    <button class="filter-btn btn-copy-all" onclick="copyAllMagnets()">
                        🧲 Copy All
                    </button>
                </div>
            </div>
            
            <!-- Results Container -->
            <div id="resultsContainer" class="results-container"></div>
            
            <!-- Pagination -->
            <div id="paginationContainer" class="pagination-container"></div>
        </div>
    </section>

    <!-- Toast Container -->
    <div id="toastContainer" class="toast-container"></div>

    <!-- Back to Top Button -->
    <button id="backToTop" class="back-to-top" title="Back to top">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="18 15 12 9 6 15"/>
        </svg>
    </button>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-left">
                    <span class="footer-brand">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="8" width="18" height="13" rx="2"/>
                            <polygon points="12,3 19,8 5,8"/>
                        </svg>
                        LEGEND HOUSE
                    </span>
                    <span class="footer-divider"></span>
                    <span class="footer-stats">Stream • Download • Watch</span>
                </div>
                <div class="footer-right">
                    <span class="footer-shortcut">Press <kbd>?</kbd> for shortcuts</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- WebTorrent Script -->
    <script src="https://cdn.jsdelivr.net/npm/webtorrent@latest/webtorrent.min.js"></script>
    <script src="script.js"></script>
    <script src="advanced-features.js"></script>
</body>
</html>
