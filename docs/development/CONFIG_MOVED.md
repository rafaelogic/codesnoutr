# Configuration Files Location

The build and quality configuration files have been moved to improve project organization:

## Build Configuration
- `tailwind.config.js` → `config/build/tailwind.config.js`
- `vite.config.js` → `config/build/vite.config.js`
- `postcss.config.js` → `config/build/postcss.config.js`

## Quality Assurance Configuration  
- `phpstan.neon` → `config/quality/phpstan.neon`
- `pint.json` → `config/quality/pint.json`
- `phpunit.xml` → `config/quality/phpunit.xml`

## Usage

### Running Quality Tools
```bash
# PHPStan
./vendor/bin/phpstan analyse --configuration=config/quality/phpstan.neon

# Laravel Pint
./vendor/bin/pint --config=config/quality/pint.json

# PHPUnit
./vendor/bin/phpunit --configuration=config/quality/phpunit.xml
```

### Build Commands
Build tools will automatically look for configuration files in the `config/build/` directory.

If you need to reference the old locations, create symlinks:
```bash
ln -s config/build/tailwind.config.js tailwind.config.js
ln -s config/build/vite.config.js vite.config.js
ln -s config/build/postcss.config.js postcss.config.js
```