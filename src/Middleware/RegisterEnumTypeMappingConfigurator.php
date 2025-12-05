<?php

declare(strict_types=1);

/*
 * This file is part of the "Doctrine extension to manage enumerations in PostgreSQL" package.
 * (c) Alexey Sitka <alexey.sitka@gmail.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Enumeum\DoctrineEnumBundle\Middleware;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Enumeum\DoctrineEnum\Definition\DefinitionRegistry;
use Enumeum\DoctrineEnum\Type\EnumeumType;

/**
 * Connection configurator that registers enum types with Doctrine's type registry and mapping system.
 * This ensures that when Doctrine introspects the database schema (e.g., during schema:drop),
 * it recognizes custom PostgreSQL enum types and doesn't throw "Unknown database type" errors.
 *
 * In DBAL 4.0, this is invoked as a service configurator after the connection is created
 * to register enum types in the type registry and their mappings on the platform.
 */
final class RegisterEnumTypeMappingConfigurator
{
    public function __construct(
        private readonly DefinitionRegistry $definitionRegistry,
    ) {
    }

    /**
     * Configure the DBAL connection by registering enum type mappings.
     *
     * @param Connection $connection The DBAL connection to configure
     */
    public function __invoke(Connection $connection): void
    {
        $platform = $connection->getDatabasePlatform();
        $typeRegistry = Type::getTypeRegistry();

        // Register each enum type in the Doctrine type registry
        // This allows Doctrine to recognize the enum types during schema introspection
        foreach ($this->definitionRegistry->getDefinitions() as $definition) {
            $typeName = $definition->name;

            // Only register if not already registered
            if (!$platform->hasDoctrineTypeMappingFor($typeName)) {
                if (!$typeRegistry->has($typeName)) {
                    $typeRegistry->register($typeName, EnumeumType::create($typeName));
                }
                $platform->registerDoctrineTypeMapping($typeName, $typeName);
            }
        }
    }
}
