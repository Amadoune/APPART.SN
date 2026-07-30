<?php

namespace App\Application\PublicProjectionUpdater;

use App\Application\PublicProjectionStore\PublicProjectionGenerationId;
use Appart\Modules\ContentSeo\Domain\Model\CanonicalHistoryEntry;
use Appart\Modules\ContentSeo\Domain\Model\ListingSeoSource;
use Appart\Modules\ContentSeo\Domain\Model\PropertySeoSource;
use Appart\Modules\ContentSeo\Domain\Model\PublicGeographySeoSource;
use Appart\Modules\ContentSeo\Domain\Model\PublicMediaSeoSource;
use Appart\Modules\ContentSeo\Domain\Model\SearchSeoSource;
use Appart\Modules\ListingLifecycle\Domain\Model\Listing;
use Appart\Modules\Media\Domain\Model\MediaCollection;
use Appart\Modules\RealEstateCatalog\Domain\Model\Property;
use DateTimeImmutable;
use InvalidArgumentException;

final readonly class PublicListingProjectionSources
{
    /** @param list<CanonicalHistoryEntry> $canonicalHistory */
    public function __construct(
        public Property $property,
        public MediaCollection $media,
        public Listing $listing,
        public ListingSeoSource $listingSeo,
        public SearchSeoSource $searchSeo,
        public PropertySeoSource $propertySeo,
        public ?PublicGeographySeoSource $publicGeographySeo,
        public ?PublicMediaSeoSource $publicMediaSeo,
        public array $canonicalHistory,
        public DateTimeImmutable $decisionAt,
        public int $searchVersion,
        public int $contentSeoVersion,
        public ?int $publicGeographyVersion,
        public ?int $publicMediaVersion,
        public PublicProjectionGenerationId $generationId,
    ) {
        if ($searchVersion < 1 || $contentSeoVersion < 1 || ($publicGeographyVersion !== null && $publicGeographyVersion < 1) || ($publicMediaVersion !== null && $publicMediaVersion < 1)) {
            throw new InvalidArgumentException('Public reconstruction source versions are invalid.');
        }
        if (($publicGeographySeo === null) !== ($publicGeographyVersion === null) || ($publicMediaSeo === null) !== ($publicMediaVersion === null)) {
            throw new InvalidArgumentException('A public source and its stable version must be supplied together.');
        }
    }
}
