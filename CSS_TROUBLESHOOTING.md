# Code Snoutr CSS Not Applied - Troubleshooting Guide

If you've installed Code Snoutr but the UI appears broken with no CSS styling applied, follow this guide to resolve the issue.

## Quick Fix

Run these commands in your Laravel project root:

```bash
# Re-publish assets with force flag
php artisan vendor:publish --provider="Rafaelogic\CodeSnoutr\CodeSnoutrServiceProvider" --tag="codesnoutr-assets" --force

# Or run the install command again
php artisan codesnoutr:install --force

# Verify assets are published
php artisan codesnoutr:asset-status
```

## Diagnosis

### 1. Check if Assets are Published

Verify that the assets exist in your public directory:

```bash
ls -la public/vendor/codesnoutr/build/
```

You should see:
- `manifest.json`
- `css/` directory with compiled CSS files
- `js/` directory with compiled JS files

### 2. Check Manifest File

View the manifest to ensure CSS files are listed:

```bash
cat public/vendor/codesnoutr/build/manifest.json
```

Expected output:
```json
{
  "resources/css/app.css": {
    "file": "css/app-[hash].css",
    "isEntry": true,
    "src": "resources/css/app.css"
  },
  "resources/css/codesnoutr.css": {
    "file": "css/codesnoutr-[hash].css",
    "isEntry": true,
    "src": "resources/css/codesnoutr.css"
  }
}
```

### 3. Verify Asset URLs

When viewing a Code Snoutr page, check the browser's Network tab:
- CSS files should load from: `http://yourapp.test/vendor/codesnoutr/build/css/codesnoutr-[hash].css`
- Check for 404 errors on these files

## Common Issues & Solutions

### Issue 1: Assets Not Published

**Problem:** `public/vendor/codesnoutr/build/` directory doesn't exist

**Solution:**
```bash
# Publish assets
php artisan vendor:publish --provider="Rafaelogic\CodeSnoutr\CodeSnoutrServiceProvider" --tag="codesnoutr-assets" --force

# If that doesn't work, manually copy
mkdir -p public/vendor/codesnoutr
cp -r vendor/rafaelogic/codesnoutr/public/build public/vendor/codesnoutr/
```

### Issue 2: 404 Errors on Asset Files

**Problem:** Browser shows 404 errors for CSS/JS files

**Solutions:**

1. **Clear application cache:**
```bash
php artisan cache:clear
php artisan view:clear
php artisan config:clear
```

2. **Check web server configuration:**
   - Ensure your web server serves files from the `public/vendor` directory

3. **Fix permissions:**
```bash
chmod -R 755 public/vendor/codesnoutr
```

### Issue 3: Old Cached CSS

**Problem:** Changes not reflecting, seeing old styles

**Solution:**
```bash
# Clear all caches
php artisan cache:clear
php artisan view:clear

# Clear browser cache (Ctrl+Shift+R / Cmd+Shift+R)
```

## Prevention

To avoid this issue in the future:

1. **Always run after updating:**
```bash
composer update rafaelogic/codesnoutr
php artisan codesnoutr:install --force
```

2. **Add to deployment script:**
```bash
php artisan vendor:publish --provider="Rafaelogic\CodeSnoutr\CodeSnoutrServiceProvider" --tag="codesnoutr-assets" --force
```

## Getting Help

If you're still experiencing issues:

1. **Check asset status:**
```bash
php artisan codesnoutr:asset-status
```

2. **Create an issue:**
   - Repository: https://github.com/rafaelogic/codesnoutr/issues
   - Include Laravel version, PHP version, and error messages

---

**Last Updated:** October 9, 2025
**Package Version:** 1.0.1
