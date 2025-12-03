<?php

declare(strict_types=1);

/*
 * This file is part of the "Doctrine extension to manage enumerations in PostgreSQL" package.
 * (c) Alexey Sitka <alexey.sitka@gmail.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Enumeum\DoctrineEnumBundle\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\DBAL\Event\ConnectionEventArgs;
use Doctrine\DBAL\Events;
use Enumeum\DoctrineEnum\Definition\DefinitionRegistry;

/**
 * Event subscriber that registers enum types with Doctrine's type mapping system.
 * This ensures that when Doctrine introspects the database schema (e.g., during schema:drop),
 * it recognizes custom PostgreSQL enum types and doesn't throw "Unknown database type" errors.
 */
class RegisterEnumTypeMappingSubscriber implements EventSubscriber
{
    public function __construct(
        private readonly DefinitionRegistry $definitionRegistry,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents(): array
    {
        return [
            Events::postConnect,
        ];
    }

    /**
     * Registers all enum types as Doctrine type mappings when a database connection is established.
     */
    public function postConnect(ConnectionEventArgs $args): void
    {
        $connection = $args->getConnection();
        $platform = $connection->getDatabasePlatform();

        // Register each enum type name as a string type mapping
        // This allows Doctrine to recognize the enum types during schema introspection
        foreach ($this->definitionRegistry->getAll() as $definition) {
            $typeName = $definition->getName();
            
            // Only register if not already registered
            if (!$platform->hasDoctrineTypeMappingFor($typeName)) {
                $platform->registerDoctrineTypeMapping($typeName, 'string');
            }
        }
    }
}
