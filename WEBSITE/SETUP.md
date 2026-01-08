# 🚀 Quick Setup Guide - Legend House v9.0

## Welcome to Legend House!

This guide will help you get started with your advanced torrent platform in minutes.

---

## ⚡ Quick Start (3 Steps)

### Step 1: Extract Files
```bash
# Extract the website files to your server
unzip WEBSITE.zip
cd WEBSITE
```

### Step 2: Configure (Optional)
The platform works out-of-the-box with pre-configured Google OAuth credentials. 

**For production use**, update your credentials:
```bash
# Copy the example config
cp config.example.php config.php

# Edit config.php with your credentials
nano config.php
```

Get your Google OAuth credentials from:
https://console.cloud.google.com/

### Step 3: Start the Server
```bash
# Using PHP built-in server
php -S localhost:8000

# Or use Apache/Nginx
# Just point document root to the WEBSITE folder
```

**That's it!** Open your browser and visit:
```
http://localhost:8000
```

---

## 🎯 Features Overview

### For Visitors (No Login Required)
- ✅ Search across 10+ torrent sources
- ✅ Real-time search progress
- ✅ View torrent details (seeds, size, quality)
- ✅ Copy magnet links
- ✅ Stream videos in browser

### For Registered Users
- ✅ All visitor features +
- ✅ Download history tracking
- ✅ Upload torrent files
- ✅ Generate magnets from hash
- ✅ Save favorite torrents
- ✅ Profile management

---

## 🔐 Authentication Options

### Option 1: Sign In with Google (Recommended)
1. Click **"Sign in with Google"** on login page
2. Authorize the application
3. Done! You're logged in

### Option 2: Traditional Sign-Up
1. Click **"Sign Up"**
2. Enter username, email, password
3. Click **"Create Account"**
4. Log in with your credentials

---

## 🧲 Using the Torrent Download Center

### Method 1: Magnet Link
```
1. Click "Torrents" in navigation
2. Select "Magnet Link" tab
3. Paste your magnet link
4. Click "Download Torrent"
```

### Method 2: Torrent File
```
1. Click "Torrents" in navigation
2. Select "Torrent File" tab
3. Drag & drop .torrent file (or click to browse)
4. Click "Download Torrent"
```

### Method 3: Info Hash
```
1. Click "Torrents" in navigation
2. Select "Info Hash" tab
3. Enter 40-character hex hash
4. Add optional torrent name
5. Click "Generate Magnet Link"
```

---

## 🎬 Streaming Videos

### From Search Results
```
1. Search for a movie/TV show
2. Click "Watch Now" button
3. Wait for WebTorrent to connect
4. Video starts playing automatically
```

### From Torrent Center
```
1. Process your torrent (any method)
2. Click "Stream Video" in result
3. Enjoy in-browser playback
```

---

## 🛠️ Configuration Options

### Google OAuth Setup (Production)

1. **Create Google Cloud Project**
   - Go to: https://console.cloud.google.com/
   - Create new project

2. **Enable APIs**
   - Enable "Google+ API"
   - Enable "People API"

3. **Configure OAuth Consent**
   - Add authorized domains
   - Set application name
   - Add logo (optional)

4. **Create Credentials**
   - OAuth 2.0 Client ID
   - Application type: Web application
   - Authorized redirect URIs: `http://yoursite.com/google-callback.php`

5. **Update config.php**
   ```php
   define('GOOGLE_CLIENT_ID', 'your-client-id');
   define('GOOGLE_CLIENT_SECRET', 'your-client-secret');
   ```

### Environment Variables (Recommended for Production)
```bash
# Set environment variables
export GOOGLE_CLIENT_ID="your-client-id"
export GOOGLE_CLIENT_SECRET="your-client-secret"

# config.php will automatically use them
```

---

## 📱 Mobile Access

The platform is fully responsive! Access from:
- 📱 Smartphones (iOS/Android)
- 📲 Tablets (iPad, Android tablets)
- 💻 Laptops
- 🖥️ Desktops

---

## 🔒 Security Best Practices

### For Production Deployment:

1. **Use HTTPS**
   ```bash
   # Get free SSL certificate with Let's Encrypt
   sudo certbot --nginx -d yoursite.com
   ```

2. **Set Strong Passwords**
   - Minimum 12 characters
   - Mix of letters, numbers, symbols
   - Use password manager

3. **Regular Backups**
   ```bash
   # Backup database
   cp users.db users.db.backup
   ```

4. **Update Regularly**
   - Keep PHP updated
   - Monitor security advisories
   - Update dependencies

5. **Use Environment Variables**
   - Never commit credentials to git
   - Use `.env` files (excluded from git)
   - Use secret management services

---

## 🐛 Troubleshooting

### Database Permission Issues
```bash
chmod 755 .
chmod 666 users.db
```

### Google OAuth Redirect Mismatch
- Check redirect URI in Google Console
- Must match: `http://yoursite.com/google-callback.php`
- Include protocol (http:// or https://)

### WebTorrent Not Working
- Use modern browser (Chrome, Firefox, Edge)
- Enable JavaScript
- Check browser console for errors
- Some videos may not be streamable (depends on format)

### Search Not Working
- Check PHP cURL extension is enabled
- Check internet connectivity
- Some sources may be blocked by ISP
- Try different search terms

---

## 📚 Additional Resources

### Documentation
- **Full README**: See `README.md` for complete documentation
- **API Documentation**: See `api.php` for endpoint details
- **Config Template**: See `config.example.php` for all options

### Support
- Check GitHub Issues for common problems
- Review code comments for implementation details
- Contact: See repository for contact information

### Links
- Google Cloud Console: https://console.cloud.google.com/
- WebTorrent Documentation: https://webtorrent.io/
- PHP Documentation: https://www.php.net/docs.php

---

## 🎉 You're All Set!

Your Legend House platform is now ready to use. Start searching for torrents, streaming videos, and tracking your downloads!

**Enjoy your advanced torrent platform! 🚀**

---

## 📝 Quick Reference

| Feature | Page | Shortcut |
|---------|------|----------|
| Search Torrents | Home | `/` (focus search) |
| Torrent Center | /torrent.php | Click "Torrents" |
| Login | /login.php | Click "Login" |
| Sign Up | /signup.php | Click "Sign Up" |
| Watch Videos | /watch.php | Click "Watch Now" |
| Keyboard Shortcuts | Any page | Press `?` |

---

**Legend House v9.0** - Advanced Torrent & Streaming Platform  
Built with ❤️ for power users
