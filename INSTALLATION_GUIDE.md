# CodeSnoutr Installation & Setup Guide

## Requirements

- **PHP**: ^8.1
- **Laravel**: ^10.0 or ^11.0
- **Livewire**: ^3.0 (automatically installed)
- **Node.js**: For asset compilation (if customizing styles)

## Installation

### 1. Install via Composer

```bash
composer require rafaelogic/codesnoutr
```

### 2. Publish Package Assets

```bash
# Publish everything (recommended for first install)
php artisan vendor:publish --provider="Rafaelogic\CodeSnoutr\CodeSnoutrServiceProvider"

# Or publish specific components:

# Configuration
php artisan vendor:publish --provider="Rafaelogic\CodeSnoutr\CodeSnoutrServiceProvider" --tag="codesnoutr-config"

# Migrations
php artisan vendor:publish --provider="Rafaelogic\CodeSnoutr\CodeSnoutrServiceProvider" --tag="codesnoutr-migrations"

# Views, CSS, JS
php artisan vendor:publish --provider="Rafaelogic\CodeSnoutr\CodeSnoutrServiceProvider" --tag="codesnoutr-assets"

# Routes (optional)
php artisan vendor:publish --provider="Rafaelogic\CodeSnoutr\CodeSnoutrServiceProvider" --tag="codesnoutr-routes"

# Documentation
php artisan vendor:publish --provider="Rafaelogic\CodeSnoutr\CodeSnoutrServiceProvider" --tag="codesnoutr-docs"
```

### 3. Run Migrations

```bash
php artisan migrate
```

### 4. Configure Environment (Optional)

Add to your `.env` file:

```env
# CodeSnoutr Configuration
CODESNOUTR_ENABLED=true
CODESNOUTR_AUTO_LOAD_ROUTES=true

# AI Features (Optional)
CODESNOUTR_AI_ENABLED=false
OPENAI_API_KEY=your_openai_api_key_here
CODESNOUTR_AI_MODEL=gpt-4

# Queue Configuration
CODESNOUTR_QUEUE_ENABLED=true
CODESNOUTR_QUEUE_CONNECTION=database

# Debugbar Integration
CODESNOUTR_DEBUGBAR=true
```

## Component Registration

The package automatically registers all Blade components when the service provider boots. You can use them in two ways:

### 1. Package Namespace (Recommended in package views)
```blade
<x-codesnoutr::atoms.icon name="search" />
<x-codesnoutr::molecules.card title="Example" />
```

### 2. Global Registration (Recommended after publishing)
```blade
<x-atoms.icon name="search" />
<x-molecules.card title="Example" />
```

## Troubleshooting Component Issues

### Issue: "Unable to locate a class or view for component [organisms.header]"

**Solution 1: Clear View Cache**
```bash
php artisan view:clear
php artisan config:clear
php artisan cache:clear
```

**Solution 2: Re-publish Assets**
```bash
php artisan vendor:publish --provider="Rafaelogic\CodeSnoutr\CodeSnoutrServiceProvider" --tag="codesnoutr-assets" --force
```

**Solution 3: Verify Component Registration**
All components should be automatically registered. The templates include fallback implementations for complex components.

### Issue: "Livewire only supports one HTML element per component. Multiple root elements detected"

**Cause**: Livewire components must have a single root element wrapping all content.

**Solution**: This has been fixed in the package. All Livewire components now have proper single root elements.

If you encounter this issue after customizing components:
1. Ensure your custom Livewire views start with a single wrapping `<div>`
2. All content must be inside this single root element
3. Check that there are no comments or whitespace outside the root element

**Example Structure**:
```blade
<div>
    <!-- All your component content goes here -->
    <x-templates.your-layout>
        <!-- Content -->
    </x-templates.your-layout>
</div>
```

### Issue: Icons Not Displaying

The icon component includes all SVG paths directly, so this should not occur. If icons are missing:

1. Check that the icon name is correct
2. Verify available icons in the documentation
3. Clear view cache as above

## Template System Features

### Robust Design
The template system is designed to be resilient:

- **Self-Contained**: Templates include fallback implementations for complex components
- **Progressive Enhancement**: Basic functionality works even if some components aren't available
- **Graceful Degradation**: Falls back to simple HTML when advanced components aren't found

## Available Components
```
### Template Hierarchy
```
app-layout (base template)
├── dashboard-layout (extends app-layout)
├── settings-layout (extends app-layout)
└── simple-layout (standalone)
```

## Available Components
```

### Atoms (Basic Building Blocks)
- `atoms.icon` - SVG icons with multiple variants
- `atoms.button` - Styled buttons with variants
- `atoms.input` - Form inputs with validation
- `atoms.badge` - Status and category badges
- `atoms.spinner` - Loading indicators
- `atoms.progress-bar` - Progress indicators
- `atoms.alert` - Notification messages
- `atoms.tooltip` - Hover tooltips

### Molecules (Composed Components)
- `molecules.search-box` - Search input with icon
- `molecules.stat-card` - Statistics display cards
- `molecules.card` - Content containers
- `molecules.navigation` - Navigation menus
- `molecules.empty-state` - Empty state messages
- `molecules.dropdown-item` - Dropdown menu items
- `molecules.file-upload` - File upload interface

### Organisms (Complex Components)
- `organisms.header` - Page header with navigation
- `organisms.sidebar` - Navigation sidebar
- `organisms.scan-form` - Code scanning forms
- `organisms.scan-results` - Results display table
- `organisms.data-table` - Data tables with actions

### Templates (Page Layouts)
- `templates.app-layout` - Base application layout with navigation
- `templates.dashboard-layout` - Dashboard page layout
- `templates.simple-layout` - Simple page layout
- `templates.settings-layout` - Settings page layout

## Routes

### Automatic Route Loading
By default, routes are automatically loaded. Set `CODESNOUTR_AUTO_LOAD_ROUTES=false` to disable.

### Manual Route Integration
If you've published routes, include them in your `routes/web.php`:

```php
// Basic integration
require base_path('routes/codesnoutr.php');

// With middleware and prefix
Route::group([
    'middleware' => ['web', 'auth'],
    'prefix' => 'admin',
], function() {
    require base_path('routes/codesnoutr.php');
});
```

### Available Routes
- `/codesnoutr` - Dashboard
- `/codesnoutr/wizard` - Scan wizard
- `/codesnoutr/scan` - Manual scan
- `/codesnoutr/results` - Scan results
- `/codesnoutr/settings` - Configuration

## Queue Configuration

For large codebases, enable queue processing:

```bash
# Set up database queue driver
php artisan queue:table
php artisan migrate

# Start queue worker
php artisan queue:work
```

## Security Considerations

1. **CSRF Protection**: All forms include CSRF tokens
2. **Authentication**: Add auth middleware to routes if needed
3. **File Access**: Ensure proper file permissions
4. **API Keys**: Keep AI API keys secure in environment files

## Performance Optimization

1. **Queue Large Scans**: Enable queue processing for large codebases
2. **Cache Results**: Results are automatically cached
3. **Chunk Processing**: Configure chunk sizes in config file
4. **Memory Limits**: Adjust memory limits for large files

## Support

- Documentation: `docs/codesnoutr-*.md` (after publishing docs)
- Issues: Report on GitHub repository
- Configuration: Check `config/codesnoutr.php` for all options
