# Contributing to CodeSnoutr

Thank you for your interest in contributing to CodeSnoutr! We welcome contributions from the community and are pleased to have you join us.

## Code of Conduct

This project adheres to a [Code of Conduct](CODE_OF_CONDUCT.md). By participating, you are expected to uphold this code.

## How to Contribute

### Reporting Issues

Before creating an issue, please:

1. **Search existing issues** to avoid duplicates
2. **Use the issue template** provided
3. **Include relevant details** like:
   - Laravel version
   - PHP version
   - CodeSnoutr version
   - Steps to reproduce
   - Expected vs actual behavior

### Suggesting Features

We love feature suggestions! Please:

1. **Check the roadmap** first - [ROADMAP.md](ROADMAP.md)
2. **Open a discussion** before creating a pull request for major features
3. **Provide clear use cases** and benefits
4. **Consider backwards compatibility**

### Pull Requests

1. **Fork the repository**
2. **Create a feature branch** from `main`
3. **Follow our coding standards** (see below)
4. **Add tests** for new functionality
5. **Update documentation** as needed
6. **Create a pull request** with a clear description

#### Pull Request Process

1. Update the README.md with details of changes if applicable
2. Update the CHANGELOG.md following [Keep a Changelog](https://keepachangelog.com/) format
3. Increase version numbers in composer.json following [Semantic Versioning](https://semver.org/)
4. Your PR will be merged once you have the sign-off of at least one maintainer

## Development Setup

### Prerequisites

- PHP 8.1 or higher
- Composer
- Laravel 10+ test application
- Git

### Setup Steps

1. **Clone your fork**:
   ```bash
   git clone https://github.com/rafaelogic/codesnoutr.git
   cd codesnoutr
   ```

2. **Install dependencies**:
   ```bash
   composer install
   ```

3. **Set up test Laravel application**:
   ```bash
   # Create a test Laravel app
   composer create-project laravel/laravel ../test-app
   cd ../test-app
   
   # Require your local package
   composer config repositories.codesnoutr path ../codesnoutr
   composer require rafaelogic/codesnoutr:@dev
   ```

4. **Set up the package**:
   ```bash
   php artisan codesnoutr:install
   ```

## Coding Standards

### PHP Standards

- Follow [PSR-12](https://www.php-fig.org/psr/psr-12/) coding standards
- Use type hints for all method parameters and return types
- Write descriptive variable and method names
- Add PHPDoc blocks for all public methods and properties

### Laravel Conventions

- Follow Laravel naming conventions
- Use Eloquent relationships properly
- Follow Laravel directory structure
- Use Laravel's built-in features when possible

### Code Style

We use PHP CS Fixer for code formatting. Run it before committing:

```bash
composer format
```

### Testing

- Write tests for all new features
- Maintain or improve test coverage
- Follow AAA pattern (Arrange, Act, Assert)
- Use descriptive test method names

Run tests:
```bash
composer test
```

## Testing Guidelines

### Test Types

1. **Unit Tests**: Test individual classes and methods
2. **Feature Tests**: Test complete features end-to-end
3. **Browser Tests**: Test UI interactions (when applicable)

### Test Structure

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use RafaelOgic\CodeSnoutr\ScanManager;

class ScanManagerTest extends TestCase
{
    /** @test */
    public function it_can_scan_a_single_file()
    {
        // Arrange
        $manager = app(ScanManager::class);
        $testFile = __DIR__ . '/fixtures/test-file.php';
        
        // Act
        $result = $manager->scanFile($testFile);
        
        // Assert
        $this->assertInstanceOf(ScanResult::class, $result);
        $this->assertGreaterThan(0, $result->getIssues()->count());
    }
}
```

### Test Fixtures

Create test fixtures in `tests/fixtures/` for consistent testing:

- Sample PHP files with known issues
- Configuration files
- Expected output examples

## Documentation

### Code Documentation

- All public methods must have PHPDoc blocks
- Include `@param`, `@return`, and `@throws` tags
- Provide usage examples for complex methods
- Document any side effects or important behavior

### User Documentation

- Update README.md for new features
- Add examples to the documentation
- Update FEATURES.md for feature changes
- Include configuration options

## Database Changes

### Migrations

- Create reversible migrations
- Use descriptive migration names
- Add foreign key constraints where appropriate
- Consider performance implications

### Model Changes

- Update model relationships
- Add appropriate fillable/guarded properties
- Include model factories for testing
- Update PHPDoc blocks for IDE support

## Frontend Development

### Livewire Components

- Follow Livewire naming conventions
- Keep components focused and single-purpose
- Use proper lifecycle hooks
- Include loading states

### CSS/JavaScript

- Follow existing naming conventions
- Ensure dark mode compatibility
- Test responsive design
- Maintain accessibility standards

## Release Process

### Version Numbering

We follow [Semantic Versioning](https://semver.org/):

- **MAJOR**: Breaking changes
- **MINOR**: New features (backwards compatible)
- **PATCH**: Bug fixes (backwards compatible)

### Changelog

Update CHANGELOG.md with:
- New features
- Bug fixes
- Breaking changes
- Deprecations

## Security

### Reporting Security Issues

**Do not report security vulnerabilities through public GitHub issues.**

Please email 40rrafael@gmail.com with:
- Description of the vulnerability
- Steps to reproduce
- Potential impact
- Suggested fix (if any)

### Security Best Practices

- Validate all user inputs
- Use parameterized queries
- Escape output properly
- Follow OWASP guidelines
- Test for common vulnerabilities

## Performance Considerations

### Guidelines

- Profile code changes for performance impact
- Optimize database queries
- Consider memory usage for large files
- Use caching where appropriate
- Test with large codebases

### Benchmarking

Run performance tests:
```bash
composer benchmark
```

## Contributing to AI Features

### OpenAI Client Implementation

We have a detailed roadmap for AI integration improvements. See [ROADMAP.md](ROADMAP.md) for:

- Current implementation status
- Planned phases and features
- Technical debt and known issues
- Success metrics and KPIs
- High-priority contribution areas

**High Priority AI Contributions:**
1. **JSON Parsing** - Fix docblock escaping and namespace backslash issues
2. **Prompt Engineering** - Optimize prompts to reduce token usage
3. **Caching System** - Implement intelligent caching for similar fixes
4. **Error Handling** - Add retry logic and rate limit handling
5. **Testing** - Expand test coverage for AI features

**AI Feature Guidelines:**
- All AI features must include safety checks (validation, backups, preview)
- Test with multiple OpenAI models (GPT-4, GPT-3.5-turbo)
- Include cost estimates in PRs that affect token usage
- Document prompt changes with before/after examples
- Add rollback support for any destructive changes

### AI Testing

When contributing AI features:

```bash
# Test with different models
CODESNOUTR_AI_MODEL=gpt-4 composer test
CODESNOUTR_AI_MODEL=gpt-3.5-turbo composer test

# Test cost tracking
php artisan codesnoutr:test-ai-cost

# Validate safety checks
php artisan codesnoutr:test-ai-safety
```

## Getting Help

### Communication Channels

- **GitHub Discussions**: General questions and ideas
- **GitHub Issues**: Bug reports and feature requests
- **Email**: security@example.com for security issues

### Documentation

- [README.md](README.md): Getting started guide
- [ROADMAP.md](ROADMAP.md): AI integration roadmap
- [API Documentation]: Detailed API reference (coming soon)

## Recognition

Contributors will be:
- Added to the contributors list in README.md
- Mentioned in release notes for significant contributions
- Invited to the maintainers team for substantial ongoing contributions

## License

By contributing to CodeSnoutr, you agree that your contributions will be licensed under the MIT License.

---

Thank you for contributing to CodeSnoutr! 🎉
