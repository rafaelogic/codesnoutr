# CodeSnoutr Icon Reference

## Available Icons

The CodeSnoutr package includes a comprehensive set of SVG icons in both outline and solid variants.

## Usage

```blade
{{-- Basic usage --}}
<x-atoms.icon name="search" />

{{-- With size and color --}}
<x-atoms.icon name="clock" size="lg" color="primary" />

{{-- Solid variant --}}
<x-atoms.icon name="trash" solid="true" />

{{-- With custom classes --}}
<x-atoms.icon name="refresh" size="sm" class="mr-2 hover:rotate-180 transition-transform" />
```

## Props

- `name` (string): Icon name (required)
- `size` (string): xs, sm, md, lg, xl, 2xl (default: md)
- `color` (string): current, primary, secondary, success, danger, warning, muted (default: current)
- `solid` (boolean): Use solid variant instead of outline (default: false)

## Available Icons

### Navigation & Actions
- `arrow-down`, `arrow-up`, `arrow-right` (new)
- `chevron-down`, `chevron-up`, `chevron-left`, `chevron-right`
- `external-link`
- `menu`

### Interface Elements
- `search`
- `filter`
- `refresh`
- `cog`
- `view-grid`
- `dots-vertical`
- `plus-circle`

### Status & Feedback
- `check`, `check-circle`
- `exclamation-circle`, `exclamation-triangle`
- `information-circle`
- `flag`

### File & Data Operations
- `eye`
- `download`
- `cloud-upload`
- `paperclip`
- `document-text`
- `archive`
- `folder`
- `trash` (new)

### Time & Analytics
- `clock` (new)
- `chart-bar`
- `lightning-bolt`

### User & Social
- `users`
- `tag`

### Form Controls
- `x`
- `clipboard`

## Icon Sizes

| Size | Classes | Pixel Size |
|------|---------|------------|
| xs   | w-3 h-3 | 12px |
| sm   | w-4 h-4 | 16px |
| md   | w-5 h-5 | 20px |
| lg   | w-6 h-6 | 24px |
| xl   | w-8 h-8 | 32px |
| 2xl  | w-10 h-10 | 40px |

## Color Variants

| Color | CSS Class |
|-------|-----------|
| current | text-current |
| primary | text-blue-600 |
| secondary | text-gray-600 |
| success | text-green-600 |
| danger | text-red-600 |
| warning | text-yellow-600 |
| muted | text-gray-400 |

## Adding New Icons

To add new icons, you have two options:

### Option 1: Direct Integration (Recommended)
Add the icon path directly to the `$iconPaths` array in `resources/views/components/atoms/icon.blade.php`:

```php
'your-icon-name' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="..." />'
```

### Option 2: Separate Files (Legacy)
1. Create SVG path content in `resources/views/components/atoms/icons/outline/{name}.blade.php`
2. Optionally create solid variant in `resources/views/components/atoms/icons/solid/{name}.blade.php`
3. Use Heroicons or similar for consistency

**Note**: The icon component now includes all icon paths directly to ensure compatibility when published to Laravel applications.

## Examples in Dashboard

The dashboard demonstrates various icon usage patterns:

```blade
{{-- Status with animation --}}
<x-atoms.icon name="refresh" size="sm" class="mr-2 hover:rotate-180 transition-transform" />

{{-- Conditional styling --}}
<x-atoms.icon 
    name="{{ $this->getStatusIcon($scan['status']) }}" 
    :color="$scan['status'] === 'completed' ? 'success' : 'danger'"
    :class="$scan['status'] === 'running' ? 'animate-spin' : ''"
/>

{{-- Button integration --}}
<x-atoms.button variant="primary">
    <x-atoms.icon name="search" size="sm" class="mr-2" />
    New Scan
</x-atoms.button>
```
