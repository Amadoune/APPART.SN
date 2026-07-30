<?php

namespace Appart\Modules\SearchDiscovery\Application\UseCase;

use Appart\Modules\SearchDiscovery\Application\Contract\ListingCatalog;
use Appart\Modules\SearchDiscovery\Application\Contract\MediaCatalog;
use Appart\Modules\SearchDiscovery\Application\Contract\PropertyCatalog;
use Appart\Modules\SearchDiscovery\Domain\Exception\ProjectionSourceUnavailable;
use Appart\Modules\SearchDiscovery\Domain\Model\ListingProjectionSource;
use Appart\Modules\SearchDiscovery\Domain\Model\MediaProjectionSource;
use Appart\Modules\SearchDiscovery\Domain\Model\PropertyProjectionSource;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\ListingId;

final readonly class ProjectionSources
{
    public function __construct(private ListingCatalog $listings, private PropertyCatalog $properties, private MediaCatalog $media) {}

    /** @return array{ListingProjectionSource,PropertyProjectionSource,MediaProjectionSource} */
    public function load(ListingId $id): array
    {
        return [$this->listings->projectionFor($id) ?? throw new ProjectionSourceUnavailable, $this->properties->projectionFor($id) ?? throw new ProjectionSourceUnavailable, $this->media->projectionFor($id) ?? throw new ProjectionSourceUnavailable];
    }

    public function listing(ListingId $id): ListingProjectionSource
    {
        return $this->listings->projectionFor($id) ?? throw new ProjectionSourceUnavailable;
    }
}
