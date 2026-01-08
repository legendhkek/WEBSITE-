# ads.txt Configuration Guide for Legend House

## What is ads.txt?

The ads.txt (Authorized Digital Sellers) file is an IAB Tech Lab initiative that helps ensure that your digital ad inventory is only sold through sellers (such as AdSense) who you've identified as authorized.

## Setup Instructions

### 1. Get Your Publisher ID

If you're using **Google AdSense**:
1. Log in to your AdSense account at https://www.google.com/adsense
2. Go to Account → Settings
3. Find your Publisher ID (format: `pub-XXXXXXXXXXXXXXXX`)

### 2. Update ads.txt

Open the `ads.txt` file in the root directory and:

1. **For Google AdSense**, uncomment and update this line:
   ```
   google.com, pub-1940810089559549, DIRECT, f08c47fec0942fa0
   ```
   Replace `pub-1940810089559549` with your actual Publisher ID.

2. **For other ad networks**, add their entries following this format:
   ```
   domain.com, publisher-id, RELATIONSHIP, certification-authority-id
   ```

### 3. Common Ad Networks

Here are some common ad network entries (update with your actual IDs):

**Google AdSense:**
```
google.com, pub-XXXXXXXXXXXXXXXX, DIRECT, f08c47fec0942fa0
```

**Media.net:**
```
media.net, XXXXXXXXX, DIRECT
contextweb.com, XXXXXXXXX, DIRECT
```

**PropellerAds:**
```
propellerads.com, XXXX, DIRECT
```

**AdsTerra:**
```
adsterra.com, XXXXXX, DIRECT
```

### 4. Verify Your ads.txt

After uploading, verify your ads.txt file:

1. **Check accessibility**: Visit `https://yourdomain.com/ads.txt`
2. **Use validation tools**:
   - https://adstxt.guru/
   - https://adstxt.adngin.com/
   - Google AdSense (will show validation status)

### 5. Important Notes

- ✅ The file **must** be in the root directory
- ✅ The file **must** be accessible via HTTP/HTTPS
- ✅ The file **must** be served as `text/plain`
- ✅ One entry per line
- ✅ Case-insensitive
- ❌ No redirects allowed
- ❌ No authentication required

### 6. Validation Timeline

- Google may take up to **24 hours** to crawl and validate your ads.txt file
- Check AdSense dashboard for validation status
- Fix any warnings or errors shown

### 7. Troubleshooting

**Issue**: ads.txt not found (404 error)
- **Solution**: Ensure the file is in the root directory
- **Solution**: Check file permissions (644)
- **Solution**: Clear server cache

**Issue**: Incorrect format warnings
- **Solution**: Verify Publisher ID is correct
- **Solution**: Check for extra spaces or characters
- **Solution**: Use plain text editor (not Word)

**Issue**: AdSense still shows warning
- **Solution**: Wait 24-48 hours for crawling
- **Solution**: Request re-crawl in Search Console
- **Solution**: Check HTTPS version of the URL

### 8. Security

The `.htaccess` file is configured to:
- Serve ads.txt with correct MIME type
- Add `X-Robots-Tag: noindex` to prevent indexing
- Ensure proper content type headers

### 9. Multiple Domains

If you have multiple domains:
1. Upload ads.txt to each domain's root directory
2. Use the same Publisher ID across all domains
3. Or create domain-specific configurations

### 10. Best Practices

✅ **DO**:
- Keep the file updated
- Remove unauthorized sellers
- Use DIRECT relationship for your own accounts
- Document your changes with comments

❌ **DON'T**:
- Share your Publisher ID publicly
- Include fake or test entries
- Redirect the ads.txt file
- Put it in a subdirectory

## Example Complete ads.txt

```
# Legend House - Authorized Digital Sellers

# Google AdSense - Primary ad provider
google.com, pub-1234567890123456, DIRECT, f08c47fec0942fa0

# Media.net - Secondary provider
media.net, 123456, DIRECT
contextweb.com, 123456, DIRECT

# Comments start with #
# Update regularly to maintain ad inventory control
```

## Support

- **Google AdSense Help**: https://support.google.com/adsense/answer/7532444
- **IAB ads.txt Spec**: https://iabtechlab.com/ads-txt/
- **Validation Tool**: https://adstxt.guru/

---

**Last Updated**: January 8, 2026
**Status**: Ready for configuration
