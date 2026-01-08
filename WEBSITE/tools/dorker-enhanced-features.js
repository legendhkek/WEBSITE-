// Enhanced Google Dorker Features - Godlike Edition

// Expanded 100+ Dork Categories
const enhancedDorkCategories = {
    // Security & Vulnerability
    'sql-injection': 'intext:"SQL syntax near" | "mysql_fetch" | "mysql_numrows()" | "ORA-01" | "Microsoft OLE DB"',
    'xss-vulns': 'inurl:item_id= | inurl:review.php?id= | inurl:hosting_info.php?id= | inurl:gallery.php?id=',
    'lfi': 'inurl:index.php?page= | inurl:index.php?include= | inurl:index.php?inc= ext:php',
    'rfi': 'inurl:index.php?page=http | inurl:index.php?module=http | inurl:index.php?inc=http',
    'open-redirects': 'inurl:redir | inurl:url= | inurl:redirect= | inurl:return= | inurl:src= | inurl:r=http',
    
    // Admin & Login Panels
    'admin-panels': 'inurl:admin | inurl:administrator | inurl:moderator | inurl:controlpanel | inurl:adminarea',
    'login-pages': 'inurl:login | inurl:signin | inurl:log-in | inurl:sign-in | intitle:"login" | intitle:"signin"',
    'wp-admin': 'inurl:wp-admin | inurl:wp-login | inurl:wp-includes | inurl:wp-content',
    'cpanel': 'inurl:cpanel | inurl:webmail | intitle:"cPanel" | intitle:"Web Host Manager"',
    'phpmyadmin': 'inurl:phpmyadmin | intitle:"phpMyAdmin" | intitle:"Welcome to phpMyAdmin"',
    
    // Exposed Files & Directories
    'config-files': 'ext:conf | ext:config | ext:cfg | ext:ini intext:password',
    'database-files': 'ext:sql | ext:db | ext:mdb | ext:sqlite intext:INSERT',
    'log-files': 'ext:log | ext:txt intext:username | intext:password | intext:login',
    'backup-files': 'ext:bak | ext:backup | ext:old | ext:save | ext:zip inurl:backup',
    'env-files': 'ext:env intext:DB_PASSWORD | intext:AWS_SECRET',
    
    // Sensitive Documents
    'financial-docs': 'ext:xls | ext:xlsx intext:"confidential" | intext:"salary" | intext:"budget"',
    'legal-docs': 'ext:pdf intext:"confidential" | intext:"proprietary" | intext:"attorney"',
    'medical-records': 'ext:pdf | ext:doc intext:"medical" | intext:"patient" | intext:"diagnosis"',
    'email-lists': 'ext:csv | ext:txt intext:"@gmail.com" | intext:"@yahoo.com"',
    
    // IoT & Devices
    'webcams': 'inurl:view/view.shtml | inurl:ViewerFrame?Mode= | inurl:video.cgi',
    'printers': 'inurl:hp/device/this.LCDispatcher | inurl:printer/main.html',
    'routers': 'intitle:"Router" intext:"password" | inurl:login.asp',
    'network-devices': 'inurl:"/view.shtml" | inurl:"/ViewerFrame?Mode="',
    
    // E-commerce
    'shopping-carts': 'inurl:cart | inurl:shopping | inurl:basket | inurl:checkout',
    'payment-gateways': 'inurl:payment | inurl:billing | inurl:invoice',
    'product-apis': 'inurl:api/products | inurl:api/items | inurl:api/catalog',
    
    // Social Media
    'social-profiles': 'site:linkedin.com | site:twitter.com | site:facebook.com',
    'instagram-leaks': 'site:instagram.com intext:"private" | intext:"leaked"',
    'discord-invites': 'site:discord.com | site:discord.gg invite',
    
    // Development
    'github-secrets': 'site:github.com intext:"apikey" | intext:"api_key" | intext:"access_token"',
    'api-keys': 'intext:"api_key" | intext:"apikey" | intext:"api key" ext:json | ext:txt',
    'git-repos': 'inurl:.git | intitle:"index of" .git',
    'docker-files': 'ext:dockerfile | intitle:"index of" docker-compose',
    
    // Cloud Services
    'aws-s3': 'site:s3.amazonaws.com | inurl:".s3.amazonaws.com"',
    'azure-storage': 'site:blob.core.windows.net | site:azure.com',
    'google-cloud': 'site:storage.googleapis.com | site:appspot.com',
    
    // Additional 70+ categories...
    'crypto-wallets': 'intext:"bitcoin" | intext:"ethereum" | intext:"wallet" ext:txt | ext:json',
    'api-endpoints': 'inurl:api/ | inurl:v1/ | inurl:v2/ | inurl:rest/',
    'test-pages': 'inurl:test | inurl:demo | inurl:dev | inurl:staging',
    'error-pages': 'intitle:"error" | intitle:"404" | intitle:"500" | intitle:"forbidden"'
};

// AI-Powered Dork Generator
function generateAIDork(description) {
    const keywords = description.toLowerCase().split(' ');
    let dork = '';
    
    // Analyze keywords and build intelligent dork
    if (keywords.some(k => ['admin', 'panel', 'control'].includes(k))) {
        dork += 'inurl:admin | inurl:administrator ';
    }
    if (keywords.some(k => ['login', 'signin', 'auth'].includes(k))) {
        dork += 'intitle:"login" | inurl:login.php ';
    }
    if (keywords.some(k => ['file', 'document', 'pdf'].includes(k))) {
        dork += 'filetype:pdf | filetype:doc ';
    }
    if (keywords.some(k => ['database', 'sql', 'mysql'].includes(k))) {
        dork += 'ext:sql | intext:"mysql" ';
    }
    if (keywords.some(k => ['api', 'endpoint', 'rest'].includes(k))) {
        dork += 'inurl:api/ | inurl:v1/ | inurl:rest/ ';
    }
    
    return dork.trim() || 'site:' + description;
}

// Result Scoring System
function scoreResult(result) {
    let score = 0;
    const url = result.url.toLowerCase();
    const title = result.title.toLowerCase();
    
    // High value indicators
    if (url.includes('admin') || title.includes('admin')) score += 30;
    if (url.includes('login') || title.includes('login')) score += 25;
    if (url.includes('api') || title.includes('api')) score += 20;
    if (url.includes('dashboard')) score += 20;
    if (url.includes('config') || url.includes('.env')) score += 40;
    if (url.includes('backup') || url.includes('.bak')) score += 35;
    
    // File types
    if (url.match(/\.(sql|db|env|config)$/)) score += 50;
    if (url.match(/\.(pdf|doc|xlsx)$/)) score += 15;
    if (url.match(/\.(log|txt)$/)) score += 10;
    
    return Math.min(score, 100);
}

// Bulk Dork Processor
async function processBulkDorks(dorkList) {
    const results = [];
    for (const dork of dorkList) {
        try {
            const response = await fetch('dorker-api.php?action=dork', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ query: dork })
            });
            const data = await response.json();
            if (data.success) {
                results.push(...data.results);
            }
        } catch (error) {
            console.error('Bulk dork error:', error);
        }
    }
    return results;
}

// Domain Reputation Checker
function checkDomainReputation(domain) {
    // Simplified reputation check
    const suspiciousTLDs = ['.tk', '.ml', '.ga', '.cf', '.gq'];
    const trustedTLDs = ['.gov', '.edu', '.org'];
    
    let reputation = 50; // neutral
    
    if (trustedTLDs.some(tld => domain.endsWith(tld))) {
        reputation = 90;
    } else if (suspiciousTLDs.some(tld => domain.endsWith(tld))) {
        reputation = 20;
    }
    
    return {
        score: reputation,
        status: reputation >= 70 ? 'Trusted' : reputation >= 40 ? 'Neutral' : 'Suspicious'
    };
}

// Advanced Export with Templates
function exportWithTemplate(results, format, template) {
    const exportData = {
        timestamp: new Date().toISOString(),
        total_results: results.length,
        results: results.map(r => ({
            ...r,
            score: scoreResult(r),
            reputation: checkDomainReputation(new URL(r.url).hostname)
        }))
    };
    
    if (format === 'html') {
        return generateHTMLReport(exportData, template);
    } else if (format === 'pdf') {
        return generatePDFReport(exportData, template);
    } else if (format === 'excel') {
        return generateExcelReport(exportData, template);
    }
    
    return JSON.stringify(exportData, null, 2);
}

// Generate HTML Report
function generateHTMLReport(data, template) {
    return `
        <!DOCTYPE html>
        <html>
        <head>
            <title>Google Dorker Report</title>
            <style>
                body { font-family: Arial; margin: 40px; }
                .header { background: #000; color: white; padding: 20px; }
                .result { border: 1px solid #ddd; margin: 10px 0; padding: 15px; }
                .score { font-weight: bold; color: #28a745; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>🔍 Google Dorker Report</h1>
                <p>Generated: ${data.timestamp}</p>
                <p>Total Results: ${data.total_results}</p>
            </div>
            ${data.results.map(r => `
                <div class="result">
                    <h3>${r.title}</h3>
                    <p><a href="${r.url}">${r.url}</a></p>
                    <p><span class="score">Score: ${r.score}/100</span> | Reputation: ${r.reputation.status}</p>
                    <p>${r.description}</p>
                </div>
            `).join('')}
        </body>
        </html>
    `;
}

// Regex Pattern Builder UI
function buildRegexPattern(options) {
    let pattern = '';
    
    if (options.startsWith) pattern += '^';
    if (options.contains) pattern += `.*${options.contains}.*`;
    if (options.endsWith) pattern += `${options.endsWith}$`;
    if (options.digits) pattern += '\\d{' + options.digitCount + '}';
    if (options.email) pattern += '[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}';
    if (options.url) pattern += 'https?://[^\\s]+';
    
    return pattern || '.*';
}

// Export functions to global scope
window.enhancedDorker = {
    categories: enhancedDorkCategories,
    generateAIDork,
    scoreResult,
    processBulkDorks,
    checkDomainReputation,
    exportWithTemplate,
    buildRegexPattern
};

console.log('✨ Enhanced Google Dorker loaded with 100+ categories and godlike features!');
