<?php
/**
 * ═══════════════════════════════════════════════════════════════════════════════
 * LEGEND HOUSE - Main Landing Page
 * SEO-optimized landing page for search engines
 * ═══════════════════════════════════════════════════════════════════════════════
 */
session_start();
require_once __DIR__ . '/auth.php';

// For logged-in users, redirect to dashboard
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

// SEO Configuration
$seo_title = 'Legend House - #1 Downloading Platform | Stream & Download Movies, TV Shows, Games | LegendBL.tech';
$seo_description = 'Legend House (LegendBL.tech) is the ultimate downloading platform. Stream movies directly in browser, download via magnet links, search 10+ torrent sources. Features Google Dorker, Proxy Scraper, AI Assistant. Free, fast, no registration required.';
$seo_keywords = 'legend house, legendbl, legendbltech, legendbl.tech, downloading platform, best torrent site, stream movies free, download movies, torrent search engine, magnet links, watch movies online free, download games free, tv shows download, anime download, webtorrent streaming, free movie streaming, torrent finder, proxy scraper, google dorker';
$seo_url = 'https://legendbl.tech/';
$seo_canonical = 'https://legendbl.tech/';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <?php include 'seo-head.php'; ?>
    
    <title><?php echo htmlspecialchars($seo_title); ?></title>
    
    <!-- Google AdSense -->
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-1940810089559549"
         crossorigin="anonymous"></script>
    
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🏠</text></svg>">
    
    <!-- Additional Landing Page Structured Data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebSite",
        "name": "Legend House",
        "alternateName": ["LegendBL", "LegendBL.tech", "Legend BL Tech", "LegendBLTech"],
        "url": "https://legendbl.tech",
        "description": "The ultimate downloading platform - Stream and download movies, TV shows, games, software, and more.",
        "potentialAction": {
            "@type": "SearchAction",
            "target": {
                "@type": "EntryPoint",
                "urlTemplate": "https://legendbl.tech/home.php?q={search_term_string}"
            },
            "query-input": "required name=search_term_string"
        }
    }
    </script>
    
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "Legend House",
        "url": "https://legendbl.tech",
        "logo": "https://legendbl.tech/logo.png",
        "sameAs": [],
        "contactPoint": {
            "@type": "ContactPoint",
            "contactType": "customer support",
            "availableLanguage": "English"
        }
    }
    </script>
    
    <style>
        .landing-cta {
            display: flex;
            gap: 16px;
            justify-content: center;
            margin-top: 32px;
            flex-wrap: wrap;
        }
        .cta-btn {
            padding: 16px 32px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }
        .cta-primary {
            background: linear-gradient(135deg, #f59e0b, #ef4444);
            color: white;
            box-shadow: 0 4px 20px rgba(245, 158, 11, 0.3);
        }
        .cta-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(245, 158, 11, 0.4);
        }
        .cta-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .cta-secondary:hover {
            background: rgba(255, 255, 255, 0.15);
            border-color: rgba(255, 255, 255, 0.3);
        }
        .seo-content {
            max-width: 800px;
            margin: 60px auto;
            padding: 0 24px;
            text-align: center;
        }
        .seo-content h2 {
            font-size: 28px;
            color: #f0f6fc;
            margin-bottom: 16px;
        }
        .seo-content p {
            color: #8b949e;
            line-height: 1.8;
            margin-bottom: 24px;
        }
        .feature-highlights {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 24px;
            margin-top: 40px;
        }
        .feature-highlight {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 24px;
            text-align: left;
        }
        .feature-highlight h3 {
            color: #f0f6fc;
            font-size: 18px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .feature-highlight p {
            color: #8b949e;
            font-size: 14px;
            margin: 0;
        }
    </style>
</head>
<body>
    <!-- Wallpaper -->
    <div class="wallpaper-container" id="wallpaperContainer"></div>
    <div class="wallpaper-overlay"></div>

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
                <a href="login.php" class="nav-btn">
                    <span class="nav-btn-icon">🔐</span>
                    <span class="nav-btn-text">Login</span>
                </a>
                <a href="signup.php" class="nav-btn nav-btn-featured">
                    <span class="nav-btn-icon">✨</span>
                    <span class="nav-btn-text">Sign Up</span>
                </a>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-badge">
                <span class="hero-badge-dot"></span>
                <span class="hero-badge-text">🎬 #1 Downloading Platform - Stream Movies • Download Games • Watch TV Shows</span>
            </div>
            <h1 class="hero-title">
                <span class="title-gradient">Legend</span> House
            </h1>
            <p class="hero-subtitle">
                The ultimate downloading platform. Stream movies directly in browser or download via magnet links. 
                Search 10+ sources instantly. Free, fast, and no registration required.
            </p>
            
            <!-- Call to Action Buttons -->
            <div class="landing-cta">
                <a href="home.php" class="cta-btn cta-primary">
                    🔍 Start Searching
                </a>
                <a href="signup.php" class="cta-btn cta-secondary">
                    ✨ Create Free Account
                </a>
                <a href="tools.php" class="cta-btn cta-secondary">
                    🛠️ Explore Tools
                </a>
            </div>
            
            <!-- Quick Search Tags -->
            <div class="quick-tags" style="margin-top: 40px;">
                <span class="quick-tag-label">🔥 Popular Searches:</span>
                <a href="home.php?q=Avatar+3" class="quick-tag">Avatar 3</a>
                <a href="home.php?q=Dune+2" class="quick-tag">Dune 2</a>
                <a href="home.php?q=GTA+6" class="quick-tag">GTA 6</a>
                <a href="home.php?q=The+Witcher" class="quick-tag">The Witcher</a>
                <a href="home.php?q=Cyberpunk" class="quick-tag">Cyberpunk</a>
            </div>
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
                    <div class="feature-icon">🛠️</div>
                    <h3>20+ Free Tools</h3>
                    <p>Google Dorker, Proxy Scraper, Link Shortener, AI Assistant, and more</p>
                </div>
            </div>
        </div>
    </section>

    <!-- SEO Content Section -->
    <section class="seo-content">
        <h2>Welcome to Legend House - LegendBL.tech</h2>
        <p>
            <strong>Legend House</strong> (also known as <strong>LegendBL</strong> or <strong>LegendBL.tech</strong>) is the premier 
            <strong>downloading platform</strong> for streaming and downloading movies, TV shows, games, software, anime, and more. 
            Our platform aggregates content from over 10+ torrent sources, providing you with the best quality downloads and instant streaming capabilities.
        </p>
        
        <h2>Why Choose Legend House?</h2>
        <div class="feature-highlights">
            <div class="feature-highlight">
                <h3>🎬 Stream Movies Free</h3>
                <p>Watch movies directly in your browser without downloading. Our WebTorrent technology enables instant streaming of your favorite content in HD quality.</p>
            </div>
            <div class="feature-highlight">
                <h3>🔍 Multi-Source Search</h3>
                <p>Search across 10+ torrent sources simultaneously. Find movies, TV shows, games, software, and more with a single search query.</p>
            </div>
            <div class="feature-highlight">
                <h3>🛠️ Powerful Tools</h3>
                <p>Access 20+ free online tools including Google Dorker, Proxy Scraper, Rotating Proxy Maker, Link Shortener, and AI Chat Assistant.</p>
            </div>
            <div class="feature-highlight">
                <h3>🤖 AI-Powered</h3>
                <p>Get intelligent search suggestions, contextual help, and automated assistance with our built-in AI Chat powered by GPT-4o.</p>
            </div>
        </div>
        
        <h2>Our Tools & Features</h2>
        <p>
            At <strong>LegendBL.tech</strong>, we offer more than just torrent search. Our comprehensive suite of tools includes:
        </p>
        <ul style="text-align: left; color: #8b949e; line-height: 2;">
            <li><strong>Google Dorker</strong> - Advanced search with 100+ operators for OSINT and research</li>
            <li><strong>Proxy Scraper</strong> - Scrape proxies from 100+ sources with auto-validation</li>
            <li><strong>Rotating Proxy Maker</strong> - Create rotating proxy pools with health monitoring</li>
            <li><strong>Link Shortener</strong> - Create short links with analytics and QR codes</li>
            <li><strong>WebTorrent Player</strong> - Stream torrents directly in browser</li>
            <li><strong>AI Chat Assistant</strong> - Get help 24/7 with our intelligent assistant</li>
        </ul>
    </section>

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
                        LEGEND HOUSE - LegendBL.tech
                    </span>
                    <span class="footer-divider"></span>
                    <span class="footer-stats">#1 Downloading Platform</span>
                </div>
                <div class="footer-right">
                    <a href="login.php" style="color: #8b949e; margin-right: 16px;">Login</a>
                    <a href="signup.php" style="color: #8b949e;">Sign Up</a>
                </div>
            </div>
        </div>
    </footer>

    <script src="script.js"></script>
</body>
</html>
