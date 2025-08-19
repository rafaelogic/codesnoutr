# CodeSnoutr Test Application

This is a minimal Laravel application used for testing the CodeSnoutr package during development.

## Setup Instructions

To set up this test application:

1. **Install PHP dependencies:**
   ```bash
   composer install
   ```

2. **Install Node.js dependencies:**
   ```bash
   npm install
   ```

3. **Set up environment:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Run the application:**
   ```bash
   php artisan serve
   ```

## Purpose

This test application is used for:
- Testing CodeSnoutr package integration
- Validating scanning functionality
- Testing Livewire components
- Ensuring cross-Laravel version compatibility

## Note

The `vendor/` and `node_modules/` directories are excluded from the repository to reduce package size. They will be automatically created when you run the setup commands above.
