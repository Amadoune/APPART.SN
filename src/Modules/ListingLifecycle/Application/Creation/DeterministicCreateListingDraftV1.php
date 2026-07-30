<?php

namespace Appart\Modules\ListingLifecycle\Application\Creation;

use Appart\Modules\ListingLifecycle\Application\Creation\Contract\CreateListingDraftV1;
use Appart\Modules\ListingLifecycle\Application\Creation\Contract\ListingCreationIntentStore;
use Appart\Modules\ListingLifecycle\Application\Creation\Contract\ListingCreationTransaction;
use Appart\Modules\ListingLifecycle\Application\UseCase\CreateDraft;
use Appart\Modules\ListingLifecycle\Domain\Exception\InvalidListingValue;
use Appart\Modules\ListingLifecycle\Domain\Exception\ListingIdConflict;
use Appart\Modules\ListingLifecycle\Domain\Exception\TransitionConditionNotSatisfied;
use Appart\Modules\ListingLifecycle\Domain\Model\TransitionEvidence;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ActorId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingRevisionId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\PropertyId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\TransitionOrigin;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\TransitionReason;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\TransitionTrigger;
use Throwable;

final readonly class DeterministicCreateListingDraftV1 implements CreateListingDraftV1
{
    public function __construct(
        private CreateDraft $createDraft,
        private ListingCreationIntentStore $intents,
        private ListingCreationTransaction $transaction,
    ) {}

    public function create(CreateListingDraftCommandV1 $command): CreateListingDraftResultV1
    {
        $intent = new ListingCreationIntent(
            strtolower(trim($command->intentId)),
            $command->checksum(),
            strtolower(trim($command->listingId)),
            strtolower(trim($command->propertyId)),
        );

        try {
            return $this->transaction->run(function () use ($command, $intent): CreateListingDraftResultV1 {
                if (! $this->intents->reserve($intent)) {
                    $existing = $this->intents->find($intent->intentId);
                    if ($existing === null || ! hash_equals($existing->checksum, $intent->checksum)) {
                        return CreateListingDraftResultV1::failed(CreateListingDraftStatusV1::DivergentIntent, 'intent_divergence');
                    }

                    return CreateListingDraftResultV1::alreadyApplied(
                        $existing->listingId,
                        $existing->propertyId,
                        $existing->aggregateVersion ?? 0,
                    );
                }

                $listing = $this->createDraft->execute(
                    ListingId::fromString($command->listingId),
                    PropertyId::fromString($command->propertyId),
                    ListingRevisionId::fromString($command->revisionId),
                    new TransitionEvidence(
                        ActorId::fromString($command->actorId),
                        TransitionTrigger::DraftStarted,
                        TransitionReason::fromString('Initial authoring draft.'),
                        TransitionOrigin::Advertiser,
                        $command->occurredAt,
                    ),
                );
                $this->intents->markApplied($intent->intentId, $listing->version());

                return CreateListingDraftResultV1::applied(
                    $listing->id()->value,
                    $listing->propertyId()->value,
                    $listing->version(),
                );
            });
        } catch (TransitionConditionNotSatisfied) {
            return CreateListingDraftResultV1::failed(CreateListingDraftStatusV1::PropertyUnavailable, 'property_unavailable');
        } catch (ListingIdConflict) {
            return CreateListingDraftResultV1::failed(CreateListingDraftStatusV1::ListingIdConflict, 'listing_id_conflict');
        } catch (InvalidListingValue) {
            return CreateListingDraftResultV1::failed(CreateListingDraftStatusV1::InvalidCommand, 'invalid_command');
        } catch (Throwable) {
            return CreateListingDraftResultV1::failed(CreateListingDraftStatusV1::PersistenceFailure, 'persistence_failure');
        }
    }
}
