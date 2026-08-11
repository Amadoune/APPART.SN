<?php

namespace Appart\Modules\ListingLifecycle\Application\UseCase;

use Appart\Modules\ListingLifecycle\Application\Contract\ListingRegistry;
use Appart\Modules\ListingLifecycle\Application\Contract\MediaCatalog;
use Appart\Modules\ListingLifecycle\Application\Contract\PropertyCatalog;
use Appart\Modules\ListingLifecycle\Application\Creation\Contract\ListingCreationTransaction;
use Appart\Modules\ListingLifecycle\Application\PublicFacts\Contract\AuthoringPublicFactHandoffV1;
use Appart\Modules\ListingLifecycle\Application\PublicFacts\PublicFactHandoffResult;
use Appart\Modules\ListingLifecycle\Domain\Model\TransitionEvidence;
use Appart\Modules\ListingLifecycle\Domain\Policy\ListingTransitionPolicy;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ExpirationDate;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingRevisionId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\MediaCollectionId;

final readonly class PublishListing extends ListingUseCase
{
    public function __construct(ListingRegistry $listings, PropertyCatalog $properties, ListingTransitionPolicy $policy, private MediaCatalog $media, private ?AuthoringPublicFactHandoffV1 $publicFacts = null, private ?ListingCreationTransaction $transaction = null)
    {
        parent::__construct($listings, $properties, $policy);
    }

    public function execute(ListingId $id, MediaCollectionId $collectionId, ListingRevisionId $revisionId, ExpirationDate $expiration, TransitionEvidence $evidence): void
    {
        $operation = function () use ($id, $collectionId, $revisionId, $expiration, $evidence): void {
            [$listing, $version] = $this->load($id);
            $propertyAvailability = $this->properties->availabilityOf($listing->propertyId());
            $mediaAvailability = $this->media->publicationAvailabilityOf($collectionId, $listing->propertyId());
            $candidate = $this->publicFacts?->candidate($id->value);
            $listing->publish($revisionId, $expiration, $evidence, $this->policy, $propertyAvailability, $mediaAvailability, $candidate?->transactionKind);
            $this->listings->save($listing, $version);
            if ($candidate !== null) {
                $sealed = $this->publicFacts->seal($id->value, $revisionId->value, $evidence->occurredAt);
                if (! in_array($sealed, [PublicFactHandoffResult::Applied, PublicFactHandoffResult::AlreadyApplied], true)) {
                    throw new \RuntimeException('Public facts could not be sealed.');
                }
            }
        };

        if ($this->transaction !== null) {
            $this->transaction->run($operation);
        } else {
            $operation();
        }
    }
}
