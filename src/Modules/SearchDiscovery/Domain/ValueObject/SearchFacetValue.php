<?php

namespace Appart\Modules\SearchDiscovery\Domain\ValueObject;

use Appart\Modules\SearchDiscovery\Domain\Exception\InvalidSearchValue;

final readonly class SearchFacetValue
{
    private function __construct(public string $value) {}

    public static function fromString(string $value): self
    {
        $value = trim((string) preg_replace('/\s+/u', ' ', $value));
        if ($value === '' || mb_strlen($value) > 200) {
            throw InvalidSearchValue::field('facet_value');
        }

        return new self($value);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
