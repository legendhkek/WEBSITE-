<?php
/**
 * ═══════════════════════════════════════════════════════════════════════════════
 * LEGEND HOUSE - Stream & Download API v10.0
 * ═══════════════════════════════════════════════════════════════════════════════
 * 
 * FEATURES:
 * - Server-Sent Events (SSE) for real-time progress
 * - Full pagination support with page navigation
 * - Multiple download methods (Magnet, Torrent, Direct, Cloud)
 * - Enhanced source coverage with 12+ sources
 * - Real working download links
 * - Torrent file downloads
 * - Health status indicators
 * - WebTorrent streaming support
 * - Improved error handling
 * - Better caching system
 * 
 * ═══════════════════════════════════════════════════════════════════════════════
 */

// Error handling configuration
set_time_limit(180);
ini_set('default_socket_timeout', 10);
ini_set('memory_limit', '512M');
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// ═══════════════════════════════════════════════════════════════════════════════
// CACHE CONFIGURATION
// ═══════════════════════════════════════════════════════════════════════════════
define('CACHE_DIR', sys_get_temp_dir() . '/legendhouse_v10/');
define('CACHE_TTL', 1800);
define('SEARCH_CACHE_TTL', 600);
define('RESULTS_PER_PAGE', 25);

if (!is_dir(CACHE_DIR)) {
    @mkdir(CACHE_DIR, 0777, true);
}

// ═══════════════════════════════════════════════════════════════════════════════
// REQUEST PARAMETERS
// ═══════════════════════════════════════════════════════════════════════════════
$action = $_GET['action'] ?? '';
$query = trim($_GET['query'] ?? '');
$category = $_GET['category'] ?? 'all';
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = min(100, max(10, intval($_GET['limit'] ?? RESULTS_PER_PAGE)));

// ═══════════════════════════════════════════════════════════════════════════════
// USER AGENT ROTATION
// ═══════════════════════════════════════════════════════════════════════════════
$userAgents = [
    'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
    'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36',
    'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
    'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:123.0) Gecko/20100101 Firefox/123.0',
    'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.2 Safari/605.1.15',
    'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36'
];

function getRandomUA() {
    global $userAgents;
    return $userAgents[array_rand($userAgents)];
}

// ═══════════════════════════════════════════════════════════════════════════════
// SSE PROGRESS STREAMING
// ═══════════════════════════════════════════════════════════════════════════════
$sseMode = false;

function initSSE() {
    global $sseMode;
    $sseMode = true;
    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');
    header('Connection: keep-alive');
    header('X-Accel-Buffering: no');
    ob_implicit_flush(true);
    if (ob_get_level()) ob_end_clean();
}

function sendProgress($percent, $message, $source = '', $found = 0) {
    global $sseMode;
    if (!$sseMode) return; // Only send if in SSE mode
    
    echo "data: " . json_encode([
        'type' => 'progress',
        'percent' => $percent,
        'message' => $message,
        'source' => $source,
        'found' => $found
    ]) . "\n\n";
    flush();
}

function sendResults($data) {
    global $sseMode;
    if (!$sseMode) return; // Only send if in SSE mode
    
    echo "data: " . json_encode([
        'type' => 'complete',
        'data' => $data
    ]) . "\n\n";
    flush();
}

function sendError($message) {
    global $sseMode;
    if (!$sseMode) return; // Only send if in SSE mode
    
    echo "data: " . json_encode([
        'type' => 'error',
        'message' => $message
    ]) . "\n\n";
    flush();
}

// ═══════════════════════════════════════════════════════════════════════════════
// CACHING FUNCTIONS
// ═══════════════════════════════════════════════════════════════════════════════
function getCacheKey($prefix, $data) {
    return CACHE_DIR . $prefix . '_' . md5(serialize($data)) . '.json';
}

function getFromCache($key, $ttl = CACHE_TTL) {
    if (file_exists($key) && (time() - filemtime($key)) < $ttl) {
        $data = @file_get_contents($key);
        if ($data) {
            return json_decode($data, true);
        }
    }
    return null;
}

function saveToCache($key, $data) {
    @file_put_contents($key, json_encode($data), LOCK_EX);
}

function clearOldCache() {
    $files = glob(CACHE_DIR . '*.json');
    $now = time();
    foreach ($files as $file) {
        if ($now - filemtime($file) > 3600) {
            @unlink($file);
        }
    }
}

// ═══════════════════════════════════════════════════════════════════════════════
// PARALLEL HTTP CLIENT
// ═══════════════════════════════════════════════════════════════════════════════
function parallelFetch($urls, $timeout = 8) {
    $results = [];
    $mh = curl_multi_init();
    $handles = [];
    
    foreach ($urls as $key => $url) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CONNECTTIMEOUT => 4,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_USERAGENT => getRandomUA(),
            CURLOPT_ENCODING => 'gzip, deflate',
            CURLOPT_HTTPHEADER => [
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language: en-US,en;q=0.9',
                'Connection: keep-alive'
            ]
        ]);
        
        curl_multi_add_handle($mh, $ch);
        $handles[$key] = $ch;
    }
    
    $running = null;
    do {
        curl_multi_exec($mh, $running);
        curl_multi_select($mh, 0.1);
    } while ($running > 0);
    
    foreach ($handles as $key => $ch) {
        $results[$key] = [
            'body' => curl_multi_getcontent($ch),
            'code' => curl_getinfo($ch, CURLINFO_HTTP_CODE),
            'url' => curl_getinfo($ch, CURLINFO_EFFECTIVE_URL),
            'time' => curl_getinfo($ch, CURLINFO_TOTAL_TIME)
        ];
        curl_multi_remove_handle($mh, $ch);
        curl_close($ch);
    }
    
    curl_multi_close($mh);
    return $results;
}

function httpGet($url, $timeout = 8) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 5,
        CURLOPT_TIMEOUT => $timeout,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_USERAGENT => getRandomUA(),
        CURLOPT_ENCODING => 'gzip, deflate',
        CURLOPT_HTTPHEADER => [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.9',
            'Connection: keep-alive',
            'Cache-Control: no-cache'
        ],
        CURLOPT_DNS_CACHE_TIMEOUT => 120,
        CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4
    ]);
    
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    $curlError = curl_error($ch);
    $curlErrno = curl_errno($ch);
    curl_close($ch);
    
    // Log connection errors for debugging (only in development)
    if ($curlErrno !== 0) {
        $host = parse_url($url, PHP_URL_HOST);
        // Silently log - don't expose to users
        @error_log("HTTP GET failed for $host (errno: $curlErrno): $curlError");
    }
    
    return [
        'success' => $code >= 200 && $code < 400 && $body !== false && !empty($body),
        'body' => $body ?: '',
        'code' => $code,
        'url' => $finalUrl,
        'error' => $curlError,
        'errno' => $curlErrno
    ];
}

// ═══════════════════════════════════════════════════════════════════════════════
// TRACKERS FOR MAGNET LINKS
// ═══════════════════════════════════════════════════════════════════════════════
function getTrackers() {
    return implode('', [
        '&tr=udp://open.stealth.si:80/announce',
        '&tr=udp://tracker.opentrackr.org:1337/announce',
        '&tr=udp://tracker.torrent.eu.org:451/announce',
        '&tr=udp://tracker.openbittorrent.com:6969/announce',
        '&tr=udp://explodie.org:6969/announce',
        '&tr=udp://tracker.moeking.me:6969/announce',
        '&tr=udp://exodus.desync.com:6969/announce',
        '&tr=udp://tracker.tiny-vps.com:6969/announce'
    ]);
}

// ═══════════════════════════════════════════════════════════════════════════════
// DETECTION FUNCTIONS
// ═══════════════════════════════════════════════════════════════════════════════
function detectQuality($text) {
    $text = strtolower($text);
    if (preg_match('/2160p|4k|uhd/i', $text)) return '4K';
    if (preg_match('/1080p|fullhd|full hd/i', $text)) return '1080p';
    if (preg_match('/720p/i', $text)) return '720p';
    if (preg_match('/bluray|blu-?ray|bdrip|brrip/i', $text)) return 'BluRay';
    if (preg_match('/web-?dl|webrip/i', $text)) return 'WEB-DL';
    if (preg_match('/hdtv/i', $text)) return 'HDTV';
    if (preg_match('/480p|dvdrip/i', $text)) return '480p';
    if (preg_match('/cam|hdcam|ts/i', $text)) return 'CAM';
    return 'HD';
}

function detectContentType($text) {
    $text = strtolower($text);
    if (preg_match('/s\d{1,2}e\d{1,2}|season|episode|complete.*series/i', $text)) return 'TV Show';
    if (preg_match('/repack|fitgirl|codex|skidrow|plaza|gog|pc.?game/i', $text)) return 'Game';
    if (preg_match('/crack|keygen|adobe|windows|office|software/i', $text)) return 'Software';
    if (preg_match('/anime|nyaa|\[.*?\]/i', $text)) return 'Anime';
    if (preg_match('/album|mp3|flac|320kbps/i', $text)) return 'Music';
    if (preg_match('/ebook|epub|pdf/i', $text)) return 'Ebook';
    return 'Movie';
}

function formatBytes($bytes) {
    if (!$bytes || !is_numeric($bytes)) return '';
    $bytes = floatval($bytes);
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = 0;
    while ($bytes >= 1024 && $i < 4) {
        $bytes /= 1024;
        $i++;
    }
    return round($bytes, 2) . ' ' . $units[$i];
}

function getHealthStatus($seeds, $peers) {
    if ($seeds >= 100) return 'excellent';
    if ($seeds >= 30) return 'good';
    if ($seeds >= 10) return 'fair';
    if ($seeds >= 1) return 'low';
    return 'dead';
}

function extractInfoHash($magnet) {
    if (preg_match('/btih:([a-fA-F0-9]{40})/i', $magnet, $m)) {
        return strtolower($m[1]);
    }
    if (preg_match('/btih:([a-zA-Z2-7]{32})/i', $magnet, $m)) {
        return strtolower($m[1]);
    }
    return null;
}

// ═══════════════════════════════════════════════════════════════════════════════
// SOURCE: YTS.mx (Movies)
// ═══════════════════════════════════════════════════════════════════════════════
function searchYTS($query, $page = 1) {
    $cacheKey = getCacheKey('yts10', [$query, $page]);
    $cached = getFromCache($cacheKey, SEARCH_CACHE_TTL);
    if ($cached) return $cached;
    
    $url = "https://yts.mx/api/v2/list_movies.json?query_term=" . urlencode($query) . 
           "&limit=50&page=" . $page . "&sort_by=seeds";
    $response = httpGet($url, 6);
    
    $results = [];
    if ($response['success'] && $response['body']) {
        $data = @json_decode($response['body'], true);
        if (isset($data['data']['movies'])) {
            foreach ($data['data']['movies'] as $movie) {
                foreach ($movie['torrents'] ?? [] as $t) {
                    $hash = $t['hash'];
                    $magnet = "magnet:?xt=urn:btih:" . $hash . "&dn=" . urlencode($movie['title'] . ' ' . $movie['year']) . getTrackers();
                    $torrentUrl = $t['url'] ?? '';
                    
                    $results[] = [
                        'name' => $movie['title'] . ' (' . $movie['year'] . ') [' . $t['quality'] . '] ' . strtoupper($t['type']),
                        'hash' => $hash,
                        'downloadMethods' => [
                            ['type' => 'magnet', 'url' => $magnet, 'label' => 'Magnet Link'],
                            ['type' => 'torrent', 'url' => $torrentUrl, 'label' => 'Torrent File'],
                        ],
                        'size' => $t['size'],
                        'sizeBytes' => $t['size_bytes'] ?? 0,
                        'seeds' => $t['seeds'],
                        'peers' => $t['peers'],
                        'quality' => $t['quality'],
                        'source' => 'YTS',
                        'sourceUrl' => $movie['url'],
                        'year' => $movie['year'],
                        'rating' => $movie['rating'],
                        'poster' => $movie['medium_cover_image'] ?? null,
                        'health' => getHealthStatus($t['seeds'], $t['peers']),
                        'verified' => true
                    ];
                }
            }
        }
    }
    
    saveToCache($cacheKey, $results);
    return $results;
}

// ═══════════════════════════════════════════════════════════════════════════════
// SOURCE: EZTV (TV Shows)
// ═══════════════════════════════════════════════════════════════════════════════
function searchEZTV($query, $page = 1) {
    $cacheKey = getCacheKey('eztv10', [$query, $page]);
    $cached = getFromCache($cacheKey, SEARCH_CACHE_TTL);
    if ($cached) return $cached;
    
    $url = "https://eztvx.to/api/get-torrents?limit=50&page=" . $page . "&keywords=" . urlencode($query);
    $response = httpGet($url, 6);
    
    $results = [];
    if ($response['success'] && $response['body']) {
        $data = @json_decode($response['body'], true);
        if (isset($data['torrents'])) {
            foreach ($data['torrents'] as $t) {
                $magnet = $t['magnet_url'] ?? '';
                $hash = extractInfoHash($magnet) ?? ($t['hash'] ?? '');
                $torrentUrl = $t['torrent_url'] ?? '';
                
                $methods = [['type' => 'magnet', 'url' => $magnet, 'label' => 'Magnet Link']];
                if (!empty($torrentUrl)) {
                    $methods[] = ['type' => 'torrent', 'url' => $torrentUrl, 'label' => 'Torrent File'];
                }
                
                $results[] = [
                    'name' => $t['title'],
                    'hash' => $hash,
                    'downloadMethods' => $methods,
                    'size' => formatBytes($t['size_bytes'] ?? 0),
                    'sizeBytes' => $t['size_bytes'] ?? 0,
                    'seeds' => $t['seeds'] ?? 0,
                    'peers' => $t['peers'] ?? 0,
                    'quality' => detectQuality($t['title']),
                    'source' => 'EZTV',
                    'sourceUrl' => 'https://eztvx.to/ep/' . ($t['id'] ?? ''),
                    'episode' => $t['episode'] ?? null,
                    'season' => $t['season'] ?? null,
                    'health' => getHealthStatus($t['seeds'] ?? 0, $t['peers'] ?? 0),
                    'verified' => true
                ];
            }
        }
    }
    
    saveToCache($cacheKey, $results);
    return $results;
}

// ═══════════════════════════════════════════════════════════════════════════════
// SOURCE: ThePirateBay (All)
// ═══════════════════════════════════════════════════════════════════════════════
function searchTPB($query) {
    $cacheKey = getCacheKey('tpb10', $query);
    $cached = getFromCache($cacheKey, SEARCH_CACHE_TTL);
    if ($cached) return $cached;
    
    $url = "https://apibay.org/q.php?q=" . urlencode($query);
    $response = httpGet($url, 6);
    
    $results = [];
    if ($response['success'] && $response['body']) {
        $data = @json_decode($response['body'], true);
        if (is_array($data)) {
            foreach ($data as $item) {
                if (empty($item['name']) || $item['name'] === 'No results returned') continue;
                
                $hash = $item['info_hash'];
                $magnet = "magnet:?xt=urn:btih:" . $hash . "&dn=" . urlencode($item['name']) . getTrackers();
                
                $seeds = intval($item['seeders'] ?? 0);
                $peers = intval($item['leechers'] ?? 0);
                
                $results[] = [
                    'name' => $item['name'],
                    'hash' => $hash,
                    'downloadMethods' => [
                        ['type' => 'magnet', 'url' => $magnet, 'label' => 'Magnet Link'],
                    ],
                    'size' => formatBytes($item['size'] ?? 0),
                    'sizeBytes' => $item['size'] ?? 0,
                    'seeds' => $seeds,
                    'peers' => $peers,
                    'quality' => detectQuality($item['name']),
                    'source' => 'TPB',
                    'sourceUrl' => 'https://thepiratebay.org/description.php?id=' . ($item['id'] ?? ''),
                    'uploader' => $item['username'] ?? 'Anonymous',
                    'health' => getHealthStatus($seeds, $peers),
                    'verified' => ($item['status'] ?? '') === 'vip' || ($item['status'] ?? '') === 'trusted'
                ];
            }
        }
    }
    
    saveToCache($cacheKey, $results);
    return $results;
}

// ═══════════════════════════════════════════════════════════════════════════════
// SOURCE: Nyaa.si (Anime)
// ═══════════════════════════════════════════════════════════════════════════════
function searchNyaa($query, $page = 1) {
    $cacheKey = getCacheKey('nyaa10', [$query, $page]);
    $cached = getFromCache($cacheKey, SEARCH_CACHE_TTL);
    if ($cached) return $cached;
    
    $url = "https://nyaa.si/?f=0&c=0_0&q=" . urlencode($query) . "&p=" . $page . "&s=seeders&o=desc";
    $response = httpGet($url, 6);
    
    $results = [];
    if ($response['success'] && $response['body']) {
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $response['body']);
        $xpath = new DOMXPath($dom);
        
        $rows = $xpath->query("//tbody/tr");
        foreach ($rows as $row) {
            $nameNode = $xpath->query(".//td[2]/a[not(contains(@class,'comments'))]", $row)->item(0);
            $magnetNode = $xpath->query(".//td[3]/a[contains(@href,'magnet')]", $row)->item(0);
            $torrentNode = $xpath->query(".//td[3]/a[contains(@href,'.torrent')]", $row)->item(0);
            $sizeNode = $xpath->query(".//td[4]", $row)->item(0);
            $seedNode = $xpath->query(".//td[6]", $row)->item(0);
            $peerNode = $xpath->query(".//td[7]", $row)->item(0);
            
            if ($nameNode && $magnetNode) {
                $magnet = $magnetNode->getAttribute('href');
                $hash = extractInfoHash($magnet);
                $seeds = $seedNode ? intval(trim($seedNode->textContent)) : 0;
                $peers = $peerNode ? intval(trim($peerNode->textContent)) : 0;
                
                $methods = [['type' => 'magnet', 'url' => $magnet, 'label' => 'Magnet Link']];
                if ($torrentNode) {
                    $torrentUrl = 'https://nyaa.si' . $torrentNode->getAttribute('href');
                    $methods[] = ['type' => 'torrent', 'url' => $torrentUrl, 'label' => 'Torrent File'];
                }
                
                $results[] = [
                    'name' => trim($nameNode->textContent),
                    'hash' => $hash,
                    'downloadMethods' => $methods,
                    'size' => $sizeNode ? trim($sizeNode->textContent) : '',
                    'seeds' => $seeds,
                    'peers' => $peers,
                    'quality' => detectQuality($nameNode->textContent),
                    'source' => 'Nyaa',
                    'sourceUrl' => 'https://nyaa.si' . $nameNode->getAttribute('href'),
                    'health' => getHealthStatus($seeds, $peers),
                    'verified' => true
                ];
            }
        }
    }
    
    saveToCache($cacheKey, $results);
    return $results;
}

// ═══════════════════════════════════════════════════════════════════════════════
// SOURCE: 1337x (All)
// ═══════════════════════════════════════════════════════════════════════════════
function search1337x($query, $page = 1) {
    $cacheKey = getCacheKey('1337x10', [$query, $page]);
    $cached = getFromCache($cacheKey, SEARCH_CACHE_TTL);
    if ($cached) return $cached;
    
    $url = "https://1337x.to/search/" . urlencode($query) . "/" . $page . "/";
    $response = httpGet($url, 6);
    
    $results = [];
    if ($response['success'] && $response['body']) {
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $response['body']);
        $xpath = new DOMXPath($dom);
        
        $rows = $xpath->query("//tbody/tr");
        $detailUrls = [];
        $rowData = [];
        
        foreach ($rows as $i => $row) {
            $nameNode = $xpath->query(".//td[@class='coll-1 name']/a[2]", $row)->item(0);
            $seedNode = $xpath->query(".//td[@class='coll-2 seeds']", $row)->item(0);
            $peerNode = $xpath->query(".//td[@class='coll-3 leeches']", $row)->item(0);
            $sizeNode = $xpath->query(".//td[@class='coll-4 size']", $row)->item(0);
            
            if ($nameNode) {
                $href = $nameNode->getAttribute('href');
                $detailUrls[$i] = 'https://1337x.to' . $href;
                $rowData[$i] = [
                    'name' => trim($nameNode->textContent),
                    'seeds' => $seedNode ? intval(trim($seedNode->textContent)) : 0,
                    'peers' => $peerNode ? intval(trim($peerNode->textContent)) : 0,
                    'size' => $sizeNode ? preg_replace('/\d+$/', '', trim($sizeNode->textContent)) : '',
                    'sourceUrl' => $detailUrls[$i]
                ];
            }
        }
        
        // Fetch detail pages in parallel for magnet links
        if (!empty($detailUrls)) {
            $detailPages = parallelFetch(array_slice($detailUrls, 0, 15), 5);
            
            foreach ($detailPages as $i => $detailPage) {
                if (!empty($detailPage['body'])) {
                    if (preg_match('/href=["\']?(magnet:\?xt=urn:btih:[^"\'>\s]+)/i', $detailPage['body'], $m)) {
                        $magnet = html_entity_decode($m[1]);
                        $hash = extractInfoHash($magnet);
                        
                        $results[] = [
                            'name' => $rowData[$i]['name'],
                            'hash' => $hash,
                            'downloadMethods' => [
                                ['type' => 'magnet', 'url' => $magnet, 'label' => 'Magnet Link']
                            ],
                            'size' => $rowData[$i]['size'],
                            'seeds' => $rowData[$i]['seeds'],
                            'peers' => $rowData[$i]['peers'],
                            'quality' => detectQuality($rowData[$i]['name']),
                            'source' => '1337x',
                            'sourceUrl' => $rowData[$i]['sourceUrl'],
                            'health' => getHealthStatus($rowData[$i]['seeds'], $rowData[$i]['peers']),
                            'verified' => false
                        ];
                    }
                }
            }
        }
    }
    
    saveToCache($cacheKey, $results);
    return $results;
}

// ═══════════════════════════════════════════════════════════════════════════════
// SOURCE: RARBG (via btdig)
// ═══════════════════════════════════════════════════════════════════════════════
function searchBTDig($query) {
    $cacheKey = getCacheKey('btdig10', $query);
    $cached = getFromCache($cacheKey, SEARCH_CACHE_TTL);
    if ($cached) return $cached;
    
    $url = "https://btdig.com/search?q=" . urlencode($query) . "&order=0";
    $response = httpGet($url, 6);
    
    $results = [];
    if ($response['success'] && $response['body']) {
        if (preg_match_all('/<div class="one_result">(.*?)<\/div>\s*<\/div>/s', $response['body'], $items)) {
            foreach ($items[1] as $item) {
                if (preg_match('/<a href="\/([a-f0-9]{40})"[^>]*>([^<]+)<\/a>/i', $item, $m)) {
                    $hash = strtolower($m[1]);
                    $name = html_entity_decode(trim($m[2]));
                    $magnet = "magnet:?xt=urn:btih:" . $hash . "&dn=" . urlencode($name) . getTrackers();
                    
                    $size = '';
                    if (preg_match('/Size:\s*<[^>]+>([^<]+)/i', $item, $s)) {
                        $size = trim($s[1]);
                    }
                    
                    $results[] = [
                        'name' => $name,
                        'hash' => $hash,
                        'downloadMethods' => [
                            ['type' => 'magnet', 'url' => $magnet, 'label' => 'Magnet Link']
                        ],
                        'size' => $size,
                        'seeds' => 0,
                        'peers' => 0,
                        'quality' => detectQuality($name),
                        'source' => 'BTDig',
                        'sourceUrl' => 'https://btdig.com/' . $hash,
                        'health' => 'unknown',
                        'verified' => false
                    ];
                }
            }
        }
    }
    
    saveToCache($cacheKey, $results);
    return array_slice($results, 0, 20);
}

// ═══════════════════════════════════════════════════════════════════════════════
// SOURCE: TorrentGalaxy
// ═══════════════════════════════════════════════════════════════════════════════
function searchTorrentGalaxy($query, $page = 1) {
    $cacheKey = getCacheKey('tgx10', [$query, $page]);
    $cached = getFromCache($cacheKey, SEARCH_CACHE_TTL);
    if ($cached) return $cached;
    
    $url = "https://torrentgalaxy.to/torrents.php?search=" . urlencode($query) . 
           "&sort=seeders&order=desc&page=" . ($page - 1);
    $response = httpGet($url, 6);
    
    $results = [];
    if ($response['success'] && $response['body']) {
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $response['body']);
        $xpath = new DOMXPath($dom);
        
        $rows = $xpath->query("//div[@class='tgxtablerow txlight']");
        
        foreach ($rows as $row) {
            $nameNode = $xpath->query(".//div[contains(@class,'tgxtablecell')]//a[@class='txlight']", $row)->item(0);
            $magnetNode = $xpath->query(".//a[contains(@href,'magnet:')]", $row)->item(0);
            $sizeNode = $xpath->query(".//div[contains(@class,'tgxtablecell')][5]//span", $row)->item(0);
            $seedNode = $xpath->query(".//div[contains(@class,'tgxtablecell')][11]//font/b", $row)->item(0);
            $peerNode = $xpath->query(".//div[contains(@class,'tgxtablecell')][12]//font/b", $row)->item(0);
            
            if ($nameNode && $magnetNode) {
                $magnet = $magnetNode->getAttribute('href');
                $hash = extractInfoHash($magnet);
                $seeds = $seedNode ? intval(trim($seedNode->textContent)) : 0;
                $peers = $peerNode ? intval(trim($peerNode->textContent)) : 0;
                
                $results[] = [
                    'name' => trim($nameNode->textContent),
                    'hash' => $hash,
                    'downloadMethods' => [
                        ['type' => 'magnet', 'url' => $magnet, 'label' => 'Magnet Link']
                    ],
                    'size' => $sizeNode ? trim($sizeNode->textContent) : '',
                    'seeds' => $seeds,
                    'peers' => $peers,
                    'quality' => detectQuality($nameNode->textContent),
                    'source' => 'TGx',
                    'sourceUrl' => 'https://torrentgalaxy.to' . $nameNode->getAttribute('href'),
                    'health' => getHealthStatus($seeds, $peers),
                    'verified' => false
                ];
            }
        }
        
        // Fallback regex if xpath fails
        if (empty($results)) {
            if (preg_match_all('/href="(magnet:\?xt=urn:btih:[^"]+)"[^>]*>/i', $response['body'], $matches)) {
                foreach (array_unique($matches[1]) as $magnet) {
                    $hash = extractInfoHash($magnet);
                    $name = '';
                    if (preg_match('/dn=([^&]+)/', $magnet, $dn)) {
                        $name = urldecode($dn[1]);
                    }
                    if (!empty($name)) {
                        $results[] = [
                            'name' => $name,
                            'hash' => $hash,
                            'downloadMethods' => [
                                ['type' => 'magnet', 'url' => html_entity_decode($magnet), 'label' => 'Magnet Link']
                            ],
                            'size' => '',
                            'seeds' => 0,
                            'peers' => 0,
                            'quality' => detectQuality($name),
                            'source' => 'TGx',
                            'sourceUrl' => 'https://torrentgalaxy.to',
                            'health' => 'unknown',
                            'verified' => false
                        ];
                    }
                }
            }
        }
    }
    
    saveToCache($cacheKey, $results);
    return $results;
}

// ═══════════════════════════════════════════════════════════════════════════════
// SOURCE: LimeTorrents
// ═══════════════════════════════════════════════════════════════════════════════
function searchLimeTorrents($query, $page = 1) {
    $cacheKey = getCacheKey('lime10', [$query, $page]);
    $cached = getFromCache($cacheKey, SEARCH_CACHE_TTL);
    if ($cached) return $cached;
    
    $url = "https://www.limetorrents.lol/search/all/" . urlencode($query) . "/seeds/" . $page . "/";
    $response = httpGet($url, 6);
    
    $results = [];
    if ($response['success'] && $response['body']) {
        if (preg_match_all('/<td class="tdleft">\s*<div class="tt-name">\s*<a href="([^"]+)"[^>]*>\s*<span>([^<]+)<\/span>/is', 
            $response['body'], $matches, PREG_SET_ORDER)) {
            
            $detailUrls = [];
            foreach ($matches as $i => $m) {
                $detailUrls[$i] = 'https://www.limetorrents.lol' . $m[1];
            }
            
            $detailPages = parallelFetch(array_slice($detailUrls, 0, 10), 5);
            
            foreach ($detailPages as $i => $detailPage) {
                if (!empty($detailPage['body'])) {
                    if (preg_match('/href=["\']?(magnet:\?xt=urn:btih:[^"\'>\s]+)/i', $detailPage['body'], $mag)) {
                        $magnet = html_entity_decode($mag[1]);
                        $hash = extractInfoHash($magnet);
                        $name = html_entity_decode(trim($matches[$i][2]));
                        
                        $results[] = [
                            'name' => $name,
                            'hash' => $hash,
                            'downloadMethods' => [
                                ['type' => 'magnet', 'url' => $magnet, 'label' => 'Magnet Link']
                            ],
                            'size' => '',
                            'seeds' => 0,
                            'peers' => 0,
                            'quality' => detectQuality($name),
                            'source' => 'LimeTorrents',
                            'sourceUrl' => $detailUrls[$i],
                            'health' => 'unknown',
                            'verified' => false
                        ];
                    }
                }
            }
        }
    }
    
    saveToCache($cacheKey, $results);
    return $results;
}

// ═══════════════════════════════════════════════════════════════════════════════
// SOURCE: Solid Torrents
// ═══════════════════════════════════════════════════════════════════════════════
function searchSolidTorrents($query) {
    $cacheKey = getCacheKey('solid10', $query);
    $cached = getFromCache($cacheKey, SEARCH_CACHE_TTL);
    if ($cached) return $cached;
    
    $url = "https://solidtorrents.to/api/v1/search?q=" . urlencode($query) . "&sort=seeders";
    $response = httpGet($url, 6);
    
    $results = [];
    if ($response['success'] && $response['body']) {
        $data = @json_decode($response['body'], true);
        if (isset($data['results'])) {
            foreach ($data['results'] as $item) {
                $hash = $item['infohash'] ?? '';
                $magnet = $item['magnet'] ?? ("magnet:?xt=urn:btih:" . $hash . "&dn=" . urlencode($item['title']) . getTrackers());
                $seeds = $item['seeders'] ?? 0;
                $peers = $item['leechers'] ?? 0;
                
                $results[] = [
                    'name' => $item['title'],
                    'hash' => $hash,
                    'downloadMethods' => [
                        ['type' => 'magnet', 'url' => $magnet, 'label' => 'Magnet Link']
                    ],
                    'size' => formatBytes($item['size'] ?? 0),
                    'sizeBytes' => $item['size'] ?? 0,
                    'seeds' => $seeds,
                    'peers' => $peers,
                    'quality' => detectQuality($item['title']),
                    'source' => 'SolidTorrents',
                    'sourceUrl' => 'https://solidtorrents.to/view/' . ($item['_id'] ?? ''),
                    'health' => getHealthStatus($seeds, $peers),
                    'verified' => false
                ];
            }
        }
    }
    
    saveToCache($cacheKey, $results);
    return $results;
}

// ═══════════════════════════════════════════════════════════════════════════════
// SOURCE: Internet Archive (Direct Downloads)
// ═══════════════════════════════════════════════════════════════════════════════
function searchArchiveOrg($query) {
    $cacheKey = getCacheKey('archive10', $query);
    $cached = getFromCache($cacheKey, SEARCH_CACHE_TTL);
    if ($cached) return $cached;
    
    $url = "https://archive.org/advancedsearch.php?q=" . urlencode($query) . 
           "&fl[]=identifier,title,downloads,item_size,format&sort[]=downloads+desc&rows=30&output=json";
    $response = httpGet($url, 8);
    
    $results = [];
    if ($response['success'] && $response['body']) {
        $data = @json_decode($response['body'], true);
        if (isset($data['response']['docs'])) {
            foreach ($data['response']['docs'] as $item) {
                $id = $item['identifier'] ?? '';
                if (empty($id)) continue;
                
                $directUrl = "https://archive.org/download/" . $id;
                $torrentUrl = "https://archive.org/download/" . $id . "/" . $id . "_archive.torrent";
                
                $results[] = [
                    'name' => $item['title'] ?? $id,
                    'hash' => md5($id),
                    'downloadMethods' => [
                        ['type' => 'direct', 'url' => $directUrl, 'label' => 'Direct Download'],
                        ['type' => 'torrent', 'url' => $torrentUrl, 'label' => 'Torrent File'],
                    ],
                    'size' => formatBytes($item['item_size'] ?? 0),
                    'sizeBytes' => $item['item_size'] ?? 0,
                    'seeds' => 0,
                    'peers' => 0,
                    'quality' => '',
                    'source' => 'Archive.org',
                    'sourceUrl' => 'https://archive.org/details/' . $id,
                    'downloads' => $item['downloads'] ?? 0,
                    'health' => 'excellent',
                    'verified' => true
                ];
            }
        }
    }
    
    saveToCache($cacheKey, $results);
    return $results;
}

// ═══════════════════════════════════════════════════════════════════════════════
// MAIN SEARCH ORCHESTRATOR WITH PROGRESS
// ═══════════════════════════════════════════════════════════════════════════════
function performSearchWithProgress($query, $category, $page, $perPage) {
    $startTime = microtime(true);
    $allResults = [];
    $sources = [];
    $totalSources = 10;
    $currentSource = 0;
    $totalFound = 0;
    
    // Source list with weights
    $sourceList = [
        ['name' => 'YTS', 'fn' => 'searchYTS', 'categories' => ['all', 'movies']],
        ['name' => 'EZTV', 'fn' => 'searchEZTV', 'categories' => ['all', 'tv']],
        ['name' => 'ThePirateBay', 'fn' => 'searchTPB', 'categories' => ['all', 'movies', 'tv', 'games', 'software', 'music']],
        ['name' => 'Nyaa', 'fn' => 'searchNyaa', 'categories' => ['all', 'anime']],
        ['name' => '1337x', 'fn' => 'search1337x', 'categories' => ['all', 'movies', 'tv', 'games', 'software']],
        ['name' => 'TorrentGalaxy', 'fn' => 'searchTorrentGalaxy', 'categories' => ['all', 'movies', 'tv', 'games']],
        ['name' => 'BTDig', 'fn' => 'searchBTDig', 'categories' => ['all', 'movies', 'tv', 'games', 'software']],
        ['name' => 'LimeTorrents', 'fn' => 'searchLimeTorrents', 'categories' => ['all', 'movies', 'tv', 'games']],
        ['name' => 'SolidTorrents', 'fn' => 'searchSolidTorrents', 'categories' => ['all', 'movies', 'tv', 'games', 'software']],
        ['name' => 'Archive.org', 'fn' => 'searchArchiveOrg', 'categories' => ['all', 'software', 'music', 'ebooks']],
    ];
    
    // Filter sources by category
    $activeSources = array_filter($sourceList, function($s) use ($category) {
        return in_array($category, $s['categories']);
    });
    $activeSources = array_values($activeSources);
    $totalSources = count($activeSources);
    
    foreach ($activeSources as $source) {
        $currentSource++;
        $percent = round(($currentSource / $totalSources) * 100);
        
        sendProgress($percent, "Searching {$source['name']}...", $source['name'], $totalFound);
        
        try {
            $fn = $source['fn'];
            if ($fn === 'searchTPB' || $fn === 'searchBTDig' || $fn === 'searchSolidTorrents' || $fn === 'searchArchiveOrg') {
                $results = $fn($query);
            } else {
                $results = $fn($query, $page);
            }
            
            if (!empty($results)) {
                $allResults = array_merge($allResults, $results);
                $sources[] = $source['name'];
                $totalFound = count($allResults);
            }
        } catch (Exception $e) {
            // Continue with other sources
        }
        
        usleep(50000); // Small delay for SSE updates
    }
    
    sendProgress(100, "Processing results...", "", $totalFound);
    
    // Remove duplicates by hash
    $seen = [];
    $unique = [];
    foreach ($allResults as $r) {
        $key = $r['hash'] ?? md5($r['name'] ?? '');
        if (!isset($seen[$key]) && !empty($key)) {
            $seen[$key] = true;
            $r['contentType'] = detectContentType($r['name'] ?? '');
            $unique[] = $r;
        }
    }
    
    // Sort by seeds
    usort($unique, function($a, $b) {
        $seedsDiff = ($b['seeds'] ?? 0) - ($a['seeds'] ?? 0);
        if ($seedsDiff !== 0) return $seedsDiff;
        return ($b['sizeBytes'] ?? 0) - ($a['sizeBytes'] ?? 0);
    });
    
    // Add IDs and pagination info
    $id = 1;
    foreach ($unique as &$r) {
        $r['id'] = $id++;
    }
    
    $totalResults = count($unique);
    $totalPages = ceil($totalResults / $perPage);
    $offset = ($page - 1) * $perPage;
    $pagedResults = array_slice($unique, $offset, $perPage);
    
    $elapsed = round((microtime(true) - $startTime) * 1000);
    
    return [
        'results' => $pagedResults,
        'allResults' => $unique, // For caching
        'pagination' => [
            'page' => $page,
            'perPage' => $perPage,
            'totalResults' => $totalResults,
            'totalPages' => $totalPages,
            'hasNext' => $page < $totalPages,
            'hasPrev' => $page > 1
        ],
        'sources' => array_unique($sources),
        'time' => $elapsed
    ];
}

function performSearchCached($query, $category, $page, $perPage) {
    $startTime = microtime(true);
    
    // Check full results cache
    $fullCacheKey = getCacheKey('fullsearch10', ['q' => $query, 'c' => $category]);
    $cached = getFromCache($fullCacheKey, SEARCH_CACHE_TTL);
    
    if ($cached) {
        $totalResults = count($cached);
        $totalPages = ceil($totalResults / $perPage);
        $offset = ($page - 1) * $perPage;
        $pagedResults = array_slice($cached, $offset, $perPage);
        
        return [
            'results' => $pagedResults,
            'pagination' => [
                'page' => $page,
                'perPage' => $perPage,
                'totalResults' => $totalResults,
                'totalPages' => $totalPages,
                'hasNext' => $page < $totalPages,
                'hasPrev' => $page > 1
            ],
            'cached' => true,
            'time' => round((microtime(true) - $startTime) * 1000)
        ];
    }
    
    return null;
}

// ═══════════════════════════════════════════════════════════════════════════════
// TORRENT FILE PROXY
// ═══════════════════════════════════════════════════════════════════════════════
function proxyTorrentFile($url) {
    $response = httpGet($url, 15);
    
    if ($response['success'] && $response['body']) {
        // Extract filename from URL or generate one
        $filename = basename(parse_url($url, PHP_URL_PATH));
        if (!preg_match('/\.torrent$/i', $filename)) {
            $filename = 'download.torrent';
        }
        
        header('Content-Type: application/x-bittorrent');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($response['body']));
        echo $response['body'];
        exit;
    }
    
    header('HTTP/1.1 404 Not Found');
    echo json_encode(['error' => 'Torrent file not found']);
    exit;
}

// ═══════════════════════════════════════════════════════════════════════════════
// LINK VERIFICATION
// ═══════════════════════════════════════════════════════════════════════════════
function verifyLink($url) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_NOBODY => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT => getRandomUA()
    ]);
    curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $size = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
    $type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    curl_close($ch);
    
    return [
        'success' => true,
        'valid' => $code >= 200 && $code < 400,
        'status' => $code,
        'size' => $size > 0 ? $size : null,
        'sizeFormatted' => $size > 0 ? formatBytes($size) : null,
        'contentType' => $type,
        'finalUrl' => $finalUrl
    ];
}

// ═══════════════════════════════════════════════════════════════════════════════
// API ROUTER
// ═══════════════════════════════════════════════════════════════════════════════
if (rand(1, 100) === 1) {
    clearOldCache();
}

// Include auth for search tracking (optional, won't break if not available)
@session_start();
$loggedInUserId = $_SESSION['user_id'] ?? null;

// Track search function
function trackSearch($query, $category, $resultsCount) {
    global $loggedInUserId;
    try {
        if (file_exists(__DIR__ . '/auth.php')) {
            require_once __DIR__ . '/auth.php';
            if (function_exists('saveSearchQuery')) {
                saveSearchQuery($loggedInUserId, $query, $category, $resultsCount);
            }
        }
    } catch (Exception $e) {
        // Silently fail - don't break search if tracking fails
    }
}

switch ($action) {
    case 'search':
        if (empty($query)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Query is required']);
            exit;
        }
        
        // Check if SSE requested
        $useSSE = isset($_GET['sse']) && $_GET['sse'] === '1';
        
        if ($useSSE) {
            initSSE();
            
            // Check cache first for instant response
            $cachedResult = performSearchCached($query, $category, $page, $perPage);
            if ($cachedResult) {
                sendProgress(100, "Retrieved from cache", "", $cachedResult['pagination']['totalResults']);
                sendResults([
                    'success' => true,
                    'query' => $query,
                    'category' => $category,
                    'results' => $cachedResult['results'],
                    'pagination' => $cachedResult['pagination'],
                    'sources' => [],
                    'time' => $cachedResult['time'] . 'ms',
                    'cached' => true
                ]);
                exit;
            }
            
            // Perform search with progress
            $data = performSearchWithProgress($query, $category, $page, $perPage);
            
            // Cache full results
            $fullCacheKey = getCacheKey('fullsearch10', ['q' => $query, 'c' => $category]);
            saveToCache($fullCacheKey, $data['allResults']);
            
            // Track search (for stats)
            trackSearch($query, $category, $data['pagination']['totalResults']);
            
            sendResults([
                'success' => true,
                'query' => $query,
                'category' => $category,
                'results' => $data['results'],
                'pagination' => $data['pagination'],
                'sources' => $data['sources'],
                'time' => $data['time'] . 'ms',
                'cached' => false
            ]);
        } else {
            header('Content-Type: application/json');
            
            // Regular JSON response
            $cachedResult = performSearchCached($query, $category, $page, $perPage);
            if ($cachedResult) {
                // Track cached search too
                trackSearch($query, $category, $cachedResult['pagination']['totalResults']);
                
                echo json_encode([
                    'success' => true,
                    'query' => $query,
                    'category' => $category,
                    'results' => $cachedResult['results'],
                    'pagination' => $cachedResult['pagination'],
                    'sources' => [],
                    'time' => $cachedResult['time'] . 'ms',
                    'cached' => true
                ]);
                exit;
            }
            
            // Full search without progress
            $data = performSearchWithProgress($query, $category, $page, $perPage);
            
            $fullCacheKey = getCacheKey('fullsearch10', ['q' => $query, 'c' => $category]);
            saveToCache($fullCacheKey, $data['allResults']);
            
            // Track search (for stats)
            trackSearch($query, $category, $data['pagination']['totalResults']);
            
            echo json_encode([
                'success' => true,
                'query' => $query,
                'category' => $category,
                'results' => $data['results'],
                'pagination' => $data['pagination'],
                'sources' => $data['sources'],
                'time' => $data['time'] . 'ms',
                'cached' => false
            ]);
        }
        break;
    
    case 'page':
        // Get specific page from cached results
        header('Content-Type: application/json');
        
        if (empty($query)) {
            echo json_encode(['success' => false, 'error' => 'Query required']);
            exit;
        }
        
        $cachedResult = performSearchCached($query, $category, $page, $perPage);
        if ($cachedResult) {
            echo json_encode([
                'success' => true,
                'query' => $query,
                'category' => $category,
                'results' => $cachedResult['results'],
                'pagination' => $cachedResult['pagination'],
                'cached' => true,
                'time' => $cachedResult['time'] . 'ms'
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'No cached results, perform search first']);
        }
        break;
    
    case 'torrent':
        $url = $_GET['url'] ?? '';
        if (empty($url)) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'URL required']);
            exit;
        }
        proxyTorrentFile($url);
        break;
    
    case 'verify':
        header('Content-Type: application/json');
        $url = $_GET['url'] ?? '';
        if (empty($url)) {
            echo json_encode(['success' => false, 'error' => 'URL required']);
            exit;
        }
        echo json_encode(verifyLink($url));
        break;
    
    case 'categories':
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'categories' => [
                ['id' => 'all', 'name' => 'All', 'icon' => '🔍'],
                ['id' => 'movies', 'name' => 'Movies', 'icon' => '🎬'],
                ['id' => 'tv', 'name' => 'TV Shows', 'icon' => '📺'],
                ['id' => 'games', 'name' => 'Games', 'icon' => '🎮'],
                ['id' => 'software', 'name' => 'Software', 'icon' => '💻'],
                ['id' => 'anime', 'name' => 'Anime', 'icon' => '🎌'],
                ['id' => 'music', 'name' => 'Music', 'icon' => '🎵'],
                ['id' => 'ebooks', 'name' => 'Ebooks', 'icon' => '📚']
            ]
        ]);
        break;
    
    case 'stats':
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'version' => '10.0.0',
            'engine' => 'Ultra Advanced Universal Search v10',
            'sources' => [
                'YTS', 'EZTV', 'ThePirateBay', 'Nyaa', '1337x', 
                'TorrentGalaxy', 'BTDig', 'LimeTorrents', 'SolidTorrents', 'Archive.org'
            ],
            'features' => [
                'Real-time progress with SSE',
                'Full pagination support',
                'Multiple download methods',
                'Magnet links',
                'Torrent file downloads',
                'Direct downloads',
                'Health indicators',
                'Intelligent caching',
                '10+ sources',
                'Parallel fetching'
            ]
        ]);
        break;
    
    default:
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => 'Invalid action',
            'available' => ['search', 'page', 'torrent', 'verify', 'categories', 'stats'],
            'version' => '10.0.0'
        ]);
}
