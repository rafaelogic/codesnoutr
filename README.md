# CodeSnoutr - Laravel Code Scanner Package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/rafaelogic/codesnoutr.svg?style=flat-square)](https://packagist.org/packages/rafaelogic/codesnoutr)
[![Total Downloads](https://img.shields.io/packagist/dt/rafaelogic/codesnoutr.svg?style=flat-square)](https://packagist.org/packages/rafaelogic/codesnoutr)
[![PHP Version Require](https://img.shields.io/packagist/php-v/rafaelogic/codesnoutr.svg?style=flat-square)](https://packagist.org/packages/rafaelogic/codesnoutr)
[![Laravel](https://img.shields.io/badge/Laravel-10%2B%7C11%2B%7C12%2B-FF2D20?style=flat-square&logo=laravel)](https://laravel.com)
[![Tests](https://img.shields.io/github/actions/workflow/status/rafaelogic/codesnoutr/tests.yml?style=flat-square&label=tests)](https://github.com/rafaelogic/codesnoutr/actions)

A comprehensive Laravel code scanner that detects security vulnerabilities, performance issues, and code quality problems with a modern web interface and AI-powered auto-fix capabilities.

> **⚠️ Important**: This package is designed for **local development environments only**. It is not recommended for production use.

![Image](https://github.com/user-attachments/assets/db1ea42d-4c8a-4cf9-af68-0a3d7a31dafd)

## 📚 Table of Contents

- [✨ Features](#-features)
- [📋 Requirements](#-requirements)
- [🚀 Installation](#-installation)
- [⚡ Quick Start](#-quick-start)
- [⚙️ Configuration](#%EF%B8%8F-configuration)
- [🔍 Scanning Categories](#-scanning-categories)
- [🤖 AI Integration](#-ai-integration-ready-for-implementation)
- [📈 Current Status](#-current-status)
- [�️ Roadmap](#%EF%B8%8F-roadmap)
- [�📁 Package Structure](#-package-structure)
- [🤝 Contributing](#-contributing)
- [🔒 Security](#-security)
- [📄 License](#-license)

## ✨ Features

🔍 **Comprehensive Scanning**
- ✅ Security vulnerability detection (SQL injection, XSS, hardcoded credentials)
- ✅ Performance optimization suggestions (N+1 queries, missing indexes, cache opportunities)
- ✅ Code quality analysis (complexity, naming conventions, documentation)
- ✅ Laravel best practices enforcement (Eloquent, routes, validation)
- ✅ Blade template analysis (XSS protection, CSRF, accessibility, SEO)
- ✅ Context-aware exception handling for inheritance and constants

🎨 **Modern Web Interface**
- ✅ Complete dark/light mode support with user preferences
- ✅ Livewire-powered interactive dashboard with expandable details
- ✅ Tailwind CSS styling with responsive design and modern hover effects
- ✅ Real-time filtering, sorting, and bulk operations
- ✅ Enhanced file grouping with collapsible sections and smooth animations

🚀 **Flexible Scanning Options**
- ✅ Single file scanning with detailed analysis
- ✅ Directory scanning with exclusion patterns
- ✅ Full codebase scanning with progress tracking
- ✅ Category-based filtering (security, performance, quality, laravel)
- ✅ Intelligent queue management with auto-start functionality
- ✅ Real-time queue status monitoring and automatic worker detection
- ✅ **Queue worker protection** - Prevents job dispatch when worker not running, saves AI tokens

🔧 **Developer Integration**
- ✅ Comprehensive Artisan commands with flexible options
- ✅ Export capabilities (JSON, CSV, database storage)
- ✅ Detailed reports with code context and suggestions
- ✅ Configuration-driven behavior with extensive options
- ✅ Automatic queue detection and management for background processing

🤖 **AI Smart Assistant**
- ✅ Complete AI-powered chat interface for code scanning assistance
- ✅ OpenAI integration with conversation management
- ✅ Context-aware suggestions, tips, and best practices
- ✅ Real-time markdown rendering with code syntax highlighting
- ✅ Dark/light mode optimized interface with smooth scrolling

🤖 **AI-Ready Architecture**
- ✅ Professional chat interface with loading indicators
- ✅ Robust response processing for complex AI output
- ✅ UTF-8 character support and encoding handling
- 🚧 Context-aware suggestion framework (expandable)
- 🚧 Safe code generation with preview system (coming soon)
- 🚧 Cost tracking and safety limits (planned)

## 📋 Requirements

### System Requirements

- **PHP**: 8.1 or higher with required extensions:
  - `mbstring` - Multi-byte string handling
  - `curl` - API requests and HTTP communication
  - `xml` - XML parsing for configuration
  - `zip` - Archive handling
  - `bcmath` - Arbitrary precision mathematics
  - `tokenizer` - PHP code tokenization for scanning
  - `json` - JSON encoding/decoding
  - `pdo` - Database connectivity
  - `fileinfo` - File type detection

- **Laravel**: 10.0, 11.0, or 12.0
  - Livewire 2.x or 3.x (automatically installed)
  - Alpine.js (included with Livewire)

- **Database** (for local development): 
  - MySQL 5.7+ / MariaDB 10.3+
  - PostgreSQL 10+
  - SQLite 3.8+
  - Minimum 50MB free space for scan results

- **Local Environment**:
  - Minimum 512MB RAM (1GB+ recommended)
  - PHP `max_execution_time` ≥ 300 seconds (for large scans)
  - Two terminal windows (one for app, one for queue worker)
  - PHP `memory_limit` ≥ 256MB (512MB+ recommended)
  - Disk space: 100MB+ for package and scan data

### Queue & Cache Requirements (For AI Auto-Fix)

**CRITICAL**: The AI Auto-Fix and Fix All features require proper queue and cache configuration:

#### Cache Driver (Required)
- ✅ **Supported**: `file`, `redis`, `database`
- ❌ **NOT Supported**: `array` (doesn't persist across processes)
- **Why**: Queue workers and web interface must share progress data

#### Queue Driver (Required)
- ✅ **Supported**: `database`, `redis`, `beanstalkd`, `sqs`
- ❌ **NOT Supported**: `sync` (blocks the interface)
- **Why**: AI fixes run in background to prevent timeouts

#### Queue Worker (Required)
- Must have at least one worker running: `php artisan queue:work`
- Supervisor recommended for production
- CodeSnoutr includes automatic worker detection

### AI Auto-Fix Requirements (Optional)

To use AI-powered automatic fixing:

- **OpenAI Account**: [platform.openai.com](https://platform.openai.com)
  - Valid API key with access to GPT-4 or GPT-3.5
  - Minimum $5 credit balance recommended
  - Rate limits: See OpenAI tier documentation

- **Internet Connection**: Stable connection for API requests

- **Recommended Models**:
  - `gpt-4` - Best quality, higher cost (~$0.03/issue)
  - `gpt-4-turbo` - Faster, more cost-effective (~$0.01/issue)
  - `gpt-3.5-turbo` - Budget option (~$0.002/issue)

- **Cost Estimation**:
  - Small project (50 issues): $0.10 - $1.50
  - Medium project (200 issues): $0.40 - $6.00
  - Large project (1000 issues): $2.00 - $30.00

### Frontend Requirements

- **Modern Browser**: 
  - Chrome 90+, Firefox 88+, Safari 14+, Edge 90+
  - JavaScript enabled
  - LocalStorage enabled (for theme preferences)

- **Screen Resolution**: 1024x768 minimum (responsive design)

## 🚀 Installation

### Prerequisites

Before installing CodeSnoutr, ensure your system meets these requirements:

- **PHP**: 8.1 or higher with the following extensions:
  - `mbstring`, `curl`, `xml`, `zip`, `bcmath`, `tokenizer`
- **Laravel**: 10.0, 11.0, or 12.0
- **Database**: MySQL 5.7+, PostgreSQL 10+, or SQLite 3.8+
- **Composer**: Latest version recommended
- **Frontend**: Modern browser with JavaScript enabled

### Step 1: Install via Composer

Install the package using Composer:

```bash
composer require rafaelogic/codesnoutr
```

### Step 2: Run Installation Command

Execute the installation command to set up CodeSnoutr:

```bash
php artisan codesnoutr:install
```

This interactive installation will:

1. **Publish Configuration**: Creates `config/codesnoutr.php` with default settings
2. **Run Migrations**: Sets up database tables for scans, issues, and settings
3. **Publish Assets**: Copies CSS and JavaScript files to `public/vendor/codesnoutr/`
4. **Create Directories**: Sets up necessary storage directories
5. **Configure Routes**: Registers web and API routes
6. **Set Permissions**: Ensures proper file permissions

### Step 3: Configure Environment (Optional)

Add CodeSnoutr-specific environment variables to your `.env` file:

```env
# Basic Configuration
CODESNOUTR_ENABLED=true

# Queue Management (Recommended for Better Performance)
CODESNOUTR_QUEUE_ENABLED=true
CODESNOUTR_QUEUE_CONNECTION=database
CODESNOUTR_QUEUE_AUTO_START=true

# Cache Driver (REQUIRED for Fix All Progress Tracking)
# ⚠️ CRITICAL: Do NOT use 'array' driver - it doesn't persist between processes!
CACHE_DRIVER=file          # Recommended for development
# CACHE_DRIVER=redis       # Recommended for production
# CACHE_DRIVER=database    # Alternative if you have cache table

# Queue Driver (Required for Background Processing)
QUEUE_CONNECTION=database  # Recommended for most cases
# QUEUE_CONNECTION=redis   # Better for high-volume production

# AI Integration (Optional)
CODESNOUTR_AI_ENABLED=false
OPENAI_API_KEY=your_openai_api_key_here
CODESNOUTR_AI_MODEL=gpt-4

# Security & Access
CODESNOUTR_ACCESS_MIDDLEWARE=web
```

> **⚠️ Important Cache Configuration**
> 
> The **Fix All Issues** feature requires a persistent cache driver to track progress between the queue worker and web interface. The `array` cache driver will **NOT work** because it's per-process memory and doesn't share data between processes.
> 
> **Supported Cache Drivers:**
> - ✅ `file` - Works out of the box, good for development
> - ✅ `redis` - Best performance, recommended for production
> - ✅ `database` - Requires `php artisan cache:table && php artisan migrate`
> - ❌ `array` - **Does NOT work** (no cross-process sharing)
>
> **Why This Matters:**
> - Queue workers run in separate processes from your web server
> - Progress updates must be shared between these processes
> - Without proper cache driver, progress bars won't update
> - AI fixes may waste tokens if jobs run unexpectedly

### Step 4: Configure Queue & Cache (Required for Fix All Features)

> **⚠️ Important**: CodeSnoutr is designed for **local development environments only**. Do not use in production.

#### 🎯 Quick Setup for Local Development

For the **Fix All Issues** feature to work properly, you MUST configure both queue and cache drivers:

```bash
# 1. Set cache driver in .env (DO NOT use 'array')
CACHE_DRIVER=file

# 2. Set queue driver in .env
QUEUE_CONNECTION=database

# 3. If using database queue, create the table (only once)
php artisan queue:table
php artisan migrate

# 4. Start queue worker in a separate terminal (required for background processing)
php artisan queue:work --verbose --timeout=300
```

#### 📋 Cache Driver Requirements

The Fix All progress tracking **requires** a cache driver that persists across processes:

| Driver | Status | Setup | Use Case |
|--------|--------|-------|----------|
| **file** | ✅ Recommended | Works out of box | Local development |
| **database** | ✅ Supported | Run `php artisan cache:table` | Alternative option |
| **array** | ❌ **Won't Work** | N/A | Queue workers can't share data |

**Why This Matters:**
```
Web Server Process        Queue Worker Process
    ↓                           ↓
[Read Progress]  ←─── [Shared Cache] ←─── [Write Progress]
                      (file/database)

❌ With 'array': Each process has its own memory - NO SHARING
✅ With 'file': Both read/write to same file - WORKS
```

#### 🔧 Queue Worker for Development

Open a new terminal window and run:

```bash
# Start queue worker (keep this running)
php artisan queue:work --verbose --timeout=300
```

**Tips:**
- Keep the queue worker running in a separate terminal
- Use `--verbose` flag to see jobs being processed
- Press `Ctrl+C` to stop the worker when done
- Restart worker after code changes to pick up new code

#### 🛡️ Queue Worker Protection

CodeSnoutr includes **automatic queue worker detection** to prevent wasting AI tokens:

- ✅ **Before Fix All**: Checks if queue worker is running
- ❌ **Blocks Dispatch**: If no worker detected, shows error with instructions
- 💰 **Saves Tokens**: Prevents jobs from accumulating and running unexpectedly
- 🔔 **User Notification**: Clear error message with recommendations

**What You'll See:**
```
❌ Queue Worker Not Running

Cannot dispatch Fix All job. Jobs would accumulate and run 
unexpectedly later, wasting AI tokens.

Recommendations:
✓ Start queue worker: php artisan queue:work
✓ Or run synchronously: Use "Run Fix All (Sync)" button
✓ Or enable auto-start in config: CODESNOUTR_QUEUE_AUTO_START=true
```

#### 📊 Monitoring Progress

The Fix All feature includes comprehensive logging:

**Browser Console (F12):**
```javascript
🔄 wire:poll #5 (1000ms since last)
✅ Changes detected: currentStep: 2→3, fixedCount: 0→1
```

**Laravel Logs:**
```bash
# Watch progress updates in real-time
tail -f storage/logs/laravel.log | grep "wire:poll"

# Or use the provided monitoring script
./watch_poll_logs.sh
```

**Troubleshooting:**
```bash
# Check if queue worker is running
ps aux | grep "queue:work"

# Check failed jobs
php artisan queue:failed

# Clear failed jobs
php artisan queue:flush

# Restart queue worker
php artisan queue:restart
```

### Step 5: Access the Dashboard

Navigate to the CodeSnoutr dashboard:

```
http://your-app.com/codesnoutr
```

You should see:
- ✅ Dashboard with welcome message
- ✅ Navigation menu with all sections
- ✅ Dark/light mode toggle
- ✅ Ready-to-use scanning interface

### Troubleshooting Installation

#### Permission Issues
```bash
# Fix storage permissions
chmod -R 755 storage/
chown -R www-data:www-data storage/

# Fix published assets permissions
chmod -R 755 public/vendor/codesnoutr/
```

#### Database Issues
```bash
# Re-run migrations if needed
php artisan migrate:refresh

# Check database connection
php artisan tinker
>>> \DB::connection()->getPdo()
```

#### Asset Publishing Issues
```bash
# Force republish assets
php artisan vendor:publish --tag=codesnoutr-assets --force

# Clear and rebuild cache
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

#### Queue Worker Issues

**Important:** CodeSnoutr now includes **queue worker protection** to prevent wasting AI tokens. If you try to run Fix All without a queue worker running, you'll see an error message and the job will not be dispatched.

```bash
# Check queue status
php artisan queue:status

# Restart queue workers
php artisan queue:restart

# Test queue functionality
php artisan queue:work --once

# Start queue worker for Fix All jobs
php artisan queue:work --verbose
```

**Why This Matters:**
- Prevents jobs from being queued when they can't execute
- Saves AI API tokens by not dispatching unprocessable jobs
- Provides immediate feedback when queue worker is missing
- Clear instructions on how to start the worker

See [QUEUE_WORKER_PROTECTION.md](QUEUE_WORKER_PROTECTION.md) for detailed documentation.

### Manual Installation (Advanced)

If you prefer manual installation or need custom configuration:

#### 1. Publish Components Individually

```bash
# Publish configuration only
php artisan vendor:publish --tag=codesnoutr-config

# Publish assets only
php artisan vendor:publish --tag=codesnoutr-assets

# Publish migrations only
php artisan vendor:publish --tag=codesnoutr-migrations

# Publish all at once
php artisan vendor:publish --provider="Rafaelogic\CodeSnoutr\CodeSnoutrServiceProvider"
```

#### 2. Run Migrations Manually

```bash
php artisan migrate
```

#### 3. Configure Custom Routes (Optional)

If you want to customize routes, add to your `routes/web.php`:

```php
Route::group(['prefix' => 'code-scanner', 'middleware' => ['web', 'auth']], function () {
    require base_path('vendor/rafaelogic/codesnoutr/routes/web.php');
});
```

### Updating CodeSnoutr

When updating to a new version:

```bash
# Update the package
composer update rafaelogic/codesnoutr

# Republish assets and config
php artisan vendor:publish --tag=codesnoutr-assets --force
php artisan vendor:publish --tag=codesnoutr-config --force

# Run any new migrations
php artisan migrate

# Clear caches
php artisan config:clear
php artisan view:clear
php artisan route:clear
```

### Uninstallation

To completely remove CodeSnoutr:

```bash
# Remove the package
composer remove rafaelogic/codesnoutr

# Remove database tables (optional)
php artisan migrate:rollback --path=database/migrations/codesnoutr

# Remove published files (optional)
rm -rf config/codesnoutr.php
rm -rf public/vendor/codesnoutr/

# Clear caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Docker Installation

For Docker environments, add to your `Dockerfile`:

```dockerfile
# Install CodeSnoutr
RUN composer require rafaelogic/codesnoutr

# Install and configure
RUN php artisan codesnoutr:install --no-interaction

# Set up queue worker (add to supervisord.conf)
COPY docker/supervisord/codesnoutr.conf /etc/supervisor/conf.d/
```

### Production Deployment

For production environments:

1. **Optimize Performance**:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

2. **Set Up Monitoring**:
```bash
# Monitor queue workers
php artisan horizon  # If using Laravel Horizon
```

3. **Configure Logging**:
```php
// In config/logging.php
'channels' => [
    'codesnoutr' => [
        'driver' => 'daily',
        'path' => storage_path('logs/codesnoutr.log'),
        'level' => 'info',
        'days' => 7,
    ],
],
```

4. **Set Up Scheduled Tasks** (Optional):
```php
// In app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    // Schedule periodic scans
    $schedule->command('codesnoutr:scan codebase --save')
             ->daily()
             ->withoutOverlapping();
}
```

## ⚡ Quick Start

### 🚀 First Scan in 30 Seconds

After installation, get your first scan results immediately:

```bash
# Scan a single file for quick testing
php artisan codesnoutr:scan file app/Models/User.php

# Or scan your entire application
php artisan codesnoutr:scan codebase --save
```

### 🌐 Web Interface Walkthrough

1. **Open the Dashboard**: Visit `http://your-app.com/codesnoutr`

2. **Start Your First Scan**:
   - Click "New Scan" or "Scan Wizard"
   - Choose scan type: File, Directory, or Full Codebase
   - Select categories: Security, Performance, Quality, Laravel
   - Click "Start Scan"

3. **View Results**:
   - Real-time progress updates during scanning
   - Automatic redirect to results when complete
   - Filter by severity: Critical, High, Medium, Low, Info
   - Expand issue groups for detailed analysis

4. **Explore Features**:
   - **Dark Mode**: Toggle in the top navigation
   - **AI Assistant**: Click the floating AI button (with OpenAI configured)
   - **Export**: Download results as JSON or CSV
   - **Settings**: Configure scanning preferences

### 🛠️ Common Use Cases

#### Security Audit
```bash
# Focus on security issues only
php artisan codesnoutr:scan codebase --categories=security --format=json
```

#### Performance Review
```bash
# Check for N+1 queries and performance issues
php artisan codesnoutr:scan codebase --categories=performance
```

#### Code Quality Check
```bash
# Analyze code quality and Laravel best practices
php artisan codesnoutr:scan directory app --categories=quality,laravel
```

#### Pre-deployment Scan
```bash
# Comprehensive scan before deployment
php artisan codesnoutr:scan codebase --save --export-path=deployment-report.json
```

### Web Dashboard

Access your CodeSnoutr dashboard:
```
http://your-app.com/codesnoutr
```

The web interface provides:
- **Dashboard**: Overview with statistics and recent scans
- **Scanner**: Interactive scanning with real-time progress and automatic queue management
- **Results**: Advanced filtering, sorting, and issue management with expandable details
- **AI Assistant**: Smart chat interface for code scanning help and best practices
- **Settings**: Configuration management and AI setup

### 🔄 Enhanced User Experience

CodeSnoutr provides a seamless scanning experience with modern UI/UX features:

- **Smart File Grouping**: Issues are intelligently grouped by file with collapsible sections
- **Expandable Details**: Click to expand issue details in the detailed table view
- **Modern Hover Effects**: Elegant glow border effects provide visual feedback
- **Automatic Redirects**: Smooth workflow transitions after resolving issues
- **Real-Time Updates**: Live status updates without page refreshes
- **Responsive Design**: Works perfectly on desktop, tablet, and mobile devices

### 🔄 Automatic Queue Management

CodeSnoutr intelligently manages Laravel queues for optimal performance:

- **Auto-Detection**: Automatically checks if queue workers are running before starting scans
- **Smart Start**: Launches queue workers automatically if none are detected
- **Real-Time Status**: Shows queue status during scan preparation
- **Background Processing**: All scans run in background jobs for better performance
- **Progress Tracking**: Real-time updates without blocking the UI

**No Configuration Required**: Queue management works out-of-the-box with sensible defaults. Customize behavior in `config/codesnoutr.php` if needed.

### 🤖 AI Smart Assistant

The AI Assistant provides intelligent help throughout your code scanning experience:

- **Context-Aware Conversations**: Ask questions about code scanning, security, performance, and Laravel best practices
- **Real-Time Markdown Rendering**: Beautiful formatting for code examples, bullet points, and structured responses
- **Smart Suggestions**: Get personalized tips based on your current scanning context
- **Code Examples**: Interactive code snippets with syntax highlighting
- **Best Practices**: Expert advice on PHP/Laravel development standards

**Access the AI Assistant**: Look for the floating AI button in the bottom-right corner of the interface, or navigate to any CodeSnoutr page where it's available as a slide-out panel.

**Configuration**: Set up your OpenAI API key in Settings → AI Integration to unlock the full potential of AI-powered assistance.

### Artisan Commands

**Scan entire codebase:**
```bash
php artisan codesnoutr:scan codebase
```

**Scan specific file:**
```bash
php artisan codesnoutr:scan file app/Models/User.php
```

**Scan directory:**
```bash
php artisan codesnoutr:scan directory app/Models
```

**Filter by categories:**
```bash
php artisan codesnoutr:scan codebase --categories=security,performance
```

**Export results:**
```bash
# Export as JSON
php artisan codesnoutr:scan codebase --format=json --export-path=results.json

# Export as CSV  
php artisan codesnoutr:scan codebase --format=csv --export-path=results.csv

# Save to database (default)
php artisan codesnoutr:scan codebase --save
```

## ⚙️ Configuration

The configuration file is published to `config/codesnoutr.php` with comprehensive options:

```php
return [
    'enabled' => env('CODESNOUTR_ENABLED', true),
    
    // Scanning configuration
    'scan' => [
        'paths' => ['app', 'config', 'routes', 'database/migrations', 'resources/views'],
        'exclude_paths' => ['vendor', 'node_modules', 'storage', 'bootstrap/cache'],
        'file_extensions' => ['php', 'blade.php'],
        'max_file_size' => 1024 * 1024, // 1MB
        'timeout' => 300, // 5 minutes
    ],
    
    // Queue management configuration
    'queue' => [
        'enabled' => env('CODESNOUTR_QUEUE_ENABLED', true),
        'name' => env('CODESNOUTR_QUEUE_NAME', 'default'),
        'connection' => env('CODESNOUTR_QUEUE_CONNECTION', config('queue.default')),
        'auto_start' => env('CODESNOUTR_QUEUE_AUTO_START', true),
        'timeout' => env('CODESNOUTR_QUEUE_TIMEOUT', 300),
        'memory' => env('CODESNOUTR_QUEUE_MEMORY', 512),
    ],
    
    // Scanner rules configuration
    'scanners' => [
        'security' => ['enabled' => true, 'rules' => [...]],
        'performance' => ['enabled' => true, 'rules' => [...]],
        'quality' => ['enabled' => true, 'rules' => [...]],
        'laravel' => ['enabled' => true, 'rules' => [...]],
    ],
    
    // AI integration (ready for implementation)
    'ai' => [
        'enabled' => env('CODESNOUTR_AI_ENABLED', false),
        'provider' => env('CODESNOUTR_AI_PROVIDER', 'openai'),
        'openai' => [
            'api_key' => env('OPENAI_API_KEY'),
            'model' => env('CODESNOUTR_AI_MODEL', 'gpt-4'),
        ],
    ],
    
    // UI preferences
    'ui' => [
        'theme' => ['default' => 'system', 'persist' => true],
        'pagination' => ['per_page' => 25, 'max_per_page' => 100],
    ],
];
```

## 🔍 Scanning Categories

### Security 🔒
**Implemented Detection Rules:**
- ✅ SQL injection vulnerabilities (raw queries, unsafe concatenation)
- ✅ XSS risks (unescaped output, dangerous HTML tags)
- ✅ Hardcoded credentials (passwords, API keys, secrets)
- ✅ Insecure file operations (unsafe uploads, directory traversal)
- ✅ Weak cryptography (MD5, SHA1 usage)
- ✅ Unsafe deserialization and eval() usage

### Performance ⚡
**Implemented Analysis Rules:**
- ✅ N+1 query detection and eager loading suggestions
- ✅ Missing database indexes identification
- ✅ Cache opportunity detection for expensive operations
- ✅ Memory usage optimization (chunking large datasets)
- ✅ Loop efficiency improvements (count() in conditions)
- ✅ File operation optimization suggestions

### Code Quality 📝
**Implemented Quality Checks:**
- ✅ Code complexity analysis (deep nesting, long methods)
- ✅ Naming convention enforcement (variables, classes, methods)
- ✅ Documentation requirements (PHPDoc for public methods)
- ✅ Code standards (line length, trailing whitespace)
- ✅ Best practices (magic numbers, empty catch blocks)
- ✅ Dead code and unused variable detection
- ✅ Context-aware variable validation with snake_case exception handling
- ✅ Intelligent inheritance and interface analysis

### Laravel Best Practices 🎯
**Implemented Laravel Rules:**
- ✅ Eloquent optimization (select(*) usage, raw SQL in models)
- ✅ Route efficiency (missing names, model binding opportunities)
- ✅ Blade template quality (PHP blocks, deep nesting)
- ✅ Validation completeness and rule strength
- ✅ Service container best practices
- ✅ Migration quality and safety checks
- ✅ Enhanced console command and artisan command analysis

### Blade Template Analysis 🎨
**Comprehensive Blade Template Scanning:**
- ✅ XSS vulnerability detection (unescaped output, dangerous functions)
- ✅ CSRF protection validation for forms
- ✅ Performance optimization (N+1 queries, inline styles, complex loops)
- ✅ Template complexity analysis (nesting depth, logic separation)
- ✅ Accessibility compliance (alt text, form labels, ARIA attributes)
- ✅ SEO optimization (meta tags, structured content)
- ✅ Code quality (deprecated syntax, hardcoded values, unused variables)
- ✅ Best practices enforcement (component usage, section structure)

## 🤖 AI Integration (Ready for Implementation)

### Current Status
The package includes complete infrastructure for AI integration:

- ✅ **Configuration Framework**: OpenAI API setup and cost tracking
- ✅ **UI Components**: Settings interface for AI configuration  
- ✅ **Safety Systems**: Preview and approval mechanisms
- ✅ **Context Engine**: Code analysis and suggestion generation framework

### Setup OpenAI (When Ready)

1. Get an API key from [OpenAI](https://platform.openai.com/api-keys)
2. Configure in your `.env`:
```env
OPENAI_API_KEY=your_api_key_here
CODESNOUTR_AI_ENABLED=true
CODESNOUTR_AI_MODEL=gpt-4
```

3. Or configure via the web interface at `/codesnoutr/settings`

### Planned AI Features
- **Smart Auto-fixes**: Context-aware code improvements
- **Explanation Generation**: Detailed explanations of issues
- **Best Practice Suggestions**: Laravel-specific recommendations
- **Safety Checks**: Preview changes before applying
- **Cost Management**: Usage tracking and monthly limits

## 🤖 AI Auto-Fix Setup Guide

> **⚠️ Local Development Only**: This package is designed for local development environments.

### Prerequisites Checklist

Before using AI Auto-Fix, ensure you have:

- ✅ **Queue Worker Running** (in a separate terminal)
- ✅ **Persistent Cache** (file or database driver, NOT array)
- ✅ **OpenAI API Key** (from platform.openai.com)
- ✅ **Code Backup** (always backup before running auto-fix)

### Quick Setup

#### 1. Start Queue Worker

Open a new terminal and run:

```bash
# Terminal 1: Queue worker (keep this running)
php artisan queue:work --verbose --timeout=300

# Terminal 2: Your Laravel app
php artisan serve
```

#### 2. Configure Cache Driver

In your `.env`:

```env
# DO NOT use 'array' - it won't work with queue workers
CACHE_DRIVER=file  # Recommended for local development
```

Or use database cache:
```bash
php artisan cache:table
php artisan migrate

# Then in .env:
CACHE_DRIVER=database
```

#### 3. Set Up OpenAI API Key

Get your API key from [platform.openai.com/api-keys](https://platform.openai.com/api-keys)

**Option 1: Configure in .env:**
```env
OPENAI_API_KEY=sk-your-actual-key-here
CODESNOUTR_AI_ENABLED=true
CODESNOUTR_AI_MODEL=gpt-4-turbo

# Optional: Set limits
CODESNOUTR_AI_MAX_TOKENS=4000
CODESNOUTR_AI_TEMPERATURE=0.2
```

**Option 2: Use Web Interface:**
- Navigate to `/codesnoutr/settings`
- Go to "AI Integration" tab
- Enter your API key and save

#### 4. Verify Everything Works

```bash
# Check queue is working
php artisan queue:status

# Test cache (should return "working")
php artisan tinker
>>> Cache::put('test', 'working', 60);
>>> Cache::get('test');
```

### Using AI Auto-Fix

#### Single Issue Fix

1. Navigate to scan results
2. Click on an issue to expand details
3. Click "AI Auto-Fix" button
4. Review the proposed fix in the preview
5. Click "Apply Fix" to implement

#### Fix All Issues

1. Navigate to scan results
2. Click "Fix All with AI" button
3. Confirm you want to proceed
4. Monitor progress in real-time:
   - Progress bar shows completion
   - Live updates every second
   - Success/failure count
   - Estimated time remaining

5. Review summary when complete:
   - Fixed issues count
   - Failed issues count
   - Total cost (AI tokens used)
   - Link to detailed results

### Troubleshooting AI Auto-Fix

#### Common Issues

**"Queue Worker Not Running" Error:**
```bash
# Start queue worker in separate terminal
php artisan queue:work --verbose --timeout=300
```

**Progress Bar Not Updating:**
- Make sure `CACHE_DRIVER=file` (not `array`)
- Test cache: `Cache::put('test', 1); Cache::get('test');`
- Check browser console (F12) for errors

**OpenAI Errors:**
- **Rate Limit**: Wait and retry, or reduce concurrent fixes
- **Invalid Key**: Check `.env` has correct key starting with `sk-`
- **No Credits**: Add funds at [platform.openai.com/account/billing](https://platform.openai.com/account/billing)

**Cost Management:**
```env
# Use cheaper model for simple fixes
CODESNOUTR_AI_MODEL=gpt-3.5-turbo

# Reduce token usage
CODESNOUTR_AI_MAX_TOKENS=2000
```

### Best Practices

1. ✅ **Test Small First**: Try a few issues before Fix All
2. ✅ **Review Fixes**: Always review AI changes before committing
3. ✅ **Use Git**: Commit your code before running auto-fix
4. ✅ **Monitor Costs**: Track OpenAI usage and set budget alerts
5. ✅ **Backup Code**: Always have backups before automated fixes
6. ⚠️ **Code Privacy**: AI sends your code to OpenAI servers
7. 🔒 **Protect Keys**: Never commit API keys to version control

## 📈 Current Status

### ✅ Production Ready (v1.0.0)

**Core Package** - 100% Complete
- [x] Complete package structure with service provider
- [x] Database migrations and Eloquent models
- [x] Core ScanManager with full scanning capabilities
- [x] All four rule engines fully implemented
- [x] Comprehensive Artisan commands
- [x] Professional configuration system

**Web Interface** - 100% Complete  
- [x] Modern Livewire components (Dashboard, Scanner, Results, Settings)
- [x] Complete dark/light mode system with user preferences
- [x] Responsive design with Tailwind CSS and modern hover effects
- [x] Interactive features with Alpine.js and smooth animations
- [x] Advanced filtering, sorting, and bulk operations
- [x] Enhanced file grouping with expandable issue details
- [x] Export functionality (JSON, CSV)
- [x] Real-time queue status monitoring and management
- [x] Automatic redirects and workflow optimization

**Developer Experience** - 100% Complete
- [x] Comprehensive documentation and setup guides
- [x] Professional code quality with testing framework
- [x] Flexible CLI interface with multiple output formats
- [x] Extensive configuration options
- [x] Professional error handling and validation
- [x] Context-aware static analysis with inheritance support
- [x] Enhanced queue management with auto-detection

### � Ready for Implementation

**AI Integration** - Infrastructure Complete
- [x] OpenAI configuration framework
- [x] Cost tracking and safety systems
- [x] UI components for AI settings
- [ ] OpenAI API client implementation
- [ ] Auto-fix generation algorithms

**Advanced Features** - Framework Ready
- [x] Export system foundation
- [x] Analytics data structure
- [ ] Debugbar integration (custom collector)
- [ ] PDF report generation
- [ ] Scheduled scanning capabilities

### 📊 Package Statistics
- **Total Files**: 50+ source files
- **Lines of Code**: 8,000+ lines of professional PHP
- **Test Coverage**: Unit test framework established
- **Documentation**: Complete with examples and guides
- **Code Quality**: PSR-4, PHPStan, Laravel Pint configured
- **UI Components**: Modern Livewire components with enhanced UX
- **Recent Updates**: Enhanced queue management, expandable details, modern hover effects

## �️ Roadmap

### OpenAI API Client Implementation

For detailed information about our AI integration plans, see the **[OpenAI Client Roadmap](OPENAI_CLIENT_ROADMAP.md)**.

**Quick Overview:**

📅 **v1.1.0 (Q4 2025) - Stability Release**
- Enhanced error handling with retry logic
- Improved JSON parsing for AI responses
- Better validation and testing
- Configuration wizard

📅 **v1.2.0 (Q1 2026) - Performance Release**
- Intelligent caching (30-50% cost savings)
- Prompt optimization (20-30% token reduction)
- Smart model selection
- Cost reduction features

📅 **v1.3.0 (Q2 2026) - Advanced Features**
- OpenAI Function calling integration
- Streaming responses for real-time feedback
- Enhanced progress tracking
- Vision API for diagram analysis

📅 **v2.0.0 (Q3 2026) - Intelligence Release**
- Feedback loop system
- Pattern recognition and learning
- Custom rule suggestions
- Fine-tuning support

📅 **v2.1.0 (Q4 2026) - Expansion Release**
- Multi-file refactoring
- Automated test generation
- Security vulnerability patching
- PR/commit automation

📅 **v3.0.0 (2027) - Multi-Provider Release**
- Support for Claude, Gemini, and local models
- Provider abstraction layer
- Hybrid optimization
- Enterprise features

**Current Focus:**
- ✅ Core AI features implemented (v1.0.0)
- 🚧 Stability improvements in progress
- 📋 Performance optimization planned

See [OPENAI_CLIENT_ROADMAP.md](OPENAI_CLIENT_ROADMAP.md) for complete details including:
- Detailed feature breakdown per phase
- Success metrics and KPIs
- Technical debt tracking
- Contribution opportunities

## �📁 Package Structure

```
src/
├── CodeSnoutrServiceProvider.php    # Main service provider
├── ScanManager.php                  # Core scanning orchestrator
├── Commands/                        # Artisan commands
│   ├── InstallCommand.php           # Package installation
│   └── ScanCommand.php              # CLI scanning interface
├── Http/
│   ├── Controllers/
│   │   └── DashboardController.php  # Web interface controller
│   └── Livewire/                    # Interactive components
│       ├── Dashboard.php            # Main dashboard with statistics
│       ├── ScanForm.php             # Interactive scanning interface
│       ├── ScanResults.php          # Advanced results management
│       ├── Settings.php             # Configuration management
│       └── DarkModeToggle.php       # Theme switching
├── Models/                          # Eloquent models
│   ├── Scan.php                     # Scan records and relationships
│   ├── Issue.php                    # Detected issues with metadata
│   └── Setting.php                  # Configuration storage
└── Scanners/                        # Core scanning engines
    ├── AbstractScanner.php          # Base scanner class
    ├── FileScanHandler.php          # Single file scanning
    ├── DirectoryScanHandler.php     # Directory scanning
    ├── CodebaseScanHandler.php      # Full codebase scanning
    └── Rules/                       # Analysis rule engines
        ├── AbstractRuleEngine.php   # Base rule engine
        ├── SecurityRules.php        # Security vulnerability detection
        ├── PerformanceRules.php     # Performance optimization
        ├── QualityRules.php         # Code quality analysis
        └── LaravelRules.php         # Laravel best practices

config/
└── codesnoutr.php                   # Comprehensive configuration

database/migrations/                 # Database schema
├── create_codesnoutr_scans_table.php
├── create_codesnoutr_issues_table.php
└── create_codesnoutr_settings_table.php

resources/                           # UI assets and views
├── views/
│   ├── layouts/app.blade.php        # Main layout with navigation
│   ├── pages/                       # Page templates
│   └── livewire/                    # Component views
├── css/codesnoutr.css              # Custom styles with dark mode
└── js/codesnoutr.js                # Interactive features

routes/web.php                       # Web and API routes
tests/                              # Testing framework
```

## 🤝 Contributing

We welcome contributions! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

### Development Setup

1. **Clone the repository**
```bash
git clone https://github.com/rafaelogic/codesnoutr.git
cd codesnoutr
```

2. **Install dependencies**
```bash
composer install
```

3. **Set up a test Laravel application**
```bash
composer create-project laravel/laravel test-app
cd test-app
```

4. **Link the package for development**
```bash
# In composer.json, add:
"repositories": [
    {
        "type": "path",
        "url": "../codesnoutr"
    }
]

# Then require the package
composer require rafaelogic/codesnoutr:dev-main
```

5. **Run tests and quality checks**
```bash
composer test              # Run PHPUnit tests
composer format            # Format code with Laravel Pint
composer analyse           # Static analysis with PHPStan
composer all-checks        # Run all quality checks
```

### Development Tools

The package includes professional development tools:
- **Laravel Pint**: Code formatting and style enforcement
- **PHPStan**: Static analysis for type safety and bugs
- **PHPUnit**: Unit and feature testing framework
- **GitHub Actions**: Automated CI/CD pipeline

## 🔒 Security

If you discover any security-related issues, please email security@rafaelogic.com instead of using the issue tracker. All security vulnerabilities will be promptly addressed.

### Security Features

The package itself includes:
- **Input Validation**: Comprehensive path and parameter validation
- **Output Sanitization**: Safe rendering of code snippets and user data
- **Access Control**: Configurable access restrictions
- **Data Protection**: Secure storage of scan results and configurations

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## 👥 Credits

- [Rafa Rafael](https://github.com/rafaelogic) - Creator and maintainer
- [All Contributors](../../contributors) - Community contributions

## 📝 Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

---

## 🚀 Roadmap

### Phase 2: AI Integration (Q4 2025)
- **OpenAI API Client**: Complete implementation with rate limiting
- **Auto-Fix Generation**: Context-aware code improvements  
- **Safety Systems**: Preview and approval mechanisms
- **Cost Management**: Usage tracking and budget controls

### Phase 3: Advanced Features (Q1 2026)
- **Debugbar Integration**: Custom collector with quick access
- **PDF Reports**: Professional report generation
- **Scheduled Scanning**: Automated periodic scans
- **Advanced Analytics**: Trend analysis and historical comparisons

### Phase 4: Enterprise Features (Q2 2026)
- **Team Collaboration**: Multi-user support and shared configurations
- **Public API**: RESTful API for third-party integrations
- **Custom Rule Builder**: Visual interface for creating custom rules
- **Advanced Integrations**: CI/CD pipeline integration tools

---

## 🤝 Contributing

We welcome contributions! CodeSnoutr is open source and available on GitHub.

### 📍 Repository
- **GitHub**: [https://github.com/rafaelogic/codesnoutr](https://github.com/rafaelogic/codesnoutr)
- **Issues**: [Report bugs or request features](https://github.com/rafaelogic/codesnoutr/issues)
- **Discussions**: [Join the community](https://github.com/rafaelogic/codesnoutr/discussions)

### 🛠️ Development Setup

1. **Clone the repository**:
```bash
git clone https://github.com/rafaelogic/codesnoutr.git
cd codesnoutr
```

2. **Install dependencies**:
```bash
composer install
```

3. **Run the test suite**:
```bash
composer test
```

4. **Format code**:
```bash
composer format
```

5. **Run static analysis**:
```bash
composer analyse
```

### 📝 Contributing Guidelines

- **Fork** the repository and create your branch from `main`
- **Write tests** for any new functionality
- **Follow PSR-12** coding standards
- **Update documentation** for any API changes
- **Create a pull request** with a clear description

### 🐛 Bug Reports

When reporting bugs, please include:
- Laravel version
- PHP version  
- CodeSnoutr version
- Steps to reproduce
- Expected vs actual behavior

### 💡 Feature Requests

We're always interested in new ideas! Please check existing issues first, then create a new issue with:
- Use case description
- Proposed implementation approach
- Any relevant examples or mockups

---

## 📄 License

CodeSnoutr is open-sourced software licensed under the [MIT license](LICENSE.md).

---

## 🏆 Why Choose CodeSnoutr?

### ✅ **Production Ready**
- Complete implementation with professional code quality
- Comprehensive testing and validation
- Extensive documentation and examples
- Ready for enterprise deployment

### ⚡ **Performance Optimized**
- Efficient scanning algorithms with chunking for large codebases
- Indexed database queries for fast result retrieval
- Memory-optimized processing with configurable limits
- Lazy loading and pagination for responsive UI

### 🎨 **Modern Experience**
- Beautiful, responsive interface that works on all devices
- Intuitive workflow from scanning to issue resolution
- Dark/light mode with user preference persistence
- Real-time updates and interactive components

### 🔧 **Developer Friendly**
- Flexible CLI interface with multiple output formats
- Comprehensive configuration options
- Extensible architecture for custom rules
- Professional documentation and examples

---

**Built with ❤️ for the Laravel community**

*CodeSnoutr v1.0.0 - Production ready as of August 2025*

---

## ❓ Frequently Asked Questions

### General Questions

**Q: Is CodeSnoutr ready for production use?**
A: Yes! CodeSnoutr v1.0.0 is production-ready with comprehensive testing, professional code quality, and full Laravel compatibility.

**Q: What Laravel versions are supported?**
A: CodeSnoutr supports Laravel 10.0, 11.0, and 12.0 with PHP 8.1+.

**Q: Does CodeSnoutr slow down my application?**
A: No. CodeSnoutr runs scans in background queues and doesn't affect your application's performance. The web interface is lightweight and optimized.

### Installation & Setup

**Q: Do I need to set up queues?**
A: While optional, queues are highly recommended for better performance. CodeSnoutr can auto-manage queue workers or you can set up dedicated workers.

**Q: Can I customize which files are scanned?**
A: Yes! Configure scan paths, exclusions, and file types in `config/codesnoutr.php` or through the web interface.

**Q: How do I update CodeSnoutr?**
A: Run `composer update rafaelogic/codesnoutr` and republish assets with `php artisan vendor:publish --tag=codesnoutr-assets --force`.

### Features & Functionality

**Q: What types of issues can CodeSnoutr detect?**
A: CodeSnoutr detects 50+ types of issues including security vulnerabilities, performance problems, code quality issues, and Laravel best practice violations.

**Q: Can I export scan results?**
A: Yes! Export results as JSON, CSV, or store them in your database. Use the web interface or CLI commands.

**Q: Is AI integration required?**
A: No. AI features are optional and require an OpenAI API key. All core functionality works without AI integration.

### Technical Questions

**Q: Can I create custom scanning rules?**
A: Yes! Extend the `AbstractRuleEngine` class to create custom rules. See the source code examples in `src/Scanners/Rules/`.

**Q: Does CodeSnoutr work with Docker?**
A: Yes! CodeSnoutr is fully compatible with Docker. See the installation guide for Docker-specific instructions.

**Q: Can I integrate CodeSnoutr with CI/CD?**
A: Absolutely! Use the CLI commands in your CI/CD pipeline to automatically scan code and fail builds on critical issues.

### Troubleshooting

**Q: Scans are not starting or taking too long**
A: Check your queue configuration. Ensure queue workers are running or enable auto-start in the configuration.

**Q: Getting permission errors**
A: Make sure your web server has read permissions on your codebase and write permissions on storage directories.

**Q: Web interface not loading properly**
A: Clear Laravel caches (`php artisan cache:clear`) and ensure assets are published (`php artisan vendor:publish --tag=codesnoutr-assets --force`).

**Q: UI appears broken with no CSS styling**
A: This means the assets weren't published correctly. Run:
```bash
php artisan codesnoutr:install --force
# or
php artisan vendor:publish --provider="Rafaelogic\CodeSnoutr\CodeSnoutrServiceProvider" --tag="codesnoutr-assets" --force
```
Verify assets exist at `public/vendor/codesnoutr/build/`. See [CSS_TROUBLESHOOTING.md](./CSS_TROUBLESHOOTING.md) for detailed help.

---

## 🆘 Support & Community

### Getting Help

- 📖 **Documentation**: Comprehensive guides in this README
- 🐛 **Bug Reports**: [GitHub Issues](https://github.com/rafaelogic/codesnoutr/issues)
- 💬 **Discussions**: [GitHub Discussions](https://github.com/rafaelogic/codesnoutr/discussions)
- 📧 **Security Issues**: security@rafaelogic.com

### Community

- ⭐ **Star the Project**: [GitHub Repository](https://github.com/rafaelogic/codesnoutr)
- 🐦 **Follow Updates**: [@rafaelogic](https://twitter.com/rafaelogic)
- 💼 **Professional Support**: Available for enterprise customers

### Feature Requests

We're always looking to improve! Submit feature requests through:
- GitHub Issues with the "enhancement" label
- GitHub Discussions in the "Ideas" category
- Direct email for enterprise features

---
