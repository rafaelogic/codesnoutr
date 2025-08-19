# AI Assistant Troubleshooting Guide

## Issue: "AI assistant is not available" despite successful connection test

This issue occurs when the Smart Assistant component doesn't refresh its availability status after AI settings are configured.

### Solutions:

1. **Use the "Force Refresh" button**: This will completely reinitialize the AI service
2. **Use the "Test Connection" button**: This will test the connection and update the availability status
3. **Save AI settings again**: This will trigger an automatic refresh

### What was fixed:

1. **Enhanced refresh logic**: The SmartAssistant component now properly updates its `$aiAvailable` status after successful connection tests
2. **Event-driven updates**: Settings changes now trigger specific events that the Smart Assistant listens to
3. **Force refresh functionality**: Added a new "Force Refresh" button that completely reinitializes the service
4. **Better debug information**: The debug panel now shows clearer status indicators

### Debug Information:

The Smart Assistant panel will show debug information when `APP_DEBUG=true`, including:
- Whether AI is enabled in settings
- Whether API key exists
- API key length (for verification)
- Current model
- Service availability status

### Usage:

1. Configure AI settings in the Settings page
2. Save the settings
3. Navigate to the dashboard
4. The Smart Assistant should automatically refresh
5. If not, use the "Force Refresh" or "Test Connection" buttons

### Events Added:

- `ai-settings-updated`: Dispatched when AI settings are saved
- `setting-saved`: Dispatched when individual settings are saved
- `settings-saved`: Dispatched when all settings are saved

The Smart Assistant component listens to these events and refreshes accordingly.
