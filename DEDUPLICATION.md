# Issue Deduplication

## Overview
The CodeSnoutr package now includes issue deduplication to prevent the same issues from being displayed multiple times when running subsequent scans.

## How It Works

### Automatic Deduplication
When a new scan is performed, the system:
1. Checks if an issue already exists based on:
   - File path
   - Line number
   - Rule ID
   - Description
   - Not already fixed
2. If a duplicate is found:
   - Updates the existing issue's `last_seen_scan_id` to the current scan
   - Does not create a new issue record
3. If no duplicate is found:
   - Creates a new issue record with `last_seen_scan_id` set to the current scan

### Manual Cleanup
If you have existing duplicate issues from previous scans, you can clean them up using the provided command:

```bash
# See what duplicates would be removed (dry run)
php artisan codesnoutr:deduplicate-issues --dry-run

# Actually remove duplicate issues
php artisan codesnoutr:deduplicate-issues
```

## Database Changes
- Added `last_seen_scan_id` column to `codesnoutr_issues` table
- Added foreign key constraint to `codesnoutr_scans` table
- Added composite index for better query performance

## Benefits
- Clean, non-cluttered issue lists
- Accurate issue counts
- Better performance with fewer duplicate records
- Historical tracking of when issues were last detected