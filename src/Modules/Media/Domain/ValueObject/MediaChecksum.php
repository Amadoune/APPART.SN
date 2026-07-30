<?php

namespace Appart\Modules\Media\Domain\ValueObject;

use Appart\Modules\Media\Domain\Exception\InvalidMediaValue;

final readonly class MediaChecksum
{
    private function __construct(public string $value) {}

    public static function fromSha256(string $value): self
    {
        $value = strtolower(trim($value));
        if (preg_match('/^[0-9a-f]{64}$/', $value) !== 1) {
            throw InvalidMediaValue::field('media_checksum');
        }

        return new self($value);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
