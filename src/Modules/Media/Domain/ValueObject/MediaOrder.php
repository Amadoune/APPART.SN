<?php

namespace Appart\Modules\Media\Domain\ValueObject;

use Appart\Modules\Media\Domain\Exception\InvalidMediaValue;

final readonly class MediaOrder
{
    private function __construct(public int $value) {}

    public static function fromInt(int $value): self
    {
        if ($value < 1 || $value > 10_000) {
            throw InvalidMediaValue::field('media_order');
        }

        return new self($value);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
