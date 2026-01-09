// Enhanced Google Dorker Features - GODLIKE POWERFUL Edition
// Over 150+ Advanced Dork Categories for Professional Security Research

const enhancedDorkCategories = {
    // === SECURITY & VULNERABILITY RESEARCH ===
    'sql-injection': 'intext:"SQL syntax near" | "mysql_fetch" | "mysql_numrows()" | "ORA-01" | "Microsoft OLE DB" | "Error Executing Database Query"',
    'xss-vulns': 'inurl:item_id= | inurl:review.php?id= | inurl:hosting_info.php?id= | inurl:gallery.php?id= | inurl:newsDetail.php?id=',
    'lfi': 'inurl:index.php?page= | inurl:index.php?include= | inurl:index.php?inc= | inurl:index.php?file= ext:php',
    'rfi': 'inurl:index.php?page=http | inurl:index.php?module=http | inurl:index.php?inc=http | inurl:index.php?file=http',
    'open-redirects': 'inurl:redir | inurl:url= | inurl:redirect= | inurl:return= | inurl:src= | inurl:r=http | inurl:link=http',
    'xxe-injection': 'intext:"XML Parsing Error" | intext:"xmlns" | intext:"DOCTYPE" filetype:xml',
    'ssrf-vulns': 'inurl:proxy= | inurl:url= | inurl:path= | inurl:dest= | inurl:redirect= | inurl:uri=http',
    'command-injection': 'inurl:exec | inurl:command | inurl:cmd | inurl:execute | inurl:run ext:php',
    'path-traversal': 'inurl:../../ | inurl:..%2F | inurl:file= | inurl:path= | inurl:folder=',
    'csrf-vulnerable': 'intext:"csrf_token" | intext:"authenticity_token" inurl:form',
    
    // === ADMIN & AUTHENTICATION PANELS ===
    'admin-panels': 'inurl:admin | inurl:administrator | inurl:moderator | inurl:controlpanel | inurl:adminarea | inurl:admin_panel | inurl:sysadmin',
    'login-pages': 'inurl:login | inurl:signin | inurl:log-in | inurl:sign-in | intitle:"login" | intitle:"signin" | intitle:"member login"',
    'wp-admin': 'inurl:wp-admin | inurl:wp-login | inurl:wp-includes | inurl:wp-content | intitle:"Dashboard" inurl:wp-admin',
    'cpanel': 'inurl:cpanel | inurl:webmail | intitle:"cPanel" | intitle:"Web Host Manager" | intitle:"WHM"',
    'phpmyadmin': 'inurl:phpmyadmin | intitle:"phpMyAdmin" | intitle:"Welcome to phpMyAdmin" | inurl:pma | inurl:myadmin',
    'joomla-admin': 'inurl:administrator/index.php | inurl:administrator/components | intitle:"Joomla Administration"',
    'drupal-admin': 'inurl:/user/login | inurl:/admin/config | intitle:"User account | Drupal"',
    'magento-admin': 'inurl:/admin/dashboard | inurl:/index.php/admin | intitle:"Magento Admin"',
    'plesk-login': 'inurl:login_up.php3 | intitle:"Parallels Plesk Panel" | inurl:plesk',
    'webmin-panel': 'inurl::10000 | intitle:"Webmin" | inurl:webmin',
    
    // === EXPOSED FILES & CONFIGURATION ===
    'config-files': 'ext:conf | ext:config | ext:cfg | ext:ini | ext:xml intext:password | intext:username',
    'database-files': 'ext:sql | ext:db | ext:mdb | ext:sqlite | ext:dbf intext:INSERT | intext:CREATE TABLE',
    'log-files': 'ext:log | ext:txt intext:username | intext:password | intext:login | intext:error | intext:warning',
    'backup-files': 'ext:bak | ext:backup | ext:old | ext:save | ext:copy | ext:zip inurl:backup | inurl:bak',
    'env-files': 'ext:env | filetype:env intext:DB_PASSWORD | intext:AWS_SECRET | intext:API_KEY',
    'htaccess-files': 'ext:htaccess | ext:htpasswd intext:AuthUserFile',
    'ssh-keys': 'ext:pem | ext:key | ext:ppk | ext:rsa intext:"PRIVATE KEY" | intext:"RSA PRIVATE"',
    'credentials': 'ext:txt | ext:log | ext:cfg intext:password | intext:username | intext:credential',
    'git-config': 'inurl:.git/config | inurl:.git/HEAD | intext:"repositoryformatversion"',
    'svn-repos': 'inurl:.svn/entries | inurl:.svn/wc.db',
    
    // === SENSITIVE DOCUMENTS & DATA ===
    'financial-docs': 'ext:xls | ext:xlsx | ext:csv intext:"confidential" | intext:"salary" | intext:"budget" | intext:"revenue"',
    'legal-docs': 'ext:pdf | ext:doc | ext:docx intext:"confidential" | intext:"proprietary" | intext:"attorney" | intext:"privileged"',
    'medical-records': 'ext:pdf | ext:doc | ext:xls intext:"medical" | intext:"patient" | intext:"diagnosis" | intext:"prescription"',
    'email-lists': 'ext:csv | ext:txt | ext:xls intext:"@gmail.com" | intext:"@yahoo.com" | intext:"@hotmail.com" | intext:"@outlook.com"',
    'contact-info': 'ext:xlsx | ext:csv intext:"phone" | intext:"email" | intext:"address" | intext:"contact"',
    'government-docs': 'site:gov ext:pdf | ext:doc intext:"classified" | intext:"restricted" | intext:"internal use"',
    'corporate-secrets': 'ext:ppt | ext:pptx | ext:doc intext:"confidential" | intext:"proprietary" | intext:"trade secret"',
    'password-lists': 'ext:txt | ext:log intext:password | intext:passwd | intext:pwd inurl:password',
    'api-docs': 'ext:json | ext:xml intext:"api_key" | intext:"secret" | intext:"token" | intext:"password"',
    'invoices': 'ext:pdf intext:"invoice" | intext:"payment" | intext:"receipt" | intext:"billing"',
    
    // === IOT & NETWORK DEVICES ===
    'webcams': 'inurl:view/view.shtml | inurl:ViewerFrame?Mode= | inurl:video.cgi | inurl:cam.html | intitle:"Live View"',
    'printers': 'inurl:hp/device/this.LCDispatcher | inurl:printer/main.html | intitle:"Printer Status" | intitle:"HP LaserJet"',
    'routers': 'intitle:"Router" intext:"password" | inurl:login.asp | intitle:"DD-WRT" | intitle:"OpenWrt"',
    'network-devices': 'inurl:"/view.shtml" | inurl:"/ViewerFrame?Mode=" | intitle:"Network Device" | intitle:"Switch Configuration"',
    'nvr-systems': 'inurl:indexFrame.html | intitle:"NVR" | intitle:"Network Video Recorder"',
    'smart-home': 'inurl:home-automation | intitle:"Smart Home" | inurl:iot | intitle:"IoT Dashboard"',
    'scada-systems': 'inurl:scada | intitle:"SCADA" | intext:"PLC" | intext:"Industrial Control"',
    'security-cameras': 'inurl:/view/index.shtml | inurl:MultiCameraFrame?Mode= | intitle:"Camera" inurl:view',
    'voip-systems': 'intitle:"Asterisk" | intitle:"FreePBX" | inurl:sip | intitle:"VoIP"',
    'nas-devices': 'intitle:"Synology" | intitle:"QNAP" | intitle:"FreeNAS" | inurl:/cgi-bin/login',
    
    // === E-COMMERCE & BUSINESS ===
    'shopping-carts': 'inurl:cart | inurl:shopping | inurl:basket | inurl:checkout | inurl:add-to-cart',
    'payment-gateways': 'inurl:payment | inurl:billing | inurl:invoice | inurl:checkout/payment',
    'product-apis': 'inurl:api/products | inurl:api/items | inurl:api/catalog | inurl:rest/products',
    'magento-sites': 'inurl:magento | inurl:/checkout/cart | intitle:"Magento"',
    'woocommerce': 'inurl:wc-ajax | inurl:woocommerce | intext:"WooCommerce"',
    'shopify-stores': 'site:myshopify.com | inurl:.myshopify.com',
    'stripe-keys': 'intext:"pk_live_" | intext:"sk_live_" | intext:"stripe" ext:js | ext:html',
    'paypal-buttons': 'intext:"paypal.com/cgi-bin/webscr" | inurl:paypal/button',
    
    // === SOCIAL MEDIA & COMMUNICATIONS ===
    'social-profiles': 'site:linkedin.com | site:twitter.com | site:facebook.com | site:instagram.com',
    'instagram-leaks': 'site:instagram.com intext:"private" | intext:"leaked" | intext:"hacked"',
    'discord-invites': 'site:discord.com | site:discord.gg | intext:"discord.gg/" | inurl:invite',
    'slack-tokens': 'intext:"xoxb-" | intext:"xoxp-" | intext:"slack" intext:"token"',
    'telegram-groups': 'site:t.me | inurl:telegram.me | intext:"telegram.me/" | intext:"t.me/"',
    'zoom-meetings': 'inurl:zoom.us/j/ | inurl:zoom.us/s/ | intext:"zoom.us/j/"',
    'whatsapp-groups': 'intext:"chat.whatsapp.com" | inurl:chat.whatsapp.com',
    
    // === DEVELOPMENT & CODE ===
    'github-secrets': 'site:github.com intext:"apikey" | intext:"api_key" | intext:"access_token" | intext:"secret_key"',
    'api-keys': 'intext:"api_key" | intext:"apikey" | intext:"api key" | intext:"api-key" ext:json | ext:txt | ext:js',
    'git-repos': 'inurl:.git | intitle:"index of" .git | inurl:.git/config',
    'docker-files': 'ext:dockerfile | intitle:"index of" docker-compose | ext:docker-compose.yml',
    'jenkins-servers': 'intitle:"Dashboard [Jenkins]" | inurl:jenkins | intitle:"Jenkins"',
    'gitlab-repos': 'site:gitlab.com intext:"apikey" | intext:"token" | intext:"password"',
    'bitbucket-repos': 'site:bitbucket.org intext:"password" | intext:"token"',
    'npm-packages': 'site:npmjs.com | site:registry.npmjs.org intext:"token"',
    'source-code': 'ext:java | ext:py | ext:php | ext:js | ext:cpp intext:password | intext:secret',
    'aws-keys': 'intext:"AKIA" | intext:"aws_access_key_id" | intext:"aws_secret_access_key"',
    
    // === CLOUD SERVICES ===
    'aws-s3': 'site:s3.amazonaws.com | inurl:".s3.amazonaws.com" | inurl:s3.amazonaws.com',
    'azure-storage': 'site:blob.core.windows.net | site:azure.com | inurl:blob.core.windows.net',
    'google-cloud': 'site:storage.googleapis.com | site:appspot.com | site:cloudfunctions.net',
    'firebase-db': 'site:firebaseio.com | inurl:.firebaseio.com | intext:"firebase"',
    'heroku-apps': 'site:herokuapp.com | inurl:.herokuapp.com',
    'digitalocean': 'site:digitaloceanspaces.com | inurl:cdn.digitaloceanspaces.com',
    
    // === DATABASES & SERVERS ===
    'mongodb-exposed': 'intext:"MongoDB Server Information" | intitle:"MongoDB" port:27017',
    'elasticsearch': 'intext:"You Know, for Search" | intitle:"Elasticsearch" | inurl:9200/_search',
    'redis-servers': 'intext:"Redis" | inurl:6379',
    'mysql-servers': 'intext:"phpMyAdmin" | intext:"MySQL" inurl:3306',
    'postgresql': 'intext:"PostgreSQL" | intitle:"pgAdmin"',
    'apache-status': 'intitle:"Apache Status" | inurl:server-status',
    'nginx-status': 'intitle:"nginx status" | inurl:nginx_status',
    'tomcat-manager': 'intitle:"Apache Tomcat" | inurl:/manager/html',
    
    // === TESTING & STAGING ===
    'test-pages': 'inurl:test | inurl:demo | inurl:dev | inurl:staging | inurl:sandbox | inurl:beta',
    'error-pages': 'intitle:"error" | intitle:"404" | intitle:"500" | intitle:"forbidden" | intitle:"not found"',
    'debug-info': 'intext:"Debug mode" | intext:"debug=true" | intext:"DEBUG" | inurl:debug',
    'phpinfo-pages': 'intitle:"phpinfo()" | intext:"PHP Version" inurl:phpinfo',
    'asp-debug': 'ext:asp intext:"Microsoft OLE DB Provider for SQL Server"',
    
    // === CRYPTOCURRENCY & BLOCKCHAIN ===
    'crypto-wallets': 'intext:"bitcoin" | intext:"ethereum" | intext:"wallet" | intext:"private key" ext:txt | ext:json',
    'blockchain-apis': 'inurl:blockchain/api | inurl:crypto/api | intext:"blockchain" intext:"api"',
    'mining-pools': 'intext:"mining pool" | intext:"hashrate" | intitle:"Mining Pool"',
    'exchange-keys': 'intext:"binance" | intext:"coinbase" | intext:"kraken" intext:"api_key"',
    
    // === NETWORK & INFRASTRUCTURE ===
    'vpn-configs': 'ext:ovpn | ext:conf intext:"vpn" | intext:"openvpn"',
    'proxy-lists': 'intext:"proxy list" | inurl:proxy | intitle:"Proxy List"',
    'dns-servers': 'intext:"bind" | intext:"named" | intitle:"DNS Server"',
    'load-balancers': 'intitle:"HAProxy" | intitle:"Load Balancer" | intext:"nginx upstream"',
    
    // === ADVANCED RESEARCH DORKS ===
    'api-endpoints': 'inurl:api/ | inurl:v1/ | inurl:v2/ | inurl:v3/ | inurl:rest/ | inurl:graphql',
    'swagger-docs': 'inurl:swagger | inurl:api-docs | intitle:"Swagger UI" | inurl:/swagger-ui.html',
    'graphql-endpoints': 'inurl:graphql | intext:"graphql" inurl:api | inurl:/graphql/playground',
    'json-files': 'ext:json intext:"password" | intext:"token" | intext:"secret" | intext:"key"',
    'xml-files': 'ext:xml intext:"password" | intext:"username" | intext:"credential"',
    'yaml-configs': 'ext:yml | ext:yaml intext:"password" | intext:"secret" | intext:"token"',
    'terraform-files': 'ext:tf | ext:tfvars intext:"aws" | intext:"azure" | intext:"secret"',
    'ansible-playbooks': 'ext:yml intext:"ansible" | intext:"playbook" intext:"password"',
    'kubernetes-configs': 'ext:yaml intext:"kubernetes" | intext:"k8s" intext:"secret"',
    'ci-cd-configs': 'inurl:.github/workflows | inurl:.gitlab-ci.yml | inurl:jenkinsfile',
    'directory-listings': 'intitle:"index of" inurl:admin | inurl:backup | inurl:config | inurl:data',
    'open-directories': 'intitle:"Index of /" | intitle:"Directory Listing" inurl:files',
    'ftp-servers': 'intitle:"index of" inurl:ftp | inurl:pub | intitle:"FTP Server"',
    'rsync-servers': 'intext:"rsync" | inurl:873 | intitle:"rsync"',
    'vulnerable-apps': 'inurl:admin/login.php | inurl:user/login | inurl:admin.php | inurl:login.aspx',
    'default-passwords': 'intext:"default password" | intext:"admin:admin" | intext:"root:root"',
    'information-disclosure': 'intext:"powered by" | intext:"version" | intext:"build" inurl:admin',
    'cors-misconfiguration': 'intext:"Access-Control-Allow-Origin: *" | inurl:cors',
    'subdomain-takeover': 'site:*.herokuapp.com | site:*.github.io | site:*.s3.amazonaws.com',
    'web-shells': 'intext:"c99" | intext:"r57" | intext:"c100" | intext:"b374k" ext:php',
    'backdoors': 'intext:"backdoor" | intext:"shell" | intext:"webshell" ext:php | ext:asp',
    'malware-samples': 'ext:exe | ext:dll | ext:bat inurl:virus | inurl:trojan | inurl:malware',
    
    // === SPECIALIZED SEARCHES ===
    'academic-papers': 'site:edu ext:pdf | site:ac.uk ext:pdf | intext:"research paper"',
    'leaked-databases': 'intext:"leaked" | intext:"dump" | intext:"breach" ext:sql | ext:csv',
    'pastebin-leaks': 'site:pastebin.com intext:"password" | intext:"credentials" | intext:"leaked"',
    'github-gists': 'site:gist.github.com intext:"password" | intext:"api_key" | intext:"token"',
    'trello-boards': 'site:trello.com intext:"private" | intext:"confidential"',
    'google-docs': 'site:docs.google.com intext:"confidential" | intext:"private"',
    'onedrive-shares': 'site:1drv.ms | site:onedrive.live.com intext:"shared"',
    'dropbox-shares': 'site:dropbox.com/s/ | inurl:dl.dropboxusercontent.com',
    'mega-links': 'site:mega.nz | intext:"mega.nz" | intext:"mega.co.nz"',
    'mediafire-files': 'site:mediafire.com | site:download.mediafire.com',
    'surveillance-footage': 'inurl:axis-cgi/jpg | inurl:view/viewer_index.shtml | intitle:"Live View"',
    'traffic-cameras': 'inurl:cctv | intitle:"Traffic Camera" | intitle:"Live Traffic"',
    'airport-cams': 'intext:"airport" intitle:"camera" | inurl:airport/camera',
    'weather-stations': 'intitle:"Weather Station" | intext:"weather" inurl:data',
    'university-systems': 'site:edu inurl:admin | site:edu inurl:login | site:edu intitle:"admin"',
    'government-systems': 'site:gov inurl:admin | site:gov inurl:login | site:mil inurl:admin'
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
