# 🔐 Google OAuth Setup Guide

This guide will help you set up Google OAuth authentication for Legend House.

## Why Set Up Google OAuth?

With Google OAuth, users can:
- ✅ Sign in with one click using their Google account
- ✅ No need to remember another password
- ✅ Automatically import profile pictures
- ✅ Enhanced security with Google's authentication

**Without Google OAuth configured**, users can still use the website with email/password authentication.

## Prerequisites

- A Google account
- Access to Google Cloud Console
- Your website must be accessible via a public URL or localhost

## Step-by-Step Setup

### 1. Create a Google Cloud Project

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Click **"Select a project"** → **"New Project"**
3. Enter project name (e.g., "Legend House")
4. Click **"Create"**

### 2. Enable Required APIs

1. In your project, go to **"APIs & Services"** → **"Library"**
2. Search for and enable:
   - **Google+ API**
   - **People API**

### 3. Configure OAuth Consent Screen

1. Go to **"APIs & Services"** → **"OAuth consent screen"**
2. Choose **"External"** (for public access) or **"Internal"** (for organization only)
3. Fill in required information:
   - **App name**: Legend House
   - **User support email**: Your email
   - **Developer contact email**: Your email
4. Add scopes:
   - `openid`
   - `email`
   - `profile`
5. Add test users (for testing mode)
6. Click **"Save and Continue"**

### 4. Create OAuth 2.0 Credentials

1. Go to **"APIs & Services"** → **"Credentials"**
2. Click **"Create Credentials"** → **"OAuth client ID"**
3. Select **"Web application"**
4. Configure:
   - **Name**: Legend House Web Client
   - **Authorized JavaScript origins**:
     - `http://localhost:8000` (for local testing)
     - `https://yourdomain.com` (your production domain)
   - **Authorized redirect URIs**:
     - `http://localhost:8000/google-callback.php` (for local testing)
     - `https://yourdomain.com/google-callback.php` (your production domain)
5. Click **"Create"**
6. Copy the **Client ID** and **Client Secret** - you'll need these!

### 5. Configure Legend House

#### Option A: Using Environment Variables (Recommended for Production)

Set environment variables:

```bash
# Linux/Mac
export GOOGLE_CLIENT_ID="your-client-id-here"
export GOOGLE_CLIENT_SECRET="your-client-secret-here"

# Windows (PowerShell)
$env:GOOGLE_CLIENT_ID="your-client-id-here"
$env:GOOGLE_CLIENT_SECRET="your-client-secret-here"
```

#### Option B: Update config.php Directly

Edit `config.php` and update:

```php
define('GOOGLE_CLIENT_ID', 'your-client-id-here');
define('GOOGLE_CLIENT_SECRET', 'your-client-secret-here');
```

⚠️ **Security Warning**: If using Option B, make sure NOT to commit your credentials to git!

### 6. Test the Setup

1. Restart your web server
2. Go to your login page
3. Click **"Sign in with Google"**
4. You should be redirected to Google's authentication page
5. After authorizing, you should be redirected back and logged in

## Troubleshooting

### Error: "Error 401: invalid_client"

**Cause**: Invalid or missing Client ID/Secret

**Solutions**:
1. Check that you've set the correct Client ID and Secret
2. Verify they match exactly what's in Google Cloud Console
3. Make sure there are no extra spaces or quotes
4. Restart your web server after updating credentials

### Error: "Redirect URI mismatch"

**Cause**: The redirect URI in your request doesn't match what's configured in Google Cloud Console

**Solutions**:
1. Check the URL shown in the error message
2. Go to Google Cloud Console → Credentials
3. Add the exact redirect URI to your OAuth client's authorized redirect URIs
4. Make sure the protocol (http/https) matches
5. Make sure the domain and path match exactly

### Google Sign-In Button Does Nothing

**Cause**: OAuth not configured

**Solution**:
- Check browser console for errors
- Verify environment variables are set correctly
- Check that `config.php` has valid credentials

### "OAuth is not configured" Error Message

**Cause**: Google OAuth credentials are not set up

**Solution**:
- Follow the setup steps above to configure OAuth
- Or, users can simply use email/password authentication instead

## Disable Google OAuth

If you don't want to use Google OAuth:

1. Keep the default empty values in `config.php`:
   ```php
   define('GOOGLE_CLIENT_ID', '');
   define('GOOGLE_CLIENT_SECRET', '');
   ```

2. The "Sign in with Google" buttons will show an error message directing users to use email/password login

## Security Best Practices

1. ✅ **Never commit credentials to git**
   - Add `config.php` to `.gitignore`
   - Use environment variables in production

2. ✅ **Use HTTPS in production**
   - Google OAuth requires HTTPS for non-localhost domains
   - Get a free SSL certificate with Let's Encrypt

3. ✅ **Restrict authorized domains**
   - Only add domains you control to authorized origins/redirect URIs

4. ✅ **Keep credentials secret**
   - Don't share Client Secret in public repositories
   - Rotate credentials if exposed

5. ✅ **Monitor usage**
   - Check Google Cloud Console for API usage
   - Set up quotas and alerts

## Additional Resources

- [Google OAuth 2.0 Documentation](https://developers.google.com/identity/protocols/oauth2)
- [Google Cloud Console](https://console.cloud.google.com/)
- [OAuth 2.0 Playground](https://developers.google.com/oauthplayground/)

## Need Help?

- Check the Google Cloud Console error logs
- Review the browser console for JavaScript errors
- Check your server's PHP error logs
- Ensure all required PHP extensions are enabled (cURL, SQLite3)

---

✅ **Once configured, users will see the "Sign in with Google" button working perfectly!**

Without configuration, users can still use the website with traditional email/password authentication.
