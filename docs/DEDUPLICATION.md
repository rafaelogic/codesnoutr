# Issue Deduplication in CodeSnoutr

CodeSnoutr now includes intelligent issue deduplication to prevent the same issues from appearing multiple times when scans are run repeatedly.

## How It Works

### Deduplication Logic
When a new scan is processed, the system checks for existing issues using these criteria:
- **Same file path** (`file_path`)
- **Same line number** (`line_number`) 
- **Same rule ID** (`rule_id`)
- **Same description** (`description`)
- **Issue is not yet fixed** (`fixed = false`)

### Behavior
- **If an existing issue is found**: Updates `last_seen_scan_id` and `updated_at` timestamp
- **If no existing issue is found**: Creates a new issue record

### Database Schema
The `codesnoutr_issues` table now includes:
- `last_seen_scan_id` - Tracks the most recent scan that detected this issue
- Helps identify which issues are still active vs. resolved

## Usage

### Automatic Deduplication
Deduplication happens automatically during every scan. No additional configuration needed.

### Manual Cleanup
To clean up existing duplicate issues (from before deduplication was implemented):

```bash
php artisan codesnoutr:deduplicate
```

This command will:
- Find issues with identical file, line, rule, and description
- Keep the most recent issue
- Remove older duplicates
- Preserve any AI fixes, manual resolutions, or other issue data

### Viewing Issue History
You can track issue persistence across scans:
- `scan_id` - The scan that first detected the issue
- `last_seen_scan_id` - The most recent scan that found the issue
- `updated_at` - When the issue was last seen

## Benefits

1. **Cleaner Results**: No more duplicate issues cluttering scan results
2. **Better Tracking**: See which issues persist across multiple scans
3. **Preserved Work**: AI fixes and manual resolutions are maintained
4. **Performance**: Faster scans as duplicate detection is efficient

## Migration

The deduplication feature requires a database migration:

```bash
php artisan migrate --path=vendor/rafaelogic/codesnoutr/database/migrations
```

Or if you've published the migrations:

```bash
php artisan migrate
```

## Backwards Compatibility

- Existing issues are preserved
- Old scans continue to work normally
- The feature is enabled automatically after migration
- No configuration changes required