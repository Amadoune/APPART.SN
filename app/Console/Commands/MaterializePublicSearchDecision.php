<?php

namespace App\Console\Commands;

use Appart\Modules\SearchDiscovery\Application\Materialization\Contract\CatchUpPublicSearchDecisionV1;
use Appart\Modules\SearchDiscovery\Application\Materialization\PublicSearchMaterializationStatus;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\ListingId;
use Illuminate\Console\Command;

final class MaterializePublicSearchDecision extends Command
{
    protected $signature = 'appart:search:materialize {listingId}';

    protected $description = 'Materialize or catch up the authoritative Search decision for a Listing.';

    public function handle(CatchUpPublicSearchDecisionV1 $catchUp): int
    {
        $result = $catchUp->catchUp(ListingId::fromString((string) $this->argument('listingId')));
        $this->line($result->status->value);

        return in_array($result->status, [PublicSearchMaterializationStatus::Applied, PublicSearchMaterializationStatus::AlreadyApplied], true)
            ? self::SUCCESS
            : self::FAILURE;
    }
}
