# Publishing CodeSnoutr Assets and Views

This guide explains how to publish and customize CodeSnoutr assets and views in your Laravel application.

## Available Publishing Tags

CodeSnoutr provides several publishing tags to give you control over what gets published:

### 1. Configuration (`codesnoutr-config`)

Publishes the configuration file to `config/codesnoutr.php`:

```bash
php artisan vendor:publish --provider="Rafaelogic\CodeSnoutr\CodeSnoutrServiceProvider" --tag="codesnoutr-config"
```

### 2. Database Migrations (`codesnoutr-migrations`)

Publishes migration files to `database/migrations/`:

```bash
php artisan vendor:publish --provider="Rafaelogic\CodeSnoutr\CodeSnoutrServiceProvider" --tag="codesnoutr-migrations"
```

**Published migrations:**
- `2024_01_01_000001_create_codesnoutr_scans_table.php`
- `2024_01_01_000002_create_codesnoutr_issues_table.php`
- `2024_01_01_000003_create_codesnoutr_settings_table.php`

### 3. Assets (`codesnoutr-assets`)

Publishes views, CSS, and JavaScript to your resources directory:

```bash
php artisan vendor:publish --provider="Rafaelogic\CodeSnoutr\CodeSnoutrServiceProvider" --tag="codesnoutr-assets"
```

**Published assets:**
- Views: `resources/views/vendor/codesnoutr/`
- CSS: `resources/css/vendor/codesnoutr/codesnoutr.css`
- JavaScript: `resources/js/vendor/codesnoutr/codesnoutr.js`

### 4. Routes (`codesnoutr-routes`)

Publishes the route file for custom integration:

```bash
php artisan vendor:publish --provider="Rafaelogic\CodeSnoutr\CodeSnoutrServiceProvider" --tag="codesnoutr-routes"
```

**Published to:** `routes/codesnoutr.php`

### 5. Documentation (`codesnoutr-docs`)

Publishes integration and troubleshooting documentation:

```bash
php artisan vendor:publish --provider="Rafaelogic\CodeSnoutr\CodeSnoutrServiceProvider" --tag="codesnoutr-docs"
```

**Published documentation:**
- `docs/codesnoutr-integration.md`
- `docs/codesnoutr-troubleshooting.md`
- `docs/codesnoutr-csrf-troubleshooting.md`

### 6. Publish Everything

To publish all assets at once:

```bash
php artisan vendor:publish --provider="Rafaelogic\CodeSnoutr\CodeSnoutrServiceProvider"
```

## Using Published Assets

### CSS Integration

After publishing assets, include the CSS in your application:

```blade
{{-- In your main layout --}}
<link href="{{ asset('css/vendor/codesnoutr/codesnoutr.css') }}" rel="stylesheet">
```

Or if using Vite/Laravel Mix, import it in your CSS:

```css
/* In resources/css/app.css */
@import '../css/vendor/codesnoutr/codesnoutr.css';
```

### JavaScript Integration

Include the JavaScript in your application:

```blade
{{-- In your main layout --}}
<script src="{{ asset('js/vendor/codesnoutr/codesnoutr.js') }}"></script>
```

Or if using Vite/Laravel Mix:

```javascript
// In resources/js/app.js
import './vendor/codesnoutr/codesnoutr.js';
```

### View Customization

Once views are published, you can customize them:

1. **Atomic Design Components**: Modify components in `resources/views/vendor/codesnoutr/components/`
2. **Layouts**: Customize layouts in `resources/views/vendor/codesnoutr/layouts/`
3. **Livewire Components**: Modify Livewire views in `resources/views/vendor/codesnoutr/livewire/`
4. **Pages**: Customize page views in `resources/views/vendor/codesnoutr/pages/`

## Atomic Design System

The published views include a complete atomic design system:

### Atoms
```
resources/views/vendor/codesnoutr/components/atoms/
├── badge.blade.php          # Status badges and labels
├── button.blade.php         # Interactive buttons
├── icon.blade.php           # SVG icon system
├── input.blade.php          # Form inputs
├── label.blade.php          # Form labels
├── progress-bar.blade.php   # Progress indicators
├── select.blade.php         # Dropdown selects
├── spinner.blade.php        # Loading spinners
└── toggle.blade.php         # Toggle switches
```

### Molecules
```
resources/views/vendor/codesnoutr/components/molecules/
├── alert.blade.php          # Notification messages
├── card.blade.php           # Content containers
├── dropdown.blade.php       # Dropdown menus
├── empty-state.blade.php    # Empty state displays
├── form-field.blade.php     # Complete form fields
├── modal.blade.php          # Modal dialogs
├── pagination.blade.php     # Page navigation
├── search-box.blade.php     # Search components
├── settings-form.blade.php  # Settings forms
├── stat-card.blade.php      # Statistics cards
└── tabs.blade.php           # Tab navigation
```

### Organisms
```
resources/views/vendor/codesnoutr/components/organisms/
├── data-table.blade.php     # Data tables
├── navigation.blade.php     # Main navigation
├── scan-results.blade.php   # Scan result displays
└── sidebar.blade.php        # Sidebar components
```

### Templates
```
resources/views/vendor/codesnoutr/components/templates/
├── app-layout.blade.php     # Main application layout
├── dashboard-layout.blade.php # Dashboard-specific layout
└── settings-layout.blade.php  # Settings page layout
```

### Icons
```
resources/views/vendor/codesnoutr/components/icons/
└── outline/                 # Outline SVG icons
    ├── search.blade.php
    ├── cog.blade.php
    ├── chart-bar.blade.php
    └── ... (30+ icons)
```

## CSS Class System

The published CSS includes utility classes for consistent styling:

### Button Classes
- `.btn` - Base button class
- `.btn--primary`, `.btn--secondary`, `.btn--danger` - Color variants
- `.btn--xs`, `.btn--sm`, `.btn--md`, `.btn--lg` - Size variants

### Input Classes
- `.input` - Base input class
- `.input--default`, `.input--error`, `.input--success` - State variants
- `.input--sm`, `.input--md`, `.input--lg` - Size variants

### Badge Classes
- `.badge` - Base badge class
- `.badge--primary`, `.badge--success`, `.badge--danger` - Color variants
- `.badge--sm`, `.badge--md`, `.badge--lg` - Size variants

### Alert Classes
- `.alert` - Base alert class
- `.alert--info`, `.alert--success`, `.alert--warning`, `.alert--danger` - Type variants

## JavaScript Features

The published JavaScript provides:

### Dark Mode
```javascript
// Toggle dark mode
window.CodeSnoutr.toggleDarkMode();

// Listen for theme changes
window.addEventListener('theme-changed', (event) => {
    console.log('Theme changed to:', event.detail.theme);
});
```

### Utility Functions
```javascript
// Copy to clipboard
window.CodeSnoutr.copyToClipboard(text, button);

// Format file sizes
window.CodeSnoutr.formatFileSize(bytes);

// Format duration
window.CodeSnoutr.formatDuration(seconds);

// Animate progress bars
window.CodeSnoutr.animateProgressBar(element, targetValue, duration);
```

### Search and Highlighting
```javascript
// Highlight search terms
window.CodeSnoutr.highlightSearchTerms(container, searchTerm);

// Scroll to element
window.CodeSnoutr.scrollToElement(elementId);
```

## Configuration After Publishing

### 1. Update `config/codesnoutr.php`

Configure scan settings, AI features, and other options:

```php
return [
    'auto_load_routes' => true,
    'scan_paths' => [
        'app',
        'resources/views',
        'routes',
    ],
    'ai' => [
        'enabled' => true,
        'provider' => 'openai',
        'confidence_threshold' => 85,
    ],
    // ... other settings
];
```

### 2. Run Migrations

After publishing migrations:

```bash
php artisan migrate
```

### 3. Compile Assets

If using Vite or Laravel Mix, compile the assets:

```bash
# Vite
npm run build

# Laravel Mix
npm run production
```

## Customization Examples

### Custom Button Component

```blade
{{-- resources/views/vendor/codesnoutr/components/atoms/button.blade.php --}}
@props([
    'variant' => 'primary',
    'size' => 'md',
    'loading' => false,
    'disabled' => false,
    'tag' => 'button'
])

{{-- Your custom button implementation --}}
<{{ $tag }} 
    {{ $attributes->merge(['class' => "your-custom-btn-classes"]) }}
    @if($disabled || $loading) disabled @endif
>
    @if($loading)
        <span class="your-spinner-classes"></span>
    @endif
    {{ $slot }}
</{{ $tag }}>
```

### Custom CSS Overrides

```css
/* resources/css/vendor/codesnoutr/codesnoutr.css */

/* Override button styles */
.btn--primary {
    @apply bg-purple-600 hover:bg-purple-700;
}

/* Add custom severity colors */
.severity-custom {
    @apply bg-purple-100 text-purple-800;
}

.dark .severity-custom {
    @apply bg-purple-900 text-purple-200;
}
```

## Best Practices

1. **Always backup** your customizations before updating the package
2. **Use version control** to track your customizations
3. **Test thoroughly** after updating published assets
4. **Follow the atomic design pattern** when creating custom components
5. **Maintain dark mode compatibility** in custom styles
6. **Use the provided utility functions** for consistency

## Troubleshooting

### Assets Not Loading
1. Ensure assets are published: `php artisan vendor:publish --tag=codesnoutr-assets`
2. Clear cache: `php artisan view:clear && php artisan config:clear`
3. Recompile assets: `npm run build`

### Components Not Found
1. Check if views are published to `resources/views/vendor/codesnoutr/`
2. Verify component namespace in your Blade templates
3. Clear view cache: `php artisan view:clear`

### Styling Issues
1. Ensure CSS is included in your layout
2. Check Tailwind CSS is properly configured
3. Verify dark mode classes are working

For more detailed troubleshooting, see the published documentation files.
