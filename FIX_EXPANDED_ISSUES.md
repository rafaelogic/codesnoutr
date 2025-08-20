# Fix for Undefined Variable $expandedIssues

## Problem
The detailed-table.blade.php component was trying to use an undefined variable `$expandedIssues`, causing a PHP error:

```
Undefined variable $expandedIssues (View: /Users/rafaelogic/Desktop/projects/pwm/christies-malta/resources/views/vendor/codesnoutr/components/scan-results/detailed-table.blade.php)
```

## Root Cause
The detailed table view includes functionality to expand/collapse individual issues to show more details, but the necessary properties and methods were missing from the ScanResults Livewire component:

1. `$expandedIssues` property was not defined
2. `toggleIssueExpansion()` method was not implemented

## Solution Applied

### 1. Added `$expandedIssues` Property
**File**: `/src/Livewire/ScanResults.php`
**Line**: ~32

Added the public property to track which issues are expanded in the detailed view:
```php
public $expandedIssues = []; // Track which individual issues are expanded in detailed view
```

### 2. Added `toggleIssueExpansion()` Method
**File**: `/src/Livewire/ScanResults.php`
**After**: `toggleFileExpansion()` method

Added the method to handle expanding/collapsing individual issues:
```php
/**
 * Toggle individual issue expansion in detailed view
 */
public function toggleIssueExpansion($issueId)
{
    if (in_array($issueId, $this->expandedIssues)) {
        // Collapse issue
        $this->expandedIssues = array_diff($this->expandedIssues, [$issueId]);
    } else {
        // Expand issue
        $this->expandedIssues[] = $issueId;
    }
}
```

### 3. Updated Reset Method
**File**: `/src/Livewire/ScanResults.php`
**Method**: `resetFileGroupData()`

Added `$this->expandedIssues = [];` to reset expanded issues when filters or view modes change:
```php
protected function resetFileGroupData()
{
    $this->currentFileGroupPage = 1;
    $this->allFileGroups = [];
    $this->loadedFileGroups = [];
    $this->expandedFiles = [];
    $this->expandedIssues = []; // Added this line
    $this->loadingFiles = [];
}
```

## How It Works

1. **Expand/Collapse Issues**: Users can click the dropdown arrow in the detailed table view to expand/collapse individual issues
2. **Show Details**: When expanded, issues show:
   - Full description
   - Code context (if available)
   - Fix suggestions (if available)
   - Metadata (rule ID, timestamps, etc.)
3. **State Management**: The `$expandedIssues` array tracks which issues are currently expanded
4. **Automatic Reset**: When users change filters, view modes, or refresh results, expanded issues are reset to prevent stale state

## Files Modified

1. `/src/Livewire/ScanResults.php`
   - Added `$expandedIssues` property
   - Added `toggleIssueExpansion()` method
   - Updated `resetFileGroupData()` method

## Testing
The detailed table view should now work without errors and allow users to expand/collapse individual issues to see more information.
