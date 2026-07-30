<?php

namespace Appart\Modules\ContentSeo\Domain\Model;

use Appart\Modules\ContentSeo\Domain\ValueObject\ListingId;
use DateTimeImmutable;

final readonly class SeoDocument
{
    private function __construct(public ListingId $listingId, public SeoMaterial $material, public DateTimeImmutable $generatedAt, public DateTimeImmutable $updatedAt) {}

    public static function generate(ListingId $listingId, SeoMaterial $material, DateTimeImmutable $at): self
    {
        return new self($listingId, $material, $at, $at);
    }

    public function update(SeoMaterial $material, DateTimeImmutable $at): self
    {
        return new self($this->listingId, $material, $this->generatedAt, $at);
    }
}
