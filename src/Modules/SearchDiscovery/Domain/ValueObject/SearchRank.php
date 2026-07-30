<?php

namespace Appart\Modules\SearchDiscovery\Domain\ValueObject;

use Appart\Modules\SearchDiscovery\Domain\Exception\InvalidSearchValue;

final readonly class SearchRank
{
    private function __construct(public int $value) {}

    public static function fromInt(int $value): self
    {
        if ($value < 0 || $value > 10000) {
            throw InvalidSearchValue::field('rank');
        }

        return new self($value);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
