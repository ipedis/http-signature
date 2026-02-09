# Testing and Quality Assurance

This repository is configured with comprehensive testing and code quality tools.

## Tools Configured

### PHPUnit (Unit Testing)
- Version: ^11.5
- Configuration: `phpunit.xml`
- Tests are located in `tests/` directory
- Run tests: `composer test`
- Run with coverage: `composer test:coverage`

### PHPStan (Static Analysis)
- Version: ^2.1
- Configuration: `phpstan.neon`
- Level: 9 (strictest)
- Run: `composer phpstan`

### Laravel Pint (Code Style)
- Version: ^1.19
- Configuration: `pint.json`
- Preset: Laravel
- Check style: `composer pint:check`
- Fix style: `composer pint`

### Rector (Code Refactoring)
- Version: ^2.3
- Configuration: `rector.php`
- Check: `composer rector:check`
- Fix: `composer rector`

## Running All Quality Checks

Run all quality checks at once:
```bash
composer qa
```

This will run:
1. Pint (code style check)
2. PHPStan (static analysis)
3. Rector (refactoring check)
4. PHPUnit (tests)

## Continuous Integration

The repository is configured with GitHub Actions to automatically run all quality checks on:
- Pull requests
- Pushes to the main branch

The CI workflow runs:
- PHPUnit tests on PHP 8.2, 8.3, and 8.4
- Code quality checks (Pint, PHPStan, Rector)

See `.github/workflows/ci.yml` for the full configuration.

## Test Coverage

Test coverage reports are generated in the `coverage/` directory when running:
```bash
composer test:coverage
```

The coverage directory is excluded from version control via `.gitignore`.
