# Google OAuth Configuration Fix

## Issue
The error "You can't sign in to this app because it doesn't comply with Google's OAuth 2.0 policy" occurs when the redirect URI is not properly registered in Google Cloud Console.

## Solution Applied

### 1. Enhanced Redirect URI Configuration (config.php)
- Added support for `HTTP_X_FORWARDED_PROTO` header for proxy/load balancer scenarios
- Improved protocol detection to handle HTTPS properly
- Better handling of directory paths to avoid double slashes
- More robust host detection

### 2. Required Google Cloud Console Setup

To fix the OAuth error, you need to register the exact redirect URI in Google Cloud Console:

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Select your project
3. Navigate to "APIs & Services" > "Credentials"
4. Click on your OAuth 2.0 Client ID
5. Under "Authorized redirect URIs", add:
   ```
   http://legendbl.tech/google-callback.php
   https://legendbl.tech/google-callback.php
   ```
   (Add both HTTP and HTTPS versions if you support both)

### 3. Important Notes

- The redirect URI must match **exactly** - including protocol (http/https), domain, path, and trailing slashes
- If you're running behind a reverse proxy or load balancer, ensure the `X-Forwarded-Proto` header is set correctly
- The current configuration in config.php automatically detects:
  - Protocol (HTTP or HTTPS)
  - Host (domain name)
  - Directory path (if not in root)

### 4. Testing

After updating Google Cloud Console:
1. Clear your browser cache and cookies for the site
2. Try logging in with Google again
3. Check the error logs for any issues: `tail -f /var/log/apache2/error.log` (or your web server's error log)

### 5. Debugging

If issues persist, check these values in config.php by temporarily adding:
```php
error_log("Redirect URI: " . GOOGLE_REDIRECT_URI);
```

This will show you exactly what URI is being used, which you can then match in Google Cloud Console.

## Automatic URI Detection

The enhanced configuration now handles:
- Standard HTTP/HTTPS detection
- Reverse proxy scenarios (X-Forwarded-Proto header)
- Root and subdirectory installations
- Different domain names
- Load balancer configurations

No manual configuration needed in most cases!
