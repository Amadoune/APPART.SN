<?php

namespace Appart\Modules\SearchDiscovery\Domain\ValueObject;

use Appart\Modules\SearchDiscovery\Domain\Exception\InvalidSearchValue;

final readonly class SearchFacetKey
{
    private function __construct(public string $value) {}

    public static function fromString(string $value): self
    {
        $value = strtolower(trim($value));
        if (preg_match('/^[a-z][a-z0-9_.-]{0,63}$/', $value) !== 1) {
            throw InvalidSearchValue::field('facet_key');
        }

        return new self($value);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
