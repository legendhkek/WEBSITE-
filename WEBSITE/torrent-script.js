/**
 * ═══════════════════════════════════════════════════════════════════════════════
 * LEGEND HOUSE - Torrent Page Script v1.0
 * ═══════════════════════════════════════════════════════════════════════════════
 */

// Global variables
let currentFile = null;
let currentUser = null;

// Check authentication status
async function checkAuth() {
    try {
        const formData = new FormData();
        formData.append('action', 'check');
        
        const response = await fetch('auth.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.loggedIn && data.user) {
            currentUser = data.user;
            updateUserNav(data.user);
        } else {
            updateUserNav(null);
        }
    } catch (error) {
        console.error('Auth check failed:', error);
    }
}

// Update user navigation
function updateUserNav(user) {
    const userNav = document.getElementById('userNav');
    if (!userNav) return;
    
    if (user) {
        userNav.innerHTML = `
            <span class="nav-btn" style="pointer-events: none;">
                <span class="nav-btn-icon">👤</span>
                <span class="nav-btn-text">${escapeHtml(user.username)}</span>
            </span>
            <button class="nav-btn" onclick="logout()">
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
            <a href="signup.php" class="nav-btn nav-btn-featured" style="background: linear-gradient(135deg, var(--primary), var(--accent)); color: #fff;">
                <span class="nav-btn-icon">✨</span>
                <span class="nav-btn-text">Sign Up</span>
            </a>
        `;
    }
}

// Logout function
async function logout() {
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
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    checkAuth();
    setupFileDropZone();
});

// Tab switching
function switchTab(tabName) {
    // Update tab buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.tab === tabName);
    });
    
    // Update tab content
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active');
    });
    document.getElementById(`${tabName}-tab`).classList.add('active');
    
    // Hide result
    document.getElementById('downloadResult').style.display = 'none';
}

// File drop zone setup
function setupFileDropZone() {
    const dropZone = document.getElementById('dropZone');
    
    if (!dropZone) return;
    
    dropZone.addEventListener('click', () => {
        document.getElementById('fileInput').click();
    });
    
    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('drag-over');
    });
    
    dropZone.addEventListener('dragleave', () => {
        dropZone.classList.remove('drag-over');
    });
    
    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('drag-over');
        
        const files = e.dataTransfer.files;
        if (files.length > 0 && files[0].name.endsWith('.torrent')) {
            handleFileSelect({ target: { files: files } });
        } else {
            showToast('Please drop a .torrent file', 'error');
        }
    });
}

// Handle file selection
function handleFileSelect(event) {
    const file = event.target.files[0];
    
    if (!file) return;
    
    if (!file.name.endsWith('.torrent')) {
        showToast('Please select a .torrent file', 'error');
        return;
    }
    
    currentFile = file;
    
    // Read file and show info
    const reader = new FileReader();
    reader.onload = (e) => {
        try {
            const arrayBuffer = e.target.result;
            parseTorrentFile(arrayBuffer);
        } catch (error) {
            showToast('Failed to parse torrent file', 'error');
        }
    };
    reader.readAsArrayBuffer(file);
}

// Parse torrent file
function parseTorrentFile(arrayBuffer) {
    const fileInfo = document.getElementById('fileInfo');
    const downloadBtn = document.getElementById('fileDownloadBtn');
    
    // Show file info (simplified - in production use bencode parser)
    fileInfo.innerHTML = `
        <div class="file-info-row">
            <span class="file-info-label">File Name:</span>
            <span class="file-info-value">${escapeHtml(currentFile.name)}</span>
        </div>
        <div class="file-info-row">
            <span class="file-info-label">File Size:</span>
            <span class="file-info-value">${formatBytes(currentFile.size)}</span>
        </div>
        <div class="file-info-row">
            <span class="file-info-label">Status:</span>
            <span class="file-info-value" style="color: #10b981;">Ready to process</span>
        </div>
    `;
    
    fileInfo.style.display = 'block';
    downloadBtn.style.display = 'flex';
}

// Process magnet link
function processMagnet() {
    const magnetInput = document.getElementById('magnetInput');
    const magnetLink = magnetInput.value.trim();
    
    if (!magnetLink) {
        showToast('Please enter a magnet link', 'warning');
        return;
    }
    
    if (!magnetLink.startsWith('magnet:?')) {
        showToast('Invalid magnet link format', 'error');
        return;
    }
    
    // Extract info hash
    const hashMatch = magnetLink.match(/btih:([a-fA-F0-9]{40})/i);
    if (!hashMatch) {
        showToast('Could not extract info hash from magnet link', 'error');
        return;
    }
    
    const infoHash = hashMatch[1].toLowerCase();
    const nameMatch = magnetLink.match(/dn=([^&]+)/);
    const name = nameMatch ? decodeURIComponent(nameMatch[1]) : 'Unknown';
    
    showDownloadResult({
        name: name,
        hash: infoHash,
        magnet: magnetLink,
        type: 'magnet'
    });
    
    saveToHistory(name, infoHash, magnetLink);
}

// Process torrent file
function processTorrentFile() {
    if (!currentFile) {
        showToast('No file selected', 'warning');
        return;
    }
    
    // In a real implementation, you would parse the torrent file
    // For now, we'll show a simplified result
    const reader = new FileReader();
    reader.onload = (e) => {
        // Generate a pseudo hash for demo
        const pseudoHash = generatePseudoHash(currentFile.name);
        const magnet = `magnet:?xt=urn:btih:${pseudoHash}&dn=${encodeURIComponent(currentFile.name.replace('.torrent', ''))}`;
        
        showDownloadResult({
            name: currentFile.name.replace('.torrent', ''),
            hash: pseudoHash,
            magnet: magnet,
            size: formatBytes(currentFile.size),
            type: 'file'
        });
        
        saveToHistory(currentFile.name, pseudoHash, magnet);
    };
    reader.readAsText(currentFile);
}

// Process info hash
function processHash() {
    const hashInput = document.getElementById('hashInput');
    const nameInput = document.getElementById('nameInput');
    
    const hash = hashInput.value.trim().toLowerCase();
    const name = nameInput.value.trim() || 'Torrent';
    
    if (!hash) {
        showToast('Please enter an info hash', 'warning');
        return;
    }
    
    if (!/^[a-f0-9]{40}$/i.test(hash)) {
        showToast('Invalid info hash format (must be 40 hex characters)', 'error');
        return;
    }
    
    const magnet = `magnet:?xt=urn:btih:${hash}&dn=${encodeURIComponent(name)}` + 
                   '&tr=udp://tracker.opentrackr.org:1337/announce' +
                   '&tr=udp://open.stealth.si:80/announce' +
                   '&tr=udp://tracker.torrent.eu.org:451/announce';
    
    showDownloadResult({
        name: name,
        hash: hash,
        magnet: magnet,
        type: 'hash'
    });
    
    saveToHistory(name, hash, magnet);
}

// Show download result
function showDownloadResult(data) {
    const resultDiv = document.getElementById('downloadResult');
    
    resultDiv.innerHTML = `
        <div class="result-header">
            <div class="result-icon">✅</div>
            <div class="result-title">
                <h3>Torrent Ready</h3>
                <p>Your torrent is ready to download</p>
            </div>
        </div>
        
        <div class="result-info">
            <div class="info-item">
                <div class="info-label">Name</div>
                <div class="info-value">${escapeHtml(data.name)}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Info Hash</div>
                <div class="info-value">${escapeHtml(data.hash)}</div>
            </div>
            ${data.size ? `
            <div class="info-item">
                <div class="info-label">Size</div>
                <div class="info-value">${escapeHtml(data.size)}</div>
            </div>
            ` : ''}
        </div>
        
        <div class="result-actions">
            <a href="${escapeHtml(data.magnet)}" class="result-btn result-btn-primary">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"/>
                    <path d="M8 12l4-4 4 4M12 16V8"/>
                </svg>
                Open in Torrent Client
            </a>
            <button class="result-btn result-btn-secondary" onclick="copyToClipboard('${escapeHtml(data.magnet)}')">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                    <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                </svg>
                Copy Magnet Link
            </button>
            <a href="watch.php?magnet=${encodeURIComponent(data.magnet)}" class="result-btn result-btn-secondary">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polygon points="5 3 19 12 5 21 5 3"/>
                </svg>
                Stream Video
            </a>
        </div>
    `;
    
    resultDiv.style.display = 'block';
    resultDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    
    showToast('Torrent processed successfully!', 'success');
}

// Save to download history
async function saveToHistory(name, hash, magnet) {
    if (!currentUser) return;
    
    try {
        const formData = new FormData();
        formData.append('action', 'save_download');
        formData.append('torrent_name', name);
        formData.append('torrent_hash', hash);
        formData.append('magnet_url', magnet);
        formData.append('size', '');
        
        await fetch('auth.php', {
            method: 'POST',
            body: formData
        });
    } catch (error) {
        console.error('Failed to save to history:', error);
    }
}

// Utility functions
function generatePseudoHash(str) {
    // Simple hash generation for demo purposes
    let hash = '';
    for (let i = 0; i < 40; i++) {
        hash += Math.floor(Math.random() * 16).toString(16);
    }
    return hash;
}

function formatBytes(bytes) {
    if (!bytes || bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

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
