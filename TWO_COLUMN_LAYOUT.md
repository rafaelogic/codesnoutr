# Two-Column Results Layout - GitHub-Style Interface

## Overview

The two-column layout provides a GitHub-style interface for viewing scan results, with files organized in a directory tree on the left and issue details displayed on the right when a file is selected.

## Features

### Left Column: File Tree
- **Directory Structure**: Files are organized by directory hierarchy
- **Issue Indicators**: Each file shows issue count and highest severity
- **Collapsible Directories**: Click to expand/collapse directory contents
- **File Icons**: Visual file type indicators based on extensions
- **Search Integration**: Filters apply to both file list and issues

### Right Column: Issue Details
- **File Overview**: Selected file name, path, and statistics
- **Grouped Issues**: Issues grouped by type with occurrence counts
- **Code Snippets**: Syntax-highlighted code with line numbers
- **AI Auto-Fix**: Integration with AI-powered fix suggestions
- **Action Buttons**: Resolve, ignore, or mark as false positive

## Usage

1. **Select View Mode**: Click "Two Column" button in view options
2. **Browse Files**: Expand directories to see files with issues
3. **View Issues**: Click on a file to see its issues in the right panel
4. **Take Actions**: Use action buttons to resolve issues or apply AI fixes

## Benefits

- **Familiar Interface**: Similar to GitHub's file browser
- **Efficient Navigation**: Quick file switching without page reloads
- **Focused View**: See all issues for a specific file at once
- **Responsive Design**: Works well on desktop and large tablets
- **Performance**: Only loads issues for selected file, reducing memory usage

## Integration

- Fully integrated with existing filters and search
- Works with AI auto-fix feature
- Maintains issue resolution state
- Supports all issue actions (resolve, ignore, false positive)

## Technical Implementation

- Livewire component with reactive properties
- Dynamic directory tree generation
- Lazy loading of issue details
- Memory-efficient file browsing
- Real-time updates when issues are resolved
