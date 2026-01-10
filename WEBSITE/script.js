/**
 * ═══════════════════════════════════════════════════════════════════════════════
 * LEGEND HOUSE - Stream & Download v9.0
 * ═══════════════════════════════════════════════════════════════════════════════
 * 
 * Features:
 * - WebTorrent in-browser streaming for movies/TV shows
 * - Real-time percentage loading with SSE
 * - Full pagination with page navigation
 * - Multiple download methods (Magnet, Torrent, Direct)
 * - Stream directly in browser
 * - Download modal with all options
 * - Advanced filtering and sorting
 * - Health status indicators
 * - Keyboard shortcuts
 * 
 * ═══════════════════════════════════════════════════════════════════════════════
 */

// ═══════════════════════════════════════════════════════════════════════════════
// GLOBAL STATE
// ═══════════════════════════════════════════════════════════════════════════════
const state = {
    query: '',
    category: 'all',
    page: 1,
    perPage: 25,
    results: [],
    allResults: [],
    pagination: null,
    isLoading: false,
    eventSource: null,
    filters: {
        quality: 'all',
        type: 'all',
        minSeeds: 0
    },
    sort: 'seeds',
    // Streaming state
    webTorrentClient: null,
    currentTorrent: null,
    isStreaming: false
};

// ═══════════════════════════════════════════════════════════════════════════════
// CONFIGURATION
// ═══════════════════════════════════════════════════════════════════════════════
const config = {
    apiUrl: 'api.php',
    wallpapers: [
        'https://images.unsplash.com/photo-1534796636912-3b95b3ab5986?w=1920&q=80',
        'https://images.unsplash.com/photo-1519681393784-d120267933ba?w=1920&q=80',
        'https://images.unsplash.com/photo-1507400492013-162706c8c05e?w=1920&q=80',
        'https://images.unsplash.com/photo-1465101162946-4377e57745c3?w=1920&q=80',
        'https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=1920&q=80',
        'https://images.unsplash.com/photo-1462332420958-a05d1e002413?w=1920&q=80'
    ],
    sourceColors: {
        'YTS': '#10b981',
        'EZTV': '#6366f1',
        'TPB': '#ef4444',
        'ThePirateBay': '#ef4444',
        'Nyaa': '#ec4899',
        '1337x': '#f97316',
        'TGx': '#eab308',
        'TorrentGalaxy': '#eab308',
        'BTDig': '#06b6d4',
        'LimeTorrents': '#84cc16',
        'SolidTorrents': '#8b5cf6',
        'Archive.org': '#14b8a6'
    }
};

// ═══════════════════════════════════════════════════════════════════════════════
// INITIALIZATION
// ═══════════════════════════════════════════════════════════════════════════════
document.addEventListener('DOMContentLoaded', () => {
    initLoader();
    initWallpapers();
    initHeaderScroll();
    initKeyboardShortcuts();
    initEventListeners();
    loadRecentSearches();
    checkAuthStatus();
    
    // Focus search on load
    setTimeout(() => {
        document.getElementById('searchInput')?.focus();
    }, 500);
});

// Check authentication status
async function checkAuthStatus() {
    try {
        const formData = new FormData();
        formData.append('action', 'check');
        
        const response = await fetch('auth.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.loggedIn && data.user) {
            updateUserNavigation(data.user);
            
            // Check for login success message
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('login') && urlParams.get('login') === 'success') {
                const provider = urlParams.get('provider') || 'local';
                if (provider === 'google') {
                    showToast(`Welcome back, ${data.user.username}! Signed in with Google`, 'success');
                } else {
                    showToast(`Welcome back, ${data.user.username}!`, 'success');
                }
                // Clean URL
                window.history.replaceState({}, document.title, window.location.pathname);
            }
        } else {
            updateUserNavigation(null);
        }
    } catch (error) {
        // If auth.php doesn't exist or fails, just show login/signup
        updateUserNavigation(null);
    }
}

// Update user navigation
function updateUserNavigation(user) {
    const userNav = document.getElementById('userNav');
    if (!userNav) return;
    
    if (user) {
        userNav.innerHTML = `
            <span class="nav-btn" style="pointer-events: none;">
                <span class="nav-btn-icon">👤</span>
                <span class="nav-btn-text">${escapeHtml(user.username)}</span>
            </span>
            <button class="nav-btn" onclick="logoutUser()">
                <span class="nav-btn-icon">🚪</span>
                <span class="nav-btn-text">Logout</span>
            </button>
        `;
    } else {
        userNav.innerHTML = `
            <a href="login.php" class="nav-btn">
                <span class="nav-btn-icon">🔑</span>
                <span class="nav-btn-text">Login</span>
            </a>
            <a href="signup.php" class="nav-btn" style="background: linear-gradient(135deg, var(--primary), var(--accent)); color: #fff; box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);">
                <span class="nav-btn-icon">✨</span>
                <span class="nav-btn-text">Sign Up</span>
            </a>
        `;
    }
}

// Logout user
async function logoutUser() {
    try {
        const formData = new FormData();
        formData.append('action', 'logout');
        
        await fetch('auth.php', {
            method: 'POST',
            body: formData
        });
        
        window.location.reload();
    } catch (error) {
        console.error('Logout failed:', error);
        showToast('Logout failed', 'error');
    }
}

function initLoader() {
    const loader = document.getElementById('pageLoader');
    if (loader) {
        setTimeout(() => {
            loader.classList.add('fade-out');
            setTimeout(() => loader.remove(), 500);
        }, 800);
    }
}

function initWallpapers() {
    const container = document.getElementById('wallpaperContainer');
    if (!container) return;
    
    const wallpapers = config.wallpapers;
    let current = 0;
    
    // Create wallpaper elements
    wallpapers.forEach((url, i) => {
        const div = document.createElement('div');
        div.className = `wallpaper ${i === 0 ? 'active' : ''}`;
        div.style.backgroundImage = `url(${url})`;
        container.appendChild(div);
    });
    
    // Rotate wallpapers
    setInterval(() => {
        const walls = container.querySelectorAll('.wallpaper');
        walls[current].classList.remove('active');
        current = (current + 1) % walls.length;
        walls[current].classList.add('active');
    }, 8000);
}

function initHeaderScroll() {
    const header = document.querySelector('.header');
    if (!header) return;
    
    let lastScroll = 0;
    window.addEventListener('scroll', () => {
        const currentScroll = window.scrollY;
        if (currentScroll > 100 && currentScroll > lastScroll) {
            header.classList.add('hidden');
        } else {
            header.classList.remove('hidden');
        }
        lastScroll = currentScroll;
        
        // Back to top button
        const backToTop = document.getElementById('backToTop');
        if (backToTop) {
            backToTop.classList.toggle('visible', currentScroll > 500);
        }
    });
}

function initKeyboardShortcuts() {
    document.addEventListener('keydown', (e) => {
        // Don't trigger in inputs
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {
            if (e.key === 'Escape') {
                e.target.blur();
                closeAllModals();
            }
            return;
        }
        
        switch(e.key) {
            case '/':
                e.preventDefault();
                document.getElementById('searchInput')?.focus();
                break;
            case '?':
                e.preventDefault();
                toggleShortcutsModal();
                break;
            case 'Escape':
                closeAllModals();
                break;
            case 'ArrowLeft':
                if (state.pagination?.hasPrev) {
                    goToPage(state.page - 1);
                }
                break;
            case 'ArrowRight':
                if (state.pagination?.hasNext) {
                    goToPage(state.page + 1);
                }
                break;
        }
        
        // Number keys for categories
        if (e.key >= '1' && e.key <= '8') {
            const categories = ['all', 'movies', 'tv', 'games', 'software', 'anime', 'music', 'ebooks'];
            const idx = parseInt(e.key) - 1;
            if (categories[idx]) {
                setCategory(categories[idx]);
            }
        }
    });
}

function initEventListeners() {
    // Search form
    document.getElementById('searchForm')?.addEventListener('submit', (e) => {
        e.preventDefault();
        performSearch();
    });
    
    // Search input
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', debounce(() => {
            // Could add autocomplete here
        }, 300));
    }
    
    // Category buttons
    document.querySelectorAll('.category-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            setCategory(btn.dataset.category);
        });
    });
    
    // Back to top
    document.getElementById('backToTop')?.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
    
    // Shortcuts modal close
    document.getElementById('shortcutsModal')?.addEventListener('click', (e) => {
        if (e.target.id === 'shortcutsModal') {
            toggleShortcutsModal();
        }
    });
    
    // Download modal close
    document.getElementById('downloadModal')?.addEventListener('click', (e) => {
        if (e.target.id === 'downloadModal') {
            closeDownloadModal();
        }
    });
    
    // Filter selects
    document.getElementById('filterQuality')?.addEventListener('change', applyFilters);
    document.getElementById('filterType')?.addEventListener('change', applyFilters);
    document.getElementById('sortBy')?.addEventListener('change', applySort);
}

// ═══════════════════════════════════════════════════════════════════════════════
// SEARCH FUNCTIONS
// ═══════════════════════════════════════════════════════════════════════════════
async function performSearch(page = 1) {
    const query = document.getElementById('searchInput')?.value.trim();
    if (!query) {
        showToast('Please enter a search term', 'warning');
        return;
    }
    
    state.query = query;
    state.page = page;
    state.isLoading = true;
    
    showLoading();
    saveRecentSearch(query);
    
    // Close any existing SSE connection
    if (state.eventSource) {
        state.eventSource.close();
    }
    
    // Use SSE for real-time progress
    const url = `${config.apiUrl}?action=search&query=${encodeURIComponent(query)}&category=${state.category}&page=${page}&limit=${state.perPage}&sse=1`;
    
    state.eventSource = new EventSource(url);
    
    state.eventSource.onmessage = (event) => {
        try {
            const data = JSON.parse(event.data);
            
            if (data.type === 'progress') {
                updateProgress(data.percent, data.message, data.source, data.found);
            } else if (data.type === 'complete') {
                state.eventSource.close();
                handleSearchResults(data.data);
            } else if (data.type === 'error') {
                state.eventSource.close();
                showError(data.message);
            }
        } catch (e) {
            console.error('SSE parse error:', e);
        }
    };
    
    state.eventSource.onerror = () => {
        state.eventSource.close();
        // Fallback to regular fetch
        fetchSearchFallback(query, page);
    };
}

async function fetchSearchFallback(query, page) {
    try {
        const url = `${config.apiUrl}?action=search&query=${encodeURIComponent(query)}&category=${state.category}&page=${page}&limit=${state.perPage}`;
        const response = await fetch(url);
        const data = await response.json();
        
        if (data.success) {
            handleSearchResults(data);
        } else {
            showError(data.error || 'Search failed');
        }
    } catch (error) {
        showError('Network error: ' + error.message);
    }
}

function handleSearchResults(data) {
    state.isLoading = false;
    state.results = data.results || [];
    state.pagination = data.pagination || null;
    
    hideLoading();
    
    if (state.results.length === 0) {
        showNoResults();
        return;
    }
    
    renderResults();
    renderPagination();
    showFilterBar();
    
    // Scroll to results
    document.getElementById('resultsSection')?.scrollIntoView({ behavior: 'smooth' });
    
    // Show stats
    const statsText = `Found ${data.pagination?.totalResults || state.results.length} results`;
    const timeText = data.time ? ` in ${data.time}` : '';
    const sourcesText = data.sources?.length ? ` from ${data.sources.join(', ')}` : '';
    showToast(statsText + timeText, 'success');
}

async function goToPage(page) {
    if (page < 1 || (state.pagination && page > state.pagination.totalPages)) return;
    
    state.page = page;
    showLoading();
    
    try {
        const url = `${config.apiUrl}?action=page&query=${encodeURIComponent(state.query)}&category=${state.category}&page=${page}&limit=${state.perPage}`;
        const response = await fetch(url);
        const data = await response.json();
        
        if (data.success) {
            state.results = data.results || [];
            state.pagination = data.pagination || null;
            hideLoading();
            renderResults();
            renderPagination();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        } else {
            // If cache expired, do full search
            performSearch(page);
        }
    } catch (error) {
        performSearch(page);
    }
}

// ═══════════════════════════════════════════════════════════════════════════════
// RENDERING FUNCTIONS
// ═══════════════════════════════════════════════════════════════════════════════
function renderResults() {
    const container = document.getElementById('resultsContainer');
    if (!container) return;
    
    let html = '<div class="results-cards">';
    
    state.results.forEach((result, index) => {
        html += renderResultCard(result, index);
    });
    
    html += '</div>';
    container.innerHTML = html;
    
    // Animate cards
    requestAnimationFrame(() => {
        container.querySelectorAll('.result-card').forEach((card, i) => {
            setTimeout(() => card.classList.add('visible'), i * 50);
        });
    });
}

function renderResultCard(result, index) {
    const healthClass = `health-${result.health || 'unknown'}`;
    const sourceColor = config.sourceColors[result.source] || '#6366f1';
    const verified = result.verified ? '<span class="verified-badge" title="Verified">✓</span>' : '';
    
    // Check if this is streamable content (Movies/TV Shows)
    const isStreamable = ['Movie', 'TV Show', 'Anime'].includes(result.contentType) || 
                         result.source === 'YTS' || result.source === 'EZTV' ||
                         (result.name && (result.name.match(/\.(mp4|mkv|avi|webm)/i) || 
                          result.name.match(/(720p|1080p|2160p|4k|bluray|webrip|web-dl)/i)));
    
    // Get magnet URL for watch page
    const magnetUrl = result.downloadMethods?.find(m => m.type === 'magnet')?.url || '';
    const encodedMagnet = encodeURIComponent(magnetUrl);
    
    return `
        <div class="result-card ${healthClass}" data-index="${index}">
            <div class="result-card-header">
                <div class="result-title-wrapper">
                    <h3 class="result-title">${escapeHtml(result.name)}</h3>
                    ${verified}
                </div>
                <span class="source-badge" style="background: linear-gradient(135deg, ${sourceColor} 0%, ${adjustColor(sourceColor, -20)} 100%)">
                    ${escapeHtml(result.source)}
                </span>
            </div>
            
            <div class="result-meta">
                ${result.size ? `<span class="meta-badge size-badge">📦 ${escapeHtml(result.size)}</span>` : ''}
                ${result.quality ? `<span class="meta-badge quality-badge quality-${result.quality.toLowerCase().replace(/[^a-z0-9]/g, '')}">${escapeHtml(result.quality)}</span>` : ''}
                ${result.contentType ? `<span class="meta-badge type-badge">${getTypeIcon(result.contentType)} ${escapeHtml(result.contentType)}</span>` : ''}
                ${result.year ? `<span class="meta-badge year-badge">📅 ${result.year}</span>` : ''}
                ${result.rating ? `<span class="meta-badge rating-badge">⭐ ${result.rating}</span>` : ''}
            </div>
            
            <div class="result-stats">
                <div class="seed-peer-info">
                    <span class="seed-badge ${getSeedClass(result.seeds)}">
                        <span class="seed-icon">🌱</span>
                        <span class="seed-count">${formatNumber(result.seeds || 0)}</span>
                    </span>
                    <span class="peer-badge">
                        <span class="peer-icon">👥</span>
                        <span class="peer-count">${formatNumber(result.peers || 0)}</span>
                    </span>
                </div>
                <div class="health-indicator ${healthClass}" title="Health: ${result.health || 'Unknown'}">
                    <span class="health-dot"></span>
                    <span class="health-text">${capitalizeFirst(result.health || 'unknown')}</span>
                </div>
            </div>
            
            <!-- Main Action Buttons: Watch & Download Side by Side -->
            <div class="result-main-actions">
                ${isStreamable ? `
                    <button class="action-btn action-watch" onclick="streamTorrent(${index})" title="Stream in Browser">
                        <span class="action-icon">▶️</span>
                        <span class="action-text">Watch Now</span>
                    </button>
                    <a href="watch.php?magnet=${encodedMagnet}" class="action-btn action-watch-page" title="Open in Watch Page">
                        <span class="action-icon">🎬</span>
                        <span class="action-text">Watch Page</span>
                    </a>
                ` : ''}
                <button class="action-btn action-download" onclick="openDownloadModal(${index})" title="All Download Options">
                    <span class="action-icon">⬇️</span>
                    <span class="action-text">Download</span>
                </button>
            </div>
            
            <!-- Quick Actions Row -->
            <div class="result-quick-actions">
                ${magnetUrl ? `
                    <a href="${escapeHtml(magnetUrl)}" class="quick-btn" title="Open Magnet">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"/>
                            <path d="M8 12l4-4 4 4M12 16V8"/>
                        </svg>
                        Magnet
                    </a>
                ` : ''}
                <button class="quick-btn" onclick="copyMagnet(${index})" title="Copy Magnet Link">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                    </svg>
                    Copy
                </button>
                <button class="quick-btn ai-analyze-btn" onclick="analyzeWithAI(${index})" title="AI Analysis">
                    🤖 Analyze
                </button>
                ${result.downloadMethods?.find(m => m.type === 'torrent') ? `
                    <a href="api.php?action=torrent&url=${encodeURIComponent(result.downloadMethods.find(m => m.type === 'torrent').url)}" class="quick-btn" title="Download .torrent" download>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                        </svg>
                        .torrent
                    </a>
                ` : ''}
                ${result.sourceUrl ? `
                    <a href="${escapeHtml(result.sourceUrl)}" target="_blank" class="quick-btn" title="View Source">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                            <polyline points="15 3 21 3 21 9"/>
                            <line x1="10" y1="14" x2="21" y2="3"/>
                        </svg>
                        Source
                    </a>
                ` : ''}
            </div>
        </div>
    `;
}

function renderPagination() {
    const container = document.getElementById('paginationContainer');
    if (!container || !state.pagination) {
        if (container) container.innerHTML = '';
        return;
    }
    
    const { page, totalPages, totalResults, hasPrev, hasNext } = state.pagination;
    
    if (totalPages <= 1) {
        container.innerHTML = '';
        return;
    }
    
    let pages = [];
    const maxVisible = 7;
    
    if (totalPages <= maxVisible) {
        for (let i = 1; i <= totalPages; i++) pages.push(i);
    } else {
        pages.push(1);
        
        if (page > 3) pages.push('...');
        
        for (let i = Math.max(2, page - 1); i <= Math.min(totalPages - 1, page + 1); i++) {
            pages.push(i);
        }
        
        if (page < totalPages - 2) pages.push('...');
        
        pages.push(totalPages);
    }
    
    let html = `
        <div class="pagination">
            <div class="pagination-info">
                Page ${page} of ${totalPages} (${formatNumber(totalResults)} results)
            </div>
            <div class="pagination-controls">
                <button class="pagination-btn ${!hasPrev ? 'disabled' : ''}" 
                        onclick="goToPage(1)" ${!hasPrev ? 'disabled' : ''} title="First Page">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="11 17 6 12 11 7"/>
                        <polyline points="18 17 13 12 18 7"/>
                    </svg>
                </button>
                <button class="pagination-btn ${!hasPrev ? 'disabled' : ''}" 
                        onclick="goToPage(${page - 1})" ${!hasPrev ? 'disabled' : ''} title="Previous Page">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="15 18 9 12 15 6"/>
                    </svg>
                </button>
                
                <div class="pagination-pages">
                    ${pages.map(p => {
                        if (p === '...') {
                            return '<span class="pagination-ellipsis">...</span>';
                        }
                        return `<button class="pagination-page ${p === page ? 'active' : ''}" onclick="goToPage(${p})">${p}</button>`;
                    }).join('')}
                </div>
                
                <button class="pagination-btn ${!hasNext ? 'disabled' : ''}" 
                        onclick="goToPage(${page + 1})" ${!hasNext ? 'disabled' : ''} title="Next Page">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9 18 15 12 9 6"/>
                    </svg>
                </button>
                <button class="pagination-btn ${!hasNext ? 'disabled' : ''}" 
                        onclick="goToPage(${totalPages})" ${!hasNext ? 'disabled' : ''} title="Last Page">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="13 17 18 12 13 7"/>
                        <polyline points="6 17 11 12 6 7"/>
                    </svg>
                </button>
            </div>
        </div>
    `;
    
    container.innerHTML = html;
}

// ═══════════════════════════════════════════════════════════════════════════════
// DOWNLOAD MODAL
// ═══════════════════════════════════════════════════════════════════════════════
function openDownloadModal(index) {
    const result = state.results[index];
    if (!result) return;
    
    const modal = document.getElementById('downloadModal');
    const content = document.getElementById('downloadModalContent');
    
    if (!modal || !content) return;
    
    let methodsHtml = '';
    
    // Add all download methods
    if (result.downloadMethods && result.downloadMethods.length > 0) {
        result.downloadMethods.forEach((method, i) => {
            const icon = getMethodIcon(method.type);
            const btnClass = getMethodClass(method.type);
            
            methodsHtml += `
                <div class="download-method">
                    <div class="method-info">
                        <span class="method-icon">${icon}</span>
                        <div class="method-details">
                            <span class="method-label">${escapeHtml(method.label)}</span>
                            <span class="method-type">${method.type.toUpperCase()}</span>
                        </div>
                    </div>
                    <div class="method-actions">
                        ${method.type === 'magnet' ? `
                            <a href="${escapeHtml(method.url)}" class="btn ${btnClass}">
                                Open in Client
                            </a>
                            <button class="btn btn-copy-sm" onclick="copyToClipboard('${escapeHtml(method.url)}')">
                                Copy
                            </button>
                        ` : method.type === 'torrent' ? `
                            <a href="api.php?action=torrent&url=${encodeURIComponent(method.url)}" class="btn ${btnClass}" download>
                                Download .torrent
                            </a>
                            <a href="${escapeHtml(method.url)}" target="_blank" class="btn btn-secondary">
                                Direct Link
                            </a>
                        ` : `
                            <a href="${escapeHtml(method.url)}" target="_blank" class="btn ${btnClass}">
                                Open Link
                            </a>
                        `}
                    </div>
                </div>
            `;
        });
    }
    
    // Add hash info
    if (result.hash) {
        methodsHtml += `
            <div class="download-method hash-info">
                <div class="method-info">
                    <span class="method-icon">🔑</span>
                    <div class="method-details">
                        <span class="method-label">Info Hash</span>
                        <code class="hash-code">${escapeHtml(result.hash)}</code>
                    </div>
                </div>
                <div class="method-actions">
                    <button class="btn btn-copy-sm" onclick="copyToClipboard('${escapeHtml(result.hash)}')">
                        Copy Hash
                    </button>
                </div>
            </div>
        `;
    }
    
    content.innerHTML = `
        <div class="modal-header">
            <h3>📥 Download & Watch Options</h3>
            <button class="modal-close" onclick="closeDownloadModal()">×</button>
        </div>
        <div class="modal-body">
            <div class="download-title">
                <h4>${escapeHtml(result.name)}</h4>
                <div class="download-meta">
                    ${result.size ? `<span>📦 ${escapeHtml(result.size)}</span>` : ''}
                    ${result.source ? `<span>🌐 ${escapeHtml(result.source)}</span>` : ''}
                    ${result.seeds ? `<span>🌱 ${result.seeds} seeds</span>` : ''}
                    ${result.quality ? `<span>🎬 ${escapeHtml(result.quality)}</span>` : ''}
                </div>
            </div>
            
            <!-- Watch Section -->
            ${isStreamableResult(result) ? `
                <div class="modal-section">
                    <h5 class="section-title">▶️ Watch Options</h5>
                    <div class="watch-options">
                        <button class="watch-option-btn" onclick="closeDownloadModal(); streamTorrent(${index})">
                            <span class="watch-icon">🎬</span>
                            <span class="watch-text">
                                <strong>Stream Now</strong>
                                <small>Watch in browser using WebTorrent</small>
                            </span>
                        </button>
                        <a href="watch.php?magnet=${encodeURIComponent(result.downloadMethods?.find(m => m.type === 'magnet')?.url || '')}" class="watch-option-btn">
                            <span class="watch-icon">🖥️</span>
                            <span class="watch-text">
                                <strong>Open Watch Page</strong>
                                <small>Full-screen player with controls</small>
                            </span>
                        </a>
                    </div>
                </div>
            ` : ''}
            
            <!-- Download Section -->
            <div class="modal-section">
                <h5 class="section-title">⬇️ Download Options</h5>
                <div class="download-methods">
                    ${methodsHtml}
                </div>
            </div>
        </div>
    `;
    
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeDownloadModal() {
    const modal = document.getElementById('downloadModal');
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
}

function getMethodIcon(type) {
    const icons = {
        'magnet': '🧲',
        'torrent': '📄',
        'direct': '⬇️',
        'cloud': '☁️'
    };
    return icons[type] || '📥';
}

function getMethodClass(type) {
    const classes = {
        'magnet': 'btn-magnet',
        'torrent': 'btn-torrent',
        'direct': 'btn-direct'
    };
    return classes[type] || 'btn-primary';
}

function isStreamableResult(result) {
    return ['Movie', 'TV Show', 'Anime'].includes(result.contentType) || 
           result.source === 'YTS' || result.source === 'EZTV' ||
           (result.name && (result.name.match(/\.(mp4|mkv|avi|webm)/i) || 
            result.name.match(/(720p|1080p|2160p|4k|bluray|webrip|web-dl)/i)));
}

// ═══════════════════════════════════════════════════════════════════════════════
// LOADING & PROGRESS
// ═══════════════════════════════════════════════════════════════════════════════
function showLoading() {
    const container = document.getElementById('resultsContainer');
    const section = document.getElementById('resultsSection');
    
    if (section) section.style.display = 'block';
    
    if (container) {
        container.innerHTML = `
            <div class="loading-container">
                <div class="loading-content">
                    <div class="spinner-advanced">
                        <div class="spinner-ring"></div>
                        <div class="spinner-ring"></div>
                        <div class="spinner-ring"></div>
                        <div class="spinner-core"></div>
                    </div>
                    <h3 id="loadingTitle">Searching...</h3>
                    <p id="loadingMessage">Connecting to sources</p>
                    <div class="loading-progress">
                        <div class="loading-progress-bar" id="progressBar" style="width: 0%"></div>
                    </div>
                    <div class="loading-percent" id="loadingPercent">0%</div>
                    <div class="loading-sources" id="loadingSources"></div>
                </div>
            </div>
        `;
    }
    
    // Hide filter bar during loading
    const filterBar = document.getElementById('filterBar');
    if (filterBar) filterBar.classList.remove('visible');
    
    // Hide pagination
    const paginationContainer = document.getElementById('paginationContainer');
    if (paginationContainer) paginationContainer.innerHTML = '';
}

function updateProgress(percent, message, source, found) {
    const progressBar = document.getElementById('progressBar');
    const percentText = document.getElementById('loadingPercent');
    const messageEl = document.getElementById('loadingMessage');
    const sourcesEl = document.getElementById('loadingSources');
    
    if (progressBar) progressBar.style.width = `${percent}%`;
    if (percentText) percentText.textContent = `${percent}%`;
    if (messageEl) messageEl.textContent = message;
    
    if (sourcesEl && source) {
        const sourceColor = config.sourceColors[source] || '#6366f1';
        const existing = sourcesEl.querySelector(`[data-source="${source}"]`);
        if (!existing) {
            const badge = document.createElement('span');
            badge.className = 'loading-source';
            badge.dataset.source = source;
            badge.style.borderColor = sourceColor;
            badge.innerHTML = `<span class="source-dot" style="background:${sourceColor}"></span> ${source}`;
            sourcesEl.appendChild(badge);
        } else {
            existing.classList.add('done');
        }
    }
    
    if (found > 0) {
        const title = document.getElementById('loadingTitle');
        if (title) title.textContent = `Found ${found} results...`;
    }
}

function hideLoading() {
    // Loading container will be replaced by results
}

function showFilterBar() {
    const filterBar = document.getElementById('filterBar');
    if (filterBar) filterBar.classList.add('visible');
}

function showNoResults() {
    const container = document.getElementById('resultsContainer');
    if (container) {
        container.innerHTML = `
            <div class="no-results">
                <div class="no-results-icon">🔍</div>
                <h3>No Results Found</h3>
                <p>We couldn't find anything for "${escapeHtml(state.query)}"</p>
                <div class="no-results-suggestions">
                    <p>Try:</p>
                    <ul>
                        <li>Check your spelling</li>
                        <li>Use different keywords</li>
                        <li>Try a different category</li>
                        <li>Search for a more popular title</li>
                    </ul>
                </div>
            </div>
        `;
    }
}

function showError(message) {
    hideLoading();
    const container = document.getElementById('resultsContainer');
    if (container) {
        container.innerHTML = `
            <div class="error-container">
                <div class="error-icon">❌</div>
                <h3>Search Error</h3>
                <p>${escapeHtml(message)}</p>
                <button class="btn btn-primary" onclick="performSearch()">Try Again</button>
            </div>
        `;
    }
    showToast(message, 'error');
}

// ═══════════════════════════════════════════════════════════════════════════════
// FILTERING & SORTING
// ═══════════════════════════════════════════════════════════════════════════════
function applyFilters() {
    state.filters.quality = document.getElementById('filterQuality')?.value || 'all';
    state.filters.type = document.getElementById('filterType')?.value || 'all';
    
    // Re-render with filters applied
    renderFilteredResults();
}

function applySort() {
    state.sort = document.getElementById('sortBy')?.value || 'seeds';
    renderFilteredResults();
}

function renderFilteredResults() {
    let filtered = [...state.results];
    
    // Apply quality filter
    if (state.filters.quality !== 'all') {
        filtered = filtered.filter(r => r.quality === state.filters.quality);
    }
    
    // Apply type filter
    if (state.filters.type !== 'all') {
        filtered = filtered.filter(r => r.contentType === state.filters.type);
    }
    
    // Apply sorting
    filtered.sort((a, b) => {
        switch (state.sort) {
            case 'seeds':
                return (b.seeds || 0) - (a.seeds || 0);
            case 'size':
                return (b.sizeBytes || 0) - (a.sizeBytes || 0);
            case 'name':
                return (a.name || '').localeCompare(b.name || '');
            default:
                return 0;
        }
    });
    
    // Render filtered results
    const container = document.getElementById('resultsContainer');
    if (!container) return;
    
    if (filtered.length === 0) {
        container.innerHTML = `
            <div class="no-results">
                <h3>No results match your filters</h3>
                <button class="btn btn-secondary" onclick="clearFilters()">Clear Filters</button>
            </div>
        `;
        return;
    }
    
    let html = '<div class="results-cards">';
    filtered.forEach((result, index) => {
        // Find original index
        const originalIndex = state.results.findIndex(r => r.id === result.id);
        html += renderResultCard(result, originalIndex);
    });
    html += '</div>';
    
    container.innerHTML = html;
    
    // Animate
    requestAnimationFrame(() => {
        container.querySelectorAll('.result-card').forEach((card, i) => {
            setTimeout(() => card.classList.add('visible'), i * 50);
        });
    });
}

function clearFilters() {
    document.getElementById('filterQuality').value = 'all';
    document.getElementById('filterType').value = 'all';
    document.getElementById('sortBy').value = 'seeds';
    state.filters = { quality: 'all', type: 'all', minSeeds: 0 };
    state.sort = 'seeds';
    renderResults();
}

// ═══════════════════════════════════════════════════════════════════════════════
// CATEGORY
// ═══════════════════════════════════════════════════════════════════════════════
function setCategory(category) {
    state.category = category;
    
    // Update UI
    document.querySelectorAll('.category-btn').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.category === category);
    });
    
    // Re-search if there's a query
    if (state.query) {
        performSearch();
    }
}

// ═══════════════════════════════════════════════════════════════════════════════
// QUICK SEARCH
// ═══════════════════════════════════════════════════════════════════════════════
function quickSearch(query) {
    document.getElementById('searchInput').value = query;
    performSearch();
}

// ═══════════════════════════════════════════════════════════════════════════════
// RECENT SEARCHES
// ═══════════════════════════════════════════════════════════════════════════════
function loadRecentSearches() {
    try {
        const recent = JSON.parse(localStorage.getItem('recentSearches') || '[]');
        if (recent.length === 0) return;
        
        const container = document.getElementById('recentSearches');
        if (!container) return;
        
        let html = '<span class="recent-label">🕐 Recent:</span>';
        recent.slice(0, 5).forEach(q => {
            html += `<span class="recent-tag" onclick="quickSearch('${escapeHtml(q)}')">${escapeHtml(q)}</span>`;
        });
        html += `<span class="recent-clear" onclick="clearRecentSearches()">Clear</span>`;
        
        container.innerHTML = html;
        container.classList.add('visible');
    } catch (e) {}
}

function saveRecentSearch(query) {
    try {
        let recent = JSON.parse(localStorage.getItem('recentSearches') || '[]');
        recent = recent.filter(q => q !== query);
        recent.unshift(query);
        recent = recent.slice(0, 10);
        localStorage.setItem('recentSearches', JSON.stringify(recent));
        loadRecentSearches();
    } catch (e) {}
}

function clearRecentSearches() {
    localStorage.removeItem('recentSearches');
    const container = document.getElementById('recentSearches');
    if (container) {
        container.classList.remove('visible');
        container.innerHTML = '';
    }
}

// ═══════════════════════════════════════════════════════════════════════════════
// CLIPBOARD & COPY
// ═══════════════════════════════════════════════════════════════════════════════
function copyMagnet(index) {
    const result = state.results[index];
    if (!result || !result.downloadMethods) return;
    
    const magnetMethod = result.downloadMethods.find(m => m.type === 'magnet');
    if (magnetMethod) {
        copyToClipboard(magnetMethod.url);
    }
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showToast('Copied to clipboard!', 'success');
    }).catch(() => {
        // Fallback
        const ta = document.createElement('textarea');
        ta.value = text;
        document.body.appendChild(ta);
        ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
        showToast('Copied to clipboard!', 'success');
    });
}

function copyAllMagnets() {
    const magnets = state.results
        .filter(r => r.downloadMethods)
        .map(r => r.downloadMethods.find(m => m.type === 'magnet')?.url)
        .filter(Boolean);
    
    if (magnets.length > 0) {
        copyToClipboard(magnets.join('\n'));
        showToast(`Copied ${magnets.length} magnet links!`, 'success');
    } else {
        showToast('No magnet links to copy', 'warning');
    }
}

// ═══════════════════════════════════════════════════════════════════════════════
// EXPORT
// ═══════════════════════════════════════════════════════════════════════════════
function exportResults() {
    if (state.results.length === 0) {
        showToast('No results to export', 'warning');
        return;
    }
    
    const data = {
        query: state.query,
        category: state.category,
        exportedAt: new Date().toISOString(),
        totalResults: state.results.length,
        results: state.results.map(r => ({
            name: r.name,
            size: r.size,
            seeds: r.seeds,
            peers: r.peers,
            quality: r.quality,
            source: r.source,
            downloadMethods: r.downloadMethods,
            hash: r.hash
        }))
    };
    
    const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `search-${state.query.replace(/[^a-z0-9]/gi, '_')}-${Date.now()}.json`;
    a.click();
    URL.revokeObjectURL(url);
    
    showToast('Results exported!', 'success');
}

// ═══════════════════════════════════════════════════════════════════════════════
// MODALS
// ═══════════════════════════════════════════════════════════════════════════════
function toggleShortcutsModal() {
    const modal = document.getElementById('shortcutsModal');
    if (modal) {
        modal.classList.toggle('active');
        document.body.style.overflow = modal.classList.contains('active') ? 'hidden' : '';
    }
}

function closeAllModals() {
    document.querySelectorAll('.modal.active, .shortcuts-modal.active').forEach(m => {
        m.classList.remove('active');
    });
    document.body.style.overflow = '';
}

// ═══════════════════════════════════════════════════════════════════════════════
// TOAST NOTIFICATIONS
// ═══════════════════════════════════════════════════════════════════════════════
function showToast(message, type = 'info') {
    const container = document.getElementById('toastContainer');
    if (!container) return;
    
    const icons = {
        success: '✓',
        error: '✕',
        warning: '⚠',
        info: 'ℹ'
    };
    
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
        <span class="toast-icon">${icons[type]}</span>
        <span class="toast-message">${escapeHtml(message)}</span>
        <button class="toast-close" onclick="this.parentElement.remove()">×</button>
    `;
    
    container.appendChild(toast);
    
    setTimeout(() => toast.classList.add('show'), 10);
    
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

// ═══════════════════════════════════════════════════════════════════════════════
// UTILITY FUNCTIONS
// ═══════════════════════════════════════════════════════════════════════════════
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatNumber(num) {
    if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M';
    if (num >= 1000) return (num / 1000).toFixed(1) + 'K';
    return num.toString();
}

function capitalizeFirst(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

function getSeedClass(seeds) {
    if (seeds >= 100) return 'seed-excellent';
    if (seeds >= 30) return 'seed-good';
    if (seeds >= 10) return 'seed-fair';
    if (seeds >= 1) return 'seed-low';
    return 'seed-dead';
}

function getTypeIcon(type) {
    const icons = {
        'Movie': '🎬',
        'TV Show': '📺',
        'Game': '🎮',
        'Software': '💻',
        'Anime': '🎌',
        'Music': '🎵',
        'Ebook': '📚'
    };
    return icons[type] || '📁';
}

function adjustColor(color, amount) {
    const clamp = (num) => Math.min(255, Math.max(0, num));
    
    let hex = color.replace('#', '');
    if (hex.length === 3) {
        hex = hex.split('').map(c => c + c).join('');
    }
    
    const num = parseInt(hex, 16);
    const r = clamp((num >> 16) + amount);
    const g = clamp(((num >> 8) & 0x00FF) + amount);
    const b = clamp((num & 0x0000FF) + amount);
    
    return '#' + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1);
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// ═══════════════════════════════════════════════════════════════════════════════
// AI ANALYSIS - Legend House AI Integration
// ═══════════════════════════════════════════════════════════════════════════════

/**
 * Analyze a torrent result using AI
 */
async function analyzeWithAI(index) {
    const result = state.results[index];
    if (!result) {
        showToast('Result not found', 'error');
        return;
    }
    
    // Check if AI chat widget exists and is initialized
    if (window.legendAI) {
        // Open the AI chat if not already open
        if (!window.legendAI.isOpen) {
            window.legendAI.toggleChat();
        }
        
        // Send the analysis request
        const analysisPrompt = `Please analyze this torrent for me:\n\nName: ${result.name}\nSize: ${result.size || 'Unknown'}\nQuality: ${result.quality || 'Unknown'}\nSeeds: ${result.seeds || 0}\nPeers: ${result.peers || 0}\nSource: ${result.source || 'Unknown'}\nType: ${result.contentType || 'Unknown'}\n\nPlease tell me:\n1. Is this a good quality release?\n2. Any potential concerns?\n3. Recommendations for similar content`;
        
        // Set the message in the input
        const inputEl = document.getElementById('ai-chat-input');
        if (inputEl) {
            inputEl.value = analysisPrompt;
            inputEl.dispatchEvent(new Event('input'));
            
            // Trigger send after a short delay
            setTimeout(() => {
                window.legendAI.sendMessage();
            }, 100);
        }
        
        showToast('🤖 Analyzing with AI...', 'info');
    } else {
        // AI not available - show fallback analysis
        showFallbackAnalysis(result);
    }
}

/**
 * Show fallback analysis when AI is not available
 */
function showFallbackAnalysis(result) {
    const modal = document.getElementById('downloadModal');
    const content = document.getElementById('downloadModalContent');
    
    if (!modal || !content) {
        showToast('AI analysis not available. Please login to use AI features.', 'warning');
        return;
    }
    
    // Quality assessment
    const qualityScore = assessQuality(result);
    const healthInfo = getHealthInfo(result);
    
    content.innerHTML = `
        <div class="modal-header">
            <h3>🤖 Quick Analysis</h3>
            <button class="modal-close" onclick="closeDownloadModal()">×</button>
        </div>
        <div class="modal-body">
            <div class="analysis-title">
                <h4>${escapeHtml(result.name)}</h4>
            </div>
            
            <div class="analysis-section">
                <h5>📊 Quality Assessment</h5>
                <div class="analysis-score">
                    <span class="score-label">Overall Score:</span>
                    <span class="score-value" style="color: ${qualityScore.color}">${qualityScore.score}/100</span>
                </div>
                <p>${qualityScore.message}</p>
            </div>
            
            <div class="analysis-section">
                <h5>🏥 Health Status</h5>
                <p>${healthInfo}</p>
            </div>
            
            <div class="analysis-section">
                <h5>📦 File Details</h5>
                <ul style="margin: 0; padding-left: 1.5rem;">
                    <li><strong>Size:</strong> ${result.size || 'Unknown'}</li>
                    <li><strong>Quality:</strong> ${result.quality || 'Unknown'}</li>
                    <li><strong>Type:</strong> ${result.contentType || 'Unknown'}</li>
                    <li><strong>Source:</strong> ${result.source || 'Unknown'}</li>
                </ul>
            </div>
            
            <div class="analysis-section" style="background: #fff3cd; padding: 1rem; border-radius: 8px; margin-top: 1rem;">
                <p style="margin: 0; font-size: 0.9rem;">
                    💡 <strong>Tip:</strong> Login to access full AI-powered analysis with recommendations, 
                    safety checks, and similar content suggestions.
                </p>
            </div>
        </div>
    `;
    
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

/**
 * Assess quality of a torrent
 */
function assessQuality(result) {
    let score = 50;
    let messages = [];
    
    // Seeds assessment
    const seeds = result.seeds || 0;
    if (seeds >= 100) {
        score += 20;
        messages.push('Excellent seed count');
    } else if (seeds >= 30) {
        score += 15;
        messages.push('Good seed count');
    } else if (seeds >= 10) {
        score += 10;
        messages.push('Moderate seed count');
    } else if (seeds >= 1) {
        score += 5;
        messages.push('Low seed count - may be slow');
    } else {
        score -= 10;
        messages.push('No seeds - download may not work');
    }
    
    // Quality assessment
    const quality = (result.quality || '').toUpperCase();
    if (quality.includes('4K') || quality.includes('2160')) {
        score += 15;
        messages.push('Excellent 4K quality');
    } else if (quality.includes('1080')) {
        score += 12;
        messages.push('Full HD quality');
    } else if (quality.includes('720')) {
        score += 8;
        messages.push('HD quality');
    } else if (quality.includes('BLURAY')) {
        score += 12;
        messages.push('BluRay source');
    }
    
    // Verified status
    if (result.verified) {
        score += 10;
        messages.push('Verified uploader');
    }
    
    // Cap score
    score = Math.min(100, Math.max(0, score));
    
    // Determine color
    let color = '#10b981'; // green
    if (score < 50) color = '#ef4444'; // red
    else if (score < 70) color = '#f59e0b'; // yellow
    
    return {
        score: score,
        color: color,
        message: messages.join('. ') + '.'
    };
}

/**
 * Get health information for a torrent
 */
function getHealthInfo(result) {
    const seeds = result.seeds || 0;
    const peers = result.peers || 0;
    const ratio = seeds > 0 ? (seeds / Math.max(1, peers)).toFixed(2) : 0;
    
    let status = '';
    if (seeds >= 100) {
        status = '🟢 Excellent - Very fast download expected';
    } else if (seeds >= 30) {
        status = '🟢 Good - Fast download expected';
    } else if (seeds >= 10) {
        status = '🟡 Fair - Moderate download speed expected';
    } else if (seeds >= 1) {
        status = '🟠 Low - Slow download, may take time';
    } else {
        status = '🔴 Dead - No seeders, download unlikely to complete';
    }
    
    return `${status}<br><small>Seeds: ${seeds} | Peers: ${peers} | Ratio: ${ratio}</small>`;
}

// Expose functions globally
window.performSearch = performSearch;
window.goToPage = goToPage;
window.setCategory = setCategory;
window.quickSearch = quickSearch;
window.openDownloadModal = openDownloadModal;
window.closeDownloadModal = closeDownloadModal;
window.copyMagnet = copyMagnet;
window.copyToClipboard = copyToClipboard;
window.copyAllMagnets = copyAllMagnets;
window.exportResults = exportResults;
window.toggleShortcutsModal = toggleShortcutsModal;
window.clearFilters = clearFilters;
window.clearRecentSearches = clearRecentSearches;
window.applyFilters = applyFilters;
window.applySort = applySort;
// Streaming functions
window.streamTorrent = streamTorrent;
window.closeStreamModal = closeStreamModal;
window.toggleFullscreen = toggleFullscreen;
window.copyStreamLink = copyStreamLink;
// AI Analysis function
window.analyzeWithAI = analyzeWithAI;

// ═══════════════════════════════════════════════════════════════════════════════
// WEBTORRENT STREAMING - LEGEND HOUSE EXCLUSIVE
// ═══════════════════════════════════════════════════════════════════════════════

/**
 * Initialize WebTorrent client
 */
function initWebTorrent() {
    if (!state.webTorrentClient && typeof WebTorrent !== 'undefined') {
        state.webTorrentClient = new WebTorrent();
        console.log('🎬 Legend House: WebTorrent client initialized');
    }
    return state.webTorrentClient;
}

/**
 * Stream a torrent directly in the browser
 */
function streamTorrent(index) {
    const result = state.results[index];
    if (!result) {
        showToast('Result not found', 'error');
        return;
    }
    
    // Find magnet link
    const magnetMethod = result.downloadMethods?.find(m => m.type === 'magnet');
    if (!magnetMethod) {
        showToast('No magnet link available for streaming', 'error');
        return;
    }
    
    const magnetUrl = magnetMethod.url;
    
    // Show stream modal
    const modal = document.getElementById('streamModal');
    const loadingEl = document.getElementById('streamLoading');
    const playerEl = document.getElementById('streamPlayer');
    const titleEl = document.getElementById('streamTitle');
    const statusEl = document.getElementById('streamStatus');
    const progressBarEl = document.getElementById('streamProgressBar');
    const peersEl = document.getElementById('streamPeers');
    const speedEl = document.getElementById('streamSpeed');
    const progressEl = document.getElementById('streamProgress');
    const qualityEl = document.getElementById('streamQuality');
    const sizeEl = document.getElementById('streamSize');
    const sourceEl = document.getElementById('streamSource');
    
    if (!modal) return;
    
    // Reset UI
    if (titleEl) titleEl.textContent = result.name;
    if (statusEl) statusEl.textContent = 'Initializing WebTorrent...';
    if (progressBarEl) progressBarEl.style.width = '0%';
    if (loadingEl) loadingEl.style.display = 'flex';
    if (playerEl) {
        playerEl.style.display = 'none';
        playerEl.src = '';
    }
    if (qualityEl) qualityEl.textContent = result.quality || '-';
    if (sizeEl) sizeEl.textContent = result.size || '-';
    if (sourceEl) sourceEl.textContent = result.source || '-';
    
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
    state.isStreaming = true;
    
    // Initialize WebTorrent
    const client = initWebTorrent();
    if (!client) {
        if (statusEl) statusEl.textContent = 'WebTorrent not supported in this browser';
        showToast('WebTorrent not available. Try Chrome or Firefox.', 'error');
        return;
    }
    
    // Remove any existing torrents
    if (state.currentTorrent) {
        state.currentTorrent.destroy();
        state.currentTorrent = null;
    }
    
    if (statusEl) statusEl.textContent = 'Connecting to peers...';
    
    // Add torrent
    client.add(magnetUrl, { announce: getTrackers() }, (torrent) => {
        state.currentTorrent = torrent;
        console.log('🎬 Torrent added:', torrent.name);
        
        // Find video file
        const videoFile = torrent.files.find(file => {
            const ext = file.name.toLowerCase();
            return ext.endsWith('.mp4') || ext.endsWith('.mkv') || 
                   ext.endsWith('.avi') || ext.endsWith('.webm') ||
                   ext.endsWith('.mov') || ext.endsWith('.m4v');
        });
        
        if (!videoFile) {
            if (statusEl) statusEl.textContent = 'No video file found in torrent';
            showToast('No video file found. This torrent may not be streamable.', 'warning');
            return;
        }
        
        if (statusEl) statusEl.textContent = `Found: ${videoFile.name}`;
        
        // Update progress
        const updateInterval = setInterval(() => {
            if (!state.isStreaming) {
                clearInterval(updateInterval);
                return;
            }
            
            const progress = Math.round(torrent.progress * 100);
            const downloadSpeed = formatBytes(torrent.downloadSpeed) + '/s';
            const peers = torrent.numPeers;
            
            if (progressBarEl) progressBarEl.style.width = `${progress}%`;
            if (peersEl) peersEl.textContent = `Peers: ${peers}`;
            if (speedEl) speedEl.textContent = `Speed: ${downloadSpeed}`;
            if (progressEl) progressEl.textContent = `Progress: ${progress}%`;
            
            if (progress >= 100) {
                clearInterval(updateInterval);
            }
        }, 1000);
        
        // Start streaming to video element
        if (statusEl) statusEl.textContent = 'Starting stream...';
        
        // Use render method for streaming
        videoFile.renderTo(playerEl, { autoplay: true }, (err) => {
            if (err) {
                console.error('Render error:', err);
                if (statusEl) statusEl.textContent = 'Error rendering video: ' + err.message;
                
                // Fallback: try blob URL
                videoFile.getBlobURL((err, url) => {
                    if (err) {
                        showToast('Failed to stream video: ' + err.message, 'error');
                        return;
                    }
                    if (playerEl) {
                        playerEl.src = url;
                        playerEl.style.display = 'block';
                        if (loadingEl) loadingEl.style.display = 'none';
                        playerEl.play();
                    }
                });
                return;
            }
            
            // Success
            if (loadingEl) loadingEl.style.display = 'none';
            if (playerEl) playerEl.style.display = 'block';
            
            showToast('🎬 Streaming started!', 'success');
        });
        
        // Handle errors
        torrent.on('error', (err) => {
            console.error('Torrent error:', err);
            if (statusEl) statusEl.textContent = 'Error: ' + err.message;
        });
        
        torrent.on('warning', (warn) => {
            console.warn('Torrent warning:', warn);
        });
    });
}

/**
 * Close stream modal and cleanup
 */
function closeStreamModal() {
    const modal = document.getElementById('streamModal');
    const playerEl = document.getElementById('streamPlayer');
    
    if (modal) modal.classList.remove('active');
    if (playerEl) {
        playerEl.pause();
        playerEl.src = '';
    }
    
    state.isStreaming = false;
    document.body.style.overflow = '';
    
    // Cleanup torrent
    if (state.currentTorrent) {
        state.currentTorrent.destroy();
        state.currentTorrent = null;
    }
}

/**
 * Toggle fullscreen for video player
 */
function toggleFullscreen() {
    const player = document.getElementById('streamPlayer');
    if (!player) return;
    
    if (!document.fullscreenElement) {
        player.requestFullscreen().catch(err => {
            showToast('Fullscreen not available', 'warning');
        });
    } else {
        document.exitFullscreen();
    }
}

/**
 * Copy current stream magnet link
 */
function copyStreamLink() {
    if (state.currentTorrent) {
        copyToClipboard(state.currentTorrent.magnetURI);
    } else {
        showToast('No active stream', 'warning');
    }
}

/**
 * Get tracker list for WebTorrent
 */
function getTrackers() {
    return [
        'wss://tracker.openwebtorrent.com',
        'wss://tracker.btorrent.xyz',
        'wss://tracker.fastcast.nz',
        'udp://tracker.opentrackr.org:1337/announce',
        'udp://open.stealth.si:80/announce',
        'udp://tracker.torrent.eu.org:451/announce',
        'udp://tracker.tiny-vps.com:6969/announce',
        'udp://open.demonii.com:1337/announce'
    ];
}

/**
 * Format bytes to human readable
 */
function formatBytes(bytes, decimals = 2) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
}
