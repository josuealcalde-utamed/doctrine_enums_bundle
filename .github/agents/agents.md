# Copilot Agent Instructions for Doctrine Enums Bundle

## Project Overview

This is a Symfony bundle that provides integration with the Doctrine Enums extension for PostgreSQL. The bundle allows developers to use PHP 8.1+ enums as database types in Doctrine ORM entities, with support for migrations, schema validation, and commands decoration.

## Technology Stack

- **Language**: PHP 8.1+
- **Framework**: Symfony (4.4, 5.4, 6.0, 7.0)
- **ORM**: Doctrine ORM (2.14+ or 3.2+)
- **Database**: PostgreSQL with enum type support
- **Testing**: PHPUnit (8.5 or 9.5)
- **Code Style**: PHP CS Fixer with Symfony standards
- **Dependency Management**: Composer
- **Development Environment**: Docker (PHP + PostgreSQL containers)

## Commands

### Testing
```bash
# Run all tests using Docker
make test

# Or directly with PHPUnit
docker-compose run --rm --no-deps php ./bin/phpunit -c tests/
```

### Linting and Code Style
```bash
# Run all linting (composer normalize, composer validate, PHP CS Fixer, and tests)
make lint

# Fix PHP code style using PHP CS Fixer
make lint-php
# Or directly:
docker-compose run --rm --no-deps php ./bin/php-cs-fixer fix --config=.php-cs-fixer.dist.php

# Or using composer script:
composer fix-cs

# Lint and normalize composer.json
make lint-composer
# Or directly:
docker-compose run --rm --no-deps php composer normalize
docker-compose run --rm --no-deps php composer validate
```

### Docker Environment
```bash
# Start Docker containers (PHP + PostgreSQL)
make start

# Access PHP container bash
make console
```

## Project Structure

```
src/
├── Command/                    # Symfony console commands (migrations, schema validation)
├── DependencyInjection/        # Symfony DI configuration and extension
├── EventSubscriber/            # Doctrine event subscribers for enum management
├── Exception/                  # Custom exception classes
└── Resources/                  # Bundle resources and configuration

tests/
└── Enumeum/                    # PHPUnit test suite

.php-cs-fixer.dist.php         # PHP CS Fixer configuration
composer.json                   # Composer dependencies and scripts
docker-compose.yml             # Docker services configuration
Makefile                       # Common development tasks
```

### Key Components

- **Commands**: `enumeum:migrations:diff`, `enumeum:schema:validate` - Custom commands for enum migration and validation
- **Commands Decoration**: Decorates Doctrine's commands to add `-E` option for running enum commands first
- **Definition Registry**: Manages enum type definitions and their registration with Doctrine
- **Event Subscribers**: Handles enum schema operations during Doctrine lifecycle events

## Code Style Guidelines

### PHP CS Fixer Rules
Follow Symfony coding standards with these specific rules:
- **Strict types**: Always use `declare(strict_types=1);`
- **Header comment**: Required on all PHP files (see `.php-cs-fixer.dist.php`)
- **Concatenation**: Use single space around concat operator: `'string' . $var . 'more'`
- **Imports**: Import classes, functions, and constants; sort alphabetically
- **Trailing commas**: Required in multiline arrays, arguments, parameters, and match expressions
- **Type hints**: Use modern type casting and strict parameter types

### Code Example
```php
<?php

declare(strict_types=1);

/*
 * This file is part of the "Doctrine extension to manage enumerations in PostgreSQL" package.
 * (c) Alexey Sitka <alexey.sitka@gmail.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Enumeum\DoctrineEnumBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ExampleCommand extends Command
{
    public function __construct(
        private readonly SomeService $service,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Implementation
        return Command::SUCCESS;
    }
}
```

### Testing Conventions
- Test files must end with `Test.php`
- Tests are located in `tests/Enumeum/` directory
- Follow PHPUnit best practices:
  - Use camelCase for test method names
  - Use `self::` for static method calls in test cases
  - Use strict assertions
  - Use dedicated assertions when available

## Git Workflow

- Work on feature branches
- Ensure all tests pass before committing
- Run linting (`make lint`) before pushing
- Keep commits focused and atomic
- Write clear commit messages

## Boundaries and Constraints

### DO NOT modify:
- `.docker/` - Docker configuration (unless explicitly requested)
- `LICENSE` - License file
- `.gitattributes`, `.gitignore` - Git configuration (unless adding new patterns)
- `docker-compose.yml` - Docker services (unless explicitly requested)

### DO NOT:
- Remove or skip existing tests
- Change Doctrine or Symfony core behaviors
- Modify enum handling logic without understanding PostgreSQL enum constraints
- Add dependencies without checking compatibility with supported PHP/Symfony versions
- Remove backward compatibility for supported PHP 8.1+ and Symfony 4.4+/5.4+/6.0+/7.0+

### ALWAYS:
- Run tests after making changes
- Use PHP CS Fixer to maintain code style
- Add or update tests when adding/modifying features
- Consider Docker environment for development (tests run in Docker)
- Validate composer.json with `composer validate` and normalize with `composer normalize`
- Use strict types: `declare(strict_types=1);`
- Add appropriate header comments to new PHP files

## Domain-Specific Knowledge

### Enum Handling in Doctrine
- PHP enums (backed by string or int) are mapped to PostgreSQL enum types
- The `#[EnumType(name: 'type_name')]` attribute marks PHP enums as database types
- Entities use `enumType: StatusType::class` in column definitions
- The bundle intercepts Doctrine schema operations to manage enum types
- Enum changes require migrations (cannot be altered in-place in PostgreSQL)

### Configuration
- Configuration file: `doctrine_enum.yaml` in Symfony config folder
- Supports single (default) or multiple (named) connections
- Requires both `types` (enum classes) and `paths` (enum directories) configuration

### Common Operations
- **Migration Diff**: Creates migration files for enum changes
- **Schema Validate**: Checks if database enums match code definitions
- **Commands Decoration**: Optional feature to integrate with Doctrine commands using `-E` flag
- **Ignore Existing**: `-U` option to ignore enums already in database

## Development Tips

1. **Use Docker**: All development commands should run through `docker-compose` or `make` targets
2. **Test First**: Run tests before and after changes to ensure nothing breaks
3. **Lint Often**: Use `make lint-php` frequently to catch style issues early
4. **Check Dependencies**: Be mindful of version constraints for PHP, Symfony, and Doctrine
5. **Read Tests**: Tests in `tests/Enumeum/` show usage patterns and expected behavior
6. **PostgreSQL Specifics**: Remember that PostgreSQL enum types have specific constraints (cannot be renamed easily, values cannot be removed)

## Resources

- Main dependency: [josuealcalde-utamed/doctrine-enums](https://github.com/josuealcalde-utamed/doctrine-enums)
- Symfony Console Component documentation
- Doctrine ORM and DBAL documentation
- PostgreSQL enum type documentation
