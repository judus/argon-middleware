<?php

declare(strict_types=1);

namespace Tests\Integration\Fixtures;

final class LogCollector
{
    /** @var list<string> */
    private array $entries = [];

    public function record(string $entry): void
    {
        $this->entries[] = $entry;
    }

    /**
     * @return list<string>
     */
    public function entries(): array
    {
        return $this->entries;
    }
}
