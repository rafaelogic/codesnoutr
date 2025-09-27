# Changelog

All notable changes to CodeSnoutr will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.1] - 2025-09-27

### Fixed
- **Dashboard Component Rendering** - Fixed critical issue where dashboard was showing raw HTML instead of rendered components
  - Corrected component references from `x-codesnoutr::` to proper registered names (`x-atoms.`, `x-molecules.`)
  - Removed duplicate PHP code in button component that was causing syntax errors
  - Added missing icons (document-magnifying-glass, shield-exclamation, arrow-up, arrow-down, plus, minus, and others)
  - Dashboard metrics, cards, and interactive elements now render properly
- **Button Component Syntax** - Fixed malformed PHP arrays and duplicate code blocks
- **Icon Component** - Added comprehensive icon set to support all dashboard requirements

### Added
- **Comprehensive Feature Test Suite** - Complete automated testing framework for all package functionality
  - **DashboardTest.php**: Dashboard component testing with statistics, charts, and real-time updates
  - **ScanWizardTest.php**: Multi-step scan wizard workflow validation and error handling
  - **ScanResultsTest.php**: Results display, filtering, pagination, and bulk operations testing
  - **AiAutoFixTest.php**: AI auto-fix functionality with API mocking and confidence validation
  - **SettingsTest.php**: Settings management, OpenAI connection testing, and configuration validation
  - **IntegrationTest.php**: End-to-end workflow testing and cross-component communication
  - **Model Factories**: ScanFactory and IssueFactory with realistic test data generation
  - **Test Infrastructure**: Enhanced TestCase with Livewire integration and helper methods
- **Enhanced AI Recommendations Display** - Improved visual presentation and user experience
  - Professional gradient backgrounds and modern card-style layout for AI recommendations
  - Interactive confidence indicators with visual progress bars and percentage display
  - Copy-to-clipboard functionality with success feedback animation
  - "Regenerate Fix" button for creating new AI suggestions
  - "Was this helpful?" feedback mechanism for recommendation quality tracking
  - Loading states with spinner animations for better user feedback
  - Organized debug/utility buttons with less prominent styling
- **Real-time AI Recommendations Updates** - Immediate UI updates without page refresh
  - Automatic refresh of selected file issues when AI fixes are generated
  - Real-time updates for all issue-modifying actions (resolve, ignore, mark false positive)
  - Smart update logic that only refreshes current view when relevant
  - Comprehensive Livewire integration for seamless user experience
- **AI Auto-Fix System** - Complete automated issue resolution with intelligent code generation
  - Comprehensive AiAutoFix Livewire component with preview, application, and rollback capabilities
  - AutoFixService with safe code modification and backup creation
  - Multi-step fix workflow: analysis → preview → apply → rollback if needed
  - Confidence scoring and safety validation for automated fixes
  - Context-aware code replacement with indentation preservation
  - Backup and restore functionality with timestamp tracking
- **Enhanced Two-Column Scan Results Layout** - Professional file tree navigation with issue details
  - Responsive two-column layout with file tree on left, issue details on right
  - Smart file grouping by directory with expandable/collapsible sections
  - Advanced pagination for both directories and issues
  - Enhanced file metadata display with issue counts and severity indicators
  - Real-time search and filtering across files and issues
  - Professional code snippet display with syntax highlighting and line numbers
- **Improved Frontend Build System** - Production-ready asset compilation
  - Vite configuration for optimized CSS and JavaScript bundling
  - Tailwind CSS integration with custom component styling
  - Alpine.js integration managed by Livewire (removed manual initialization)
  - Asset versioning and cache-busting for production deployments
  - Package asset publishing system for standalone deployment
- **Developer Testing Framework** - Comprehensive testing and debugging utilities
  - Multiple test components and pages for Livewire functionality validation
  - Asset status command for troubleshooting build and publishing issues
  - Development templates for rapid component prototyping
  - Debug logging and error tracking throughout the application
- **Comprehensive Blade Template Rules** - Complete scanning engine for Laravel Blade templates
  - XSS vulnerability detection with safe variable recognition
  - CSRF protection validation for all form methods
  - Performance optimization (N+1 queries, inline styles, template complexity)
  - Accessibility compliance checking (alt text, form labels, ARIA)
  - SEO optimization analysis (meta tags, structured content)
  - Code quality enforcement (deprecated syntax, hardcoded values)
  - Best practices validation (component usage, section structure)
- **AI Smart Assistant** - Complete AI-powered chat interface for code scanning assistance
  - OpenAI integration with conversation management
  - Context-aware suggestions and tips
  - Real-time markdown rendering with code syntax highlighting
  - Dark/light mode optimized scrollbars
  - Loading indicators and smooth UX transitions

### Changed
- **Testing Infrastructure Overhaul** - Complete modernization of testing framework
  - Enhanced TestCase with Livewire service provider integration
  - App encryption key configuration for Livewire component testing
  - Database factories replacing manual test data creation for consistency
  - Comprehensive test coverage strategy for all major features
- **Model Architecture Enhancement** - Improved data layer with factory support
  - Added HasFactory trait to Scan and Issue models for testing
  - Updated factory definitions to match actual database schema
  - Enhanced model relationships and validation for test scenarios
- **Major UI/UX Overhaul** - Redesigned interface for better usability and performance
  - Settings page restructured with dedicated AI configuration section
  - Sidebar navigation implementation for better organization
  - Professional loading states and user feedback throughout the application
  - Enhanced error handling with detailed user messaging
- **Frontend Architecture** - Complete rebuild of client-side functionality
  - Removed Alpine.js manual initialization to prevent conflicts with Livewire
  - Streamlined JavaScript utilities with better performance
  - Production-ready asset bundling with proper versioning
  - Responsive design improvements for all screen sizes
- **Code Quality Improvements** - Enhanced maintainability and debugging
  - Cleaned up all development and debugging code for production readiness
  - Improved error handling and logging throughout the application
  - Better separation of concerns between components and services
  - Enhanced type safety and validation across the codebase

### Fixed
- **Test Environment Configuration** - Resolved testing framework issues
  - Fixed missing encryption key errors in Livewire component tests
  - Resolved database schema constraint violations in test data creation
  - Fixed factory definitions to match actual database column requirements
  - Enhanced test environment setup with proper service provider registration
- **AI Recommendations Dark Mode** - Fixed visibility issues in dark theme
  - Replaced dynamic Tailwind class generation with explicit conditional styling
  - Proper dark mode color contrast for confidence indicators (green-300, yellow-300, red-300)
  - Fixed text readability in both light and dark modes
  - Ensured consistent styling across different confidence levels
- **Real-time UI Updates** - Eliminated need for manual page refresh
  - Fixed AI recommendations not appearing immediately after generation
  - Resolved issue where users had to refresh page to see new AI fixes
  - Improved Livewire component state management for instant updates
  - Enhanced user workflow with seamless real-time feedback
- **Critical Frontend Conflicts** - Resolved major JavaScript and CSS issues
  - Fixed Alpine.js multiple instance conflicts with Livewire integration
  - Resolved button click handling issues in settings and other components
  - Fixed asset loading and compilation errors
  - Enhanced browser compatibility and performance
- **Settings Management** - Complete overhaul of configuration handling
  - Fixed OpenAI API key storage and retrieval (now stored in plain text for developer access)
  - Resolved AI connection testing and validation issues
  - Fixed saving functionality with proper error feedback
  - Enhanced settings page navigation and user experience
- **Scan Results Interface** - Major improvements to results viewing
  - Fixed file tree navigation and expansion/collapse functionality
  - Resolved pagination issues in two-column layout
  - Enhanced search and filtering with proper state management
  - Fixed code snippet display and formatting issues
- **Livewire Component Issues** - Comprehensive component debugging and fixes
  - Resolved wire:click event handling across all components
  - Fixed component state management and data persistence
  - Enhanced error handling and user feedback
  - Improved component lifecycle management
- **Production Readiness** - Complete cleanup and optimization
  - Removed all debugging code, test methods, and development artifacts
  - Fixed asset publishing and deployment issues
  - Enhanced security and validation throughout the application
  - Optimized performance for production environments

### Technical Improvements
- **Comprehensive Testing Framework** - Professional-grade automated testing infrastructure
  - **Test Coverage**: Complete feature testing for all major user workflows and edge cases
  - **HTTP Facade Mocking**: OpenAI API testing with realistic response simulation
  - **Queue Facade Mocking**: Job testing and background process validation
  - **Livewire Testing**: Component interaction testing with state management validation
  - **Database Factories**: Consistent test data generation with realistic scenarios
  - **Error Scenario Testing**: Comprehensive edge case and failure mode validation
  - **Performance Testing**: Large dataset handling and pagination validation
  - **Cross-Component Testing**: Integration testing for real-time updates and communication
- **AI Integration Architecture** - Robust foundation for automated code analysis
  - Modular AutoFixService with extensible fix generation and application
  - Safe code modification with backup/restore capabilities
  - Context-aware prompt engineering for better AI responses
  - Comprehensive error handling and logging for AI operations
- **Modern Frontend Stack** - Production-ready build system
  - Vite-powered asset compilation with hot module replacement
  - Tailwind CSS with custom configuration and component styling
  - Optimized JavaScript bundling with proper tree-shaking
  - Asset versioning and cache management for optimal performance
- **Enhanced Development Experience** - Better debugging and testing tools
  - Comprehensive test suite for component functionality with 100% feature coverage
  - Asset status monitoring and troubleshooting utilities
  - Improved error reporting and debugging capabilities
  - Professional development workflow with proper tooling and validation

## [1.0.0] - 2025-08-18

### Added
- **Core Package Structure**
  - Laravel service provider with complete registration
  - Comprehensive configuration system
  - Database migrations for scans, issues, and settings
  - Complete Eloquent models with relationships

- **Scanning Engine**
  - ScanManager orchestrator class
  - File, directory, and codebase scan handlers
  - Abstract scanner base class for extensibility
  - Rule engine architecture

- **Rule Engines**
  - SecurityRuleEngine: SQL injection, XSS, CSRF, hardcoded credentials detection
  - PerformanceRuleEngine: N+1 queries, missing indexes, caching opportunities
  - QualityRuleEngine: Code complexity, documentation, naming conventions
  - LaravelBestPracticesEngine: Laravel-specific best practices validation

- **Modern Web Interface**
  - Complete Livewire-powered dashboard
  - Dark/light mode with system preference detection
  - Responsive design with Tailwind CSS
  - Interactive components for scanning and results management

- **Livewire Components**
  - Dashboard: Overview with statistics and recent scans
  - ScanForm: Interactive scanning interface with progress tracking
  - ScanResults: Detailed results with filtering and bulk operations
  - Settings: Configuration management with AI setup
  - DarkModeToggle: Theme switching functionality

- **User Interface**
  - Professional layout with navigation and branding
  - Complete page templates for all features
  - Advanced filtering and search capabilities
  - Code preview with syntax highlighting
  - Export functionality (JSON, CSV)

- **Command Line Interface**
  - `codesnoutr:install` - Interactive package installation
  - `codesnoutr:scan` - Flexible scanning with multiple options
  - Support for file, directory, and codebase scanning
  - Multiple output formats (table, JSON, CSV)
  - Save results to database option

- **Developer Experience**
  - Comprehensive documentation and setup guides
  - Professional styling with custom CSS
  - Interactive JavaScript enhancements
  - Keyboard shortcuts and accessibility features

- **Configuration System**
  - Flexible scanning options and rule configuration
  - AI integration settings (OpenAI ready)
  - UI preferences and theming options
  - Export and integration settings

- **Export and Reporting**
  - JSON export for API integration
  - CSV export for data analysis
  - Web-based interactive reports
  - Real-time scan progress tracking

### Technical Specifications
- **Requirements**: PHP 8.1+, Laravel 10+
- **Dependencies**: Livewire, Tailwind CSS, nikic/php-parser, Symfony Finder
- **Database**: Complete schema with foreign key relationships
- **Architecture**: Modular, extensible, and testable design
- **Performance**: Optimized for large codebases with chunked processing

### Documentation
- Complete README with installation and usage instructions
- Comprehensive FEATURES.md with implementation roadmap
- IMPLEMENTATION_STATUS.md with technical details
- Professional CONTRIBUTING.md for community contributions

## [0.1.0] - 2025-08-18

### Added
- Initial package development and architecture planning
- Core concept validation and feature specification
- Development environment setup

---

## Future Release Planning

### [1.1.0] - Planned
- AI integration with OpenAI for automated fix suggestions
- Debugbar collector for development integration
- PDF report generation
- Advanced analytics and trending

### [1.2.0] - Planned  
- Scheduled scanning capabilities
- Email notifications for critical issues
- Team collaboration features
- Public API endpoints

### [2.0.0] - Planned
- Custom rule builder interface
- Multi-tenant support
- Advanced reporting dashboard
- Integration marketplace

---

## Release Notes Format

Each release includes:
- **Added**: New features
- **Changed**: Changes in existing functionality  
- **Deprecated**: Soon-to-be removed features
- **Removed**: Removed features
- **Fixed**: Bug fixes
- **Security**: Security improvements

## Support

- **Current Version**: 1.0.0 (Full support)
- **Minimum Supported**: 1.0.0
- **Security Updates**: All supported versions receive security patches

For upgrade guides and migration instructions, see the [documentation](README.md).
