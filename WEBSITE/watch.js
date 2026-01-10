/**
 * ═══════════════════════════════════════════════════════════════════════════════
 * LEGEND HOUSE - Watch Page v9.0
 * WebTorrent Streaming Engine
 * ═══════════════════════════════════════════════════════════════════════════════
 */

// State
let client = null;
let currentTorrent = null;
let currentMagnet = '';
let updateInterval = null;
let connectionTimeout = null;
let peerCheckInterval = null;

// Extended list of WebTorrent trackers (WebSocket-based for browser support)
const trackers = [
    // Primary WebTorrent trackers
    'wss://tracker.openwebtorrent.com',
    'wss://tracker.webtorrent.dev',
    'wss://tracker.btorrent.xyz',
    'wss://tracker.fastcast.nz',
    // Additional WebSocket trackers
    'wss://tracker.files.fm:7073/announce',
    'wss://spacetradersapi-chatbox.herokuapp.com:443/announce',
    'wss://peertube.cpy.re:443/tracker/socket',
    // UDP trackers (converted to announce URLs for metadata)
    'udp://tracker.opentrackr.org:1337/announce',
    'udp://open.stealth.si:80/announce',
    'udp://tracker.torrent.eu.org:451/announce',
    'udp://tracker.openbittorrent.com:6969/announce',
    'udp://open.demonii.com:1337/announce',
    'udp://explodie.org:6969/announce',
    'udp://exodus.desync.com:6969/announce'
];

// Connection timeout settings
const CONNECTION_TIMEOUT_MS = 45000; // 45 seconds
const PEER_CHECK_DELAY_MS = 15000; // 15 seconds before showing warning

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    initWallpapers();
    
    // Check for magnet in URL
    const params = new URLSearchParams(window.location.search);
    const magnet = params.get('magnet');
    if (magnet) {
        document.getElementById('magnetInput').value = magnet;
        startStream();
    }
});

// Wallpaper rotation
function initWallpapers() {
    const container = document.getElementById('wallpaperContainer');
    if (!container) return;
    
    const wallpapers = [
        'https://images.unsplash.com/photo-1534796636912-3b95b3ab5986?w=1920&q=80',
        'https://images.unsplash.com/photo-1519681393784-d120267933ba?w=1920&q=80',
        'https://images.unsplash.com/photo-1507400492013-162706c8c05e?w=1920&q=80'
    ];
    
    let current = 0;
    
    wallpapers.forEach((url, i) => {
        const div = document.createElement('div');
        div.className = `wallpaper ${i === 0 ? 'active' : ''}`;
        div.style.backgroundImage = `url(${url})`;
        container.appendChild(div);
    });
    
    setInterval(() => {
        const walls = container.querySelectorAll('.wallpaper');
        walls[current].classList.remove('active');
        current = (current + 1) % walls.length;
        walls[current].classList.add('active');
    }, 10000);
}

// Start streaming
function startStream() {
    const magnetInput = document.getElementById('magnetInput');
    const magnet = magnetInput.value.trim();
    
    if (!magnet) {
        showToast('Please enter a magnet link', 'warning');
        return false;
    }
    
    if (!magnet.startsWith('magnet:')) {
        showToast('Invalid magnet link format', 'error');
        return false;
    }
    
    currentMagnet = magnet;
    
    // Check WebTorrent support
    if (typeof WebTorrent === 'undefined') {
        showToast('WebTorrent is not supported in this browser', 'error');
        return false;
    }
    
    // Show player section
    const heroSection = document.getElementById('watchHero');
    const playerSection = document.getElementById('playerSection');
    const fileListSection = document.getElementById('fileListSection');
    
    if (heroSection) heroSection.style.display = 'none';
    if (playerSection) playerSection.classList.add('active');
    
    // Reset UI
    resetPlayerUI();
    
    // Clear any existing timeouts
    clearConnectionTimeouts();
    
    // Initialize WebTorrent client with better options
    if (!client) {
        client = new WebTorrent({
            tracker: {
                rtcConfig: {
                    iceServers: [
                        { urls: 'stun:stun.l.google.com:19302' },
                        { urls: 'stun:stun1.l.google.com:19302' },
                        { urls: 'stun:stun2.l.google.com:19302' },
                        { urls: 'stun:global.stun.twilio.com:3478' }
                    ]
                }
            }
        });
        console.log('🎬 Legend House: WebTorrent initialized');
    }
    
    // Destroy existing torrent
    if (currentTorrent) {
        currentTorrent.destroy();
        currentTorrent = null;
    }
    
    updateStatus('Connecting to peers...');
    updateLoadingTitle('Searching for peers');
    
    // Set up peer check warning (after 15 seconds with no peers)
    peerCheckInterval = setTimeout(() => {
        if (currentTorrent && currentTorrent.numPeers === 0) {
            updateLoadingTitle('Still searching for peers...');
            updateStatus('This may take longer if there are few seeders online. WebTorrent can only connect to other browser-based peers.');
            showToast('Searching for browser peers... This torrent may have limited WebTorrent seeders.', 'warning');
        }
    }, PEER_CHECK_DELAY_MS);
    
    // Set up connection timeout (45 seconds)
    connectionTimeout = setTimeout(() => {
        if (currentTorrent && currentTorrent.numPeers === 0 && currentTorrent.progress === 0) {
            updateLoadingTitle('Connection timeout');
            updateStatus('No peers found. This torrent may not have any WebTorrent seeders online.');
            showConnectionHelp();
        }
    }, CONNECTION_TIMEOUT_MS);
    
    // Add torrent with extended tracker list
    try {
        client.add(magnet, { announce: trackers }, (torrent) => {
            currentTorrent = torrent;
            console.log('🎬 Torrent loaded:', torrent.name);
            
            // Clear timeout on successful metadata
            clearConnectionTimeouts();
            
            // Start progress updates
            startProgressUpdates();
            
            // Show file list
            showFileList(torrent);
            
            // Set up peer event handlers
            torrent.on('wire', () => {
                updateLoadingTitle('Connected to peers!');
                updateStatus('Buffering video...');
            });
            
            torrent.on('download', () => {
                if (torrent.progress > 0.01) {
                    updateStatus('Streaming in progress...');
                }
            });
            
            // Auto-play first video file
            const videoFile = findVideoFile(torrent);
            if (videoFile) {
                playFile(videoFile);
            } else {
                updateStatus('No video files found. Select a file to play.');
                showToast('No video files detected in this torrent', 'warning');
            }
        });
        
        client.on('error', (err) => {
            console.error('WebTorrent error:', err);
            clearConnectionTimeouts();
            updateStatus('Error: ' + err.message);
            showToast('Streaming error: ' + err.message, 'error');
        });
        
    } catch (err) {
        console.error('Failed to add torrent:', err);
        clearConnectionTimeouts();
        updateStatus('Failed to process magnet link');
        showToast('Invalid magnet link or connection error', 'error');
    }
    
    return false;
}

// Clear connection timeouts
function clearConnectionTimeouts() {
    if (connectionTimeout) {
        clearTimeout(connectionTimeout);
        connectionTimeout = null;
    }
    if (peerCheckInterval) {
        clearTimeout(peerCheckInterval);
        peerCheckInterval = null;
    }
}

// Update loading title
function updateLoadingTitle(text) {
    const title = document.getElementById('loadingTitle');
    if (title) title.textContent = text;
}

// Show connection help message
function showConnectionHelp() {
    const loading = document.getElementById('playerLoading');
    if (!loading) return;
    
    loading.innerHTML = `
        <div style="text-align: center; max-width: 500px;">
            <div style="font-size: 64px; margin-bottom: 20px;">⚠️</div>
            <h3 style="margin-bottom: 16px; color: var(--primary);">No Peers Available</h3>
            <p style="color: var(--text-secondary); margin-bottom: 24px; line-height: 1.6;">
                WebTorrent in browsers can only connect to other browser-based peers (using WebRTC). 
                This torrent may not have any WebTorrent seeders online right now.
            </p>
            <div style="background: var(--bg-glass); border-radius: 12px; padding: 20px; margin-bottom: 24px; text-align: left;">
                <p style="font-weight: 600; margin-bottom: 12px; color: var(--text-primary);">💡 What you can do:</p>
                <ul style="color: var(--text-secondary); margin: 0; padding-left: 20px; line-height: 1.8;">
                    <li>Try a more popular torrent with more seeders</li>
                    <li>Copy the magnet link and use a desktop torrent client</li>
                    <li>Try again later when more peers might be online</li>
                    <li>Search for an alternative source on our home page</li>
                </ul>
            </div>
            <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
                <button onclick="retryStream()" class="player-btn player-btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M1 4v6h6"></path>
                        <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path>
                    </svg>
                    Retry
                </button>
                <button onclick="copyMagnetLink()" class="player-btn player-btn-outline">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                    </svg>
                    Copy Magnet
                </button>
                <button onclick="stopStream()" class="player-btn player-btn-stop">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                    Go Back
                </button>
            </div>
        </div>
    `;
}

// Retry stream
function retryStream() {
    if (currentMagnet) {
        resetPlayerUI();
        document.getElementById('magnetInput').value = currentMagnet;
        startStream();
    }
}

// Find video file in torrent
function findVideoFile(torrent) {
    const videoExtensions = ['.mp4', '.mkv', '.avi', '.webm', '.mov', '.m4v'];
    
    // Sort by size (largest first, usually the main video)
    const sortedFiles = [...torrent.files].sort((a, b) => b.length - a.length);
    
    return sortedFiles.find(file => {
        const name = file.name.toLowerCase();
        return videoExtensions.some(ext => name.endsWith(ext));
    });
}

// Play a specific file
function playFile(file) {
    const loading = document.getElementById('playerLoading');
    const video = document.getElementById('playerVideo');
    const fileName = document.getElementById('playingFileName');
    const fileSize = document.getElementById('playingFileSize');
    
    if (!file || !video) return;
    
    updateStatus('Loading: ' + file.name);
    
    // Update file info
    if (fileName) fileName.textContent = file.name;
    if (fileSize) fileSize.textContent = formatBytes(file.length);
    
    // Render to video element
    file.renderTo(video, { autoplay: true, controls: true }, (err) => {
        if (err) {
            console.error('Render error:', err);
            
            // Fallback to blob URL
            file.getBlobURL((blobErr, url) => {
                if (blobErr) {
                    showToast('Failed to stream: ' + blobErr.message, 'error');
                    return;
                }
                
                video.src = url;
                video.style.display = 'block';
                if (loading) loading.style.display = 'none';
                video.play();
            });
            return;
        }
        
        // Success
        if (loading) loading.style.display = 'none';
        video.style.display = 'block';
        showToast('🎬 Streaming started!', 'success');
    });
}

// Show file list
function showFileList(torrent) {
    const section = document.getElementById('fileListSection');
    const list = document.getElementById('fileList');
    
    if (!section || !list) return;
    
    const videoExtensions = ['.mp4', '.mkv', '.avi', '.webm', '.mov', '.m4v', '.ts', '.wmv', '.flv'];
    
    const videoFiles = torrent.files.filter(file => {
        const name = file.name.toLowerCase();
        return videoExtensions.some(ext => name.endsWith(ext));
    });
    
    if (videoFiles.length <= 1) {
        section.classList.remove('active');
        return;
    }
    
    section.classList.add('active');
    
    list.innerHTML = videoFiles.map((file, index) => `
        <div class="file-item">
            <div class="file-item-info">
                <span class="file-icon">${getFileIcon(file.name)}</span>
                <div class="file-details">
                    <div class="file-name">${escapeHtml(file.name)}</div>
                    <div class="file-size">${formatBytes(file.length)}</div>
                </div>
            </div>
            <button class="file-play-btn" onclick="playFileByIndex(${index})">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                    <polygon points="5 3 19 12 5 21 5 3"/>
                </svg>
                Play
            </button>
        </div>
    `).join('');
}

// Play file by index
function playFileByIndex(index) {
    if (!currentTorrent) return;
    
    const videoExtensions = ['.mp4', '.mkv', '.avi', '.webm', '.mov', '.m4v', '.ts', '.wmv', '.flv'];
    const videoFiles = currentTorrent.files.filter(file => {
        const name = file.name.toLowerCase();
        return videoExtensions.some(ext => name.endsWith(ext));
    });
    
    if (videoFiles[index]) {
        // Show loading again
        const loading = document.getElementById('playerLoading');
        const video = document.getElementById('playerVideo');
        if (loading) loading.style.display = 'flex';
        if (video) {
            video.style.display = 'none';
            video.pause();
            video.src = '';
        }
        
        playFile(videoFiles[index]);
    }
}

// Start progress updates
function startProgressUpdates() {
    if (updateInterval) clearInterval(updateInterval);
    
    updateInterval = setInterval(() => {
        if (!currentTorrent) return;
        
        const progress = Math.round(currentTorrent.progress * 100);
        const downloadSpeed = formatBytes(currentTorrent.downloadSpeed) + '/s';
        const peers = currentTorrent.numPeers;
        
        const progressBar = document.getElementById('progressBar');
        const statPeers = document.getElementById('statPeers');
        const statSpeed = document.getElementById('statSpeed');
        const statProgress = document.getElementById('statProgress');
        
        if (progressBar) progressBar.style.width = `${progress}%`;
        if (statPeers) statPeers.textContent = `Peers: ${peers}`;
        if (statSpeed) statSpeed.textContent = `Speed: ${downloadSpeed}`;
        if (statProgress) statProgress.textContent = `Progress: ${progress}%`;
        
        if (progress >= 100) {
            clearInterval(updateInterval);
        }
    }, 1000);
}

// Update status text
function updateStatus(text) {
    const status = document.getElementById('loadingStatus');
    const title = document.getElementById('loadingTitle');
    if (status) status.textContent = text;
}

// Reset player UI
function resetPlayerUI() {
    const loading = document.getElementById('playerLoading');
    const video = document.getElementById('playerVideo');
    const progressBar = document.getElementById('progressBar');
    
    // Restore loading UI if it was replaced
    if (loading) {
        loading.style.display = 'flex';
        loading.innerHTML = `
            <div class="loading-spinner"></div>
            <h3 id="loadingTitle">Connecting to peers...</h3>
            <p id="loadingStatus">Initializing WebTorrent</p>
            <div class="progress-bar-container">
                <div class="progress-bar-fill" id="progressBar"></div>
            </div>
            <div class="stats-row">
                <span id="statPeers">Peers: 0</span>
                <span id="statSpeed">Speed: 0 KB/s</span>
                <span id="statProgress">Progress: 0%</span>
            </div>
        `;
    }
    
    if (video) {
        video.style.display = 'none';
        video.pause();
        video.src = '';
    }
    
    updateStatus('Initializing...');
}

// Stop streaming
function stopStream() {
    // Clear all intervals and timeouts
    if (updateInterval) {
        clearInterval(updateInterval);
        updateInterval = null;
    }
    clearConnectionTimeouts();
    
    if (currentTorrent) {
        currentTorrent.destroy();
        currentTorrent = null;
    }
    
    // Reset UI
    const heroSection = document.getElementById('watchHero');
    const playerSection = document.getElementById('playerSection');
    const fileListSection = document.getElementById('fileListSection');
    const video = document.getElementById('playerVideo');
    
    if (heroSection) heroSection.style.display = 'block';
    if (playerSection) playerSection.classList.remove('active');
    if (fileListSection) fileListSection.classList.remove('active');
    if (video) {
        video.pause();
        video.src = '';
    }
    
    showToast('Streaming stopped', 'info');
}

// Toggle fullscreen
function toggleFullscreen() {
    const video = document.getElementById('playerVideo');
    if (!video) return;
    
    if (!document.fullscreenElement) {
        video.requestFullscreen().catch(err => {
            showToast('Fullscreen not available', 'warning');
        });
    } else {
        document.exitFullscreen();
    }
}

// Copy magnet link
function copyMagnetLink() {
    if (currentMagnet) {
        navigator.clipboard.writeText(currentMagnet).then(() => {
            showToast('Magnet link copied!', 'success');
        }).catch(() => {
            // Fallback
            const ta = document.createElement('textarea');
            ta.value = currentMagnet;
            document.body.appendChild(ta);
            ta.select();
            document.execCommand('copy');
            document.body.removeChild(ta);
            showToast('Magnet link copied!', 'success');
        });
    }
}

// Format bytes to human readable
function formatBytes(bytes, decimals = 2) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
}

// Get file icon based on extension
function getFileIcon(filename) {
    const ext = filename.toLowerCase().split('.').pop();
    const icons = {
        'mp4': '🎬',
        'mkv': '🎬',
        'avi': '🎬',
        'webm': '🎬',
        'mov': '🎬',
        'm4v': '🎬',
        'mp3': '🎵',
        'flac': '🎵',
        'wav': '🎵',
        'srt': '📝',
        'sub': '📝',
        'ass': '📝',
        'txt': '📄',
        'nfo': '📄',
        'jpg': '🖼️',
        'jpeg': '🖼️',
        'png': '🖼️'
    };
    return icons[ext] || '📁';
}

// Escape HTML
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Toast notification
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

// Global functions
window.startStream = startStream;
window.stopStream = stopStream;
window.toggleFullscreen = toggleFullscreen;
window.copyMagnetLink = copyMagnetLink;
window.playFileByIndex = playFileByIndex;
window.retryStream = retryStream;
