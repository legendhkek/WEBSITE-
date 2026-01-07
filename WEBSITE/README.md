# ⚡ Ultra Advanced Universal Download Search Engine v6.0

The most powerful download search engine with **parallel multi-source fetching**, **intelligent caching**, and **direct API integrations**. Features a stunning dark theme with rotating 4K wallpapers.

![Version](https://img.shields.io/badge/version-6.0.0-blue)
![PHP](https://img.shields.io/badge/PHP-8.0+-purple)
![License](https://img.shields.io/badge/license-MIT-green)
![Status](https://img.shields.io/badge/status-ultra%20fast-brightgreen)

## 🚀 What's New in v6.0

- ⚡ **Parallel HTTP fetching** with `curl_multi` - searches all sources simultaneously
- 💾 **Intelligent caching** - cached results returned instantly
- 🎯 **Direct API integrations** - YTS, EZTV, TPB, Nyaa, TorrentGalaxy, BitSearch
- 🌐 **Multi-engine web search** - DuckDuckGo + Bing as fallback
- 🔍 **Universal page scraping** - extracts downloads from ANY website
- 📊 **Seed health indicators** - visual seed/peer status

## ✨ Features

### ⚡ Ultra-Fast Multi-Source Search
- **8 sources queried in parallel**: YTS, EZTV, TPB, Nyaa, TorrentGalaxy, BitSearch, DuckDuckGo, Bing
- **Direct API access** for YTS, EZTV, and TPB (no HTML scraping = faster)
- **Parallel page fetching** with `curl_multi_*` functions
- **Smart caching** - repeated searches return instantly (10 min cache)

### 📥 Universal Download Extraction
- **Magnet links**: Highest priority, instant detection
- **Torrent files**: Direct `.torrent` file links
- **Direct downloads**: MP4, MKV, AVI, ZIP, RAR, 7Z, EXE, ISO, APK, DMG
- **Cloud storage**: Google Drive, MEGA, MediaFire, GoFile, Pixeldrain, Dropbox, 1Fichier, Rapidgator, Nitroflare

### 🎨 Premium UI v6.0
- **Dark glassmorphism theme** with blur effects
- **4K wallpaper rotation** every 25 seconds
- **Animated card reveals** with staggered entrance
- **Seed health badges** with color coding
- **Source-specific badge colors** (YTS=green, TPB=red, etc.)
- **Fully responsive** for all devices

### 🛠️ Advanced Features
- **Link verification** - check if downloads are still active
- **Link bypass** - skip URL shorteners
- **Download tracking** - local history storage
- **Category filtering** - Movies, TV, Games, Software, Anime, Music, Ebooks

## 🚀 Quick Start

### Requirements
- PHP 8.0 or higher
- cURL extension enabled
- DOM extension enabled

### Installation

1. **Clone or download** this repository

2. **Start the PHP development server**:
   ```bash
   php -S localhost:8000
   ```

3. **Open in browser**:
   ```
   http://localhost:8000
   ```

## 📁 Project Structure

```
├── api.php          # Ultra-fast backend API (v6.0)
├── index.php        # Main HTML page
├── script.js        # Frontend JavaScript (v6.0)
├── style.css        # Premium dark theme CSS (v6.0)
├── watch.php        # Media watch page (optional)
├── watch.js         # Watch page scripts (optional)
└── README.md        # This documentation
```

## 🔌 API Endpoints

### Search (Main Endpoint)
```
GET /api.php?action=search&query=QUERY&category=CATEGORY
```

**Parameters:**
- `query` (required): Search term
- `category` (optional): all, movies, tv, games, software, anime, music, ebooks

**Response:**
```json
{
  "success": true,
  "query": "batman",
  "category": "movies",
  "results": [
    {
      "id": 1,
      "name": "The Batman (2022) [1080p] [BluRay]",
      "downloadUrl": "magnet:?xt=urn:btih:...",
      "size": "2.5 GB",
      "seeds": 1250,
      "peers": 340,
      "quality": "1080p",
      "type": "Magnet",
      "source": "YTS.mx",
      "sourceUrl": "https://yts.mx/movies/...",
      "contentType": "Movie",
      "isMagnet": true
    }
  ],
  "total": 100,
  "sources": ["YTS", "TPB", "EZTV"],
  "time": "3500ms",
  "cached": false
}
```

### Extract Downloads from URL
```
GET /api.php?action=extract&url=URL
```
Extracts all download links from any webpage.

### Bypass URL Shortener
```
GET /api.php?action=bypass&url=URL
```
Follows redirects to get the final destination URL.

### Verify Link
```
GET /api.php?action=verify&url=URL
```
Checks if a download link is active and returns file size.

### Get Categories
```
GET /api.php?action=categories
```
Returns available search categories with icons.

### API Stats
```
GET /api.php?action=stats
```
Returns API version, sources, and features.

## ⚡ How It Works

### Phase 1: Direct API Sources (Fastest)
1. **YTS API** → Movie torrents (1080p, 4K BluRay)
2. **EZTV API** → TV show torrents
3. **TPB API** → General torrents (all categories)
4. **Nyaa Scraper** → Anime torrents
5. **TorrentGalaxy** → Mixed content
6. **BitSearch** → Torrent aggregator

### Phase 2: Web Search (Fallback)
If Phase 1 returns < 20 results:
1. Search DuckDuckGo and Bing
2. Parallel fetch top 8 result pages
3. Extract magnet links, torrents, direct files, cloud links

### Phase 3: Processing
1. Remove duplicates by URL hash
2. Sort by seeds (highest first), then by priority
3. Add content type detection (Movie, TV Show, Game, etc.)
4. Cache results for 10 minutes

## 🔒 Supported Download Types

| Type | Priority | Detection |
|------|----------|-----------|
| Magnet Links | 100 | `magnet:?xt=urn:btih:` |
| Torrent Files | 95 | `.torrent` extension |
| Video Files | 90 | `.mp4`, `.mkv`, `.avi`, `.mov`, `.wmv` |
| Archives | 90 | `.zip`, `.rar`, `.7z` |
| Executables | 90 | `.exe`, `.iso`, `.dmg`, `.msi`, `.apk` |
| Audio | 90 | `.mp3`, `.flac`, `.wav`, `.aac` |
| Documents | 90 | `.pdf`, `.epub`, `.mobi` |
| Google Drive | 88 | `drive.google.com` |
| MEGA | 88 | `mega.nz`, `mega.io` |
| MediaFire | 88 | `mediafire.com` |
| GoFile | 88 | `gofile.io` |
| Pixeldrain | 88 | `pixeldrain.com` |
| Dropbox | 88 | `dropbox.com` |
| 1Fichier | 85 | `1fichier.com` |
| Rapidgator | 85 | `rapidgator.net` |
| Nitroflare | 85 | `nitroflare.com` |

## 💾 Caching System

- **Search results**: Cached for 10 minutes
- **Individual source results**: Cached for 10 minutes
- **Cache storage**: System temp directory
- **Auto-cleanup**: Old cache files removed hourly

```php
// Cache configuration in api.php
define('CACHE_TTL', 1800);        // 30 minutes general
define('SEARCH_CACHE_TTL', 600);  // 10 minutes for searches
```

## 🎨 UI Customization

### Wallpapers
Edit `wallpapers` array in `script.js`:
```javascript
const wallpapers = [
    'https://images.unsplash.com/photo-...?w=1920&q=85',
    // Add more 4K wallpapers...
];
```

### Theme Colors
Edit CSS variables in `style.css`:
```css
:root {
    --primary: #6366f1;        /* Main accent color */
    --accent: #22d3ee;         /* Secondary accent */
    --success: #10b981;        /* Success/seeds good */
    --warning: #f59e0b;        /* Magnet color */
    --danger: #ef4444;         /* Error/seeds low */
    --bg-dark: #0f0f1a;        /* Background */
}
```

### Source Badge Colors
```css
.source-badge.source-yts { background: linear-gradient(135deg, #10b981, #059669); }
.source-badge.source-tpb { background: linear-gradient(135deg, #ef4444, #dc2626); }
.source-badge.source-nyaa { background: linear-gradient(135deg, #ec4899, #db2777); }
```

## 📊 Performance Tips

1. **Use categories** - Filtering reduces sources to query
2. **Repeat searches** - Cached results return instantly
3. **Shorter queries** - More specific = faster results
4. **Check seed count** - High seeds = faster downloads

## ⚠️ Disclaimer

This tool is for **educational purposes only**. Users are responsible for:
- Complying with local laws and regulations
- Respecting copyright and intellectual property rights
- Using downloads only for content they have legal rights to access

The developers are not responsible for misuse of this tool.

## 📄 License

MIT License - Free to use, modify, and distribute.

## 🤝 Contributing

Contributions welcome! Feel free to:
- Report bugs
- Suggest new sources
- Improve performance
- Submit pull requests

---

⚡ **Ultra Advanced Universal Download Search Engine v6.0**  
Made with ❤️ for power users
