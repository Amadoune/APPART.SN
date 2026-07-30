<?php

namespace Appart\Modules\Geography\Domain\ValueObject;

use Appart\Modules\Geography\Domain\Exception\InvalidPlaceName;

final readonly class PlaceName
{
    private function __construct(public string $value) {}

    public static function fromString(string $value): self
    {
        $normalized = preg_replace('/\s+/u', ' ', trim($value));

        if (! is_string($normalized) || mb_strlen($normalized) < 2 || mb_strlen($normalized) > 120) {
            throw InvalidPlaceName::fromValue($value);
        }

        return new self($normalized);
    }

    public function normalizationKey(): string
    {
        return mb_strtolower($this->value);
    }

    public function equals(self $other): bool
    {
        return $this->normalizationKey() === $other->normalizationKey();
    }
}
