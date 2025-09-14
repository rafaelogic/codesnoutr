# Frontend Build Setup

This document explains the Tailwind CSS and JavaScript build setup for CodeSnoutr.

## Overview

CodeSnoutr now uses a proper build process with Vite instead of relying on the Tailwind CSS CDN. This provides:

- Better performance with optimized CSS
- Consistent styling across environments
- Custom component classes
- Dark mode support
- Production-ready assets

## Build Tools

- **Vite**: Fast build tool and development server
- **Tailwind CSS**: Utility-first CSS framework
- **Alpine.js**: Lightweight JavaScript framework
- **PostCSS**: CSS processing with Autoprefixer

## File Structure

```
├── package.json          # Node.js dependencies and scripts
├── vite.config.js         # Vite configuration
├── tailwind.config.js     # Tailwind CSS configuration
├── postcss.config.js      # PostCSS configuration
├── resources/
│   ├── css/
│   │   └── app.css        # Main CSS file with Tailwind directives
│   └── js/
│       └── app.js         # Main JavaScript file with Alpine.js
└── public/
    └── build/             # Built assets (committed to git)
        ├── manifest.json
        ├── css/
        └── js/
```

## Development Workflow

### Initial Setup

```bash
npm install
```

### Build Commands

```bash
# Build for production (run this after changes)
npm run build

# Development server with hot reloading
npm run dev

# Watch mode (rebuilds on file changes)
npm run watch
```

### Making Changes

1. Edit CSS in `resources/css/app.css`
2. Edit JavaScript in `resources/js/app.js`
3. Run `npm run build` to generate production assets
4. Built assets are automatically loaded via `@vite` directive

## Asset Loading

The assets are loaded in the app layout template:

```blade
@vite(['resources/css/app.css', 'resources/js/app.js'])
```

This automatically includes the built CSS and JavaScript files.

## Custom CSS Classes

The build includes custom component classes for consistent styling:

### Buttons
```css
.btn              /* Base button styles */
.btn-primary      /* Primary button */
.btn-secondary    /* Secondary button */
.btn-outline      /* Outlined button */
.btn-danger       /* Danger button */
.btn-success      /* Success button */
```

### Cards
```css
.card             /* Card container */
.card-header      /* Card header */
.card-body        /* Card body */
.card-footer      /* Card footer */
```

### Forms
```css
.form-input       /* Text inputs */
.form-select      /* Select dropdowns */
.form-checkbox    /* Checkboxes */
.form-radio       /* Radio buttons */
```

### Status Badges
```css
.badge            /* Base badge */
.badge-success    /* Success badge */
.badge-warning    /* Warning badge */
.badge-danger     /* Danger badge */
.badge-info       /* Info badge */
.badge-secondary  /* Secondary badge */
```

### Issue Severity
```css
.severity-critical  /* Critical issues */
.severity-high      /* High severity */
.severity-medium    /* Medium severity */
.severity-low       /* Low severity */
.severity-info      /* Info level */
```

## Dark Mode

Dark mode is fully supported and automatically switches based on:

1. User preference (stored in localStorage)
2. System preference (prefers-color-scheme)

Classes are prefixed with `dark:` for dark mode variants.

## JavaScript Features

The JavaScript bundle includes:

- **Alpine.js**: Reactive components
- **Dark mode toggle**: Automatic theme switching
- **Tooltips**: For truncated text
- **Copy to clipboard**: Utility function
- **Smooth scrolling**: For anchor links
- **Toast notifications**: User feedback
- **Livewire integration**: Event handling

## Performance

- CSS is purged of unused classes
- JavaScript is tree-shaken and minified
- Assets include content hashes for caching
- Total CSS size: ~82KB (11.6KB gzipped)
- Total JS size: ~47KB (17KB gzipped)

## Deployment

### For Package Development

The `public/build/` directory should be committed to git and deployed with your package.

### For Laravel Applications Using the Package

After installing the package, you need to publish the built assets:

```bash
# Publish all CodeSnoutr resources including built assets
php artisan vendor:publish --tag=codesnoutr-resources

# Or publish just the assets
php artisan vendor:publish --tag=codesnoutr-assets
```

This will copy the built assets to `public/vendor/codesnoutr/build/` in your Laravel application.

The app layout automatically detects and loads these assets when available.

## Troubleshooting

### Build Errors

If you encounter build errors:

1. Clear node_modules: `rm -rf node_modules package-lock.json`
2. Reinstall: `npm install`
3. Rebuild: `npm run build`

### Missing Assets

If assets aren't loading:

1. Ensure `public/build/manifest.json` exists
2. Check that `@vite` directive is in the layout
3. Verify file paths in `vite.config.js`

### Tailwind Classes Not Working

If Tailwind classes aren't being applied:

1. Check `tailwind.config.js` content paths
2. Ensure the CSS file includes `@tailwind` directives
3. Rebuild assets: `npm run build`