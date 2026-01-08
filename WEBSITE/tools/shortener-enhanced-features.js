// Enhanced Link Shortener Features - Pro Edition

// Advanced Analytics Dashboard
class AnalyticsDashboard {
    constructor(containerId) {
        this.container = document.getElementById(containerId);
        this.charts = {};
    }
    
    // Initialize all charts
    async init() {
        await this.createClicksChart();
        await this.createGeographicChart();
        await this.createDeviceChart();
        await this.createBrowserChart();
    }
    
    // Clicks over time chart
    async createClicksChart() {
        const canvas = document.createElement('canvas');
        canvas.id = 'clicksChart';
        this.container.appendChild(canvas);
        
        const ctx = canvas.getContext('2d');
        this.charts.clicks = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Clicks',
                    data: [12, 19, 3, 5, 2, 3, 9],
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Clicks Over Time'
                    }
                }
            }
        });
    }
    
    // Geographic distribution chart
    async createGeographicChart() {
        const canvas = document.createElement('canvas');
        canvas.id = 'geoChart';
        this.container.appendChild(canvas);
        
        const ctx = canvas.getContext('2d');
        this.charts.geo = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['USA', 'UK', 'Canada', 'Germany', 'France'],
                datasets: [{
                    label: 'Clicks by Country',
                    data: [45, 25, 15, 10, 5],
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(153, 102, 255, 0.2)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)'
                    ],
                    borderWidth: 1
                }]
            }
        });
    }
    
    // Device type chart
    async createDeviceChart() {
        const canvas = document.createElement('canvas');
        canvas.id = 'deviceChart';
        this.container.appendChild(canvas);
        
        const ctx = canvas.getContext('2d');
        this.charts.device = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Desktop', 'Mobile', 'Tablet'],
                datasets: [{
                    data: [60, 30, 10],
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Device Distribution'
                    }
                }
            }
        });
    }
    
    // Browser distribution chart
    async createBrowserChart() {
        const canvas = document.createElement('canvas');
        canvas.id = 'browserChart';
        this.container.appendChild(canvas);
        
        const ctx = canvas.getContext('2d');
        this.charts.browser = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Chrome', 'Firefox', 'Safari', 'Edge', 'Other'],
                datasets: [{
                    data: [45, 20, 15, 12, 8],
                    backgroundColor: ['#4285F4', '#FF7139', '#00C4CC', '#0078D4', '#999']
                }]
            }
        });
    }
}

// A/B Testing Manager
class ABTestManager {
    constructor() {
        this.tests = new Map();
    }
    
    createTest(testId, variants) {
        this.tests.set(testId, {
            variants: variants,
            results: variants.map(v => ({ url: v, clicks: 0, conversions: 0 }))
        });
    }
    
    getVariant(testId) {
        const test = this.tests.get(testId);
        if (!test) return null;
        
        // Round-robin distribution
        const totalClicks = test.results.reduce((sum, r) => sum + r.clicks, 0);
        const variantIndex = totalClicks % test.variants.length;
        
        test.results[variantIndex].clicks++;
        return test.variants[variantIndex];
    }
    
    recordConversion(testId, variantUrl) {
        const test = this.tests.get(testId);
        if (!test) return;
        
        const result = test.results.find(r => r.url === variantUrl);
        if (result) result.conversions++;
    }
    
    getResults(testId) {
        const test = this.tests.get(testId);
        if (!test) return null;
        
        return test.results.map(r => ({
            ...r,
            conversionRate: r.clicks > 0 ? (r.conversions / r.clicks * 100).toFixed(2) + '%' : '0%'
        }));
    }
}

// Bulk URL Shortener
async function bulkShortenUrls(urlList) {
    const results = [];
    
    for (const url of urlList) {
        try {
            const response = await fetch('shortener-api.php?action=create', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `original_url=${encodeURIComponent(url)}`
            });
            
            const data = await response.json();
            if (data.success) {
                results.push({
                    original: url,
                    short: data.short_url,
                    code: data.short_code
                });
            }
        } catch (error) {
            console.error('Bulk shorten error:', error);
            results.push({
                original: url,
                error: error.message
            });
        }
    }
    
    return results;
}

// Parse CSV file for bulk operations
function parseCSV(csvText) {
    const lines = csvText.split('\n');
    const urls = [];
    
    lines.forEach(line => {
        const trimmed = line.trim();
        if (trimmed && trimmed.startsWith('http')) {
            urls.push(trimmed);
        }
    });
    
    return urls;
}

// UTM Parameter Builder
class UTMBuilder {
    constructor() {
        this.parameters = {
            utm_source: '',
            utm_medium: '',
            utm_campaign: '',
            utm_term: '',
            utm_content: ''
        };
    }
    
    setSource(source) {
        this.parameters.utm_source = source;
        return this;
    }
    
    setMedium(medium) {
        this.parameters.utm_medium = medium;
        return this;
    }
    
    setCampaign(campaign) {
        this.parameters.utm_campaign = campaign;
        return this;
    }
    
    setTerm(term) {
        this.parameters.utm_term = term;
        return this;
    }
    
    setContent(content) {
        this.parameters.utm_content = content;
        return this;
    }
    
    build(baseUrl) {
        const url = new URL(baseUrl);
        Object.entries(this.parameters).forEach(([key, value]) => {
            if (value) url.searchParams.set(key, value);
        });
        return url.toString();
    }
}

// QR Code Generator with options
function generateQRCode(text, options = {}) {
    const qr = qrcode(0, options.errorLevel || 'M');
    qr.addData(text);
    qr.make();
    
    const size = options.size || 256;
    const cellSize = Math.floor(size / qr.getModuleCount());
    
    return qr.createDataURL(cellSize, 0);
}

// Link Rotation Manager
class LinkRotator {
    constructor(links) {
        this.links = links;
        this.currentIndex = 0;
        this.stats = links.map(l => ({ url: l, clicks: 0 }));
    }
    
    getNext() {
        const link = this.links[this.currentIndex];
        this.stats[this.currentIndex].clicks++;
        this.currentIndex = (this.currentIndex + 1) % this.links.length;
        return link;
    }
    
    getRandom() {
        const index = Math.floor(Math.random() * this.links.length);
        this.stats[index].clicks++;
        return this.links[index];
    }
    
    getWeighted() {
        // Distribute based on performance (less clicks = higher weight)
        const totalClicks = this.stats.reduce((sum, s) => sum + s.clicks, 0);
        const weights = this.stats.map(s => {
            return totalClicks === 0 ? 1 : (totalClicks - s.clicks + 1);
        });
        
        const totalWeight = weights.reduce((sum, w) => sum + w, 0);
        let random = Math.random() * totalWeight;
        
        for (let i = 0; i < weights.length; i++) {
            random -= weights[i];
            if (random <= 0) {
                this.stats[i].clicks++;
                return this.links[i];
            }
        }
        
        return this.links[0];
    }
    
    getStats() {
        return this.stats;
    }
}

// Device Detection
function detectDevice(userAgent) {
    const ua = userAgent.toLowerCase();
    
    let device = 'desktop';
    if (/(tablet|ipad|playbook|silk)|(android(?!.*mobi))/i.test(ua)) {
        device = 'tablet';
    } else if (/Mobile|Android|iP(hone|od)|IEMobile|BlackBerry|Kindle|Silk-Accelerated|(hpw|web)OS|Opera M(obi|ini)/.test(ua)) {
        device = 'mobile';
    }
    
    let os = 'unknown';
    if (/windows/i.test(ua)) os = 'Windows';
    else if (/macintosh|mac os x/i.test(ua)) os = 'macOS';
    else if (/linux/i.test(ua)) os = 'Linux';
    else if (/android/i.test(ua)) os = 'Android';
    else if (/ios|iphone|ipad/i.test(ua)) os = 'iOS';
    
    let browser = 'unknown';
    if (/chrome/i.test(ua) && !/edge/i.test(ua)) browser = 'Chrome';
    else if (/firefox/i.test(ua)) browser = 'Firefox';
    else if (/safari/i.test(ua) && !/chrome/i.test(ua)) browser = 'Safari';
    else if (/edge/i.test(ua)) browser = 'Edge';
    else if (/msie|trident/i.test(ua)) browser = 'Internet Explorer';
    
    return { device, os, browser };
}

// Advanced Security Manager
class SecurityManager {
    constructor() {
        this.ipWhitelist = new Set();
        this.ipBlacklist = new Set();
        this.passwordProtected = new Map();
        this.timeRestrictions = new Map();
    }
    
    addIPWhitelist(ip) {
        this.ipWhitelist.add(ip);
    }
    
    addIPBlacklist(ip) {
        this.ipBlacklist.add(ip);
    }
    
    setPassword(shortCode, password) {
        this.passwordProtected.set(shortCode, password);
    }
    
    setTimeRestriction(shortCode, startTime, endTime) {
        this.timeRestrictions.set(shortCode, { start: startTime, end: endTime });
    }
    
    canAccess(shortCode, ip, password = null, currentTime = new Date()) {
        // Check IP blacklist
        if (this.ipBlacklist.has(ip)) {
            return { allowed: false, reason: 'IP blacklisted' };
        }
        
        // Check IP whitelist (if set)
        if (this.ipWhitelist.size > 0 && !this.ipWhitelist.has(ip)) {
            return { allowed: false, reason: 'IP not whitelisted' };
        }
        
        // Check password
        if (this.passwordProtected.has(shortCode)) {
            if (password !== this.passwordProtected.get(shortCode)) {
                return { allowed: false, reason: 'Invalid password' };
            }
        }
        
        // Check time restrictions
        if (this.timeRestrictions.has(shortCode)) {
            const restriction = this.timeRestrictions.get(shortCode);
            const currentHour = currentTime.getHours();
            if (currentHour < restriction.start || currentHour > restriction.end) {
                return { allowed: false, reason: 'Outside allowed time window' };
            }
        }
        
        return { allowed: true };
    }
}

// Export all features
window.linkShortenerPro = {
    AnalyticsDashboard,
    ABTestManager,
    bulkShortenUrls,
    parseCSV,
    UTMBuilder,
    generateQRCode,
    LinkRotator,
    detectDevice,
    SecurityManager
};

console.log('🚀 Enhanced Link Shortener Pro loaded with godlike features!');
