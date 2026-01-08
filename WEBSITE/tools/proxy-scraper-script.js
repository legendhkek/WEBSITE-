// Proxy Scraper JavaScript

let scrapedProxies = [];
let validatedProxies = [];
let validationQueue = [];
let isValidating = false;

// Start scraping
async function startScraping() {
    const btn = document.getElementById('scrapeBtn');
    btn.disabled = true;
    btn.textContent = '⏳ Scraping...';
    
    const sources = Array.from(document.getElementById('sourceSelect').selectedOptions).map(opt => opt.value);
    const customSource = document.getElementById('customSource').value;
    const protocol = document.getElementById('protocolFilter').value;
    const country = document.getElementById('countryFilter').value;
    const timeout = document.getElementById('maxTimeout').value;
    
    // Show progress section
    document.getElementById('progressSection').style.display = 'block';
    document.getElementById('progressText').textContent = 'Fetching proxies from sources...';
    
    try {
        const formData = new FormData();
        formData.append('action', 'scrape');
        formData.append('sources', JSON.stringify(sources));
        formData.append('custom_source', customSource);
        
        const response = await fetch('proxy-scraper-api.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            scrapedProxies = data.proxies;
            document.getElementById('totalScraped').textContent = scrapedProxies.length;
            document.getElementById('progressText').textContent = `Found ${scrapedProxies.length} proxies. Starting validation...`;
            
            // Start validation
            validationQueue = [...scrapedProxies];
            validatedProxies = [];
            startValidation(timeout);
        } else {
            showToast(data.error || 'Failed to scrape proxies', 'error');
            btn.disabled = false;
            btn.textContent = '🚀 Start Scraping & Validation';
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('An error occurred', 'error');
        btn.disabled = false;
        btn.textContent = '🚀 Start Scraping & Validation';
    }
}

// Start validation process
async function startValidation(timeout) {
    isValidating = true;
    const concurrent = 10; // Validate 10 at a time
    
    while (validationQueue.length > 0 && isValidating) {
        const batch = validationQueue.splice(0, concurrent);
        await Promise.all(batch.map(proxy => validateSingleProxy(proxy, timeout)));
        
        updateProgress();
    }
    
    isValidating = false;
    document.getElementById('progressText').textContent = 'Validation complete!';
    document.getElementById('scrapeBtn').disabled = false;
    document.getElementById('scrapeBtn').textContent = '🚀 Start Scraping & Validation';
    
    showToast(`Found ${validatedProxies.length} working proxies!`, 'success');
    loadProxiesList();
}

// Validate single proxy
async function validateSingleProxy(proxy, timeout) {
    try {
        const formData = new FormData();
        formData.append('action', 'validate');
        formData.append('ip', proxy.ip);
        formData.append('port', proxy.port);
        formData.append('protocol', proxy.protocol);
        formData.append('timeout', timeout);
        
        const response = await fetch('proxy-scraper-api.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success && data.is_working) {
            validatedProxies.push({
                ...proxy,
                speed: data.speed,
                anonymity: data.anonymity,
                country: data.country,
                is_working: true
            });
        }
    } catch (error) {
        console.error('Validation error:', error);
    }
}

// Update progress display
function updateProgress() {
    const totalScraped = scrapedProxies.length;
    const totalValidated = totalScraped - validationQueue.length;
    const workingCount = validatedProxies.length;
    const successRate = totalValidated > 0 ? Math.round((workingCount / totalValidated) * 100) : 0;
    
    document.getElementById('totalScraped').textContent = totalScraped;
    document.getElementById('totalValidated').textContent = totalValidated;
    document.getElementById('workingProxies').textContent = workingCount;
    document.getElementById('successRate').textContent = successRate + '%';
    
    const progress = totalScraped > 0 ? (totalValidated / totalScraped) * 100 : 0;
    document.getElementById('progressBar').style.width = progress + '%';
    document.getElementById('progressText').textContent = `Validating... ${totalValidated}/${totalScraped} (${workingCount} working)`;
}

// Load proxies list
async function loadProxiesList() {
    try {
        const response = await fetch('proxy-scraper-api.php?action=list');
        const data = await response.json();
        
        if (data.success) {
            const tbody = document.getElementById('proxiesTableBody');
            
            if (data.proxies.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="loading">No working proxies yet. Start scraping!</td></tr>';
                return;
            }
            
            tbody.innerHTML = data.proxies.map(proxy => `
                <tr>
                    <td><code>${proxy.ip}</code></td>
                    <td>${proxy.port}</td>
                    <td><span class="status-badge ${proxy.protocol}">${proxy.protocol.toUpperCase()}</span></td>
                    <td>${proxy.country}</td>
                    <td>${proxy.anonymity}</td>
                    <td>${proxy.speed}ms</td>
                    <td><span class="status-badge active">✅ Working</span></td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn-action" onclick="copyProxy('${proxy.ip}:${proxy.port}')" title="Copy">📋</button>
                            <button class="btn-action" onclick="testProxy(${proxy.id})" title="Re-test">🔄</button>
                            <button class="btn-action" onclick="deleteProxyItem(${proxy.id})" title="Delete">🗑️</button>
                        </div>
                    </td>
                </tr>
            `).join('');
        }
    } catch (error) {
        console.error('Error loading proxies:', error);
    }
}

// Copy proxy
function copyProxy(proxy) {
    const input = document.createElement('input');
    input.value = proxy;
    document.body.appendChild(input);
    input.select();
    document.execCommand('copy');
    document.body.removeChild(input);
    showToast('Proxy copied to clipboard!', 'success');
}

// Delete proxy
async function deleteProxyItem(id) {
    if (!confirm('Remove this proxy from the list?')) return;
    
    try {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id', id);
        
        const response = await fetch('proxy-scraper-api.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        if (data.success) {
            showToast('Proxy removed', 'success');
            loadProxiesList();
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

// Test proxy
async function testProxy(id) {
    showToast('Re-testing proxy...', 'success');
    // Implement re-test logic
}

// Export proxies
function exportProxies(format) {
    window.location.href = `proxy-scraper-api.php?action=export&format=${format}`;
    showToast(`Exporting as ${format.toUpperCase()}...`, 'success');
}

// Clear all results
async function clearResults() {
    if (!confirm('Clear all scraped proxies? This cannot be undone.')) return;
    
    // Implement clear logic
    showToast('Results cleared', 'success');
    loadProxiesList();
}

// Show toast
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    loadProxiesList();
});
