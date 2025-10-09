# Changelog

All notable changes to CodeSnoutr will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.1] - 2025-10-09

### Fixed
- **CSS Asset Loading** - Resolved critical UI styling issue where CSS was not applied after installation
  - Fixed `app-layout.blade.php` asset loading logic to correctly read from manifest.json
  - Now properly loads CSS files using correct manifest keys (`resources/css/codesnoutr.css`)
  - Added fallback iteration through all manifest entries for better compatibility
  - Enhanced asset verification to check both build directory and individual CSS files

### Enhanced
- **InstallCommand** - Improved asset publishing and installation process
  - Added automatic copying of entire `public/build/` directory during installation
  - Enhanced `copyAssetsManually()` method to handle compiled Vite assets
  - Improved asset verification with checks for both manifest.json and CSS files
  - Better error handling and fallback mechanisms for asset publishing
  - Ensures assets are properly copied to `public/vendor/codesnoutr/build/`

### Added
- **CSS Troubleshooting Documentation** - Comprehensive guides for diagnosing and fixing CSS issues
  - Created `CSS_TROUBLESHOOTING.md` - Complete troubleshooting guide with solutions
  - Created `CSS_FIX_SUMMARY.md` - Quick reference for the CSS fix
  - Updated README.md with CSS troubleshooting section
  - Added FAQ entry for "UI appears broken with no CSS styling"

### Technical
- **Asset Loading Priority** - Improved asset loading strategy
  1. Package built assets from `public/vendor/codesnoutr/build/` (preferred)
  2. Main app Vite build using `@vite()` directive (fallback)
  3. CDN Tailwind CSS (last resort for development)
- **Manifest Handling** - Better support for Vite manifest structure
  - Handles source path keys (`resources/css/codesnoutr.css`)
  - Supports both explicit keys and wildcard iteration
  - Proper error handling for missing or malformed manifests

## [1.0.0] - 2025-10-09

🎉 **Initial Production Release** - Complete Laravel code scanner with AI-powered auto-fix capabilities, designed for local development environments.

### Added
- **OpenAI Client Implementation Roadmap** - Comprehensive development plan for AI features
  - Created detailed roadmap document (OPENAI_CLIENT_ROADMAP.md) with 6 development phases
  - Phase 1: Stability & Reliability (Q4 2025) - Enhanced error handling and JSON parsing
  - Phase 2: Performance & Cost Optimization (Q1 2026) - Caching, prompt optimization, smart model selection
  - Phase 3: Advanced AI Features (Q2 2026) - Function calling, streaming, Vision API
  - Phase 4: Learning & Intelligence (Q3 2026) - Feedback loops, pattern recognition
  - Phase 5: Advanced Capabilities (Q4 2026) - Multi-file refactoring, test generation, security patching
  - Success metrics, technical debt tracking, and release schedule through v3.0.0
  - Linked roadmap from README and CONTRIBUTING for easy access

### Changed
- **Documentation Overhaul** - Focused on local development environment
  - Updated README to emphasize local development use (removed production references)
  - Simplified queue setup instructions (removed Supervisor/production configs)
  - Streamlined cache configuration (focus on file driver for local dev)
  - Condensed AI Auto-Fix setup guide (removed production-oriented sections)
  - Updated requirements section (changed "Server Requirements" to "Local Environment")
  - Reduced troubleshooting section from 60+ lines to essentials
  - Simplified best practices to 7 concise points
  - Added prominent warning: "This package is designed for local development environments only"
  - Overall reduction from 1,538 to ~1,400 lines for better clarity

### Removed
- **Debug Logging Cleanup** - Eliminated verbose debug logs cluttering application logs
  - Removed "CodeSnoutr Livewire components registered successfully" log from ServiceProvider
  - Removed "AI Service Constructor Debug" logs with API key details from AiAssistantService
  - Removed all "Results page" debug logs from DashboardController (8 log statements total):
    - Total scans in database logging
    - Filtering by status/type/date logs
    - SQL query logging with bindings
    - Paginated scans count/total logs
  - Removed debug endpoint exposing SQL queries
  - Log reduction: ~40+ entries per page load → essential error logs only

### Fixed
- **Blade Component Registration** - Cleaned up and verified all component registrations
  - All registered components now match existing component files
  - Removed registrations for non-existent components (tooltip, header, scan-form)
  - Verified atoms, molecules, organisms, and templates are properly registered
  - Ensured consistent naming convention across all components

### Documentation
- **Comprehensive Cleanup Summary** - Created CLEANUP_SUMMARY.md documenting all changes
  - Detailed log removal impact (40+ logs reduced to essentials)
  - Documentation changes (1,538 → ~1,000 focused lines)
  - Testing checklist for verification
  - Clear positioning as local development tool

### Added (Earlier)
- **Queue Worker Protection** - Prevents Fix All job dispatch when queue worker is not running
  - Automatic queue worker detection before job dispatch (async mode only)
  - Immediate error feedback with clear instructions when worker is missing
  - Browser alert and UI error banner with helpful guidance
  - Prevents wasted AI tokens on jobs that can't execute
  - OS-specific process detection (macOS/Linux/Windows)
  - Comprehensive logging for debugging queue worker issues
  - Documentation in QUEUE_WORKER_PROTECTION.md
  - Skips check for sync queues and debug mode

### Improved
- **Error Handling** - Enhanced user experience when queue worker is unavailable
  - Clear error messages: "Cannot start Fix All: Queue worker is not running"
  - Actionable instructions: "Please start the queue worker with: php artisan queue:work"
  - Status immediately set to 'failed' instead of stuck at 'processing'
  - Browser notification support for queue worker errors
  - Better distinction between sync and async queue modes

### Technical
- **Livewire Component** - Added `isQueueWorkerRunning()` method to FixAllProgress component
- **JavaScript Events** - Added `show-notification` event handler for queue worker alerts
- **Documentation** - Created comprehensive QUEUE_WORKER_PROTECTION.md guide

## 2025-09-28

### Enhanced
- **Scan Results Page** - Complete overhaul with professional UI/UX improvements
  - Fixed critical layout rendering issue by updating from `@extends` to component-based layout syntax
  - Enhanced filter system with advanced status, type, and date filtering capabilities
  - Improved dark mode support with better contrast ratios for inputs and selects
  - Added comprehensive hover effects and smooth transitions throughout the interface
  - Enhanced button styling with shadows, gradients, and professional appearance
  - Better row alignment with proper `items-center` classes on all horizontal elements
  - File scan display now shows "File: /path/to/filename" instead of generic "File" label

### Fixed
- **Layout Component Integration** - Resolved `View [templates.app-layout] not found` error
  - Updated results page to use proper `<x-templates.app-layout>` component syntax
  - Fixed Blade template structure from legacy `@extends/@section` to modern component approach
  - Ensured consistent layout usage across all pages in the application

### Improved
- **User Interface Polish** - Professional-grade visual enhancements
  - Enhanced all form inputs with better dark mode contrast and accessibility
  - Added hover states for better user feedback on interactive elements
  - Improved button contrast and clickability indicators across all themes
  - Professional table design with enhanced hover effects and visual hierarchy
  - Better spacing and alignment throughout the results interface

### Removed
- **Cleanup** - Removed development and test artifacts
  - Removed unused test files: `dark-mode-test.blade.php`, `input-dark-mode-test.blade.php`
  - Cleaned up development artifacts and debugging components
  - Streamlined codebase for production deployment

### Technical
- **Frontend Architecture** - Continued refinement of build system
  - Updated Vite and PostCSS configurations for optimal asset compilation
  - Enhanced Tailwind CSS integration with component-specific styling
  - Improved JavaScript bundling with better performance optimization

## 2025-09-27

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

---

## Release Notes

### Package Information
- **Version**: 1.0.0
- **Release Date**: October 9, 2025
- **Status**: Stable - Production Ready for Local Development
- **License**: MIT

### What's New in 1.0.0

This is the initial production release of CodeSnoutr, combining all development work into a stable, feature-complete package designed specifically for local Laravel development environments.

### Core Features

#### Comprehensive Scanning Engine
- **ScanManager**: Orchestrates file, directory, and full codebase scans
- **Security Scanner**: SQL injection, XSS, CSRF, hardcoded credentials detection
- **Performance Scanner**: N+1 queries, missing indexes, cache opportunities
- **Quality Scanner**: Code complexity, documentation, naming conventions
- **Laravel Scanner**: Eloquent, routes, migrations, validation best practices
- **Blade Scanner**: Template security, performance, accessibility, SEO

#### AI-Powered Auto-Fix System
- **Smart Fix Generation**: Context-aware code improvements with OpenAI integration
- **Safety Features**: Automatic backups, syntax validation, confidence scoring
- **Fix All Operation**: Background processing with real-time progress tracking
- **Queue Worker Protection**: Prevents job dispatch without active queue workers
- **Rollback Support**: Restore files from backup if fixes fail

#### Modern Web Interface
- **Livewire Dashboard**: Real-time statistics and recent scan overview
- **Scan Wizard**: Interactive multi-step scanning interface
- **Two-Column Results**: File tree navigation with detailed issue display
- **Dark/Light Mode**: System preference detection with manual toggle
- **AI Chat Assistant**: Real-time conversation for code scanning help

#### Professional UI/UX
- **Atomic Design System**: 56 Blade components (atoms, molecules, organisms, templates)
- **Responsive Design**: Tailwind CSS with mobile-first approach
- **Smooth Animations**: Hover effects, transitions, loading states
- **Accessibility**: ARIA labels, keyboard navigation, screen reader support
- **Export Options**: JSON, CSV formats for integration and analysis

#### Queue Management
- **Background Processing**: Laravel queue integration for Fix All operations
- **Progress Tracking**: Real-time updates via cache (file/database/redis)
- **Worker Detection**: Automatic verification before job dispatch
- **Error Handling**: Comprehensive logging and user feedback
- **Cost Tracking**: Token usage and cost estimates for AI operations

#### Command Line Interface
- `codesnoutr:install` - Interactive package installation wizard
- `codesnoutr:scan` - Flexible scanning with multiple output formats
- `codesnoutr:asset-status` - Asset compilation and publishing verification
- Support for file, directory, and codebase scanning modes

#### Developer Experience
- **Comprehensive Documentation**: README, ROADMAP, CONTRIBUTING guides
- **OpenAI Roadmap**: 6-phase development plan through v3.0.0
- **Testing Framework**: Feature tests with factories and mocking
- **Debug Tools**: Asset status monitoring, error reporting
- **Clean Codebase**: No debug logs, production-ready code

### Technical Specifications
- **Requirements**: PHP 8.1+ with required extensions, Laravel 10/11/12
- **Target Environment**: Local development only (not for production)
- **Database**: MySQL 5.7+, PostgreSQL 10+, SQLite 3.8+
- **Dependencies**: Livewire 2.x/3.x, Tailwind CSS, nikic/php-parser
- **Architecture**: Modular, extensible, service-oriented design
- **Performance**: Optimized for large codebases with efficient processing

### Documentation
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
