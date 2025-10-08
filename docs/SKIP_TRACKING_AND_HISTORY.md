# Skip Tracking and Fix Attempt History

## Overview

This document describes the skip tracking and fix attempt history features added to CodeSnoutr. These features provide enhanced observability into the AI auto-fix process, allowing you to understand why issues fail, skip, or succeed.

## Database Schema

### New Columns in `codesnoutr_issues`

- **`skipped`** (boolean): Indicates if the AI skipped this issue
- **`skipped_at`** (timestamp): When the issue was marked as skipped
- **`skip_reason`** (text): Explanation from AI about why it was skipped
- **`fix_attempts`** (json): Array of fix attempt records (last 10)
- **`fix_attempt_count`** (integer): Total number of fix attempts
- **`last_fix_attempt_at`** (timestamp): Timestamp of most recent fix attempt

### New Columns in `codesnoutr_scans`

- **`fixed_issues`** (integer): Counter for successfully fixed issues
- **`skipped_issues`** (integer): Counter for issues AI chose to skip

## Issue Model Methods

### `markAsSkipped(string $reason): void`

Marks an issue as skipped by the AI.

```php
$issue->markAsSkipped('Context is ambiguous - multiple possible interpretations');
```

### `recordFixAttempt(string $status, ?string $error, ?array $data): void`

Records a fix attempt in the issue's history. Keeps last 10 attempts.

**Parameters:**
- `$status`: 'success', 'failed', or 'skipped'
- `$error`: Error message (for failed attempts)
- `$data`: Additional data (backup path, exception details, etc.)

**Examples:**

```php
// Record successful fix
$issue->recordFixAttempt('success', null, [
    'backup_path' => 'backups/file.php.bak',
    'confidence' => 85,
]);

// Record failed attempt
$issue->recordFixAttempt('failed', 'Parse error: unexpected token', [
    'exception' => 'ParseException',
    'line' => 45,
]);

// Record skip
$issue->recordFixAttempt('skipped', null, [
    'reason' => 'Multi-line statement with unclear boundaries',
    'confidence' => 30,
]);
```

### `isSkipped(): bool`

Check if an issue has been skipped.

```php
if ($issue->isSkipped()) {
    echo "AI chose to skip: " . $issue->skip_reason;
}
```

### `getLastFixAttempt(): ?array`

Get the most recent fix attempt details.

```php
$lastAttempt = $issue->getLastFixAttempt();
if ($lastAttempt) {
    echo "Status: " . $lastAttempt['status'];
    echo "Time: " . $lastAttempt['timestamp'];
}
```

## Fix Attempt Data Structure

Each fix attempt is stored as a JSON object with the following structure:

```json
{
  "timestamp": "2024-01-15T14:30:45+00:00",
  "status": "failed",
  "error": "Parse error: syntax error, unexpected token",
  "data": {
    "exception": "ParseException",
    "file": "/path/to/file.php",
    "line": 45
  }
}
```

## AutoFixService Integration

The `AutoFixService` automatically records fix attempts:

### Skip Detection
When AI returns a skip response:
```php
if (isset($fixData['type']) && $fixData['type'] === 'skip') {
    $issue->markAsSkipped($fixData['explanation']);
    $issue->recordFixAttempt('skipped', null, [
        'reason' => $fixData['explanation'],
        'confidence' => $fixData['confidence'],
    ]);
}
```

### Success Tracking
After successful fix:
```php
$issue->recordFixAttempt('success', null, [
    'backup_path' => $backupPath,
    'confidence' => $fixData['confidence'],
]);
```

### Failure Tracking
On exceptions or validation failures:
```php
catch (\Exception $e) {
    $issue->recordFixAttempt('failed', $e->getMessage(), [
        'exception' => get_class($e),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
    ]);
}
```

## FixAllIssuesJob Updates

The background job now tracks all three outcomes:

```php
$fixedCount = 0;
$failedCount = 0;
$skippedCount = 0;

// During processing
if ($applyResult['success']) {
    $fixedCount++;
    $results[] = ['status' => 'success', ...];
} elseif (isset($applyResult['skipped']) && $applyResult['skipped']) {
    $skippedCount++;
    $results[] = ['status' => 'skipped', ...];
} else {
    $failedCount++;
    $results[] = ['status' => 'failed', ...];
}
```

Progress updates include all counts:
```php
$this->updateProgress([
    'fixed_count' => $fixedCount,
    'failed_count' => $failedCount,
    'skipped_count' => $skippedCount,
]);
```

Final message:
```
"Fixed: 180, Skipped: 45, Failed: 76"
```

## Analyzing Results

### Query Skipped Issues
```php
$skippedIssues = Issue::where('skipped', true)->get();

foreach ($skippedIssues as $issue) {
    echo "{$issue->rule_name}: {$issue->skip_reason}\n";
}
```

### Find Issues with Multiple Failed Attempts
```php
$problematicIssues = Issue::where('fix_attempt_count', '>', 3)
    ->whereNull('fixed_at')
    ->get();
```

### Analyze Fix Attempt History
```php
$issue = Issue::find($id);
$attempts = $issue->fix_attempts;

foreach ($attempts as $attempt) {
    echo "[{$attempt['timestamp']}] {$attempt['status']}";
    if ($attempt['error']) {
        echo ": {$attempt['error']}";
    }
    echo "\n";
}
```

## UI Implementation (Pending)

### Tabs for Grouping
Create tabs in the Fix All Progress view:
- **Fixed** (167): Successfully applied fixes
- **Skipped** (45): AI determined unsafe/unclear
- **Failed** (89): Attempted but failed

### History Display
Show fix attempt timeline for each issue:
```
Fix Attempts (3):
✅ 2024-01-15 14:30 - Success (confidence: 85%)
❌ 2024-01-14 10:15 - Failed: Parse error
⊘ 2024-01-13 09:00 - Skipped: Context unclear
```

## Benefits

### For Debugging
- **Pattern Recognition**: See which rule types consistently skip/fail
- **Root Cause Analysis**: Understand why fixes fail repeatedly
- **AI Behavior**: Track if AI is using skip appropriately

### For Optimization
- **Disable Problematic Rules**: If a rule always skips, disable it
- **Improve Prompts**: Use skip reasons to refine AI instructions
- **Prioritize Fixes**: Focus on high-success-rate categories

### For Reporting
- **Success Metrics**: Accurate counts of fixed vs skipped vs failed
- **Trend Analysis**: Track improvement over time
- **Confidence Tracking**: Correlate confidence scores with outcomes

## Migration

Run the migration to add the new columns:

```bash
php artisan migrate --path=vendor/rafaelogic/codesnoutr/database/migrations/2024_01_01_000006_add_skip_and_history_tracking_to_issues.php
```

## Best Practices

### 1. Monitor Skip Reasons
Regularly review skip reasons to identify patterns:
```php
$skipReasons = Issue::whereNotNull('skip_reason')
    ->groupBy('skip_reason')
    ->selectRaw('skip_reason, count(*) as count')
    ->orderBy('count', 'desc')
    ->get();
```

### 2. Clean Up Old Attempts
The system automatically keeps only last 10 attempts per issue, but you can manually clean older data:
```php
// Clear attempts older than 30 days for fixed issues
Issue::whereNotNull('fixed_at')
    ->where('last_fix_attempt_at', '<', now()->subDays(30))
    ->update(['fix_attempts' => null, 'fix_attempt_count' => 0]);
```

### 3. Export for Analysis
Export attempt data for deeper analysis:
```php
$data = Issue::select('rule_name', 'fix_attempts', 'skipped', 'fixed')
    ->whereNotNull('fix_attempts')
    ->get()
    ->map(function($issue) {
        return [
            'rule' => $issue->rule_name,
            'attempts' => count($issue->fix_attempts),
            'outcome' => $issue->fixed ? 'fixed' : ($issue->skipped ? 'skipped' : 'failed'),
            'last_status' => $issue->getLastFixAttempt()['status'] ?? null,
        ];
    });
```

## Expected Outcomes

### Optimal Distribution
- **Fixed**: 60-70% (high success rate)
- **Skipped**: 15-25% (AI recognizing ambiguity)
- **Failed**: 10-15% (legitimate failures)

### Warning Signs
- **0% Skipped**: AI not using skip option (may be forcing bad fixes)
- **50%+ Skipped**: AI being too conservative or prompts unclear
- **High Repeat Failures**: Same issues failing 5+ times → investigate patterns

## Next Steps

1. ✅ **Database Migration**: Completed
2. ✅ **Backend Tracking**: Completed
3. 🔄 **Test Fix All**: Run to validate skip tracking
4. ⏳ **UI Tabs**: Create tabbed interface for grouping
5. ⏳ **History Display**: Show attempt timeline per issue
6. ⏳ **Analytics Dashboard**: Aggregate skip/fix statistics

## Related Files

- **Migration**: `database/migrations/2024_01_01_000006_add_skip_and_history_tracking_to_issues.php`
- **Issue Model**: `src/Models/Issue.php`
- **Scan Model**: `src/Models/Scan.php`
- **AutoFixService**: `src/Services/AI/AutoFixService.php`
- **FixAllIssuesJob**: `src/Jobs/FixAllIssuesJob.php`
