# CodeSnoutr Architecture Overview

## Directory Structure

```
src/
├── Actions/                    # Command Pattern actions
│   └── IssueActions/          # Issue-specific actions
├── Commands/                   # Artisan commands
├── Contracts/                  # Service interfaces
├── Http/                      # Controllers and middleware
├── Jobs/                      # Queue jobs
├── Livewire/                  # Livewire components
├── Models/                    # Eloquent models
├── Scanners/                  # Code scanning logic
│   └── Rules/                 # Scanning rules by category
│       └── Blade/             # Blade template rules
├── Services/                  # Business logic services
└── CodeSnoutrServiceProvider.php

config/
├── build/                     # Build configuration
├── quality/                   # Quality assurance configs
└── codesnoutr.php            # Package configuration

docs/
├── development/               # Development guides
└── guides/                   # User guides

backups/
└── refactoring/              # Refactoring backups
```

## Architecture Patterns

### 1. Single Responsibility Principle (SRP)
- Each service has one clear responsibility
- Actions handle single operations on issues
- Rules focus on specific categories of checks

### 2. Command Pattern
- `IssueActionInvoker` coordinates all issue actions
- Individual action classes implement `IssueActionInterface`
- Actions are composable and testable in isolation

### 3. Service Layer Architecture
- Business logic separated into focused services
- Services injected via Laravel's service container
- Clean separation between presentation and business logic

### 4. Interface Segregation
- Contracts define clear service interfaces
- Easy to mock for testing
- Enables implementation swapping

## Key Services

### ScanResultsViewService
Handles view-related operations for scan results:
- Directory tree generation
- File issue loading
- Statistics calculation

### IssueFilterService
Manages filtering and searching of issues:
- Filter application
- Search functionality
- Statistical breakdowns

### IssueExportService
Handles exporting of scan results:
- Multiple format support (JSON, CSV, PDF)
- Configurable export options

### BulkActionService
Manages bulk operations on multiple issues:
- Bulk resolution, ignoring, false positives
- Transaction handling
- Progress tracking

### BladeRuleEngine
Modular rule system for Blade templates:
- Category-based rule organization
- Easy rule addition/removal
- Performance optimized scanning

## Design Benefits

1. **Maintainability**: Small, focused classes are easier to understand and modify
2. **Testability**: Each component can be unit tested independently
3. **Extensibility**: New features can be added without modifying existing code
4. **Reusability**: Services can be reused across different parts of the application
5. **Performance**: Optimized database queries and caching strategies