<?php

namespace App\Application\PublicProjectionStore;

use App\ReadModels\PublicListingReadModel;
use InvalidArgumentException;

final readonly class PublicListingProjectionRecord
{
    private function __construct(
        public string $listingId,
        public string $canonicalPath,
        public ?PublicListingReadModel $readModel,
        public PublicProjectionWatermark $watermark,
        public PublicListingProjectionState $state,
        public PublicProjectionGenerationId $generationId,
    ) {
        self::assertUuid($listingId);
        self::assertCanonicalPath($canonicalPath);

        if ($state === PublicListingProjectionState::Current) {
            if ($readModel === null || $readModel->listingId !== $listingId || self::pathOf($readModel->canonicalUrl) !== $canonicalPath) {
                throw new InvalidArgumentException('A current public projection must contain the matching final read model.');
            }
        } elseif ($readModel !== null) {
            throw new InvalidArgumentException('Historical projections and tombstones do not expose a public read model.');
        }
    }

    public static function current(
        string $listingId,
        string $canonicalPath,
        PublicListingReadModel $readModel,
        PublicProjectionWatermark $watermark,
        PublicProjectionGenerationId $generationId,
    ): self {
        return new self($listingId, $canonicalPath, $readModel, $watermark, PublicListingProjectionState::Current, $generationId);
    }

    public static function historical(
        string $listingId,
        string $canonicalPath,
        PublicProjectionWatermark $watermark,
        PublicProjectionGenerationId $generationId,
    ): self {
        return new self($listingId, $canonicalPath, null, $watermark, PublicListingProjectionState::Historical, $generationId);
    }

    public static function tombstone(
        string $listingId,
        string $canonicalPath,
        PublicProjectionWatermark $watermark,
        PublicProjectionGenerationId $generationId,
    ): self {
        return new self($listingId, $canonicalPath, null, $watermark, PublicListingProjectionState::Tombstone, $generationId);
    }

    public function equivalentPayloadTo(self $other): bool
    {
        return $this->listingId === $other->listingId
            && $this->canonicalPath === $other->canonicalPath
            && $this->readModel == $other->readModel
            && $this->watermark == $other->watermark
            && $this->state === $other->state
            && $this->generationId->equals($other->generationId);
    }

    private static function assertUuid(string $value): void
    {
        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $value) !== 1) {
            throw new InvalidArgumentException('Invalid internal listing identity for public projection.');
        }
    }

    private static function assertCanonicalPath(string $value): void
    {
        if ($value === '' || trim($value) !== $value || str_starts_with($value, '/') || str_ends_with($value, '/') || str_contains($value, '?') || str_contains($value, '#')) {
            throw new InvalidArgumentException('Invalid decided canonical path for public projection.');
        }
    }

    private static function pathOf(string $canonicalUrl): ?string
    {
        $path = parse_url($canonicalUrl, PHP_URL_PATH);

        return is_string($path) ? ltrim($path, '/') : null;
    }
}
