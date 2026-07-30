<?php

namespace Appart\Modules\Media\Domain\ValueObject;

use Appart\Modules\Media\Domain\Exception\InvalidMediaValue;

final readonly class MediaCaption
{
    private function __construct(public string $value) {}

    public static function fromString(string $value): self
    {
        $value = preg_replace('/\s+/u', ' ', trim($value));
        if (! is_string($value) || mb_strlen($value) < 1 || mb_strlen($value) > 500) {
            throw InvalidMediaValue::field('media_caption');
        }

        return new self($value);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
