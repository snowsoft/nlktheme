<?php

namespace Nlk\Theme\FlexPage;

use Nlk\Theme\FlexPage\Contracts\Section;
use InvalidArgumentException;

/**
 * Registry for all registered section types.
 * Singleton — bound via ThemeServiceProvider.
 */
class SectionRegistry
{
    /** @var array<string, class-string<Section>> */
    private array $sections = [];

    /**
     * Register a section type.
     *
     * @param  string  $type       e.g. 'hero'
     * @param  class-string<Section>  $class
     */
    public function register(string $type, string $class): void
    {
        if (!is_a($class, Section::class, true)) {
            throw new InvalidArgumentException(
                "Class [{$class}] must implement " . Section::class
            );
        }

        $this->sections[$type] = $class;
    }

    /**
     * Resolve a section instance by type.
     *
     * @throws InvalidArgumentException
     */
    public function resolve(string $type): Section
    {
        if (!isset($this->sections[$type])) {
            throw new InvalidArgumentException(
                "Section type [{$type}] is not registered."
            );
        }

        return app($this->sections[$type]);
    }

    /**
     * @return array<string, class-string<Section>>
     */
    public function all(): array
    {
        return $this->sections;
    }

    public function has(string $type): bool
    {
        return isset($this->sections[$type]);
    }
}
