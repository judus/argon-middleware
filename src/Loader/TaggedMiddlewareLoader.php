<?php

declare(strict_types=1);

namespace Maduser\Argon\Middleware\Loader;

use Maduser\Argon\Middleware\Contracts\MiddlewareLoaderInterface;
use Maduser\Argon\Middleware\Exception\MiddlewareException;
use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Middleware\MiddlewareDefinition;

final readonly class TaggedMiddlewareLoader implements MiddlewareLoaderInterface
{
    public function __construct(
        private ArgonContainer $container,
        private ?string $tag = null,
    ) {
    }

    /**
     * @return list<MiddlewareDefinition>
     */
    public function load(): array
    {
        if ($this->tag === null) {
            throw new MiddlewareException('No tag provided for loading middleware.');
        }

        $tagged = $this->container->getTaggedMeta($this->tag);

        $definitions = [];
        foreach ($tagged as $class => $meta) {
            $priority = isset($meta['priority']) ? (int) $meta['priority'] : 0;
            $definitions[] = new MiddlewareDefinition($class, $priority);
        }

        return $definitions;
    }

    /**
     * @return array<string, list<MiddlewareDefinition>>
     */
    public function loadGrouped(): array
    {
        if ($this->tag === null) {
            throw new MiddlewareException('No tag provided for loading middleware.');
        }

        $tagged = $this->container->getTaggedMeta($this->tag);
        $groups = [];

        foreach ($tagged as $class => $meta) {
            $priority = isset($meta['priority']) ? (int) $meta['priority'] : 0;
            $groupMeta = $meta['group'] ?? MiddlewareDefinition::DEFAULT_GROUP;
            $groupNames = is_array($groupMeta) ? $groupMeta : [$groupMeta];

            if ($groupNames === []) {
                $groupNames = [MiddlewareDefinition::DEFAULT_GROUP];
            }

            $definition = new MiddlewareDefinition($class, $priority);

            foreach ($groupNames as $name) {
                $group = $name !== null && $name !== ''
                    ? (string) $name
                    : MiddlewareDefinition::DEFAULT_GROUP;

                $groups[$group][] = $definition;
            }
        }

        return $groups;
    }
}
