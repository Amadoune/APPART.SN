<?php

namespace App\Application\PublicProjectionStore;

use InvalidArgumentException;

final readonly class PublicProjectionWatermark
{
    public function __construct(
        public int $listingVersion,
        public int $propertyVersion,
        public int $mediaVersion,
        public int $searchVersion,
        public int $contentSeoVersion,
        public ?int $publicGeographyVersion,
        public ?int $publicMediaVersion,
    ) {
        if ($listingVersion < 0 || $propertyVersion < 0 || $mediaVersion < 0 || $searchVersion < 1 || $contentSeoVersion < 1 || ($publicGeographyVersion !== null && $publicGeographyVersion < 1) || ($publicMediaVersion !== null && $publicMediaVersion < 1)) {
            throw new InvalidArgumentException('Public projection source versions are invalid.');
        }
    }

    public function readiness(): PublicProjectionPromotionReadiness
    {
        return match (true) {
            $this->publicGeographyVersion === null && $this->publicMediaVersion === null => PublicProjectionPromotionReadiness::MissingPublicGeographyAndMediaVersions,
            $this->publicGeographyVersion === null => PublicProjectionPromotionReadiness::MissingPublicGeographyVersion,
            $this->publicMediaVersion === null => PublicProjectionPromotionReadiness::MissingPublicMediaVersion,
            default => PublicProjectionPromotionReadiness::Ready,
        };
    }

    public function compareTo(self $other): PublicProjectionWatermarkRelation
    {
        if ($this->readiness() !== PublicProjectionPromotionReadiness::Ready || $other->readiness() !== PublicProjectionPromotionReadiness::Ready) {
            return PublicProjectionWatermarkRelation::Incomplete;
        }

        $left = $this->versions();
        $right = $other->versions();
        $hasGreater = false;
        $hasLower = false;

        foreach ($left as $index => $version) {
            $hasGreater = $hasGreater || $version > $right[$index];
            $hasLower = $hasLower || $version < $right[$index];
        }

        return match (true) {
            $hasGreater && $hasLower => PublicProjectionWatermarkRelation::Incomparable,
            $hasGreater => PublicProjectionWatermarkRelation::Newer,
            $hasLower => PublicProjectionWatermarkRelation::Older,
            default => PublicProjectionWatermarkRelation::Equal,
        };
    }

    /** @return list<int> */
    private function versions(): array
    {
        if ($this->publicGeographyVersion === null || $this->publicMediaVersion === null) {
            throw new \LogicException('An incomplete watermark cannot expose a comparable vector.');
        }

        return [$this->listingVersion, $this->propertyVersion, $this->mediaVersion, $this->searchVersion, $this->contentSeoVersion, $this->publicGeographyVersion, $this->publicMediaVersion];
    }
}
