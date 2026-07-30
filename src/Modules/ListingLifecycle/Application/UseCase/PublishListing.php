<?php

namespace Appart\Modules\ListingLifecycle\Application\UseCase;

use Appart\Modules\ListingLifecycle\Application\Contract\ListingRegistry;
use Appart\Modules\ListingLifecycle\Application\Contract\MediaCatalog;
use Appart\Modules\ListingLifecycle\Application\Contract\PropertyCatalog;
use Appart\Modules\ListingLifecycle\Domain\Model\TransitionEvidence;
use Appart\Modules\ListingLifecycle\Domain\Policy\ListingTransitionPolicy;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ExpirationDate;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingRevisionId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\MediaCollectionId;

final readonly class PublishListing extends ListingUseCase
{
    public function __construct(ListingRegistry $listings, PropertyCatalog $properties, ListingTransitionPolicy $policy, private MediaCatalog $media)
    {
        parent::__construct($listings, $properties, $policy);
    }

    public function execute(ListingId $id, MediaCollectionId $collectionId, ListingRevisionId $revisionId, ExpirationDate $expiration, TransitionEvidence $evidence): void
    {
        [$listing, $version] = $this->load($id);
        $propertyAvailability = $this->properties->availabilityOf($listing->propertyId());
        $mediaAvailability = $this->media->publicationAvailabilityOf($collectionId, $listing->propertyId());
        $listing->publish($revisionId, $expiration, $evidence, $this->policy, $propertyAvailability, $mediaAvailability);
        $this->listings->save($listing, $version);
    }
}
