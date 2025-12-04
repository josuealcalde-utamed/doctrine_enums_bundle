<?php

declare(strict_types=1);

/*
 * This file is part of the "Doctrine extension to manage enumerations in PostgreSQL" package.
 * (c) Alexey Sitka <alexey.sitka@gmail.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Enumeum\DoctrineEnumBundle\Middleware;

use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\Middleware;
use Doctrine\DBAL\Driver\Middleware\AbstractDriverMiddleware;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\ServerVersionProvider;
use Enumeum\DoctrineEnum\Definition\DefinitionRegistry;

/**
 * Middleware that registers enum types with Doctrine's type mapping system.
 * This ensures that when Doctrine introspects the database schema (e.g., during schema:drop),
 * it recognizes custom PostgreSQL enum types and doesn't throw "Unknown database type" errors.
 */
final class RegisterEnumTypeMappingMiddleware implements Middleware
{
    public function __construct(
        private readonly DefinitionRegistry $definitionRegistry,
    ) {
    }

    public function wrap(Driver $driver): Driver
    {
        return new class ($driver, $this->definitionRegistry) extends AbstractDriverMiddleware {
            public function __construct(
                Driver $wrappedDriver,
                private readonly DefinitionRegistry $definitionRegistry,
            ) {
                parent::__construct($wrappedDriver);
            }

            public function getDatabasePlatform(ServerVersionProvider $versionProvider): AbstractPlatform
            {
                $platform = parent::getDatabasePlatform($versionProvider);

                // Register each enum type name as a string type mapping
                // This allows Doctrine to recognize the enum types during schema introspection
                foreach ($this->definitionRegistry->getDefinitions() as $definition) {
                    $typeName = $definition->name;

                    // Only register if not already registered
                    if (!$platform->hasDoctrineTypeMappingFor($typeName)) {
                        $platform->registerDoctrineTypeMapping($typeName, 'string');
                    }
                }

                return $platform;
            }
        };
    }
}
