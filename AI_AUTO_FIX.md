# AI Auto-Fix Feature Documentation

## Overview

The AI Auto-Fix feature provides automated code fixes using AI-powered analysis. This feature is designed to be safe, user-controlled, and cost-effective, focusing only on fixing issues rather than scanning (which was slow and expensive).

## Key Features

### 1. AI-Powered Code Analysis
- Analyzes issues and generates fix recommendations
- Provides confidence scores for each suggested fix
- Supports multiple fix strategies with explanations

### 2. Safe Fix Application
- **Backup System**: Automatically creates backups before applying fixes
- **Preview Mode**: Shows proposed changes before application
- **Rollback Capability**: Can restore original files from backup
- **Confidence Thresholds**: Only applies fixes above configured confidence levels

### 3. User Interaction Controls
- **Manual Trigger**: Users click "Analyze with AI" button to start analysis
- **Confirmation Required**: Optional confirmation before applying fixes
- **Copy Code**: Users can copy suggested fixes to apply manually
- **Safe Mode**: Extra safety checks and confirmations

## Implementation

### Core Services

#### AutoFixService (`src/Services/AutoFixService.php`)
- **generateFix()**: Analyzes issues and generates fix suggestions
- **previewFix()**: Shows what changes will be made
- **applyFix()**: Applies the fix with backup creation
- **restoreBackup()**: Restores original file from backup
- **hasBackup()**: Checks if backup exists for restoration

#### AiAssistantService (Enhanced)
- Existing service enhanced to support auto-fix operations
- Provides AI communication layer for fix generation

### UI Components

#### AiAutoFix Livewire Component (`src/Livewire/AiAutoFix.php`)
- **analyzeIssue()**: Triggers AI analysis for an issue
- **previewFix()**: Shows fix preview with confidence score
- **applyFix()**: Applies the suggested fix
- **restoreBackup()**: Restores from backup
- **copyCode()**: Copies fix code to clipboard

#### Blade View (`resources/views/livewire/ai-auto-fix.blade.php`)
- Interactive UI with buttons for each action
- Shows analysis results, confidence scores, and previews
- Responsive design with proper styling

### Configuration

#### Settings Available in UI (`src/Livewire/Settings.php`)
- **AI Auto-Fix Enabled**: Master toggle for the feature
- **Backup Disk**: Storage location for backup files
- **Minimum Confidence**: Threshold for auto-applying fixes (0-100%)
- **Safe Mode**: Enable extra safety checks
- **Require Confirmation**: Always ask before applying fixes

#### Config File (`config/codesnoutr.php`)
```php
'ai' => [
    'auto_fix' => [
        'enabled' => env('CODESNOUTR_AI_AUTO_FIX_ENABLED', false),
        'backup_disk' => env('CODESNOUTR_AUTO_FIX_BACKUP_DISK', 'local'),
        'min_confidence' => env('CODESNOUTR_AUTO_FIX_MIN_CONFIDENCE', 80),
        'safe_mode' => env('CODESNOUTR_AUTO_FIX_SAFE_MODE', true),
        'require_confirmation' => env('CODESNOUTR_AUTO_FIX_REQUIRE_CONFIRMATION', true),
        'create_backup' => true,
        'max_file_size' => 50 * 1024, // 50KB
    ],
],
```

### Integration

#### Scan Results Integration
- Auto-fix component integrated into scan results view
- Appears for each unfixed issue instance
- Shows "Analyze with AI" button to trigger analysis

#### Service Provider Registration
- AutoFixService registered as singleton
- AiAutoFix Livewire component registered
- All dependencies properly resolved

## Usage Workflow

1. **Issue Detection**: User runs scan and finds issues
2. **AI Analysis**: User clicks "Analyze with AI" for specific issue
3. **Review Suggestions**: AI provides fix suggestions with confidence scores
4. **Preview Changes**: User can preview what changes will be made
5. **Apply Fix**: User applies fix (with automatic backup)
6. **Verification**: User verifies fix works as expected
7. **Rollback** (if needed): User can restore from backup if issues arise

## Safety Features

- **Automatic Backups**: Every fix creates a backup of the original file
- **Confidence Thresholds**: Only applies fixes meeting minimum confidence
- **Safe Mode**: Extra verification and confirmation steps
- **Preview Mode**: Shows exact changes before application
- **Rollback Support**: Can restore original files at any time
- **File Size Limits**: Only processes files under size limit
- **User Confirmation**: Optional confirmation for every fix

## Cost Optimization

- **On-Demand Only**: AI only used when user explicitly requests it
- **No Scanning**: AI not used for code scanning (avoiding previous cost issues)
- **Targeted Analysis**: Focuses only on specific issues user wants fixed
- **Efficient Prompting**: Optimized prompts to minimize token usage

## Environment Variables

```bash
# AI Auto-Fix Configuration
CODESNOUTR_AI_AUTO_FIX_ENABLED=false
CODESNOUTR_AUTO_FIX_BACKUP_DISK=local
CODESNOUTR_AUTO_FIX_MIN_CONFIDENCE=80
CODESNOUTR_AUTO_FIX_SAFE_MODE=true
CODESNOUTR_AUTO_FIX_REQUIRE_CONFIRMATION=true

# OpenAI Configuration (required)
OPENAI_API_KEY=your_api_key_here
CODESNOUTR_AI_MODEL=gpt-4
```

## Next Steps

1. **Enable Feature**: Set `CODESNOUTR_AI_AUTO_FIX_ENABLED=true`
2. **Configure OpenAI**: Add your OpenAI API key
3. **Adjust Settings**: Configure confidence thresholds and safety settings
4. **Test Workflow**: Try the analyze → preview → apply → verify workflow
5. **Monitor Usage**: Keep track of AI costs and usage patterns
