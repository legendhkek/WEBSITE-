<?php
/**
 * ═══════════════════════════════════════════════════════════════════════════════
 * LEGEND HOUSE - SEO Header Include
 * Include this file in all pages for comprehensive SEO optimization
 * ═══════════════════════════════════════════════════════════════════════════════
 */

// Default SEO values - override these before including this file
$seo_title = $seo_title ?? 'Legend House - Stream & Download Movies, TV Shows, Games';
$seo_description = $seo_description ?? 'Legend House (LegendBL.tech) - The ultimate downloading platform. Stream and download movies, TV shows, games, software, anime, and more. 10+ sources, instant access, no registration required.';
$seo_keywords = $seo_keywords ?? 'legend house, legendbl, legendbltech, downloading platform, stream movies, download movies, torrent search, magnet links, free movies, watch movies online, download games, tv shows download, anime download, webtorrent streaming';
$seo_image = $seo_image ?? 'https://legendbl.tech/og-image.png';
$seo_url = $seo_url ?? 'https://legendbl.tech' . $_SERVER['REQUEST_URI'];
$seo_type = $seo_type ?? 'website';
$seo_site_name = 'Legend House';
$seo_twitter = $seo_twitter ?? '@legendbltech';
$seo_canonical = $seo_canonical ?? $seo_url;

// Clean URLs
$seo_canonical = strtok($seo_canonical, '?'); // Remove query strings for canonical
?>

<!-- Primary Meta Tags -->
<meta name="title" content="<?php echo htmlspecialchars($seo_title); ?>">
<meta name="description" content="<?php echo htmlspecialchars($seo_description); ?>">
<meta name="keywords" content="<?php echo htmlspecialchars($seo_keywords); ?>">
<meta name="author" content="Legend House">
<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
<meta name="googlebot" content="index, follow">
<meta name="bingbot" content="index, follow">
<link rel="canonical" href="<?php echo htmlspecialchars($seo_canonical); ?>">

<!-- Open Graph / Facebook -->
<meta property="og:type" content="<?php echo htmlspecialchars($seo_type); ?>">
<meta property="og:url" content="<?php echo htmlspecialchars($seo_url); ?>">
<meta property="og:title" content="<?php echo htmlspecialchars($seo_title); ?>">
<meta property="og:description" content="<?php echo htmlspecialchars($seo_description); ?>">
<meta property="og:image" content="<?php echo htmlspecialchars($seo_image); ?>">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:site_name" content="<?php echo htmlspecialchars($seo_site_name); ?>">
<meta property="og:locale" content="en_US">

<!-- Twitter Card -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:url" content="<?php echo htmlspecialchars($seo_url); ?>">
<meta name="twitter:title" content="<?php echo htmlspecialchars($seo_title); ?>">
<meta name="twitter:description" content="<?php echo htmlspecialchars($seo_description); ?>">
<meta name="twitter:image" content="<?php echo htmlspecialchars($seo_image); ?>">
<meta name="twitter:site" content="<?php echo htmlspecialchars($seo_twitter); ?>">
<meta name="twitter:creator" content="<?php echo htmlspecialchars($seo_twitter); ?>">

<!-- Additional SEO Tags -->
<meta name="theme-color" content="#0d1117">
<meta name="msapplication-TileColor" content="#0d1117">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="Legend House">
<meta name="application-name" content="Legend House">
<meta name="mobile-web-app-capable" content="yes">

<!-- Geo Tags (Optional - helps with local SEO) -->
<meta name="geo.region" content="US">
<meta name="geo.placename" content="United States">

<!-- Language & Content Tags -->
<meta http-equiv="Content-Language" content="en">
<meta name="language" content="English">
<meta name="revisit-after" content="1 days">
<meta name="rating" content="general">
<meta name="distribution" content="global">

<!-- Structured Data - Organization -->
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "WebSite",
    "name": "Legend House",
    "alternateName": ["LegendBL", "LegendBLTech", "Legend BL Tech"],
    "url": "https://legendbl.tech",
    "description": "<?php echo addslashes($seo_description); ?>",
    "potentialAction": {
        "@type": "SearchAction",
        "target": {
            "@type": "EntryPoint",
            "urlTemplate": "https://legendbl.tech/home.php?q={search_term_string}"
        },
        "query-input": "required name=search_term_string"
    },
    "publisher": {
        "@type": "Organization",
        "name": "Legend House",
        "url": "https://legendbl.tech",
        "logo": {
            "@type": "ImageObject",
            "url": "https://legendbl.tech/logo.png"
        }
    }
}
</script>

<!-- Structured Data - Software Application -->
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "SoftwareApplication",
    "name": "Legend House",
    "alternateName": "LegendBL.tech",
    "applicationCategory": "MultimediaApplication",
    "operatingSystem": "Web Browser",
    "offers": {
        "@type": "Offer",
        "price": "0",
        "priceCurrency": "USD"
    },
    "aggregateRating": {
        "@type": "AggregateRating",
        "ratingValue": "4.8",
        "ratingCount": "1250",
        "bestRating": "5",
        "worstRating": "1"
    },
    "description": "Stream and download movies, TV shows, games, software, and more. No registration required.",
    "featureList": [
        "Stream movies directly in browser",
        "Download via magnet links",
        "10+ torrent sources",
        "WebTorrent streaming",
        "AI-powered search",
        "Google Dorker tool",
        "Proxy Scraper",
        "No registration required"
    ]
}
</script>

<!-- Preconnect to improve performance -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preconnect" href="https://pagead2.googlesyndication.com">
<link rel="dns-prefetch" href="https://cdn.jsdelivr.net">

<!-- Alternate URLs for different languages (if applicable) -->
<link rel="alternate" hreflang="en" href="https://legendbl.tech">
<link rel="alternate" hreflang="x-default" href="https://legendbl.tech">
