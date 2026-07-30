<?php

namespace Appart\Modules\ListingLifecycle\Domain\ValueObject;

use Appart\Modules\ListingLifecycle\Domain\Exception\InvalidListingValue;

final readonly class MediaCollectionId
{
    private function __construct(public string $value) {}

    public static function fromString(string $value): self
    {
        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $value) !== 1) {
            throw InvalidListingValue::field('media_collection_id');
        }

        return new self(strtolower($value));
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
