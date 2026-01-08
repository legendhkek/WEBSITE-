// Link Shortener JavaScript

let qrCodeInstance = null;

// Create short link
document.getElementById('createLinkForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('action', 'create');
    
    try {
        const response = await fetch('shortener-api.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('shortUrl').value = data.short_url;
            document.getElementById('linkResult').style.display = 'block';
            
            // Generate QR code
            generateQRCode(data.short_url);
            
            // Reset form
            this.reset();
            
            // Reload links
            loadUserLinks();
            
            showToast('Link created successfully!', 'success');
        } else {
            showToast(data.error || 'Failed to create link', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('An error occurred', 'error');
    }
});

// Load user links
async function loadUserLinks() {
    try {
        const response = await fetch('shortener-api.php?action=list');
        const data = await response.json();
        
        if (data.success) {
            const tbody = document.getElementById('linksTableBody');
            
            if (data.urls.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="loading">No links yet. Create your first one above!</td></tr>';
                return;
            }
            
            tbody.innerHTML = data.urls.map(url => `
                <tr>
                    <td><a href="${url.short_url}" target="_blank">${url.short_url}</a></td>
                    <td title="${url.original_url}">${truncateUrl(url.original_url, 50)}</td>
                    <td>${url.clicks}</td>
                    <td>${formatDate(url.created_at)}</td>
                    <td><span class="status-badge ${url.status}">${formatStatus(url.status)}</span></td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn-action" onclick="viewStats(${url.id})" title="View Statistics">📊</button>
                            <button class="btn-action" onclick="copyLink('${url.short_url}')" title="Copy Link">📋</button>
                            <button class="btn-action" onclick="deleteLink(${url.id})" title="Delete">🗑️</button>
                        </div>
                    </td>
                </tr>
            `).join('');
            
            // Update analytics
            updateAnalytics(data.urls);
        }
    } catch (error) {
        console.error('Error loading links:', error);
    }
}

// Delete link
async function deleteLink(id) {
    if (!confirm('Are you sure you want to delete this link?')) {
        return;
    }
    
    try {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id', id);
        
        const response = await fetch('shortener-api.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Link deleted successfully', 'success');
            loadUserLinks();
        } else {
            showToast(data.error || 'Failed to delete link', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('An error occurred', 'error');
    }
}

// View statistics
async function viewStats(id) {
    try {
        const response = await fetch(`shortener-api.php?action=stats&id=${id}`);
        const data = await response.json();
        
        if (data.success) {
            // Show stats modal (implement as needed)
            alert(`Total Clicks: ${data.url.clicks}\nBrowsers: ${JSON.stringify(data.browsers)}`);
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

// Copy to clipboard
function copyToClipboard() {
    const shortUrl = document.getElementById('shortUrl');
    shortUrl.select();
    document.execCommand('copy');
    showToast('Link copied to clipboard!', 'success');
}

// Copy link
function copyLink(url) {
    const input = document.createElement('input');
    input.value = url;
    document.body.appendChild(input);
    input.select();
    document.execCommand('copy');
    document.body.removeChild(input);
    showToast('Link copied to clipboard!', 'success');
}

// Generate QR code
function generateQRCode(url) {
    const container = document.getElementById('qrcode');
    container.innerHTML = '';
    
    qrCodeInstance = new QRCode(container, {
        text: url,
        width: 256,
        height: 256,
        colorDark: "#000000",
        colorLight: "#ffffff",
        correctLevel: QRCode.CorrectLevel.H
    });
}

// Download QR code
function downloadQR() {
    const canvas = document.querySelector('#qrcode canvas');
    if (canvas) {
        const url = canvas.toDataURL('image/png');
        const a = document.createElement('a');
        a.href = url;
        a.download = 'qrcode.png';
        a.click();
        showToast('QR code downloaded!', 'success');
    }
}

// Initialize analytics
function initializeAnalytics() {
    const ctx = document.getElementById('clicksChart');
    if (!ctx) return;
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Clicks Over Time',
                data: [],
                borderColor: '#000',
                backgroundColor: 'rgba(0, 0, 0, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

// Update analytics
function updateAnalytics(urls) {
    const totalLinks = urls.length;
    const totalClicks = urls.reduce((sum, url) => sum + url.clicks, 0);
    const avgClicks = totalLinks > 0 ? Math.round(totalClicks / totalLinks) : 0;
    
    document.getElementById('totalLinks').textContent = totalLinks;
    document.getElementById('totalClicks').textContent = totalClicks;
    document.getElementById('avgClicks').textContent = avgClicks;
}

// Utility functions
function truncateUrl(url, length) {
    return url.length > length ? url.substring(0, length) + '...' : url;
}

function formatDate(timestamp) {
    const date = new Date(timestamp * 1000);
    return date.toLocaleDateString();
}

function formatStatus(status) {
    const statusMap = {
        'active': 'Active',
        'expired': 'Expired',
        'protected': 'Protected',
        'limit_reached': 'Limit Reached'
    };
    return statusMap[status] || status;
}

// Show toast notification
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 3000);
}
