<?php

namespace Appart\Modules\Media\Domain\ValueObject;

use Appart\Modules\Media\Domain\Exception\InvalidMediaValue;

final readonly class MediaCollectionId
{
    private function __construct(public string $value) {}

    public static function fromString(string $value): self
    {
        return new self(self::uuid($value));
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    private static function uuid(string $value): string
    {
        $value = strtolower(trim($value));
        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $value) !== 1) {
            throw InvalidMediaValue::field('media_collection_id');
        }

        return $value;
    }
}
