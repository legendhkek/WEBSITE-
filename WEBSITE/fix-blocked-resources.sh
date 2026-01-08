#!/bin/bash
# Fix blocked external resources by removing them

echo "Fixing blocked external resources..."

# Remove Google Fonts - use system fonts instead
find . -name "*.php" -type f -exec sed -i 's|<link href="https://fonts\.googleapis\.com/css[^>]*>||g' {} \;

# Remove Google AdSense (keep comment for reference)
find . -name "*.php" -type f -exec sed -i 's|<script async src="https://pagead2\.googlesyndication\.com/[^>]*></script>|<!-- Google AdSense removed - add your own ads -->|g' {} \;

# Remove Google Fonts preconnect
find . -name "*.php" -type f -exec sed -i 's|<link rel="preconnect" href="https://fonts\.googleapis\.com[^>]*>||g' {} \;
find . -name "*.php" -type f -exec sed -i 's|<link rel="preconnect" href="https://fonts\.gstatic\.com[^>]*>||g' {} \;

echo "Fixed! External dependencies removed."
echo "Pages will now use system fonts (faster and no blocking)"
