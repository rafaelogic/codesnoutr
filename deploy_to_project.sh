#!/bin/bash

# Deploy CodeSnoutr Package Changes to Main Project
# This copies changes from the package development folder to the vendor directory

PACKAGE_DIR="/Users/rafaelogic/Desktop/projects/laravel/packages/codesnoutr"
PROJECT_DIR="/Users/rafaelogic/Desktop/projects/pwm/aristo-pwm"
VENDOR_DIR="$PROJECT_DIR/vendor/rafaelogic/codesnoutr"

echo "🚀 Deploying CodeSnoutr package changes..."
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# Check if directories exist
if [ ! -d "$PACKAGE_DIR" ]; then
    echo "❌ Package directory not found: $PACKAGE_DIR"
    exit 1
fi

if [ ! -d "$VENDOR_DIR" ]; then
    echo "❌ Vendor directory not found: $VENDOR_DIR"
    echo "💡 Run 'composer install' in the project first"
    exit 1
fi

# Copy the AutoFixService.php file
echo "📦 Copying AutoFixService.php..."
cp "$PACKAGE_DIR/src/Services/AI/AutoFixService.php" "$VENDOR_DIR/src/Services/AI/AutoFixService.php"

if [ $? -eq 0 ]; then
    echo "✅ AutoFixService.php copied successfully"
else
    echo "❌ Failed to copy AutoFixService.php"
    exit 1
fi

# Optional: Copy other changed files if needed
# echo "📦 Copying other files..."
# cp "$PACKAGE_DIR/src/..." "$VENDOR_DIR/src/..."

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "✅ Deployment complete!"
echo ""
echo "🔄 Next steps:"
echo "1. cd $PROJECT_DIR"
echo "2. php artisan queue:restart"
echo "3. php artisan queue:work --verbose"
echo "4. Test Fix All Issues in browser"
echo ""
echo "📊 Monitor logs:"
echo "tail -f storage/logs/laravel.log | grep -E 'SKIPPING|inside array|Fixed'"
