<?php

namespace Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Contract;

use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationPersistenceReadResult;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationPersistenceWriteResult;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationState;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationTransition;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;

interface ListingPublicationWorkflowStore
{
    public function initialize(ListingId $listingId, ListingPublicationState $state): ListingPublicationPersistenceWriteResult;

    public function append(ListingId $listingId, ListingPublicationTransition $transition, int $version): ListingPublicationPersistenceWriteResult;

    public function read(ListingId $listingId): ListingPublicationPersistenceReadResult;
}
